<?php

namespace App\Providers;

use App\Mail\Transport\ZeptoMailTransport;
use GuzzleHttp\Client;
use Illuminate\Mail\MailManager;
use Illuminate\Support\ServiceProvider;

class ZeptoMailServiceProvider extends ServiceProvider
{
    /**
     * Register the ZeptoMail Transport instance.
     *
     * @return void
     */
    public function register()
    {
        $this->app->afterResolving(MailManager::class, function (MailManager $manager) {
            $manager->extend('zeptomail', function ($config) {
                $client = new Client();
                $key = $config['api_key'] ?? config('services.zeptomail.api_key');
                $endpoint = $config['endpoint'] ?? config('services.zeptomail.endpoint', 'https://api.zeptomail.com/v1.1/email');

                return new ZeptoMailTransport($client, $key, $endpoint);
            });
        });
    }
}
