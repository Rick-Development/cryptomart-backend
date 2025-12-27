<?php

use Illuminate\Support\Facades\Route;
use Modules\P2P\Http\Controllers\P2PController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('p2ps', P2PController::class)->names('p2p');
});
