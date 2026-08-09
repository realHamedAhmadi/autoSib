<?php

namespace App\Listeners;

use App\Jobs\ProcessAutomationRunUserJob;
use App\Models\PendingAutomationRun;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class PendingAutomationRunListener
{
    /**
     * Create the event listener.
     */
    public function __construct(
        protected $userId
    )
    {

    }

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {

    }
}
