# Busha API (Connect Flow) - cURL Commands

## 1. Create Quote
Use this to get the rate and a `quote_id`.

**Buy Quote (I want to buy 0.001 BTC)**
```bash
curl -X GET "${BASE_URL}/api/user/busha/quote?pair=BTC-NGN&amount=0.001&side=buy" \
  -H "Authorization: Bearer ${ACCESS_TOKEN}" \
  -H "Accept: application/json"
```

**Sell Quote (I want to sell 0.001 BTC)**
```bash
curl -X GET "${BASE_URL}/api/user/busha/quote?pair=BTC-NGN&amount=0.001&side=sell" \
  -H "Authorization: Bearer ${ACCESS_TOKEN}" \
  -H "Accept: application/json"
```

**Response Example:**
```json
{
    "message": "Quote created",
    "data": {
        "id": "QUOTE-UUID-HERE",
        "source_amount": "50000",
        "target_amount": "0.001",
        "rate": "50000000",
        "expires_at": "..."
    }
}
```

## 2. Execute Trade
Use the `id` from the quote response to execute.

**Execute Buy**
```bash
curl -X POST "${BASE_URL}/api/user/busha/trade" \
  -H "Authorization: Bearer ${ACCESS_TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{
    "pair": "BTC-NGN",
    "side": "buy",
    "quote_id": "QUOTE-UUID-FROM-ABOVE",
    "amount": 0.001,
    "total": 50000 
  }'
```
*Note: `total` here refers to the fiat cost (source_amount from quote).*

**Execute Sell**
```bash
curl -X POST "${BASE_URL}/api/user/busha/trade" \
  -H "Authorization: Bearer ${ACCESS_TOKEN}" \
  -H "Content-Type: application/json" \
  -d '{
    "pair": "BTC-NGN",
    "side": "sell",
    "quote_id": "QUOTE-UUID-FROM-ABOVE",
    "amount": 0.001,
    "total": 48000
  }'
```
*Note: `total` here refers to the fiat proceeds (target_amount from quote).*

## 3. History
```bash
curl -X GET "${BASE_URL}/api/user/busha/history" \
  -H "Authorization: Bearer ${ACCESS_TOKEN}" \
  -H "Accept: application/json"
```
