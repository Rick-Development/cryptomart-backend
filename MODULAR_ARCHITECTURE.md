# Modular Architecture Documentation

## Overview

The CryptoMart application has been restructured using the **nwidart/laravel-modules** package to implement a modular architecture. This allows for better code organization, separation of concerns, and easier maintenance.

## Module Structure

The application is now organized into the following modules:

### 1. **Core Module** (`Modules/Core`)
- **Purpose**: Shared functionality, common models, traits, constants, and helpers
- **Contains**:
  - Base models and traits used across multiple modules
  - Constants (AdminRoleConst, ExtensionConst, GlobalConst, etc.)
  - Common helpers and utilities
  - Shared services

### 2. **Admin Module** (`Modules/Admin`)
- **Purpose**: Admin panel functionality
- **Contains**:
  - Admin controllers (Dashboard, User Management, Settings, etc.)
  - Admin models (Admin, AdminRole, AdminNotification, etc.)
  - Admin authentication
  - Admin routes and views

### 3. **User Module** (`Modules/User`)
- **Purpose**: User-facing functionality
- **Contains**:
  - User controllers (Dashboard, Profile, Transactions, etc.)
  - User models (User, UserProfile, UserWallet, etc.)
  - User authentication and authorization
  - User routes and views

### 4. **PaymentGateway Module** (`Modules/PaymentGateway`)
- **Purpose**: Payment gateway integrations
- **Contains**:
  - Payment gateway traits (Stripe, PayPal, Razorpay, etc.)
  - Payment gateway controllers
  - Payment validation and processing logic
  - Payment gateway configuration

### 5. **Payscribe Module** (`Modules/Payscribe`)
- **Purpose**: Payscribe API integration
- **Contains**:
  - Payscribe API controllers
  - Payscribe helpers (Bill payments, Virtual cards, etc.)
  - Payscribe models and services
  - Payscribe routes

### 6. **VirtualCard Module** (`Modules/VirtualCard`)
- **Purpose**: Virtual card management
- **Contains**:
  - Virtual card controllers
  - Virtual card models
  - Virtual card services
  - Strowallet integration

### 7. **Support Module** (`Modules/Support`)
- **Purpose**: Customer support system
- **Contains**:
  - Support ticket controllers
  - Support chat functionality
  - Support models (UserSupportTicket, UserSupportChat, etc.)
  - Support routes and views

### 8. **Frontend Module** (`Modules/Frontend`)
- **Purpose**: Public-facing website
- **Contains**:
  - Frontend controllers (Index, Announcements)
  - Frontend models (Announcement, ContactRequest, etc.)
  - Public routes and views

### 9. **P2P Module** (`Modules/P2P`)
- **Purpose**: Peer-to-peer trading functionality
- **Contains**:
  - P2P order controllers
  - P2P trader and taker models
  - P2P order management
  - P2P routes and views

### 10. **Savings Module** (`Modules/Savings`)
- **Purpose**: Savings account and interest management
- **Contains**:
  - Savings controllers
  - Savings models (Savings, SavingsTargets, SavingsTransaction)
  - Interest calculation logic
  - Savings routes and views

### 11. **GiftCard Module** (`Modules/GiftCard`)
- **Purpose**: Gift card management and transactions
- **Contains**:
  - Gift card controllers
  - Gift card models
  - Gift card purchase and redemption logic
  - Gift card routes and views

### 12. **Transaction Module** (`Modules/Transaction`)
- **Purpose**: All kinds of transaction management
- **Contains**:
  - Transaction controllers
  - Transaction models (Transaction, TransactionMethod, TransactionDevice)
  - Transaction history and statements
  - Transaction routes and views

### 13. **Collection Module** (`Modules/Collection`)
- **Purpose**: Fiat currency send and receive operations
- **Contains**:
  - Collection controllers
  - Collection models
  - Fiat transfer logic
  - Collection routes and views

### 14. **Trade Module** (`Modules/Trade`)
- **Purpose**: Cryptocurrency buy and sell features
- **Contains**:
  - Trade controllers
  - Trade models
  - Crypto trading logic
  - Order book management
  - Trade routes and views

### 15. **KYC Module** (`Modules/KYC`)
- **Purpose**: Know Your Customer verification
- **Contains**:
  - KYC controllers
  - KYC models (UserKycData, SetupKyc)
  - KYC verification logic
  - Identity verification services
  - KYC routes and views

### 16. **BillPayments Module** (`Modules/BillPayments`)
- **Purpose**: Bill payment services (electricity, cable TV, internet, etc.)
- **Contains**:
  - Bill payment controllers
  - Bill payment models
  - Payment processing logic
  - Integration with payment providers
  - Bill payment routes and views

### 17. **Wallet Module** (`Modules/Wallet`)
- **Purpose**: Wallet and balance management
- **Contains**:
  - Wallet controllers
  - Wallet models (UserWallet, LockedFund, etc.)
  - Deposit/withdraw workflows
  - Wallet transfer logic
  - Wallet routes and views

### 18. **Auth Module** (`Modules/Auth`)
- **Purpose**: Authentication flows for users and admins
- **Contains**:
  - Auth controllers (login, registration, password reset)
  - Auth services for OTP/2FA
  - Auth routes and views
  - Middleware for guards

### 19. **Referral Module** (`Modules/Referral`)
- **Purpose**: Referral program management
- **Contains**:
  - Referral controllers
  - Referral models (referral links, referral rewards)
  - Referral tracking logic
  - Referral routes and views

### 20. **AppConfiguration Module** (`Modules/AppConfiguration`)
- **Purpose**: Application-wide configuration management
- **Contains**:
  - Configuration controllers (basic settings, email, notifications)
  - Configuration models (BasicSettings, AppSettings)
  - Feature toggle logic
  - App configuration routes and views

## Module Directory Structure

Each module follows this structure:

```
Modules/
└── [ModuleName]/
    ├── app/
    │   ├── Http/
    │   │   ├── Controllers/
    │   │   ├── Middleware/
    │   │   └── Requests/
    │   ├── Models/
    │   ├── Providers/
    │   │   ├── [ModuleName]ServiceProvider.php
    │   │   ├── EventServiceProvider.php
    │   │   └── RouteServiceProvider.php
    │   ├── Services/
    │   ├── Traits/
    │   └── Helpers/
    ├── config/
    │   └── config.php
    ├── database/
    │   ├── migrations/
    │   ├── seeders/
    │   └── factories/
    ├── resources/
    │   ├── views/
    │   └── assets/
    ├── routes/
    │   ├── web.php
    │   └── api.php
    ├── tests/
    ├── module.json
    └── composer.json
```

## Namespace Convention

All modules follow this namespace pattern:
- **Service Providers**: `Modules\[ModuleName]\app\Providers`
- **Controllers**: `Modules\[ModuleName]\app\Http\Controllers`
- **Models**: `Modules\[ModuleName]\app\Models`
- **Services**: `Modules\[ModuleName]\app\Services`

## Module Configuration

### module.json
Each module has a `module.json` file that defines:
- Module name and alias
- Service providers
- Priority (load order)
- Files to load

### Service Providers
Each module has three service providers:
1. **Main Service Provider** (`[ModuleName]ServiceProvider`): Registers routes, views, config, migrations
2. **Event Service Provider**: Handles events and listeners
3. **Route Service Provider**: Defines web and API routes

## Working with Modules

### Creating a New Module

```bash
php artisan module:make ModuleName
php fix-module-namespaces.php ModuleName
```

### Listing Modules

```bash
php artisan module:list
```

### Enabling/Disabling Modules

```bash
php artisan module:enable ModuleName
php artisan module:disable ModuleName
```

### Generating Module Components

```bash
# Generate a controller
php artisan module:make-controller ControllerName ModuleName

# Generate a model
php artisan module:make-model ModelName ModuleName

# Generate a migration
php artisan module:make-migration create_table_name ModuleName

# Generate a seeder
php artisan module:make-seeder SeederName ModuleName
```

### Publishing Module Assets

```bash
php artisan module:publish ModuleName
```

## Migration Strategy

The existing codebase structure in `app/` is still functional. To migrate code to modules:

1. **Identify the feature** (e.g., Admin, User, Payment Gateway)
2. **Move controllers** from `app/Http/Controllers/[Feature]/` to `Modules/[Feature]/app/Http/Controllers/`
3. **Move models** from `app/Models/` to `Modules/[Feature]/app/Models/`
4. **Move routes** from `routes/[feature].php` to `Modules/[Feature]/routes/web.php` or `api.php`
5. **Update namespaces** in all moved files
6. **Update imports** in other files that reference the moved classes

## Best Practices

1. **Keep modules independent**: Each module should be self-contained with minimal dependencies on other modules
2. **Use Core module for shared code**: Place common functionality in the Core module
3. **Follow PSR-4 autoloading**: Ensure all classes follow proper namespace conventions
4. **Module-specific routes**: Keep routes within module route files
5. **Module-specific views**: Use module view namespaces (e.g., `admin::dashboard`)
6. **Module migrations**: Keep migrations within module directories

## View Namespaces

Access module views using the module alias:

```php
// In controllers
return view('admin::dashboard');
return view('user::profile');
return view('frontend::index');
```

## Route Naming

Module routes are automatically prefixed. Access them using:

```php
// In views or redirects
route('admin.dashboard');
route('user.profile');
```

## Configuration

Module configurations are automatically loaded. Access them using:

```php
config('admin.some_setting');
config('user.some_setting');
```

## Dependencies

Modules can depend on other modules. Declare dependencies in `module.json`:

```json
{
  "name": "ModuleName",
  "dependencies": ["Core", "Admin"]
}
```

## Troubleshooting

### Module not loading?
1. Check `module.json` has correct provider namespace
2. Run `composer dump-autoload`
3. Check module is enabled: `php artisan module:list`
4. Clear cache: `php artisan optimize:clear`

### Namespace errors?
- Ensure all namespaces match the file structure
- Run `php fix-module-namespaces.php ModuleName` if needed
- Check `composer.json` has `Modules\\` in autoload

### Routes not working?
- Check `RouteServiceProvider` in the module
- Verify routes are registered in `map()` method
- Check middleware is correctly applied

## Next Steps

1. **Gradually migrate existing code** from `app/` to appropriate modules
2. **Organize shared code** into the Core module
3. **Update imports** throughout the codebase
4. **Test each module** independently
5. **Document module-specific APIs**

## Module Status

All modules are currently **enabled** and ready for use:
- ✅ Core
- ✅ Admin
- ✅ User
- ✅ PaymentGateway
- ✅ Payscribe
- ✅ VirtualCard
- ✅ Support
- ✅ Frontend
- ✅ P2P
- ✅ Savings
- ✅ GiftCard
- ✅ Transaction
- ✅ Collection
- ✅ Trade
- ✅ KYC
- ✅ BillPayments
- ✅ Wallet
- ✅ Auth
- ✅ Referral
- ✅ AppConfiguration

---

*For more information, refer to the [nwidart/laravel-modules documentation](https://nwidart.com/laravel-modules/)*

