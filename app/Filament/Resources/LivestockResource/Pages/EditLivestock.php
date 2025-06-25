<?php

namespace App\Filament\Resources\LivestockResource\Pages;

use App\Filament\Resources\LivestockResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLivestock extends EditRecord
{
    protected static string $resource = LivestockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
