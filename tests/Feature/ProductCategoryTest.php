<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_produk_bisa_memilih_dan_mengubah_kategori_dari_categories(): void
    {
        $user = User::factory()->create(['role' => 'kasir']);
        $cat1 = Category::create(['name' => 'Minuman']);
        $cat2 = Category::create(['name' => 'Makanan']);

        // Halaman products menampilkan opsi kategori
        $this->actingAs($user)->get('/products')
            ->assertOk()
            ->assertSee('Minuman')
            ->assertSee('Makanan');

        // Create produk dengan kategori valid
        $this->actingAs($user)->post('/products', [
            'category_id' => $cat1->id,
            'name' => 'Es Teh',
            'price' => 5000,
            'stock' => 20,
        ])->assertRedirect(route('products.index'));

        $product = Product::firstOrFail();
        $this->assertSame($cat1->id, $product->category_id);

        // API produk mengembalikan relasi category.name
        $this->actingAs($user)->getJson('/api/products')
            ->assertOk()
            ->assertJsonPath('0.category.name', 'Minuman');

        // Update kategori produk
        $this->actingAs($user)->put("/products/{$product->id}", [
            'category_id' => $cat2->id,
            'name' => 'Es Teh',
            'price' => 5000,
            'stock' => 20,
        ])->assertRedirect(route('products.index'));

        $this->assertSame($cat2->id, $product->fresh()->category_id);

        // Kategori tidak valid ditolak
        $this->actingAs($user)->post('/products', [
            'category_id' => 99999,
            'name' => 'Invalid',
            'price' => 1000,
            'stock' => 1,
        ])->assertSessionHasErrors('category_id');

        // Hapus kategori menjadikan category_id null (tidak crash)
        $cat2->delete();
        $this->assertNull($product->fresh()->category_id);
    }
}
