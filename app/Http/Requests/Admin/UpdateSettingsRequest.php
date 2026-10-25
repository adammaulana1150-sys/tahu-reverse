<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_admin === true;
    }

    public function rules(): array
    {
        return [
            'whatsapp_number' => ['nullable', 'string', 'regex:/^(?:\+?62|0)8[1-9][0-9]{6,11}$/'],
            'instagram_url' => ['nullable', 'url', 'max:2048'],
            'site_title' => ['required', 'string', 'max:120'],
            'qris_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_qris_image' => ['nullable', 'boolean'],
        ];
    }
}
