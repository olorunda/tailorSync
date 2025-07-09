# ZeptoMail Integration

This document explains how to set up and use ZeptoMail for sending emails in the application.

## Overview

ZeptoMail is an email delivery service provided by Zoho. It offers a reliable way to send transactional emails with high deliverability rates.

## Setup

### 1. Environment Variables

Add the following environment variables to your `.env` file:

```
MAIL_MAILER=zeptomail
ZEPTOMAIL_API_KEY=your_zeptomail_api_key
```

Replace `your_zeptomail_api_key` with your actual ZeptoMail API key.

### 2. Getting a ZeptoMail API Key

1. Sign up for a ZeptoMail account at [https://www.zoho.com/zeptomail/](https://www.zoho.com/zeptomail/)
2. Navigate to the API Keys section in your ZeptoMail dashboard
3. Create a new API key for your application
4. Copy the API key and add it to your `.env` file

## Usage

The application is configured to use ZeptoMail as the default mail driver. All emails sent through Laravel's mail system will automatically use ZeptoMail.

### Example:

```php
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeEmail;

Mail::to($user)->send(new WelcomeEmail($user));
```

## Troubleshooting

If you encounter issues with email sending:

1. Check that your ZeptoMail API key is correct
2. Verify that your ZeptoMail account is active and has sufficient credits
3. Check the Laravel logs for any error messages
4. Ensure your sender email address is verified in ZeptoMail

## Fallback Configuration

If you need to use a different mail driver for testing or development, you can change the `MAIL_MAILER` environment variable:

```
# Use Laravel's log driver for local development
MAIL_MAILER=log

# Use SMTP for testing
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
```
