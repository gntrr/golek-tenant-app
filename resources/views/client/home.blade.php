@extends('layouts.client')

@section('content')
<div class="flex flex-col gap-8">
    <!-- Hero Section -->
    <section class="hero bg-base-200 rounded-xl p-8 md:p-12">
        <div class="hero-content flex-col lg:flex-row">
            <img
            src="{{ Storage::disk('s3')->url('logo.png') }}"
            class="max-w-sm"
            />
            <div>
                <h1 class="text-5xl font-bold">Temukan Booth Terbaik Dari Berbagai Event untuk Daganganmu!</h1>
                <p class="py-6">
                    Booking booth jadi mudah. Pilih event, lihat ketersediaan, dan amankan tempat Anda hanya dalam beberapa langkah.
                </p>
                <a href="{{ route('client.events.index') }}" class="btn btn-primary">Lihat Events</a>
            </div>
        </div>
    </section>

    <!-- Gallery 3 Active Events -->
    <section>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-semibold">Event Aktif</h2>
            <a href="{{ route('client.events.index') }}" class="btn btn-sm">Selengkapnya</a>
        </div>
        @if($events->isEmpty())
            <div class="alert">Belum ada event aktif.</div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($events as $event)
                    <div class="card bg-base-100 shadow">
                        @if($event->flyer_url)
                            <figure class="max-h-56 overflow-hidden"><img src="{{ $event->flyer_url }}" alt="{{ $event->name }}" class="w-full object-cover"/></figure>
                        @endif
                        <div class="card-body">
                            <h3 class="card-title">{{ $event->name }}</h3>
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
    </section>
</div>
@endsection
