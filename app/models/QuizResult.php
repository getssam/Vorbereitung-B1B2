<?php
/**
 * QuizResult Model
 *
 * Manages quiz results and user progress
 */

class QuizResult
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Save quiz result
     *
     * @param array $data Result data
     * @return int Result ID
     */
    public function create($data)
    {
        $sql = "INSERT INTO quiz_results (user_id, quiz_id, quiz_level, score, total_questions, answers)
                VALUES (?, ?, ?, ?, ?, ?)";

        $answers = isset($data['answers']) ? json_encode($data['answers']) : null;

        $this->db->execute($sql, [
            $data['user_id'],
            $data['quiz_id'],
            $data['quiz_level'],
            $data['score'],
            $data['total_questions'],
            $answers
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Get all results for a user
     *
     * @param int $userId User ID
     * @return array
     */
    public function findByUser($userId)
    {
        $sql = "SELECT * FROM quiz_results
                WHERE user_id = ?
                ORDER BY completed_at DESC";

        return $this->db->fetchAll($sql, [$userId]);
    }

    /**
     * Get results by user and level
     *
     * @param int $userId User ID
     * @param string $level 'B1' or 'B2'
     * @return array
     */
    public function findByUserAndLevel($userId, $level)
    {
        $sql = "SELECT * FROM quiz_results
                WHERE user_id = ? AND quiz_level = ?
                ORDER BY completed_at DESC";

        return $this->db->fetchAll($sql, [$userId, $level]);
    }

    /**
     * Get specific quiz result
     *
     * @param int $userId User ID
     * @param string $quizId Quiz ID
     * @return array|false
     */
    public function findByQuiz($userId, $quizId)
    {
        $sql = "SELECT * FROM quiz_results
                WHERE user_id = ? AND quiz_id = ?
                ORDER BY completed_at DESC
                LIMIT 1";

        return $this->db->fetch($sql, [$userId, $quizId]);
    }

    /**
     * Get user statistics
     *
     * @param int $userId User ID
     * @return array
     */
    public function getStats($userId)
    {
        $sql = "SELECT
                    COUNT(*) as total_quizzes,
                    SUM(score) as total_score,
                    SUM(total_questions) as total_questions,
                    AVG(score / total_questions * 100) as average_percentage,
                    quiz_level
                FROM quiz_results
                WHERE user_id = ?
                GROUP BY quiz_level";

        $results = $this->db->fetchAll($sql, [$userId]);

        $stats = [
            'B1' => ['total_quizzes' => 0, 'average_percentage' => 0],
            'B2' => ['total_quizzes' => 0, 'average_percentage' => 0],
            'overall' => ['total_quizzes' => 0, 'average_percentage' => 0]
        ];

        $totalQuizzes = 0;
        $totalPercentage = 0;

        foreach ($results as $row) {
            $level = $row['quiz_level'];
            $stats[$level] = [
                'total_quizzes' => (int)$row['total_quizzes'],
                'average_percentage' => round((float)$row['average_percentage'], 2)
            ];

            $totalQuizzes += (int)$row['total_quizzes'];
            $totalPercentage += (float)$row['average_percentage'];
        }

        $stats['overall'] = [
            'total_quizzes' => $totalQuizzes,
            'average_percentage' => $totalQuizzes > 0 ? round($totalPercentage / count($results), 2) : 0
        ];

        return $stats;
    }

    /**
     * Get recent results
     *
     * @param int $userId User ID
     * @param int $limit Number of results
     * @return array
     */
    public function getRecent($userId, $limit = 10)
    {
        $sql = "SELECT * FROM quiz_results
                WHERE user_id = ?
                ORDER BY completed_at DESC
                LIMIT ?";

        return $this->db->fetchAll($sql, [$userId, $limit]);
    }

    /**
     * Delete user results
     *
     * @param int $userId User ID
     * @return bool
     */
    public function deleteByUser($userId)
    {
        $sql = "DELETE FROM quiz_results WHERE user_id = ?";
        return $this->db->execute($sql, [$userId]);
    }
}
