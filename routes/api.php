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
use App\Http\Controllers\Api\EventBookingController;
use App\Http\Controllers\Api\LivestreamController;
use App\Http\Controllers\Api\Admin\LivestreamController as AdminLivestreamController;
use App\Http\Controllers\Api\AgoraWebhookController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Agora webhook: RTMP stream start/stop (no auth; validate signature/IP in controller)
Route::post('agora/webhook', AgoraWebhookController::class)->name('agora.webhook');

// Local livestream testing: no auth when LIVESTREAM_LOCAL_TEST=true (returns 404 when disabled)
Route::get('livestreams/test-live', [LivestreamController::class, 'testLive']);
Route::get('livestreams/{id}/test-credentials', [LivestreamController::class, 'testCredentials']);
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
    Route::get('events-booking', [EventBookingController::class, 'index']);
    Route::get('events-booking/{id}', [EventBookingController::class, 'show']);
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

    // Livestreams (user)
    Route::get('livestreams/upcoming', [LivestreamController::class, 'upcoming']);
    Route::get('livestreams/live', [LivestreamController::class, 'live']);
    Route::get('livestreams/{id}', [LivestreamController::class, 'show']);
    Route::post('livestreams/{id}/book', [LivestreamController::class, 'book']);
    Route::post('livestreams/{id}/join', [LivestreamController::class, 'join']);
    Route::post('livestreams/{id}/viewer-joined', [LivestreamController::class, 'viewerJoined']);
    Route::post('livestreams/{id}/viewer-left', [LivestreamController::class, 'viewerLeft']);

    Route::middleware('role:admin')->group(function () {
        // Events
        Route::post('events', [EventController::class, 'store']);
        Route::put('events/{id}', [EventController::class, 'update']);
        Route::post('events/{id}/status', [EventController::class, 'changeStatus']);

        // Categories
        Route::post('categories', [EventCategoryController::class, 'store']);
        Route::put('categories/{id}', [EventCategoryController::class, 'update']);
        Route::delete('categories/{id}', [EventCategoryController::class, 'destroy']);

        // Admin livestreams
        Route::get('admin/livestreams', [AdminLivestreamController::class, 'index']);
        Route::post('admin/livestreams', [AdminLivestreamController::class, 'store']);
        Route::put('admin/livestreams/{id}', [AdminLivestreamController::class, 'update']);
        Route::post('admin/livestreams/{id}/go-live', [AdminLivestreamController::class, 'goLive']);
        Route::post('admin/livestreams/{id}/end-stream', [AdminLivestreamController::class, 'endStream']);
        Route::get('admin/livestreams/{id}/participants', [AdminLivestreamController::class, 'participants']);
    });

        Route::get('/admin/users', [AdminController::class, 'listUsers']);
        Route::get('/admin/users/transactions', [AdminController::class, 'allTransactions']);
        Route::put('/admin/users/{id}/toggle', [AdminController::class, 'toggleUserActive']);
        Route::get('/admin/analytics', [AdminController::class, 'analytics']);
        Route::get('/admin/bookings', [AdminController::class, 'bookingHistory']);

});
