<?php

use App\Http\Controllers\SpotController;
use Illuminate\Support\Facades\Route;

Route::get('/', [SpotController::class, 'home'])->name('home');

Route::middleware('auth')->group(function () {
    Route::redirect('dashboard', '/spots')->name('dashboard');

    Route::resource('spots', SpotController::class)
        ->only(['index', 'create', 'store', 'destroy']);
});

require __DIR__.'/settings.php';
