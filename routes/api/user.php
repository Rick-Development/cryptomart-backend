<?php
use App\Http\Controllers\Admin\InterestController;
use App\Http\Controllers\Api\CryptomartInterestController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\User\WalletController;
use App\Http\Controllers\Api\V1\User\ProfileController;
use App\Http\Controllers\Api\V1\User\AddMoneyController;
use App\Http\Controllers\Api\V1\User\MoneyOutController;
use App\Http\Controllers\Api\V1\User\SecurityController;
use App\Http\Controllers\Api\V1\User\SetupPinController;
use App\Http\Controllers\Api\V1\User\DashboardController;
use App\Http\Controllers\Api\V1\User\BeneficiaryController;
use App\Http\Controllers\Api\V1\User\TransactionController;
use App\Http\Controllers\Api\V1\User\FundTransferController;
use App\Http\Controllers\Api\V1\User\InternalTransferController;
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

use App\Http\Controllers\Api\V1\User\TransactionPinController;
use App\Http\Controllers\Api\V1\User\LoginPinController;

Route::prefix("user")->name("api.user.")->group(function () {
    Route::middleware(['auth:api', 'verification.guard.api'])->group(function () {
        // ... (wallets, profile, quidax, instant order, dashboard routes remain here or referenced implicitly if not customized)

        // security group
        Route::controller(SecurityController::class)->group(function () {
            // google 2fa
            Route::get('google-2fa', 'google2FA')->middleware('app.mode');
            Route::post('google-2fa/status/update', 'google2FAStatusUpdate')->middleware('app.mode');
            Route::post('google-2fa/verify/code', 'verify2FACode')->middleware('app.mode');

            // kyc
            Route::get('kyc-input-fields', 'getKycInputFields');
            Route::post('kyc-submit', 'KycSubmit')->middleware('app.mode');
            Route::get('kyc/status', 'getKycStatus');
        });

        // Transaction PIN (4-digit)
        Route::controller(TransactionPinController::class)->prefix('transaction-pin')->group(function() {
            Route::post('store', 'store');
            Route::post('update', 'update');
            Route::post('check', 'check');
        });

        // Login PIN (6-digit)
        Route::controller(LoginPinController::class)->prefix('login-pin')->group(function() {
            Route::post('store', 'store');
            Route::post('update', 'update');
            Route::post('check', 'check');
        });


        // Logout Route
        Route::post('logout', [ProfileController::class, 'logout']);

        // OLD setup pin - keeping for backward compatibility if needed, but logic moved to TransactionPinController
        // The user asked to create separate controllers, implying specific routes. 
        // I will point 'setup-pin' to the new controller to prevent code duplication or just leave it for now but prioritize new routes.
        
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
            Route::post('check-user', [InternalTransferController::class, 'checkUser']);
            Route::post('confirm', [InternalTransferController::class, 'confirm']);
            Route::post('beneficiary-select', 'beneficiarySelect')->withoutMiddleware(['api.kyc.verification.guard', 'pin.setup.guard']);
            Route::post('charge-info', 'chargeInfo')->withoutMiddleware(['api.kyc.verification.guard', 'pin.setup.guard']);
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
        
        // Banners
        Route::controller(App\Http\Controllers\Api\V1\User\BannerController::class)->prefix('banner')->group(function () {
            Route::get('/', 'index');
        });

        // Announcements
        Route::controller(App\Http\Controllers\Api\V1\User\AnnouncementController::class)->prefix('announcement')->group(function () {
             Route::get('/', 'index');
             Route::get('categories', 'categories');
             Route::get('{slug}', 'show');
        });

        // Support Tickets
        Route::controller(App\Http\Controllers\Api\V1\User\SupportTicketController::class)->prefix('support-ticket')->group(function () {
             Route::get('/', 'index');
             Route::post('/', 'store');
             Route::get('conversation/{token}', 'conversation');
             Route::post('message/send', 'messageSend');
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

        // P2P Trading
        Route::post('/p2p/trade/{uid}/release', [App\Http\Controllers\Api\OrderController::class, 'release']);
        Route::post('/p2p/trade/{uid}/dispute', [App\Http\Controllers\Api\OrderController::class, 'dispute']);

        // P2P Marketplace (New Bybit-style)
        Route::prefix('p2p')->group(function () {
            // Ads
            Route::get('/ads', [App\Http\Controllers\Api\V1\User\P2PAdController::class, 'index']);
            Route::get('/ads/{id}', [App\Http\Controllers\Api\V1\User\P2PAdController::class, 'show']);
            Route::post('/ads', [App\Http\Controllers\Api\V1\User\P2PAdController::class, 'store']);
            Route::post('/ads/{id}/toggle', [App\Http\Controllers\Api\V1\User\P2PAdController::class, 'toggle']);
            Route::get('/my-ads', [App\Http\Controllers\Api\V1\User\P2PAdController::class, 'myAds']);
            
            // Payment Methods
            Route::apiResource('payment-methods', App\Http\Controllers\Api\V1\User\P2PPaymentMethodController::class);
            
            // Orders
            Route::post('/orders', [App\Http\Controllers\Api\V1\User\P2POrderController::class, 'store']);
            Route::get('/my-orders', [App\Http\Controllers\Api\V1\User\P2POrderController::class, 'myOrders']);
            Route::post('/orders/{uid}/mark-paid', [App\Http\Controllers\Api\V1\User\P2POrderController::class, 'markPaid']);
            Route::post('/orders/{uid}/release', [App\Http\Controllers\Api\V1\User\P2POrderController::class, 'release']);
            Route::post('/orders/{uid}/appeal', [App\Http\Controllers\Api\V1\User\P2POrderController::class, 'appeal']);
            Route::get('/orders/{uid}/chat', [App\Http\Controllers\Api\V1\User\P2POrderController::class, 'chat']);
            Route::post('/orders/{uid}/chat', [App\Http\Controllers\Api\V1\User\P2POrderController::class, 'sendMessage']);
            
            // Disclaimers
            Route::get('/disclaimers', [App\Http\Controllers\Api\V1\User\P2PDisclaimerController::class, 'index']);
            Route::get('/disclaimers/{key}', [App\Http\Controllers\Api\V1\User\P2PDisclaimerController::class, 'show']);
            Route::post('/disclaimers/{key}/accept', [App\Http\Controllers\Api\V1\User\P2PDisclaimerController::class, 'accept']);
            
            // KYC Status & Limits
            Route::get('/kyc/status', [App\Http\Controllers\Api\V1\User\P2PKycController::class, 'status']);
            Route::get('/kyc/limits', [App\Http\Controllers\Api\V1\User\P2PKycController::class, 'limits']);
            Route::post('/kyc/check-permission', [App\Http\Controllers\Api\V1\User\P2PKycController::class, 'checkPermission']);
            
            // Feedback
            Route::post('/orders/{uid}/feedback', [App\Http\Controllers\Api\V1\User\P2PFeedbackController::class, 'store']);
            Route::get('/feedback', [App\Http\Controllers\Api\V1\User\P2PFeedbackController::class, 'index']);
            Route::get('/feedback/{userId}', [App\Http\Controllers\Api\V1\User\P2PFeedbackController::class, 'index']);
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

        // SafeHaven Integration
        Route::controller(App\Http\Controllers\Api\V1\User\SafeHavenController::class)->prefix('safehaven')->group(function () {
            Route::get('sub-account', 'virtualAccount');
            Route::get('banks', 'banks');
            Route::post('name-enquiry', 'nameEnquiry');
            Route::post('transfer', 'transfer');
        });

        // Gift Cards (Reloadly)
        $giftCardRoutes = function () {
            Route::get('/categories', 'categories');
            Route::get('/discovery', 'discovery');
            Route::get('/countries', 'countries');
            Route::get('/countries/{isoCode}', 'countryDetails');
            Route::get('/products', 'products');
            Route::get('/products/{id}', 'productDetails');
            Route::get('/fx-rate', 'fxRate');
            Route::post('/order', 'storeOrder');
            Route::get('/sync-metadata', 'syncMetadata'); // Internal sync
        };

        Route::controller(App\Http\Controllers\Api\V1\User\GiftCardController::class)->prefix('gift-card')->group($giftCardRoutes);
        Route::controller(App\Http\Controllers\Api\V1\User\GiftCardController::class)->prefix('gift-cards')->group($giftCardRoutes);
    });


});