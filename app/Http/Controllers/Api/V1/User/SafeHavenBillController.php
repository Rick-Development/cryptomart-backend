<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\SafeHavenBillService;
use App\Http\Helpers\Response;
use Exception;

class SafeHavenBillController extends Controller
{
    protected $billService;

    public function __construct(SafeHavenBillService $billService)
    {
        $this->billService = $billService;
    }

    /**
     * Get All Services
     */
    public function getServices()
    {
        try {
            $data = $this->billService->getServices();
            return Response::successResponse('Services fetched successfully', $data);
        } catch (Exception $e) {
            return Response::errorResponse($e->getMessage());
        }
    }

    /**
     * Get Categories for a Service
     */
    public function getCategories($serviceId)
    {
        try {
            $data = $this->billService->getCategories($serviceId);
            return Response::successResponse('Service categories fetched successfully', $data);
        } catch (Exception $e) {
            return Response::errorResponse($e->getMessage());
        }
    }

    /**
     * Get Products for a Category
     */
    public function getProducts($categoryId)
    {
        try {
            $data = $this->billService->getProducts($categoryId);
            return Response::successResponse('Products fetched successfully', $data);
        } catch (Exception $e) {
            return Response::errorResponse($e->getMessage());
        }
    }

    /**
     * Verify Customer (Meter/Smartcard)
     */
    public function verifyCustomer(Request $request)
    {
        $request->validate([
            'serviceCategoryId' => 'required|string',
            'entityNumber' => 'required|string',
        ]);

        try {
            $data = $this->billService->verifyCustomer($request->all());
            return Response::successResponse('Customer verified successfully', $data);
        } catch (Exception $e) {
            return Response::errorResponse($e->getMessage());
        }
    }

    /**
     * Purchase Airtime
     */
    public function buyAirtime(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:50',
            'phoneNumber' => 'required|string',
            'network' => 'required|in:MTN,GLO,AIRTEL,9MOBILE'
        ]);

        try {
            $user = auth()->user();
            $data = $this->billService->purchase($user, 'airtime', $request->all());
            return Response::successResponse('Airtime purchase successful', $data);
        } catch (Exception $e) {
            return Response::errorResponse($e->getMessage());
        }
    }

    /**
     * Purchase Data
     */
    public function buyData(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:50',
            'network' => 'required|in:MTN,GLO,AIRTEL,9MOBILE',
            'bundleCode' => 'required|string',
            'phoneNumber' => 'required|string'
        ]);

        try {
            $user = auth()->user();
            $data = $this->billService->purchase($user, 'data', $request->all());
            return Response::successResponse('Data purchase successful', $data);
        } catch (Exception $e) {
            return Response::errorResponse($e->getMessage());
        }
    }

    /**
     * Purchase Cable TV
     */
    public function buyCable(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:100',
            'provider' => 'required|in:DSTV,GOTV,STARTIMES',
            'bundleCode' => 'required|string',
            'cardNumber' => 'required|string'
        ]);

        try {
            $user = auth()->user();
            $data = $this->billService->purchase($user, 'cable', $request->all());
            return Response::successResponse('Cable TV purchase successful', $data);
        } catch (Exception $e) {
            return Response::errorResponse($e->getMessage());
        }
    }

    /**
     * Purchase Utility Bill
     */
    public function buyUtility(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:100',
            'provider' => 'required|in:EKEDC,IKEDC,AEDC,PHED,KEDCO',
            'meterNumber' => 'required|string',
            'vendType' => 'required|in:PREPAID,POSTPAID'
        ]);

        try {
            $user = auth()->user();
            $data = $this->billService->purchase($user, 'utility', $request->all());
            return Response::successResponse('Utility bill purchase successful', $data);
        } catch (Exception $e) {
            return Response::errorResponse($e->getMessage());
        }
    }
}
