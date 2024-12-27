<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        try {
            $setting = Setting::latest()->first();

            if (!defined('MONTHLY_SUBSCRIPTION')) {
                define('MONTHLY_SUBSCRIPTION', $setting->subscription_fee ?? 50);
                define('QUARTERLY_DISCOUNT', 10);
                define('ANNUAL_DISCOUNT', 16);
                define('TOTAL_NUMBER', 100);
            }
        } catch (\Exception $e) {
            if (!defined('MONTHLY_SUBSCRIPTION')) {
                define('MONTHLY_SUBSCRIPTION', 50);
                define('QUARTERLY_DISCOUNT', 10);
                define('ANNUAL_DISCOUNT', 16);
                define('TOTAL_NUMBER', 100);
            }

            Log::error('Error in boot method: ' . $e->getMessage());
        }
    }
}
