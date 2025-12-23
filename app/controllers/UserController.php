<?php
/**
 * User Controller
 *
 * Handles user-facing pages (home/dashboards).
 */

require_once __DIR__ . '/../models/QuizResult.php';
require_once __DIR__ . '/../models/Setting.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../middleware/MaintenanceMiddleware.php';

class UserController
{
    private $quizResultModel;
    private $settingModel;
    private $userModel;
    private $authMiddleware;
    private $maintenanceMiddleware;

    public function __construct()
    {
        $this->quizResultModel = new QuizResult();
        $this->settingModel = new Setting();
        $this->userModel = new User();
        $this->authMiddleware = new AuthMiddleware();
        $this->maintenanceMiddleware = new MaintenanceMiddleware();
    }

    /**
     * Home/dashboard view for authenticated users.
     */
    public function home()
    {
        $this->authMiddleware->handle();
        $this->maintenanceMiddleware->handle();

        $user = currentUser();
        $stats = $this->quizResultModel->getStats($user['id']);
        $recent = $this->quizResultModel->getRecent($user['id'], 5);

        view('home.index', [
            'user' => $user,
            'stats' => $stats,
            'recentResults' => $recent,
        ], 'main');
    }

    /**
     * Show profile page.
     */
    public function showProfile()
    {
        $this->authMiddleware->handle();
        $this->maintenanceMiddleware->handle();

        $user = currentUser();
        // Get fresh user data from database
        $dbUser = $this->userModel->findById($user['id']);

        view('user.profile', [
            'user' => $dbUser ?: $user,
        ], 'main');
    }

    /**
     * Update profile information (API).
     */
    public function updateProfile()
    {
        $this->authMiddleware->handle();

        if (!isPost()) {
            return $this->jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        $user = currentUser();
        if (!$user) {
            return $this->jsonResponse(['success' => false, 'message' => 'Not authenticated'], 401);
        }

        $input = json_decode(file_get_contents('php://input'), true);
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
            if ($existingUser && $existingUser['id'] != $user['id']) {
                return $this->jsonResponse(['success' => false, 'message' => 'Email already in use'], 400);
            }
            $updateData['email'] = $email;
        }
        if (isset($input['phone'])) {
            $updateData['phone'] = cleanString($input['phone']) ?: null;
        }

        if (empty($updateData)) {
            return $this->jsonResponse(['success' => false, 'message' => 'No data to update'], 400);
        }

        $this->userModel->update($user['id'], $updateData);

        // Update session
        $updatedUser = $this->userModel->findById($user['id']);
        $_SESSION['user']['name'] = $updatedUser['name'];
        $_SESSION['user']['surname'] = $updatedUser['surname'];
        $_SESSION['user']['email'] = $updatedUser['email'];
        if (isset($updatedUser['phone'])) {
            $_SESSION['user']['phone'] = $updatedUser['phone'];
        }

        return $this->jsonResponse([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => [
                'user' => $_SESSION['user']
            ]
        ]);
    }

    /**
     * Update password (API).
     */
    public function updatePassword()
    {
        $this->authMiddleware->handle();

        if (!isPost()) {
            return $this->jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        $user = currentUser();
        if (!$user) {
            return $this->jsonResponse(['success' => false, 'message' => 'Not authenticated'], 401);
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $currentPassword = $input['current_password'] ?? '';
        $newPassword = $input['new_password'] ?? '';
        $confirmPassword = $input['confirm_password'] ?? '';

        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            return $this->jsonResponse(['success' => false, 'message' => 'All password fields are required'], 400);
        }

        // Verify current password
        $dbUser = $this->userModel->findById($user['id']);
        if (!password_verify($currentPassword, $dbUser['password'])) {
            return $this->jsonResponse(['success' => false, 'message' => 'Current password is incorrect'], 400);
        }

        // Validate new password
        if (strlen($newPassword) < 6) {
            return $this->jsonResponse(['success' => false, 'message' => 'New password must be at least 6 characters'], 400);
        }

        if ($newPassword !== $confirmPassword) {
            return $this->jsonResponse(['success' => false, 'message' => 'New passwords do not match'], 400);
        }

        // Update password
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 10]);
        $this->userModel->updatePassword($user['id'], $hashedPassword);

        return $this->jsonResponse([
            'success' => true,
            'message' => 'Password updated successfully'
        ]);
    }

    private function jsonResponse($data, $code = 200)
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
