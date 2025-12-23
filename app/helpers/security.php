<?php
/**
 * Security Helper Functions
 *
 * CSRF protection, XSS prevention, and other security utilities
 */

/**
 * Generate CSRF token
 *
 * @return string
 */
function generateCsrfToken()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Get CSRF token
 *
 * @return string
 */
function csrfToken()
{
    return generateCsrfToken();
}

/**
 * Validate CSRF token
 *
 * @param string $token Token to validate
 * @return bool
 */
function validateCsrfToken($token)
{
    if (empty($_SESSION['csrf_token'])) {
        return false;
    }

    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Generate CSRF field for forms
 *
 * @return string HTML input field
 */
function csrfField()
{
    $token = csrfToken();
    $appConfig = require __DIR__ . '/../../config/app.php';
    $name = $appConfig['csrf_token_name'];
    return '<input type="hidden" name="' . $name . '" value="' . $token . '">';
}

/**
 * Verify CSRF token from request
 *
 * @return bool
 */
function verifyCsrf()
{
    $appConfig = require __DIR__ . '/../../config/app.php';
    $tokenName = $appConfig['csrf_token_name'];
    $token = $_POST[$tokenName] ?? $_GET[$tokenName] ?? '';

    if (!validateCsrfToken($token)) {
        http_response_code(403);
        die('CSRF token validation failed');
    }

    return true;
}

/**
 * Sanitize input (XSS prevention)
 *
 * @param mixed $input Input to sanitize
 * @return mixed Sanitized input
 */
function sanitizeInput($input)
{
    if (is_array($input)) {
        return array_map('sanitizeInput', $input);
    }

    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Clean string (remove HTML tags)
 *
 * @param string $string String to clean
 * @return string
 */
function cleanString($string)
{
    return strip_tags(trim($string));
}

/**
 * Hash password
 *
 * @param string $password Plain text password
 * @return string Hashed password
 */
function hashPassword($password)
{
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
}

/**
 * Verify password
 *
 * @param string $password Plain text password
 * @param string $hash Hashed password
 * @return bool
 */
function verifyPassword($password, $hash)
{
    return password_verify($password, $hash);
}

/**
 * Generate random token
 *
 * @param int $length Token length
 * @return string
 */
function generateToken($length = 32)
{
    return bin2hex(random_bytes($length));
}

/**
 * Generate device fingerprint
 *
 * @return string
 */
function generateDeviceFingerprint()
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $fingerprint = $ip . '|' . $userAgent;
    return hash('sha256', $fingerprint);
}

/**
 * Get client IP address
 *
 * @return string
 */
function getClientIp()
{
    $ipKeys = [
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_X_CLUSTER_CLIENT_IP',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED',
        'REMOTE_ADDR'
    ];

    foreach ($ipKeys as $key) {
        if (array_key_exists($key, $_SERVER)) {
            $ip = $_SERVER[$key];
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }

    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/**
 * Get user agent
 *
 * @return string
 */
function getUserAgent()
{
    return $_SERVER['HTTP_USER_AGENT'] ?? '';
}

/**
 * Prevent directory traversal
 *
 * @param string $path Path to sanitize
 * @return string
 */
function sanitizePath($path)
{
    $path = str_replace(['../', '..\\'], '', $path);
    return preg_replace('/[^a-zA-Z0-9_\-\/.]/', '', $path);
}

/**
 * Rate limiting check
 *
 * @param string $key Rate limit key
 * @param int $maxAttempts Maximum attempts
 * @param int $decay Decay time in seconds
 * @return bool True if rate limit exceeded
 */
function isRateLimited($key, $maxAttempts = 5, $decay = 60)
{
    $cacheKey = 'rate_limit_' . md5($key);

    if (!isset($_SESSION[$cacheKey])) {
        $_SESSION[$cacheKey] = [
            'attempts' => 0,
            'reset_at' => time() + $decay
        ];
    }

    $data = $_SESSION[$cacheKey];

    // Reset if decay time passed
    if (time() > $data['reset_at']) {
        $_SESSION[$cacheKey] = [
            'attempts' => 1,
            'reset_at' => time() + $decay
        ];
        return false;
    }

    // Increment attempts
    $_SESSION[$cacheKey]['attempts']++;

    return $data['attempts'] >= $maxAttempts;
}

/**
 * Reset rate limit
 *
 * @param string $key Rate limit key
 */
function resetRateLimit($key)
{
    $cacheKey = 'rate_limit_' . md5($key);
    unset($_SESSION[$cacheKey]);
}
