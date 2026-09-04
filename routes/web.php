<?php

use App\Http\Controllers\Admin\AttributeController;
use App\Http\Controllers\Admin\Auth\LoginController as AdminLoginController;
use App\Http\Controllers\Admin\BannerController as AdminBannerController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LanguageController as AdminLanguageController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Admin\ShortController as AdminShortController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\Store\Auth\OtpLoginController;
use App\Http\Controllers\Store\CartController;
use App\Http\Controllers\Store\CheckoutController;
use App\Http\Controllers\Store\HomeController;
use App\Http\Controllers\Admin\PaymentGatewayController as AdminPaymentGatewayController;
use App\Http\Controllers\Store\OrderController;
use App\Http\Controllers\Store\PageController;
use App\Http\Controllers\Store\PaymentController;
use App\Http\Controllers\Store\ProductController;
use App\Http\Controllers\Store\ReviewController;
use App\Http\Controllers\Store\ShortController as StoreShortController;
use Illuminate\Support\Facades\Route;

Route::get('/locale/{locale}', LocaleController::class)
    ->whereIn('locale', ['en', 'ar'])
    ->name('locale.switch');

Route::get('/', HomeController::class)->name('home');
Route::get('/shorts', [StoreShortController::class, 'index'])->name('shorts.index');
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{slug}', [ProductController::class, 'show'])->name('products.show');
Route::post('/products/{product}/reviews', [ReviewController::class, 'store'])->name('products.reviews.store');

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
Route::patch('/cart', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/item', [CartController::class, 'destroy'])->name('cart.destroy');

Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
Route::get('/checkout/pay/{orderNumber}', [PaymentController::class, 'show'])->name('checkout.pay');
Route::post('/payment/verify', [PaymentController::class, 'verify'])->name('payment.verify');

Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
Route::get('/orders/{orderNumber}', [OrderController::class, 'show'])->name('orders.show');

Route::get('/privacy-policy', [PageController::class, 'privacy'])->name('pages.privacy');
Route::get('/terms-and-conditions', [PageController::class, 'terms'])->name('pages.terms');
Route::get('/refund-cancellation-policy', [PageController::class, 'refund'])->name('pages.refund');
Route::get('/contact', [PageController::class, 'contact'])->name('pages.contact');
Route::post('/contact', [PageController::class, 'contactSubmit'])->name('pages.contact.submit');

Route::middleware('guest')->group(function () {
    Route::get('/login', [OtpLoginController::class, 'show'])->name('login');
    Route::post('/login/otp', [OtpLoginController::class, 'send'])->name('login.otp');
    Route::get('/login/verify', [OtpLoginController::class, 'showVerify'])->name('login.verify');
    Route::post('/login/verify', [OtpLoginController::class, 'verify'])->name('login.verify.submit');
});

Route::post('/logout', [OtpLoginController::class, 'logout'])->middleware('auth')->name('logout');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest:admin')->group(function () {
        Route::get('/login', [AdminLoginController::class, 'show'])->name('login');
        Route::post('/login', [AdminLoginController::class, 'store'])->name('login.store');
    });

    Route::middleware(['auth:admin', 'admin'])->group(function () {
        Route::post('/logout', [AdminLoginController::class, 'destroy'])->name('logout');
        Route::get('/', DashboardController::class)->name('dashboard');
        Route::resource('categories', AdminCategoryController::class)->except(['show']);
        Route::resource('products', AdminProductController::class)->except(['show']);
        Route::get('attributes', [AttributeController::class, 'index'])->name('attributes.index');
        Route::post('attributes', [AttributeController::class, 'store'])->name('attributes.store');
        Route::delete('attributes/{attribute}', [AttributeController::class, 'destroy'])->name('attributes.destroy');
        Route::post('attributes/{attribute}/values', [AttributeController::class, 'storeValue'])->name('attributes.values.store');
        Route::delete('attribute-values/{value}', [AttributeController::class, 'destroyValue'])->name('attributes.values.destroy');
        Route::resource('banners', AdminBannerController::class)->except(['show']);
        Route::resource('shorts', AdminShortController::class)->except(['show']);
        Route::get('reviews', [AdminReviewController::class, 'index'])->name('reviews.index');
        Route::delete('reviews/{review}', [AdminReviewController::class, 'destroy'])->name('reviews.destroy');
        Route::patch('reviews/{review}/toggle', [AdminReviewController::class, 'toggle'])->name('reviews.toggle');
        Route::get('orders', [AdminOrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
        Route::patch('orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.status');

        Route::middleware('super_admin')->group(function () {
            Route::get('payments', [AdminPaymentGatewayController::class, 'index'])->name('payments.index');
            Route::put('payments', [AdminPaymentGatewayController::class, 'update'])->name('payments.update');
            Route::get('languages', [AdminLanguageController::class, 'index'])->name('languages.index');
            Route::put('languages', [AdminLanguageController::class, 'update'])->name('languages.update');
        });
    });
});
