<?php

namespace App\Filament;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAndCreateAnother extends CreateRecord
{
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('create');
    }
}