<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Booth;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BoothController extends Controller
{
    public function index(Event $event, Request $request)
    {
        if (!$event->is_active || $event->ends_at < now()) {
            abort(404, 'Event tidak ditemukan atau sudah berakhir.');
        }

        // Auto-release expired ON_HOLD booths
        Booth::where('event_id', $event->id)
            ->where('status', 'ON_HOLD')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->update(['status' => 'AVAILABLE', 'expires_at' => null]);
        
        $query = Booth::with(['zone'])
            ->where('event_id', $event->id);

        // Filter by zone
        if ($request->filled('zone_id')) {
            $query->where('zone_id', $request->integer('zone_id'));
        }

        // Filter by status
        if ($request->filled('status')) {
            $status = strtoupper($request->get('status'));
            if (in_array($status, ['AVAILABLE', 'ON_HOLD', 'BOOKED'])) {
                $query->where('status', $status);
            }
        }

        $booths = $query->orderBy('code')->paginate(20);

        // Add venue map url for this event
        $venueMapUrl = $event->venue_map_path ? Storage::disk('s3')->url($event->venue_map_path) : null;

        // zones list for filters
        $zones = $event->zones()->get(['id','name']);

        return view('client.booths.index', compact('event', 'booths', 'zones', 'venueMapUrl'));
    }
}