<?php

namespace App\Filament\Resources;

use App\Models\WebhookLog;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WebhookLogResource extends Resource
{
    protected static ?string $model = WebhookLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-bell-alert';
    protected static ?string $navigationLabel = 'Log Sistem';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('provider')->badge()->sortable()->searchable(),
                Tables\Columns\TextColumn::make('event')->searchable(),
                Tables\Columns\IconColumn::make('processed')->boolean(),
                Tables\Columns\TextColumn::make('processed_at')->since()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')->since()->sortable(),
            ])
            ->actions([
                // Read-only: no view/edit/delete actions
            ])
            ->bulkActions([
                // Read-only: no bulk actions
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => WebhookLogResource\Pages\ListWebhookLogs::route('/'),
        ];
    }
}
