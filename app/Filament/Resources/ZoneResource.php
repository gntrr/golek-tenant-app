<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ZoneResource\Pages;
use App\Filament\Resources\ZoneResource\RelationManagers;
use App\Models\Zone;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ZoneResource extends Resource
{
    protected static ?string $model = Zone::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';
    protected static ?string $navigationLabel = 'Kelola Zona';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Zona Booth')
                ->columns(3)
                ->schema([
                    Forms\Components\Select::make('event_id')
                        ->relationship('event', 'name')
                        ->label('Pilih Event')
                        ->searchable()
                        ->preload()
                        ->required(),

                    Forms\Components\TextInput::make('name')
                        ->label('Nama Zona')
                        ->helperText('Masukkan nama zona, misal: Reguler, VIP, dll.')
                        ->required()
                        ->maxLength(60),

                    // Forms\Components\ColorPicker::make('color')
                    //     ->label('Warna')
                    //     ->nullable(),

                    Forms\Components\TextInput::make('price_multiplier')
                        ->label('Kelipatan Harga')
                        ->helperText('Masukkan kelipatan harga untuk zona ini. Misal: 1.5 untuk 150% dari harga dasar')
                        ->numeric()
                        ->minValue(0.1)
                        ->maxValue(9.99)
                        ->step('0.01')
                        ->default(1.00),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Zona')
                    ->searchable()
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('event.name')
                    ->label('Event')
                    ->badge()
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(),

                // Tables\Columns\ColorColumn::make('color')
                //     ->label('Warna')
                //     ->sortable()
                //     ->toggleable(),

                Tables\Columns\TextColumn::make('price_multiplier')
                    ->label('Kelipatan Harga')
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('booths_count')
                    ->label('Jumlah Booth')
                    ->counts('booths')
                    ->badge()
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->since()
                    ->label('Dibuat Pada')
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('event_id')
                    ->label('Event')
                    ->relationship('event', 'name')
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListZones::route('/'),
            'create' => Pages\CreateZone::route('/create'),
            'view' => Pages\ViewZone::route('/{record}'),
            'edit' => Pages\EditZone::route('/{record}/edit'),
        ];
    }
}
