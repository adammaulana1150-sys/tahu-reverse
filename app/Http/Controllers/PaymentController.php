<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function show(string $token): View
    {
        $payment = Payment::query()
            ->with(['order.product'])
            ->where('lookup_token_hash', hash('sha256', $token))
            ->firstOrFail();

        $imagePath = Setting::query()->whereKey('qris_image_path')->value('value');
        $qrisImageUrl = $imagePath && Storage::disk('public')->exists($imagePath)
            ? Storage::disk('public')->url($imagePath)
            : null;
        $phone = preg_replace('/\D+/', '', (string) Setting::query()->whereKey('whatsapp_number')->value('value'));
        $whatsappUrl = $phone !== '' ? 'https://wa.me/'.$phone : null;

        return view('payments.show', compact('payment', 'qrisImageUrl', 'whatsappUrl'));
    }
}
