<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if (class_exists(\Laravel\Fortify\Fortify::class)) {
            \Laravel\Fortify\Fortify::ignoreRoutes();
        }

        if (class_exists(\Laravel\Jetstream\Jetstream::class)) {
            \Laravel\Jetstream\Jetstream::ignoreRoutes();
        }

        view()->composer('layouts.master', function ($view) {
            $view->with('setting', Setting::first());
        });
        view()->composer('layouts.auth', function ($view) {
            $view->with('setting', Setting::first());
        });
        view()->composer('auth.login', function ($view) {
            $view->with('setting', Setting::first());
        });
    }

    public function boot()
    {
        //
    }
}
