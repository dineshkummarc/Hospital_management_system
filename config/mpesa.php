<?php

return [
    'env'                => env('MPESA_ENV', 'sandbox'), // 'sandbox' or 'live'
    'shortcode'          => env('MPESA_SHORTCODE', '174379'),
    'passkey'            => env('MPESA_PASSKEY', ''),
    'consumer_key'       => env('MPESA_CONSUMER_KEY', ''),
    'consumer_secret'    => env('MPESA_CONSUMER_SECRET', ''),
    'callback_url'       => env('MPESA_CALLBACK_URL', 'https://example.com/api/mpesa/callback'),
    'account_reference' => env('MPESA_ACCOUNT_REFERENCE', 'HospitalMS'),
    'transaction_desc'  => env('MPESA_TRANSACTION_DESC', 'Hospital Service Payment'),
];
