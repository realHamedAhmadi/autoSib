<?php

namespace App\Models;

use App\Support\CareType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AllowedCare extends Model
{
    use HasFactory;

    protected $fillable=[
        'user_id','type'
    ];

    function scopeType($q,CareType $type)
    {
        return $q->where('type',$type->name);
    }
}
