<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\RelationManagers\RelationManager;

class PaymentProofsRelationManager extends RelationManager
{
    protected static string $relationship = 'paymentProofs';
    protected static ?string $title = 'Bukti Pembayaran';
    protected static ?string $icon = 'heroicon-o-photo';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('file_path')->label('File'),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('reviewer.name')->label('Reviewer')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('reviewed_at')->dateTime()->since()->toggleable(isToggledHiddenByDefault: true),
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
