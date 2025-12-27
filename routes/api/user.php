<?php
use App\Http\Controllers\Admin\InterestController;
use App\Http\Controllers\Api\CryptomartInterestController;
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
use App\Http\Controllers\Api\PayscribeAirtimeController;
use App\Http\Controllers\Api\PayscribeCableTvSubsController;
use App\Http\Controllers\Api\PayscribeDataBundleController;
use App\Http\Controllers\Api\PayscribeElectricityBillsController;
use App\Http\Controllers\Api\PayscribeNGNVirtualAccountController;
use App\Http\Controllers\Api\PayscribeCustomerController;
use App\Http\Controllers\Api\PayscribeCardDetailsController;
use App\Http\Controllers\Api\PayscribeCreateCardController;
use App\Http\Controllers\Api\PayscribeSavingsController;
use App\Http\Controllers\Admin\BannerController;

Route::prefix("user")->name("api.user.")->group(function () {
    Route::middleware(['auth:api', 'verification.guard.api'])->group(function () {
        // profile
        Route::controller(ProfileController::class)->prefix('profile')->group(function () {
            Route::get('info', 'profileInfo');
            Route::post('info/update', 'profileInfoUpdate')->middleware('app.mode');
            Route::post('password/update', 'profilePasswordUpdate')->middleware('app.mode');
            Route::post('delete-account', 'deleteProfile')->middleware('app.mode');

            Route::get('user-balances', 'get_balances')->middleware('app.mode');
        });


        Route::controller(QuidaxController::class)->prefix('quidax')->group(function () {
            Route::get("get-user", "getUser");
            Route::get("fetch-user-wallets", "fetchUserWallets");
            Route::get("fetch-user-wallet", "fetchUserWallet");
            Route::get("fetch-payment-address", "fetchPaymentAddress");
            Route::get("fetch-payment-addresses", "fetchPaymentAddressses");
            Route::post("create-crypto-payment-address", "createCryptoPaymentAddress");
            Route::post("create-swap-quotation", "createSwapQuotation");
            Route::post("swap", "swap");
            Route::get('fetch-withdraws', "fetch_withdraws");
            Route::post('cancel-withdrawal', "cancel_withdrawal");
            Route::post('create-withdrawal', 'create_withdrawal');
            Route::post('instant-swap-quotation', "refresh_instant_swap_quotation");
            Route::get('fetch-swap-transaction', "fetch_swap_transaction");
            Route::get("get-swap-transaction", "get_swap_transaction");
            Route::post('temporary-swap-quotaion', "temporary_swap_quotation");
            Route::get('fetch-deposits', "fetch_deposits");
            Route::get('fetch-a-deposit', "fetch_a_deposit");

            Route::prefix('ramp')->group(function () {
                Route::post('initiate-ramp-transaction', "initiate_ramp_transaction");
            });
        });

        // Dashboard, Notification,
        Route::controller(DashboardController::class)->group(function () {
            Route::get("dashboard", "dashboard");
            Route::get("notifications", "notifications");
        });

        // security
        Route::controller(SecurityController::class)->group(function () {
            // google 2fa
            Route::get('google-2fa', 'google2FA')->middleware('app.mode');
            Route::post('google-2fa/status/update', 'google2FAStatusUpdate')->middleware('app.mode');
            Route::post('google-2fa/verify/code', 'verify2FACode')->middleware('app.mode');

            // kyc
            Route::get('kyc-input-fields', 'getKycInputFields');
            Route::post('kyc-submit', 'KycSubmit')->middleware('app.mode');
            Route::get('kyc/status', 'getKycStatus');

            //pin check
            Route::post('pin-check', 'pinCheck');
        });

        // Logout Route
        Route::post('logout', [ProfileController::class, 'logout']);

        // setup pin
        Route::controller(SetupPinController::class)->prefix('setup-pin')->group(function () {
            Route::post('store', 'store')->name('store');
            Route::post('update', 'update')->name('update');
        });

        // Add Money Routes
        Route::controller(AddMoneyController::class)->prefix("add-money")->name('add.money.')->group(function () {
            Route::get("payment-gateways", "getPaymentGateways");

            // Submit with automatic gateway
            Route::post("automatic/submit", "automaticSubmit")->middleware('api.kyc.verification.guard');

            // Automatic Gateway Response Routes
            Route::get('success/response/{gateway}', 'success')->withoutMiddleware(['auth:api', 'verification.guard.api', 'kyc.verification.guard', 'api.kyc.verification.guard', 'pin.setup.guard'])->name("payment.success");
            Route::get("cancel/response/{gateway}", 'cancel')->withoutMiddleware(['auth:api', 'verification.guard.api', 'kyc.verification.guard', 'api.kyc.verification.guard', 'pin.setup.guard'])->name("payment.cancel");

            // POST Route For Unauthenticated Request
            Route::post('success/response/{gateway}', 'postSuccess')->name('payment.success')->withoutMiddleware(['auth:api', 'verification.guard.api', 'kyc.verification.guard', 'api.kyc.verification.guard', 'pin.setup.guard']);
            Route::post('cancel/response/{gateway}', 'postCancel')->name('payment.cancel')->withoutMiddleware(['auth:api', 'verification.guard.api', 'kyc.verification.guard', 'api.kyc.verification.guard', 'pin.setup.guard']);

            Route::get('manual/input-fields', 'manualInputFields');

            // Submit with manual gateway
            Route::post("manual/submit", "manualSubmit");

            // Automatic gateway additional fields
            Route::get('payment-gateway/additional-fields', 'gatewayAdditionalFields');

            //redirect with Btn Pay
            Route::get('redirect/btn/checkout/{gateway}', 'redirectBtnPay')->name('payment.btn.pay')->withoutMiddleware(['auth:api', 'verification.guard.api', 'kyc.verification.guard', 'user.google.two.factor']);

            Route::prefix('payment')->name('payment.')->group(function () {
                Route::post('crypto/confirm/{trx_id}', 'cryptoPaymentConfirm')->name('crypto.confirm');
            });
        });

        // money out
        Route::controller(MoneyOutController::class)->middleware(['api.kyc.verification.guard', 'pin.setup.guard'])->prefix('money-out')->group(function () {
            Route::get('info', 'info')->withoutMiddleware(['api.kyc.verification.guard', 'pin.setup.guard']);
            Route::post('submit', 'submit');
            Route::post('instruction', 'instruction');
            Route::post('instruction-submit', 'instructionSubmit');
            Route::post('confirm', 'confirm');
        });

        // beneficiary
        Route::controller(BeneficiaryController::class)->middleware(['api.kyc.verification.guard', 'pin.setup.guard'])->prefix('beneficiary')->group(function () {
            Route::get('/', 'index')->withoutMiddleware(['api.kyc.verification.guard', 'pin.setup.guard']);
            Route::get('methods', 'method')->withoutMiddleware(['api.kyc.verification.guard', 'pin.setup.guard']);
            Route::get('bank-list', 'bankList')->withoutMiddleware(['api.kyc.verification.guard', 'pin.setup.guard']);
            Route::post('find-branch', 'findBranch');
            Route::post('account-details', 'accountDetails');
            Route::post('store', 'store');
            Route::post('delete', 'delete');
        });

        // fund transfer
        Route::controller(FundTransferController::class)->middleware(['api.kyc.verification.guard', 'pin.setup.guard'])->prefix('fund-transfer')->group(function () {
            Route::post('beneficiary-select', 'beneficiarySelect')->withoutMiddleware(['api.kyc.verification.guard', 'pin.setup.guard']);
            ;
            Route::post('charge-info', 'chargeInfo')->withoutMiddleware(['api.kyc.verification.guard', 'pin.setup.guard']);
            ;
            Route::post('submit', 'submit');
            Route::post('confirm', 'confirm');
        });

        // strowallet virtual card
        Route::controller(StrowalletVirtualCardController::class)->middleware(['api.kyc.verification.guard', 'pin.setup.guard'])->prefix('strowallet-card')->group(function () {
            Route::get('/', 'index');
            Route::get('charges', 'charges');
            Route::get('create/info', 'createPage');
            Route::get('update/customer/status', 'updateCustomerStatus');
            Route::post('create/customer', 'createCustomer');
            Route::post('update/customer', 'updateCustomer');
            Route::post('create', 'cardBuy');
            Route::post('fund', 'cardFundConfirm');
            Route::get('details', 'cardDetails');
            Route::get('transaction', 'cardTransaction');
            Route::post('block', 'cardBlock');
            Route::post('unblock', 'cardUnBlock')->name('block');
            Route::post('make-remove/default', 'makeDefaultOrRemove');
        });

        // Transaction
        Route::controller(TransactionController::class)->prefix("transaction")->group(function () {
            Route::get("log", "log");
        });

        // statement
        Route::controller(StatementController::class)->prefix('statement')->group(function () {
            Route::get('/', 'index');
        });
        
        Route::prefix('payscribe')->group(function () {
            // airtime
            Route::post('airtime', [PayscribeAirtimeController::class, 'airtime']);
            // Route::get('fetch/services', [PayscribeCableTvSubsController::class, 'fetchBouquents']);

            // Data
            Route::get('data-lookup', [PayscribeDataBundleController::class, 'dataLookup']);
            Route::post('data-vending', [PayscribeDataBundleController::class, 'dataVending']);
            
            // Cable Tv
            Route::get('fetch/services', [PayscribeCableTvSubsController::class, 'fetchBouquents']);
            Route::post('validate-smartcardnumber', [PayscribeCableTvSubsController::class, 'validateSmartCardNumber']);
            Route::post('pay-cabletv', [PayscribeCableTvSubsController::class, 'payCableTv']);

            //Electricity
            Route::post('validate-electricity', [PayscribeElectricityBillsController::class, 'validateElectricity']);
            Route::post('pay-electricity', [PayscribeElectricityBillsController::class, 'payElectricity']);

            // Virtual Account
            Route::post('create-virtual-account', [PayscribeNGNVirtualAccountController::class, 'create_parmenent_virtual_account']);
            Route::get('virtual-account-details', [PayscribeNGNVirtualAccountController::class, 'virtualAccountDetails']);
            Route::post('deactivate-virtual-account', [PayscribeNGNVirtualAccountController::class, 'deactivateVirtualAccount']);
            Route::post('reactivate-virtual-account', [PayscribeNGNVirtualAccountController::class, 'activateVirtualAccount']);

            // create customer
            Route::post('create-customer', [PayscribeCustomerController::class, 'createCustomer']);
            
            // CARDS
            Route::post('create-card', [PayscribeCreateCardController::class, 'createCard']);
            
            Route::controller(PayscribeSavingsController::class)->group(function () {
                Route::post('create-savings', 'createSavings');
                Route::post('deposit-savings', 'depositSavings');
                Route::post('withdraw-savings', 'withdrawFromSavings');
                Route::get('list-savings', 'listUserSavings');
            });
        });
        
        Route::controller(BannerController::class)->prefix('banner')->group(function () {
            Route::post('/upload-image', 'store');
        });
        
        // P2P
        Route::controller(App\Http\Controllers\Api\OrderController::class)->prefix('p2p')->group(function () {
            Route::get('/orders', 'index');
            Route::post('/order/create', 'store');
            Route::get('/order/{id}', 'show');

            // fetching takers
            Route::get('/traders', 'fetch_traders');
            Route::post('/create-trader', 'create_trader');
            
            // trade actions
            Route::post('/trade/{uid}/release', 'release');
            Route::post('/trade/{uid}/dispute', 'dispute');
        });

        // Locked funds
        Route::controller(App\Http\Controllers\Api\LockedFundsController::class)->prefix('locked-funds')->group(function () {
            Route::post('lock', 'lock');
            Route::get('list-funds', 'list_locked_funds');
        });

        Route::post('/create-interest', [InterestController::class, 'create_interest']);
        Route::controller(CryptomartInterestController::class)->prefix('interest')->group(function () {
            Route::get('fetch-interests', 'interests');
        });
    });


});