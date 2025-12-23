<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Fallback login route to prevent 500 RouteNotFoundException
Route::get('/login', function () {
    return response()->json(['message' => 'Unauthenticated.'], 401);
})->name('login');

// Savings (Piggyvest) Routes
// Savings (Piggyvest) Routes
Route::middleware('auth:api')->prefix('v1/savings')->group(function () {
    // Flex
    Route::prefix('flex')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\Savings\FlexController::class, 'index']);
        Route::post('deposit', [\App\Http\Controllers\Api\Savings\FlexController::class, 'deposit']);
        Route::post('withdraw', [\App\Http\Controllers\Api\Savings\FlexController::class, 'withdraw']);
        Route::get('history', [\App\Http\Controllers\Api\Savings\FlexController::class, 'history']);
    });

    // SafeLock
    Route::prefix('safelock')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\Savings\SafeLockController::class, 'index']);
        Route::get('plans', [\App\Http\Controllers\Api\Savings\SafeLockController::class, 'plans']);
        Route::post('create', [\App\Http\Controllers\Api\Savings\SafeLockController::class, 'create']);
        Route::post('break', [\App\Http\Controllers\Api\Savings\SafeLockController::class, 'break']);
        Route::get('history', [\App\Http\Controllers\Api\Savings\SafeLockController::class, 'history']);
    });

    // Target Savings
    Route::prefix('target')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\Savings\TargetController::class, 'index']);
        Route::post('create', [\App\Http\Controllers\Api\Savings\TargetController::class, 'create']);
        Route::post('quick-save', [\App\Http\Controllers\Api\Savings\TargetController::class, 'quickSave']);
        Route::post('break', [\App\Http\Controllers\Api\Savings\TargetController::class, 'break']);
        Route::get('history', [\App\Http\Controllers\Api\Savings\TargetController::class, 'history']);
    });
});

// KYC (YouVerify)
Route::middleware('auth:api')->prefix('kyc')->group(function () {
    Route::post('initiate', [\App\Http\Controllers\Api\User\KycController::class, 'initiate']);
});