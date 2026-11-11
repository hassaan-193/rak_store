<?php

return [
    // Master switch: when false, nothing is sent to the portal and the API refuses calls.
    'enabled' => env('PORTAL_INTEGRATION_ENABLED', false),

    'url' => rtrim(env('PORTAL_URL', ''), '/'),

    // Key the portal uses to sign calls to the store (we verify with it).
    'inbound_key_id' => env('PORTAL_TO_STORE_KEY_ID', 'portal'),
    'inbound_secret' => env('PORTAL_TO_STORE_SECRET'),

    // Key the store uses to sign calls to the portal.
    'outbound_key_id' => env('STORE_TO_PORTAL_KEY_ID', 'store'),
    'outbound_secret' => env('STORE_TO_PORTAL_SECRET'),

    // Requests older or newer than this many seconds are rejected (replay protection).
    'max_clock_skew' => env('PORTAL_MAX_CLOCK_SKEW', 300),

    // Comma separated list; empty means any IP is allowed.
    'allowed_ips' => array_filter(array_map('trim', explode(',', env('PORTAL_ALLOWED_IPS', '')))),

    'timeout' => 10,
    'max_attempts' => 10,
];
