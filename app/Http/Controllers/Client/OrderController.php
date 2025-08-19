<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Booth;
use App\Models\Event;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function create(Booth $booth)
    {
        $event = $booth->event;
        if (!$event->is_active || $event->ends_at < now()) {
            abort(404, 'Event tidak ditemukan atau sudah berakhir.');
        }

        if (!in_array($booth->status, ['AVAILABLE', 'ON_HOLD'])) {
            abort(400, 'Booth tidak tersedia.');
        }

        $venueMapUrl = $event->venue_map_path ? Storage::disk('s3')->url($event->venue_map_path) : null;

        return view('client.orders.create', compact('event', 'booth', 'venueMapUrl'));
    }

    public function store(Request $request, Booth $booth)
    {
        $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'phone' => ['required', 'string', 'max:50'],
            'company_name' => ['required', 'string', 'max:255'],
        ]);

        if ($booth->status !== 'AVAILABLE') {
            return back()->withErrors(['booth' => 'Booth tidak tersedia untuk dipesan.']);
        }

        return DB::transaction(function () use ($request, $booth) {
            // Create order
            $order = new Order();
            $order->event_id = $booth->event_id;
            $order->customer_name = $request->customer_name;
            $order->email = $request->email;
            $order->phone = $request->phone;
            $order->company_name = $request->company_name;
            $order->invoice_number = 'INV-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
            $order->total_amount = $booth->base_price; // can be adjusted with zone multipliers
            $order->payment_method = 'MIDTRANS';
            $order->status = 'AWAITING_PAYMENT';
            $order->expires_at = now()->addMinutes(10);
            $order->save();

            // Link booth to order
            OrderItem::create([
                'order_id' => $order->id,
                'booth_id' => $booth->id,
                'price_snapshot' => $booth->base_price,
            ]);

            // Lock booth for 10 minutes
            $booth->status = 'ON_HOLD';
            $booth->expires_at = now()->addMinutes(10);
            $booth->save();

            // Send Invoice Email (queued or simple for now)
            // TODO: Implement mailable/template and queue later
            try {
                // dispatch(new SendInvoiceEmail($order)); // placeholder
            } catch (\Throwable $e) {
                // log email failure if necessary
            }

            return redirect()->route('client.payment.select', $order);
        });
    }

    public function show(Order $order)
    {
        $order->load(['items.booth', 'event']);
        return view('client.orders.show', compact('order'));
    }
}