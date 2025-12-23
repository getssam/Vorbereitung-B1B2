<?php
/**
 * User Model
 *
 * Handles all user-related database operations
 */

class User
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Create a new user
     *
     * @param array $data User data
     * @return int User ID
     */
    public function create($data)
    {
        $sql = "INSERT INTO users (name, surname, email, password, phone, role, is_active, access_b1, access_b2, device_limit)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $this->db->execute($sql, [
            $data['name'],
            $data['surname'],
            $data['email'],
            $data['password'],
            $data['phone'] ?? null,
            $data['role'] ?? 'user',
            $data['is_active'] ?? 0,
            $data['access_b1'] ?? 0,
            $data['access_b2'] ?? 0,
            $data['device_limit'] ?? 1
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Find user by email
     *
     * @param string $email
     * @return array|false
     */
    public function findByEmail($email)
    {
        $sql = "SELECT * FROM users WHERE email = ? LIMIT 1";
        return $this->db->fetch($sql, [$email]);
    }

    /**
     * Find user by ID
     *
     * @param int $id
     * @return array|false
     */
    public function findById($id)
    {
        $sql = "SELECT * FROM users WHERE id = ? LIMIT 1";
        return $this->db->fetch($sql, [$id]);
    }

    /**
     * Get all users
     *
     * @param bool $includePending Include pending users
     * @return array
     */
    public function getAll($includePending = true)
    {
        if ($includePending) {
            $sql = "SELECT id, name, surname, email, phone, role, is_active, access_b1, access_b2, device_limit, created_at
                    FROM users
                    ORDER BY created_at DESC";
        } else {
            $sql = "SELECT id, name, surname, email, phone, role, is_active, access_b1, access_b2, device_limit, created_at
                    FROM users
                    WHERE is_active = 1
                    ORDER BY created_at DESC";
        }

        return $this->db->fetchAll($sql);
    }

    /**
     * Get pending users (awaiting approval)
     *
     * @return array
     */
    public function getPendingUsers()
    {
        $sql = "SELECT id, name, surname, email, phone, created_at
                FROM users
                WHERE is_active = 0 AND role = 'user'
                ORDER BY created_at DESC";

        return $this->db->fetchAll($sql);
    }

    /**
     * Update user
     *
     * @param int $id User ID
     * @param array $data Data to update
     * @return bool
     */
    public function update($id, $data)
    {
        $fields = [];
        $values = [];

        foreach ($data as $key => $value) {
            $fields[] = "$key = ?";
            $values[] = $value;
        }

        $values[] = $id;

        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
        return $this->db->execute($sql, $values);
    }

    /**
     * Delete user
     *
     * @param int $id User ID
     * @return bool
     */
    public function delete($id)
    {
        $sql = "DELETE FROM users WHERE id = ?";
        return $this->db->execute($sql, [$id]);
    }

    /**
     * Activate user account
     *
     * @param int $id User ID
     * @return bool
     */
    public function activate($id)
    {
        return $this->update($id, ['is_active' => 1]);
    }

    /**
     * Deactivate user account
     *
     * @param int $id User ID
     * @return bool
     */
    public function deactivate($id)
    {
        return $this->update($id, ['is_active' => 0]);
    }

    /**
     * Set user access levels
     *
     * @param int $id User ID
     * @param bool $b1 B1 access
     * @param bool $b2 B2 access
     * @return bool
     */
    public function setAccess($id, $b1, $b2)
    {
        return $this->update($id, [
            'access_b1' => $b1 ? 1 : 0,
            'access_b2' => $b2 ? 1 : 0
        ]);
    }

    /**
     * Set device limit
     *
     * @param int $id User ID
     * @param int $limit Device limit
     * @return bool
     */
    public function setDeviceLimit($id, $limit)
    {
        return $this->update($id, ['device_limit' => $limit]);
    }

    /**
     * Verify user credentials
     *
     * @param string $email
     * @param string $password
     * @return array|false User data or false
     */
    public function verifyCredentials($email, $password)
    {
        $user = $this->findByEmail($email);

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }

        return false;
    }

    /**
     * Check if user is admin
     *
     * @param int $id User ID
     * @return bool
     */
    public function isAdmin($id)
    {
        $user = $this->findById($id);
        return $user && $user['role'] === 'admin';
    }

    /**
     * Check if user is active
     *
     * @param int $id User ID
     * @return bool
     */
    public function isActive($id)
    {
        $user = $this->findById($id);
        return $user && $user['is_active'] == 1;
    }

    /**
     * Check if email exists
     *
     * @param string $email
     * @return bool
     */
    public function emailExists($email)
    {
        return $this->findByEmail($email) !== false;
    }

    /**
     * Get user's access levels
     *
     * @param int $id User ID
     * @return array ['b1' => bool, 'b2' => bool]
     */
    public function getAccessLevels($id)
    {
        $user = $this->findById($id);

        return [
            'b1' => $user ? (bool)$user['access_b1'] : false,
            'b2' => $user ? (bool)$user['access_b2'] : false
        ];
    }

    /**
     * Update user password
     *
     * @param int $id User ID
     * @param string $password Hashed password
     * @return bool
     */
    public function updatePassword($id, $password)
    {
        return $this->update($id, ['password' => $password]);
    }
}
