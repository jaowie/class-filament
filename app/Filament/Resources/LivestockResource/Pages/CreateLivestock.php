<?php

namespace App\Filament\Resources\LivestockResource\Pages;

use App\Filament\CreateAndCreateAnother;
use App\Filament\Resources\LivestockResource;
use App\Models\Delivery;
use App\Models\OwnerLivestockBatch;
use Illuminate\Support\Facades\DB;

class CreateLivestock extends CreateAndCreateAnother
{
    protected static string $resource = LivestockResource::class;

    protected function getFormActions(): array
    {
        return [];
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Livestock Created Successfully';
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

            $delivery = Delivery::create([
                'handler_id' => $handlerId,
                'origin' => $origin,
                'delivered_at' => $dateOfDelivery,
                'time_delivered' => $timeOfDelivery,
            ]);

            $batch = null;
            $ownerLivestockBatch = null;

            // Check for existing livestock batches for this owner
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

            $createdLivestockRecords = collect(); // Collection to hold created livestock models
            foreach ($livestockCodes as $code) {
                $livestock = $this->getModel()::create([
                    'type' => $livestockType,
                    'code' => $code,
                    'status' => 'received', // Default status for new livestock entries
                    'remarks' => $remarks,
                    'owner_id' => $ownerId,
                    'batch' => $batch, // Link to the determined batch
                    'handler_id' => $handlerId,
                    'delivery_id' => $delivery->id,
                ]);
                $createdLivestockRecords->push($livestock);
            }

            // Filament's CreateRecord expects an Eloquent model back.
            // Return the first created livestock record or a new instance if none were created.
            return $createdLivestockRecords->first() ?? new ($this->getModel());
        });
    }
    
}
