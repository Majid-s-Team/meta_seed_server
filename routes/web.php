<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LivestreamTestController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EventController as AdminEventController;
use App\Http\Controllers\Admin\LivestreamController as AdminLivestreamController;
use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\CmsController;
use App\Http\Controllers\Admin\EventCategoryController;
use App\Http\Controllers\Admin\EventRecordingController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', function () {
    return redirect()->route('admin.login');
})->name('login');

// Admin panel auth (session-based)
Route::get('/admin/login', [LoginController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [LoginController::class, 'login'])->name('admin.login.submit');

Route::middleware(['auth', 'admin.panel'])->prefix('admin')->name('admin.')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('events', AdminEventController::class)->except(['show']);
    Route::post('events/bulk-delete', [AdminEventController::class, 'bulkDestroy'])->name('events.bulk-delete');
    Route::resource('categories', EventCategoryController::class)->except(['show']);
    Route::post('categories/bulk-delete', [EventCategoryController::class, 'bulkDestroy'])->name('categories.bulk-delete');
    Route::resource('recordings', EventRecordingController::class)->except(['show']);
    Route::resource('livestreams', AdminLivestreamController::class)->except(['show']);
    Route::post('livestreams/bulk-delete', [AdminLivestreamController::class, 'bulkDestroy'])->name('livestreams.bulk-delete');
    Route::get('livestreams/{livestream}/broadcast', [AdminLivestreamController::class, 'broadcast'])->name('livestreams.broadcast');
    Route::post('livestreams/{livestream}/go-live', [AdminLivestreamController::class, 'goLive'])->name('livestreams.go-live');
    Route::post('livestreams/{livestream}/end-stream', [AdminLivestreamController::class, 'endStream'])->name('livestreams.end-stream');
    Route::get('bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('bookings/event', [BookingController::class, 'eventBookings'])->name('bookings.event');
    Route::get('bookings/event/export', [BookingController::class, 'eventBookingsExport'])->name('bookings.event.export');
    Route::get('bookings/livestream', [BookingController::class, 'livestreamBookings'])->name('bookings.livestream');
    Route::get('users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('users/{user}', [AdminUserController::class, 'show'])->name('users.show');
    Route::put('users/{user}/toggle', [AdminUserController::class, 'toggle'])->name('users.toggle');
    Route::get('transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('cms', [CmsController::class, 'index'])->name('cms.index');
    Route::get('cms/{type}/edit', [CmsController::class, 'edit'])->name('cms.edit');
    Route::get('cms/{type}/preview', [CmsController::class, 'preview'])->name('cms.preview');
    Route::put('cms/{type}', [CmsController::class, 'update'])->name('cms.update');
});

// Livestream web testing interface (no auth; use API token on page)
Route::get('/admin/livestream-test', [LivestreamTestController::class, 'adminPage'])->name('livestream-test.admin');
Route::get('/livestream-test', [LivestreamTestController::class, 'userPage'])->name('livestream-test.user');
Route::get('/livestream-test/publisher', [LivestreamTestController::class, 'publisherPage'])->name('livestream-test.publisher');
