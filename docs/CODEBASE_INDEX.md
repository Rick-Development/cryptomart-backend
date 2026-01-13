# Cryptomart Backend - Codebase Index

## Project Overview
**Cryptomart Backend** is a Laravel 9-based cryptocurrency marketplace platform that provides comprehensive financial services including crypto trading, virtual cards, P2P trading, savings, bill payments, and fund transfers.

## Technology Stack

### Core Framework
- **Laravel**: ^9.19
- **PHP**: ^8.1.25
- **Database**: MySQL (via Laravel migrations)

### Key Dependencies
- **Laravel Passport**: ^11.8 (API Authentication)
- **Guzzle HTTP**: ^7.2 (HTTP Client)
- **Intervention Image**: ^2.7 (Image Processing)
- **Laravel Excel**: ^3.1 (Excel Export/Import)
- **Stripe**: ^10.3 (Payment Processing)
- **PayPal**: ^3.0 (Payment Gateway)
- **Pusher**: ^7.2 (Real-time Notifications)
- **Google 2FA**: ^8.0 (Two-Factor Authentication)
- **GeoIP**: ^3.0 (Geolocation)
- **DomPDF**: ^2.0 (PDF Generation)

### Frontend Assets
- **Vite**: ^3.0.0 (Build Tool)
- **Bootstrap**: ^5.2.3
- **Axios**: ^1.1.2

---

## Directory Structure

### `/app` - Application Core

#### `/app/Constants` - Application Constants
- `AdminRoleConst.php` - Admin role constants
- `ExtensionConst.php` - Extension constants
- `GlobalConst.php` - Global application constants
- `LanguageConst.php` - Language constants
- `NotificationConst.php` - Notification constants
- `PaymentGatewayConst.php` - Payment gateway constants
- `SiteSectionConst.php` - Site section constants
- `SupportTicketConst.php` - Support ticket constants

#### `/app/Http/Controllers` - Request Handlers

**Admin Controllers** (`/Admin/`)
- `AddMoneyController.php` - Admin add money management
- `AdminCareController.php` - Admin user management
- `AppOnboardScreensController.php` - App onboarding screens
- `AppSettingsController.php` - Application settings
- `Auth/` - Admin authentication (Login, ForgotPassword, ResetPassword)
- `BankBranchController.php` - Bank branch management
- `BankListController.php` - Bank list management
- `BannerController.php` - Banner management
- `BroadcastingController.php` - Broadcasting management
- `ContactMessageController.php` - Contact message handling
- `CookieController.php` - Cookie settings
- `CryptoAssetController.php` - Crypto asset management
- `CurrencyController.php` - Currency management
- `DashboardController.php` - Admin dashboard
- `ExtensionsController.php` - Extension management
- `FundTransferController.php` - Fund transfer management
- `InterestController.php` - Interest management
- `LanguageController.php` - Language management
- `MoneyOutController.php` - Money withdrawal management
- `PaymentGatewayCurrencyController.php` - Payment gateway currency
- `PaymentGatewaysController.php` - Payment gateway management
- `ProfileController.php` - Admin profile
- `PushNotificationController.php` - Push notification management
- `SalaryDisbursementController.php` - Salary disbursement
- `ServerInfoController.php` - Server information
- `SetupEmailController.php` - Email configuration
- `SetupKycController.php` - KYC setup
- `SetupPagesController.php` - Page setup
- `SetupSectionsController.php` - Section setup
- `SubscriberController.php` - Subscriber management
- `SupportTicketController.php` - Support ticket management
- `SystemMaintenanceController.php` - System maintenance
- `TrxSettingsController.php` - Transaction settings
- `UsefulLinkController.php` - Useful links management
- `UserCareController.php` - User management
- `VirtualCardController.php` - Virtual card management
- `WebSettingsController.php` - Web settings

**API Controllers** (`/Api/`)
- `CryptomartInterestController.php` - Interest API
- `LockedFundsController.php` - Locked funds API
- `OrderController.php` - P2P order management
- `PayscribeAirtimeController.php` - Airtime purchase
- `PayscribeAirtimeToWalletController.php` - Airtime to wallet
- `PayscribeCableTvSubsController.php` - Cable TV subscription
- `PayscribeCardDetailsController.php` - Card details
- `PayscribeCardTransactionController.php` - Card transactions
- `PayscribeController.php` - Main Payscribe controller
- `PayscribeCreateCardController.php` - Card creation
- `PayscribeCustomerController.php` - Customer management
- `PayscribeDataBundleController.php` - Data bundle purchase
- `PayscribeElectricityBillsController.php` - Electricity bills
- `PayscribeEpinsController.php` - E-pins
- `PayscribeFreezeCardController.php` - Freeze card
- `PayscribeFundBetWalletController.php` - Fund betting wallet
- `PayscribeInternetSubController.php` - Internet subscription
- `PayscribelIntAirtimeDataController.php` - Integrated airtime/data
- `PayscribeNGNVirtualAccountController.php` - NGN virtual account
- `PayscribePayoutController.php` - Payout management
- `PayscribeSavingsController.php` - Savings management
- `PayscribeTerminateCardController.php` - Terminate card
- `PayscribeTopupCardController.php` - Card top-up
- `PayscribeUnfreezeCardController.php` - Unfreeze card
- `PayscribeUserCardController.php` - User card management
- `PayscribeWithdrawFromCardController.php` - Withdraw from card
- `SafeHeavenIdentityCheckController.php` - Identity verification
- `UserAuthController.php` - User authentication API
- `V1/User/` - Version 1 user API endpoints (17 controllers)

**User Controllers** (`/User/`)
- `AddMoneyController.php` - Add money to wallet
- `Auth/` - User authentication (4 controllers)
- `AuthorizationController.php` - User authorization
- `BeneficiaryController.php` - Beneficiary management
- `DashboardController.php` - User dashboard
- `FundTransferController.php` - Fund transfer
- `KycController.php` - KYC submission
- `MoneyOutController.php` - Money withdrawal
- `ProfileController.php` - User profile
- `SecurityController.php` - Security settings (2FA, KYC)
- `SettingsController.php` - User settings
- `SetupPinController.php` - PIN setup
- `StatementController.php` - Transaction statements
- `StrowalletVirtualCardController.php` - Strowallet virtual cards
- `SupportTicketController.php` - Support tickets
- `TransactionController.php` - Transaction management
- `UserController.php` - User management

**Frontend Controllers** (`/Frontend/`)
- `AnnouncementController.php` - Announcements
- `IndexController.php` - Frontend index

**Other Controllers**
- `Controller.php` - Base controller
- `FileController.php` - File handling
- `GlobalController.php` - Global operations
- `HomeController.php` - Home page

#### `/app/Http/Middleware` - Request Middleware

**Admin Middleware** (`/Admin/`)
- `AdminDeleteGuard.php` - Admin delete protection
- `AppModeGuard.php` - Application mode guard
- `Localization.php` - Localization
- `LoginGuard.php` - Login protection
- `MailGuard.php` - Mail protection
- `RoleDeleteGuard.php` - Role delete protection
- `RoleGuard.php` - Role-based access
- `SystemMaintenance.php` - System maintenance mode
- `SystemMaintenanceApi.php` - API maintenance mode

**API Middleware** (`/Api/`)
- `Authenticate.php` - API authentication
- `V1/HandleLocalization.php` - API localization
- `V1/User/AuthGuard.php` - User auth guard
- `V1/User/KycVerificationGuard.php` - KYC verification

**User Middleware** (`/User/`)
- `GoogleTwoFactor.php` - 2FA verification
- `PinSetupGuard.php` - PIN setup requirement
- `VerificationGuardApi.php` - API verification guard

**Core Middleware**
- `Authenticate.php` - Authentication
- `EncryptCookies.php` - Cookie encryption
- `ForceScheme.php` - Force HTTPS
- `IdempotencyMiddleware.php` - Idempotency
- `KycVerificationGuard.php` - KYC verification
- `PreventRequestsDuringMaintenance.php` - Maintenance mode
- `RedirectIfAuthenticated.php` - Redirect if authenticated
- `TrimStrings.php` - String trimming
- `TrustHosts.php` - Trusted hosts
- `TrustProxies.php` - Proxy trust
- `URLBlocker.php` - URL blocking
- `ValidateSignature.php` - Signature validation
- `VerificationGuard.php` - Verification guard
- `VerifyCsrfToken.php` - CSRF protection

#### `/app/Http/Helpers` - Helper Functions
- 39 helper files including payment gateway helpers, wallet helpers, and utility functions

#### `/app/Models` - Database Models

**Admin Models** (`/Admin/`)
- `Admin.php` - Admin user model
- `AdminHasRole.php` - Admin role assignment
- `AdminLoginLogs.php` - Admin login logs
- `AdminNotification.php` - Admin notifications
- `AdminRole.php` - Admin roles
- `AdminRoleHasPermission.php` - Role permissions
- `AdminRolePermission.php` - Permission definitions
- `AppOnboardScreens.php` - App onboarding screens
- `AppSettings.php` - Application settings
- `BankBranch.php` - Bank branches
- `BankList.php` - Bank list
- `BasicSettings.php` - Basic settings
- `CryptoAsset.php` - Crypto assets
- `CryptoTransaction.php` - Crypto transactions
- `Currency.php` - Currencies
- `Extension.php` - Extensions
- `Language.php` - Languages
- `MobileBank.php` - Mobile banking
- `PaymentGateway.php` - Payment gateways
- `PaymentGatewayCurrency.php` - Payment gateway currencies
- `PushNotificationRecord.php` - Push notification records
- `SalaryDisbursementUser.php` - Salary disbursement users
- `SetupKyc.php` - KYC setup
- `SetupPage.php` - Setup pages
- `SetupSeo.php` - SEO settings
- `SiteSections.php` - Site sections
- `SystemMaintenance.php` - System maintenance
- `TransactionSetting.php` - Transaction settings
- `UsefulLink.php` - Useful links
- `VirtualCardApi.php` - Virtual card API settings

**Frontend Models** (`/Frontend/`)
- `Announcement.php` - Announcements
- `AnnouncementCategory.php` - Announcement categories
- `ContactRequest.php` - Contact requests
- `Subscribe.php` - Subscriptions

**Core Models**
- `BasicControl.php` - Basic control settings
- `Beneficiary.php` - Beneficiaries
- `CreatePayscribeCustomer.php` - Payscribe customers
- `Image.php` - Images
- `Interest.php` - Interest rates
- `LockedFund.php` - Locked funds
- `OrderTransaction.php` - Order transactions
- `OrderWallet.php` - Order wallets
- `P2POrder.php` - P2P orders
- `P2PTakers.php` - P2P takers
- `P2PTraders.php` - P2P traders
- `PayscribeAirtimeTransaction.php` - Airtime transactions
- `PayscribeVirtualCardDetails.php` - Virtual card details
- `ReceivedInterest.php` - Received interest
- `Savings.php` - Savings accounts
- `SavingsTargets.php` - Savings targets
- `SavingsTransaction.php` - Savings transactions
- `StrowalletCustomerKyc.php` - Strowallet KYC
- `StrowalletVirtualCard.php` - Strowallet virtual cards
- `TemporaryData.php` - Temporary data storage
- `Transaction.php` - Transactions
- `TransactionDevice.php` - Transaction devices
- `TransactionMethod.php` - Transaction methods
- `User.php` - Users
- `UserAuthorization.php` - User authorizations
- `UserKycData.php` - User KYC data
- `UserLoginLog.php` - User login logs
- `UserMailLog.php` - User mail logs
- `UserNotification.php` - User notifications
- `UserPasswordReset.php` - Password resets
- `UserProfile.php` - User profiles
- `UserSupportChat.php` - Support chats
- `UserSupportTicket.php` - Support tickets
- `UserSupportTicketAttachment.php` - Support ticket attachments
- `UserWallet.php` - User wallets
- `VirtualAccounts.php` - Virtual accounts
- `Withdrawals.php` - Withdrawals

#### `/app/Services` - Business Logic Services
- `CurlService.php` - cURL service wrapper
- `LockedFundsService.php` - Locked funds business logic
- `OrderService.php` - Order processing service
- `QuidaxService.php` - Quidax cryptocurrency service integration
- `WalletService.php` - Wallet operations service

#### `/app/Traits` - Reusable Traits
- `ApiResponse.php` - API response formatting
- `ContentDelete.php` - Content deletion
- `ControlDynamicInputFields.php` - Dynamic input fields
- `Frontend.php` - Frontend utilities
- `FundTransfer/` - Fund transfer traits (2 files)
- `ManageWallet.php` - Wallet management
- `Notify.php` - Notification handling
- `PaymentGateway/` - Payment gateway traits (8 files: Authorize, CoinGate, Flutterwave, PayPal, PerfectMoney, Razorpay, SslCommerz, Stripe)
- `PaymentValidationCheck.php` - Payment validation
- `SendEmail.php` - Email sending
- `Translatable.php` - Translation support
- `Upload.php` - File upload handling
- `User/` - User traits (2 files)
- `VirtualCardTrait.php` - Virtual card operations

#### `/app/Events` - Event Handlers
- `/Admin/` - Admin events (2 files)

#### `/app/Jobs` - Background Jobs
- `ProcessSavingsInterest.php` - Process savings interest calculations

#### `/app/Mail` - Email Templates
- `/Admin/` - Admin emails (1 file)
- `UserConfirmMail.php` - User confirmation email
- `UserEmail.php` - General user email
- `UserForgotPasswordCode.php` - Password reset code
- `UserGroupEmail.php` - Group email
- `UserRegister.php` - Registration email

#### `/app/Notifications` - Notification Handlers
- `/Admin/` - Admin notifications (8 files)
- `/User/` - User notifications (14 files)
- `websiteSubscribeNotification.php` - Website subscription notification

#### `/app/Exports` - Data Exports
- `ContactRequestExport.php` - Contact request export
- `VirtualCardTrxExport.php` - Virtual card transaction export

#### `/app/Imports` - Data Imports
- `LanguageImport.php` - Language import

---

### `/routes` - Route Definitions
- `admin.php` - Admin panel routes
- `api.php` - Base API routes
- `api/auth.php` - API authentication routes
- `api/global.php` - Global API routes
- `api/user.php` - User API routes (comprehensive)
- `auth.php` - Authentication routes
- `channels.php` - Broadcasting channels
- `console.php` - Console commands
- `frontend.php` - Frontend routes
- `global.php` - Global routes
- `user.php` - User routes
- `web.php` - Web routes

---

### `/database` - Database Layer

#### `/database/migrations` - Database Migrations
- 84 migration files covering all database tables

#### `/database/seeders` - Database Seeders
- `/Admin/` - Admin seeders (23 files)
- `/User/` - User seeders (1 file)
- `DatabaseSeeder.php` - Main seeder

#### `/database/factories` - Model Factories
- `/Admin/` - Admin factories (2 files)
- `UserFactory.php` - User factory
- `UserProfileFactory.php` - User profile factory

---

### `/config` - Configuration Files
- `app.php` - Application configuration
- `auth.php` - Authentication configuration
- `broadcasting.php` - Broadcasting configuration
- `cache.php` - Cache configuration
- `coinbase.php` - Coinbase integration
- `cors.php` - CORS configuration
- `database.php` - Database configuration
- `excel.php` - Excel configuration
- `filesystems.php` - File system configuration
- `geoip.php` - GeoIP configuration
- `hashing.php` - Hashing configuration
- `image.php` - Image processing configuration
- `laravel-webp.php` - WebP configuration
- `logging.php` - Logging configuration
- `mail.php` - Mail configuration
- `paypal.php` - PayPal configuration
- `paystack.php` - Paystack configuration
- `queue.php` - Queue configuration
- `services.php` - Third-party services
- `session.php` - Session configuration
- `starting-point.php` - Starting point configuration
- `view.php` - View configuration

---

### `/resources` - Resources

#### `/resources/views` - Blade Templates
- 350 PHP/Blade template files
- Frontend and admin panel views

#### `/resources/js` - JavaScript Assets
- `app.js` - Main application JS
- `bootstrap.js` - Bootstrap JS

#### `/resources/css` - Stylesheets
- `app.css` - Main application CSS

#### `/resources/sass` - Sass Files
- 2 Sass files

#### `/resources/world` - World Data
- `cities.json` - Cities data
- `countries.json` - Countries data
- `states.json` - States data

---

### `/public` - Public Assets
- Frontend assets (138 files)
- Backend assets (174 files)
- Error images (18 files)
- Service worker
- Storage uploads

---

### `/lang` - Language Files
- `ar.json` - Arabic translations
- `en.json` - English translations
- `es.json` - Spanish translations
- `en/` - English language files (4 PHP files)
- `predefined_keys.json` - Predefined translation keys

---

## Key Features & Modules

### 1. **User Management**
- User registration and authentication
- Email verification
- Password reset
- Profile management
- KYC (Know Your Customer) verification
- Two-factor authentication (Google 2FA)
- PIN setup for transactions
- User authorization levels

### 2. **Wallet System**
- Multi-currency wallet support
- Wallet balance management
- Transaction history
- Wallet statements
- Locked funds functionality

### 3. **Payment Gateways**
- **Stripe** - Credit card payments
- **PayPal** - PayPal integration
- **Paystack** - Paystack integration
- **Authorize.net** - Authorize.net integration
- **CoinGate** - Cryptocurrency payments
- **Flutterwave** - Flutterwave integration
- **Perfect Money** - Perfect Money integration
- **Razorpay** - Razorpay integration
- **SslCommerz** - SSLCommerz integration
- Manual payment gateways
- Crypto payment addresses

### 4. **Fund Management**
- Add money to wallet
- Money withdrawal
- Fund transfers (own bank, other bank, mobile wallet)
- Beneficiary management
- Transaction charges
- Transaction limits

### 5. **Cryptocurrency Features**
- **Quidax Integration** - Crypto trading platform
- Crypto asset management
- Crypto payment addresses
- Crypto swaps
- Crypto deposits and withdrawals
- Instant swap quotations
- Ramp transactions (fiat to crypto)

### 6. **Virtual Cards**
- **Strowallet Virtual Cards** - Virtual card creation and management
- **Payscribe Virtual Cards** - Payscribe card integration
- Card creation, funding, blocking, unblocking
- Card transactions
- Card details management
- Default card selection

### 7. **P2P Trading**
- P2P order creation
- P2P traders management
- P2P takers management
- Order transactions
- Order wallets

### 8. **Savings & Interest**
- Savings account creation
- Savings deposits and withdrawals
- Savings targets
- Interest calculation
- Interest payments
- Locked funds with interest

### 9. **Bill Payments (Payscribe Integration)**
- **Airtime** - Mobile airtime purchase
- **Data Bundles** - Internet data purchase
- **Cable TV** - Cable TV subscription
- **Electricity Bills** - Electricity bill payment
- **Internet Subscription** - Internet subscription
- **E-pins** - E-pin purchase
- Virtual account creation (NGN)

### 10. **Support System**
- Support ticket creation
- Support chat
- Ticket attachments
- Ticket status management

### 11. **Notifications**
- Email notifications
- SMS notifications
- Push notifications
- In-app notifications
- Admin notifications
- User notifications

### 12. **Admin Panel**
- Admin authentication and authorization
- Role-based access control (RBAC)
- Dashboard with analytics
- User management
- Transaction management
- Payment gateway configuration
- Currency management
- Language management
- Site settings
- KYC management
- Support ticket management
- System maintenance mode
- Broadcasting management

### 13. **Security Features**
- Google 2FA
- Transaction PIN
- KYC verification
- Email verification
- SMS verification
- Login logs
- Device tracking
- IP blocking
- CSRF protection
- API authentication (Laravel Passport)

### 14. **Localization**
- Multi-language support
- Language switching
- Translation management
- RTL support (Arabic)

### 15. **Reporting & Analytics**
- Transaction logs
- User statements
- Export functionality (Excel)
- Dashboard analytics
- Server information

---

## API Endpoints Overview

### User API (`/api/user/`)

#### Authentication & Profile
- `POST /api/user/register` - User registration
- `POST /api/user/login` - User login
- `POST /api/user/logout` - User logout
- `GET /api/user/profile/info` - Get profile info
- `POST /api/user/profile/info/update` - Update profile
- `POST /api/user/profile/password/update` - Update password
- `POST /api/user/profile/delete-account` - Delete account
- `GET /api/user/profile/user-balances` - Get user balances

#### Security
- `GET /api/user/google-2fa` - Get 2FA status
- `POST /api/user/google-2fa/status/update` - Update 2FA status
- `POST /api/user/google-2fa/verify/code` - Verify 2FA code
- `GET /api/user/kyc-input-fields` - Get KYC fields
- `POST /api/user/kyc-submit` - Submit KYC
- `POST /api/user/pin-check` - Check PIN

#### PIN Setup
- `POST /api/user/setup-pin/store` - Create PIN
- `POST /api/user/setup-pin/update` - Update PIN

#### Dashboard & Notifications
- `GET /api/user/dashboard` - Dashboard data
- `GET /api/user/notifications` - User notifications

#### Add Money
- `GET /api/user/add-money/payment-gateways` - Get payment gateways
- `POST /api/user/add-money/automatic/submit` - Submit automatic payment
- `GET /api/user/add-money/success/response/{gateway}` - Payment success
- `GET /api/user/add-money/cancel/response/{gateway}` - Payment cancel
- `GET /api/user/add-money/manual/input-fields` - Manual payment fields
- `POST /api/user/add-money/manual/submit` - Submit manual payment
- `POST /api/user/add-money/payment/crypto/confirm/{trx_id}` - Confirm crypto payment

#### Money Out
- `GET /api/user/money-out/info` - Withdrawal info
- `POST /api/user/money-out/submit` - Submit withdrawal
- `POST /api/user/money-out/instruction` - Get instructions
- `POST /api/user/money-out/confirm` - Confirm withdrawal

#### Beneficiary
- `GET /api/user/beneficiary/` - List beneficiaries
- `GET /api/user/beneficiary/methods` - Get methods
- `GET /api/user/beneficiary/bank-list` - Get bank list
- `POST /api/user/beneficiary/find-branch` - Find branch
- `POST /api/user/beneficiary/account-details` - Get account details
- `POST /api/user/beneficiary/store` - Create beneficiary
- `POST /api/user/beneficiary/delete` - Delete beneficiary

#### Fund Transfer
- `POST /api/user/fund-transfer/beneficiary-select` - Select beneficiary
- `POST /api/user/fund-transfer/charge-info` - Get charges
- `POST /api/user/fund-transfer/submit` - Submit transfer
- `POST /api/user/fund-transfer/confirm` - Confirm transfer

#### Virtual Cards (Strowallet)
- `GET /api/user/strowallet-card/` - List cards
- `GET /api/user/strowallet-card/charges` - Get charges
- `GET /api/user/strowallet-card/create/info` - Create card info
- `POST /api/user/strowallet-card/create/customer` - Create customer
- `POST /api/user/strowallet-card/create` - Create card
- `POST /api/user/strowallet-card/fund` - Fund card
- `GET /api/user/strowallet-card/details` - Card details
- `GET /api/user/strowallet-card/transaction` - Card transactions
- `POST /api/user/strowallet-card/block` - Block card
- `POST /api/user/strowallet-card/unblock` - Unblock card

#### Quidax (Cryptocurrency)
- `GET /api/user/quidax/get-user` - Get user
- `GET /api/user/quidax/fetch-user-wallets` - Get wallets
- `GET /api/user/quidax/fetch-user-wallet` - Get wallet
- `GET /api/user/quidax/fetch-payment-address` - Get payment address
- `POST /api/user/quidax/create-crypto-payment-address` - Create address
- `POST /api/user/quidax/create-swap-quotation` - Create swap quote
- `POST /api/user/quidax/swap` - Execute swap
- `POST /api/user/quidax/create-withdrawal` - Create withdrawal
- `GET /api/user/quidax/fetch-deposits` - Get deposits
- `POST /api/user/quidax/ramp/initiate-ramp-transaction` - Initiate ramp

#### Payscribe Services
- `POST /api/user/payscribe/airtime` - Buy airtime
- `GET /api/user/payscribe/data-lookup` - Data lookup
- `POST /api/user/payscribe/data-vending` - Buy data
- `GET /api/user/payscribe/fetch/services` - Get cable services
- `POST /api/user/payscribe/validate-smartcardnumber` - Validate smartcard
- `POST /api/user/payscribe/pay-cabletv` - Pay cable TV
- `POST /api/user/payscribe/validate-electricity` - Validate electricity
- `POST /api/user/payscribe/pay-electricity` - Pay electricity
- `POST /api/user/payscribe/create-virtual-account` - Create virtual account
- `GET /api/user/payscribe/virtual-account-details` - Virtual account details
- `POST /api/user/payscribe/create-customer` - Create customer
- `POST /api/user/payscribe/create-card` - Create card
- `POST /api/user/payscribe/create-savings` - Create savings
- `POST /api/user/payscribe/deposit-savings` - Deposit to savings
- `POST /api/user/payscribe/withdraw-savings` - Withdraw from savings
- `GET /api/user/payscribe/list-savings` - List savings

#### P2P Trading
- `GET /api/user/p2p/orders` - List orders
- `POST /api/user/p2p/order/create` - Create order
- `GET /api/user/p2p/order/{id}` - Get order
- `GET /api/user/p2p/traders` - Get traders
- `POST /api/user/p2p/create-trader` - Create trader

#### Locked Funds
- `POST /api/user/locked-funds/lock` - Lock funds
- `GET /api/user/locked-funds/list-funds` - List locked funds

#### Interest
- `POST /api/user/create-interest` - Create interest
- `GET /api/user/interest/fetch-interests` - Get interests

#### Transactions
- `GET /api/user/transaction/log` - Transaction log
- `GET /api/user/statement/` - Statement

---

## Database Schema Overview

### Core Tables
- `users` - User accounts
- `user_profiles` - User profile information
- `user_wallets` - User wallet balances
- `transactions` - Transaction records
- `user_kyc_data` - KYC verification data
- `beneficiaries` - Fund transfer beneficiaries

### Admin Tables
- `admins` - Admin users
- `admin_roles` - Admin roles
- `admin_has_roles` - Admin role assignments
- `admin_role_permissions` - Role permissions

### Payment Tables
- `payment_gateways` - Payment gateway configurations
- `payment_gateway_currencies` - Gateway currency settings
- `withdrawals` - Withdrawal requests
- `order_transactions` - Order transactions

### Crypto Tables
- `crypto_assets` - Cryptocurrency assets
- `crypto_transactions` - Crypto transactions

### Virtual Card Tables
- `strowallet_virtual_cards` - Strowallet cards
- `payscribe_virtual_card_details` - Payscribe cards
- `virtual_card_apis` - Virtual card API settings

### P2P Tables
- `p2_p_orders` - P2P orders
- `p2_p_traders` - P2P traders
- `p2_p_takers` - P2P takers

### Savings Tables
- `savings` - Savings accounts
- `savings_targets` - Savings targets
- `savings_transactions` - Savings transactions
- `interests` - Interest rates
- `received_interests` - Received interest payments
- `locked_funds` - Locked funds

### Support Tables
- `user_support_tickets` - Support tickets
- `user_support_chats` - Support chats
- `user_support_ticket_attachments` - Ticket attachments

### Configuration Tables
- `basic_controls` - Basic settings
- `currencies` - Currency list
- `languages` - Language settings
- `extensions` - Extension settings
- `transaction_settings` - Transaction settings
- `transaction_methods` - Transaction methods

---

## Key Services Integration

### 1. **Quidax Service**
- Cryptocurrency trading platform
- Wallet management
- Payment address generation
- Swap functionality
- Deposit/withdrawal processing
- Ramp transactions

### 2. **Payscribe Service**
- Bill payments (airtime, data, cable, electricity)
- Virtual card management
- Virtual account creation
- Savings management
- Customer management

### 3. **Strowallet Service**
- Virtual card creation
- Card funding
- Card management (block/unblock)
- Card transactions

### 4. **Payment Gateways**
- Multiple payment gateway integrations
- Automatic and manual payment processing
- Webhook handling
- Payment verification

---

## Security Implementation

### Authentication
- Laravel Passport for API authentication
- JWT tokens
- Session-based authentication for web

### Authorization
- Role-based access control (RBAC)
- Permission-based access
- Middleware guards for different access levels

### Verification
- Email verification
- SMS verification
- Google 2FA
- Transaction PIN
- KYC verification

### Protection
- CSRF protection
- XSS protection
- SQL injection prevention (Eloquent ORM)
- Rate limiting
- IP blocking
- Device tracking

---

## Installation & Setup

### Prerequisites
- PHP ^8.1.25
- Composer
- MySQL Database
- Node.js & NPM (for frontend assets)

### Installation Steps
1. Update Composer packages: `composer update`
2. Seed database: `php artisan migrate:fresh --seed`
3. Install Passport tokens: `php artisan passport:install`
4. Clear cache: `php artisan optimize:clear`

### Environment Configuration
- Copy `.env.example` to `.env`
- Configure database credentials
- Set up payment gateway API keys
- Configure mail settings
- Set up third-party service credentials

---

## Development Notes

### Code Organization
- Follows Laravel MVC architecture
- PSR-4 autoloading
- Service layer for business logic
- Traits for reusable functionality
- Middleware for request handling

### Best Practices
- Eloquent ORM for database operations
- Form validation
- Error handling
- Logging
- Caching where appropriate

### Testing
- PHPUnit test suite
- Feature and unit tests
- Test factories for data generation

---

## Additional Resources

### Documentation Files
- `README.md` - Installation guide

### Configuration Files
- `composer.json` - PHP dependencies
- `package.json` - Node.js dependencies
- `vite.config.js` - Vite build configuration
- `phpunit.xml` - PHPUnit configuration

---

## Support & Maintenance

### Logging
- Application logs in `/storage/logs`
- Error logging enabled
- Debug bar for development

### Maintenance Mode
- System maintenance controller
- Maintenance mode middleware
- API maintenance handling

### Monitoring
- Server info controller
- Transaction monitoring
- User activity logs

---

*Last Updated: Generated automatically*
*Laravel Version: 9.19*
*PHP Version: 8.1.25*

