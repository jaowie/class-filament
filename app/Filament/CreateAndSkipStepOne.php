<?php

namespace App\Filament;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAndSkipStepOne extends CreateRecord
{
    protected function getRedirectUrl(): string
    {
        // return $this->getResource()::getUrl('create').'?step=2';
        return $this->getResource()::getUrl(name: 'create') . '?step=2';
    }
}