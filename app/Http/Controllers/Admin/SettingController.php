<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSettingsRequest;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SettingController extends Controller
{
    /**
     * Settings that are allowed to be updated through this endpoint.
     * Any other key is silently ignored to prevent arbitrary setting injection.
     */
    private const ALLOWED_KEYS = [
        'site_title',
        'whatsapp_number',
        'instagram_url',
        'contact_email',
    ];

    public function edit(): View
    {
        $settings = Setting::query()->pluck('value', 'key');

        return view('admin.settings.edit', [
            'settings' => $settings,
        ]);
    }

    public function update(UpdateSettingsRequest $request): RedirectResponse
    {
        $values = $request->validated();

        // Explicit allow-list: ignore any validated key not in the list.
        $values = array_intersect_key($values, array_flip(self::ALLOWED_KEYS));

        if (isset($values['whatsapp_number'])) {
            $values['whatsapp_number'] = preg_replace('/\D+/', '', (string) $values['whatsapp_number']);
            if (str_starts_with($values['whatsapp_number'], '0')) {
                $values['whatsapp_number'] = '62'.substr($values['whatsapp_number'], 1);
            }
        }

        DB::transaction(function () use ($values): void {
            foreach ($values as $key => $value) {
                Setting::updateOrCreate(['key' => $key], ['value' => $value]);
            }
        });

        return back()->with('status', 'Pengaturan berhasil diperbarui.');
    }
}
