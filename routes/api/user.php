<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\User\ProfileController;
use App\Http\Controllers\Api\V1\User\AddMoneyController;
use App\Http\Controllers\Api\V1\User\MoneyOutController;
use App\Http\Controllers\Api\V1\User\SecurityController;
use App\Http\Controllers\Api\V1\User\SetupPinController;
use App\Http\Controllers\Api\V1\User\DashboardController;
use App\Http\Controllers\Api\V1\User\BeneficiaryController;
use App\Http\Controllers\Api\V1\User\TransactionController;
use App\Http\Controllers\Api\V1\User\FundTransferController;
use App\Http\Controllers\Api\V1\User\StatementController;
use App\Http\Controllers\Api\V1\User\StrowalletVirtualCardController;
use App\Http\Controllers\Api\V1\User\QuidaxController;

Route::prefix("user")->name("api.user.")->group(function(){
    Route::middleware('auth:api','verification.guard.api')->group(function(){
        // profile
        Route::controller(ProfileController::class)->prefix('profile')->group(function(){
            Route::get('info','profileInfo');
            Route::post('info/update','profileInfoUpdate')->middleware('app.mode');
            Route::post('password/update','profilePasswordUpdate')->middleware('app.mode');
            Route::post('delete-account','deleteProfile')->middleware('app.mode');
        });


             Route::controller(QuidaxController::class)->prefix('quidax')->group(function(){
                 Route::get("get-user","getUser");
                 Route::get("fetch-user-wallets","fetchUserWallets");
                 Route::get("fetch-user-wallet","fetchUserWallet");
                 Route::get("fetch-payment-address","fetchPaymentAddress");
                 Route::get("fetch-payment-addresses","fetchPaymentAddressses");
                 Route::post("create-crypto-payment-address","createCryptoPaymentAddress");
                 Route::post("create-swap-quotation","createSwapQuotation");
                 Route::post("swap","swap");
                 Route::get('fetch-withdraws', "fetch_withdraws");
             });

        // Dashboard, Notification,
        Route::controller(DashboardController::class)->group(function(){
            Route::get("dashboard","dashboard");
            Route::get("notifications","notifications");
        });

        // security
        Route::controller(SecurityController::class)->group(function(){
            // google 2fa
            Route::get('google-2fa', 'google2FA')->middleware('app.mode');
            Route::post('google-2fa/status/update', 'google2FAStatusUpdate')->middleware('app.mode');
            Route::post('google-2fa/verify/code', 'verify2FACode')->middleware('app.mode');

            // kyc
            Route::get('kyc-input-fields','getKycInputFields');
            Route::post('kyc-submit','KycSubmit')->middleware('app.mode');

            //pin check
            Route::post('pin-check','pinCheck');
        });

        // Logout Route
        Route::post('logout',[ProfileController::class,'logout']);

        // setup pin
        Route::controller(SetupPinController::class)->prefix('setup-pin')->group(function(){
            Route::post('store', 'store')->name('store');
            Route::post('update','update')->name('update');
        });

        // Add Money Routes
        Route::controller(AddMoneyController::class)->prefix("add-money")->name('add.money.')->group(function(){
            Route::get("payment-gateways","getPaymentGateways");

            // Submit with automatic gateway
            Route::post("automatic/submit","automaticSubmit")->middleware('api.kyc.verification.guard');

            // Automatic Gateway Response Routes
            Route::get('success/response/{gateway}','success')->withoutMiddleware(['auth:api','verification.guard.api','kyc.verification.guard','api.kyc.verification.guard','pin.setup.guard'])->name("payment.success");
            Route::get("cancel/response/{gateway}",'cancel')->withoutMiddleware(['auth:api','verification.guard.api','kyc.verification.guard','api.kyc.verification.guard','pin.setup.guard'])->name("payment.cancel");

            // POST Route For Unauthenticated Request
            Route::post('success/response/{gateway}', 'postSuccess')->name('payment.success')->withoutMiddleware(['auth:api','verification.guard.api','kyc.verification.guard','api.kyc.verification.guard','pin.setup.guard']);
            Route::post('cancel/response/{gateway}', 'postCancel')->name('payment.cancel')->withoutMiddleware(['auth:api','verification.guard.api','kyc.verification.guard','api.kyc.verification.guard','pin.setup.guard']);

            Route::get('manual/input-fields','manualInputFields');

            // Submit with manual gateway
            Route::post("manual/submit","manualSubmit");

            // Automatic gateway additional fields
            Route::get('payment-gateway/additional-fields','gatewayAdditionalFields');

            //redirect with Btn Pay
            Route::get('redirect/btn/checkout/{gateway}', 'redirectBtnPay')->name('payment.btn.pay')->withoutMiddleware(['auth:api','verification.guard.api','kyc.verification.guard','user.google.two.factor']);

            Route::prefix('payment')->name('payment.')->group(function() {
                Route::post('crypto/confirm/{trx_id}','cryptoPaymentConfirm')->name('crypto.confirm');
            });
        });

        // money out
        Route::controller(MoneyOutController::class)->middleware(['api.kyc.verification.guard','pin.setup.guard'])->prefix('money-out')->group(function(){
            Route::get('info','info')->withoutMiddleware(['api.kyc.verification.guard','pin.setup.guard']);
            Route::post('submit','submit');
            Route::post('instruction','instruction');
            Route::post('instruction-submit','instructionSubmit');
            Route::post('confirm','confirm');
        });

        // beneficiary
        Route::controller(BeneficiaryController::class)->middleware(['api.kyc.verification.guard','pin.setup.guard'])->prefix('beneficiary')->group(function(){
            Route::get('/','index')->withoutMiddleware(['api.kyc.verification.guard','pin.setup.guard']);
            Route::get('methods','method')->withoutMiddleware(['api.kyc.verification.guard','pin.setup.guard']);
            Route::get('bank-list','bankList')->withoutMiddleware(['api.kyc.verification.guard','pin.setup.guard']);
            Route::post('find-branch','findBranch');
            Route::post('account-details','accountDetails');
            Route::post('store','store');
            Route::post('delete','delete');
        });

        // fund transfer
        Route::controller(FundTransferController::class)->middleware(['api.kyc.verification.guard','pin.setup.guard'])->prefix('fund-transfer')->group(function(){
            Route::post('beneficiary-select','beneficiarySelect')->withoutMiddleware(['api.kyc.verification.guard','pin.setup.guard']);;
            Route::post('charge-info','chargeInfo')->withoutMiddleware(['api.kyc.verification.guard','pin.setup.guard']);;
            Route::post('submit','submit');
            Route::post('confirm','confirm');
        });

        // strowallet virtual card
        Route::controller(StrowalletVirtualCardController::class)->middleware(['api.kyc.verification.guard','pin.setup.guard'])->prefix('strowallet-card')->group(function(){
            Route::get('/','index');
            Route::get('charges','charges');
            Route::get('create/info','createPage');
            Route::get('update/customer/status','updateCustomerStatus');
            Route::post('create/customer','createCustomer');
            Route::post('update/customer','updateCustomer');
            Route::post('create','cardBuy');
            Route::post('fund','cardFundConfirm');
            Route::get('details','cardDetails');
            Route::get('transaction','cardTransaction');
            Route::post('block','cardBlock');
            Route::post('unblock','cardUnBlock')->name('block');
            Route::post('make-remove/default','makeDefaultOrRemove');
        });

        // Transaction
        Route::controller(TransactionController::class)->prefix("transaction")->group(function(){
            Route::get("log","log");
        });

        // statement
        Route::controller(StatementController::class)->prefix('statement')->group(function(){
            Route::get('/','index');
        });
    });


});
