<?php
/**
 * Constants Configuration
 * 
 * Centralized constants for the entire application
 */

// ========== Application Paths ==========
define('BASE_PATH', dirname(dirname(__FILE__)));
define('APP_PATH', BASE_PATH . '/app');
define('CONFIG_PATH', BASE_PATH . '/config');
define('PUBLIC_PATH', BASE_PATH . '/public');
define('STORAGE_PATH', BASE_PATH . '/storage');
define('VIEWS_PATH', BASE_PATH . '/views');
define('DATABASE_PATH', BASE_PATH . '/database');
define('UPLOADS_PATH', PUBLIC_PATH . '/uploads');

// ========== Application Settings ==========
define('APP_NAME', getenv('APP_NAME') ?: 'House Rental Management System');
define('APP_URL', getenv('APP_URL') ?: 'http://localhost:8000');
define('APP_ENV', getenv('APP_ENV') ?: 'production');
define('APP_DEBUG', getenv('APP_DEBUG') ?: false);
define('APP_TIMEZONE', getenv('APP_TIMEZONE') ?: 'UTC');

// ========== User Roles ==========
define('ROLE_ADMIN', 'admin');
define('ROLE_OWNER', 'owner');
define('ROLE_TENANT', 'tenant');

define('ROLES', [
    'admin' => 'Administrator',
    'owner' => 'Property Owner',
    'tenant' => 'Tenant'
]);

// ========== User Status ==========
define('STATUS_ACTIVE', 'active');
define('STATUS_INACTIVE', 'inactive');
define('STATUS_BANNED', 'banned');
define('STATUS_PENDING', 'pending');

define('USER_STATUS', [
    'active' => 'Active',
    'inactive' => 'Inactive',
    'banned' => 'Banned',
    'pending' => 'Pending Verification'
]);

// ========== Property Status ==========
define('PROPERTY_AVAILABLE', 'available');
define('PROPERTY_RENTED', 'rented');
define('PROPERTY_MAINTENANCE', 'maintenance');
define('PROPERTY_INACTIVE', 'inactive');

define('PROPERTY_STATUS', [
    'available' => 'Available',
    'rented' => 'Rented',
    'maintenance' => 'Under Maintenance',
    'inactive' => 'Inactive'
]);

// ========== Rental Application Status ==========
define('APP_PENDING', 'pending');
define('APP_APPROVED', 'approved');
define('APP_REJECTED', 'rejected');
define('APP_CANCELED', 'canceled');

define('APPLICATION_STATUS', [
    'pending' => 'Pending',
    'approved' => 'Approved',
    'rejected' => 'Rejected',
    'canceled' => 'Canceled'
]);

// ========== Rating Status ==========
define('RATING_PENDING', 'pending');
define('RATING_APPROVED', 'approved');
define('RATING_REJECTED', 'rejected');

define('RATING_STATUS', [
    'pending' => 'Pending Review',
    'approved' => 'Approved',
    'rejected' => 'Rejected'
]);

// ========== Security ==========
define('SESSION_LIFETIME', getenv('SESSION_LIFETIME') ?: 1440); // in minutes
define('CSRF_TOKEN_NAME', '_csrf_token');
define('PASSWORD_MIN_LENGTH', 8);
define('PASSWORD_RESET_TOKEN_EXPIRY', getenv('PASSWORD_RESET_TOKEN_EXPIRY') ?: 1); // in hours
define('EMAIL_VERIFICATION_EXPIRY', getenv('EMAIL_VERIFICATION_EXPIRY') ?: 24); // in hours
define('RATE_LIMIT_LOGIN_ATTEMPTS', getenv('RATE_LIMIT_LOGIN_ATTEMPTS') ?: 5);
define('RATE_LIMIT_LOGIN_TIMEOUT', getenv('RATE_LIMIT_LOGIN_TIMEOUT') ?: 900); // in seconds (15 minutes)

// ========== File Upload ==========
define('MAX_FILE_SIZE', getenv('MAX_FILE_SIZE') ?: 5242880); // 5MB in bytes
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('ALLOWED_DOCUMENT_TYPES', ['pdf', 'doc', 'docx', 'xls', 'xlsx']);

// ========== Pagination ==========
define('ITEMS_PER_PAGE', getenv('ITEMS_PER_PAGE') ?: 10);

// ========== Email Configuration ==========
define('MAIL_FROM_NAME', getenv('MAIL_FROM_NAME') ?: APP_NAME);
define('MAIL_FROM_ADDRESS', getenv('MAIL_FROM_ADDRESS') ?: 'noreply@example.com');
define('EMAIL_VERIFICATION_REQUIRED', getenv('EMAIL_VERIFICATION_REQUIRED') ?: true);

// ========== HTTP Status Codes ==========
define('HTTP_OK', 200);
define('HTTP_CREATED', 201);
define('HTTP_BAD_REQUEST', 400);
define('HTTP_UNAUTHORIZED', 401);
define('HTTP_FORBIDDEN', 403);
define('HTTP_NOT_FOUND', 404);
define('HTTP_CONFLICT', 409);
define('HTTP_INTERNAL_ERROR', 500);

// ========== Response Messages ==========
define('SUCCESS_MESSAGE', 'Operation completed successfully');
define('ERROR_MESSAGE', 'An error occurred. Please try again');
define('VALIDATION_ERROR_MESSAGE', 'Please check your input and try again');
define('UNAUTHORIZED_MESSAGE', 'You are not authorized to perform this action');
define('NOT_FOUND_MESSAGE', 'The requested resource was not found');

// ========== Validation Patterns ==========
define('EMAIL_PATTERN', '/^[^\s@]+@[^\s@]+\.[^\s@]+$/');
define('PHONE_PATTERN', '/^[0-9\s\-\+\(\)]{10,20}$/');
define('URL_PATTERN', '/^https?:\/\/(www\.)?[-a-zA-Z0-9@:%._\+~#=]{1,256}\.[a-zA-Z0-9()]{1,6}\b([-a-zA-Z0-9()@:%_\+.~#?&//=]*)$/');

// ========== Rating Range ==========
define('MIN_RATING', 1);
define('MAX_RATING', 5);

// ========== Default Values ==========
define('DEFAULT_PAGE', 1);
define('DEFAULT_SORT', 'created_at');
define('DEFAULT_ORDER', 'DESC');

// Set default timezone
date_default_timezone_set(APP_TIMEZONE);
