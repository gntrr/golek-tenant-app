<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Invoice {{ $order->invoice_number }}</title>
</head>
<body style="font-family: Arial, sans-serif; color: #111;">
    <h2>Invoice {{ $order->invoice_number }}</h2>
    <p>Yth. {{ $order->customer_name }},</p>
    <p>Terima kasih telah melakukan pemesanan booth untuk event <strong>{{ $order->event->name }}</strong>.</p>

    <table cellpadding="6" cellspacing="0" border="0" style="border-collapse: collapse;">
        <tr>
            <td>Invoice</td><td>:</td><td>{{ $order->invoice_number }}</td>
        </tr>
        <tr>
            <td>Tanggal</td><td>:</td><td>{{ $order->created_at->format('d M Y H:i') }}</td>
        </tr>
        <tr>
            <td>Total</td><td>:</td><td>Rp {{ number_format($order->total_amount,0,',','.') }}</td>
        </tr>
        <tr>
            <td>Status</td><td>:</td><td>{{ $order->status }}</td>
        </tr>
    </table>

    <h3 style="margin-top: 16px;">Detail Booth</h3>
    <ul>
        @foreach($order->items as $item)
            <li>Booth {{ $item->booth->code }} — Rp {{ number_format($item->price_snapshot,0,',','.') }}</li>
        @endforeach
    </ul>

    <p>Untuk melanjutkan pembayaran, silakan buka halaman berikut:</p>
    <p>
        <a href="{{ route('client.payment.select', $order) }}">Lanjut ke pembayaran</a>
    </p>

    <p>Hormat kami,<br/>Golek Tenant</p>
</body>
</html>
