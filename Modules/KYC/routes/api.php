<?php

use Illuminate\Support\Facades\Route;
use Modules\KYC\app\Http\Controllers\KYCController;

Route::middleware(['auth:api'])->prefix('kyc')->name('kyc.')->group(function () {
    Route::post('verify', [KYCController::class, 'initiate'])->name('verify');
    Route::get('status/{reference}', [KYCController::class, 'status'])->name('status');
});

Route::post('kyc/webhook', [KYCController::class, 'webhook'])->name('kyc.webhook');
