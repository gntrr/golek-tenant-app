@extends('layouts.client')

@section('content')
<div class="flex flex-col gap-4">
    <div class="breadcrumbs text-sm">
        <ul>
            <li><a href="{{ route('client.events.index') }}">Events</a></li>
            <li><a href="{{ route('client.events.show', $event) }}">{{ $event->name }}</a></li>
            <li>Booths</li>
        </ul>
    </div>

    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <h1 class="card-title text-2xl">Booths for {{ $event->name }}</h1>
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">
                <div>
                    <label class="label"><span class="label-text">Zona</span></label>
                    <select name="zone_id" class="select select-bordered w-full">
                        <option value="">Semua Zona</option>
                        @foreach($zones as $z)
                            <option value="{{ $z->id }}" @selected(request('zone_id') == $z->id)>{{ $z->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label"><span class="label-text">Status</span></label>
                    <select name="status" class="select select-bordered w-full">
                        <option value="">Semua Status</option>
                        <option value="AVAILABLE" @selected(request('status')=='AVAILABLE')>Available</option>
                        <option value="ON_HOLD" @selected(request('status')=='ON_HOLD')>On-Hold</option>
                        <option value="BOOKED" @selected(request('status')=='BOOKED')>Booked</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button class="btn btn-primary w-full">Filter</button>
                </div>
            </form>
        </div>
    </div>

    @if($venueMapUrl)
    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <h2 class="card-title">Venue Map</h2>
            <img src="{{ $venueMapUrl }}" alt="Venue Map" class="w-full rounded" />
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($booths as $booth)
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <div class="flex justify-between items-center">
                        <h3 class="card-title">Booth {{ $booth->code }}</h3>
                        <div class="badge {{ $booth->status === 'AVAILABLE' ? 'badge-success' : ($booth->status === 'ON_HOLD' ? 'badge-warning' : 'badge-neutral') }}">
                            {{ ucfirst(strtolower(str_replace('_',' ',$booth->status))) }}
                        </div>
                    </div>
                    <p class="text-sm">Zona: {{ $booth->zone?->name ?? '-' }}</p>
                    <p class="text-sm font-semibold">Harga: Rp {{ number_format($booth->base_price,0,',','.') }}</p>
                    <div class="card-actions justify-end">
                        @if($booth->status === 'AVAILABLE')
                            <a href="{{ route('client.book.booth', $booth) }}" class="btn btn-primary">Pesan</a>
                        @else
                            <button class="btn" disabled>Tidak Tersedia</button>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-6">{{ $booths->withQueryString()->links() }}</div>
</div>
@endsection