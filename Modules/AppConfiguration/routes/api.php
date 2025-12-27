<?php

use Illuminate\Support\Facades\Route;
use Modules\AppConfiguration\Http\Controllers\AppConfigurationController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('appconfigurations', AppConfigurationController::class)->names('appconfiguration');
});
