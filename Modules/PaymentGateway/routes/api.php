<?php

use Illuminate\Support\Facades\Route;
use Modules\PaymentGateway\Http\Controllers\PaymentGatewayController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('paymentgateways', PaymentGatewayController::class)->names('paymentgateway');
});
