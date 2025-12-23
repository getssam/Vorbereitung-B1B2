<?php
/**
 * Maintenance Middleware
 *
 * Redirects users to maintenance page when mode is enabled.
 */

require_once __DIR__ . '/../models/Setting.php';

class MaintenanceMiddleware
{
    private $settingModel;

    public function __construct()
    {
        $this->settingModel = new Setting();
    }

    /**
     * Redirect to maintenance page if enabled (except admins).
     */
    public function handle()
    {
        if ($this->settingModel->isMaintenanceMode() && !isAdmin()) {
            redirect(url('maintenance'));
        }
    }
}
