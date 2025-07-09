<?php

namespace App\Providers;

use App\Mail\Transport\ZeptoMailTransport;
use Illuminate\Mail\MailManager;
use Illuminate\Support\ServiceProvider;

class ZeptoMailServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->app->afterResolving(MailManager::class, function (MailManager $manager) {
            $manager->extend('zeptomail', function ($config) {
                return new ZeptoMailTransport(
                    $config['api_key']
                );
            });
        });
    }
}
