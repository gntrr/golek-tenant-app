<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventResource\Pages;
use App\Filament\Resources\EventResource\RelationManagers;
use App\Models\Event;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

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
                    
                    Forms\Components\Toggle::make('is_active')
                        ->label('Tampilkan Event')
                        ->helperText('Tandai jika event ini aktif dan akan ditampilkan di halaman utama')
                        ->default(true)
                        ->inline(false),
                    
                    Forms\Components\FileUpload::make('flyer_path')
                        ->label('Upload Pamflet Event')
                        ->image()
                        ->acceptedFileTypes(['image/jpeg','image/png', 'image/jpg'])
                        ->imageEditor() // optional crop/rotate
                        // ->disk('s3')
                        ->maxSize(2 * 1024 * 1024) // 2MB
                        // ->directory('events/flyers')
                        // ->visibility('public')      // penting: file publik
                        // ->preserveFilenames(false)  // pakai hash biar unik
                        ->saveUploadedFileUsing(function (TemporaryUploadedFile $file) {
                            $name = 'flyer_' . now()->format('Ymd_His') . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                            $path = "events/flyers/{$name}";

                            // Upload manual dari file temp → S3
                            Storage::disk('s3')->put($path, file_get_contents($file->getRealPath()));

                            return $path; // <-- disimpan ke kolom flyer_path
                        })
                        ->deleteUploadedFileUsing(function (?string $path) {
                            if ($path) Storage::disk('s3')->delete($path);
                        })
                        ->helperText('Gunakan rasio 16:9 atau 4:3, max ukuran 2MB, format .jpg atau .png.'),

                    Forms\Components\FileUpload::make('venue_map_path')
                        ->label('Upload Denah Booth')
                        ->acceptedFileTypes(['image/jpeg','image/png', 'image/jpg'])
                        ->image()
                        ->imageEditor() // optional crop/rotate
                        // ->disk('s3')
                        ->maxSize(5 * 1024 * 1024) // 5MB
                        // ->directory('events/venue-maps')
                        // ->visibility('public')
                        // ->preserveFilenames(false)
                        ->saveUploadedFileUsing(function (TemporaryUploadedFile $file) {
                            $name = 'map_' . now()->format('Ymd_His') . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                            $path = "events/venue-maps/{$name}";

                            Storage::disk('s3')->put($path, file_get_contents($file->getRealPath()));

                            return $path;
                        })
                        ->deleteUploadedFileUsing(function (?string $path) {
                            if ($path) Storage::disk('s3')->delete($path);
                        })
                        ->helperText('Gunakan rasio 4:3 atau 1:1, max ukuran 5MB, format .jpg atau .png.'),

                    Forms\Components\DateTimePicker::make('starts_at')
                        ->label('Tanggal Mulai')
                        ->required()
                        ->seconds(false),

                    Forms\Components\DateTimePicker::make('ends_at')
                        ->label('Tanggal Selesai')
                        ->after('starts_at')
                        ->seconds(false),

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

                Tables\Columns\TextColumn::make('flyer_path')
                    ->label('Pamflet')
                    ->formatStateUsing(fn ($state) => $state ? Storage::disk('s3')->url($state) : '-')
                    ->limit(40)
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('venue_map_path')
                    ->label('Denah Booth')
                    ->formatStateUsing(fn ($state) => $state ? Storage::disk('s3')->url($state) : '-')
                    ->limit(40)
                    ->copyable()
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
