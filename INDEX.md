# CryptoMart Codebase Index

## Project Overview
**CryptoMart** is a Laravel-based cryptocurrency marketplace application (Laravel 12.0, PHP 8.4.10) that provides a comprehensive platform for cryptocurrency transactions, virtual cards, bill payments, and financial services.

---

## Directory Structure

### Core Application (`app/`)

#### Controllers (`app/Http/Controllers/`)
- **Admin Controllers** (`Admin/`)
  - Authentication: `Auth/LoginController.php`, `Auth/ForgotPasswordController.php`, `Auth/ResetPasswordController.php`
  - Financial Management: `AddMoneyController.php`, `MoneyOutController.php`, `FundTransferController.php`, `InterestController.php`
  - User Management: `UserCareController.php`, `AdminCareController.php`
  - Content Management: `BannerController.php`, `SetupPagesController.php`, `SetupSectionsController.php`
  - System Configuration: `AppSettingsController.php`, `WebSettingsController.php`, `ExtensionsController.php`, `LanguageController.php`
  - Payment Gateways: `PaymentGatewaysController.php`, `PaymentGatewayCurrencyController.php`
  - Virtual Cards: `VirtualCardController.php`
  - Support: `SupportTicketController.php`, `ContactMessageController.php`
  - Other: `DashboardController.php`, `CurrencyController.php`, `CryptoAssetController.php`, `PushNotificationController.php`, `SalaryDisbursementController.php`, `TrxSettingsController.php`, `SetupKycController.php`, `SystemMaintenanceController.php`, `ServerInfoController.php`, `CookieController.php`, `UsefulLinkController.php`, `AppOnboardScreensController.php`, `BankListController.php`, `BankBranchController.php`, `BroadcastingController.php`, `SubscriberController.php`, `ProfileController.php`

- **API Controllers** (`Api/`)
  - Payscribe Integration: Multiple controllers for bill payments, virtual cards, airtime, data bundles, electricity, cable TV, internet subscriptions, EPINs, savings, payouts, and virtual accounts
  - User Authentication: `UserAuthController.php`
  - Orders: `OrderController.php`
  - Interest: `CryptomartInterestController.php`
  - Locked Funds: `LockedFundsController.php`
  - SafeHeaven: `SafeHeavenIdentityCheckController.php`
  - User API v1: `V1/User/` (17 controllers)

- **User Controllers** (`User/`)
  - Authentication: `Auth/` (4 files)
  - Financial: `AddMoneyController.php`, `MoneyOutController.php`, `FundTransferController.php`, `TransactionController.php`, `StatementController.php`
  - Profile & Security: `ProfileController.php`, `SecurityController.php`, `SettingsController.php`, `AuthorizationController.php`, `SetupPinController.php`
  - KYC: `KycController.php`
  - Virtual Cards: `StrowalletVirtualCardController.php`
  - Support: `SupportTicketController.php`
  - Other: `DashboardController.php`, `BeneficiaryController.php`, `UserController.php`

- **Frontend Controllers** (`Frontend/`)
  - `IndexController.php`, `AnnouncementController.php`

- **Global Controllers**
  - `Controller.php`, `FileController.php`, `GlobalController.php`, `HomeController.php`

#### Models (`app/Models/`)
- **Admin Models** (`Admin/`)
  - Authentication & Roles: `Admin.php`, `AdminRole.php`, `AdminHasRole.php`, `AdminRolePermission.php`, `AdminRoleHasPermission.php`, `AdminLoginLogs.php`, `AdminNotification.php`
  - Configuration: `BasicSettings.php`, `AppSettings.php`, `SystemMaintenance.php`, `SetupSeo.php`, `SetupPage.php`, `SetupKyc.php`, `SiteSections.php`, `Language.php`, `Extension.php`
  - Financial: `Currency.php`, `CryptoAsset.php`, `CryptoTransaction.php`, `PaymentGateway.php`, `PaymentGatewayCurrency.php`, `TransactionSetting.php`
  - Virtual Cards: `VirtualCardApi.php`
  - Banking: `BankList.php`, `BankBranch.php`, `MobileBank.php`
  - Other: `AppOnboardScreens.php`, `PushNotificationRecord.php`, `UsefulLink.php`, `SalaryDisbursementUser.php`

- **User Models**
  - Core: `User.php`, `UserProfile.php`, `UserWallet.php`, `UserAuthorization.php`, `UserKycData.php`
  - Authentication: `UserPasswordReset.php`, `UserLoginLog.php`, `UserMailLog.php`
  - Transactions: `Transaction.php`, `TransactionMethod.php`, `TransactionDevice.php`, `Withdrawals.php`
  - Support: `UserSupportTicket.php`, `UserSupportChat.php`, `UserSupportTicketAttachment.php`
  - Notifications: `UserNotification.php`
  - Financial: `Beneficiary.php`, `Interest.php`, `ReceivedInterest.php`, `LockedFund.php`, `Savings.php`, `SavingsTargets.php`, `SavingsTransaction.php`
  - Virtual Cards: `StrowalletVirtualCard.php`, `StrowalletCustomerKyc.php`, `PayscribeVirtualCardDetails.php`
  - Orders: `OrderTransaction.php`, `OrderWallet.php`, `P2POrder.php`, `P2PTraders.php`, `P2PTakers.php`
  - Payscribe: `CreatePayscribeCustomer.php`, `PayscribeAirtimeTransaction.php`
  - Other: `VirtualAccounts.php`, `TemporaryData.php`, `BasicControl.php`, `Image.php`

- **Frontend Models** (`Frontend/`)
  - `Announcement.php`, `AnnouncementCategory.php`, `ContactRequest.php`, `Subscribe.php`

#### Services (`app/Services/`)
- `CurlService.php` - HTTP request handling
- `WalletService.php` - Wallet operations
- `OrderService.php` - Order management
- `LockedFundsService.php` - Locked funds management
- `QuidaxService.php` - Quidax integration

#### Helpers (`app/Http/Helpers/`)
- **Core Helpers**
  - `helpers.php` - General helper functions
  - `ConnectionHelper.php` - Database/API connections
  - `PaymentGateway.php` - Payment gateway utilities
  - `Response.php` - API response formatting

- **Payscribe Integration** (`Payscribe/`)
  - **Bills Payments** (`BillsPayments/`)
    - `AirtimeHelper.php`, `AirtimeToWalletHelper.php`, `DataBundleHelper.php`, `CableTVSubscriptionHelper.php`, `ElectricityBillsHelper.php`, `EpinsHelper.php`, `InternetSubscriptionHelper.php`, `IntAirtimeDataHelper.php`, `FundBetWalletHelper.php`, `BillPaymentHelper.php`, `PayscribeHelper.php`
  - **Card Issuing** (`CardIssusing/`)
    - `CreateCardHelper.php`, `CardDetailsHelper.php`, `CardTransactionHelper.php`, `TopupCardHelper.php`, `FreezeCardHelper.php`, `UnfreezeCardHelper.php`, `TerminateCardHelper.php`, `WithdrawFromCardHelper.php`
  - **Collections** (`Collections/`)
    - `NGNVirtualAccountsHelper.php`, `WalletSystemHelper.php`
  - **Other Payscribe Helpers**
    - `PayscribeCustomersHelper.php`, `PayscribeBalanceHelper.php`, `PayscribePayoutHelper.php`, `FXAndConversionsHelper.php`
  - **KYC** (`Kyc/`)
    - `KycLookup.php`
  - **Payout** (`Payout/`)
    - `PayoutHelper.php`

- **SafeHeaven Integration** (`SafeHeaven/`)
  - `ApiConnectionHelper.php`, `AccountHelper.php`, `IdentityCheckHelper.php`, `TransferHelper.php`, `VirtualAccount.php`, `VASHelper.php`, `SafeHeavenBalanceHelper.php`

- **Strowallet**
  - `strowallet-card.php` - Strowallet virtual card helper

#### Traits (`app/Traits/`)
- `ApiResponse.php` - API response formatting
- `ContentDelete.php` - Content deletion utilities
- `ControlDynamicInputFields.php` - Dynamic form field handling
- `Frontend.php` - Frontend utilities
- `ManageWallet.php` - Wallet management
- `Notify.php` - Notification handling
- `PaymentValidationCheck.php` - Payment validation
- `SendEmail.php` - Email sending
- `Translatable.php` - Translation support
- `Upload.php` - File upload handling
- `VirtualCardTrait.php` - Virtual card operations
- **Fund Transfer** (`FundTransfer/`)
  - `OwnBankTransferTrait.php`, `OtherBankTransferTrait.php`
- **Payment Gateway** (`PaymentGateway/`)
  - `Authorize.php`, `CoinGate.php`, `Flutterwave.php`, `Paypal.php`, `PerfectMoney.php`, `Razorpay.php`, `SslCommerz.php`, `Stripe.php`
- **User** (`User/`)
  - `LoggedInUsers.php`, `RegisteredUsers.php`

#### Constants (`app/Constants/`)
- `AdminRoleConst.php` - Admin role constants
- `ExtensionConst.php` - Extension constants
- `GlobalConst.php` - Global constants
- `LanguageConst.php` - Language constants
- `NotificationConst.php` - Notification constants
- `PaymentGatewayConst.php` - Payment gateway constants
- `SiteSectionConst.php` - Site section constants
- `SupportTicketConst.php` - Support ticket constants

#### Other App Components
- **Console** (`Console/`)
  - `Kernel.php` - Console kernel
- **Events** (`Events/Admin/`)
  - 2 event files
- **Exceptions** (`Exceptions/`)
  - `Handler.php` - Exception handling
- **Exports** (`Exports/`)
  - `ContactRequestExport.php`, `VirtualCardTrxExport.php`
- **Imports** (`Imports/`)
  - `LanguageImport.php`
- **Jobs** (`Jobs/`)
  - `ProcessSavingsInterest.php` - Savings interest processing
- **Mail** (`Mail/`)
  - `UserConfirmMail.php`, `UserEmail.php`, `UserForgotPasswordCode.php`, `UserGroupEmail.php`, `UserRegister.php`
  - Admin: 1 mail file
- **Notifications** (`Notifications/`)
  - Admin: 8 notification files
  - User: 14 notification files
  - `websiteSubscribeNotification.php`
- **Providers** (`Providers/`)
  - `AppServiceProvider.php`, `AuthServiceProvider.php`, `BroadcastServiceProvider.php`, `CustomServiceProvider.php`, `EventServiceProvider.php`, `RouteServiceProvider.php`
  - Admin: 2 provider files
- **Middleware** (`app/Http/Middleware/`)
  - 31 middleware files
- **Resources** (`app/Http/Resources/`)
  - 1 resource file

### Routes (`routes/`)
- `web.php` - Web routes
- `api.php` - API routes
- `admin.php` - Admin routes
- `user.php` - User routes
- `frontend.php` - Frontend routes
- `auth.php` - Authentication routes
- `global.php` - Global routes
- `channels.php` - Broadcasting channels
- `console.php` - Console routes
- **API Routes** (`api/`)
  - 3 API route files

### Database (`database/`)
- **Migrations** (`migrations/`)
  - 84 migration files
- **Seeders** (`seeders/`)
  - 25 seeder files
- **Factories** (`factories/`)
  - `UserFactory.php`, `UserProfileFactory.php`
  - Admin: 2 factory files

### Configuration (`config/`)
- `app.php` - Application configuration
- `auth.php` - Authentication configuration
- `database.php` - Database configuration
- `mail.php` - Mail configuration
- `queue.php` - Queue configuration
- `cache.php` - Cache configuration
- `session.php` - Session configuration
- `filesystems.php` - File system configuration
- `logging.php` - Logging configuration
- `view.php` - View configuration
- `cors.php` - CORS configuration
- `broadcasting.php` - Broadcasting configuration
- `hashing.php` - Hashing configuration
- `image.php` - Image processing configuration
- `excel.php` - Excel export/import configuration
- `geoip.php` - GeoIP configuration
- `laravel-webp.php` - WebP image configuration
- Payment Gateway Configs: `paypal.php`, `paystack.php`, `coinbase.php`
- `services.php` - Third-party services configuration
- `starting-point.php` - Starting point configuration

### Resources (`resources/`)
- **Views** (`views/`)
  - 350 files (349 PHP, 1 CSS)
- **JavaScript** (`js/`)
  - `app.js`, `bootstrap.js`
- **CSS/SASS** (`css/`, `sass/`)
  - CSS and SASS files
- **World Data** (`world/`)
  - `cities.json`, `countries.json`, `states.json`

### Public Assets (`public/`)
- **Frontend** (`frontend/`)
  - JavaScript: 15 JS files (jQuery, Bootstrap, ApexCharts, Swiper, Select2, etc.)
  - Styles: 21 SCSS files
  - Images: 48 WebP files
- **Backend** (`backend/`)
  - JavaScript: Multiple JS files (jQuery, Bootstrap, CKEditor, Chart.js, ApexCharts, etc.)
  - Images: 39 WebP files, 29 PNG files
  - Fonts: 23 TTF files
- **Other**
  - `service-worker.js` - Service worker for PWA
  - `robots.txt` - SEO robots file
  - `favicon.ico` - Favicon

### Language Files (`lang/`)
- `en.json`, `ar.json`, `es.json` - Translation files
- `predefined_keys.json` - Predefined translation keys
- `en/` - English language files (4 PHP files)

### Tests (`tests/`)
- `TestCase.php` - Base test case
- `CreatesApplication.php` - Test application creation
- **Feature** (`Feature/`)
  - 1 feature test
- **Unit** (`Unit/`)
  - 1 unit test

---

## Key Features

### Financial Services
- **Wallet Management**: Multi-currency wallet system
- **Money Transfers**: Add money, withdraw money, fund transfers (own bank & other banks)
- **Virtual Cards**: Strowallet and Payscribe virtual card integration
- **Interest & Savings**: Savings accounts, interest calculations, locked funds
- **P2P Trading**: Peer-to-peer order system with traders and takers
- **Transactions**: Comprehensive transaction management and statements

### Payment Gateways
- Stripe
- PayPal
- Paystack
- CoinGate
- Flutterwave
- Razorpay
- SSLCommerz
- Perfect Money
- Authorize.net
- Coinbase

### Payscribe Integration
- **Bill Payments**: Airtime, data bundles, electricity, cable TV, internet subscriptions, EPINs
- **Virtual Cards**: Create, manage, freeze, unfreeze, terminate, top-up, withdraw
- **Virtual Accounts**: NGN virtual accounts
- **Savings**: Savings management
- **Payouts**: Payout processing
- **FX & Conversions**: Foreign exchange operations

### SafeHeaven Integration
- Identity verification
- Account management
- Transfers
- Virtual accounts
- VAS (Value Added Services)
- Balance management

### User Management
- User registration and authentication
- Profile management
- KYC (Know Your Customer) verification
- Two-factor authentication (Google 2FA)
- Security settings and PIN setup
- Login logs and device tracking
- User notifications

### Admin Features
- Admin panel with role-based access control
- Dashboard and analytics
- User management
- Transaction management
- Content management (banners, pages, sections)
- System settings and configuration
- Payment gateway management
- Currency and crypto asset management
- Support ticket system
- Push notifications
- Email configuration
- Language management
- Extension management
- Salary disbursement
- System maintenance mode

### Support System
- Support tickets
- Support chat
- Ticket attachments
- Contact requests

### Frontend Features
- Public website
- Announcements
- Multi-language support (English, Arabic, Spanish)
- SEO optimization
- Cookie management
- Newsletter subscription

---

## Dependencies

### PHP Packages (Composer)
- **Framework**: Laravel 12.0
- **Authentication**: Laravel Passport, Laravel Socialite
- **Payment Gateways**: Stripe, PayPal (srmklive/paypal), Authorize.net
- **Image Processing**: Intervention Image 3.11
- **PDF Generation**: barryvdh/laravel-dompdf
- **Excel**: maatwebsite/excel
- **2FA**: pragmarx/google2fa
- **Real-time**: Pusher (pusher/pusher-php-server, pusher/pusher-push-notifications)
- **GeoIP**: torann/geoip
- **Modules**: nwidart/laravel-modules
- **WebP**: buglinjo/laravel-webp
- **HTTP Client**: Guzzle
- **User Agent**: jenssegers/agent

### JavaScript Packages (NPM)
- **Build Tool**: Vite 3.0
- **Framework**: Bootstrap 5.2.3
- **HTTP Client**: Axios 1.1.2
- **Utilities**: Lodash 4.17.19
- **UI**: Popper.js 2.11.6
- **CSS Preprocessor**: Sass 1.56.1
- **PostCSS**: 8.1.14

---

## Key Integrations

1. **Payscribe**: Comprehensive bill payments, virtual cards, and financial services
2. **SafeHeaven**: Identity verification and financial services
3. **Strowallet**: Virtual card services
4. **Quidax**: Cryptocurrency exchange integration
5. **Payment Gateways**: Multiple payment processor integrations

---

## Development Setup

1. **Install Dependencies**
   ```bash
   composer update
   npm install
   ```

2. **Database Setup**
   ```bash
   php artisan migrate:fresh --seed
   ```

3. **Passport Setup**
   ```bash
   php artisan passport:install
   ```

4. **Clear Cache**
   ```bash
   php artisan optimize:clear
   ```

---

## File Statistics
- **PHP Controllers**: 111 files
- **PHP Models**: 94 files
- **PHP Helpers**: 39 files
- **PHP Middleware**: 31 files
- **Database Migrations**: 84 files
- **Database Seeders**: 25 files
- **View Files**: 350 files
- **JavaScript Files**: 40+ files
- **Language Files**: Multiple JSON and PHP files

---

## Notes
- This is a Laravel 12.0 application using PHP 8.4.10
- The application uses Laravel Passport for API authentication
- Multi-language support with English, Arabic, and Spanish
- PWA support with service worker
- Modular architecture with Laravel Modules
- Comprehensive payment gateway and financial service integrations

---

*Last Updated: Generated automatically*
*For detailed API documentation, refer to the API route files in `routes/api/`*

