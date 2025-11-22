<?php
// FILE: /config/config.php

/**
 * Main Application Configuration
 * SplashWhats - Multi-tenant WhatsApp SaaS Platform
 */

return [
    'app' => [
        'name' => getenv('APP_NAME') ?: 'SplashWhats',
        'url' => getenv('APP_URL') ?: 'http://localhost',
        'timezone' => getenv('APP_TIMEZONE') ?: 'UTC',
    ],

    'session' => [
        'lifetime' => getenv('SESSION_LIFETIME') ?: 7200, // 2 hours
    ],

    'upload' => [
        'max_size' => getenv('MAX_UPLOAD_SIZE') ?: 5242880, // 5MB
        'allowed_types' => explode(',', getenv('ALLOWED_UPLOAD_TYPES') ?: 'csv,jpg,jpeg,png'),
        'path' => __DIR__ . '/../storage/uploads/',
    ],

    'pagination' => [
        'items_per_page' => getenv('ITEMS_PER_PAGE') ?: 20,
    ],

    'mail' => [
        'host' => getenv('MAIL_HOST') ?: 'smtp.example.com',
        'port' => getenv('MAIL_PORT') ?: 587,
        'username' => getenv('MAIL_USERNAME') ?: '',
        'password' => getenv('MAIL_PASSWORD') ?: '',
        'from_address' => getenv('MAIL_FROM_ADDRESS') ?: 'noreply@splashwhats.com',
        'from_name' => getenv('MAIL_FROM_NAME') ?: 'SplashWhats',
    ],
];
