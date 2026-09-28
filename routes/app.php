<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\App\AuthController;
use App\Http\Controllers\App\CatalogController;
use App\Http\Controllers\App\DashboardController;
use App\Http\Controllers\App\OrderController;
use App\Http\Controllers\App\PayoutController;
use App\Http\Controllers\App\SiteController;
use App\Http\Controllers\App\WalletController;
use Illuminate\Support\Facades\Route;

// Logged-in marketplace. Server-rendered forms only, no JavaScript.
Route::prefix('app')->name('app.')->group(function () {
    Route::post('locale', [AuthController::class, 'locale'])->name('locale');

    Route::middleware('guest')->group(function () {
        Route::get('login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('login', [AuthController::class, 'login'])->middleware('throttle:10,1');
        Route::get('register', [AuthController::class, 'showRegister'])->name('register');
        Route::post('register', [AuthController::class, 'register'])->middleware('throttle:10,1');
    });

    Route::middleware('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/', DashboardController::class)->name('dashboard');

        Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::post('orders/{order}/recheck', [OrderController::class, 'recheck'])->name('orders.recheck');

        Route::get('wallet', [WalletController::class, 'show'])->name('wallet');

        Route::middleware('role:buyer')->group(function () {
            Route::get('catalog', [CatalogController::class, 'index'])->name('catalog');
            Route::get('catalog/{site}/order', [OrderController::class, 'create'])->name('orders.create');
            Route::post('catalog/{site}/order', [OrderController::class, 'store'])->name('orders.store');
            Route::post('orders/{order}/validate', [OrderController::class, 'validateOrder'])->name('orders.validate');
            Route::post('orders/{order}/dispute', [OrderController::class, 'dispute'])->name('orders.dispute');
            Route::post('wallet/topup', [WalletController::class, 'topup'])->name('wallet.topup');
            Route::get('wallet/return/{payment}', [WalletController::class, 'return'])->name('wallet.return');
            Route::get('wallet/invoices/{payment}', [WalletController::class, 'invoice'])->name('wallet.invoice');
        });

        Route::middleware('role:publisher')->group(function () {
            Route::resource('sites', SiteController::class)->except(['destroy']);
            Route::post('sites/{site}/verify', [SiteController::class, 'verify'])->name('sites.verify');
            Route::post('orders/{order}/accept', [OrderController::class, 'accept'])->name('orders.accept');
            Route::post('orders/{order}/refuse', [OrderController::class, 'refuse'])->name('orders.refuse');
            Route::post('orders/{order}/publish', [OrderController::class, 'publish'])->name('orders.publish');
            Route::get('payouts', [PayoutController::class, 'index'])->name('payouts.index');
            Route::post('payouts', [PayoutController::class, 'store'])->name('payouts.store');
        });

        Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
            Route::get('/', [AdminController::class, 'index'])->name('index');
            Route::post('sites/{site}/approve', [AdminController::class, 'approveSite'])->name('sites.approve');
            Route::post('sites/{site}/reject', [AdminController::class, 'rejectSite'])->name('sites.reject');
            Route::post('orders/{order}/resolve', [AdminController::class, 'resolveDispute'])->name('orders.resolve');
            Route::post('payouts/{payout}/paid', [AdminController::class, 'markPayoutPaid'])->name('payouts.paid');
            Route::post('payouts/{payout}/reject', [AdminController::class, 'rejectPayout'])->name('payouts.reject');
        });
    });
});
