<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\Customer\OrderController as CustomerOrderController;
use App\Http\Controllers\Store\DashboardController;
use App\Http\Controllers\Store\ProductController as StoreProductController;
use App\Http\Controllers\Store\OrderController as StoreOrderController;
use App\Http\Controllers\Courier\OrderController as CourierOrderController;
use App\Http\Controllers\Courier\LocationController;
use Illuminate\Support\Facades\Route;

// Главная (каталог)
Route::get('/', [ProductController::class, 'index'])->name('home');

// Авторизация и регистрация
Route::middleware('guest')->group(function () {
    Route::get('register', [App\Http\Controllers\Auth\RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [App\Http\Controllers\Auth\RegisteredUserController::class, 'store']);
    Route::get('login', [App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'store']);
    Route::get('forgot-password', [App\Http\Controllers\Auth\PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [App\Http\Controllers\Auth\PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('reset-password/{token}', [App\Http\Controllers\Auth\NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [App\Http\Controllers\Auth\NewPasswordController::class, 'store'])->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', [App\Http\Controllers\Auth\EmailVerificationPromptController::class, '__invoke'])->name('verification.notice');
    Route::get('verify-email/{id}/{hash}', [App\Http\Controllers\Auth\VerifyEmailController::class, '__invoke'])->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
    Route::post('email/verification-notification', [App\Http\Controllers\Auth\EmailVerificationNotificationController::class, 'store'])->middleware('throttle:6,1')->name('verification.send');
    Route::get('confirm-password', [App\Http\Controllers\Auth\ConfirmablePasswordController::class, 'show'])->name('password.confirm');
    Route::post('confirm-password', [App\Http\Controllers\Auth\ConfirmablePasswordController::class, 'store']);
    Route::put('/password', [App\Http\Controllers\Auth\PasswordController::class, 'update'])->name('password.update');
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [App\Http\Controllers\ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('logout', [App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

Route::get('/dashboard', function () {
    $user = auth()->user();
    return match ($user->role) {
        'store'    => redirect()->route('store.dashboard'),
        'courier'  => redirect()->route('courier.orders'),
        'customer' => redirect()->route('customer.orders'),
        default    => redirect()->route('home'),
    };
})->middleware(['auth', 'verified'])->name('dashboard');

// Публичные страницы
Route::get('/stores/{store}', [StoreController::class, 'show'])->name('store.show');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('product.show');

Route::post('/cart/add/{product}', [CartController::class, 'add'])->name('cart.add');
Route::patch('/cart/update/{item}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/remove/{item}', [CartController::class, 'remove'])->name('cart.remove');
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');

Route::middleware('auth')->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
});

// Панель магазина
Route::middleware(['auth', 'role:store'])->prefix('store')->name('store.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('products', StoreProductController::class);
    Route::get('/products/{product}/history', [StoreProductController::class, 'history'])->name('products.history');
    Route::get('/products/{product}/supply', [StoreProductController::class, 'showSupplyForm'])->name('products.supply.form');
    Route::post('/products/{product}/supply', [StoreProductController::class, 'supply'])->name('products.supply');
    Route::get('/products/{product}/writeoff', [StoreProductController::class, 'showWriteOffForm'])->name('products.writeoff.form');
    Route::post('/products/{product}/writeoff', [StoreProductController::class, 'writeOff'])->name('products.writeoff');
    Route::resource('categories', App\Http\Controllers\Store\CategoryController::class)->except(['show']);
    Route::get('/orders', [StoreOrderController::class, 'index'])->name('orders');
    Route::get('/orders/{order}', [StoreOrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/status', [StoreOrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::delete('/orders/{order}', [StoreOrderController::class, 'destroy'])->name('orders.destroy');
    Route::get('/settings', [App\Http\Controllers\Store\SettingsController::class, 'edit'])->name('settings');
    Route::patch('/settings', [App\Http\Controllers\Store\SettingsController::class, 'update'])->name('settings.update');
    Route::resource('suppliers', App\Http\Controllers\Store\SupplierController::class);
});

// Панель курьера
Route::middleware(['auth', 'role:courier'])->prefix('courier')->name('courier.')->group(function () {
    Route::get('/orders', [CourierOrderController::class, 'index'])->name('orders');
    Route::get('/orders/{order}', [CourierOrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/accept', [CourierOrderController::class, 'accept'])->name('orders.accept');
    Route::patch('/orders/{order}/status', [CourierOrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::post('/location', [LocationController::class, 'store'])->name('location.update');
});

// Панель покупателя
Route::middleware(['auth', 'role:customer'])->prefix('customer')->name('customer.')->group(function () {
    Route::get('/orders', [CustomerOrderController::class, 'index'])->name('orders');
    Route::get('/orders/{order}', [CustomerOrderController::class, 'show'])->name('orders.show');
});