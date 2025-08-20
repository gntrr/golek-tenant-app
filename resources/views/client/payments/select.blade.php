@extends('layouts.client')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <h1 class="card-title">Metode Pembayaran</h1>
            <p class="opacity-80 mb-6">Pilih metode pembayaran yang Anda inginkan untuk menyelesaikan pesanan.</p>
            <p>Invoice: <span class="font-semibold">{{ $order->invoice_number }}</span></p>
            <p>Total: <span class="font-semibold">Rp {{ number_format($order->total_amount,0,',','.') }}</span></p>

            @if($fallbackBanner)
                <div class="alert alert-warning mt-2">{!! nl2br(e($fallbackBanner)) !!}</div>
            @endif

            <div class="grid grid-cols-1 gap-4 mt-4">
                @if($midtransEnabled)
                <form method="POST" action="{{ route('client.payment.midtrans', $order) }}">
                    @csrf
                    <button class="btn btn-primary w-full">Bayar via Midtrans</button>
                </form>
                @endif

                @if($bankTransferEnabled)
                <a href="{{ route('client.payment.upload.form', $order) }}" class="btn w-full">Upload Bukti Transfer</a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection