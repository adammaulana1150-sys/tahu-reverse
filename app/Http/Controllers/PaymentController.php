<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function ready(string $token): View
    {
        $payment = $this->findPayment($token);
        $imagePath = Setting::query()->whereKey('qris_image_path')->value('value');
        $qrisReady = $imagePath && Storage::disk('public')->exists($imagePath);

        return view('payments.ready', compact('payment', 'token', 'qrisReady'));
    }

    public function show(string $token): View
    {
        $payment = $this->findPayment($token);

        $imagePath = Setting::query()->whereKey('qris_image_path')->value('value');
        $qrisImageUrl = $imagePath && Storage::disk('public')->exists($imagePath)
            ? Storage::disk('public')->url($imagePath)
            : null;
        $phone = preg_replace('/\D+/', '', (string) Setting::query()->whereKey('whatsapp_number')->value('value'));
        $whatsappUrl = $phone !== '' ? 'https://wa.me/'.$phone : null;

        return view('payments.show', compact('payment', 'qrisImageUrl', 'whatsappUrl'));
    }

    private function findPayment(string $token): Payment
    {
        return Payment::query()
            ->with(['order.product'])
            ->where('lookup_token_hash', hash('sha256', $token))
            ->firstOrFail();
    }
}
