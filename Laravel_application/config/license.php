<?php
return [
    'server_url' => env('LICENSE_SERVER_URL', 'https://license.example.com'),
    'public_key_path' => storage_path('app/keys/license_public.pem'), // copy server public key here
    'token_path' => storage_path('app/license.token'),
    'validate_every_minutes' => 60*12, // 12 hours
    'offline_grace_days' => 7,
];
