<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_creates_an_admin_one_product_and_default_settings(): void
    {
        config([
            'app.admin_email' => 'admin@example.test',
            'app.admin_password' => 'a-strong-test-password',
        ]);

        $this->seed();

        $this->assertSame(1, User::count());
        $this->assertTrue(User::first()->is_admin);
        $this->assertTrue(Hash::check('a-strong-test-password', User::first()->password));
        $this->assertSame(1, Product::count());
        $this->assertSame(3, Setting::count());

        Product::first()->update(['price' => 25000]);
        $this->seed();

        $this->assertSame(25000, Product::first()->price);
    }
}
