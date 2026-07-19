<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

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
});
