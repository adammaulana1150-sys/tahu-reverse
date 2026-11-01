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
        Setting::create(['key' => 'whatsapp_number', 'value' => '6289517447079']);
        Setting::create(['key' => 'instagram_url', 'value' => 'https://www.instagram.com/tahu.reverse?dlrf=bGdxbXQzcDhmYW9q']);
        Setting::create(['key' => 'contact_email', 'value' => 'tahureverse@gmail.com']);

        $response = $this->get('/');

        $response
            ->assertOk()
            ->assertSee('Tahu Reverse')
            ->assertSee('6289517447079')
            ->assertSee('https://www.instagram.com/tahu.reverse?dlrf=bGdxbXQzcDhmYW9q', false)
            ->assertSee('tahureverse@gmail.com')
            ->assertSee('Created by XL PPLG 3 — Kelompok Tahu Reverse');
    }
}
