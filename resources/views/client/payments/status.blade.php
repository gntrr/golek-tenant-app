@extends('layouts.client')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <h2 class="card-title">Status Pesanan</h2>
            <p>Invoice: <span class="font-semibold">{{ $order->invoice_number }}</span></p>
            <p>Status Order: <span class="badge">{{ $order->status }}</span></p>
            <p>Total: <span class="font-semibold">Rp {{ number_format($order->total_amount,0,',','.') }}</span></p>

            <div class="mt-4">
                <h3 class="font-semibold">Pembayaran</h3>
                @forelse($order->payments as $p)
                    <div class="border rounded p-3 mt-2">
                        <div class="flex justify-between">
                            <div>Provider: {{ $p->provider }}</div>
                            <div class="badge">{{ $p->status }}</div>
                        </div>
                        <div>Amount: Rp {{ number_format($p->amount,0,',','.') }}</div>
                        @if($p->paid_at)
                            <div>Dibayar pada: {{ $p->paid_at->format('d M Y H:i') }}</div>
                        @endif
                    </div>
                @empty
                    <div class="alert">Belum ada data pembayaran.</div>
                @endforelse
            </div>

            <div class="mt-4 flex gap-2">
                <a href="{{ route('client.payment.select', $order) }}" class="btn">Pilih/ubah metode pembayaran</a>
            </div>
        </div>
    </div>
</div>
@endsection