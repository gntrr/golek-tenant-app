<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::where('is_active', true)
            // ->where('starts_at', '>', now())
            ->orderBy('starts_at', 'asc')
            ->get()
            ->map(function ($event) {
                $event->flyer_url = $event->flyer_path ? Storage::disk('s3')->url($event->flyer_path) : null;
                return $event;
            });

        return view('client.events.index', compact('events'));
    }

    public function show(Event $event)
    {
        // Check if event is active and not expired
        if (!$event->is_active || $event->ends_at < now()) {
            abort(404, 'Event tidak ditemukan atau sudah berakhir.');
        }

        // Get event with flyer URL
        $event->flyer_url = $event->flyer_path ? Storage::disk('s3')->url($event->flyer_path) : null;
        $event->venue_map_url = $event->venue_map_path ? Storage::disk('s3')->url($event->venue_map_path) : null;

        // Get zones for this event
        $zones = $event->zones()->get();

        // Get booth counts by status
        $boothStats = [
            'total' => $event->booths()->count(),
            'available' => $event->booths()->where('status', 'AVAILABLE')->count(),
            'on_hold' => $event->booths()->where('status', 'ON_HOLD')->count(),
            'booked' => $event->booths()->where('status', 'BOOKED')->count(),
        ];

        return view('client.events.show', compact('event', 'zones', 'boothStats'));
    }
}