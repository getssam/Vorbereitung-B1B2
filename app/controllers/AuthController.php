<?php
/**
 * Authentication Controller
 *
 * Handles user authentication (login, register, logout)
 */

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Session.php';

class AuthController
{
    private $userModel;
    private $sessionModel;

    public function __construct()
    {
        $this->userModel = new User();
        $this->sessionModel = new Session();
    }

    /**
     * Show login page
     */
    public function showLogin()
    {
        if (isAuth()) {
            redirect(url('home'));
            return;
        }

        view('auth.login', [], 'auth');
    }

    /**
     * Process login
     */
    public function login()
    {
        if (!isPost()) {
            redirect(url('login'));
            return;
        }

        // Check rate limiting
        $ip = getClientIp();
        if (isRateLimited('login_' . $ip, 5, 300)) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Too many login attempts. Please try again in 5 minutes.'
            ], 429);
            return;
        }

        // Get JSON input
        $input = json_decode(file_get_contents('php://input'), true);

        $email = $input['email'] ?? '';
        $password = $input['password'] ?? '';

        // Validate input
        if (empty($email) || empty($password)) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Email and password are required'
            ], 400);
            return;
        }

        // Verify credentials
        $user = $this->userModel->verifyCredentials($email, $password);

        if (!$user) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Invalid email or password'
            ], 401);
            return;
        }

        // Check if user is active
        if (!$user['is_active']) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Your account is pending admin approval'
            ], 403);
            return;
        }

        // Check device limit
        $deviceFingerprint = generateDeviceFingerprint();
        $deviceExists = $this->sessionModel->deviceExists($user['id'], $deviceFingerprint);

        if (!$deviceExists && $this->sessionModel->isDeviceLimitExceeded($user['id'], $user['device_limit'])) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Device limit exceeded. Please logout from another device.'
            ], 403);
            return;
        }

        // Create session
        $sessionToken = $this->sessionModel->create($user['id'], $deviceFingerprint);

        // Set PHP session
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['session_token'] = $sessionToken;
        $_SESSION['user'] = [
            'id' => $user['id'],
            'name' => $user['name'],
            'surname' => $user['surname'],
            'email' => $user['email'],
            'role' => $user['role'],
            'access_b1' => (bool)$user['access_b1'],
            'access_b2' => (bool)$user['access_b2']
        ];

        // Reset rate limit on successful login
        resetRateLimit('login_' . $ip);

        // Determine redirect URL based on role
        $redirectUrl = $user['role'] === 'admin' ? url('admin') : url('home');

        $this->jsonResponse([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => $_SESSION['user'],
                'redirect' => $redirectUrl
            ]
        ]);
    }

    /**
     * Show registration page
     */
    public function showRegister()
    {
        if (isAuth()) {
            redirect(url('home'));
            return;
        }

        view('auth.register', [], 'auth');
    }

    /**
     * Process registration
     */
    public function register()
    {
        if (!isPost()) {
            redirect(url('register'));
            return;
        }

        // Get JSON input
        $input = json_decode(file_get_contents('php://input'), true);

        $name = cleanString($input['name'] ?? '');
        $surname = cleanString($input['surname'] ?? '');
        $email = trim($input['email'] ?? '');
        $phone = cleanString($input['phone'] ?? '');
        $password = $input['password'] ?? '';

        // Validate required fields
        $required = ['name', 'surname', 'email', 'password'];
        $validation = validateRequired(compact('name', 'surname', 'email', 'password'), $required);

        if (!$validation['valid']) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'All fields are required',
                'errors' => $validation['errors']
            ], 400);
            return;
        }

        // Validate email
        if (!isValidEmail($email)) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Invalid email address'
            ], 400);
            return;
        }

        // Check if email exists
        if ($this->userModel->emailExists($email)) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Email already registered'
            ], 400);
            return;
        }

        // Validate password
        $passwordValidation = validatePassword($password);
        if (!$passwordValidation['valid']) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Password does not meet requirements',
                'errors' => $passwordValidation['errors']
            ], 400);
            return;
        }

        // Create user
        try {
            $userId = $this->userModel->create([
                'name' => $name,
                'surname' => $surname,
                'email' => $email,
                'password' => hashPassword($password),
                'phone' => $phone ?: null,
                'role' => 'user',
                'is_active' => 0, // Pending approval
                'access_b1' => 0,
                'access_b2' => 0,
                'device_limit' => 1
            ]);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Registration successful! Your account is pending admin approval.'
            ]);
        } catch (Exception $e) {
            logMessage('Registration failed: ' . $e->getMessage(), 'error');

            $this->jsonResponse([
                'success' => false,
                'message' => 'Registration failed. Please try again.'
            ], 500);
        }
    }

    /**
     * Logout user
     */
    public function logout()
    {
        if (isset($_SESSION['session_token'])) {
            $this->sessionModel->destroy($_SESSION['session_token']);
        }

        session_destroy();

        if (isAjax()) {
            $this->jsonResponse([
                'success' => true,
                'message' => 'Logged out successfully'
            ]);
        } else {
            redirect(url());
        }
    }

    /**
     * Check current session (API)
     */
    public function checkSession()
    {
        if (!isAuth()) {
            $this->jsonResponse([
                'success' => false,
                'authenticated' => false
            ], 401);
            return;
        }

        $this->jsonResponse([
            'success' => true,
            'authenticated' => true,
            'user' => currentUser()
        ]);
    }

    /**
     * Keep-alive ping
     */
    public function ping()
    {
        if (!isPost()) {
            $this->jsonResponse(['success' => false], 400);
            return;
        }

        if (isAuth() && isset($_SESSION['session_token'])) {
            $this->sessionModel->updateActivity($_SESSION['session_token']);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Activity updated'
            ]);
        } else {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Not authenticated'
            ], 401);
        }
    }

    /**
     * Send JSON response
     *
     * @param array $data Response data
     * @param int $code HTTP status code
     */
    private function jsonResponse($data, $code = 200)
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
