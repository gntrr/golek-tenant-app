@extends('layouts.client')

@section('content')
<div class="flex flex-col gap-4">
    <div class="gap-2 mb-6">
        <div class="breadcrumbs text-sm">
            <ul>
                <li><a href="{{ route('client.events.index') }}">Events</a></li>
                <li><a href="{{ route('client.events.booths', $event) }}">{{ $event->name }} - Booths</a></li>
                <li>Pesan Booth {{ $booth->code }}</li>
            </ul>
        </div>
        <h2 class="text-2xl font-semibold">Form Pemesanan</h2>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <h2 class="card-title">Data Tenant</h2>
                    <p class="text-sm opacity-70 mb-4">Isi data berikut untuk melanjutkan pemesanan booth.</p>
                    <form method="POST" action="{{ route('client.book.booth.store', $booth) }}" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @csrf
                        <div class="md:col-span-2">
                            <label class="label"><span class="label-text">Nama</span></label>
                            <input type="text" name="customer_name" value="{{ old('customer_name') }}" class="input input-bordered w-full" required />
                            @error('customer_name')<div class="text-error text-sm mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label class="label"><span class="label-text">Email</span></label>
                            <input type="email" name="email" value="{{ old('email') }}" class="input input-bordered w-full" required />
                            @error('email')<div class="text-error text-sm mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label class="label"><span class="label-text">Nomor Telepon</span></label>
                            <input type="text" name="phone" value="{{ old('phone') }}" class="input input-bordered w-full" required />
                            @error('phone')<div class="text-error text-sm mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="md:col-span-2">
                            <label class="label"><span class="label-text">Company</span></label>
                            <input type="text" name="company_name" value="{{ old('company_name') }}" class="input input-bordered w-full" required />
                            @error('company_name')<div class="text-error text-sm mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="md:col-span-2 flex justify-end mt-2">
                            <button type="submit" class="btn btn-primary">Lanjutkan ke Pembayaran</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div>
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <h3 class="card-title">Ringkasan Pesanan</h3>
                    <p><span class="font-semibold">Event:</span> {{ $event->name }}</p>
                    <p><span class="font-semibold">Booth:</span> {{ $booth->code }}</p>
                    <p><span class="font-semibold">Zona:</span> {{ $booth->zone?->name ?? '-' }}</p>
                    <p><span class="font-semibold">Harga:</span> Rp {{ number_format($booth->base_price,0,',','.') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection