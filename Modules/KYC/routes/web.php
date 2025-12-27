<?php

use Illuminate\Support\Facades\Route;
use Modules\KYC\app\Http\Controllers\KYCController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('kycs', KYCController::class)->names('kyc');
});
