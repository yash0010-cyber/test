<?php
/**
 * Helper Functions
 * 
 * Common utility functions for the application
 */

// ========== Authentication Helpers ==========

/**
 * Check if user is logged in
 * 
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_role']);
}

/**
 * Check if user has specific role
 * 
 * @param string|array $role
 * @return bool
 */
function hasRole($role) {
    if (!isLoggedIn()) {
        return false;
    }
    
    if (is_array($role)) {
        return in_array($_SESSION['user_role'], $role);
    }
    
    return $_SESSION['user_role'] === $role;
}

/**
 * Check if user is admin
 * 
 * @return bool
 */
function isAdmin() {
    return hasRole(ROLE_ADMIN);
}

/**
 * Check if user is owner
 * 
 * @return bool
 */
function isOwner() {
    return hasRole(ROLE_OWNER);
}

/**
 * Check if user is tenant
 * 
 * @return bool
 */
function isTenant() {
    return hasRole(ROLE_TENANT);
}

/**
 * Get current user ID
 * 
 * @return int|null
 */
function getCurrentUserId() {
    return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
}

/**
 * Get current user role
 * 
 * @return string|null
 */
function getCurrentUserRole() {
    return isset($_SESSION['user_role']) ? $_SESSION['user_role'] : null;
}

/**
 * Redirect to login if not authenticated
 * 
 * @return void
 */
function requireLogin() {
    if (!isLoggedIn()) {
        redirect('/login?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    }
}

/**
 * Redirect based on role if not authenticated
 * 
 * @param string|array $role
 * @return void
 */
function requireRole($role) {
    requireLogin();
    
    if (!hasRole($role)) {
        redirect('/error/unauthorized');
    }
}

// ========== Session Helpers ==========

/**
 * Start secure session
 * 
 * @return void
 */
function startSecureSession() {
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', 1);
        ini_set('session.use_strict_mode', 1);
        session_start();
        
        // Regenerate session ID
        if (!isset($_SESSION['CREATED'])) {
            $_SESSION['CREATED'] = time();
        } else if (time() - $_SESSION['CREATED'] > SESSION_LIFETIME * 60) {
            // Session timeout
            session_unset();
            session_destroy();
            redirect('/login?expired=1');
        }
    }
}

/**
 * Set session flash message
 * 
 * @param string $type
 * @param string $message
 * @return void
 */
function setFlash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get session flash message
 * 
 * @return array|null
 */
function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Set success message
 * 
 * @param string $message
 * @return void
 */
function setSuccess($message) {
    setFlash('success', $message);
}

/**
 * Set error message
 * 
 * @param string $message
 * @return void
 */
function setError($message) {
    setFlash('error', $message);
}

/**
 * Set warning message
 * 
 * @param string $message
 * @return void
 */
function setWarning($message) {
    setFlash('warning', $message);
}

/**
 * Set info message
 * 
 * @param string $message
 * @return void
 */
function setInfo($message) {
    setFlash('info', $message);
}

// ========== CSRF Helpers ==========

/**
 * Generate CSRF token
 * 
 * @return string
 */
function csrfToken() {
    return Validator::generateCsrfToken();
}

/**
 * Output CSRF token input field
 * 
 * @return string
 */
function csrfField() {
    $token = csrfToken();
    return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . $token . '">';
}

/**
 * Verify CSRF token from POST request
 * 
 * @return bool
 */
function verifyCsrfToken() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST[CSRF_TOKEN_NAME] ?? null;
        return Validator::validateCsrfToken($token);
    }
    return true;
}

// ========== Redirect & URL Helpers ==========

/**
 * Redirect to URL
 * 
 * @param string $url
 * @return void
 */
function redirect($url) {
    header("Location: $url");
    exit();
}

/**
 * Get absolute URL
 * 
 * @param string $path
 * @return string
 */
function url($path = '') {
    return rtrim(APP_URL, '/') . '/' . ltrim($path, '/');
}

/**
 * Get current URL
 * 
 * @return string
 */
function currentUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    return $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

// ========== Formatting Helpers ==========

/**
 * Format currency
 * 
 * @param float $amount
 * @param string $currency
 * @return string
 */
function formatCurrency($amount, $currency = '₹') {
    return $currency . ' ' . number_format($amount, 2);
}

/**
 * Format date
 * 
 * @param string $date
 * @param string $format
 * @return string
 */
function formatDate($date, $format = 'd M Y') {
    if (empty($date)) {
        return '';
    }
    return date($format, strtotime($date));
}

/**
 * Format time ago
 * 
 * @param string $date
 * @return string
 */
function timeAgo($date) {
    $time = strtotime($date);
    $diff = time() - $time;
    
    if ($diff < 60) {
        return 'just now';
    } elseif ($diff < 3600) {
        $minutes = floor($diff / 60);
        return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return formatDate($date);
    }
}

/**
 * Format rating as stars
 * 
 * @param float $rating
 * @return string
 */
function getRatingStars($rating) {
    $rating = (float)$rating;
    $fullStars = floor($rating);
    $hasHalfStar = ($rating - $fullStars) >= 0.5;
    
    $html = '';
    for ($i = 0; $i < 5; $i++) {
        if ($i < $fullStars) {
            $html .= '<span class="star full">★</span>';
        } elseif ($i === $fullStars && $hasHalfStar) {
            $html .= '<span class="star half">★</span>';
        } else {
            $html .= '<span class="star empty">☆</span>';
        }
    }
    
    return $html;
}

// ========== Password Helpers ==========

/**
 * Hash password
 * 
 * @param string $password
 * @return string
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * Verify password
 * 
 * @param string $password
 * @param string $hash
 * @return bool
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Generate random token
 * 
 * @param int $length
 * @return string
 */
function generateToken($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

// ========== File Helpers ==========

/**
 * Upload file
 * 
 * @param array $file
 * @param string $directory
 * @return string|false
 */
function uploadFile($file, $directory = 'uploads') {
    if (!isset($file['tmp_name']) || !isset($file['name'])) {
        return false;
    }
    
    $uploadDir = PUBLIC_PATH . '/' . $directory;
    
    // Create directory if not exists
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $filename = uniqid() . '_' . basename($file['name']);
    $filepath = $uploadDir . '/' . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return '/' . $directory . '/' . $filename;
    }
    
    return false;
}

/**
 * Delete file
 * 
 * @param string $filepath
 * @return bool
 */
function deleteFile($filepath) {
    $fullPath = PUBLIC_PATH . $filepath;
    if (file_exists($fullPath)) {
        return unlink($fullPath);
    }
    return false;
}

// ========== Logging Helpers ==========

/**
 * Log message
 * 
 * @param string $message
 * @param string $level
 * @return void
 */
function logMessage($message, $level = 'info') {
    $logFile = STORAGE_PATH . '/logs/app.log';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;
    
    // Create log directory if not exists
    if (!is_dir(STORAGE_PATH . '/logs')) {
        mkdir(STORAGE_PATH . '/logs', 0755, true);
    }
    
    error_log($logMessage, 3, $logFile);
}

/**
 * Log error
 * 
 * @param string $message
 * @return void
 */
function logError($message) {
    logMessage($message, 'error');
}

/**
 * Log success
 * 
 * @param string $message
 * @return void
 */
function logSuccess($message) {
    logMessage($message, 'success');
}

// ========== Output Helpers ==========

/**
 * Output JSON response
 * 
 * @param array $data
 * @param int $statusCode
 * @return void
 */
function jsonResponse($data, $statusCode = 200) {
    header('Content-Type: application/json');
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

/**
 * Get client IP address
 * 
 * @return string
 */
function getClientIP() {
    if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
        return $_SERVER['HTTP_CF_CONNECTING_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($ips[0]);
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

/**
 * Get user agent
 * 
 * @return string
 */
function getUserAgent() {
    return $_SERVER['HTTP_USER_AGENT'] ?? '';
}
