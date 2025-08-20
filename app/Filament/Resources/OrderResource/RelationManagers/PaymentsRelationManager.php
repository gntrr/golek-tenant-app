<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\RelationManagers\RelationManager;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';
    protected static ?string $title = 'Metode Pembayaran';
    protected static ?string $icon = 'heroicon-o-credit-card';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('provider')->badge()->label('Metode'),
                Tables\Columns\TextColumn::make('status')->badge()->label('Status'),
                Tables\Columns\TextColumn::make('amount')->money('IDR', true)->label('Jumlah'),
                Tables\Columns\TextColumn::make('paid_at')->dateTime()->since()->toggleable(isToggledHiddenByDefault: true)->label('Dibayar Pada'),
                Tables\Columns\TextColumn::make('va_number')->label('VA')->toggleable(isToggledHiddenByDefault: true)->label('Nomor VA'),
                Tables\Columns\TextColumn::make('bank')->toggleable(isToggledHiddenByDefault: true)->label('Bank'),
                Tables\Columns\TextColumn::make('midtrans_txn_id')->label('Txn ID')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')->since()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                // Read-only: no create
            ])
            ->actions([
                // Read-only: no edit/delete
            ])
            ->bulkActions([
                // Read-only: no bulk actions
            ]);
    }
}
