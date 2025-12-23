<?php
/**
 * Session Configuration
 *
 * PHP session settings for authentication and security
 */

return [
    // Session lifetime in minutes
    'lifetime' => (int)($_ENV['SESSION_LIFETIME'] ?? 15),

    // Session cookie name
    'cookie_name' => $_ENV['SESSION_COOKIE_NAME'] ?? 'vorbereitung_session',

    // Session save path
    'save_path' => dirname(__DIR__) . '/' . ($_ENV['SESSION_SAVE_PATH'] ?? 'storage/sessions'),

    // Cookie security settings
    'cookie_httponly' => true,
    'cookie_secure' => false, // Set to true in production with HTTPS
    'cookie_samesite' => 'Strict',

    // Session name for PHP session
    'session_name' => 'VORB_SESS',

    // Use strict session mode
    'use_strict_mode' => true,

    // Regenerate session ID
    'regenerate_on_login' => true,
];
