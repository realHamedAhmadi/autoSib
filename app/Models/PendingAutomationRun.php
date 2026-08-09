<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PendingAutomationRun extends Model
{
    use HasFactory;

    protected $fillable=[
        'user_id','automation_run_id',
    ];

    public function run()
    {
        return $this->belongsTo(AutomationRun::class);
    }
}
