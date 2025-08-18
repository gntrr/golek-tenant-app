<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Event;
use App\Models\Zone;
use App\Models\Booth;

class DemoEventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /*
        ======================================================
        EVENT 1
        ======================================================
        */
        $event1 = Event::create([
            'name' => 'Festival Kuliner Madiun 2025',
            'description' => 'Festival kuliner terbesar di Madiun. Tersedia tenant jenis VIP dan Festival.',
            'location' => 'GOR Wilis Madiun',
            'starts_at' => now()->addWeeks(2),
            'ends_at' => now()->addWeeks(3),
            'is_active' => true,
        ]);

        $vipZone1 = Zone::create([
            'event_id' => $event1->id,
            'name' => 'VIP Area',
            'color' => 'gold',
            'price_multiplier' => 1.5
        ]);

        $festivalZone1 = Zone::create([
            'event_id' => $event1->id,
            'name' => 'Festival Area',
            'color' => 'silver',
            'price_multiplier' => 1.2
        ]);

        Booth::insert([
            [
                'event_id' => $event1->id,
                'zone_id' => $vipZone1->id,
                'code' => 'VIP-1',
                'base_price' => 1500000,
                'status' => 'AVAILABLE',
            ],
            [
                'event_id' => $event1->id,
                'zone_id' => $vipZone1->id,
                'code' => 'VIP-2',
                'base_price' => 1500000,
                'status' => 'AVAILABLE',
            ],
            [
                'event_id' => $event1->id,
                'zone_id' => $festivalZone1->id,
                'code' => 'FST-1',
                'base_price' => 800000,
                'status' => 'AVAILABLE',
            ],
        ]);

        /*
        ======================================================
        EVENT 2
        ======================================================
        */
        $event2 = Event::create([
            'name' => 'Madiun Music Concert 2025',
            'description' => 'Konser musik terbesar di Madiun. Menampilkan berbagai artis lokal dan nasional. Tersedia tenant jenis Gold dan Silver.',
            'location' => 'Alun-Alun Kota Madiun',
            'starts_at' => now()->addMonths(1),
            'ends_at' => now()->addMonths(1)->addDays(2),
            'is_active' => true,
        ]);

        $goldZone = Zone::create([
            'event_id' => $event2->id,
            'name' => 'Gold Zone',
            'color' => 'gold',
            'price_multiplier' => 1.5
        ]);

        $silverZone = Zone::create([
            'event_id' => $event2->id,
            'name' => 'Silver Zone',
            'color' => 'silver',
            'price_multiplier' => 1.2
        ]);

        Booth::insert([
            [
                'event_id' => $event2->id,
                'zone_id' => $goldZone->id,
                'code' => 'GLD-1',
                'base_price' => 1200000,
                'status' => 'AVAILABLE',
            ],
            [
                'event_id' => $event2->id,
                'zone_id' => $silverZone->id,
                'code' => 'SLV-1',
                'base_price' => 600000,
                'status' => 'AVAILABLE',
            ],
            [
                'event_id' => $event2->id,
                'zone_id' => $silverZone->id,
                'code' => 'SLV-2',
                'base_price' => 600000,
                'status' => 'AVAILABLE',
            ],
        ]);
    }
}
