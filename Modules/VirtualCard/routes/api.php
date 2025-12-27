<?php

use Illuminate\Support\Facades\Route;
use Modules\VirtualCard\Http\Controllers\VirtualCardController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('virtualcards', VirtualCardController::class)->names('virtualcard');
});
