<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use DatabaseTransactions;

    public function test_kasir_bisa_buat_order_dan_stok_berkurang(): void
    {
        $kasir = User::factory()->create(['role' => 'kasir']);
        $member = Member::create([
            'member_code' => 'MBR-TEST-1',
            'name' => 'Budi',
            'phone' => '08123456789',
        ]);
        $product = Product::create([
            'image' => 'products/test.jpg',
            'name' => 'Kopi Susu',
            'description' => 'Test',
            'price' => 15000,
            'stock' => 10,
        ]);

        $this->actingAs($kasir)->get('/transactions/order')->assertOk();

        $res = $this->actingAs($kasir)->postJson('/transactions/order', [
            'member_id' => $member->id,
            'payment_method' => 'cash',
            'paid_amount' => 50000,
            'notes' => 'Tanpa gula',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2],
            ],
        ]);

        $res->assertCreated()
            ->assertJsonPath('order.total_amount', 30000)
            ->assertJsonPath('order.change_amount', 20000)
            ->assertJsonPath('order.status', 'completed');

        $orderId = $res->json('order.id');
        $this->assertSame(8, $product->fresh()->stock);

        $this->actingAs($kasir)->getJson('/api/transactions')->assertOk();
        $this->actingAs($kasir)->getJson("/transactions/{$orderId}")
            ->assertOk()
            ->assertJsonPath('items.0.subtotal', 30000);
        $this->actingAs($kasir)->get("/transactions/{$orderId}/receipt")->assertOk();
    }

    public function test_order_ditolak_jika_stok_atau_bayar_kurang(): void
    {
        $kasir = User::factory()->create(['role' => 'kasir']);
        $product = Product::create([
            'image' => 'products/test.jpg',
            'name' => 'Teh Tarik',
            'description' => 'Test',
            'price' => 12000,
            'stock' => 2,
        ]);

        // Stok kurang
        $this->actingAs($kasir)->postJson('/transactions/order', [
            'payment_method' => 'cash',
            'paid_amount' => 50000,
            'items' => [['product_id' => $product->id, 'quantity' => 5]],
        ])->assertUnprocessable();

        // Bayar kurang
        $this->actingAs($kasir)->postJson('/transactions/order', [
            'payment_method' => 'qris',
            'paid_amount' => 10000,
            'items' => [['product_id' => $product->id, 'quantity' => 2]],
        ])->assertUnprocessable();

        $this->assertSame(2, $product->fresh()->stock);
    }

    public function test_hanya_admin_yang_bisa_batalkan_order_dan_stok_kembali(): void
    {
        $kasir = User::factory()->create(['role' => 'kasir']);
        $admin = User::factory()->create(['role' => 'admin']);
        $product = Product::create([
            'image' => 'products/test.jpg',
            'name' => 'Roti Bakar',
            'description' => 'Test',
            'price' => 20000,
            'stock' => 5,
        ]);

        $orderId = $this->actingAs($kasir)->postJson('/transactions/order', [
            'payment_method' => 'transfer',
            'paid_amount' => 40000,
            'items' => [['product_id' => $product->id, 'quantity' => 2]],
        ])->assertCreated()->json('order.id');

        $this->assertSame(3, $product->fresh()->stock);

        // Kasir dilarang cancel
        $this->actingAs($kasir)->patchJson("/transactions/{$orderId}/cancel")->assertForbidden();

        // Admin cancel -> stok kembali 5
        $this->actingAs($admin)->patchJson("/transactions/{$orderId}/cancel")->assertOk();
        $this->assertSame('cancelled', Order::find($orderId)->status);
        $this->assertSame(5, $product->fresh()->stock);

        // Cancel dua kali ditolak
        $this->actingAs($admin)->patchJson("/transactions/{$orderId}/cancel")->assertUnprocessable();
        $this->assertSame(5, $product->fresh()->stock);
    }
}
