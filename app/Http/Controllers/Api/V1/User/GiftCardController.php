<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Services\ReloadlyService;
use App\Http\Helpers\Response;

class GiftCardController extends Controller
{
    protected $reloadly;

    public function __construct(ReloadlyService $reloadly)
    {
        $this->reloadly = $reloadly;
    }

    /**
     * Get all gift card categories from Reloadly
     */
    public function categories()
    {
        try {
            $categories = $this->reloadly->getCategories();
            return Response::successResponse('Gift card categories fetched successfully', ['categories' => $categories]);
        } catch (\Exception $e) {
            return Response::errorResponse('Failed to fetch categories: ' . $e->getMessage());
        }
    }
}
