@extends('layouts.client')

@section('content')
<div class="flex flex-col gap-4">
    <div class="gap-2 mb-4">
        <div class="breadcrumbs text-sm">
            <ul>
                <li><a href="{{ route('client.events.index') }}">Events</a></li>
                <li>{{ $event->name }}</li>
            </ul>
        </div>
        <!-- Heading Text -->
        <h2 class="text-2xl font-semibold">Detail Event</h2>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            @if($event->flyer_url)
                <div class="card bg-base-100 shadow">
                    <figure class="p-4">
                        <img src="{{ $event->flyer_url }}" alt="{{ $event->name }}" class="w-full max-h-[70vh] object-contain rounded" />
                    </figure>
                </div>
            @endif
        </div>
        <div>
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <h1 class="card-title text-3xl">{{ $event->name }}</h1>
                    <div class="mt-2">
                        <p class="opacity-80">Lokasi: {{ $event->location }}</p>
                        <p class="opacity-80">Tanggal: {{ $event->starts_at->format('d M Y') }} - {{ $event->ends_at->format('d M Y') }}</p>
                    </div>
                    <div class="mt-2">
                        <p class="card-title text-lg">Tentang Event:</p>
                        <p class="opacity-80">{{ $event->description }}</p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                        <div class="stat bg-base-200">
                            <div class="stat-title">Total Booth</div>
                            <div class="stat-value">{{ $boothStats['total'] }}</div>
                        </div>
                        <div class="stat bg-base-200">
                            <div class="stat-title">Tersedia</div>
                            <div class="stat-value text-success">{{ $boothStats['available'] }}</div>
                        </div>
                        <div class="stat bg-base-200">
                            <div class="stat-title">On-Hold</div>
                            <div class="stat-value text-warning">{{ $boothStats['on_hold'] }}</div>
                        </div>
                    </div>
                    <div class="mt-4 flex gap-2">
                        <a href="{{ route('client.events.booths', $event) }}" class="btn btn-primary">Lihat Booth</a>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</div>
@endsection