<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OperationController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\LogController;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware('auth')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('operations')->name('operations.')->group(function () {

        Route::get('/goods-in', [OperationController::class, 'showGoodsIn'])->name('goods-in');
        Route::post('/goods-in', [OperationController::class, 'processGoodsIn'])->name('goods-in.post');

        Route::get('/goods-out', [OperationController::class, 'showGoodsOut'])->name('goods-out');
        Route::post('/goods-out', [OperationController::class, 'processGoodsOut'])->name('goods-out.post');

        Route::get('/mutation', [OperationController::class, 'showMutation'])->name('mutation');
        Route::post('/mutation', [OperationController::class, 'processMutation'])->name('mutation.post');

        Route::get('/api/get-locations', [OperationController::class, 'getLocationsByItem'])->name('get-locations');
        Route::get('/api/get-batches', [OperationController::class, 'getBatchesByItem'])->name('get-batches');
        Route::get('/api/fefo-suggest', [OperationController::class, 'fefoSuggest'])->name('fefo-suggest');
    });

    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/', [InventoryController::class, 'index'])->name('index');
        Route::post('/quarantine/{stock}', [InventoryController::class, 'quarantine'])->name('quarantine');
        Route::post('/release/{stock}', [InventoryController::class, 'release'])->name('release');
        Route::get('/export', [InventoryController::class, 'exportExcel'])->name('export');
    });

    Route::get('/logs', [LogController::class, 'index'])->name('logs.index');

    Route::get('/ai-analysis', [\App\Http\Controllers\AiController::class, 'index'])->name('ai.index');

    Route::middleware('role:admin')->prefix('master')->name('master.')->group(function () {

        Route::resource('items', ItemController::class)->except(['create', 'show', 'edit']);

        Route::resource('locations', LocationController::class)->except(['create', 'show', 'edit']);

        Route::resource('categories', CategoryController::class)->except(['create', 'show', 'edit']);
    });

});
