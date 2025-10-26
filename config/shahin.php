<?php

use Mrmr7\LaravelShahin\CacheTokenStorage;

return [
    'sandbox' => true, // If true sandbox is enabled
    'base_url' => env('SHAHIN_BASE_URL'),
    'sandbox_base_url' => env('SHAHIN_SANDBOX_BASE_URL', 'https://10.10.10.112'),
    'port' => env('SHAHIN_PORT', '28453'),
    'port_1way_without_signature' => env('PORT_1WAY_WITHOUT_SIGNATURE', '38453'),
    'port_1way_with_signature' => env('PORT_1WAY_WITH_SIGNATURE', '58453'),
    'port_2way_with_signature' => env('PORT_2WAY_WITH_SIGNATURE', '48453'),
    'token_storage_class' => CacheTokenStorage::class,
    'bank' => env('SHAHIN_BANK', 'BSI'),
    'client_id' => env('SHAHIN_CLIENT_ID'),
    'client_secret' => env('SHAHIN_CLIENT_SECRET'),
];
