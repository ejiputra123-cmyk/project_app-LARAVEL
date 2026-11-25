<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

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
        $this->assertStringStartsWith('INV-' . now()->format('Ymd') . '-', $res->json('order.invoice_number'));
        $this->assertSame(8, $product->fresh()->stock);

        // apiData tidak mengirim items yang tidak dipakai tabel History
        $apiRes = $this->actingAs($kasir)->getJson('/api/transactions')->assertOk();
        $this->assertArrayNotHasKey('items', $apiRes->json('0'));

        $this->actingAs($kasir)->getJson("/transactions/{$orderId}")
            ->assertOk()
            ->assertJsonPath('items.0.subtotal', 30000);
        $this->actingAs($kasir)->get("/transactions/{$orderId}/receipt")->assertOk();
    }

    public function test_harga_palsu_dari_client_diabaikan_dan_multi_item_umum_dihitung_server(): void
    {
        $kasir = User::factory()->create(['role' => 'kasir']);
        $p1 = Product::create(['name' => 'Kopi', 'price' => 10000, 'stock' => 5]);
        $p2 = Product::create(['name' => 'Snack', 'price' => 7500, 'stock' => 4]);

        $res = $this->actingAs($kasir)->postJson('/transactions/order', [
            'member_id' => null,
            'payment_method' => 'qris',
            'paid_amount' => 35000,
            'total_amount' => 1, // manipulasi client harus diabaikan
            'items' => [
                ['product_id' => $p1->id, 'quantity' => 2, 'price' => 1],
                ['product_id' => $p2->id, 'quantity' => 2, 'price' => 1],
            ],
        ]);

        $res->assertCreated()
            ->assertJsonPath('order.member_id', null)
            ->assertJsonPath('order.total_amount', 35000)
            ->assertJsonPath('order.change_amount', 0);

        $this->assertSame(3, $p1->fresh()->stock);
        $this->assertSame(2, $p2->fresh()->stock);
    }

    public function test_order_ditolak_jika_stok_kurang_bayar_kurang_atau_produk_duplikat(): void
    {
        $kasir = User::factory()->create(['role' => 'kasir']);
        $product = Product::create(['name' => 'Teh Tarik', 'price' => 12000, 'stock' => 2]);

        $this->actingAs($kasir)->postJson('/transactions/order', [
            'payment_method' => 'cash',
            'paid_amount' => 50000,
            'items' => [['product_id' => $product->id, 'quantity' => 5]],
        ])->assertUnprocessable();

        $this->actingAs($kasir)->postJson('/transactions/order', [
            'payment_method' => 'qris',
            'paid_amount' => 10000,
            'items' => [['product_id' => $product->id, 'quantity' => 2]],
        ])->assertUnprocessable();

        $this->actingAs($kasir)->postJson('/transactions/order', [
            'payment_method' => 'cash',
            'paid_amount' => 50000,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ])->assertUnprocessable();

        $this->assertSame(2, $product->fresh()->stock);
    }

    public function test_hanya_admin_yang_bisa_batalkan_order_dan_stok_kembali(): void
    {
        $kasir = User::factory()->create(['role' => 'kasir']);
        $admin = User::factory()->create(['role' => 'admin']);
        $p1 = Product::create(['name' => 'Roti Bakar', 'price' => 20000, 'stock' => 5]);
        $p2 = Product::create(['name' => 'Susu', 'price' => 10000, 'stock' => 3]);

        $orderId = $this->actingAs($kasir)->postJson('/transactions/order', [
            'payment_method' => 'transfer',
            'paid_amount' => 50000,
            'items' => [
                ['product_id' => $p1->id, 'quantity' => 2],
                ['product_id' => $p2->id, 'quantity' => 1],
            ],
        ])->assertCreated()->json('order.id');

        // Hapus salah satu produk untuk memastikan cancel & receipt tetap aman
        $p2->delete();

        $this->actingAs($kasir)->patchJson("/transactions/{$orderId}/cancel")->assertForbidden();

        $this->actingAs($admin)->patchJson("/transactions/{$orderId}/cancel")->assertOk();
        $this->assertSame('cancelled', Order::find($orderId)->status);
        $this->assertSame(5, $p1->fresh()->stock);
        $this->actingAs($admin)->get("/transactions/{$orderId}/receipt")->assertOk();

        // Cancel dua kali ditolak
        $this->actingAs($admin)->patchJson("/transactions/{$orderId}/cancel")->assertUnprocessable();
        $this->assertSame(5, $p1->fresh()->stock);
    }

    public function test_user_yang_memiliki_transaksi_tidak_bisa_dihapus(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kasir = User::factory()->create(['role' => 'kasir']);
        $product = Product::create(['name' => 'Air Mineral', 'price' => 5000, 'stock' => 10]);

        $this->actingAs($kasir)->postJson('/transactions/order', [
            'payment_method' => 'cash',
            'paid_amount' => 5000,
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ])->assertCreated();

        $this->actingAs($admin)->delete("/users/{$kasir->id}")
            ->assertRedirect(route('users.index'))
            ->assertSessionHasErrors();

        $this->assertDatabaseHas('users', ['id' => $kasir->id]);
    }
}
