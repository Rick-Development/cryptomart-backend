# P2P Marketplace API Documentation

## Base URL
```
https://your-domain.com/api/user/p2p
```

## Authentication
All endpoints require Bearer token authentication:
```
Authorization: Bearer {your_access_token}
```

---

## Response Format

### Success Response
```json
{
  "message": "Success message",
  "data": {...},
  "type": "success"
}
```

### Error Response
```json
{
  "message": "Error message",
  "data": null,
  "type": "error"
}
```

---

# Endpoints

## 1. Ads

### 1.1 Browse Ads
**GET** `/ads`

Browse all active ads with optional filters.

**Query Parameters:**
- `asset` (string, optional) - Filter by crypto asset (e.g., USDT, BTC)
- `fiat` (string, optional) - Filter by fiat currency (e.g., NGN, USD)
- `type` (string, optional) - Filter by type: `buy` or `sell`
- `payment_method_id` (integer, optional) - Filter by payment method ID
- `amount` (number, optional) - Filter ads that accept this amount

**Example Request:**
```bash
GET /api/user/p2p/ads?asset=USDT&fiat=NGN&type=sell&amount=50000
```

**Example Response:**
```json
{
  "message": "Ads fetched successfully",
  "data": {
    "ads": {
      "current_page": 1,
      "data": [
        {
          "id": 1,
          "user_id": 5,
          "type": "sell",
          "asset": "USDT",
          "fiat": "NGN",
          "price": "1500.00",
          "available_amount": "1000.00000000",
          "min_limit": "10000.00000000",
          "max_limit": "500000.00000000",
          "payment_method_ids": [1, 2],
          "terms": "Payment within 15 minutes",
          "status": "online",
          "user": {
            "id": 5,
            "username": "trader123",
            "p2p_user_stat": {
              "completion_rate": "98.50",
              "total_trades": 150,
              "rating": "4.80"
            }
          }
        }
      ],
      "per_page": 20,
      "total": 45
    }
  },
  "type": "success"
}
```

---

### 1.2 View Ad Details
**GET** `/ads/{id}`

Get detailed information about a specific ad.

**Example Response:**
```json
{
  "message": "Ad details",
  "data": {
    "ad": {
      "id": 1,
      "user_id": 5,
      "type": "sell",
      "asset": "USDT",
      "fiat": "NGN",
      "price_type": "fixed",
      "price": "1500.00",
      "total_amount": "5000.00000000",
      "available_amount": "3500.00000000",
      "min_limit": "10000.00000000",
      "max_limit": "500000.00000000",
      "payment_method_ids": [1, 2],
      "terms": "Payment within 15 minutes. No third-party payments.",
      "auto_reply": "Hello! Please complete payment within the time limit.",
      "time_limit": 15,
      "status": "online"
    }
  },
  "type": "success"
}
```

---

### 1.3 Create Ad
**POST** `/ads`

Create a new P2P ad. Requires KYC Level 2.

**Request Body:**
```json
{
  "type": "sell",
  "asset": "USDT",
  "fiat": "NGN",
  "price_type": "fixed",
  "price": 1500,
  "total_amount": 5000,
  "min_limit": 10000,
  "max_limit": 500000,
  "payment_method_ids": [1, 2],
  "terms": "Payment within 15 minutes",
  "auto_reply": "Hello! Please complete payment.",
  "time_limit": 15
}
```

**Validation Rules:**
- `type`: required, must be `buy` or `sell`
- `asset`: required, string, max 16 chars
- `fiat`: required, string, max 16 chars
- `price_type`: required, must be `fixed` or `floating`
- `price`: required, numeric, min 0
- `total_amount`: required, numeric, min 0
- `min_limit`: required, numeric, min 0
- `max_limit`: required, numeric, min 0
- `payment_method_ids`: required, array of existing payment method IDs
- `time_limit`: optional, integer, min 5, max 60 (minutes)

**Example Response:**
```json
{
  "message": "Ad created successfully. Pending approval.",
  "data": {
    "ad": {
      "id": 15,
      "status": "offline",
      "created_at": "2025-12-27T15:30:00.000000Z"
    }
  },
  "type": "success"
}
```

---

### 1.4 Toggle Ad Status
**POST** `/ads/{id}/toggle`

Toggle ad between online and offline.

**Example Response:**
```json
{
  "message": "Ad is now online",
  "data": {
    "ad": {
      "id": 15,
      "status": "online"
    }
  },
  "type": "success"
}
```

---

### 1.5 My Ads
**GET** `/my-ads`

Get all ads created by the authenticated user.

**Example Response:**
```json
{
  "message": "Your ads fetched",
  "data": {
    "ads": [
      {
        "id": 15,
        "type": "sell",
        "asset": "USDT",
        "status": "online",
        "available_amount": "5000.00000000"
      }
    ]
  },
  "type": "success"
}
```

---

## 2. Payment Methods

### 2.1 List Payment Methods
**GET** `/payment-methods`

Get all active payment methods for the authenticated user.

**Example Response:**
```json
{
  "message": "Payment methods fetched",
  "data": {
    "payment_methods": [
      {
        "id": 1,
        "name": "My GTBank Account",
        "provider": "GTBank",
        "details": {
          "acc_no": "0123456789",
          "acc_name": "John Doe",
          "bank_code": "058"
        },
        "status": "active"
      }
    ]
  },
  "type": "success"
}
```

---

### 2.2 Add Payment Method
**POST** `/payment-methods`

Add a new payment method.

**Request Body:**
```json
{
  "name": "My GTBank Account",
  "provider": "GTBank",
  "details": {
    "acc_no": "0123456789",
    "acc_name": "John Doe",
    "bank_code": "058"
  }
}
```

**Example Response:**
```json
{
  "message": "Payment method added",
  "data": {
    "payment_method": {
      "id": 2,
      "name": "My GTBank Account",
      "status": "active"
    }
  },
  "type": "success"
}
```

---

### 2.3 Update Payment Method
**PUT** `/payment-methods/{id}`

Update an existing payment method.

---

### 2.4 Delete Payment Method
**DELETE** `/payment-methods/{id}`

Delete a payment method.

---

## 3. Orders

### 3.1 Create Order
**POST** `/orders`

Create an order from an existing ad.

**Request Body:**
```json
{
  "ad_id": 1,
  "amount": 100
}
```

**Example Response:**
```json
{
  "message": "Order created successfully",
  "data": {
    "order": {
      "id": 50,
      "ad_id": 1,
      "maker_id": 5,
      "taker_id": 10,
      "type": "sell",
      "asset": "USDT",
      "amount": "100.00000000",
      "price": "1500.00",
      "total": "150000.00",
      "status": "accepted",
      "payment_deadline": "2025-12-27T16:00:00.000000Z"
    }
  },
  "type": "success"
}
```

---

### 3.2 My Orders
**GET** `/my-orders`

Get all orders for the authenticated user.

**Example Response:**
```json
{
  "message": "Your orders",
  "data": {
    "orders": [
      {
        "id": 50,
        "status": "accepted",
        "amount": "100.00000000",
        "total": "150000.00",
        "maker": {
          "id": 5,
          "username": "seller123"
        },
        "taker": {
          "id": 10,
          "username": "buyer456"
        }
      }
    ]
  },
  "type": "success"
}
```

---

### 3.3 Mark Payment Sent
**POST** `/orders/{uid}/mark-paid`

Buyer marks that payment has been sent.

**Example Response:**
```json
{
  "message": "Payment marked as sent. Waiting for seller confirmation.",
  "data": {
    "order": {
      "id": 50,
      "status": "paid"
    }
  },
  "type": "success"
}
```

---

### 3.4 Release Crypto
**POST** `/orders/{uid}/release`

Seller releases crypto to buyer after confirming payment.

**Example Response:**
```json
{
  "message": "Crypto released successfully",
  "data": {
    "order": {
      "id": 50,
      "status": "completed"
    }
  },
  "type": "success"
}
```

---

### 3.5 Raise Dispute
**POST** `/orders/{uid}/appeal`

Raise a dispute for an order.

**Request Body:**
```json
{
  "reason": "Payment sent but seller not responding",
  "evidence": [
    "https://example.com/payment-proof.jpg"
  ]
}
```

**Example Response:**
```json
{
  "message": "Dispute raised successfully. Admin will review.",
  "data": {
    "order": {
      "id": 50,
      "appeal_status": "pending"
    }
  },
  "type": "success"
}
```

---

## 4. Chat

### 4.1 Get Chat Messages
**GET** `/orders/{uid}/chat`

Get all chat messages for an order.

**Example Response:**
```json
{
  "message": "Chat messages",
  "data": {
    "messages": [
      {
        "id": 1,
        "order_id": 50,
        "sender_id": 10,
        "message": "Payment sent",
        "attachment": null,
        "is_read": true,
        "created_at": "2025-12-27T15:45:00.000000Z",
        "sender": {
          "id": 10,
          "username": "buyer456"
        }
      }
    ]
  },
  "type": "success"
}
```

---

### 4.2 Send Message
**POST** `/orders/{uid}/chat`

Send a chat message.

**Request Body:**
```json
{
  "message": "Payment sent. Please check.",
  "attachment": "https://example.com/proof.jpg"
}
```

**Example Response:**
```json
{
  "message": "Message sent",
  "data": {
    "message": {
      "id": 2,
      "message": "Payment sent. Please check.",
      "created_at": "2025-12-27T15:50:00.000000Z"
    }
  },
  "type": "success"
}
```

---

## 5. Disclaimers

### 5.1 Get All Disclaimers
**GET** `/disclaimers`

Get all active disclaimers.

**Example Response:**
```json
{
  "message": "Disclaimers fetched",
  "data": {
    "disclaimers": [
      {
        "id": 1,
        "key": "welcome",
        "title": "Welcome to the P2P Marketplace",
        "content": "Welcome to our Peer-to-Peer...",
        "type": "info",
        "requires_acceptance": false
      },
      {
        "id": 2,
        "key": "first_time",
        "title": "Important Notice – Please Read Carefully",
        "content": "This P2P marketplace...",
        "type": "warning",
        "requires_acceptance": true
      }
    ]
  },
  "type": "success"
}
```

---

### 5.2 Get Specific Disclaimer
**GET** `/disclaimers/{key}`

Get a specific disclaimer by key.

**Example Response:**
```json
{
  "message": "Disclaimer details",
  "data": {
    "disclaimer": {
      "id": 2,
      "key": "first_time",
      "title": "Important Notice",
      "content": "...",
      "requires_acceptance": true
    },
    "has_accepted": false
  },
  "type": "success"
}
```

---

### 5.3 Accept Disclaimer
**POST** `/disclaimers/{key}/accept`

Accept a disclaimer that requires acceptance.

**Example Response:**
```json
{
  "message": "Disclaimer accepted",
  "data": {
    "acceptance": {
      "id": 1,
      "user_id": 10,
      "disclaimer_id": 2,
      "ip_address": "192.168.1.1",
      "created_at": "2025-12-27T16:00:00.000000Z"
    }
  },
  "type": "success"
}
```

---

## Error Codes

| Status Code | Description |
|-------------|-------------|
| 200 | Success |
| 201 | Created |
| 400 | Bad Request / Validation Error |
| 401 | Unauthorized |
| 403 | Forbidden (e.g., KYC required) |
| 404 | Not Found |
| 500 | Server Error |

---

## Common Error Responses

### Validation Error
```json
{
  "message": "Validation Error",
  "data": [
    "The amount field is required.",
    "The amount must be at least 10000."
  ],
  "type": "error"
}
```

### Insufficient Balance
```json
{
  "message": "Insufficient balance",
  "data": null,
  "type": "error"
}
```

### KYC Required
```json
{
  "message": "KYC Level 2 required to create ads",
  "data": null,
  "type": "error"
}
```

---

## Disclaimer Keys Reference

| Key | Title | Requires Acceptance |
|-----|-------|---------------------|
| `welcome` | Welcome to the P2P Marketplace | No |
| `first_time` | Important Notice – Please Read Carefully | Yes |
| `buyer_confirm` | Buyer Confirmation Required | No |
| `seller_confirm` | Seller Confirmation Required | No |
| `payment_warning` | Payment Confirmation Warning | No |
| `dispute_notice` | Dispute Resolution Notice | No |
| `anti_fraud` | Security Notice | No |
| `merchant_notice` | Advertiser Responsibility Notice | No |
| `compliance` | Compliance & Jurisdiction Disclaimer | No |
| `payment_received_warning` | Critical: Verify Payment Before Release | No |

---

## Best Practices

1. **Always check disclaimers** before first trade
2. **Accept required disclaimers** (`first_time`) before creating ads
3. **Verify payment details** match seller's registered info
4. **Use chat** for all communication (never off-platform)
5. **Upload evidence** when raising disputes
6. **Check user reputation** (completion_rate, rating) before trading
7. **Respect time limits** - orders auto-cancel if payment not marked
