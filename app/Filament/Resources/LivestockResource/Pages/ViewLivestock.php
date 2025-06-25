<?php
namespace App\Filament\Resources\LivestockResource\Pages;

use App\Models\Livestock;
use App\Models\Owner;
use Filament\Resources\Pages\Page;
use App\Filament\Resources\LivestockResource;

class ViewLivestock extends Page
{

    protected static string $resource = LivestockResource::class;

    protected static string $view = 'filament.resources.livestock-resource.pages.view-livestock';

    public $owner;
    public $batch;
    public $livestocks;

public function mount($owner, $batch)
{
    $this->owner = \App\Models\Owner::where('uuid', $owner)->firstOrFail();
    $this->batch = $batch;

    $this->livestocks = \App\Models\Livestock::where('owner_id', $this->owner->id)
        ->where('batch', $batch)
        ->get();
}
}
