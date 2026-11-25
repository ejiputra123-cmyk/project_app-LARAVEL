<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('products.index')
        : redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.post');
    Route::get('register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('register', [AuthController::class, 'register'])->name('register.post');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    // Admin & kasir
    Route::resource('categories', CategoryController::class);
    Route::get('api/categories', [CategoryController::class, 'apiData'])->name('categories.api');

    Route::resource('products', ProductController::class);
    Route::get('api/products', [ProductController::class, 'apiData'])->name('products.api');

    Route::get('transactions/order', [TransactionController::class, 'order'])->name('transactions.order');
    Route::post('transactions/order', [TransactionController::class, 'store'])->name('transactions.store');
    Route::get('transactions/history', [TransactionController::class, 'history'])->name('transactions.history');
    Route::get('api/transactions', [TransactionController::class, 'apiData'])->name('transactions.api');
    Route::get('transactions/{order}/receipt', [TransactionController::class, 'receipt'])->whereNumber('order')->name('transactions.receipt');
    Route::get('transactions/{order}', [TransactionController::class, 'show'])->whereNumber('order')->name('transactions.show');

    // Khusus admin
    Route::middleware('can:admin')->group(function () {
        Route::patch('transactions/{order}/cancel', [TransactionController::class, 'cancel'])->whereNumber('order')->name('transactions.cancel');

        Route::get('reports/transactions', [ReportController::class, 'transactions'])->name('reports.transactions');
        Route::get('api/reports/transactions', [ReportController::class, 'transactionsData'])->name('reports.transactions.api');
        Route::get('reports/products', [ReportController::class, 'products'])->name('reports.products');
        Route::get('api/reports/products', [ReportController::class, 'productsData'])->name('reports.products.api');
        Route::get('reports/members', [ReportController::class, 'members'])->name('reports.members');
        Route::get('api/reports/members', [ReportController::class, 'membersData'])->name('reports.members.api');

        Route::resource('members', MemberController::class);
        Route::get('api/members', [MemberController::class, 'apiData'])->name('members.api');

        Route::resource('users', UserController::class);
        Route::get('api/users', [UserController::class, 'apiData'])->name('users.api');
    });
});
