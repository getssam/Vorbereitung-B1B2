<?php
/**
 * Admin Middleware
 *
 * Restricts routes to admin users only.
 */

class AdminMiddleware
{
    /**
     * Ensure current user is an admin.
     */
    public function handle()
    {
        if (isAdmin()) {
            return true;
        }

        if (isAjax()) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'message' => 'Admin access required',
            ]);
            exit;
        }

        view('errors.403', ['message' => 'Admin access required'], 'main');
        exit;
    }
}
