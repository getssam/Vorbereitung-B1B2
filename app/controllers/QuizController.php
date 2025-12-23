<?php
/**
 * Quiz Controller
 *
 * Handles quiz dashboards and result APIs.
 */

require_once __DIR__ . '/../models/QuizResult.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';
require_once __DIR__ . '/../middleware/MaintenanceMiddleware.php';

class QuizController
{
    private $quizResultModel;
    private $authMiddleware;
    private $maintenanceMiddleware;

    public function __construct()
    {
        $this->quizResultModel = new QuizResult();
        $this->authMiddleware = new AuthMiddleware();
        $this->maintenanceMiddleware = new MaintenanceMiddleware();
    }

    public function showB1Dashboard()
    {
        $this->authMiddleware->handle();
        $this->maintenanceMiddleware->handle();

        $user = currentUser();
        if (!$user || !isset($user['id'])) {
            redirect(url('login'));
            return;
        }
        
        // Refresh user data from database to get latest access levels
        require_once __DIR__ . '/../models/User.php';
        $userModel = new User();
        $dbUser = $userModel->findById($user['id']);
        
        if (!$dbUser) {
            view('errors.403', ['message' => 'User not found in database'], 'main');
            return;
        }
        
        // Ensure we have the access_b1 field
        if (!isset($dbUser['access_b1'])) {
            view('errors.403', ['message' => 'Access information not available'], 'main');
            return;
        }
        
        // Check access_b1 explicitly - convert to int for reliable comparison
        // MySQL TINYINT(1) can return as string "1"/"0" or int 1/0 depending on PDO settings
        $accessB1 = (int)$dbUser['access_b1'];
        
        if ($accessB1 !== 1) {
            view('errors.403', [
                'message' => 'B1 access required. Please contact an administrator to grant you access.',
                'debug' => [
                    'user_id' => $user['id'],
                    'access_b1_raw' => $dbUser['access_b1'],
                    'access_b1_type' => gettype($dbUser['access_b1']),
                    'access_b1_int' => $accessB1
                ]
            ], 'main');
            return;
        }
        
        // Update session with latest access data
        $_SESSION['user']['access_b1'] = (bool)$dbUser['access_b1'];
        $_SESSION['user']['access_b2'] = (bool)$dbUser['access_b2'];
        $user = currentUser();

        require_once __DIR__ . '/../helpers/quiz_categories.php';
        
        $allQuizzes = $this->getQuizFilesWithNames();
        $organizedQuizzes = getB1QuizzesByCategory($allQuizzes);
        
        view('b1.dashboard', [
            'user' => $user,
            'quizzes' => $organizedQuizzes,
        ], 'main');
    }

    public function showB2Dashboard()
    {
        $this->authMiddleware->handle();
        $this->maintenanceMiddleware->handle();

        $user = currentUser();
        if (!$user) {
            redirect(url('login'));
            return;
        }
        
        // Refresh user data from database to get latest access levels
        require_once __DIR__ . '/../models/User.php';
        $userModel = new User();
        $dbUser = $userModel->findById($user['id']);
        
        if (!$dbUser) {
            view('errors.403', ['message' => 'User not found'], 'main');
            return;
        }
        
        // Check access_b2 explicitly (can be 0 or 1 from database, string or int)
        $hasAccess = (int)$dbUser['access_b2'] === 1;
        if (!$hasAccess) {
            // Debug info (remove in production)
            error_log("B2 Access Check Failed - User ID: {$user['id']}, access_b2 value: " . var_export($dbUser['access_b2'], true) . ", type: " . gettype($dbUser['access_b2']));
            view('errors.403', ['message' => 'B2 access required. Please contact an administrator to grant you access.'], 'main');
            return;
        }
        
        // Update session with latest access data
        $_SESSION['user']['access_b1'] = (bool)$dbUser['access_b1'];
        $_SESSION['user']['access_b2'] = (bool)$dbUser['access_b2'];
        $user = currentUser();

        require_once __DIR__ . '/../helpers/quiz_categories.php';
        
        $allQuizzes = $this->getQuizFilesWithNames();
        $organizedQuizzes = getB2QuizzesByCategory($allQuizzes);
        
        view('b2.dashboard', [
            'user' => $user,
            'quizzes' => $organizedQuizzes,
        ], 'main');
    }

    /**
     * API: check access to a level.
     */
    public function checkAccess()
    {
        $this->authMiddleware->handle();

        $level = strtoupper($_GET['level'] ?? ($_GET['quiz_level'] ?? ''));
        $user = currentUser();

        // Refresh user data from database for API calls too
        require_once __DIR__ . '/../models/User.php';
        $userModel = new User();
        $dbUser = $userModel->findById($user['id']);
        
        $allowed = false;
        if ($level === 'B1') {
            $allowed = $dbUser && (int)$dbUser['access_b1'] === 1;
        } elseif ($level === 'B2') {
            $allowed = $dbUser && (int)$dbUser['access_b2'] === 1;
        }

        if (!$allowed) {
            $this->jsonResponse(['success' => false, 'message' => 'Access denied'], 403);
            return;
        }

        $this->jsonResponse(['success' => true, 'message' => 'Access granted']);
    }

    /**
     * API: save quiz result.
     */
    public function saveResult()
    {
        $this->authMiddleware->handle();

        $input = json_decode(file_get_contents('php://input'), true);
        $quizId = $input['quiz_id'] ?? null;
        $quizLevel = strtoupper($input['quiz_level'] ?? '');
        $score = (int)($input['score'] ?? 0);
        $total = (int)($input['total_questions'] ?? 0);
        $answers = $input['answers'] ?? null;

        if (!$quizId || !in_array($quizLevel, ['B1', 'B2'], true)) {
            return $this->jsonResponse(['success' => false, 'message' => 'Invalid payload'], 400);
        }

        $id = $this->quizResultModel->create([
            'user_id' => currentUser()['id'],
            'quiz_id' => $quizId,
            'quiz_level' => $quizLevel,
            'score' => $score,
            'total_questions' => $total,
            'answers' => $answers,
        ]);

        $this->jsonResponse(['success' => true, 'id' => $id]);
    }

    /**
     * Helper: list quiz files in /quiz.
     */
    private function getQuizFiles()
    {
        $base = dirname(__DIR__, 2) . '/quiz';
        $files = glob($base . '/*.html') ?: [];

        return array_map(function ($file) {
            return basename($file);
        }, $files);
    }
    
    /**
     * Get quiz files with their display names
     * 
     * @return array Array of ['file' => filename, 'name' => display_name]
     */
    private function getQuizFilesWithNames()
    {
        require_once __DIR__ . '/../helpers/quiz_functions.php';
        
        $files = $this->getQuizFiles();
        $result = [];
        
        foreach ($files as $file) {
            $result[] = [
                'file' => $file,
                'name' => getQuizDisplayName($file)
            ];
        }
        
        return $result;
    }

    private function jsonResponse($data, $code = 200)
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
