<?php

return [
    'base_url' => env('OPENCODE_BASE_URL', 'http://localhost:4096'),
    'provider' => env('OPENCODE_PROVIDER', 'kimi-for-coding'),
    'model' => env('OPENCODE_MODEL', 'k2p5'),

    'recovery' => [
        'max_attempts' => (int) env('OPENCODE_RECOVERY_MAX_ATTEMPTS', 2),
    ],
];
