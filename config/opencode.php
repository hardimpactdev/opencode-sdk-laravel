<?php

return [
    'base_url' => env('OPENCODE_BASE_URL', 'http://localhost:4096'),
    'provider' => env('OPENCODE_PROVIDER', 'kimi-for-coding'),
    'model' => env('OPENCODE_MODEL', 'k2p5'),

    'recovery' => [
        'max_attempts' => (int) env('OPENCODE_RECOVERY_MAX_ATTEMPTS', 2),
    ],

    'session' => [
        'stale_threshold_ms' => (int) env('OPENCODE_STALE_THRESHOLD_MS', 120_000),
        'fallback_idle_threshold_ms' => (int) env('OPENCODE_FALLBACK_IDLE_MS', 300_000),
    ],
];
