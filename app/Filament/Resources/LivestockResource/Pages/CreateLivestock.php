<?php

namespace App\Filament\Resources\LivestockResource\Pages;

use App\Filament\CreateAndSkipStepOne;
use App\Filament\Resources\LivestockResource;
use App\Models\Delivery;
use App\Models\OwnerLivestockBatch;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Filament\Support\Facades\FilamentView;

class CreateLivestock extends CreateAndSkipStepOne
{
    protected static string $resource = LivestockResource::class;

    public function mount(): void
    {
        parent::mount();
    }

    protected function getFormActions(): array
    {
        return [];
    }
    
    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Livestock Created Successfully';
    }

    public function create(bool $another = false): void
    {
        try {
            $data = $this->form->getState();

            session([
                'handler_id' => $data['handler_id'],
                'handler_plate_number_id' => $data['handler_plate_number_id'],
            ]);

            $record = $this->handleRecordCreation($data);
            

            Notification::make()
                ->title('Livestock entries created successfully.')
                ->success()
                ->send();

    

            $this->redirect($this->getRedirectUrl());
        } catch (\Throwable $e) {
            report($e);

            Notification::make()
                ->title('Error creating livestock.')
                ->danger()
                ->body($e->getMessage())
                ->send();
        }
    }

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        return DB::transaction(function () use ($data) {
            $ownerId = $data['owner_id'];
            $handlerId = $data['handler_id'];
            $origin = $data['origin'];
            $dateOfDelivery = $data['date_of_delivery'];
            $timeOfDelivery = $data['time_of_delivery'];
            $livestockCodes = $data['livestock_codes'] ?? [];
            $livestockType = $data['type'];
            $remarks = $data['remarks'];
            $handlerPlateId = $data['handler_plate_number_id'];

            $delivery = Delivery::create([
                'handler_id' => $handlerId,
                'origin' => $origin,
                'delivered_at' => $dateOfDelivery,
                'time_delivered' => $timeOfDelivery,
            ]);

            $batch = null;
            $ownerLivestockBatch = null;
            
            $existingLivestockBatches = OwnerLivestockBatch::where('owner_id', $ownerId)->get();

            if ($existingLivestockBatches->isEmpty()) {
                $batch = 'batch1';
            } else {
                $maxBatchNumber = $existingLivestockBatches->max(function ($livestockBatch) {
                    if (preg_match('/batch(\d+)/i', $livestockBatch->batch, $matches)) {
                        return (int) $matches[1];
                    }
                    return 0;
                });
                $batch = 'batch' . ($maxBatchNumber + 1);
            }

            // Create the OwnerLivestockBatch record
            $ownerLivestockBatch = OwnerLivestockBatch::create([
                'owner_id' => $ownerId,
                'status' => 'received', 
                'batch' => $batch,
            ]);

            $createdLivestockRecords = collect();
            foreach ($livestockCodes as $code) {
                $livestock = $this->getModel()::create([
                    'type' => $livestockType,
                    'code' => $code,
                    'status' => 'received',
                    'remarks' => $remarks,
                    'owner_id' => $ownerId,
                    'batch' => $batch,
                    'handler_id' => $handlerId,
                    'delivery_id' => $delivery->id,
                    'handler_plate_number_id' => $handlerPlateId,
                ]);
                $createdLivestockRecords->push($livestock);
            }

            return $createdLivestockRecords->first() ?? new ($this->getModel());
        });
    }
    
}
