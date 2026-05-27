-- House Rental Management System Database Schema
-- MySQL Version 8.0+

-- Create Database
CREATE DATABASE IF NOT EXISTS house_rental;
USE house_rental;

-- ===== USERS TABLE =====
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'owner', 'tenant') NOT NULL,
    phone VARCHAR(20),
    profile_picture VARCHAR(255),
    status ENUM('active', 'inactive', 'banned', 'pending') DEFAULT 'pending',
    email_verified TINYINT DEFAULT 0,
    email_verified_at TIMESTAMP NULL,
    two_factor_enabled TINYINT DEFAULT 0,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===== EMAIL VERIFICATION TABLE =====
CREATE TABLE IF NOT EXISTS email_verifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    token VARCHAR(255) UNIQUE NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===== PASSWORD RESETS TABLE =====
CREATE TABLE IF NOT EXISTS password_resets (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    token VARCHAR(255) UNIQUE NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    used_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===== PROPERTIES TABLE =====
CREATE TABLE IF NOT EXISTS properties (
    id INT PRIMARY KEY AUTO_INCREMENT,
    owner_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description LONGTEXT,
    address VARCHAR(500) NOT NULL,
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100) NOT NULL,
    zip_code VARCHAR(10),
    country VARCHAR(100) DEFAULT 'India',
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    bedrooms INT NOT NULL,
    bathrooms INT NOT NULL,
    total_area DECIMAL(10, 2),
    rent_price DECIMAL(12, 2) NOT NULL,
    deposit_amount DECIMAL(12, 2),
    amenities JSON,
    photos JSON,
    featured_image VARCHAR(255),
    status ENUM('available', 'rented', 'maintenance', 'inactive') DEFAULT 'available',
    available_from DATE,
    views_count INT DEFAULT 0,
    rating_average DECIMAL(3, 2) DEFAULT 0,
    total_ratings INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_owner_id (owner_id),
    INDEX idx_status (status),
    INDEX idx_city (city),
    INDEX idx_rent_price (rent_price),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===== RATINGS/REVIEWS TABLE =====
CREATE TABLE IF NOT EXISTS ratings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    property_id INT NOT NULL,
    tenant_id INT NOT NULL,
    rating DECIMAL(3, 1) NOT NULL CHECK (rating >= 1 AND rating <= 5),
    review_title VARCHAR(255),
    review_text LONGTEXT,
    cleanliness_rating INT,
    amenities_rating INT,
    location_rating INT,
    landlord_rating INT,
    is_verified_tenant TINYINT DEFAULT 0,
    helpful_count INT DEFAULT 0,
    images JSON,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    FOREIGN KEY (tenant_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_property_id (property_id),
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_rating (rating),
    INDEX idx_status (status),
    UNIQUE KEY unique_property_tenant (property_id, tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===== RENTAL APPLICATIONS TABLE =====
CREATE TABLE IF NOT EXISTS rental_applications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    property_id INT NOT NULL,
    tenant_id INT NOT NULL,
    owner_id INT NOT NULL,
    message LONGTEXT,
    status ENUM('pending', 'approved', 'rejected', 'canceled') DEFAULT 'pending',
    viewed_by_owner TINYINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    FOREIGN KEY (tenant_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_property_id (property_id),
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_owner_id (owner_id),
    INDEX idx_status (status),
    UNIQUE KEY unique_property_tenant_app (property_id, tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===== FAVORITES TABLE =====
CREATE TABLE IF NOT EXISTS favorites (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tenant_id INT NOT NULL,
    property_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_property_id (property_id),
    UNIQUE KEY unique_favorite (tenant_id, property_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===== MESSAGES TABLE =====
CREATE TABLE IF NOT EXISTS messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    sender_id INT NOT NULL,
    recipient_id INT NOT NULL,
    property_id INT,
    subject VARCHAR(255),
    message LONGTEXT NOT NULL,
    is_read TINYINT DEFAULT 0,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (recipient_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE SET NULL,
    INDEX idx_sender_id (sender_id),
    INDEX idx_recipient_id (recipient_id),
    INDEX idx_is_read (is_read),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===== LOGIN ATTEMPTS TABLE =====
CREATE TABLE IF NOT EXISTS login_attempts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) NOT NULL,
    attempt_count INT DEFAULT 1,
    last_attempt_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===== LOGIN LOGS TABLE =====
CREATE TABLE IF NOT EXISTS login_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    ip_address VARCHAR(45),
    status ENUM('SUCCESS', 'FAILED', 'LOGOUT') NOT NULL,
    email VARCHAR(255),
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_timestamp (timestamp),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===== AUDIT LOGS TABLE =====
CREATE TABLE IF NOT EXISTS audit_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action VARCHAR(255) NOT NULL,
    entity_type VARCHAR(100),
    entity_id INT,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent VARCHAR(500),
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_timestamp (timestamp)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===== NOTIFICATIONS TABLE =====
CREATE TABLE IF NOT EXISTS notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message LONGTEXT,
    type VARCHAR(50),
    related_id INT,
    is_read TINYINT DEFAULT 0,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_is_read (is_read),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===== SETTINGS TABLE =====
CREATE TABLE IF NOT EXISTS settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(255) UNIQUE NOT NULL,
    setting_value LONGTEXT,
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_setting_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===== CREATE VIEWS =====

-- View: Available Properties
CREATE OR REPLACE VIEW available_properties AS
SELECT p.*, u.name as owner_name, u.email as owner_email, u.phone as owner_phone
FROM properties p
JOIN users u ON p.owner_id = u.id
WHERE p.status = 'available' AND p.deleted_at IS NULL;

-- View: Property Statistics
CREATE OR REPLACE VIEW property_statistics AS
SELECT 
    p.id,
    p.title,
    COUNT(DISTINCT r.id) as total_ratings,
    AVG(r.rating) as avg_rating,
    COUNT(DISTINCT f.id) as total_favorites,
    p.views_count
FROM properties p
LEFT JOIN ratings r ON p.id = r.property_id AND r.status = 'approved'
LEFT JOIN favorites f ON p.id = f.property_id
WHERE p.deleted_at IS NULL
GROUP BY p.id;

-- ===== INSERT SAMPLE DATA (OPTIONAL) =====

-- Sample Admin User
INSERT INTO users (name, email, password, role, phone, status, email_verified, email_verified_at) 
VALUES ('Admin User', 'admin@example.com', '$2y$12$8VQVbGU5Lm3dS0Wq9K2h9eJ3Q7x1P5M0L8c9R2T6U4V3W1X0Y7Z2', 'admin', '9876543210', 'active', 1, NOW())
ON DUPLICATE KEY UPDATE id=id;

-- Sample Owner User
INSERT INTO users (name, email, password, role, phone, status, email_verified, email_verified_at) 
VALUES ('Property Owner', 'owner@example.com', '$2y$12$8VQVbGU5Lm3dS0Wq9K2h9eJ3Q7x1P5M0L8c9R2T6U4V3W1X0Y7Z2', 'owner', '8765432109', 'active', 1, NOW())
ON DUPLICATE KEY UPDATE id=id;

-- Sample Tenant User
INSERT INTO users (name, email, password, role, phone, status, email_verified, email_verified_at) 
VALUES ('Sample Tenant', 'tenant@example.com', '$2y$12$8VQVbGU5Lm3dS0Wq9K2h9eJ3Q7x1P5M0L8c9R2T6U4V3W1X0Y7Z2', 'tenant', '7654321098', 'active', 1, NOW())
ON DUPLICATE KEY UPDATE id=id;

-- ===== CREATE INDEXES FOR PERFORMANCE =====

-- Composite indexes
ALTER TABLE properties ADD INDEX idx_owner_status_city (owner_id, status, city);
ALTER TABLE ratings ADD INDEX idx_property_status_rating (property_id, status, rating);
ALTER TABLE rental_applications ADD INDEX idx_owner_status_created (owner_id, status, created_at);

-- ===== SET FOREIGN KEY CHECKS =====
SET FOREIGN_KEY_CHECKS = 1;
