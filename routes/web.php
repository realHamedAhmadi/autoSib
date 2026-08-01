<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\System\ProjectUpdaterController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ResumeAutomationRunUserController;
use App\Http\Controllers\ShowAutomationRunController;
use App\Http\Controllers\StartAutomationRunController;


Route::middleware('guest')->group(function (){
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

});

Route::middleware('role-pending')->group(function (){
    Route::get('/role',[\App\Http\Controllers\RoleController::class,'getRole'])->name('get.role');
    Route::post('/role',[\App\Http\Controllers\RoleController::class,'setRole'])->name('set.role');
});
Route::middleware('auth')->group(function (){
    Route::get('/', [\App\Http\Controllers\DashboardController::class,'index'])->name('dashboard');
   Route::resource('family-env-health',\App\Http\Controllers\FamilyEnvHealthController::class);
   Route::resource('diabetic',\App\Http\Controllers\DiabeticController::class);
   Route::resource('hyper-tension',\App\Http\Controllers\HyperTensionController::class);
   Route::resource('young',\App\Http\Controllers\HyperTensionController::class);

    Route::post('/automation-runs', StartAutomationRunController::class)->name('start.automation');
    Route::get('/automation-runs/{automationRun}', ShowAutomationRunController::class)->name('show.automation');
    Route::post('/automation-run-users/{runUser}/resume', ResumeAutomationRunUserController::class)->name('resume.automation');

});

Route::prefix('__system')->group(function () {
    Route::get('/updater', [ProjectUpdaterController::class, 'index'])
        ->name('system.updater.index');

    Route::post('/update-from-github', [ProjectUpdaterController::class, 'update'])
        ->name('system.updater.update');
});
