<?php

namespace App\Filament\Resources\BoothResource\Pages;

use App\Filament\Resources\BoothResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBooth extends EditRecord
{
    protected static string $resource = BoothResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
