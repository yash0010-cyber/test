<?php
/**
 * Authentication Middleware
 * 
 * Handles user authentication, authorization, and session management
 */

class AuthMiddleware {
    private $db;
    private $user = null;

    /**
     * Constructor
     * 
     * @param Database $db
     */
    public function __construct(Database $db) {
        $this->db = $db;
    }

    /**
     * Handle authentication check
     * 
     * @return void
     */
    public function handle() {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            $this->startSecureSession();
        }

        // Check for session timeout
        $this->checkSessionTimeout();

        // If user is logged in, verify they still exist and are active
        if (isset($_SESSION['user_id'])) {
            $this->verifyUser();
        }

        // Set security headers
        $this->setSecurityHeaders();
    }

    /**
     * Start secure session with proper settings
     * 
     * @return void
     */
    private function startSecureSession() {
        // Session configuration
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? 1 : 0);
        ini_set('session.use_strict_mode', 1);
        ini_set('session.cookie_samesite', 'Lax');
        ini_set('session.gc_maxlifetime', SESSION_LIFETIME * 60);

        session_start();

        // Initialize session metadata
        if (!isset($_SESSION['CREATED'])) {
            $_SESSION['CREATED'] = time();
        }

        // Regenerate session ID on login (should be called from login controller)
        if (!isset($_SESSION['REGENERATED'])) {
            session_regenerate_id(true);
            $_SESSION['REGENERATED'] = true;
        }
    }

    /**
     * Check for session timeout
     * 
     * @return void
     */
    private function checkSessionTimeout() {
        if (isset($_SESSION['CREATED'])) {
            $timeout = SESSION_LIFETIME * 60;
            if (time() - $_SESSION['CREATED'] > $timeout) {
                $this->logout();
                setError('Your session has expired. Please log in again.');
                redirect('/login');
            }
        }
    }

    /**
     * Verify user still exists and is active
     * 
     * @return void
     */
    private function verifyUser() {
        $userId = $_SESSION['user_id'];

        $this->db->prepare("SELECT id, email, role, status FROM users WHERE id = :id AND deleted_at IS NULL");
        $this->db->bind(':id', $userId);
        $user = $this->db->single();

        if (!$user) {
            $this->logout();
            redirect('/login');
        }

        // Check if user is banned
        if ($user['status'] === STATUS_BANNED) {
            $this->logout();
            setError('Your account has been banned.');
            redirect('/login');
        }

        // Update last activity time
        if (!isset($_SESSION['last_activity'])) {
            $_SESSION['last_activity'] = time();
        }
    }

    /**
     * Set security headers
     * 
     * @return void
     */
    private function setSecurityHeaders() {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
    }

    /**
     * Check if user is authenticated
     * 
     * @return bool
     */
    public function isAuthenticated() {
        return isset($_SESSION['user_id']) && isset($_SESSION['user_role']);
    }

    /**
     * Check if user has specific role
     * 
     * @param string|array $role
     * @return bool
     */
    public function hasRole($role) {
        if (!$this->isAuthenticated()) {
            return false;
        }

        if (is_array($role)) {
            return in_array($_SESSION['user_role'], $role);
        }

        return $_SESSION['user_role'] === $role;
    }

    /**
     * Get current user ID
     * 
     * @return int|null
     */
    public function userId() {
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Get current user role
     * 
     * @return string|null
     */
    public function userRole() {
        return $_SESSION['user_role'] ?? null;
    }

    /**
     * Get current user data
     * 
     * @return array|null
     */
    public function user() {
        if ($this->user === null && $this->isAuthenticated()) {
            $this->db->prepare("SELECT * FROM users WHERE id = :id");
            $this->db->bind(':id', $this->userId());
            $this->user = $this->db->single();
        }

        return $this->user;
    }

    /**
     * Login user
     * 
     * @param int $userId
     * @param string $role
     * @param bool $rememberMe
     * @return void
     */
    public function login($userId, $role, $rememberMe = false) {
        // Regenerate session ID
        session_regenerate_id(true);

        // Set session variables
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_role'] = $role;
        $_SESSION['CREATED'] = time();
        $_SESSION['REGENERATED'] = true;
        $_SESSION['login_time'] = time();

        // Remember me functionality
        if ($rememberMe) {
            $this->setRememberMeCookie($userId);
        }

        // Log login
        $this->logLogin($userId, 'SUCCESS');

        // Reset login attempts
        $this->resetLoginAttempts($_SESSION['user_email']);
    }

    /**
     * Logout user
     * 
     * @return void
     */
    public function logout() {
        $userId = $_SESSION['user_id'] ?? null;

        // Log logout
        if ($userId) {
            $this->logLogin($userId, 'LOGOUT');
        }

        // Clear session
        session_unset();
        session_destroy();

        // Clear remember me cookie
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/');
        }
    }

    /**
     * Check if user has attempted login too many times
     * 
     * @param string $email
     * @return bool
     */
    public function isRateLimited($email) {
        $this->db->prepare("SELECT attempt_count, last_attempt_time FROM login_attempts WHERE email = :email");
        $this->db->bind(':email', $email);
        $attempt = $this->db->single();

        if (!$attempt) {
            return false;
        }

        $timeDiff = time() - strtotime($attempt['last_attempt_time']);
        
        // Reset if timeout period has passed
        if ($timeDiff > RATE_LIMIT_LOGIN_TIMEOUT) {
            $this->db->prepare("DELETE FROM login_attempts WHERE email = :email");
            $this->db->bind(':email', $email);
            $this->db->execute();
            return false;
        }

        return $attempt['attempt_count'] >= RATE_LIMIT_LOGIN_ATTEMPTS;
    }

    /**
     * Record failed login attempt
     * 
     * @param string $email
     * @return void
     */
    public function recordFailedAttempt($email) {
        $this->db->prepare("
            INSERT INTO login_attempts (email, attempt_count, last_attempt_time)
            VALUES (:email, 1, NOW())
            ON DUPLICATE KEY UPDATE
            attempt_count = attempt_count + 1,
            last_attempt_time = NOW()
        ");
        $this->db->bind(':email', $email);
        $this->db->execute();

        // Log failed attempt
        $this->logLogin(null, 'FAILED', $email);
    }

    /**
     * Reset login attempts
     * 
     * @param string $email
     * @return void
     */
    private function resetLoginAttempts($email) {
        $this->db->prepare("DELETE FROM login_attempts WHERE email = :email");
        $this->db->bind(':email', $email);
        $this->db->execute();
    }

    /**
     * Set remember me cookie
     * 
     * @param int $userId
     * @return void
     */
    private function setRememberMeCookie($userId) {
        $token = generateToken(32);
        $expiry = time() + (30 * 24 * 60 * 60); // 30 days

        // Store in database
        // This would typically store the token hash in a remember_tokens table
        
        setcookie('remember_token', $token, $expiry, '/', '', true, true);
    }

    /**
     * Log login/logout
     * 
     * @param int|null $userId
     * @param string $status
     * @param string $email
     * @return void
     */
    private function logLogin($userId = null, $status = 'SUCCESS', $email = '') {
        $this->db->prepare("
            INSERT INTO login_logs (user_id, ip_address, status, email, timestamp)
            VALUES (:user_id, :ip_address, :status, :email, NOW())
        ");
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':ip_address', getClientIP());
        $this->db->bind(':status', $status);
        $this->db->bind(':email', $email);
        $this->db->execute();
    }

    /**
     * Audit log - Record user actions
     * 
     * @param string $action
     * @param string $entityType
     * @param int $entityId
     * @param array $oldValues
     * @param array $newValues
     * @return void
     */
    public function auditLog($action, $entityType = null, $entityId = null, $oldValues = [], $newValues = []) {
        if (!$this->isAuthenticated()) {
            return;
        }

        $this->db->prepare("
            INSERT INTO audit_logs (user_id, action, entity_type, entity_id, old_values, new_values, ip_address, user_agent, timestamp)
            VALUES (:user_id, :action, :entity_type, :entity_id, :old_values, :new_values, :ip_address, :user_agent, NOW())
        ");
        $this->db->bind(':user_id', $this->userId());
        $this->db->bind(':action', $action);
        $this->db->bind(':entity_type', $entityType);
        $this->db->bind(':entity_id', $entityId);
        $this->db->bind(':old_values', !empty($oldValues) ? json_encode($oldValues) : null);
        $this->db->bind(':new_values', !empty($newValues) ? json_encode($newValues) : null);
        $this->db->bind(':ip_address', getClientIP());
        $this->db->bind(':user_agent', getUserAgent());
        $this->db->execute();
    }
}
