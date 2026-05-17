<?php

namespace App\Providers;

use App\Models\Configuration;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Gate;
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
        // Gate: editar configuración general de la escuela
        // Solo Root y Gerente Deportivo
        Gate::define('config:edit-school', function (User $user) {
            return in_array((int) $user->role, [User::ROLE_ROOT, User::ROLE_SPORT_MANAGER]);
        });

        view()->composer('*', function ($view) {

            $country = session('config_country');
            $currency = session('config_currency');

            if (!$country || !$currency) {
                try {
                    $config = Configuration::first();
                    $country = $country ?: $config?->country;
                    $currency = $currency ?: $config?->currency;
                } catch (QueryException $e) {
                    // Evita un 500 en cascada si la BD no esta disponible.
                }
            }

            $view->with('uiCountry', $country);
            $view->with('uiCurrency', $currency);
            
        });
    }
}
