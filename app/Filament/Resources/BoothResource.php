<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BoothResource\Pages;
use App\Filament\Resources\BoothResource\RelationManagers;
use App\Models\Booth;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Forms\Set;
use Filament\Forms\Get;
use Filament\Tables;
use Illuminate\Validation\Rule;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BoothResource extends Resource
{
    protected static ?string $model = Booth::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?string $navigationLabel = 'Kelola Booth';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Booth')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('event_id')
                        ->label('Pilih Event')
                        ->helperText('Pilih event yang akan memiliki booth ini')
                        ->relationship('event', 'name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->afterStateUpdated(fn (Set $set) => $set('zone_id', null)) // reset zone ketika event berubah
                        ->reactive(),

                    Forms\Components\Select::make('zone_id')
                        ->label('Pilih Zona')
                        ->helperText('Pilih zona yang sesuai untuk booth ini')
                        ->relationship('zone', 'name', fn ($query, $get) =>
                            $query->where('event_id', $get('event_id'))
                        )
                        ->searchable()
                        ->preload()
                        ->disabled(fn (Get $get) => blank($get('event_id'))) // <<< kunci di sini
                        ->required(),

                    /* === CREATE: multiple codes === */
                    Forms\Components\Textarea::make('codes')
                        ->label('Kode Booth')
                        ->visibleOn('create')                  // hanya saat create
                        ->rows(3)
                        ->placeholder("Contoh:\nVIP-A1, VIP-A2\nFEST-B1\nFEST-B2")
                        ->helperText('Pisahkan dengan koma atau baris baru. Unik per event.')
                        ->required(),

                    /* === EDIT: single code === */
                    Forms\Components\TextInput::make('code')
                        ->label('Kode Booth')
                        ->visibleOn('edit')                    // hanya saat edit
                        ->required()
                        ->maxLength(30)
                        ->rules(function (callable $get, ?\App\Models\Booth $record) {
                            $eventId = $get('event_id');
                            $ignoreId = $record?->id ?? null;
                            return [
                                Rule::unique('booths', 'code')
                                    ->where(fn ($q) => $q->where('event_id', $eventId))
                                    ->ignore($ignoreId),
                            ];
                        })
                        ->helperText('Kode unik per event (contoh: VIP-A1, FEST-B2).'),

                    Forms\Components\TextInput::make('base_price')
                        ->label('Harga Dasar')
                        ->helperText('Masukkan harga dasar untuk booth ini')
                        ->numeric()
                        ->minValue(0)
                        ->required()
                        ->prefix('IDR'),

                    Forms\Components\Select::make('status')
                        ->options([
                            'AVAILABLE' => 'Tersedia',
                            'ON_HOLD'   => 'Ditahan',
                            'BOOKED'    => 'Dipesan',
                            'DISABLED'  => 'Dinonaktifkan',
                        ])
                        ->native(false)
                        ->label('Status Booth')
                        ->helperText('Pilih status booth saat ini')
                        ->required()
                        ->reactive(),

                    Forms\Components\DateTimePicker::make('expires_at')
                        ->label('Berakhir Pada')
                        ->helperText('Tanggal dan waktu berakhirnya pemesanan')
                        ->seconds(false)
                        ->visible(fn (callable $get) => $get('status') === 'ON_HOLD')
                        ->rule('nullable|date'),

                    // Forms\Components\TextInput::make('row')
                    //     ->numeric()->minValue(0)->maxValue(65535)
                    //     ->nullable()
                    //     ->helperText('Opsional, untuk koordinat peta denah.'),

                    // Forms\Components\TextInput::make('col')
                    //     ->numeric()->minValue(0)->maxValue(65535)
                    //     ->nullable()
                    //     ->helperText('Opsional, untuk koordinat peta denah.'),
                ]),
            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Kode Booth')
                    ->alignCenter()
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('event.name')
                    ->label('Event')
                    ->alignCenter()
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('zone.name')
                    ->label('Zona')
                    ->alignCenter()
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('base_price')
                    ->label('Harga Dasar')
                    ->alignCenter()
                    ->money('IDR', true)
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success'   => 'AVAILABLE',
                        'warning'   => 'ON_HOLD',
                        'danger'    => 'BOOKED',
                        'gray' => 'DISABLED',
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'AVAILABLE' => 'Tersedia',
                        'ON_HOLD'   => 'Ditahan',
                        'BOOKED'    => 'Dipesan',
                        'DISABLED'  => 'Dinonaktifkan',
                    })
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Berakhir Pada')
                    ->alignCenter()
                    ->dateTime('d M Y H:i')
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('event_id')
                    ->label('Event')
                    ->relationship('event', 'name')
                    ->preload(),

                Tables\Filters\SelectFilter::make('zone_id')
                    ->label('Zona')
                    ->relationship('zone', 'name')
                    ->preload(),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'AVAILABLE' => 'Tersedia',
                        'ON_HOLD'   => 'Ditahan',
                        'BOOKED'    => 'Dipesan',
                        'DISABLED'  => 'Dinonaktifkan',
                    ]),
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
                        ->label('Hapus Booth Terpilih')
                        ->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('setAvailable')
                        ->label('Aktifkan Booth ke Publik')
                        ->icon('heroicon-o-check')
                        ->action(fn ($records) => $records->each->update(['status' => 'AVAILABLE', 'expires_at' => null])),
                    Tables\Actions\BulkAction::make('setDisabled')
                        ->label('Nonaktifkan Booth')
                        ->icon('heroicon-o-x-mark')
                        ->action(fn ($records) => $records->each->update(['status' => 'DISABLED', 'expires_at' => null])),
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
            'index' => Pages\ListBooths::route('/'),
            'create' => Pages\CreateBooth::route('/create'),
            'view' => Pages\ViewBooth::route('/{record}'),
            'edit' => Pages\EditBooth::route('/{record}/edit'),
        ];
    }
}
