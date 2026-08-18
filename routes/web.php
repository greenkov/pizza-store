<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\PizzaPresetController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [PizzaPresetController::class, 'index'])->name('dashboard');
});

Route::middleware(['auth'])->group(function () {
    Route::get('pizza_presets', [PizzaPresetController::class, 'adminIndex'])->name('pizza-presets.index-admin');
    Route::get('pizza_presets/create', [PizzaPresetController::class, 'create'])->name('pizza-presets.create');
    Route::post('pizza_presets', [PizzaPresetController::class, 'store'])->name('pizza-presets.store');
    Route::get('pizza_presets/{pizza_preset}', [PizzaPresetController::class, 'show'])->name('pizza-presets.show');
    Route::get('pizza_presets/{pizza_preset}/edit', [PizzaPresetController::class, 'edit'])->name('pizza-presets.edit');
    Route::patch('pizza_presets/{pizza_preset}', [PizzaPresetController::class, 'update'])->name('pizza-presets.update');
    Route::delete('pizza_presets/{pizza_preset}', [PizzaPresetController::class, 'destroy'])->name('pizza-presets.destroy');

    Route::group(['prefix' => 'cart'], function () {
        Route::post('/add', [CartController::class, 'store'])->name('card.store');
        Route::delete('/remove/{id}', [CartController::class, 'destroy'])->name('card.destroy');
    });
});

require __DIR__.'/settings.php';
