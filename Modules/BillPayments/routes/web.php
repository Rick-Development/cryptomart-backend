<?php

use Illuminate\Support\Facades\Route;
use Modules\BillPayments\Http\Controllers\BillPaymentsController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('billpayments', BillPaymentsController::class)->names('billpayments');
});
