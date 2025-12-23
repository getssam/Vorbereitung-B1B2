<?php
/**
 * Auth Middleware
 *
 * Ensures routes are accessible only to authenticated users.
 */

class AuthMiddleware
{
    /**
     * Require an authenticated user or redirect / return JSON error.
     */
    public function handle()
    {
        if (isAuth()) {
            return true;
        }

        if (isAjax()) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'Authentication required',
            ]);
            exit;
        }

        redirect(url('login'));
    }
}
