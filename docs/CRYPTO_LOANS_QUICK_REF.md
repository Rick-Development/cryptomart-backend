# Crypto Loans API - Quick Reference

## Import to Postman
1. Open Postman
2. Click **Import**
3. Select `Crypto_Loans_API.postman_collection.json`
4. Update the `token` variable with your auth token
5. Update the `base_url` if needed (default: `http://localhost:8000/api/v1/loans`)

## Quick cURL Setup

### Set Environment Variables
```bash
export BASE_URL="http://localhost:8000/api/v1/loans"
export TOKEN="YOUR_AUTH_TOKEN_HERE"
```

### Replace in Commands
Or use find/replace in the curl commands:
- Replace `{{base_url}}` with `http://localhost:8000/api/v1/loans`
- Replace `$TOKEN` with your actual token

## Quick Test Commands

### 1. Create Lending Offer (USDT)
```bash
curl -X POST "{{base_url}}/lend" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"asset":"USDT","amount":1000,"duration_days":30}'
```

### 2. Calculate Collateral
```bash
curl -X POST "{{base_url}}/calculate-collateral" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"asset":"USDT","amount":1000,"collateral_asset":"BTC"}'
```

### 3. Create Borrow Request
```bash
curl -X POST "{{base_url}}/borrow" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"asset":"USDT","amount":1000,"duration_days":30,"collateral_asset":"BTC"}'
```

### 4. View My Loans (Borrower)
```bash
curl -X GET "{{base_url}}/my-loans/borrower" \
  -H "Authorization: Bearer $TOKEN"
```

### 5. Repay Loan
```bash
curl -X POST "{{base_url}}/repay/1" \
  -H "Authorization: Bearer $TOKEN"
```

## All Endpoints Summary

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/lend` | Create lending offer |
| GET | `/my-lending-offers` | Get my lending offers |
| POST | `/cancel-offer/{id}` | Cancel pending offer |
| POST | `/calculate-collateral` | Calculate required collateral |
| POST | `/borrow` | Create borrow request |
| GET | `/my-borrow-requests` | Get my borrow requests |
| GET | `/my-loans/borrower` | Get loans as borrower |
| GET | `/my-loans/lender` | Get loans as lender |
| GET | `/details/{id}` | Get loan details |
| POST | `/repay/{id}` | Repay active loan |

## Key Parameters

### Assets (Lend/Borrow)
- `USDT`
- `USDC`
- `BTC`

### Collateral Assets
- `BTC`
- `SOL`
- `BNB`
- (Any crypto asset supported by Quidax)

### Duration Options
- `30` days
- `60` days
- `90` days
- `180` days

## Business Rules
- **Interest Rate**: 5% Monthly (Fixed)
- **Collateralization**: 125% (25% above loan amount)
- **One Active Loan**: Borrowers can only have 1 active loan
- **Auto-Matching**: System matches borrow requests with lending offers
- **Collateral**: Auto-calculated, user cannot edit

## Files Created
1. `Crypto_Loans_API.postman_collection.json` - Postman collection
2. `CRYPTO_LOANS_CURL.md` - Complete cURL commands with examples
3. `CRYPTO_LOANS_API.md` - Full API documentation
4. `CRYPTO_LOANS_SPECIFICATION.md` - System specification
