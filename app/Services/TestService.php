<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;

class TestService
{
    public function sendMailSes()
    {
        return Mail::raw('This is a test email via Zoho Mail!', function ($message) {
            $message->to('o.olorunda@ttp.com.ng')
                ->subject('Test Zoho Mail');
        });
    }
}
