<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class LandingPageController extends Controller
{
    public function index(): View
    {
        $product = Product::query()->where('is_active', true)->firstOrFail();
        $settings = Setting::query()->pluck('value', 'key')->all();
        $phone = preg_replace('/\D+/', '', $settings['whatsapp_number'] ?? '');
        $whatsappUrl = preg_match('/^[1-9][0-9]{7,14}$/', $phone)
            ? 'https://wa.me/'.$phone.'?text='.rawurlencode('Halo, saya tertarik untuk memesan '.$product->name.'.')
            : null;
        $instagramUrl = filter_var($settings['instagram_url'] ?? null, FILTER_VALIDATE_URL)
            ? $settings['instagram_url']
            : null;
        $productImage = str_starts_with($product->image, 'images/')
            ? asset($product->image)
            : Storage::disk('public')->url($product->image);
        $reservedStock = $product->orders()->where('status', 'pending')->sum('quantity');

        return view('home', [
            'product' => $product,
            'productImage' => $productImage,
            'settings' => $settings,
            'whatsappUrl' => $whatsappUrl,
            'instagramUrl' => $instagramUrl,
            'availableStock' => max(0, $product->stock - $reservedStock),
        ]);
    }
}
