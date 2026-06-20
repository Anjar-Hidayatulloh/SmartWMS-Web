<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\OperationController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\LogController;

// 1. Guest Routes (Authentication)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// Redirect root to dashboard/login
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// 2. Authenticated Routes (Core WMS Workspace)
Route::middleware('auth')->group(function () {
    
    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // --- Warehouse Operations ---
    Route::prefix('operations')->name('operations.')->group(function () {
        // Goods In
        Route::get('/goods-in', [OperationController::class, 'showGoodsIn'])->name('goods-in');
        Route::post('/goods-in', [OperationController::class, 'processGoodsIn'])->name('goods-in.post');

        // Goods Out
        Route::get('/goods-out', [OperationController::class, 'showGoodsOut'])->name('goods-out');
        Route::post('/goods-out', [OperationController::class, 'processGoodsOut'])->name('goods-out.post');

        // Mutation
        Route::get('/mutation', [OperationController::class, 'showMutation'])->name('mutation');
        Route::post('/mutation', [OperationController::class, 'processMutation'])->name('mutation.post');

        // AJAX API endpoints for interactive forms
        Route::get('/api/get-locations', [OperationController::class, 'getLocationsByItem'])->name('get-locations');
        Route::get('/api/get-batches', [OperationController::class, 'getBatchesByItem'])->name('get-batches');
        Route::get('/api/fefo-suggest', [OperationController::class, 'fefoSuggest'])->name('fefo-suggest');
    });

    // --- Stock Monitor & Reports ---
    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/', [InventoryController::class, 'index'])->name('index');
        Route::post('/quarantine/{stock}', [InventoryController::class, 'quarantine'])->name('quarantine');
        Route::post('/release/{stock}', [InventoryController::class, 'release'])->name('release');
        Route::get('/export', [InventoryController::class, 'exportExcel'])->name('export');
    });

    // --- Audit Trail Logs ---
    Route::get('/logs', [LogController::class, 'index'])->name('logs.index');

    // --- AI Analytics (FastAPI Integration) ---
    Route::get('/ai-analysis', [\App\Http\Controllers\AiController::class, 'index'])->name('ai.index');

    // 3. Admin Only Routes (Master Data Management)
    Route::middleware('role:admin')->prefix('master')->name('master.')->group(function () {
        // Master Items CRUD
        Route::resource('items', ItemController::class)->except(['create', 'show', 'edit']);
        
        // Master Locations CRUD
        Route::resource('locations', LocationController::class)->except(['create', 'show', 'edit']);
    });

});
