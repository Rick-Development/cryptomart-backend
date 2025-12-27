<?php

use Illuminate\Support\Facades\Route;
use Modules\Payscribe\Http\Controllers\PayscribeController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('payscribes', PayscribeController::class)->names('payscribe');
});
