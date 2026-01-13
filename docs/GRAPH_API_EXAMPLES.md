# Graph API - Postman Examples

## 1. Create Graph Customer

**Endpoint:** `POST /api/user/graph/create-customer`

**Headers:**
```
Authorization: Bearer {your_token}
Content-Type: application/json
```

**Body:**
```json
{
  "dob": "1990-01-15",
  "address": "123 Main Street",
  "city": "Lagos",
  "state": "Lagos",
  "zip_code": "100001",
  "id_number": "12345678901",
  "id_type": "NIN"
}
```

**Response:**
```json
{
  "status": true,
  "message": ["Customer created successfully"],
  "data": {
    "customer": {
      "id": 1,
      "user_id": 1,
      "graph_id": "per_abc123xyz",
      "kyc_status": "pending",
      "data": {...},
      "created_at": "2026-01-12T00:00:00.000000Z",
      "updated_at": "2026-01-12T00:00:00.000000Z"
    }
  }
}
```

---

## 2. Create USD Wallet

**Endpoint:** `POST /api/user/graph/create-wallet`

**Headers:**
```
Authorization: Bearer {your_token}
Content-Type: application/json
```

**Body:**
```json
{
  "currency": "USD"
}
```

**Response:**
```json
{
  "status": true,
  "message": ["Wallet created successfully"],
  "data": {
    "wallet": {
      "id": 1,
      "user_id": 1,
      "graph_customer_id": 1,
      "wallet_id": "acc_xyz789",
      "account_number": "1234567890",
      "currency": "USD",
      "balance": "0.00000000",
      "status": "active",
      "data": {...},
      "created_at": "2026-01-12T00:00:00.000000Z",
      "updated_at": "2026-01-12T00:00:00.000000Z"
    }
  }
}
```

---

## 3. Get User Wallets

**Endpoint:** `GET /api/user/graph/wallet`

**Headers:**
```
Authorization: Bearer {your_token}
```

**Response:**
```json
{
  "status": true,
  "message": ["Wallets fetched successfully"],
  "data": {
    "wallets": [
      {
        "id": 1,
        "user_id": 1,
        "wallet_id": "acc_xyz789",
        "account_number": "1234567890",
        "currency": "USD",
        "balance": "1500.50000000",
        "status": "active"
      }
    ]
  }
}
```

---

## 4. Get Wallet Transactions

**Endpoint:** `GET /api/user/graph/transactions?wallet_id=acc_xyz789`

**Headers:**
```
Authorization: Bearer {your_token}
```

**Response:**
```json
{
  "status": true,
  "message": ["Transactions fetched successfully"],
  "data": {
    "transactions": [
      {
        "id": "txn_123",
        "amount": 100.00,
        "currency": "USD",
        "type": "credit",
        "status": "completed",
        "created_at": "2026-01-12T00:00:00Z"
      }
    ]
  }
}
```
