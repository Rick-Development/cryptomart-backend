<?php

use Illuminate\Support\Facades\Route;
use Modules\Trade\Http\Controllers\TradeController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('trades', TradeController::class)->names('trade');
});
