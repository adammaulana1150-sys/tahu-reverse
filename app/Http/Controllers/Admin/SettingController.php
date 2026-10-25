<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSettingsRequest;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use finfo;

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
        'qris_image_path',
    ];

    public function edit(): View
    {
        $settings = Setting::query()->pluck('value', 'key');
        $qrisImagePath = $settings['qris_image_path'] ?? null;
        $qrisImageUrl = $qrisImagePath && Storage::disk('public')->exists($qrisImagePath)
            ? Storage::disk('public')->url($qrisImagePath)
            : null;

        return view('admin.settings.edit', [
            'settings' => $settings,
            'qrisImageUrl' => $qrisImageUrl,
        ]);
    }

    public function update(UpdateSettingsRequest $request): RedirectResponse
    {
        $values = $request->validated();
        unset($values['qris_image'], $values['remove_qris_image']);

        // Explicit allow-list: ignore any validated key not in the list.
        $values = array_intersect_key($values, array_flip(self::ALLOWED_KEYS));

        if (isset($values['whatsapp_number'])) {
            $values['whatsapp_number'] = preg_replace('/\D+/', '', (string) $values['whatsapp_number']);
            if (str_starts_with($values['whatsapp_number'], '0')) {
                $values['whatsapp_number'] = '62'.substr($values['whatsapp_number'], 1);
            }
        }

        // Validate uploaded QRIS image content type in addition to Laravel's
        // image/mimes rule to mitigate polyglot uploads.
        if ($request->hasFile('qris_image')) {
            $uploadedFile = $request->file('qris_image');
            $tmpPath = $uploadedFile->getRealPath();
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($tmpPath);

            $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
            if (! in_array($mimeType, $allowedMimes, true)) {
                return back()->withErrors([
                    'qris_image' => 'File yang diunggah bukan gambar QRIS yang valid (JPG, PNG, atau WebP).',
                ])->withInput();
            }

            $newImagePath = $uploadedFile->store('qris', 'public');
            if ($newImagePath === false) {
                return back()->withErrors(['qris_image' => 'Gambar QRIS gagal disimpan. Silakan coba lagi.'])->withInput();
            }

            $values['qris_image_path'] = $newImagePath;
        } elseif ($request->boolean('remove_qris_image')) {
            $values['qris_image_path'] = null;
        }

        $oldImagePath = Setting::query()->whereKey('qris_image_path')->value('value');

        DB::transaction(function () use ($values): void {
            foreach ($values as $key => $value) {
                Setting::updateOrCreate(['key' => $key], ['value' => $value]);
            }
        });

        if ($oldImagePath && array_key_exists('qris_image_path', $values) && $oldImagePath !== $values['qris_image_path']) {
            Storage::disk('public')->delete($oldImagePath);
        }

        return back()->with('status', 'Pengaturan berhasil diperbarui.');
    }
}
