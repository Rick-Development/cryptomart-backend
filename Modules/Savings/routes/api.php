<?php

use Illuminate\Support\Facades\Route;
use Modules\Savings\Http\Controllers\SavingsController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('savings', SavingsController::class)->names('savings');
});
