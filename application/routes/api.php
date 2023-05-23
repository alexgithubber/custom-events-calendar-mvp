<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::controller(AuthController::class)->group(function(){
    Route::post('users', 'store');
    Route::post('login', 'login');
});

Route::apiResource('events', EventController::class)->middleware('auth:sanctum');
Route::get('locations', [EventController::class, 'getLocations'])->middleware('auth:sanctum');
