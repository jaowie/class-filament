<?php

namespace App\Models;

use App\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderOfPayment extends Model
{
    use HasFactory, UsesUuid, SoftDeletes;

    protected $fillable = [
        'owner_id',
        'owner_livestock_batch_id',
        'batch',
        'account_codes',
        'encoded_by',
        'order_of_payment_no',
        'status',
        'amount',
        'remarks',
        'purpose',
    ];

    public function livestocks()
    {
        return $this->hasMany(Livestock::class, 'order_of_payment_id');
    }

    public function owner()
    {
        return $this->belongsTo(Owner::class);
    }

    public function encoder()
    {
        return $this->belongsTo(User::class, 'encoded_by');
    }
}
