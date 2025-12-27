<?php

use Illuminate\Support\Facades\Route;
use Modules\Frontend\Http\Controllers\FrontendController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('frontends', FrontendController::class)->names('frontend');
});
