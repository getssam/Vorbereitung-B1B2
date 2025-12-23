<?php
/**
 * Session Model
 *
 * Handles session management and device tracking
 */

class Session
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Create new session
     *
     * @param int $userId User ID
     * @param string $deviceFingerprint Device fingerprint
     * @return string Session token
     */
    public function create($userId, $deviceFingerprint)
    {
        $token = bin2hex(random_bytes(32));
        $ip = getClientIp();
        $userAgent = getUserAgent();

        $sql = "INSERT INTO sessions (user_id, session_token, device_fingerprint, ip_address, user_agent)
                VALUES (?, ?, ?, ?, ?)";

        $this->db->execute($sql, [$userId, $token, $deviceFingerprint, $ip, $userAgent]);

        return $token;
    }

    /**
     * Find session by token
     *
     * @param string $token Session token
     * @return array|false
     */
    public function findByToken($token)
    {
        $sql = "SELECT * FROM sessions WHERE session_token = ? LIMIT 1";
        return $this->db->fetch($sql, [$token]);
    }

    /**
     * Get user sessions
     *
     * @param int $userId User ID
     * @return array
     */
    public function getUserSessions($userId)
    {
        $sql = "SELECT * FROM sessions WHERE user_id = ? ORDER BY last_activity DESC";
        return $this->db->fetchAll($sql, [$userId]);
    }

    /**
     * Update last activity timestamp
     *
     * @param string $token Session token
     * @return bool
     */
    public function updateActivity($token)
    {
        $sql = "UPDATE sessions SET last_activity = CURRENT_TIMESTAMP WHERE session_token = ?";
        return $this->db->execute($sql, [$token]);
    }

    /**
     * Destroy session
     *
     * @param string $token Session token
     * @return bool
     */
    public function destroy($token)
    {
        $sql = "DELETE FROM sessions WHERE session_token = ?";
        return $this->db->execute($sql, [$token]);
    }

    /**
     * Destroy all user sessions
     *
     * @param int $userId User ID
     * @return bool
     */
    public function destroyAll($userId)
    {
        $sql = "DELETE FROM sessions WHERE user_id = ?";
        return $this->db->execute($sql, [$userId]);
    }

    /**
     * Count active user devices
     *
     * @param int $userId User ID
     * @return int
     */
    public function countUserDevices($userId)
    {
        $sql = "SELECT COUNT(DISTINCT device_fingerprint) as count
                FROM sessions
                WHERE user_id = ?";

        $result = $this->db->fetch($sql, [$userId]);
        return (int)($result['count'] ?? 0);
    }

    /**
     * Clean expired sessions
     *
     * @param int $minutes Session lifetime in minutes
     * @return bool
     */
    public function cleanExpired($minutes = 15)
    {
        $sql = "DELETE FROM sessions
                WHERE last_activity < DATE_SUB(NOW(), INTERVAL ? MINUTE)";

        return $this->db->execute($sql, [$minutes]);
    }

    /**
     * Check if device limit exceeded
     *
     * @param int $userId User ID
     * @param int $limit Device limit
     * @return bool True if limit exceeded
     */
    public function isDeviceLimitExceeded($userId, $limit)
    {
        $deviceCount = $this->countUserDevices($userId);
        return $deviceCount >= $limit;
    }

    /**
     * Check if device fingerprint exists for user
     *
     * @param int $userId User ID
     * @param string $deviceFingerprint Device fingerprint
     * @return bool
     */
    public function deviceExists($userId, $deviceFingerprint)
    {
        $sql = "SELECT COUNT(*) as count
                FROM sessions
                WHERE user_id = ? AND device_fingerprint = ?";

        $result = $this->db->fetch($sql, [$userId, $deviceFingerprint]);
        return (int)($result['count'] ?? 0) > 0;
    }

    /**
     * Get session info with user data
     *
     * @param string $token Session token
     * @return array|false
     */
    public function getSessionWithUser($token)
    {
        $sql = "SELECT s.*, u.id as user_id, u.name, u.surname, u.email, u.role,
                       u.is_active, u.access_b1, u.access_b2, u.device_limit
                FROM sessions s
                INNER JOIN users u ON s.user_id = u.id
                WHERE s.session_token = ?
                LIMIT 1";

        return $this->db->fetch($sql, [$token]);
    }

    /**
     * Clean old sessions for a specific user
     *
     * @param int $userId User ID
     * @param int $keepCount Number of recent sessions to keep
     * @return bool
     */
    public function cleanOldUserSessions($userId, $keepCount = 5)
    {
        $sql = "DELETE FROM sessions
                WHERE user_id = ?
                AND id NOT IN (
                    SELECT id FROM (
                        SELECT id FROM sessions
                        WHERE user_id = ?
                        ORDER BY last_activity DESC
                        LIMIT ?
                    ) as keep_sessions
                )";

        return $this->db->execute($sql, [$userId, $userId, $keepCount]);
    }
}
