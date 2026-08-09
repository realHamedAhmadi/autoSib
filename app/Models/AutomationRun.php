<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AutomationRun extends Model
{
    protected $fillable = [
        'user_id',
        'status',
        'total_users',
        'processed_users',
        'total_cares',
        'processed_cares',
        'input',
        'result',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'input' => 'array',
        'result' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(AutomationRunUser::class);
    }

    public function pending()
    {
        return $this->hasOne(PendingAutomationRun::class);
    }

}
