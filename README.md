# House Rental Management System

A comprehensive, production-ready web application for managing house rentals with role-based access for Owners, Tenants, and Administrators.

## 📋 Table of Contents

- [Features](#features)
- [System Requirements](#system-requirements)
- [Installation Guide](#installation-guide)
- [Configuration](#configuration)
- [User Roles](#user-roles)
- [Project Structure](#project-structure)
- [API Documentation](#api-documentation)
- [Security Features](#security-features)

## ✨ Features

### Owner Features
- User registration and login with email verification
- Add, edit, and delete rental properties
- View tenant applications
- Manage property details (images, description, pricing)
- Track rental statistics and earnings
- Password reset functionality

### Tenant Features
- User registration and email verification
- Browse available properties
- Rate and review properties
- View property ratings
- View rental history
- Manage profile and preferences

### Admin Features
- Dashboard with system statistics
- View and manage all owner accounts
- View and manage all tenant accounts
- Monitor property listings
- View all ratings and reviews
- Generate reports
- User account approval and deactivation

## 🔧 System Requirements

### Server Requirements
- **PHP**: 8.0 or higher
- **MySQL**: 8.0 or higher (recommended: 8.0+)
- **Apache/Nginx**: with mod_rewrite enabled
- **OpenSSL**: for secure connections
- **GD Library**: for image processing

### Software Stack
- **Frontend**: Bootstrap 5 (via CDN)
- **Backend**: PHP 8.x
- **Database**: MySQL
- **Email Service**: PHPMailer
- **Authentication**: Session-based with password hashing

## 📦 Installation Guide

### Step 1: Clone the Repository

```bash
git clone https://github.com/yash0010-cyber/test.git
cd test
```

### Step 2: Create Database

```bash
# Connect to MySQL
mysql -u root -p

# Run the following SQL
CREATE DATABASE house_rental;
USE house_rental;

# The database tables will be created automatically on first run
# Or import the database dump:
mysql -u root -p house_rental < database/schema.sql
```

### Step 3: Configuration

Create a `.env` file in the root directory:

```env
# Database Configuration
DB_HOST=localhost
DB_USERNAME=root
DB_PASSWORD=your_password
DB_NAME=house_rental

# Email Configuration
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USERNAME=your_email@gmail.com
SMTP_PASSWORD=your_app_password
SENDER_EMAIL=your_email@gmail.com
SENDER_NAME=House Rental System

# Application Configuration
APP_URL=http://localhost:8000
APP_NAME=House Rental Management
APP_ENV=production

# Security
JWT_SECRET=your_secret_key_here
ENCRYPTION_KEY=your_encryption_key_here
```

### Step 4: Set File Permissions

```bash
# For Linux/Mac
chmod -R 755 public/
chmod -R 755 uploads/
chmod -R 755 storage/
chmod 600 config/.env
```

### Step 5: Install Dependencies (if using Composer)

```bash
composer install
```

### Step 6: Run Database Migrations

The application will automatically create required tables on first setup. You can also manually run:

```bash
php scripts/setup.php
```

### Step 7: Start the Application

#### Using PHP Built-in Server (Development)
```bash
php -S localhost:8000
```

#### Using Apache/Nginx (Production)
Configure your virtual host to point to the `public/` directory.

### Step 8: Access the Application

- **Application URL**: `http://localhost:8000`
- **Admin Panel**: `http://localhost:8000/admin/login`

## ⚙️ Configuration

### Email Configuration (Gmail)

1. Enable 2-Factor Authentication on your Gmail account
2. Generate an App Password: https://myaccount.google.com/apppasswords
3. Use the generated password in `.env` file as `SMTP_PASSWORD`

### Database Configuration

Update `config/Database.php` with your database credentials:

```php
$host = getenv('DB_HOST');
$db_name = getenv('DB_NAME');
$db_user = getenv('DB_USERNAME');
$db_pass = getenv('DB_PASSWORD');
```

## 👥 User Roles

### Owner
- **Login**: `owner@example.com` / `password123`
- **Permissions**: Add properties, manage listings, view tenant applications
- **Dashboard**: Property management, earnings, tenant requests

### Tenant
- **Login**: `tenant@example.com` / `password123`
- **Permissions**: Browse properties, rate and review, view ratings
- **Dashboard**: Browsed properties, saved properties, ratings history

### Admin
- **Login**: `admin@example.com` / `password123`
- **Permissions**: Full system access, user management, reporting
- **Dashboard**: System overview, user management, analytics

## 📁 Project Structure

```
test/
├── public/
│   ├── index.php                 # Entry point
│   ├── css/
│   │   └── custom.css           # Custom styles
│   ├── js/
│   │   └── main.js              # Custom scripts
│   └── uploads/                 # Property images
├── app/
│   ├── controllers/             # Request handlers
│   │   ├── AuthController.php
│   │   ├── OwnerController.php
│   │   ├── TenantController.php
│   │   └── AdminController.php
│   ├── models/                  # Data models
│   │   ├── User.php
│   │   ├── Property.php
│   │   ├── Rating.php
│   │   └── Tenant.php
│   ├── middleware/              # Request middleware
│   │   ├── AuthMiddleware.php
│   │   └── RoleMiddleware.php
│   └── utils/                   # Utility functions
│       ├── Validator.php
│       ├── Mailer.php
│       └── Helper.php
├── config/
│   ├── Database.php             # Database configuration
│   ├── constants.php            # Application constants
│   └── .env.example             # Environment template
├── database/
│   ├── schema.sql               # Database schema
│   └── seeders/                 # Sample data
├── storage/
│   ├── logs/                    # Application logs
│   └── cache/                   # Cache files
├── views/
│   ├── layouts/
│   │   └── main.php
│   ├── auth/
│   │   ├── login.php
│   │   └── register.php
│   ├── owner/                   # Owner views
│   ├── tenant/                  # Tenant views
│   └── admin/                   # Admin views
├── routes/
│   └── web.php                  # Application routes
├── .env.example                 # Environment template
├── .htaccess                    # Apache configuration
├── composer.json                # PHP dependencies
└── README.md                    # This file
```

## 🔐 Security Features

- Password hashing using `password_hash()` with bcrypt
- CSRF token protection on all forms
- SQL injection prevention through prepared statements
- XSS protection through output escaping
- Email verification for new registrations
- Session management and timeout
- Secure password reset with token expiration
- Input validation and sanitization
- Rate limiting on authentication endpoints

## 🚀 Deployment Guide

### Production Checklist

- [ ] Set `APP_ENV=production` in `.env`
- [ ] Use strong `JWT_SECRET` and `ENCRYPTION_KEY`
- [ ] Configure SSL certificate (HTTPS)
- [ ] Set up automated backups
- [ ] Configure email service credentials
- [ ] Set proper file permissions (755 for directories, 644 for files)
- [ ] Enable error logging
- [ ] Disable debug mode
- [ ] Configure firewall rules
- [ ] Set up monitoring and logging

### Server Setup (Ubuntu 20.04+)

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP 8.0+
sudo apt install php8.0-cli php8.0-mysql php8.0-gd php8.0-curl -y

# Install MySQL
sudo apt install mysql-server -y

# Install Apache
sudo apt install apache2 libapache2-mod-php8.0 -y

# Enable mod_rewrite
sudo a2enmod rewrite
sudo systemctl restart apache2
```

## 📖 API Documentation

### Authentication Endpoints

#### Register
```
POST /api/auth/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "SecurePass123",
  "role": "tenant"
}
```

#### Login
```
POST /api/auth/login
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "SecurePass123"
}
```

#### Forgot Password
```
POST /api/auth/forgot-password
Content-Type: application/json

{
  "email": "john@example.com"
}
```

### Property Endpoints (Owner)

#### Create Property
```
POST /api/properties
Content-Type: application/json
Authorization: Bearer {token}

{
  "title": "Beautiful 2BHK House",
  "description": "Modern house with all amenities",
  "price": 50000,
  "bedrooms": 2,
  "bathrooms": 2,
  "address": "123 Main Street"
}
```

#### Get Properties
```
GET /api/properties
```

#### Update Property
```
PUT /api/properties/{id}
```

#### Delete Property
```
DELETE /api/properties/{id}
```

### Rating Endpoints (Tenant)

#### Create Rating
```
POST /api/ratings
Content-Type: application/json
Authorization: Bearer {token}

{
  "property_id": 1,
  "rating": 4.5,
  "review": "Great property!"
}
```

#### Get Property Ratings
```
GET /api/properties/{id}/ratings
```

## 🐛 Troubleshooting

### Database Connection Error
- Verify MySQL is running: `sudo systemctl status mysql`
- Check database credentials in `.env`
- Ensure database exists: `CREATE DATABASE house_rental;`

### Email Not Sending
- Verify SMTP credentials in `.env`
- Enable "Less secure app access" (if using Gmail)
- Check PHP error logs: `tail -f /var/log/apache2/error.log`

### File Upload Issues
- Check permissions: `chmod -R 755 uploads/`
- Verify `php.ini` upload limits
- Check available disk space

### 404 Errors
- Ensure `.htaccess` is in the root and Apache has `mod_rewrite` enabled
- Check virtual host DocumentRoot points to `public/` directory

## 📝 License

This project is licensed under the MIT License - see LICENSE file for details.

## 👨‍💻 Support

For issues and feature requests, please create an issue on GitHub.

## 🙏 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

---

**Version**: 1.0.0  
**Last Updated**: May 2026  
**Maintained by**: Development Team
