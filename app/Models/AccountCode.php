<?php

namespace App\Models;

use App\Traits\UsesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountCode extends Model
{
    use HasFactory, UsesUuid, SoftDeletes;

    protected $fillable = ['account_code', 'description'];

}
