@extends('layouts.client')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <h2 class="card-title">Upload Bukti Transfer</h2>
            <p>Invoice: <span class="font-semibold">{{ $order->invoice_number }}</span></p>
            <p>Total: <span class="font-semibold">Rp {{ number_format($order->total_amount,0,',','.') }}</span></p>

            @if($fallbackBanner)
                <div class="alert alert-warning mt-2">{!! nl2br(e($fallbackBanner)) !!}</div>
            @endif

            @if($instructions)
                <div class="alert mt-2">{!! nl2br(e($instructions)) !!}</div>
            @endif

            <form method="POST" action="{{ route('client.payment.upload.store', $order) }}" enctype="multipart/form-data" class="mt-4">
                @csrf
                <input type="file" name="proof" class="file-input file-input-bordered w-full" accept="image/png,image/jpeg,application/pdf" required />
                @error('proof')<div class="text-error text-sm mt-1">{{ $message }}</div>@enderror
                <div class="mt-4 flex justify-end">
                    <button class="btn btn-primary">Kirim</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection