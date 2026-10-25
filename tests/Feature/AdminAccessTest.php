<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_rejects_guests(): void
    {
        $this->get(route('admin.login'))->assertOk();
        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_admin_dashboard_rejects_authenticated_non_admins(): void
    {
        $user = User::create([
            'name' => 'Guest',
            'email' => 'guest@example.test',
            'password' => 'password',
            'is_admin' => false,
        ]);

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_admin_can_render_all_management_pages(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.test',
            'password' => 'password',
            'is_admin' => true,
        ]);
        Product::create([
            'name' => 'Tahu Reverse',
            'slug' => 'tahu-reverse',
            'description' => 'Olahan tahu.',
            'price' => 10000,
            'stock' => 10,
            'image' => 'images/product.jpg',
            'is_active' => true,
        ]);
        Setting::create(['key' => 'site_title', 'value' => 'Tahu Reverse']);
        Setting::create(['key' => 'whatsapp_number', 'value' => '']);
        Setting::create(['key' => 'instagram_url', 'value' => '']);

        $this->actingAs($admin);

        foreach ([
            'admin.dashboard',
            'admin.product.edit',
            'admin.orders.index',
            'admin.customers.index',
            'admin.settings.edit',
        ] as $routeName) {
            $this->get(route($routeName))->assertOk();
        }
    }
}
