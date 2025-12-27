<?php

use Illuminate\Support\Facades\Route;
use Modules\Collection\Http\Controllers\CollectionController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('collections', CollectionController::class)->names('collection');
});
