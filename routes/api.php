<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MobileAuthController;
use App\Http\Controllers\Api\MobileOperationController;

Route::post('/login', [MobileAuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user', [MobileAuthController::class, 'userProfile']);
    Route::post('/logout', [MobileAuthController::class, 'logout']);

    Route::get('/items', [MobileOperationController::class, 'getItems']);
    Route::get('/locations', [MobileOperationController::class, 'getLocations']);

    Route::get('/get-locations-by-item', [MobileOperationController::class, 'getLocationsByItem']);
    Route::get('/get-batches-by-item-location', [MobileOperationController::class, 'getBatchesByItemLocation']);
    Route::get('/fefo-suggest', [MobileOperationController::class, 'fefoSuggest']);

    Route::post('/operations/goods-in', [MobileOperationController::class, 'goodsIn']);
    Route::post('/operations/goods-out', [MobileOperationController::class, 'goodsOut']);
    Route::post('/operations/mutation', [MobileOperationController::class, 'mutation']);

    Route::get('/logs', [MobileOperationController::class, 'getOperatorLogs']);
});
