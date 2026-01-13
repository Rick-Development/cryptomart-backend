# Deployment Commands for SafeHaven Integration

To deploy the SafeHaven sub-account and transfer integration, please run the following commands on the server:

### 1. Database Migrations
Run the migrations to update the `virtual_accounts` table, create the `banks` table, and add the KYC provider settings to `basic_settings`.
```bash
php artisan migrate
```

### 2. Bank List Synchronization
This command fetches the latest bank list from SafeHaven and populates the local `banks` table, which is required for outbound transfers.
```bash
php artisan safehaven:sync-banks
```

### 3. Environment Configuration
Ensure the following keys are added to your `.env` file:
```env
SAFE_HEAVEN_CLIENTID=your_client_id
SAFE_HEAVEN_CLIENT_ASSERTION=your_client_assertion
SAFE_HEAVEN_URL=https://api.safehavenmfb.com
```

### 4. Cache Clear (Optional but Recommended)
If you updated the config files, clear the config cache.
```bash
php artisan config:clear
php artisan cache:clear
```
