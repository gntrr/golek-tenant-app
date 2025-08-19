@extends('layouts.client')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <h2 class="card-title">Ringkasan Pesanan</h2>
            <p>Invoice: <span class="font-semibold">{{ $order->invoice_number }}</span></p>
            <p>Status: <span class="badge">{{ $order->status }}</span></p>
            <p>Total: <span class="font-semibold">Rp {{ number_format($order->total_amount,0,',','.') }}</span></p>

            <div class="mt-4">
                <h3 class="font-semibold">Detail Pemesan</h3>
                <p>Nama: {{ $order->customer_name }}</p>
                <p>Email: {{ $order->email }}</p>
                <p>Telepon: {{ $order->phone }}</p>
                <p>Perusahaan: {{ $order->company_name }}</p>
            </div>

            <div class="mt-4">
                <h3 class="font-semibold">Booth</h3>
                @foreach($order->items as $item)
                    <div class="border rounded p-3 mt-2">
                        <div>Booth: {{ $item->booth?->code ?? '-' }}</div>
                        <div>Zona: {{ $item->booth?->zone?->name ?? '-' }}</div>
                        <div>Harga: Rp {{ number_format($item->price_snapshot,0,',','.') }}</div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6 flex gap-2 justify-end">
                <a href="{{ route('client.payment.select', $order) }}" class="btn btn-primary">Lanjutkan ke Pembayaran</a>
            </div>
        </div>
    </div>
</div>
@endsection