<?php
// config/bvn-verification.php

return [
    /*
    |--------------------------------------------------------------------------
    | BVN Verification Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for BVN verification package.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | API Key
    |--------------------------------------------------------------------------
    |
    | Your NIBSS NPS API key. For testing, you can use any string in mock mode.
    |
    */
    'api_key' => env('BVN_VERIFICATION_API_KEY', 'mock-key'),

    /*
    |--------------------------------------------------------------------------
    | Sandbox Mode
    |--------------------------------------------------------------------------
    |
    | Whether to use sandbox environment. Always true for mock modes.
    |
    */
    'sandbox_mode' => env('BVN_VERIFICATION_SANDBOX_MODE', true),

    /*
    |--------------------------------------------------------------------------
    | Mode
    |--------------------------------------------------------------------------
    |
    | Operation mode: 'live' for real NIBSS API, 'json-mock' for JSON-based mock data
    |
    */
    'mode' => env('BVN_VERIFICATION_MODE', 'json-mock'),

    /*
    |--------------------------------------------------------------------------
    | JSON Data File Path (for mock mode)
    |--------------------------------------------------------------------------
    |
    | Path to JSON file containing mock BVN data (only for json-mock mode)
    |
    */
    'json_data_file' => env('BVN_VERIFICATION_JSON_FILE', null),
];