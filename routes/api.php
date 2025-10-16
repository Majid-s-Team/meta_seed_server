<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\EventCategoryController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\StaticPageController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::post('/upload-media', [ProfileController::class, 'uploadMedia']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [ProfileController::class, 'view']);
    Route::post('/profile-update', [ProfileController::class, 'update']);
    Route::post('/change-password', [ProfileController::class, 'changePassword']);
    Route::put('/toggle-active/{id}', [ProfileController::class, 'toggleActive']);

});

Route::middleware('auth:sanctum')->group(function () {
    // Events
    Route::get('events', [EventController::class, 'index']);
    Route::get('events/{id}', [EventController::class, 'show']);
    Route::post('events/{id}/book', [EventController::class, 'book']);

    //    Static Pages
    
    Route::get('pages', [StaticPageController::class, 'index']);
    Route::get('pages/{type}', [StaticPageController::class, 'show']);
    Route::post('pages', [StaticPageController::class, 'store']);
    Route::put('pages/{type}', [StaticPageController::class, 'update']);
    Route::delete('pages/{type}', [StaticPageController::class, 'destroy']);

    // Categories
    Route::get('categories', [EventCategoryController::class, 'index']);

    // Wallet
    Route::get('wallet', [WalletController::class, 'index']);
    Route::post('wallet/purchase', [WalletController::class, 'purchaseCoins']);
    Route::get('transactions', [WalletController::class, 'transactions']);

    Route::middleware('role:admin')->group(function () {
        // Events
        Route::post('events', [EventController::class, 'store']);
        Route::put('events/{id}', [EventController::class, 'update']);
        Route::post('events/{id}/status', [EventController::class, 'changeStatus']);

        // Categories
        Route::post('categories', [EventCategoryController::class, 'store']);
        Route::put('categories/{id}', [EventCategoryController::class, 'update']);
        Route::delete('categories/{id}', [EventCategoryController::class, 'destroy']);
    });

        Route::get('/admin/users', [AdminController::class, 'listUsers']);
        Route::get('/admin/users/transactions', [AdminController::class, 'allTransactions']);
        Route::put('/admin/users/{id}/toggle', [AdminController::class, 'toggleUserActive']);
        Route::get('/admin/analytics', [AdminController::class, 'analytics']);
        Route::get('/admin/bookings', [AdminController::class, 'bookingHistory']);

});
