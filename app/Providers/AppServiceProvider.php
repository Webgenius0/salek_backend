<?php

namespace App\Providers;

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
        if (!defined('MONTHLY_SUBSCRIPTION')) {
            define('MONTHLY_SUBSCRIPTION', 50);
            define('QUARTERLY_DISCOUNT', 10);
            define('ANNUAL_DISCOUNT', 16);
            define('TOTAL_NUMBER', 100);
        }
    }
}
