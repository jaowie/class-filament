<?php

namespace App\Filament\Resources\OrderOfPaymentResource\Pages;

use App\Filament\Resources\OrderOfPaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOrderOfPayments extends ListRecords
{
    protected static string $resource = OrderOfPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
