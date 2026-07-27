<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutomationRunUserCare extends Model
{
    protected $fillable = [
        'automation_run_user_id',
        'care_id',
        'sort_order',
        'status',
        'attempts',
        'payload',
        'result',
        'checkpoint',
        'error_message',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'result' => 'array',
        'checkpoint' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function runUser(): BelongsTo
    {
        return $this->belongsTo(AutomationRunUser::class, 'automation_run_user_id');
    }

    public function care()
    {
        return $this->belongsTo(Care::class);
    }
}
