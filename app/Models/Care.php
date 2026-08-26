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
        'id','type','code','title','service'
    ];

    protected $casts = [
        'service' => CareServiceType::class,
    ];

    function scopeType($q,CareType $type)
    {
        return $q->where('type',$type->name);
    }
    function scopeTypeIn($q,array $types)
    {
        $t=[];
        foreach ($types as $type){
            $t[]=$type->name;
        }
        return $q->whereIn('type',$t);
    }

    function automationCares()
    {
        return $this->hasMany(AutomationRunUserCare::class);
    }
}
