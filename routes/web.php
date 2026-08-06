<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AutomationMonitoringController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DiabeticController;
use App\Http\Controllers\FamilyEnvHealthController;
use App\Http\Controllers\HyperTensionController;
use App\Http\Controllers\MiddleAgedController;
use App\Http\Controllers\RetryAutomationRunController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\System\ProjectUpdaterController;
use App\Http\Controllers\YoungCaresController;
use Illuminate\Support\Facades\Route;


Route::middleware('guest')->group(function (){
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

});

Route::middleware('role-pending')->group(function (){
    Route::get('/role',[RoleController::class,'getRole'])->name('get.role');
    Route::post('/role',[RoleController::class,'setRole'])->name('set.role');
});
Route::middleware('auth')->group(function (){
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/', [DashboardController::class,'index'])->name('dashboard');
    Route::get('/dashboard/poll', [DashboardController::class, 'poll'])->name('dashboard.poll');
    Route::resource('family-env-health',FamilyEnvHealthController::class);
   Route::resource('diabetic',DiabeticController::class);
   Route::resource('hyper-tension',HyperTensionController::class);
   Route::resource('young',YoungCaresController::class);
   Route::resource('middle-aged',MiddleAgedController::class);

    Route::post('/automation/runs/{run}/retry', [RetryAutomationRunController::class, 'retry'])
        ->name('automation.runs.retry');
    Route::get('/automation/runs', [AutomationMonitoringController::class, 'index'])->name('automation.runs.index');
    Route::get('/automation/runs/{run}', [AutomationMonitoringController::class, 'show'])->name('automation.runs.show');
    Route::get('/automation/runs/{run}/status', [AutomationMonitoringController::class, 'statusApi'])->name('automation.runs.status');
    Route::get('/automation/users/{user}/cares', [AutomationMonitoringController::class, 'userCaresApi'])->name('automation.users.cares');
});

Route::prefix('__system')->group(function () {
    Route::get('/updater', [ProjectUpdaterController::class, 'index'])
        ->name('system.updater.index');

    Route::post('/update-from-github', [ProjectUpdaterController::class, 'update'])
        ->name('system.updater.update');
});
