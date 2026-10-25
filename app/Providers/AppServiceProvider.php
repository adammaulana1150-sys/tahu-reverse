<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $adminPath = config('app.admin_path');

        if (app()->environment() !== 'local' && empty($adminPath)) {
            Log::warning(
                'ADMIN_PATH is not set. The admin panel will be unreachable until a unique ADMIN_PATH is configured.'
            );
        }
    }
}
