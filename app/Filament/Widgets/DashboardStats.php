<?php

namespace App\Filament\Widgets;

use App\Models\Booth;
use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStats extends BaseWidget
{
    protected function getStats(): array
    {
        $totalBooth = Booth::query()->count();
        $available  = Booth::query()->where('status', Booth::STATUS_AVAILABLE)->count();
        // $onHold     = Booth::query()->where('status', Booth::STATUS_ON_HOLD)->count();
        $booked     = Booth::query()->where('status', Booth::STATUS_BOOKED)->count();

        $paid       = Order::query()->where('status', Order::STATUS_PAID)->count();
        // $pending    = Order::query()->where('status', Order::STATUS_PENDING)->count();
        // $expired    = Order::query()->where('status', Order::STATUS_EXPIRED)->count();

        return [
            Stat::make('Total Booth', (string) $totalBooth),
            Stat::make('Booth yang Tersedia', (string) $available),
            // Stat::make('Booth yang ditahan', (string) $onHold),
            Stat::make('Booth yang Dipesan', (string) $booked),
            Stat::make('Order yang Dibayar', (string) $paid),
            // Stat::make('Order yang Menunggu', (string) $pending),
            // Stat::make('Order yang Kadaluarsa', (string) $expired),
        ];
    }
}
