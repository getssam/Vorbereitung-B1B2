<?php
/**
 * Admin Controller
 *
 * Handles admin authentication and management endpoints.
 */

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Session.php';
require_once __DIR__ . '/../models/Setting.php';
require_once __DIR__ . '/../models/QuizResult.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../middleware/AdminMiddleware.php';

class AdminController
{
    private $userModel;
    private $sessionModel;
    private $settingModel;
    private $quizResultModel;
    private $authMiddleware;
    private $adminMiddleware;

    public function __construct()
    {
        $this->userModel = new User();
        $this->sessionModel = new Session();
        $this->settingModel = new Setting();
        $this->quizResultModel = new QuizResult();
        $this->authMiddleware = new AuthMiddleware();
        $this->adminMiddleware = new AdminMiddleware();
    }

    /**
     * Show admin login page.
     */
    public function showAdminLogin()
    {
        if (isAdmin()) {
            redirect(url('admin/dashboard'));
            return;
        }

        view('auth.admin_login', [], 'auth');
    }

    /**
     * Process admin login.
     */
    public function adminLogin()
    {
        if (!isPost()) {
            redirect(url('admin/login'));
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $email = $input['email'] ?? '';
        $password = $input['password'] ?? '';

        if (empty($email) || empty($password)) {
            return $this->jsonResponse(['success' => false, 'message' => 'Email and password are required'], 400);
        }

        $user = $this->userModel->verifyCredentials($email, $password);

        if (!$user || $user['role'] !== 'admin') {
            return $this->jsonResponse(['success' => false, 'message' => 'Invalid admin credentials'], 401);
        }

        // Create session
        $deviceFingerprint = generateDeviceFingerprint();
        $token = $this->sessionModel->create($user['id'], $deviceFingerprint);

        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['session_token'] = $token;
        $_SESSION['user'] = [
            'id' => $user['id'],
            'name' => $user['name'],
            'surname' => $user['surname'],
            'email' => $user['email'],
            'role' => $user['role'],
            'access_b1' => (bool)$user['access_b1'],
            'access_b2' => (bool)$user['access_b2'],
        ];

        return $this->jsonResponse([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => $_SESSION['user'],
                'redirect' => url('admin/dashboard'),
            ],
        ]);
    }

    /**
     * Admin dashboard.
     */
    public function dashboard()
    {
        $this->authMiddleware->handle();
        $this->adminMiddleware->handle();

        $users = $this->userModel->getAll(true);
        $pending = $this->userModel->getPendingUsers();

        view('admin.dashboard', [
            'user' => currentUser(),
            'users' => $users,
            'pendingUsers' => $pending,
            'maintenance' => $this->settingModel->isMaintenanceMode(),
        ], 'main');
    }

    /**
     * Get all users (API).
     */
    public function getUsers()
    {
        $this->authMiddleware->handle();
        $this->adminMiddleware->handle();

        $includePending = ($_GET['pending'] ?? '1') === '1';
        $users = $this->userModel->getAll($includePending);

        $this->jsonResponse(['success' => true, 'data' => $users]);
    }

    /**
     * Get single user (API).
     */
    public function getUser()
    {
        $this->authMiddleware->handle();
        $this->adminMiddleware->handle();

        $userId = (int)($_GET['user_id'] ?? 0);
        $user = $this->userModel->findById($userId);

        if (!$user) {
            $this->jsonResponse(['success' => false, 'message' => 'User not found'], 404);
            return;
        }

        $this->jsonResponse(['success' => true, 'data' => $user]);
    }

    /**
     * Update user actions (activate/deactivate/access/device-limit).
     */
    public function updateUser()
    {
        $this->authMiddleware->handle();
        $this->adminMiddleware->handle();

        $userId = (int)($_GET['user_id'] ?? 0);
        $action = $_GET['action'] ?? '';
        $input = json_decode(file_get_contents('php://input'), true) ?: [];

        $user = $this->userModel->findById($userId);
        if (!$user) {
            return $this->jsonResponse(['success' => false, 'message' => 'User not found'], 404);
        }

        switch ($action) {
            case 'activate':
                $this->userModel->activate($userId);
                return $this->jsonResponse(['success' => true, 'message' => 'User activated']);
            case 'deactivate':
                $this->userModel->deactivate($userId);
                return $this->jsonResponse(['success' => true, 'message' => 'User deactivated']);
            case 'access':
                $b1 = isset($input['access_b1']) ? toBool($input['access_b1']) : (bool)$user['access_b1'];
                $b2 = isset($input['access_b2']) ? toBool($input['access_b2']) : (bool)$user['access_b2'];
                $this->userModel->setAccess($userId, $b1, $b2);
                return $this->jsonResponse(['success' => true, 'message' => 'Access updated']);
            case 'device-limit':
                $limit = isset($input['device_limit']) ? (int)$input['device_limit'] : $user['device_limit'];
                if ($limit < 1) {
                    $limit = 1;
                }
                $this->userModel->setDeviceLimit($userId, $limit);
                return $this->jsonResponse(['success' => true, 'message' => 'Device limit updated']);
            case 'update':
                // Update full user details
                $updateData = [];
                
                if (isset($input['name'])) {
                    $updateData['name'] = cleanString($input['name']);
                }
                if (isset($input['surname'])) {
                    $updateData['surname'] = cleanString($input['surname']);
                }
                if (isset($input['email'])) {
                    $email = trim($input['email']);
                    if (!isValidEmail($email)) {
                        return $this->jsonResponse(['success' => false, 'message' => 'Invalid email address'], 400);
                    }
                    // Check if email exists for another user
                    $existingUser = $this->userModel->findByEmail($email);
                    if ($existingUser && $existingUser['id'] != $userId) {
                        return $this->jsonResponse(['success' => false, 'message' => 'Email already in use'], 400);
                    }
                    $updateData['email'] = $email;
                }
                if (isset($input['phone'])) {
                    $updateData['phone'] = cleanString($input['phone']) ?: null;
                }
                if (isset($input['role'])) {
                    $updateData['role'] = in_array($input['role'], ['user', 'admin']) ? $input['role'] : $user['role'];
                }
                if (isset($input['is_active'])) {
                    $updateData['is_active'] = toBool($input['is_active']) ? 1 : 0;
                }
                if (isset($input['access_b1'])) {
                    $updateData['access_b1'] = toBool($input['access_b1']) ? 1 : 0;
                }
                if (isset($input['access_b2'])) {
                    $updateData['access_b2'] = toBool($input['access_b2']) ? 1 : 0;
                }
                if (isset($input['device_limit'])) {
                    $updateData['device_limit'] = max(1, (int)$input['device_limit']);
                }
                
                if (empty($updateData)) {
                    return $this->jsonResponse(['success' => false, 'message' => 'No data to update'], 400);
                }
                
                $this->userModel->update($userId, $updateData);
                return $this->jsonResponse(['success' => true, 'message' => 'User updated successfully']);
            default:
                return $this->jsonResponse(['success' => false, 'message' => 'Unsupported action'], 400);
        }
    }

    /**
     * Create new user (API).
     */
    public function createUser()
    {
        $this->authMiddleware->handle();
        $this->adminMiddleware->handle();

        if (!isPost()) {
            return $this->jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        $input = json_decode(file_get_contents('php://input'), true);

        $name = cleanString($input['name'] ?? '');
        $surname = cleanString($input['surname'] ?? '');
        $email = trim($input['email'] ?? '');
        $phone = cleanString($input['phone'] ?? '');
        $password = $input['password'] ?? '';
        $role = $input['role'] ?? 'user';
        $isActive = isset($input['is_active']) ? toBool($input['is_active']) : true;
        $accessB1 = isset($input['access_b1']) ? toBool($input['access_b1']) : false;
        $accessB2 = isset($input['access_b2']) ? toBool($input['access_b2']) : false;
        $deviceLimit = isset($input['device_limit']) ? max(1, (int)$input['device_limit']) : 1;

        // Validate required fields
        $required = ['name', 'surname', 'email', 'password'];
        $validation = validateRequired(compact('name', 'surname', 'email', 'password'), $required);

        if (!$validation['valid']) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'All required fields must be filled',
                'errors' => $validation['errors']
            ], 400);
        }

        // Validate email
        if (!isValidEmail($email)) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Invalid email address'
            ], 400);
        }

        // Check if email exists
        if ($this->userModel->emailExists($email)) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Email already registered'
            ], 400);
        }

        // Validate password
        $passwordValidation = validatePassword($password);
        if (!$passwordValidation['valid']) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Password does not meet requirements',
                'errors' => $passwordValidation['errors']
            ], 400);
        }

        // Validate role
        if (!in_array($role, ['user', 'admin'])) {
            $role = 'user';
        }

        // Create user
        try {
            $userId = $this->userModel->create([
                'name' => $name,
                'surname' => $surname,
                'email' => $email,
                'password' => hashPassword($password),
                'phone' => $phone ?: null,
                'role' => $role,
                'is_active' => $isActive ? 1 : 0,
                'access_b1' => $accessB1 ? 1 : 0,
                'access_b2' => $accessB2 ? 1 : 0,
                'device_limit' => $deviceLimit
            ]);

            return $this->jsonResponse([
                'success' => true,
                'message' => 'User created successfully',
                'data' => ['user_id' => $userId]
            ]);
        } catch (Exception $e) {
            logMessage('User creation failed: ' . $e->getMessage(), 'error');

            return $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to create user. Please try again.'
            ], 500);
        }
    }

    /**
     * Delete user (API).
     */
    public function deleteUser()
    {
        $this->authMiddleware->handle();
        $this->adminMiddleware->handle();

        $userId = (int)($_GET['user_id'] ?? 0);

        if (!$this->userModel->findById($userId)) {
            return $this->jsonResponse(['success' => false, 'message' => 'User not found'], 404);
        }

        $this->userModel->delete($userId);
        $this->sessionModel->destroyAll($userId);

        $this->jsonResponse(['success' => true, 'message' => 'User deleted']);
    }

    private function jsonResponse($data, $code = 200)
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
