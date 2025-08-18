<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Event;
use App\Models\Booth;
use App\Models\Order;
use App\Models\OrderItem;

class DemoOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ambil 6 booth AVAILABLE untuk jaga2 (3 order x 1 booth)
        $booths = Booth::where('status', 'AVAILABLE')
            ->orderBy('event_id')
            ->limit(6)
            ->get();

        if ($booths->count() < 3) {
            $this->command?->warn('Booth AVAILABLE kurang dari 3. Seed lebih banyak booth dulu.');
            return;
        }

        // helper bikin invoice unik
        $makeInvoice = fn() => 'INV-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));

        // data customer sample
        $customers = [
            ['name' => 'Budi Santoso',  'email' => 'budi@example.com',  'phone' => '081234567801', 'company' => 'Warung Budi'],
            ['name' => 'Sari Wulandari','email' => 'sari@example.com',  'phone' => '081234567802', 'company' => 'Kopi Sari'],
            ['name' => 'Dimas Rahman',  'email' => 'dimas@example.com', 'phone' => '081234567803', 'company' => 'Bakso Dimas'],
        ];

        // bikin 3 order (2 paid, 1 pending)
        foreach ([0, 1, 2] as $i) {
            DB::transaction(function () use ($booths, $i, $customers, $makeInvoice) {
                /** @var \App\Models\Booth $booth */
                $booth = $booths[$i];

                // create order
                $order = Order::create([
                    'event_id'       => $booth->event_id,
                    'customer_name'  => $customers[$i]['name'],
                    'email'          => $customers[$i]['email'],
                    'phone'          => $customers[$i]['phone'],
                    'company_name'   => $customers[$i]['company'],
                    'invoice_number' => $makeInvoice(),
                    'total_amount'   => $booth->base_price,
                    'payment_method' => 'MIDTRANS',
                    'status'         => $i < 2 ? 'PAID' : 'PENDING', // 2 PAID, 1 PENDING
                    'expires_at'     => null,
                ]);

                // create order_item (snapshot harga saat ini)
                OrderItem::create([
                    'order_id'      => $order->id,
                    'booth_id'      => $booth->id,
                    'price_snapshot'=> $booth->base_price,
                ]);

                // update booth jadi BOOKED kalau order sudah paid; pending boleh tetap BOOKED untuk simulasi "sudah dibayar" 2 order pertama
                $booth->update([
                    'status'     => $i < 2 ? 'BOOKED' : 'ON_HOLD', // yang pending kita hold saja biar realistis
                    'expires_at' => $i < 2 ? null : now()->addMinutes(10),
                ]);
            });
        }
    }
}
