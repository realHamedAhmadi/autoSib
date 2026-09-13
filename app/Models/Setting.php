<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable=['name','is_active'];

    public const MASTER_ID=1;

    public static function isActive()
    {
        return static::find(static::MASTER_ID)?->is_active;
    }
}
