<?php

use App\Http\Controllers\Api\PortalApiController;
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

// Signed calls from the FTS portal (see App\Http\Middleware\VerifyPortalSignature)
Route::prefix('portal')->middleware(['portal.signature', 'throttle:120,1'])->group(function () {
    Route::get('ping', [PortalApiController::class, 'ping']);
    Route::post('stock-check', [PortalApiController::class, 'stockCheck']);
    Route::post('purchase-orders', [PortalApiController::class, 'upsertPurchaseOrder']);
    Route::get('purchase-orders/{portalPoId}', [PortalApiController::class, 'showPurchaseOrder'])->whereNumber('portalPoId');
    Route::post('purchase-orders/{portalPoId}/void', [PortalApiController::class, 'voidPurchaseOrder'])->whereNumber('portalPoId');
});
