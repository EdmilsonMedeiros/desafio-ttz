<?php

use App\Http\Controllers\ImportController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::post('import', [ImportController::class, 'import']);
    Route::get('imported-files', [ImportController::class, 'getImportedFiles']);
    Route::delete('imported-files/{id}', [ImportController::class, 'deleteImportedFile']);
    Route::get('download-file/{id}', [ImportController::class, 'downloadFile']);

    Route::get('players', [\App\Http\Controllers\Api\PlayerController::class, 'index']);
    Route::get('events', [\App\Http\Controllers\Api\EventController::class, 'index']);
    Route::get('items', [\App\Http\Controllers\Api\ItemController::class, 'getTopItems']);
    Route::get('leaderboard', [\App\Http\Controllers\Api\PlayerController::class, 'leaderboard']);

    Route::get('players/{player}/stats', [\App\Http\Controllers\Api\PlayerController::class, 'stats']);
    Route::get('most-collected-items', [\App\Http\Controllers\Api\ItemController::class, 'getMostCollectedItems']);
    Route::get('most-killed-bosses', [\App\Http\Controllers\Api\PlayerController::class, 'getMostKilledBosses']);
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
