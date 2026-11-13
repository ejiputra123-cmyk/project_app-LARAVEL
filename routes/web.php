<?php

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

// Route::get('products', function () {
//     return view('products.index');
// });

Route::resource('products', ProductController::class);
Route::get('api/products', [ProductController::class, 'apiData'])->name('products.api');