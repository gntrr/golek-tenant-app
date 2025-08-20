<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SettingResource\Pages;
use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-8-tooth';
    protected static ?string $navigationLabel = 'Pengaturan';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('group')
                ->default('payments')
                ->disabled()
                ->dehydrated(true),
            Forms\Components\Select::make('key')
                ->label('Jenis Pengaturan')
                ->options([
                    'midtrans_enabled' => 'Toggle Midtrans',
                    'bank_transfer_enabled' => 'Toggle Bank Transfer',
                    'fallback_banner' => 'Teks Fallback jika Midtrans OFF',
                    'bank_transfer_instructions' => 'Teks Instruksi Transfer Bank (Midtrans OFF)',
                ])
                ->disabled()
                ->required(),
            Forms\Components\Fieldset::make('Nilai')
                ->schema([
                    // Toggle untuk kunci boolean (langsung bind ke kolom 'value')
                    Forms\Components\Toggle::make('value')
                        ->label('Aktif?')
                        ->helperText('Untuk Toggle Midtrans / Bank Transfer')
                        ->reactive()
                        ->visible(fn ($get) => in_array($get('key'), ['midtrans_enabled','bank_transfer_enabled']))
                        ->afterStateHydrated(function ($component, $state, $record) {
                            if ($record && in_array($record->key, ['midtrans_enabled','bank_transfer_enabled'])) {
                                $component->state((string) $record->value === '1');
                            }
                        })
                        ->dehydrateStateUsing(fn ($state) => $state ? '1' : '0'),

                    // Textarea untuk kunci teks (juga bind ke 'value')
                    Forms\Components\Textarea::make('value')
                        ->label('Isi Teks')
                        ->rows(4)
                        ->visible(fn ($get) => in_array($get('key'), ['fallback_banner','bank_transfer_instructions']))
                        ->columnSpanFull(),
                ])->columns(1),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('group')
                    ->badge()
                    ->label('Group Pengaturan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('key')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'midtrans_enabled' => 'Pembayaran via Midtrans',
                        'bank_transfer_enabled' => 'Pembayaran via Transfer Bank',
                        'fallback_banner' => 'Teks Fallback jika Midtrans OFF',
                        'bank_transfer_instructions' => 'Teks Instruksi Transfer Bank (Jika Midtrans OFF)',
                        default => $state,
                    })
                    ->label('Pengaturan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('value')
                    ->label('Isi Pengaturan')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        '1' => 'ON',
                        '0' => 'OFF',
                        default => $state,
                    })
                    ->limit(80
                    )->toggleable(),
            ])
            ->filters([
                // optional filters
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Edit'),
            ])
            ->bulkActions([
                // no bulk delete
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageSettings::route('/'),
        ];
    }
}
