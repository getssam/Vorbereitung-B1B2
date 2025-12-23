<?php
/**
 * Main Application Entry Point
 *
 * Router that handles all incoming requests
 */

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load environment variables
require_once __DIR__ . '/app/helpers/functions.php';
loadEnv();

// Load helpers
require_once __DIR__ . '/app/helpers/security.php';
require_once __DIR__ . '/app/helpers/validation.php';

// Start session
$sessionConfig = require __DIR__ . '/config/session.php';
$sessionPath = $sessionConfig['save_path'];

// If path is relative, make it absolute from project root
if (!preg_match('#^([A-Za-z]:[\\\\/]|/)#', $sessionPath)) {
    $sessionPath = __DIR__ . '/' . ltrim($sessionPath, '/\\');
}

if (!is_dir($sessionPath)) {
    mkdir($sessionPath, 0755, true);
}

session_save_path($sessionPath);
session_name($sessionConfig['session_name']);
session_start();

// Generate CSRF token for this session
generateCsrfToken();

// Get request URL
$url = $_GET['url'] ?? '';
$url = rtrim($url, '/');
$url = filter_var($url, FILTER_SANITIZE_URL);

// Parse URL into segments
$segments = explode('/', $url);

// Define routes
$routes = [
    '' => ['controller' => 'UserController', 'method' => 'home'],
    'home' => ['controller' => 'UserController', 'method' => 'home'],

    // Auth routes
    'login' => ['controller' => 'AuthController', 'method' => 'showLogin'],
    'auth/login' => ['controller' => 'AuthController', 'method' => 'login'],
    'register' => ['controller' => 'AuthController', 'method' => 'showRegister'],
    'auth/register' => ['controller' => 'AuthController', 'method' => 'register'],
    'logout' => ['controller' => 'AuthController', 'method' => 'logout'],
    'auth/logout' => ['controller' => 'AuthController', 'method' => 'logout'],

    // Admin auth routes
    'admin/login' => ['controller' => 'AdminController', 'method' => 'showAdminLogin'],
    'admin/auth' => ['controller' => 'AdminController', 'method' => 'adminLogin'],

    // Admin dashboard
    'admin' => ['controller' => 'AdminController', 'method' => 'dashboard'],
    'admin/dashboard' => ['controller' => 'AdminController', 'method' => 'dashboard'],

    // Quiz dashboards
    'b1' => ['controller' => 'QuizController', 'method' => 'showB1Dashboard'],
    'b2' => ['controller' => 'QuizController', 'method' => 'showB2Dashboard'],

    // User profile
    'profile' => ['controller' => 'UserController', 'method' => 'showProfile'],

    // Maintenance
    'maintenance' => ['controller' => 'MaintenanceController', 'method' => 'show'],

    // API routes
    'api/auth/session' => ['controller' => 'AuthController', 'method' => 'checkSession'],
    'api/auth/ping' => ['controller' => 'AuthController', 'method' => 'ping'],
    'api/user/profile' => ['controller' => 'UserController', 'method' => 'updateProfile'],
    'api/user/password' => ['controller' => 'UserController', 'method' => 'updatePassword'],
    'api/admin/users' => ['controller' => 'AdminController', 'method' => requestMethod() === 'POST' ? 'createUser' : 'getUsers'],
    'api/quiz/access' => ['controller' => 'QuizController', 'method' => 'checkAccess'],
    'api/quiz/results' => ['controller' => 'QuizController', 'method' => 'saveResult'],
    'api/maintenance/status' => ['controller' => 'MaintenanceController', 'method' => 'check'],
];

// Handle dynamic routes (e.g., /api/admin/users/123/activate)
$route = null;

if (isset($routes[$url])) {
    $route = $routes[$url];
} else {
    // Check for dynamic admin API routes
    if (preg_match('#^api/admin/users/(\d+)/(activate|deactivate|access|device-limit|update)$#', $url, $matches)) {
        $route = ['controller' => 'AdminController', 'method' => 'updateUser'];
        $_GET['user_id'] = $matches[1];
        $_GET['action'] = $matches[2];
    } elseif (preg_match('#^api/admin/users/(\d+)$#', $url, $matches)) {
        $route = ['controller' => 'AdminController', 'method' => requestMethod() === 'DELETE' ? 'deleteUser' : 'getUser'];
        $_GET['user_id'] = $matches[1];
    } elseif (preg_match('#^api/quiz/access/(B1|B2)$#i', $url, $matches)) {
        $route = ['controller' => 'QuizController', 'method' => 'checkAccess'];
        $_GET['level'] = strtoupper($matches[1]);
    }
}

// Load database and models
require_once __DIR__ . '/app/models/Database.php';

// Execute route
if ($route) {
    $controllerName = $route['controller'];
    $methodName = $route['method'];
    $controllerFile = __DIR__ . '/app/controllers/' . $controllerName . '.php';

    if (file_exists($controllerFile)) {
        require_once $controllerFile;

        if (class_exists($controllerName)) {
            $controller = new $controllerName();

            if (method_exists($controller, $methodName)) {
                $controller->$methodName();
            } else {
                http_response_code(404);
                view('errors.404', ['url' => $url, 'message' => 'Method not found'], 'main');
            }
        } else {
            http_response_code(500);
            echo "Controller class not found: {$controllerName}";
        }
    } else {
        http_response_code(500);
        echo "Controller file not found: {$controllerFile}";
    }
} else {
    // 404 - Route not found
    http_response_code(404);
    if (file_exists(__DIR__ . '/app/views/errors/404.php')) {
        view('errors.404', ['url' => $url], 'main');
    } else {
        echo "404 - Page not found";
    }
}
