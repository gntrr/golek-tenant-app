<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentProof;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\WebhookLog;
use Midtrans\Config as MidtransConfig;
use Midtrans\Snap as MidtransSnap;
use Midtrans\Transaction as MidtransTransaction;

class PaymentController extends Controller
{
    /**
     * Get the latest Payment for an order or create a new in-memory instance.
     * We aim to maintain a single payment row per order and update its provider.
     */
    private function getOrCreateOrderPayment(Order $order): Payment
    {
        $payment = Payment::where('order_id', $order->id)->latest('id')->first();
        if (!$payment) {
            $payment = new Payment();
            $payment->order_id = $order->id;
            $payment->amount = $order->total_amount;
            $payment->status = Payment::STATUS_INITIATED;
        }
        return $payment;
    }

    public function selectMethod(Order $order)
    {
        $order->load('event');
        if (!in_array($order->status, ['PENDING','AWAITING_PAYMENT'])) {
            return redirect()->route('client.payment.status', $order);
        }

        // Dukung kunci lama "midtrans_enable" selain "midtrans_enabled"
        $midtransEnabled = (bool) (
            Setting::get('payments', 'midtrans_enabled', null)
            ?? Setting::get('payments', 'midtrans_enable', true)
        );
        $bankTransferEnabled = (bool) Setting::get('payments', 'bank_transfer_enabled', true);
        // Tampilkan fallback banner hanya jika Midtrans dimatikan
        $fallbackBanner = $midtransEnabled ? null : Setting::get('payments', 'fallback_banner', null);

        return view('client.payments.select', compact('order', 'midtransEnabled', 'bankTransferEnabled', 'fallbackBanner'));
    }

    public function processMidtrans(Request $request, Order $order)
    {
        // Dukung kunci lama "midtrans_enable" selain "midtrans_enabled"
        $midtransEnabled = (bool) (
            Setting::get('payments', 'midtrans_enabled', null)
            ?? Setting::get('payments', 'midtrans_enable', true)
        );
        if (!$midtransEnabled) {
            return redirect()->route('client.payment.upload.form', $order)
                ->with('warning', 'Midtrans sedang tidak tersedia. Silakan gunakan Upload Bukti Transfer.');
        }

        // Siapkan konfigurasi Midtrans
        MidtransConfig::$serverKey = config('services.midtrans.server_key');
        MidtransConfig::$isProduction = (bool) config('services.midtrans.is_production', false);
        MidtransConfig::$isSanitized = true;
        MidtransConfig::$is3ds = true;

        try {
            // Gunakan satu baris payment per order, update providernya bila berubah
            $payment = $this->getOrCreateOrderPayment($order);
            $payment->provider = Payment::PROVIDER_MIDTRANS;
            $payment->amount = $order->total_amount;
            $payment->status = Payment::STATUS_INITIATED;

            $order->loadMissing(['items.booth']);

            $params = [
                'transaction_details' => [
                    'order_id' => $order->invoice_number,
                    'gross_amount' => $order->total_amount,
                ],
                'customer_details' => [
                    'first_name' => $order->customer_name,
                    'email' => $order->email,
                    'phone' => $order->phone,
                ],
                'item_details' => $order->items->map(function ($item) {
                    return [
                        'id' => (string) $item->booth_id,
                        'price' => (int) $item->price_snapshot,
                        'quantity' => 1,
                        'name' => optional($item->booth)->code ?? 'Booth',
                    ];
                })->values()->all(),
                // Pastikan pelanggan kembali ke halaman status setelah keluar dari Snap
                'callbacks' => [
                    'finish' => route('client.payment.midtrans.return'),
                ],
            ];

            $snap = MidtransSnap::createTransaction($params);

            // Update Payment dan Order
            $payment->status = Payment::STATUS_PENDING;
            $payment->raw_payload = [
                'request' => $params,
                'response' => $snap,
            ];
            $payment->save();

            $order->payment_method = Order::METHOD_MIDTRANS;
            $order->status = Order::STATUS_AWAITING;
            $order->save();

            // Redirect ke halaman pembayaran Midtrans (Snap)
            $redirectUrl = is_array($snap) ? ($snap['redirect_url'] ?? null) : ($snap->redirect_url ?? null);
            if ($redirectUrl) {
                return redirect()->away($redirectUrl);
            }

            return redirect()->route('client.payment.status', $order)->with('info', 'Menunggu pembayaran melalui Midtrans.');
        } catch (\Throwable $e) {
            Log::error('Midtrans error: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->route('client.payment.status', $order)
                ->with('error', 'Gagal memproses pembayaran Midtrans. Silakan coba lagi atau gunakan transfer bank.');
        }
    }

    public function uploadForm(Order $order)
    {
        $order->load('event');
        // Tampilkan fallback banner hanya jika Midtrans dimatikan
        $midtransEnabled = (bool) (
            Setting::get('payments', 'midtrans_enabled', null)
            ?? Setting::get('payments', 'midtrans_enable', true)
        );
        $fallbackBanner = $midtransEnabled ? null : Setting::get('payments', 'fallback_banner', null);
        $instructions = Setting::get('payments', 'bank_transfer_instructions', null);
        if (!$instructions) {
            $instructions = "Silakan transfer ke salah satu rekening berikut:\n\n• BCA 1234567890 a.n. PT Golek Tenant\n• BNI 9876543210 a.n. PT Golek Tenant\n\nNominal harus sesuai invoice.\nSetelah transfer, unggah bukti pada halaman \"Upload Bukti Pembayaran\".\nVerifikasi manual membutuhkan waktu maks. 1x24 jam kerja.";
        }

    // Siapkan/ambil payment tunggal dan ubah ke BANK_TRANSFER
    $payment = $this->getOrCreateOrderPayment($order);
    $payment->provider = Payment::PROVIDER_BANK;
    $payment->amount = $order->total_amount;
    $payment->status = Payment::STATUS_PENDING;
    $payment->save();

    if (!$payment->va_number) {
            // Generate VA sederhana (simulasi): BANK + tanggal + 6 digit acak
            $bank = 'bca'; // bisa ditentukan dari pilihan user di form, default bca
            $va   = '988'.date('md').str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $payment->va_number = $va;
            $payment->bank = $bank;
            $payment->save();
        }

        return view('client.payments.upload', compact('order', 'fallbackBanner', 'instructions', 'payment'));
    }

    public function uploadProof(Request $request, Order $order)
    {
        // bank wajib jika belum ada VA; jika sudah ada, boleh tidak mengirim bank lagi
        $hasVa = Payment::where('order_id', $order->id)->where('provider', Payment::PROVIDER_BANK)->whereNotNull('va_number')->exists();
        $request->validate([
            'bank' => [$hasVa ? 'nullable' : 'required', 'in:bca,bni,bri,permata'],
            'proof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $path = $request->file('proof')->store('payment-proofs/'.date('Y/m'), 's3');

        // Gunakan satu baris payment per order dan pastikan providernya BANK_TRANSFER
        $payment = $this->getOrCreateOrderPayment($order);
        $payment->provider = Payment::PROVIDER_BANK;
        $payment->amount = $order->total_amount;
        $payment->status = Payment::STATUS_PENDING;
        $payment->save();

        // Regenerate VA if bank changed or VA empty
        $selectedBank = $request->input('bank');
        if ($selectedBank && (!$payment->va_number || $payment->bank !== $selectedBank)) {
            $prefixMap = [
                'bca' => '988',
                'bni' => '609',
                'bri' => '262',
                'permata' => '825',
            ];
            $prefix = $prefixMap[$selectedBank] ?? '988';
            $va = $prefix . date('md') . str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $payment->va_number = $va;
            $payment->bank = $selectedBank;
            $payment->save();
        }

        // Save proof record
        PaymentProof::create([
            'order_id' => $order->id,
            'file_path' => $path,
            'status' => PaymentProof::STATUS_PENDING,
        ]);

        $order->payment_method = Order::METHOD_BANK;
        $order->status = Order::STATUS_AWAITING;
        $order->save();

        return redirect()->route('client.payment.status', $order)->with('success', 'Bukti transfer berhasil diunggah. Menunggu verifikasi.');
    }

    public function changeBankVA(Request $request, Order $order)
    {
        $request->validate([
            'bank' => ['required', 'in:bca,bni,bri,permata'],
        ]);

        $payment = $this->getOrCreateOrderPayment($order);
        $payment->provider = Payment::PROVIDER_BANK;
        $payment->amount = $order->total_amount;
        $payment->status = Payment::STATUS_PENDING;

        $selectedBank = $request->input('bank');
        $prefixMap = [
            'bca' => '988',
            'bni' => '609',
            'bri' => '262',
            'permata' => '825',
        ];
        $prefix = $prefixMap[$selectedBank] ?? '988';
        $va = $prefix . date('md') . str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $payment->va_number = $va;
        $payment->bank = $selectedBank;
        $payment->save();

        return back()->with('success', 'Bank VA diperbarui.');
    }

    public function status(Order $order)
    {
        $order->load(['payments', 'event']);
        return view('client.payments.status', compact('order'));
    }

    public function handleMidtransCallback(Request $request)
    {
        $payload = $request->all();

        // Simpan log webhook terlebih dahulu
        $log = null;
        try {
            $log = WebhookLog::create([
                'provider' => 'midtrans',
                'event' => 'payment.notification',
                'raw_payload' => $payload,
                'processed' => false,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed to log Midtrans webhook: '.$e->getMessage());
        }

        // Verifikasi signature
        $serverKey = config('services.midtrans.server_key');
        $expectedSignature = hash('sha512', ($payload['order_id'] ?? '').($payload['status_code'] ?? '').($payload['gross_amount'] ?? '').$serverKey);
        if (!isset($payload['signature_key']) || $expectedSignature !== $payload['signature_key']) {
            Log::warning('Midtrans signature mismatch', ['payload' => $payload]);
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        // Proses update status
        $order = Order::where('invoice_number', $payload['order_id'] ?? '')->first();
        if (!$order) {
            Log::warning('Order not found for Midtrans callback', ['order_id' => $payload['order_id'] ?? null]);
            return response()->json(['message' => 'ok']);
        }

        DB::beginTransaction();
        try {
            // Ambil payment tunggal dan set provider ke MIDTRANS
            $payment = $this->getOrCreateOrderPayment($order);
            $payment->provider = Payment::PROVIDER_MIDTRANS;
            $payment->amount = $order->total_amount;
            $payment->status = Payment::STATUS_PENDING;

            $transactionStatus = $payload['transaction_status'] ?? null;
            $fraudStatus = $payload['fraud_status'] ?? null;
            $paymentType = $payload['payment_type'] ?? null;

            // Catat transaksi Midtrans saat ada (transaction_id atau transaksi id lain)
            if (!empty($payload['transaction_id'] ?? null)) {
                $payment->midtrans_txn_id = (string) $payload['transaction_id'];
            } elseif (!empty($payload['transaction_status'] ?? null) && !empty($payload['order_id'] ?? null)) {
                // fallback: gunakan order_id + status sebagai jejak bila transaction_id tidak disertakan (jarang terjadi di sandbox)
                $payment->midtrans_txn_id = (string) ($payload['order_id'].'|'.$payload['transaction_status']);
            }

            // Simpan info VA jika ada
            if ($paymentType === 'bank_transfer') {
                if (!empty($payload['va_numbers'][0]['va_number'] ?? null)) {
                    $payment->va_number = $payload['va_numbers'][0]['va_number'];
                    $payment->bank = $payload['va_numbers'][0]['bank'] ?? null;
                } elseif (!empty($payload['permata_va_number'] ?? null)) {
                    $payment->va_number = $payload['permata_va_number'];
                    $payment->bank = 'permata';
                }
            }

            $payment->raw_payload = $payload;

            if ($transactionStatus === 'capture') {
                if ($fraudStatus === 'challenge') {
                    $payment->status = Payment::STATUS_PENDING;
                    $order->status = Order::STATUS_AWAITING;
                } else {
                    $payment->status = Payment::STATUS_SETTLEMENT;
                    $payment->paid_at = now();
                    $order->status = Order::STATUS_PAID;
                }
            } elseif ($transactionStatus === 'settlement') {
                $payment->status = Payment::STATUS_SETTLEMENT;
                $payment->paid_at = now();
                $order->status = Order::STATUS_PAID;
            } elseif ($transactionStatus === 'pending') {
                $payment->status = Payment::STATUS_PENDING;
                $order->status = Order::STATUS_AWAITING;
            } elseif ($transactionStatus === 'deny') {
                $payment->status = Payment::STATUS_DENY;
                $order->status = Order::STATUS_EXPIRED;
            } elseif ($transactionStatus === 'expire') {
                $payment->status = Payment::STATUS_EXPIRE;
                $order->status = Order::STATUS_EXPIRED;
            } elseif ($transactionStatus === 'cancel') {
                $payment->status = Payment::STATUS_CANCEL;
                $order->status = Order::STATUS_CANCELLED;
            }

            $payment->save();
            $order->save();

            // Update status booth berdasarkan status order
            $order->loadMissing('items.booth');
            if ($order->status === Order::STATUS_PAID) {
                foreach ($order->items as $item) {
                    if ($item->booth) {
                        $item->booth->update(['status' => 'BOOKED', 'expires_at' => null]);
                    }
                }
            } elseif (in_array($order->status, [Order::STATUS_EXPIRED, Order::STATUS_CANCELLED])) {
                foreach ($order->items as $item) {
                    if ($item->booth) {
                        $item->booth->update(['status' => 'AVAILABLE', 'expires_at' => null]);
                    }
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Failed processing Midtrans webhook: '.$e->getMessage(), ['payload' => $payload]);
            return response()->json(['message' => 'error'], 500);
        }

        // Tandai webhook sudah diproses
        try {
            if ($log) {
                $log->update(['processed' => true, 'processed_at' => now()]);
            }
        } catch (\Throwable $e) {
            // ignore
        }

        return response()->json(['message' => 'ok']);
    }

    /*
     * Universal return handler for Midtrans Snap redirect (Finish/Unfinish/Error)
     * We rely on webhook for the actual payment status update. This endpoint
     * simply guides user back to our order status page based on order_id query.
     */
    public function midtransReturn(Request $request)
    {
        $invoice = $request->query('order_id');
        $status  = $request->query('transaction_status');
        $action  = $request->query('action'); // e.g., back

        if (!$invoice) {
            return redirect()->route('home')->with('warning', 'Tidak ada informasi pesanan.');
        }

        $order = Order::where('invoice_number', $invoice)->first();
        if (!$order) {
            return redirect()->route('home')->with('warning', 'Pesanan tidak ditemukan.');
        }

        // Upaya konfirmasi cepat (khusus pengujian/tunnel belum siap):
        // Jika status dari Snap mengindikasikan sukses, coba verifikasi ke Midtrans
        // dan update Order/Payment agar pengguna melihat status LUNAS tanpa menunggu webhook.
        try {
            if (in_array($status, ['settlement', 'capture'])) {
                // Siapkan konfigurasi Midtrans
                MidtransConfig::$serverKey    = config('services.midtrans.server_key');
                MidtransConfig::$isProduction = (bool) config('services.midtrans.is_production', false);

                $resp = MidtransTransaction::status($invoice);
                // Normalisasi respons menjadi array sederhana
                $respArr = is_array($resp) ? $resp : json_decode(json_encode($resp), true);
                $txnStatus = $respArr['transaction_status'] ?? null;
                $fraud     = $respArr['fraud_status'] ?? null;
                $payType   = $respArr['payment_type'] ?? null;

                if ($txnStatus === 'settlement' || ($txnStatus === 'capture' && $fraud !== 'challenge')) {
                    // Sinkronkan seperti di webhook
                    \DB::transaction(function () use ($order, $respArr, $payType) {
                        $payment = $this->getOrCreateOrderPayment($order);
                        $payment->provider = Payment::PROVIDER_MIDTRANS;
                        $payment->amount = $order->total_amount;
                        $payment->status = Payment::STATUS_PENDING;

                        if (!empty($respArr['transaction_id'] ?? null)) {
                            $payment->midtrans_txn_id = (string) $respArr['transaction_id'];
                        } elseif (!empty($respArr['status_code'] ?? null)) {
                            $payment->midtrans_txn_id = (string) ($invoice.'|'.$respArr['status_code']);
                        }

                        if ($payType === 'bank_transfer') {
                            if (!empty($respArr['va_numbers'][0]['va_number'] ?? null)) {
                                $payment->va_number = $respArr['va_numbers'][0]['va_number'];
                                $payment->bank = $respArr['va_numbers'][0]['bank'] ?? null;
                            } elseif (!empty($respArr['permata_va_number'] ?? null)) {
                                $payment->va_number = $respArr['permata_va_number'];
                                $payment->bank = 'permata';
                            }
                        }

                        $payment->raw_payload = $respArr;
                        $payment->status  = Payment::STATUS_SETTLEMENT;
                        $payment->paid_at = now();
                        $payment->save();

                        $order->payment_method = Order::METHOD_MIDTRANS;
                        $order->status = Order::STATUS_PAID;
                        $order->save();

                        // Update status booth
                        $order->loadMissing('items.booth');
                        foreach ($order->items as $item) {
                            if ($item->booth) {
                                $item->booth->update(['status' => 'BOOKED', 'expires_at' => null]);
                            }
                        }
                    });

                    // Setelah sinkron, langsung arahkan ke status tanpa pesan tambahan
                    return redirect()->route('client.payment.status', $order);
                }
            }
        } catch (\Throwable $e) {
            // Abaikan bila gagal verifikasi cepat, fallback ke pesan biasa
            \Log::info('Midtrans quick verify on return failed: '.$e->getMessage());
        }

        // Berikan pesan informatif bila user belum menyelesaikan pembayaran di Snap
        $message = null;
        if ($action === 'back' || $status === 'pending') {
            $message = 'Pembayaran belum selesai di Midtrans. Silakan lanjutkan pembayaran atau pilih metode lain.';
        } elseif ($status === 'deny' || $status === 'expire' || $status === 'cancel') {
            $message = 'Transaksi tidak berhasil atau kedaluwarsa. Anda dapat mencoba lagi.';
        }

        return $message
            ? redirect()->route('client.payment.status', $order)->with('info', $message)
            : redirect()->route('client.payment.status', $order);
    }
}