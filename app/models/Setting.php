<?php
/**
 * Setting Model
 *
 * Manages application settings (maintenance mode, logout timer, etc.)
 */

class Setting
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get setting value by key
     *
     * @param string $key Setting key
     * @param mixed $default Default value if not found
     * @return mixed
     */
    public function get($key, $default = null)
    {
        $sql = "SELECT value FROM settings WHERE `key` = ? LIMIT 1";
        $result = $this->db->fetch($sql, [$key]);

        return $result ? $result['value'] : $default;
    }

    /**
     * Set setting value
     *
     * @param string $key Setting key
     * @param mixed $value Setting value
     * @return bool
     */
    public function set($key, $value)
    {
        $sql = "INSERT INTO settings (`key`, value) VALUES (?, ?)
                ON DUPLICATE KEY UPDATE value = ?, updated_at = CURRENT_TIMESTAMP";

        return $this->db->execute($sql, [$key, $value, $value]);
    }

    /**
     * Get all settings as array
     *
     * @return array
     */
    public function getAll()
    {
        $sql = "SELECT `key`, value FROM settings";
        $results = $this->db->fetchAll($sql);

        $settings = [];
        foreach ($results as $row) {
            $settings[$row['key']] = $row['value'];
        }

        return $settings;
    }

    /**
     * Check if maintenance mode is active
     *
     * @return bool
     */
    public function isMaintenanceMode()
    {
        $value = $this->get('maintenance_mode', '0');
        return $value === '1' || $value === 1 || $value === true;
    }

    /**
     * Toggle maintenance mode
     *
     * @param bool $enabled
     * @return bool
     */
    public function toggleMaintenance($enabled)
    {
        return $this->set('maintenance_mode', $enabled ? '1' : '0');
    }

    /**
     * Get logout timer in minutes
     *
     * @return int
     */
    public function getLogoutTimer()
    {
        return (int)$this->get('logout_timer', 15);
    }

    /**
     * Set logout timer
     *
     * @param int $minutes
     * @return bool
     */
    public function setLogoutTimer($minutes)
    {
        return $this->set('logout_timer', (string)$minutes);
    }
}
