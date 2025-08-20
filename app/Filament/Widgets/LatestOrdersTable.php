<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestOrdersTable extends BaseWidget
{
    protected static ?string $heading = 'Pesanan Terbaru';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::query()->latest('created_at')->limit(10)
            )
            ->columns([
                // TextColumn::make('created_at')->dateTime('d M Y H:i')->sortable()->label('Tanggal'),
                // TextColumn::make('invoice_number')->label('Invoice')->searchable(),
                TextColumn::make('customer_name')->label('Penyewa')->searchable(),
                TextColumn::make('total_amount')->money('IDR', true)->label('Total'),
                TextColumn::make('payment_method')->badge()->label('Metode'),
                TextColumn::make('status')->badge()->label('Status'),
            ])
            ->searchable(false)
            ->paginated(false);
    }
}
