<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        Product::create([
            'name' => 'Tahu Reverse',
            'slug' => 'tahu-reverse',
            'description' => 'Tahu walik.',
            'price' => 10000,
            'stock' => 10,
            'image' => 'images/product.jpg',
            'is_active' => true,
        ]);
        Setting::create(['key' => 'site_title', 'value' => 'Tahu Reverse']);
        Setting::create(['key' => 'whatsapp_number', 'value' => '']);
        Setting::create(['key' => 'instagram_url', 'value' => '']);

        $response = $this->get('/');

        $response
            ->assertOk()
            ->assertSee('Tahu Reverse')
            ->assertSee('Created by XL PPLG 3 — Kelompok Tahu Reverse');
    }
}
