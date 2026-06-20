<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MobileAuthController;
use App\Http\Controllers\Api\MobileOperationController;

// Public mobile authentication route
Route::post('/login', [MobileAuthController::class, 'login']);

// Authenticated mobile routes
Route::middleware('auth:sanctum')->group(function () {
    
    // User profile & logout
    Route::get('/user', [MobileAuthController::class, 'userProfile']);
    Route::post('/logout', [MobileAuthController::class, 'logout']);

    // Catalog & master lists
    Route::get('/items', [MobileOperationController::class, 'getItems']);
    Route::get('/locations', [MobileOperationController::class, 'getLocations']);
    
    // Interactive form dynamically queried helpers
    Route::get('/get-locations-by-item', [MobileOperationController::class, 'getLocationsByItem']);
    Route::get('/get-batches-by-item-location', [MobileOperationController::class, 'getBatchesByItemLocation']);
    Route::get('/fefo-suggest', [MobileOperationController::class, 'fefoSuggest']);

    // Warehouse Operations
    Route::post('/operations/goods-in', [MobileOperationController::class, 'goodsIn']);
    Route::post('/operations/goods-out', [MobileOperationController::class, 'goodsOut']);
    Route::post('/operations/mutation', [MobileOperationController::class, 'mutation']);

    // Operator Transaction history (logs)
    Route::get('/logs', [MobileOperationController::class, 'getOperatorLogs']);
});
