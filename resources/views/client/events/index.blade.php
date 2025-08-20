@extends('layouts.client')

@section('content')
<div class="gap-2 mb-6">
    <h1 class="text-2xl font-bold">Event Terkini</h1>
    <p class="text-md opacity-70">Lihat semua event yang sedang berlangsung dan temukan booth yang sesuai untuk kebutuhan Anda.</p>
</div>

@if(session('status'))
<div class="alert alert-info mb-4">{{ session('status') }}</div>
@endif

@if($events->isEmpty())
    <div class="alert">Tidak ada event aktif saat ini.</div>
@else
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
    @foreach($events as $event)
        <div class="card bg-base-100 shadow">
            @if($event->flyer_url)
                <figure class="max-h-56 overflow-hidden"><img src="{{ $event->flyer_url }}" alt="{{ $event->name }}" class="w-full object-cover"/></figure>
            @endif
            <div class="card-body">
                <h2 class="card-title">{{ $event->name }}</h2>
                <p class="text-sm opacity-70">{{ $event->location }}</p>
                <p class="text-sm">{{ $event->starts_at->format('d M Y') }} - {{ $event->ends_at->format('d M Y') }}</p>
                <div class="card-actions justify-end">
                    <a href="{{ route('client.events.show', $event) }}" class="btn btn-primary">Detail</a>
                    <a href="{{ route('client.events.booths', $event) }}" class="btn">Lihat Booth</a>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endif
@endsection