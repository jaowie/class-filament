<?php

namespace App\Models;

use App\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Livestock extends Model
{
    use HasFactory, UsesUuid, SoftDeletes;

    
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'cause',
        'status',
        'type',
        'live_weight',
        'time_slaughtered',
        'carcass_weight',
        'time_dispatched',
        'meat_inspection_no',
        'remarks',
        'batch',
        'order_of_payment_id',
        'code',
        'owner_id',
        'delivery_id',
        'handler_id',
        'handler_plate_number_id',
    ];

    public function handler(): BelongsTo
    {
        return $this->belongsTo(Handler::class);
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(Inspector::class);
    }

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(Delivery::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(Owner::class);
    }

    public function condemned()
    {
        return $this->hasOne(CondemnedLivestock::class, 'livestock_id')->latest();
    }

    public function emergencySlaughtered()
    {
        return $this->hasOne(EmergencySlaughtered::class, 'livestock_id');
    }

    public function ownerLivestockBatch()
    {
        return $this->belongsTo(OwnerLivestockBatch::class, 'batch', 'batch');
    }

    public function plateNumber()
    {
        return $this->belongsTo(HandlerPlateNumber::class, 'handler_plate_number_id');
    }
}
