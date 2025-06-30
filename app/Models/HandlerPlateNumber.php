<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HandlerPlateNumber extends Model
{
    use HasFactory, UsesUuid, SoftDeletes;
    protected $fillable = [
        'handler_id',
        'plate_no',
    ];


    public function handler()
    {
        return $this->belongsTo(Handler::class);
    }

    
}
