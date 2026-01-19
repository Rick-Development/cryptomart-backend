# Crypto Loans API - cURL Commands

## Setup
Replace `{{base_url}}` with your actual base URL (e.g., `http://localhost:8000/api/v1/loans`)
Replace `YOUR_AUTH_TOKEN` with your actual Bearer token

```bash
# Set environment variables
export BASE_URL="http://localhost:8000/api/v1/loans"
export TOKEN="YOUR_AUTH_TOKEN"
```

---

## LENDING ENDPOINTS

### 1. Create Lending Offer
```bash
curl -X POST "{{base_url}}/lend" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "asset": "USDT",
    "amount": 1000,
    "duration_days": 30
  }'
```

**With different assets:**
```bash
# Lend USDC
curl -X POST "{{base_url}}/lend" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "asset": "USDC",
    "amount": 5000,
    "duration_days": 60
  }'

# Lend BTC
curl -X POST "{{base_url}}/lend" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "asset": "BTC",
    "amount": 0.5,
    "duration_days": 90
  }'
```

---

### 2. Get My Lending Offers
```bash
curl -X GET "{{base_url}}/my-lending-offers" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

---

### 3. Cancel Lending Offer
```bash
# Replace {offerId} with actual offer ID
curl -X POST "{{base_url}}/cancel-offer/1" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

---

## BORROWING ENDPOINTS

### 4. Calculate Collateral
```bash
curl -X POST "{{base_url}}/calculate-collateral" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "asset": "USDT",
    "amount": 1000,
    "collateral_asset": "BTC"
  }'
```

**Different collateral assets:**
```bash
# Using SOL as collateral
curl -X POST "{{base_url}}/calculate-collateral" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "asset": "USDT",
    "amount": 2000,
    "collateral_asset": "SOL"
  }'

# Using BNB as collateral
curl -X POST "{{base_url}}/calculate-collateral" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "asset": "BTC",
    "amount": 0.1,
    "collateral_asset": "BNB"
  }'
```

---

### 5. Create Borrow Request
```bash
curl -X POST "{{base_url}}/borrow" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "asset": "USDT",
    "amount": 1000,
    "duration_days": 30,
    "collateral_asset": "BTC"
  }'
```

**Different scenarios:**
```bash
# Borrow USDC with SOL collateral
curl -X POST "{{base_url}}/borrow" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "asset": "USDC",
    "amount": 5000,
    "duration_days": 60,
    "collateral_asset": "SOL"
  }'

# Borrow BTC with BNB collateral
curl -X POST "{{base_url}}/borrow" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "asset": "BTC",
    "amount": 0.5,
    "duration_days": 90,
    "collateral_asset": "BNB"
  }'
```

---

### 6. Get My Borrow Requests
```bash
curl -X GET "{{base_url}}/my-borrow-requests" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

---

## MY LOANS ENDPOINTS

### 7. Get My Loans (As Borrower)
```bash
curl -X GET "{{base_url}}/my-loans/borrower" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

---

### 8. Get My Loans (As Lender)
```bash
curl -X GET "{{base_url}}/my-loans/lender" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

---

### 9. Get Loan Details
```bash
# Replace {loanId} with actual loan ID
curl -X GET "{{base_url}}/details/1" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

---

### 10. Repay Loan
```bash
# Replace {loanId} with actual loan ID
curl -X POST "{{base_url}}/repay/1" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

---

## COMPLETE WORKFLOW EXAMPLES

### Example 1: Lender Workflow
```bash
# Step 1: Create a lending offer
curl -X POST "{{base_url}}/lend" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "asset": "USDT",
    "amount": 10000,
    "duration_days": 30
  }'

# Step 2: Check my lending offers
curl -X GET "{{base_url}}/my-lending-offers" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"

# Step 3: View active loans (where I'm the lender)
curl -X GET "{{base_url}}/my-loans/lender" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"

# Step 4: Cancel a pending offer (if needed)
curl -X POST "{{base_url}}/cancel-offer/1" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

---

### Example 2: Borrower Workflow
```bash
# Step 1: Calculate required collateral
curl -X POST "{{base_url}}/calculate-collateral" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "asset": "USDT",
    "amount": 5000,
    "collateral_asset": "BTC"
  }'

# Step 2: Create borrow request
curl -X POST "{{base_url}}/borrow" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "asset": "USDT",
    "amount": 5000,
    "duration_days": 30,
    "collateral_asset": "BTC"
  }'

# Step 3: Check my active loans
curl -X GET "{{base_url}}/my-loans/borrower" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"

# Step 4: Get loan details
curl -X GET "{{base_url}}/details/1" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"

# Step 5: Repay the loan
curl -X POST "{{base_url}}/repay/1" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

---

## TESTING TIPS

### 1. Pretty Print JSON Responses
Add `| jq` to the end of curl commands:
```bash
curl -X GET "{{base_url}}/my-lending-offers" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" | jq
```

### 2. Save Response to File
```bash
curl -X GET "{{base_url}}/my-loans/borrower" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" \
  -o my_loans.json
```

### 3. Verbose Output (for debugging)
```bash
curl -v -X POST "{{base_url}}/lend" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "asset": "USDT",
    "amount": 1000,
    "duration_days": 30
  }'
```

### 4. Test Without Token (Should Return 401)
```bash
curl -X GET "{{base_url}}/my-lending-offers" \
  -H "Accept: application/json"
```

---

## EXPECTED RESPONSES

### Success Response (Create Lending Offer)
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

### Error Response (Insufficient Balance)
```json
{
  "success": false,
  "message": "Insufficient USDT balance. Available: 500.00"
}
```

### Error Response (Validation Failed)
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

## NOTES

- All amounts are in the asset's base unit (e.g., USDT, not cents)
- Interest rate is fixed at **5% monthly**
- Collateral ratio is **125%** (25% above loan amount)
- Only one active loan per borrower at a time
- Lending offers can only be cancelled if status is 'pending'
- Collateral is automatically calculated and locked
