<?php

namespace App\Http\Controllers;

use App\Models\AutomationRun;
use App\Services\AutomationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RetryAutomationRunController extends Controller
{
    public function retry(Request $request, AutomationRun $run, AutomationService $automationService): JsonResponse
    {
        try {
            $automationService->retry($run);

            return response()->json([
                'ok' => true,
                'message' => 'Run retried successfully.',
                'data' => [
                    'id' => $run->id,
                ],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'ok' => false,
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'ok' => false,
                'message' => 'Failed to retry automation run.',
            ], 500);
        }
    }
}
