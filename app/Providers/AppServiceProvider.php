<?php

namespace App\Providers;

use App\View\Components\SimpleSelect;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use App\Providers\ZeptoMailServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->register(AuthServiceProvider::class);
        $this->app->register(ZeptoMailServiceProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register the OrderObserver
        \App\Models\Order::observe(\App\Observers\OrderObserver::class);

        // Register the SimpleSelect component
        Blade::component('simple-select', SimpleSelect::class);
    }
}
