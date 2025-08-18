<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventResource\Pages;
use App\Filament\Resources\EventResource\RelationManagers;
use App\Models\Event;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $navigationLabel = 'Kelola Event';

    public static function form(Form $form): Form
    {
        return $form->schema([
            // Create and Update Section
            Forms\Components\Section::make('Event Info')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Event name')
                        ->required()
                        ->maxLength(150),

                    Forms\Components\TextInput::make('description')
                        ->label('Description')
                        ->maxLength(200),

                    Forms\Components\DateTimePicker::make('starts_at')
                        ->label('Starts at')
                        ->required()
                        ->seconds(false),

                    Forms\Components\DateTimePicker::make('ends_at')
                        ->label('Ends at')
                        ->after('starts_at')
                        ->seconds(false),

                    Forms\Components\Toggle::make('is_active')
                        ->label('Active')
                        ->default(true)
                        ->inline(false),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Event')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->toggleable()
                    ->limit(30),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('starts_at')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('ends_at')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('zones_count')  // plural
                    ->label('Zones')
                    ->counts('zones')    // plural juga
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('booths_count')  // plural
                    ->label('Booths')
                    ->counts('booths')    // plural juga
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only')
                    ->queries(
                        true: fn ($q) => $q->where('is_active', true),
                        false: fn ($q) => $q->where('is_active', false),
                        blank: fn ($q) => $q
                    ),

                Tables\Filters\Filter::make('date_range')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('From'),
                        Forms\Components\DatePicker::make('to')->label('To'),
                    ])
                    ->query(function (Builder $q, array $data) {
                        return $q
                            ->when($data['from'] ?? null, fn ($qq, $from) => $qq->whereDate('starts_at', '>=', $from))
                            ->when($data['to'] ?? null, fn ($qq, $to) => $qq->whereDate('ends_at', '<=', $to));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ZonesRelationManager::class,
            RelationManagers\BoothsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEvents::route('/'),
            'create' => Pages\CreateEvent::route('/create'),
            'view' => Pages\ViewEvent::route('/{record}'),
            'edit' => Pages\EditEvent::route('/{record}/edit'),
        ];
    }
}
