<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AutomationRunUser extends Model
{
    protected $fillable = [
        'automation_run_id',
        'sib_user_id',
        'sib_user_name',
        'status',
        'total_cares',
        'processed_cares',
        'current_care_id',
        'payload',
        'result',
        'error_message',
        'last_attempt_at',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'result' => 'array',
        'last_attempt_at' => 'datetime',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function run(): BelongsTo
    {
        return $this->belongsTo(AutomationRun::class, 'automation_run_id');
    }

    public function cares(): HasMany
    {
        return $this->hasMany(AutomationRunUserCare::class)->orderBy('sort_order');
    }

    public function currentCare(): BelongsTo
    {
        return $this->belongsTo(AutomationRunUserCare::class, 'current_care_id');
    }
}
