<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class OrderItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';
    protected static ?string $title = 'Order Items';
    protected static ?string $recordTitleAttribute = 'booth.code';
    protected static ?string $icon = 'heroicon-o-queue-list';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('booth_id')
                ->relationship('booth', 'code')
                ->searchable()->preload()->required(),
            Forms\Components\TextInput::make('price_snapshot')
                ->numeric()->minValue(0)->prefix('IDR')->required(),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table->columns([
                Tables\Columns\TextColumn::make('booth.code')->label('Booth')->badge(),
                Tables\Columns\TextColumn::make('price_snapshot')->label('Price')->money('IDR', true),
                Tables\Columns\TextColumn::make('created_at')->since()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([ Tables\Actions\CreateAction::make() ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->requiresConfirmation(),
                ]),
            ]);
    }
}
