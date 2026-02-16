<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LivestreamTestController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Livestream web testing interface (no auth; use API token on page)
Route::get('/admin/livestream-test', [LivestreamTestController::class, 'adminPage'])->name('livestream-test.admin');
Route::get('/livestream-test', [LivestreamTestController::class, 'userPage'])->name('livestream-test.user');
Route::get('/livestream-test/publisher', [LivestreamTestController::class, 'publisherPage'])->name('livestream-test.publisher');
