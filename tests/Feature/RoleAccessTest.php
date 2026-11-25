<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_kasir_hanya_bisa_akses_products_categories_dan_transactions(): void
    {
        $kasir = User::factory()->create(['role' => 'kasir']);

        $this->actingAs($kasir)->get('/products')->assertOk();
        $this->actingAs($kasir)->get('/categories')->assertOk();
        $this->actingAs($kasir)->get('/transactions/order')->assertOk();
        $this->actingAs($kasir)->get('/transactions/history')->assertOk();
        $this->actingAs($kasir)->get('/users')->assertForbidden();
        $this->actingAs($kasir)->get('/members')->assertForbidden();
        $this->actingAs($kasir)->get('/reports/products')->assertForbidden();
    }

    public function test_admin_bisa_akses_semua_halaman(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        foreach (['/products', '/categories', '/transactions/order', '/transactions/history', '/users', '/members', '/reports/products'] as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }
}
