<?php
namespace App\Filament\Resources\LivestockResource\Pages;

use App\Models\Livestock;
use App\Models\Owner;
use Filament\Forms;
use Filament\Resources\Pages\Page;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use App\Filament\Resources\LivestockResource;

class ViewLivestockBatch extends Page implements HasTable
{
    use Tables\Concerns\InteractsWithTable;

  public $owner;
    public $batch;
    public $livestocks;

    protected static string $view = 'filament.resources.livestock-resource.pages.view-livestock-batch';
    protected static string $resource = LivestockResource::class;

    public function mount($owner, $batch)
    {
        $this->owner = \App\Models\Owner::where('uuid', $owner)->firstOrFail();
        $this->batch = $batch;

        $this->livestocks = \App\Models\Livestock::where('owner_id', $this->owner->id)
            ->where('batch', $batch)
            ->get();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Livestock::query()
                    ->where('owner_id', $this->owner->id)
                    ->where('batch', $this->batch)
            )
            ->columns([
                Tables\Columns\TextColumn::make('code'),
                Tables\Columns\TextColumn::make('status'),
                Tables\Columns\TextColumn::make('live_weight')->label('Live Weight'),
                Tables\Columns\TextColumn::make('time_slaughtered')->label('Time Slaughtered'),
                Tables\Columns\TextColumn::make('carcass_weight')->label('Carcass Weight'),
                Tables\Columns\TextColumn::make('time_dispatched')->label('Time Dispatched'),
                Tables\Columns\TextColumn::make('remarks')->label('Remarks'),

            ])
            ->actions([
                Tables\Actions\Action::make('edit')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil')
                        ->modalHeading(fn (Livestock $record) => $record->code)
                    ->form([
                        Forms\Components\TextInput::make('remarks'),
                        Forms\Components\TextInput::make('live_weight'),
                        Forms\Components\TextInput::make('time_slaughtered'),
                        Forms\Components\TextInput::make('carcass_weight'),
                        Forms\Components\TextInput::make('time_dispatched'),
                    ])
                    ->action(fn (array $data, Livestock $record) => $record->update($data)),
            ]);
    }

    public function getOwnerProperty()
    {
        return Owner::find($this->ownerId);
    }
}
