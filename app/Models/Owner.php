<?php

namespace App\Models;

use App\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Owner extends Model
{
    use HasFactory, UsesUuid, SoftDeletes;

    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'handler_id',
        'address',
    ];

    public function livestocks()
    {
        return $this->hasMany(Livestock::class);
    }

    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->middle_name} {$this->last_name}";
    }
}
