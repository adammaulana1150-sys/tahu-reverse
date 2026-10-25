<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    private const MIN_PASSWORD_LENGTH = 12;

    /**
     * Commonly-used / breached passwords that must never be used as the admin seed.
     */
    private const COMMON_PASSWORDS = [
        'password', 'admin', 'admin123', '123456', '123456789', 'qwerty',
        'letmein', 'welcome', 'monkey', 'dragon', 'master', 'login',
        'abc123', 'password1', 'admin@123', 'root', 'toor',
    ];

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $adminEmail = config('app.admin_email');
        $adminPassword = config('app.admin_password');

        if (! $adminEmail || ! $adminPassword) {
            throw new RuntimeException(
                'Set ADMIN_EMAIL and ADMIN_PASSWORD in .env before running the seeder.'
            );
        }

        if (strlen($adminPassword) < self::MIN_PASSWORD_LENGTH) {
            throw new RuntimeException(
                'ADMIN_PASSWORD is too weak: minimum '.self::MIN_PASSWORD_LENGTH.' characters required.'
            );
        }

        $lower = strtolower($adminPassword);
        if (in_array($lower, self::COMMON_PASSWORDS, true)) {
            throw new RuntimeException('ADMIN_PASSWORD must not be a common or guessable password.');
        }

        User::updateOrCreate(['email' => $adminEmail], [
            'name' => 'Administrator',
            'password' => Hash::make($adminPassword),
            'is_admin' => true,
        ]);

        Product::firstOrCreate(['slug' => 'tahu-reverse'], [
            'name' => 'Tahu Reverse',
            'description' => 'Olahan tahu terinspirasi tahu walik, dengan kulit tahu yang dibalik dan isian adonan tepung berbumbu.',
            'price' => 10000,
            'stock' => 100,
            'image' => 'images/product.jpg',
            'is_active' => true,
        ]);

        foreach ([
            'whatsapp_number' => '',
            'instagram_url' => '',
            'site_title' => 'Tahu Reverse',
        ] as $key => $value) {
            Setting::firstOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
