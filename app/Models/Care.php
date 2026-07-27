<?php

namespace App\Models;

use App\Support\CareServiceType;
use App\Support\CareType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Care extends Model
{
    use HasFactory;

    protected $fillable=[
        'type','code','title',
    ];

    protected $casts = [
        'service' => CareServiceType::class,
    ];

    function scopeType($q,CareType $type)
    {
        return $q->where('type',$type->name);
    }
}
