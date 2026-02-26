<?php

namespace App\Providers;

use App\Models\Configuration;
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
        view()->composer('*', function ($view) {
            $country = session('config_country');
            $currency = session('config_currency');

            if (!$country || !$currency) {
                $config = Configuration::first();
                $country = $country ?: $config?->country;
                $currency = $currency ?: $config?->currency;
            }

            $view->with('uiCountry', $country);
            $view->with('uiCurrency', $currency);
        });
    }
}
