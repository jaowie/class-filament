<?php

namespace App\Models;

use App\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmergencySlaughtered extends Model
{
    use HasFactory, UsesUuid, SoftDeletes;

    public function livestock()
    {
        return $this->belongsTo(Livestock::class, 'livestock_id');
    }
}
