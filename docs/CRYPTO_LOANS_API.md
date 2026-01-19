# Crypto Loans API Documentation

## Overview
The Crypto Loans API allows users to lend and borrow crypto assets (USDT, USDC, BTC) with automated matching and collateralization.

## Base URL
```
/api/v1/loans
```

## Authentication
All endpoints require authentication via Bearer token (Laravel Passport).

---

## Endpoints

### 1. Create Lending Offer
**POST** `/lend`

Create a new lending offer to earn interest.

**Request Body:**
```json
{
  "asset": "USDT",
  "amount": 1000,
  "duration_days": 30
}
```

**Parameters:**
- `asset` (string, required): Asset to lend. Options: `USDT`, `USDC`, `BTC`
- `amount` (number, required): Amount to lend (minimum: 1)
- `duration_days` (integer, required): Loan duration. Options: `30`, `60`, `90`, `180`

**Response:**
```json
{
  "success": true,
  "message": "Lending offer created successfully",
  "data": {
    "id": 1,
    "user_id": 123,
    "asset": "USDT",
    "amount": "1000.00000000",
    "remaining_amount": "1000.00000000",
    "min_interest_rate": "5.00",
    "duration_days": 30,
    "status": "pending",
    "created_at": "2026-01-19T20:00:00.000000Z"
  }
}
```

---

### 2. Get My Lending Offers
**GET** `/my-lending-offers`

Retrieve all lending offers created by the authenticated user.

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "user_id": 123,
      "asset": "USDT",
      "amount": "1000.00000000",
      "remaining_amount": "1000.00000000",
      "min_interest_rate": "5.00",
      "duration_days": 30,
      "status": "pending",
      "created_at": "2026-01-19T20:00:00.000000Z"
    }
  ]
}
```

---

### 3. Cancel Lending Offer
**POST** `/cancel-offer/{offerId}`

Cancel a pending lending offer and unlock funds.

**URL Parameters:**
- `offerId` (integer): ID of the lending offer to cancel

**Response:**
```json
{
  "success": true,
  "message": "Lending offer cancelled successfully",
  "data": {
    "id": 1,
    "status": "cancelled"
  }
}
```

---

### 4. Calculate Collateral
**POST** `/calculate-collateral`

Calculate required collateral amount for a loan.

**Request Body:**
```json
{
  "asset": "USDT",
  "amount": 1000,
  "collateral_asset": "BTC"
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "loan_amount": 1000,
    "loan_asset": "USDT",
    "collateral_amount": "0.02666667",
    "collateral_asset": "BTC",
    "collateralization_ratio": "125%"
  }
}
```

---

### 5. Create Borrow Request
**POST** `/borrow`

Create a borrow request with collateral.

**Request Body:**
```json
{
  "asset": "USDT",
  "amount": 1000,
  "duration_days": 30,
  "collateral_asset": "BTC"
}
```

**Parameters:**
- `asset` (string, required): Asset to borrow
- `amount` (number, required): Amount to borrow
- `duration_days` (integer, required): Loan duration
- `collateral_asset` (string, required): Asset to use as collateral

**Response (Matched):**
```json
{
  "success": true,
  "message": "Loan disbursement successful",
  "data": {
    "id": 1,
    "reference_code": "LOAN-ABC123XYZ",
    "borrower_id": 123,
    "lender_id": 456,
    "asset": "USDT",
    "amount": "1000.00000000",
    "collateral_asset": "BTC",
    "collateral_amount": "0.02666667",
    "interest_rate": "5.00",
    "total_interest": "50.00000000",
    "start_date": "2026-01-19T20:00:00.000000Z",
    "due_date": "2026-02-18T20:00:00.000000Z",
    "status": "active"
  }
}
```

**Response (Pending):**
```json
{
  "success": true,
  "message": "Borrow request created. Waiting for matching lender.",
  "data": {
    "id": 1,
    "user_id": 123,
    "asset": "USDT",
    "amount": "1000.00000000",
    "status": "pending"
  }
}
```

---

### 6. Get My Borrow Requests
**GET** `/my-borrow-requests`

Retrieve all borrow requests created by the authenticated user.

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "user_id": 123,
      "asset": "USDT",
      "amount": "1000.00000000",
      "duration_days": 30,
      "collateral_asset": "BTC",
      "collateral_amount": "0.02666667",
      "status": "matched",
      "created_at": "2026-01-19T20:00:00.000000Z"
    }
  ]
}
```

---

### 7. Get My Loans (As Borrower)
**GET** `/my-loans/borrower`

Retrieve all loans where the authenticated user is the borrower.

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "reference_code": "LOAN-ABC123XYZ",
      "borrower_id": 123,
      "lender_id": 456,
      "asset": "USDT",
      "amount": "1000.00000000",
      "collateral_asset": "BTC",
      "collateral_amount": "0.02666667",
      "interest_rate": "5.00",
      "total_interest": "50.00000000",
      "start_date": "2026-01-19T20:00:00.000000Z",
      "due_date": "2026-02-18T20:00:00.000000Z",
      "status": "active",
      "lender": {
        "id": 456,
        "username": "lender_user",
        "email": "lender@example.com"
      }
    }
  ]
}
```

---

### 8. Get My Loans (As Lender)
**GET** `/my-loans/lender`

Retrieve all loans where the authenticated user is the lender.

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "reference_code": "LOAN-ABC123XYZ",
      "borrower_id": 123,
      "lender_id": 456,
      "asset": "USDT",
      "amount": "1000.00000000",
      "interest_rate": "5.00",
      "total_interest": "50.00000000",
      "status": "active",
      "borrower": {
        "id": 123,
        "username": "borrower_user",
        "email": "borrower@example.com"
      }
    }
  ]
}
```

---

### 9. Get Loan Details
**GET** `/details/{loanId}`

Get detailed information about a specific loan.

**URL Parameters:**
- `loanId` (integer): ID of the loan

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "reference_code": "LOAN-ABC123XYZ",
    "borrower_id": 123,
    "lender_id": 456,
    "asset": "USDT",
    "amount": "1000.00000000",
    "collateral_asset": "BTC",
    "collateral_amount": "0.02666667",
    "interest_rate": "5.00",
    "total_interest": "50.00000000",
    "start_date": "2026-01-19T20:00:00.000000Z",
    "due_date": "2026-02-18T20:00:00.000000Z",
    "status": "active",
    "borrower": { ... },
    "lender": { ... },
    "offer": { ... },
    "request": { ... }
  }
}
```

---

### 10. Repay Loan
**POST** `/repay/{loanId}`

Repay an active loan (principal + interest).

**URL Parameters:**
- `loanId` (integer): ID of the loan to repay

**Response:**
```json
{
  "success": true,
  "message": "Loan repaid successfully",
  "data": {
    "id": 1,
    "reference_code": "LOAN-ABC123XYZ",
    "status": "completed",
    "completed_at": "2026-02-15T10:30:00.000000Z"
  }
}
```

---

## Business Rules

### Interest Rate
- **Fixed Rate**: 5% Monthly
- Interest is calculated based on loan duration
- Example: 1000 USDT for 30 days = 50 USDT interest

### Collateralization
- **Ratio**: 125% (Collateral value must be 25% above loan amount)
- Collateral is auto-calculated based on real-time prices
- User cannot manually input collateral amount

### Loan Restrictions
- Users can only have **ONE active loan** as a borrower at a time
- Users can create multiple lending offers
- Lending offers can be cancelled only if status is `pending`

### Matching Logic
- System automatically matches borrow requests with lending offers
- Matching criteria:
  - Same asset
  - Same duration
  - Sufficient available amount
- FIFO (First In, First Out) matching

### Statuses

**Lending Offer:**
- `pending`: Waiting for match
- `matched`: Fully matched with borrower(s)
- `cancelled`: Cancelled by lender
- `completed`: All loans repaid

**Borrow Request:**
- `pending`: Waiting for match
- `matched`: Matched with lender
- `cancelled`: Cancelled (if allowed)
- `rejected`: System rejected

**Loan:**
- `active`: Loan is active
- `completed`: Loan repaid
- `overdue`: Past due date
- `liquidated`: Collateral liquidated

---

## Error Responses

### Insufficient Balance
```json
{
  "success": false,
  "message": "Insufficient USDT balance. Available: 500.00"
}
```

### Active Loan Exists
```json
{
  "success": false,
  "message": "You already have an active outstanding loan. Please repay it before borrowing again."
}
```

### Validation Error
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "asset": ["The asset field is required."],
    "amount": ["The amount must be at least 1."]
  }
}
```

---

## Admin Endpoints

### Get All Loans
**GET** `/admin/loans`

Query Parameters:
- `status`: Filter by status
- `asset`: Filter by asset
- `search`: Search by reference code

### Get Statistics
**GET** `/admin/loans/statistics`

Returns comprehensive loan statistics.

### Force Liquidate
**POST** `/admin/loans/{id}/liquidate`

Admin action to force liquidate a loan.
