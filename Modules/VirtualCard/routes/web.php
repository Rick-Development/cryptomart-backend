<?php

use Illuminate\Support\Facades\Route;
use Modules\VirtualCard\Http\Controllers\VirtualCardController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('virtualcards', VirtualCardController::class)->names('virtualcard');
});
