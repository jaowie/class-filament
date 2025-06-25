<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OwnerLivestockBatch extends Model
{
    
    protected $fillable = [
        'owner_id',
        'status',
        'batch',
    ];

    public function owner()
    {
        return $this->belongsTo(Owner::class);
    }

    public function livestocks()
    {
        return $this->hasMany(Livestock::class, 'batch', 'batch');
    }


}
