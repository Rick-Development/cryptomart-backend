<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Helpers\Payscribe\PayscribeCustomersHelper;
use App\Http\Helpers\Payscribe\CardIssusing\CardDetailsHelper;
use Response;

class PayscribeCardDetailsController extends Controller
{
    public function __construct(private CardDetailsHelper $cardDetailsHelper, private PayscribeCustomersHelper $payscribeCustomersHelper)
    {
    }
    public function createCard(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'customer_id' => 'sometimes|string',
            'currency' => 'sometimes|string',
            'brand' => 'required|string',
            'amount' => 'required',
            'type' => 'sometimes',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Validation failed',
                'data' => $validator->errors(),
            ], 422);
        }

        $data = $request->only([
            'customer_id',
            'currency',
            'brand',
            'amount',
            'type',
        ]);

        // if (is_null(auth()->user()->payscribe_customer_id)) {
        //     $this->createCustomer($data['phone']);
        // }

        // Overwrite customer_id with the authenticated user’s value
        $data['customer_id'] = auth()->user()->payscribe_customer_id;
        // dd($data['customer_id']);

        $referenceId = Str::uuid();
        $refIdString = (string) $referenceId . '-auto_bill';
        $data = array_merge($data, ['ref' => $refIdString]);

        $response = json_decode($this->cardDetailsHelper->createCard($data), true);

        if (
            $response &&
            isset($response['status'], $response['description']) &&
            $response['status'] === false &&
            $response['description'] === 'Customer not found for this business.'
        ) {
            // do something
        }

        return response()->json($response, $response['status_code']);
    }
    public function getCardDetails(Request $request)
    {
        $data = $request->validate(
            [
                'ref' => 'required | string'
            ]
        );
        $response = json_decode($this->cardDetailsHelper->getCardDetails($data), true);
        return $response;
        // $cardDetails = new CardDetails();
        // $response = $cardDetails->getCardDetails($data['card_id']);
    }

    public function createCustomer($phone): JsonResponse
    {

        $user = auth()->user();

        $data = [
            'first_name' => $user->firstname,
            'last_name' => $user->lastname,
            'email' => $user->email,
            'phone' => $phone,
        ];

        $response = $this->payscribeCustomersHelper->createUser($data);
        // dd($response);
        if ($response && $response['status'] == true) {
            $user->payscribe_customer_id = $response['message']['details']['customer_id'];
            $user->payscribe_tier = $response['message']['details']['tier'];
            $user->payscribe_customer_phone = $response['message']['details']['phone'];
            $user->payscribe_customer_country = $response['message']['details']['country'];

            $user->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Customer created successfully',
                'data' => [
                    'customer_id' => $user->payscribe_customer_id,
                    'tier' => $user->payscribe_tier,
                    'phone' => $user->payscribe_customer_phone,
                ],
                'payscribe_response' => $response,
            ], 201);
        }

        return response()->json($response, $response['status_code'] ?? 200);
    }
}
