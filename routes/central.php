<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Central\ShopRegistrationController;
use App\Http\Controllers\Central\ShopLoginController;
use App\Http\Controllers\Central\DevAuthController;
use App\Http\Controllers\Central\DevDashboardController;
use App\Http\Controllers\Central\PlansController;
use App\Http\Controllers\Central\PaystackController;

Route::middleware(['web', 'central'])->group(function () {
    Route::get('/', function () {
        return view('central.home');
    })->name('central.home');

    Route::get('/plans', [PlansController::class, 'index'])->name('central.plans');

    Route::get('/subscribe/{plan}', [PaystackController::class, 'showCheckout'])->name('central.subscribe');
    Route::post('/subscribe/{plan}', [PaystackController::class, 'startCheckout'])->name('central.subscribe.start');
    Route::get('/paystack/callback', [PaystackController::class, 'callback'])->name('central.paystack.callback');
    Route::get('/paystack/success', [PaystackController::class, 'success'])->name('central.paystack.success');
    Route::post('/paystack/webhook', [PaystackController::class, 'webhook'])->name('central.paystack.webhook');

    Route::get('/shop-login', [ShopLoginController::class, 'create'])->name('central.login');
    Route::post('/shop-login', [ShopLoginController::class, 'redirect'])->name('central.login.redirect');

    Route::get('/register', [ShopRegistrationController::class, 'create'])->name('central.register');
    Route::post('/register/check-device', [ShopRegistrationController::class, 'checkDevice'])->name('central.register.check_device');
    Route::get('/register/existing-shop', [ShopRegistrationController::class, 'existing'])->name('central.register.existing');
    Route::post('/register', [ShopRegistrationController::class, 'store'])->name('central.register.store');
    Route::get('/register/success', [ShopRegistrationController::class, 'success'])->name('central.register.success');

    // Dev dashboard (system admin)
    Route::get('/dev/login', [DevAuthController::class, 'create'])->name('dev.login');
    Route::post('/dev/login', [DevAuthController::class, 'store'])->name('dev.login.store');
    Route::post('/dev/logout', [DevAuthController::class, 'destroy'])->name('dev.logout');

    Route::middleware('auth:dev')->group(function () {
        Route::get('/dev', [DevDashboardController::class, 'index'])->name('dev.dashboard');
        Route::get('/dev/shops/{tenantId}/history', [DevDashboardController::class, 'history'])->name('dev.shops.history');
        Route::post('/dev/shops/{tenantId}/activate', [DevDashboardController::class, 'activate'])->name('dev.shops.activate');
        Route::delete('/dev/shops/{tenantId}', [DevDashboardController::class, 'destroy'])->name('dev.shops.destroy');
    });
});
