<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Model\Products;

class ProductSeeder extends Seeder
{
    // Run the database seeds.
    public function run(): void
    {
        DB::table('products')->insert([
            [
                'image' => 'https://images.unsplash.com/photo-1612832021080-1e3f5b8c9f1d',
                'name' => 'Product 1',
                'description' => 'Description for Product 1',
                'price' => 19.99,
                'stock' => 100,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'image' => 'https://images.unsplash.com/photo-1612832021080-1e3f5b8c9f1d',
                'name' => 'Product 2',
                'description' => 'Description for Product 2',
                'price' => 29.99,
                'stock' => 50,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Product::factory()->count(10)->create();
    }
}