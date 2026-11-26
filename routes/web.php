<?php

use Commisoft\FilamentComms\Http\Controllers\UnsubscribeController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'signed'])->prefix('comms')->name('filament-comms.')->group(function () {
    Route::get('preferences', [UnsubscribeController::class, 'show'])->name('preferences');
    Route::post('preferences', [UnsubscribeController::class, 'update'])->name('preferences.update');
    Route::post('unsubscribe-one-click', [UnsubscribeController::class, 'oneClick'])->name('one-click');
});
