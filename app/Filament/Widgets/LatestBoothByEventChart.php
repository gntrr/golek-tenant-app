<?php

namespace App\Filament\Widgets;

use App\Models\Booth;
use Filament\Widgets\ChartWidget;

class LatestBoothByEventChart extends ChartWidget
{
    protected static ?string $heading = 'Ketersediaan Booth per Event';

    protected function getData(): array
    {
        // Ambil jumlah booth Available per event
        $rows = Booth::query()
            ->selectRaw('event_id, SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as available, SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as on_hold, SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as booked', [
                Booth::STATUS_AVAILABLE, Booth::STATUS_ON_HOLD, Booth::STATUS_BOOKED,
            ])
            ->groupBy('event_id')
            ->with('event:id,name')
            ->get();

        $labels = $rows->map(fn($r) => optional($r->event)->name ?? ('Event #'.$r->event_id))->all();
        $available = $rows->pluck('available')->map(fn($v)=>(int)$v)->all();
        $onHold = $rows->pluck('on_hold')->map(fn($v)=>(int)$v)->all();
        $booked = $rows->pluck('booked')->map(fn($v)=>(int)$v)->all();

        return [
            'datasets' => [
                [
                    'label' => 'Available',
                    'data' => $available,
                    'backgroundColor' => '#10b981',
                ],
                [
                    'label' => 'On Hold',
                    'data' => $onHold,
                    'backgroundColor' => '#f59e0b',
                ],
                [
                    'label' => 'Booked',
                    'data' => $booked,
                    'backgroundColor' => '#3b82f6',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
