<?php

namespace App\Models;

use App\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Delivery extends Model
{
    use HasFactory, UsesUuid, SoftDeletes;

    protected $fillable = [
        'handler_id',
        'origin',
        'delivered_at',
        'time_delivered',
    ];
}
