<?php

return [
    'environment' => env('MPESA_ENVIRONMENT', 'sandbox'),
    'consumer_key' => env('MPESA_CONSUMER_KEY'),
    'consumer_secret' => env('MPESA_CONSUMER_SECRET'),
    'shortcode' => env('MPESA_SHORTCODE'),
    'passkey' => env('MPESA_PASSKEY'),
    'transaction_type' => env('MPESA_TRANSACTION_TYPE', 'CustomerPayBillOnline'),
    'callback_url' => env('MPESA_CALLBACK_URL'),
    'timeout_url' => env('MPESA_TIMEOUT_URL'),
    'result_url' => env('MPESA_RESULT_URL'),
    'confirmation_url' => env('MPESA_CONFIRMATION_URL'),
    'validation_url' => env('MPESA_VALIDATION_URL'),
    'party_b' => env('MPESA_PARTY_B'),
    'initiator_name' => env('MPESA_INITIATOR_NAME'),
    'security_credential' => env('MPESA_SECURITY_CREDENTIAL'),
    'callback_secret' => env('MPESA_CALLBACK_SECRET'),
    'callback_allowed_ips' => env('MPESA_CALLBACK_ALLOWED_IPS', ''),
    'cache_ttl_minutes' => (int) env('MPESA_TOKEN_CACHE_TTL', 55),
    'request_timeout' => (int) env('MPESA_HTTP_TIMEOUT', 30),
];
