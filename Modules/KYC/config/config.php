<?php

return [
    'name' => 'KYC',

    'youverify' => [
        'base_url' => rtrim(env('YOUVERIFY_BASE_URL', 'https://api.youverify.co'), '/'),
        'public_key' => env('YOUVERIFY_PUBLIC_KEY'),
        'secret_key' => env('YOUVERIFY_SECRET_KEY'),
        'webhook_secret' => env('YOUVERIFY_WEBHOOK_SECRET'),
        'timeout' => (int) env('YOUVERIFY_TIMEOUT', 15),
        'endpoints' => [
            'identity' => '/v2/identities/verify',
            'status' => '/v2/identities/{reference}',
        ],
    ],
];
