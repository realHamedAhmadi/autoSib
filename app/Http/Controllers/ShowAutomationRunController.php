<?php

namespace App\Http\Controllers;

use App\Models\AutomationRun;
use Illuminate\Http\JsonResponse;

class ShowAutomationRunController extends Controller
{
    public function __invoke(AutomationRun $automationRun): JsonResponse
    {
        $automationRun->load([
            'users.cares',
        ]);

        return response()->json($automationRun);
    }
}
