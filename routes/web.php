<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\ProductController;
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

    Route::resource('members', MemberController::class);
    Route::get('api/members', [MemberController::class, 'apiData'])->name('members.api');

    Route::resource('categories', CategoryController::class);
    Route::get('api/categories', [CategoryController::class, 'apiData'])->name('categories.api');

    Route::resource('products', ProductController::class);
    Route::get('api/products', [ProductController::class, 'apiData'])->name('products.api');

    Route::resource('users', UserController::class);
    Route::get('api/users', [UserController::class, 'apiData'])->name('users.api');
});
