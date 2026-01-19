# Response Format Update - Crypto Loans API

## Changes Made

All Crypto Loans API responses now use the standardized `App\Http\Helpers\Response` helper class.

## Response Format

### Success Response
```json
{
  "message": "Operation successful message",
  "data": { ... },
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

## Updated Controllers

### 1. LoanController (User API)
✅ All 10 endpoints updated:
- `createLendingOffer()` - Uses `Response::successResponse()` and `Response::errorResponse()`
- `cancelLendingOffer()` - Uses `Response::successResponse()` and `Response::errorResponse()`
- `createBorrowRequest()` - Uses `Response::successResponse()` and `Response::errorResponse()`
- `repayLoan()` - Uses `Response::successResponse()` and `Response::errorResponse()`
- `getMyLendingOffers()` - Uses `Response::successResponse()`
- `getMyBorrowRequests()` - Uses `Response::successResponse()`
- `getMyLoansAsBorrower()` - Uses `Response::successResponse()`
- `getMyLoansAsLender()` - Uses `Response::successResponse()`
- `getLoanDetails()` - Uses `Response::successResponse()`
- `calculateCollateral()` - Uses `Response::successResponse()` and `Response::errorResponse()`

### 2. LoanAdminController (Admin API)
✅ All 7 endpoints updated:
- `index()` - Uses `Response::successResponse()`
- `getLendingOffers()` - Uses `Response::successResponse()`
- `getBorrowRequests()` - Uses `Response::successResponse()`
- `getStatistics()` - Uses `Response::successResponse()`
- `show()` - Uses `Response::successResponse()`
- `forceLiquidate()` - Uses `Response::successResponse()` and `Response::errorResponse()`
- `markOverdue()` - Uses `Response::successResponse()`
- `getOverdueLoans()` - Uses `Response::successResponse()`

## Example Responses

### Create Lending Offer (Success)
```json
{
  "message": "Lending offer created successfully",
  "data": {
    "id": 1,
    "user_id": 123,
    "asset": "USDT",
    "amount": "1000.00000000",
    "status": "pending"
  },
  "type": "success"
}
```

### Create Lending Offer (Error)
```json
{
  "message": "Insufficient USDT balance. Available: 500.00",
  "data": null,
  "type": "error"
}
```

### Validation Error
```json
{
  "message": "Validation failed",
  "data": {
    "asset": ["The asset field is required."],
    "amount": ["The amount must be at least 1."]
  },
  "type": "error"
}
```

## Benefits

1. **Consistency**: All API responses follow the same format
2. **Type Safety**: Response type is always indicated ("success" or "error")
3. **Easier Client Handling**: Frontend can check `type` field to determine success/failure
4. **Centralized Logic**: All response formatting is handled in one place
5. **Maintainability**: Easy to update response format across all endpoints

## Migration Notes

- Old format with `success: true/false` is replaced with `type: "success"/"error"`
- Message is now always a string (not nested in an object)
- Data structure remains the same
- HTTP status codes are preserved (200, 201, 400, 422, etc.)
