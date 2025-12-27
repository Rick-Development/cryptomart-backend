<?php

use Illuminate\Support\Facades\Route;
use Modules\Savings\Http\Controllers\SavingsController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('savings', SavingsController::class)->names('savings');
});
