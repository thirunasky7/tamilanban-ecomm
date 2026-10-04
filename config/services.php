<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'razorpay' => [
        'key_id' => env('RAZORPAY_KEY_ID'),
        'key_secret' => env('RAZORPAY_KEY_SECRET'),
    ],

    'sms' => [
        'enabled' => env('SMS_ENABLED', false),
        'base_url' => env('SMS_BASE_URL', 'https://sms.zennexs.com/api/v1'),
        'api_key' => env('SMS_API_KEY'),
        'api_secret' => env('SMS_API_SECRET'),
        'country_code' => env('SMS_DEFAULT_COUNTRY_CODE', '91'),
        // Fixed OTP for QA and store-reviewer access. Never honoured when
        // APP_ENV=production, regardless of how it is configured.
        'dummy_otp' => env('SMS_DUMMY_OTP'),
        // Optional comma-separated mobile allowlist. Empty means "any number".
        'dummy_otp_mobiles' => env('SMS_DUMMY_OTP_MOBILES', ''),
        // Opt-in escape hatch that lets an allowlisted number use the fixed OTP
        // on a production server. Off by default, and ignored without an
        // allowlist so it can never become a global bypass.
        'allow_dummy_in_production' => env('SMS_ALLOW_DUMMY_IN_PRODUCTION', false),
    ],

];
