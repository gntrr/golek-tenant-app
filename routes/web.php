<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Client\EventController;
use App\Http\Controllers\Client\BoothController;
use App\Http\Controllers\Client\OrderController;
use App\Http\Controllers\Client\PaymentController;

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

// Landing page redirect to events
Route::get('/', [EventController::class, 'index'])->name('home');

// Client Routes - Event & Booth Browsing
Route::prefix('events')->name('client.events.')->group(function () {
    Route::get('/', [EventController::class, 'index'])->name('index');
    Route::get('/{event}', [EventController::class, 'show'])->name('show');
    Route::get('/{event}/booths', [BoothController::class, 'index'])->name('booths');
});

// Client Routes - Booking & Orders
Route::prefix('book')->name('client.book.')->group(function () {
    Route::get('/booth/{booth}', [OrderController::class, 'create'])->name('booth');
    Route::post('/booth/{booth}', [OrderController::class, 'store'])->name('booth.store');
    Route::get('/order/{order}', [OrderController::class, 'show'])->name('order.show');
});

// Client Routes - Payment Processing
Route::prefix('payment')->name('client.payment.')->group(function () {
    Route::get('/select/{order}', [PaymentController::class, 'selectMethod'])->name('select');
    Route::post('/midtrans/{order}', [PaymentController::class, 'processMidtrans'])->name('midtrans');
    Route::get('/upload/{order}', [PaymentController::class, 'uploadForm'])->name('upload.form');
    Route::post('/upload/{order}', [PaymentController::class, 'uploadProof'])->name('upload.store');
    Route::get('/status/{order}', [PaymentController::class, 'status'])->name('status');
});

// Webhook untuk callback Midtrans
Route::post('/webhook/midtrans', [PaymentController::class, 'handleMidtransCallback'])->name('webhook.midtrans');
