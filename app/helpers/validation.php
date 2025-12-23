<?php
/**
 * Validation Helper Functions
 *
 * Input validation utilities
 */

/**
 * Validate email address
 *
 * @param string $email Email to validate
 * @return bool
 */
function isValidEmail($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate password strength
 *
 * @param string $password Password to validate
 * @param int $minLength Minimum length
 * @return array ['valid' => bool, 'errors' => array]
 */
function validatePassword($password, $minLength = 8)
{
    $errors = [];

    if (strlen($password) < $minLength) {
        $errors[] = "Password must be at least {$minLength} characters long";
    }

    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}

/**
 * Validate required fields
 *
 * @param array $data Data to validate
 * @param array $required Required field names
 * @return array ['valid' => bool, 'errors' => array]
 */
function validateRequired($data, $required)
{
    $errors = [];

    foreach ($required as $field) {
        if (empty($data[$field])) {
            $fieldName = ucfirst(str_replace('_', ' ', $field));
            $errors[$field] = "{$fieldName} is required";
        }
    }

    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}

/**
 * Validate string length
 *
 * @param string $value Value to validate
 * @param int $min Minimum length
 * @param int $max Maximum length
 * @return bool
 */
function isValidLength($value, $min = 1, $max = 255)
{
    $length = strlen($value);
    return $length >= $min && $length <= $max;
}

/**
 * Validate phone number
 *
 * @param string $phone Phone number
 * @return bool
 */
function isValidPhone($phone)
{
    // Basic phone validation (digits, spaces, +, -, ())
    return preg_match('/^[0-9+\-\s()]+$/', $phone);
}

/**
 * Validate integer
 *
 * @param mixed $value Value to validate
 * @param int $min Minimum value
 * @param int $max Maximum value
 * @return bool
 */
function isValidInt($value, $min = null, $max = null)
{
    if (!is_numeric($value)) {
        return false;
    }

    $intValue = (int)$value;

    if ($min !== null && $intValue < $min) {
        return false;
    }

    if ($max !== null && $intValue > $max) {
        return false;
    }

    return true;
}

/**
 * Validate enum value
 *
 * @param mixed $value Value to validate
 * @param array $allowed Allowed values
 * @return bool
 */
function isValidEnum($value, $allowed)
{
    return in_array($value, $allowed, true);
}

/**
 * Sanitize and validate email
 *
 * @param string $email Email address
 * @return string|false
 */
function sanitizeEmail($email)
{
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    return isValidEmail($email) ? $email : false;
}

/**
 * Validate URL
 *
 * @param string $url URL to validate
 * @return bool
 */
function isValidUrl($url)
{
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

/**
 * Validate boolean value
 *
 * @param mixed $value Value to validate
 * @return bool
 */
function isValidBool($value)
{
    return in_array($value, [true, false, 1, 0, '1', '0', 'true', 'false'], true);
}

/**
 * Convert to boolean
 *
 * @param mixed $value Value to convert
 * @return bool
 */
function toBool($value)
{
    return in_array($value, [true, 1, '1', 'true', 'on', 'yes'], true);
}
