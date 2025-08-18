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
            Forms\Components\Section::make('Informasi Tentang Event')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nama Event')
                        ->helperText('Masukkan nama event')
                        ->required()
                        ->maxLength(150),

                    Forms\Components\TextInput::make('description')
                        ->label('Deskripsi')
                        ->helperText('Jelaskan tentang event nya, beritahu juga jenis booth yang tersedia')
                        ->maxLength(200),

                    Forms\Components\TextInput::make('location')
                        ->label('Lokasi')
                        ->helperText('Masukkan lokasi event yang akan diadakan')
                        ->required()
                        ->maxLength(150),

                    Forms\Components\DateTimePicker::make('starts_at')
                        ->label('Tanggal Mulai')
                        ->required()
                        ->seconds(false),

                    Forms\Components\DateTimePicker::make('ends_at')
                        ->label('Tanggal Selesai')
                        ->after('starts_at')
                        ->seconds(false),

                    Forms\Components\Toggle::make('is_active')
                        ->label('Tampilkan Event')
                        ->helperText('Tandai jika event ini aktif dan akan ditampilkan di halaman utama')
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
                    ->alignCenter()
                    ->sortable(),
                    
                // Tables\Columns\TextColumn::make('description')
                //     ->toggleable()
                //     ->limit(30),

                Tables\Columns\TextColumn::make('location')
                    ->label('Lokasi')
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Dapat Dipesan')
                    ->boolean()
                    ->alignCenter()
                    ->sortable(),

                // Tables\Columns\TextColumn::make('starts_at')
                //     ->dateTime('d M Y, H:i')
                //     ->sortable(),

                // Tables\Columns\TextColumn::make('ends_at')
                //     ->dateTime('d M Y, H:i')
                //     ->sortable()
                //     ->toggleable(),

                Tables\Columns\TextColumn::make('zones_count')  // plural
                    ->label('Jumlah Zona')
                    ->counts('zones')    // plural juga
                    ->badge()
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('booths_count')  // plural
                    ->label('Jumlah Booth')
                    ->counts('booths')    // plural juga
                    ->badge()
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->alignCenter()
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
                Tables\Actions\ViewAction::make()
                    ->label('Lihat'),
                Tables\Actions\EditAction::make()
                    ->label('Ubah'),
                Tables\Actions\DeleteAction::make()
                    ->label('Hapus')
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus Event Terpilih')
                        ->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('setActive')
                        ->label('Tampilkan Event ke Publik')
                        ->icon('heroicon-o-check')
                        ->action(fn ($records) => $records->each->update(['is_active' => true])),
                    Tables\Actions\BulkAction::make('setInactive')
                        ->label('Nonaktifkan Event')
                        ->icon('heroicon-o-x-mark')
                        ->action(fn ($records) => $records->each->update(['is_active' => false])),
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
