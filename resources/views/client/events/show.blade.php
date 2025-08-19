@extends('layouts.client')

@section('content')
<div class="flex flex-col gap-4">
    <div class="breadcrumbs text-sm">
        <ul>
            <li><a href="{{ route('client.events.index') }}">Events</a></li>
            <li>{{ $event->name }}</li>
        </ul>
    </div>

    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <h1 class="card-title text-3xl">{{ $event->name }}</h1>
            <p>{{ $event->description }}</p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                <div class="stat bg-base-200">
                    <div class="stat-title">Total Booth</div>
                    <div class="stat-value">{{ $boothStats['total'] }}</div>
                </div>
                <div class="stat bg-base-200">
                    <div class="stat-title">Available</div>
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

    @if($event->venue_map_url)
    <div class="card bg-base-100 shadow">
        <div class="card-body">
            <h2 class="card-title">Venue Map</h2>
            <img src="{{ $event->venue_map_url }}" alt="Venue Map" class="w-full rounded" />
        </div>
    </div>
    @endif
</div>
@endsection