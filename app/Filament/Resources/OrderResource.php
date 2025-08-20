<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Filament\Resources\OrderResource\RelationManagers\OrderItemsRelationManager;
use App\Filament\Resources\OrderResource\RelationManagers\PaymentsRelationManager;
use App\Filament\Resources\OrderResource\RelationManagers\PaymentProofsRelationManager;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentProof;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Illuminate\Support\Facades\DB;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReceiptMail;
use App\Models\EmailLog;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Kelola Pesanan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pesanan')
                    ->schema([
                        Forms\Components\TextInput::make('invoice_number')->label('Invoice')->disabled(),
                        Forms\Components\TextInput::make('customer_name')->label('Nama Penyewa')->disabled(),
                        Forms\Components\TextInput::make('email')->label('Email')->disabled(),
                        Forms\Components\TextInput::make('phone')->label('Telepon')->disabled(),
                        Forms\Components\Select::make('event_id')->label('Event')->relationship('event', 'name')->disabled(),
                        Forms\Components\TextInput::make('total_amount')->label('Total')->disabled()->prefix('IDR'),
                        Forms\Components\TextInput::make('payment_method')->label('Metode Pembayaran')->disabled(),
                        Forms\Components\TextInput::make('status')->label('Status')->disabled(),
                        Forms\Components\DateTimePicker::make('created_at')->label('Dibuat Pada')->disabled(),
                    ])->columns(2),
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
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('Total'))
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
                    // Tables\Actions\DeleteBulkAction::make()
                    //   ->label('Hapus Pesanan Terpilih')
                    //   ->requiresConfirmation(),

                    Tables\Actions\BulkAction::make('markPaid')
                        ->label('Tandai Lunas')
                        ->color('success')
                        ->icon('heroicon-o-check')
                        ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $paidOrderIds = [];
                            DB::transaction(function () use ($records, &$paidOrderIds) {
                                $count = 0;
                                foreach ($records as $order) {
                                    /** @var Order $order */
                                    if ($order->status !== Order::STATUS_PAID) {
                                        $order->update(['status' => Order::STATUS_PAID]);

                                        // Pastikan ada Payment untuk order ini (berdasarkan metode pembayaran bila ada)
                                        $provider = $order->payment_method === Order::METHOD_BANK
                                            ? Payment::PROVIDER_BANK
                                            : Payment::PROVIDER_MIDTRANS;
                                        Payment::firstOrCreate(
                                            ['order_id' => $order->id, 'provider' => $provider],
                                            ['amount' => $order->total_amount, 'status' => Payment::STATUS_PENDING]
                                        );

                                        // Tandai semua payment order ini sebagai SETTLEMENT dan beri paid_at
                                        Payment::where('order_id', $order->id)
                                            ->update(['status' => Payment::STATUS_SETTLEMENT, 'paid_at' => now()]);

                                        // Jika via transfer bank, setujui semua bukti pembayaran
                                        if ($order->payment_method === Order::METHOD_BANK) {
                                            PaymentProof::where('order_id', $order->id)
                                                ->update([
                                                    'status' => PaymentProof::STATUS_APPROVED,
                                                    'reviewed_by' => auth()->id(),
                                                    'reviewed_at' => now(),
                                                ]);
                                        }

                                        // Update status booth menjadi BOOKED
                                        $order->loadMissing('items.booth');
                                        foreach ($order->items as $item) {
                                            if ($item->booth) {
                                                $item->booth->update(['status' => 'BOOKED', 'expires_at' => null]);
                                            }
                                        }

                                        // Kumpulkan untuk kirim email setelah commit
                                        $paidOrderIds[] = $order->id;
                                        $count++;
                                    }
                                }
                                Notification::make()
                                    ->title('Pesanan Diperbarui')
                                    ->body("{$count} pesanan ditandai sebagai LUNAS.")
                                    ->success()
                                    ->send();
                            });

                            // Kirim email receipt setelah transaksi DB selesai
                            foreach ($paidOrderIds as $id) {
                                $fresh = Order::with(['items.booth','event'])->find($id);
                                if (!$fresh) continue;
                                try {
                                    Mail::to($fresh->email)->send(new ReceiptMail($fresh));
                                    EmailLog::create([
                                        'to_email' => $fresh->email,
                                        'subject' => 'Receipt Pembayaran '.$fresh->invoice_number,
                                        'template' => 'receipt',
                                        'status' => 'SENT',
                                    ]);
                                } catch (\Throwable $e) {
                                    EmailLog::create([
                                        'to_email' => $fresh->email,
                                        'subject' => 'Receipt Pembayaran '.$fresh->invoice_number,
                                        'template' => 'receipt',
                                        'status' => 'FAILED',
                                        'error' => $e->getMessage(),
                                    ]);
                                }
                            }
                        }),

                    Tables\Actions\BulkAction::make('markPending')
                        ->label('Tandai Menunggu')
                        ->color('warning')
                        ->icon('heroicon-o-clock')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(function ($records) {
                            DB::transaction(function () use ($records) {
                                $count = 0;
                                foreach ($records as $order) {
                                    /** @var Order $order */
                                    if ($order->status !== Order::STATUS_PENDING) {
                                        $order->update(['status' => Order::STATUS_PENDING]);

                                        // Tandai semua payment menjadi PENDING dan hapus paid_at
                                        Payment::where('order_id', $order->id)
                                            ->update(['status' => Payment::STATUS_PENDING, 'paid_at' => null]);

                                        // Jika via transfer bank, kembalikan bukti jadi PENDING dan hapus review
                                        if ($order->payment_method === Order::METHOD_BANK) {
                                            PaymentProof::where('order_id', $order->id)
                                                ->update([
                                                    'status' => PaymentProof::STATUS_PENDING,
                                                    'reviewed_by' => null,
                                                    'reviewed_at' => null,
                                                ]);
                                        }

                                        // Kembalikan booth ke ON_HOLD (masih dalam antrian pembayaran)
                                        $order->loadMissing('items.booth');
                                        foreach ($order->items as $item) {
                                            if ($item->booth) {
                                                $item->booth->update(['status' => 'ON_HOLD']);
                                            }
                                        }
                                        $count++;
                                    }
                                }
                                Notification::make()
                                    ->title('Pesanan Ditandai')
                                    ->body("{$count} order ditandai sebagai MENUNGGU.")
                                    ->success()
                                    ->send();
                            });
                        }),

                    Tables\Actions\BulkAction::make('cancelOrder')
                        ->label('Tandai Dibatalkan')
                        ->color('danger')
                        ->icon('heroicon-o-x-mark')
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion()
                        ->action(function ($records) {
                            DB::transaction(function () use ($records) {
                                $count = 0;
                                foreach ($records as $order) {
                                    /** @var Order $order */
                                    if ($order->status !== Order::STATUS_CANCELLED) {
                                        $order->update(['status' => Order::STATUS_CANCELLED]);

                                        // Tandai semua payment menjadi CANCEL dan hapus paid_at
                                        Payment::where('order_id', $order->id)
                                            ->update(['status' => Payment::STATUS_CANCEL, 'paid_at' => null]);

                                        // Jika via transfer bank, tolak semua bukti pembayaran
                                        if ($order->payment_method === Order::METHOD_BANK) {
                                            PaymentProof::where('order_id', $order->id)
                                                ->update([
                                                    'status' => PaymentProof::STATUS_REJECTED,
                                                    'reviewed_by' => auth()->id(),
                                                    'reviewed_at' => now(),
                                                ]);
                                        }

                                        // Kembalikan booth menjadi AVAILABLE
                                        $order->loadMissing('items.booth');
                                        foreach ($order->items as $item) {
                                            if ($item->booth) {
                                                $item->booth->update(['status' => 'AVAILABLE', 'expires_at' => null]);
                                            }
                                        }
                                        $count++;
                                    }
                                }
                                Notification::make()
                                    ->title('Pesanan Dibatalkan')
                                    ->body("{$count} order ditandai sebagai DIBATALKAN.")
                                    ->success()
                                    ->send();
                            });
                        }),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            OrderItemsRelationManager::class,
            PaymentsRelationManager::class,
            PaymentProofsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            // 'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            // Hilangkan halaman edit agar resource read-only (edit status tetap via bulk action di index)
        ];
    }
}
