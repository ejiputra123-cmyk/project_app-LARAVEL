<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_hanya_admin_yang_bisa_akses_semua_halaman_dan_api_laporan(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kasir = User::factory()->create(['role' => 'kasir']);

        $urls = [
            '/reports/transactions',
            '/reports/products',
            '/reports/members',
            '/api/reports/transactions',
            '/api/reports/products',
            '/api/reports/members',
        ];

        foreach ($urls as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
            $this->actingAs($kasir)->get($url)->assertForbidden();
        }
    }

    public function test_laporan_mengabaikan_transaksi_cancelled_dan_menerapkan_filter(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $m1 = Member::create(['member_code' => 'MBR-001', 'name' => 'Ani', 'phone' => '08111']);
        $m2 = Member::create(['member_code' => 'MBR-002', 'name' => 'Budi', 'phone' => '08222']);

        $p1 = Product::create(['name' => 'Kopi', 'price' => 10000, 'stock' => 3]);
        $p2 = Product::create(['name' => 'Teh', 'price' => 5000, 'stock' => 20]);

        $completed = Order::create([
            'invoice_number' => 'INV-20261125-0001',
            'user_id' => $admin->id,
            'member_id' => $m2->id,
            'total_amount' => 30000,
            'paid_amount' => 30000,
            'change_amount' => 0,
            'payment_method' => 'qris',
            'status' => 'completed',
        ]);
        $completed->items()->create([
            'product_id' => $p1->id,
            'product_name' => $p1->name,
            'price' => 10000,
            'quantity' => 3,
            'subtotal' => 30000,
        ]);

        $cancelled = Order::create([
            'invoice_number' => 'INV-20261125-0002',
            'user_id' => $admin->id,
            'member_id' => $m1->id,
            'total_amount' => 50000,
            'paid_amount' => 50000,
            'change_amount' => 0,
            'payment_method' => 'cash',
            'status' => 'cancelled',
        ]);
        $cancelled->items()->create([
            'product_id' => $p2->id,
            'product_name' => $p2->name,
            'price' => 5000,
            'quantity' => 10,
            'subtotal' => 50000,
        ]);

        // Transactions report: 2 transaksi, pendapatan hanya 30000 (cancelled diabaikan)
        $this->actingAs($admin)->getJson('/api/reports/transactions')
            ->assertOk()
            ->assertJsonPath('summary.total_transactions', 2)
            ->assertJsonPath('summary.total_revenue', 30000);

        // Filter payment_method=qris & validasi filter ilegal
        $this->actingAs($admin)->getJson('/api/reports/transactions?payment_method=qris')
            ->assertOk()
            ->assertJsonPath('summary.total_transactions', 1)
            ->assertJsonPath('summary.total_revenue', 30000);
        $this->actingAs($admin)->getJson('/api/reports/transactions?status=invalid')
            ->assertUnprocessable();

        // Products report: 2 produk, 1 stok menipis (<=5), Kopi terlaris (3), Teh 0 karena cancelled diabaikan
        $this->actingAs($admin)->getJson('/api/reports/products')
            ->assertOk()
            ->assertJsonPath('summary.total_products', 2)
            ->assertJsonPath('summary.low_stock_count', 1)
            ->assertJsonPath('data.0.name', 'Kopi')
            ->assertJsonPath('data.0.qty_sold', 3)
            ->assertJsonPath('data.0.revenue', 30000)
            ->assertJsonPath('data.1.qty_sold', 0);

        // Members report: Budi pertama (30000), Ani 0 karena transaksinya cancelled
        $this->actingAs($admin)->getJson('/api/reports/members')
            ->assertOk()
            ->assertJsonPath('data.0.member_code', 'MBR-002')
            ->assertJsonPath('data.0.transactions_count', 1)
            ->assertJsonPath('data.0.total_spent', 30000)
            ->assertJsonPath('data.1.total_spent', 0);
    }
}
