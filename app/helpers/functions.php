<?php
/**
 * Helper Functions
 *
 * General utility functions used throughout the application
 */

/**
 * Load environment variables from .env file
 */
function loadEnv($path = null)
{
    $envFile = $path ?: __DIR__ . '/../../.env';

    if (!file_exists($envFile)) {
        return;
    }

    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue; // Skip comments
        }

        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);

        if (!array_key_exists($name, $_ENV)) {
            $_ENV[$name] = $value;
        }
    }
}

/**
 * Redirect to a URL
 *
 * @param string $url URL to redirect to
 * @param int $code HTTP status code
 */
function redirect($url, $code = 302)
{
    header("Location: $url", true, $code);
    exit;
}

/**
 * Get base URL
 *
 * @return string
 */
function baseUrl()
{
    $appConfig = require __DIR__ . '/../../config/app.php';
    return rtrim($appConfig['url'], '/');
}

/**
 * Generate URL
 *
 * @param string $path Path to append
 * @return string
 */
function url($path = '')
{
    return baseUrl() . '/' . ltrim($path, '/');
}

/**
 * Render a view
 *
 * @param string $view View file name (without .php)
 * @param array $data Data to pass to view
 * @param string $layout Layout file (optional)
 */
function view($view, $data = [], $layout = null)
{
    extract($data);

    $appConfig = require __DIR__ . '/../../config/app.php';
    $viewFile = $appConfig['views_path'] . '/' . str_replace('.', '/', $view) . '.php';

    if (!file_exists($viewFile)) {
        die("View not found: {$view}");
    }

    ob_start();
    require $viewFile;
    $content = ob_get_clean();

    if ($layout) {
        $layoutFile = $appConfig['views_path'] . '/layouts/' . $layout . '.php';
        if (file_exists($layoutFile)) {
            require $layoutFile;
        } else {
            echo $content;
        }
    } else {
        echo $content;
    }
}

/**
 * Get session value
 *
 * @param string $key Session key
 * @param mixed $default Default value if not set
 * @return mixed
 */
function session($key = null, $default = null)
{
    if ($key === null) {
        return $_SESSION;
    }

    return $_SESSION[$key] ?? $default;
}

/**
 * Set session value
 *
 * @param string $key Session key
 * @param mixed $value Value to set
 */
function setSession($key, $value)
{
    $_SESSION[$key] = $value;
}

/**
 * Check if user is authenticated
 *
 * @return bool
 */
function isAuth()
{
    return isset($_SESSION['user_id']) && isset($_SESSION['user']);
}

/**
 * Check if user is admin
 *
 * @return bool
 */
function isAdmin()
{
    return isAuth() && ($_SESSION['user']['role'] ?? '') === 'admin';
}

/**
 * Get current user
 *
 * @return array|null
 */
function currentUser()
{
    return $_SESSION['user'] ?? null;
}

/**
 * Escape HTML output
 *
 * @param string $string String to escape
 * @return string
 */
function e($string)
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Dump and die (for debugging)
 *
 * @param mixed $data Data to dump
 */
function dd($data)
{
    echo '<pre>';
    var_dump($data);
    echo '</pre>';
    die();
}

/**
 * Log message to file
 *
 * @param string $message Message to log
 * @param string $level Log level (debug, info, error)
 */
function logMessage($message, $level = 'info')
{
    $appConfig = require __DIR__ . '/../../config/app.php';
    $logFile = $appConfig['log_path'];
    $logDir = dirname($logFile);

    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }

    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;
    error_log($logMessage, 3, $logFile);
}

/**
 * Check if request is AJAX
 *
 * @return bool
 */
function isAjax()
{
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Get request method
 *
 * @return string
 */
function requestMethod()
{
    return $_SERVER['REQUEST_METHOD'] ?? 'GET';
}

/**
 * Check if request method is POST
 *
 * @return bool
 */
function isPost()
{
    return requestMethod() === 'POST';
}

/**
 * Check if request method is GET
 *
 * @return bool
 */
function isGet()
{
    return requestMethod() === 'GET';
}
