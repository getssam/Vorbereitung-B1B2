<?php
/**
 * Maintenance Controller
 *
 * Handles maintenance status checks and page rendering.
 */

require_once __DIR__ . '/../models/Setting.php';

class MaintenanceController
{
    private $settingModel;

    public function __construct()
    {
        $this->settingModel = new Setting();
    }

    /**
     * Public maintenance page.
     */
    public function show()
    {
        view('maintenance.index', [
            'maintenance' => true,
        ], 'auth');
    }

    /**
     * API status check.
     */
    public function check()
    {
        $active = $this->settingModel->isMaintenanceMode();
        $this->jsonResponse([
            'success' => true,
            'maintenance' => $active,
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
