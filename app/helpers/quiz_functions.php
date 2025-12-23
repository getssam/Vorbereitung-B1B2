<?php
/**
 * Quiz Helper Functions
 */

require_once __DIR__ . '/quiz_names.php';

/**
 * Get display name for a quiz file
 * 
 * @param string $filename Quiz filename (e.g., '204.html')
 * @return string Display name or filename if not found
 */
if (!function_exists('getQuizDisplayName')) {
    function getQuizDisplayName($filename) {
        static $names = null;
        if ($names === null) {
            $names = require __DIR__ . '/quiz_names.php';
        }
        return $names[$filename] ?? $filename;
    }
}

