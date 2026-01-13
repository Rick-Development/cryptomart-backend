# Graph API - Deposit, Conversion & Withdrawal Examples

## Deposits

### 1. Create Deposit Address (Crypto)

**Endpoint:** `POST /api/user/graph/deposit/address`

**Body:**
```json
{
  "wallet_id": "acc_xyz789",
  "currency": "USDT"
}
```

**Response:**
```json
{
  "status": true,
  "message": ["Deposit address created successfully"],
  "data": {
    "address": {
      "id": "addr_123",
      "address": "0x1234567890abcdef...",
      "currency": "USDT",
      "network": "TRC20"
    }
  }
}
```

---

## Currency Conversion

### 1. Get Exchange Rate

**Endpoint:** `GET /api/user/graph/exchange-rate`

**Query Params:** `?from=USD&to=NGN`

**Response:**
```json
{
  "status": true,
  "message": ["Exchange rate fetched successfully"],
  "data": {
    "rate": {
      "from": "USD",
      "to": "NGN",
      "rate": 1500.00
    }
  }
}
```

---

## Withdrawals

### 1. Withdraw USD (Direct)

Use this for:
- Stablecoin withdrawals (USDT, USDC)
- International Wire Transfers

**Endpoint:** `POST /api/user/graph/withdraw/usd`

**Body:**
```json
{
  "wallet_id": "acc_xyz789",
  "destination_id": "dest_usd_123",
  "amount": 500,
  "narration": "Withdrawal to USDT wallet"
}
```

**Response:**
```json
{
  "status": true,
  "message": ["USD withdrawal initiated successfully"],
  "data": {
    "payout": {
      "id": "payout_123",
      "amount": 500,
      "currency": "USD",
      "status": "processing",
      "reference": "WD_USD_1736640000_1"
    }
  }
}
```

### 2. Withdraw NGN (Convert USD -> NGN)

Use this for withdrawals to Nigerian bank accounts. This endpoint automatically:
1. Converts your USD to NGN
2. Withdraws the NGN to the bank account

**Endpoint:** `POST /api/user/graph/withdraw/ngn`

**Body:**
```json
{
  "wallet_id": "acc_xyz789",
  "destination_id": "dest_ngn_123",
  "usd_amount": 100,
  "narration": "Withdrawal to NGN Bank Account"
}
```

**Response:**
```json
{
  "status": true,
  "message": ["Conversion and withdrawal successful"],
  "data": {
    "conversion": {
      "id": "conv_123",
      "from_amount": 100,
      "from_currency": "USD",
      "to_amount": 150000,
      "to_currency": "NGN",
      "rate": 1500.00
    },
    "payout": {
      "id": "payout_456",
      "amount": 150000,
      "currency": "NGN",
      "status": "processing",
      "reference": "WD_NGN_1736640000_1"
    }
  }
}
```

---

## Payout Destinations

### 1. Create USD Destination (Crypto Wallet)

**Endpoint:** `POST /api/user/graph/payout-destination`

**Body:**
```json
{
  "type": "crypto_address",
  "currency": "USD",
  "details": {
    "address": "0x1234567890abcdef...",
    "network": "TRC20",
    "currency": "USDT"
  }
}
```

### 2. Create NGN Destination (Bank Account)

**Endpoint:** `POST /api/user/graph/payout-destination`

**Body:**
```json
{
  "type": "bank_account",
  "currency": "NGN",
  "details": {
    "bank_code": "058",
    "account_number": "0123456789",
    "account_name": "JOHN DOE"
  }
}
```
