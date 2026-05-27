<?php
/**
 * Input Validator Class
 * 
 * Handles all input validation and sanitization
 */

class Validator {
    private static $errors = [];

    /**
     * Validate email format
     * 
     * @param string $email
     * @return bool
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate password strength
     * 
     * @param string $password
     * @return bool
     */
    public static function validatePassword($password) {
        // At least 8 characters, 1 uppercase, 1 lowercase, 1 number
        return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password);
    }

    /**
     * Validate password length
     * 
     * @param string $password
     * @return bool
     */
    public static function validatePasswordLength($password) {
        return strlen($password) >= PASSWORD_MIN_LENGTH;
    }

    /**
     * Validate phone number
     * 
     * @param string $phone
     * @return bool
     */
    public static function validatePhone($phone) {
        return preg_match(PHONE_PATTERN, $phone);
    }

    /**
     * Validate URL format
     * 
     * @param string $url
     * @return bool
     */
    public static function validateUrl($url) {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Validate string length
     * 
     * @param string $value
     * @param int $min
     * @param int $max
     * @return bool
     */
    public static function validateLength($value, $min = 0, $max = 255) {
        $length = strlen($value);
        return $length >= $min && $length <= $max;
    }

    /**
     * Validate required field
     * 
     * @param mixed $value
     * @return bool
     */
    public static function validateRequired($value) {
        return !empty($value) && trim($value) !== '';
    }

    /**
     * Validate numeric value
     * 
     * @param mixed $value
     * @return bool
     */
    public static function validateNumeric($value) {
        return is_numeric($value);
    }

    /**
     * Validate integer value
     * 
     * @param mixed $value
     * @return bool
     */
    public static function validateInteger($value) {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    /**
     * Validate positive number
     * 
     * @param mixed $value
     * @return bool
     */
    public static function validatePositive($value) {
        return is_numeric($value) && $value > 0;
    }

    /**
     * Validate decimal number
     * 
     * @param mixed $value
     * @param int $decimals
     * @return bool
     */
    public static function validateDecimal($value, $decimals = 2) {
        return filter_var($value, FILTER_VALIDATE_FLOAT) !== false;
    }

    /**
     * Validate date format
     * 
     * @param string $date
     * @param string $format
     * @return bool
     */
    public static function validateDate($date, $format = 'Y-m-d') {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

    /**
     * Validate file upload
     * 
     * @param array $file
     * @param array $allowedTypes
     * @param int $maxSize
     * @return bool
     */
    public static function validateFileUpload($file, $allowedTypes = [], $maxSize = null) {
        if (!isset($file['tmp_name']) || !isset($file['size']) || !isset($file['error'])) {
            return false;
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        if ($maxSize === null) {
            $maxSize = MAX_FILE_SIZE;
        }

        if ($file['size'] > $maxSize) {
            return false;
        }

        if (!empty($allowedTypes)) {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedTypes)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validate image upload
     * 
     * @param array $file
     * @return bool
     */
    public static function validateImageUpload($file) {
        return self::validateFileUpload($file, ALLOWED_IMAGE_TYPES, MAX_FILE_SIZE);
    }

    /**
     * Validate rating value
     * 
     * @param mixed $rating
     * @return bool
     */
    public static function validateRating($rating) {
        $rating = (float)$rating;
        return $rating >= MIN_RATING && $rating <= MAX_RATING;
    }

    /**
     * Validate that email exists in database
     * 
     * @param string $email
     * @param Database $db
     * @return bool
     */
    public static function emailExists($email, $db) {
        $db->prepare("SELECT id FROM users WHERE email = :email");
        $db->bind(':email', $email);
        return $db->rowCount() > 0;
    }

    /**
     * Validate that email is unique (doesn't exist)
     * 
     * @param string $email
     * @param Database $db
     * @return bool
     */
    public static function emailUnique($email, $db) {
        return !self::emailExists($email, $db);
    }

    /**
     * Sanitize string input
     * 
     * @param string $input
     * @return string
     */
    public static function sanitizeString($input) {
        $input = trim($input);
        $input = stripslashes($input);
        $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        return $input;
    }

    /**
     * Sanitize email
     * 
     * @param string $email
     * @return string
     */
    public static function sanitizeEmail($email) {
        return filter_var(trim($email), FILTER_SANITIZE_EMAIL);
    }

    /**
     * Sanitize URL
     * 
     * @param string $url
     * @return string
     */
    public static function sanitizeUrl($url) {
        return filter_var(trim($url), FILTER_SANITIZE_URL);
    }

    /**
     * Sanitize number
     * 
     * @param mixed $number
     * @return mixed
     */
    public static function sanitizeNumber($number) {
        return filter_var($number, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }

    /**
     * Escape output for HTML
     * 
     * @param string $output
     * @return string
     */
    public static function escape($output) {
        return htmlspecialchars($output, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Validate CSRF token
     * 
     * @param string $token
     * @return bool
     */
    public static function validateCsrfToken($token) {
        if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
            return false;
        }
        return hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
    }

    /**
     * Generate CSRF token
     * 
     * @return string
     */
    public static function generateCsrfToken() {
        if (empty($_SESSION[CSRF_TOKEN_NAME])) {
            $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
        }
        return $_SESSION[CSRF_TOKEN_NAME];
    }

    /**
     * Add error message
     * 
     * @param string $field
     * @param string $message
     * @return void
     */
    public static function addError($field, $message) {
        self::$errors[$field] = $message;
    }

    /**
     * Get all errors
     * 
     * @return array
     */
    public static function getErrors() {
        return self::$errors;
    }

    /**
     * Check if there are errors
     * 
     * @return bool
     */
    public static function hasErrors() {
        return !empty(self::$errors);
    }

    /**
     * Clear all errors
     * 
     * @return void
     */
    public static function clearErrors() {
        self::$errors = [];
    }

    /**
     * Get error for specific field
     * 
     * @param string $field
     * @return string|null
     */
    public static function getError($field) {
        return isset(self::$errors[$field]) ? self::$errors[$field] : null;
    }
}
