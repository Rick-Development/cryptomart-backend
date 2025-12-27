<?php

use Illuminate\Support\Facades\Route;
use Modules\BillPayments\Http\Controllers\BillPaymentsController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('billpayments', BillPaymentsController::class)->names('billpayments');
});
