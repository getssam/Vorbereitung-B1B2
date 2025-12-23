<?php
/**
 * Application Configuration
 *
 * General application settings
 */

return [
    'name' => $_ENV['APP_NAME'] ?? 'Vorbereitung B1/B2',
    'url' => $_ENV['APP_URL'] ?? 'http://localhost/Vorbereitung-B1B2',
    'env' => $_ENV['APP_ENV'] ?? 'production',
    'debug' => ($_ENV['APP_DEBUG'] ?? 'false') === 'true',
    'timezone' => 'Europe/Berlin',

    // Security
    'password_min_length' => (int)($_ENV['PASSWORD_MIN_LENGTH'] ?? 8),
    'csrf_token_name' => $_ENV['CSRF_TOKEN_NAME'] ?? 'csrf_token',

    // Paths
    'base_path' => dirname(__DIR__),
    'app_path' => dirname(__DIR__) . '/app',
    'public_path' => dirname(__DIR__) . '/public',
    'storage_path' => dirname(__DIR__) . '/storage',
    'views_path' => dirname(__DIR__) . '/app/views',

    // Logging
    'log_path' => dirname(__DIR__) . '/' . ($_ENV['LOG_PATH'] ?? 'storage/logs/app.log'),
    'log_level' => $_ENV['LOG_LEVEL'] ?? 'error',
];
