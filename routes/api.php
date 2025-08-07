<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\PlayerController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\ItemController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/players', [PlayerController::class, 'index']);
    Route::get('/players/{player}/stats', [PlayerController::class, 'stats']);
    Route::get('/leaderboard', [PlayerController::class, 'leaderboard']);

    Route::get('/events', [EventController::class, 'index']);

    Route::get('/items/top', [ItemController::class, 'getTopItems']);
});

require __DIR__ . '/api_auth.php';