https://api.busha.io/v1/customers

curl --request POST \
  --url https://api.busha.io/v1/customers \
  --header 'Authorization: Bearer RjlwR3JGR0NlTTowUkZOcUFIY0w3N3dtOEI4SEdFY2Fjd1RBaUVacUhIdlg1cTBVSnhVTVRCM1QzUWE=' \
  --header 'Content-Type: application/json' \
  --data '
{
  "email": "nwachukwupatrick069@gmail.com",
  "country_id": "NG",
  "phone": "+234 9069138889",
  "has_accepted_terms": true,
  "type": "individual",
  "birth_date": "09-04-2001",
  "address": {
    "country_id": "NG",
    "address_line_1": "3rd avenue",
    "city": "Calabar",
    "state": "Cross River",
    "address_line_2": "3rd avenue",
    "postal_code": "435101"
  },
  "first_name": "CHIBUIKE",
  "last_name": "NWACHUKWU",
  "middle_name": "Patrick"
}
'

{"status":"success","message":"Created customer successfully","data":{"address":{"address_line_1":"3rd avenue","address_line_2":"3rd avenue","city":"Calabar","country_id":"NG","postal_code":"435101","state":"Cross River"},"business_id":"BUS_zQGsgIQgjaBHmjqCIk1PC","country_id":"NG","created_at":"2026-02-04T01:06:07.37779697Z","deposit":true,"display_currency":"NGN","email":"nwachukwupatrick069@gmail.com","first_name":"CHIBUIKE","has_accepted_terms_of_service":true,"id":"CUS_IxrXk9urgsTTg","last_name":"NWACHUKWU","level":"0","middle_name":"Patrick","payout":true,"phone":"+234 9069138889","status":"inactive","type":"individual","updated_at":"2026-02-04T01:06:07.37779697Z"}}
