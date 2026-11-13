<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // display a listing of the resource.
    public function index()
    {
        return view('products.index');
    }

    public function apiData()
    {
        $products = \App\Models\Product::latest()->get();
        return response()->json($products);
    }
}
