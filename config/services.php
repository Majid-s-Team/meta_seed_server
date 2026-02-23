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

    'agora' => [
        'app_id' => trim((string) env('AGORA_APP_ID', '')),
        'app_certificate' => trim((string) env('AGORA_APP_CERTIFICATE', '')),
        'webhook_secret' => trim((string) env('AGORA_WEBHOOK_SECRET', '')),
        'webhook_allowed_ips' => array_filter(array_map('trim', explode(',', env('AGORA_WEBHOOK_ALLOWED_IPS', '')))),
        // RTLS Ingress (OBS): stream key API uses RESTful API credentials (not App Certificate)
        'rtls_customer_id' => trim((string) env('AGORA_RTLS_CUSTOMER_ID', '')),
        'rtls_customer_secret' => trim((string) env('AGORA_RTLS_CUSTOMER_SECRET', '')),
        'rtls_api_base' => trim((string) env('AGORA_RTLS_API_BASE', 'https://api.sd-rtn.com')),
        'rtls_region' => strtolower(trim((string) env('AGORA_RTLS_REGION', 'ap'))), // ap | cn | eu | na
        'rtls_rtmp_url' => trim((string) env('AGORA_RTLS_RTMP_URL', 'rtmp://rtls-ingress-prod-ap.agoramdn.com/live')),
    ],

    'livestream' => [
        'local_test' => filter_var(env('LIVESTREAM_LOCAL_TEST', false), FILTER_VALIDATE_BOOLEAN),
        'access_expiry_hours' => (int) env('LIVESTREAM_ACCESS_EXPIRY_HOURS', 24),
    ],

];
