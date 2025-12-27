<?php

use Illuminate\Support\Facades\Route;
use Modules\AppConfiguration\Http\Controllers\AppConfigurationController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('appconfigurations', AppConfigurationController::class)->names('appconfiguration');
});
