# Graph API - cURL Commands

Replace `YOUR_TOKEN` with your actual authentication token.

## 1. Currency Conversion

### Get Exchange Rate
```bash
curl -X GET "{{base_url}}/api/user/graph/exchange-rate?from=USD&to=NGN" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## 2. Withdrawals

### Withdraw USD (Direct)
```bash
curl -X POST "{{base_url}}/api/user/graph/withdraw/usd" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "wallet_id": "acc_xyz789",
    "destination_id": "dest_usd_123",
    "amount": 500,
    "narration": "Withdrawal to USDT wallet"
  }'
```

### Withdraw NGN (Convert USD -> NGN)
```bash
curl -X POST "{{base_url}}/api/user/graph/withdraw/ngn" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "wallet_id": "acc_xyz789",
    "destination_id": "dest_ngn_123",
    "usd_amount": 100,
    "narration": "Withdrawal to NGN Bank Account"
  }'
```

## 3. Payout Destinations

### Create USD Destination (Crypto)
```bash
curl -X POST "{{base_url}}/api/user/graph/payout-destination" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "type": "crypto_address",
    "currency": "USD",
    "details": {
      "address": "0x1234567890abcdef...",
      "network": "TRC20",
      "currency": "USDT"
    }
  }'
```

### Create NGN Destination (Bank)
```bash
curl -X POST "{{base_url}}/api/user/graph/payout-destination" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "type": "bank_account",
    "currency": "NGN",
    "details": {
      "bank_code": "058",
      "account_number": "0123456789",
      "account_name": "JOHN DOE"
    }
  }'
```

---

## 4. Other Commands

### Create Customer
```bash
curl -X POST "{{base_url}}/api/user/graph/create-customer" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"dob":"1990-01-15","address":"123 Main St","city":"Lagos","state":"Lagos","zip_code":"100001","id_number":"12345678901","id_type":"NIN"}'
```

### Create USD Wallet
```bash
curl -X POST "{{base_url}}/api/user/graph/create-wallet" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"currency":"USD"}'
```

### Get Wallets
```bash
curl -X GET "{{base_url}}/api/user/graph/wallet" \
  -H "Authorization: Bearer YOUR_TOKEN"
```
