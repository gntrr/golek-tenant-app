@extends('layouts.client')

@section('content')
<div class="max-w-xl mx-auto">
    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <h1 class="card-title text-2xl">Cek Status Pembayaran</h1>
            <p class="opacity-80">Masukkan nomor invoice untuk melihat status pembayaran pesanan Anda.</p>
            <form method="POST" action="{{ route('client.payment.check') }}" class="mt-4">
                @csrf
                <div class="form-control">
                    <label class="label"><span class="label-text">Nomor Invoice</span></label>
                    <input type="text" name="invoice" class="input input-bordered w-full" placeholder="contoh: INV-2025-000123" value="{{ old('invoice') }}" required />
                    @error('invoice')<div class="text-error text-sm mt-1">{{ $message }}</div>@enderror
                </div>
                <div class="mt-4 flex justify-end">
                    <button class="btn btn-primary">Lihat Status</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
