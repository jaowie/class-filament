<?php

namespace App\Filament\Resources\LivestockResource\Pages;

use App\Filament\Resources\LivestockResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLivestocks extends ListRecords
{
    protected static string $resource = LivestockResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
