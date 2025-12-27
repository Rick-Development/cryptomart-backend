<?php

use Illuminate\Support\Facades\Route;
use Modules\Payscribe\Http\Controllers\PayscribeController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('payscribes', PayscribeController::class)->names('payscribe');
});
