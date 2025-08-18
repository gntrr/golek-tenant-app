<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Filament\Resources\OrderResource\RelationManagers\OrderItemsRelationManager;
use App\Filament\Resources\OrderResource\RelationManagers\PaymentsRelationManager;
use App\Filament\Resources\OrderResource\RelationManagers\PaymentProofsRelationManager;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Kelola Pesanan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('Invoice')->searchable()->sortable()->alignCenter(),

                Tables\Columns\TextColumn::make('event.name')
                    ->label('Event')->badge()->sortable()->alignCenter(),

                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Nama Penyewa')->searchable()->alignCenter(),

                // Tables\Columns\TextColumn::make('email')
                //     ->searchable()->toggleable()->alignCenter(),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')->money('IDR', true)->sortable()
                    ->summarize(Tables\Columns\Summarizers\Sum::make())
                    ->alignCenter(),

                Tables\Columns\BadgeColumn::make('payment_method')
                    ->label('Metode Pembayaran')
                    ->colors(['primary'])
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => Order::STATUS_PENDING,
                        'info'    => Order::STATUS_AWAITING,
                        'success' => Order::STATUS_PAID,
                        'danger'  => Order::STATUS_CANCELLED,
                        'secondary' => Order::STATUS_EXPIRED,
                    ])
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Dibuat')
                    ->alignCenter()
                    ->dateTime('d M Y H:i')->sortable()
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('event_id')
                    ->label('Event')
                    ->relationship('event', 'name')->preload(),

                Tables\Filters\SelectFilter::make('payment_method')
                    ->label('Metode Pembayaran')
                    ->options([
                        Order::METHOD_MIDTRANS => 'MIDTRANS',
                        Order::METHOD_BANK     => 'Transfer Bank',
                    ]),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        Order::STATUS_PENDING   => 'Pending',
                        Order::STATUS_AWAITING  => 'Menunggu Pembayaran',
                        Order::STATUS_PAID      => 'Lunas',
                        Order::STATUS_EXPIRED   => 'Kadaluarsa',
                        Order::STATUS_CANCELLED => 'Dibatalkan',
                    ]),

                Tables\Filters\Filter::make('date_range')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('to')->label('Sampai Tanggal'),
                    ])
                    ->query(fn (Builder $q, array $data) =>
                        $q->when($data['from'] ?? null, fn ($qq, $from) => $qq->whereDate('created_at', '>=', $from))
                          ->when($data['to']   ?? null, fn ($qq, $to)   => $qq->whereDate('created_at', '<=', $to))
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Lihat'),
                // Tables\Actions\EditAction::make()
                //     ->label('Ubah'),
                // Tables\Actions\DeleteAction::make()
                //     ->label('Hapus')
                //     ->requiresConfirmation(),
                
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),

                    Tables\Actions\BulkAction::make('markPaid')
                        ->label('Tandai Lunas')
                        ->color('success')
                        ->visible(fn (Order $r) => $r->status !== Order::STATUS_PAID)
                        ->requiresConfirmation()
                        ->action(fn (Order $r) => $r->update(['status' => Order::STATUS_PAID])),

                    Tables\Actions\BulkAction::make('markPending')
                        ->label('Tandai Pending')
                        ->color('warning')
                        ->visible(fn (Order $r) => $r->status !== Order::STATUS_PENDING)
                        ->requiresConfirmation()
                        ->action(fn (Order $r) => $r->update(['status' => Order::STATUS_PENDING])),

                    Tables\Actions\BulkAction::make('cancelOrder')
                        ->label('Cancel')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->visible(fn (Order $r) => $r->status !== Order::STATUS_CANCELLED)
                        ->action(fn (Order $r) => $r->update(['status' => Order::STATUS_CANCELLED])),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
