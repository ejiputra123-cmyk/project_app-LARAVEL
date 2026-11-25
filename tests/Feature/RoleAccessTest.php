<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

// Tanpa database: user tidak disimpan, cukup cek middleware role.
class RoleAccessTest extends TestCase
{
    public function test_kasir_hanya_bisa_akses_products_dan_categories(): void
    {
        $kasir = new User(['name' => 'Kasir', 'email' => 'k@x.com', 'role' => 'kasir']);

        $this->actingAs($kasir)->get('/products')->assertOk();
        $this->actingAs($kasir)->get('/categories')->assertOk();
        $this->actingAs($kasir)->get('/transactions/history')->assertOk();
        $this->actingAs($kasir)->get('/users')->assertForbidden();
        $this->actingAs($kasir)->get('/members')->assertForbidden();
        $this->actingAs($kasir)->get('/reports/products')->assertForbidden();
    }

    public function test_admin_bisa_akses_semua_halaman(): void
    {
        $admin = new User(['name' => 'Admin', 'email' => 'a@x.com', 'role' => 'admin']);

        foreach (['/products', '/categories', '/transactions/history', '/users', '/members', '/reports/products'] as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }
}
