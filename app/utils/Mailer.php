<?php
/**
 * Mailer Class - Email Service using PHPMailer
 * 
 * Handles all email operations including verification, password reset, notifications
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer {
    private $mail;
    private $from_email;
    private $from_name;

    /**
     * Constructor - Initialize PHPMailer
     */
    public function __construct() {
        $this->mail = new PHPMailer(true);
        $this->from_email = getenv('MAIL_FROM_ADDRESS') ?: MAIL_FROM_ADDRESS;
        $this->from_name = getenv('MAIL_FROM_NAME') ?: MAIL_FROM_NAME;
        
        // Configure SMTP
        try {
            $this->mail->isSMTP();
            $this->mail->Host = getenv('MAIL_HOST') ?: 'smtp.gmail.com';
            $this->mail->SMTPAuth = true;
            $this->mail->Username = getenv('MAIL_USERNAME');
            $this->mail->Password = getenv('MAIL_PASSWORD');
            $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mail->Port = getenv('MAIL_PORT') ?: 587;
            $this->mail->CharSet = 'UTF-8';
        } catch (Exception $e) {
            error_log("Mailer Configuration Error: " . $e->getMessage());
        }
    }

    /**
     * Set sender email
     * 
     * @param string $email
     * @param string $name
     * @return Mailer
     */
    public function setFrom($email, $name = '') {
        try {
            $this->mail->setFrom($email, $name ?: $this->from_name);
        } catch (Exception $e) {
            error_log("Mailer Error: " . $e->getMessage());
        }
        return $this;
    }

    /**
     * Set recipient email
     * 
     * @param string $email
     * @param string $name
     * @return Mailer
     */
    public function setTo($email, $name = '') {
        try {
            $this->mail->addAddress($email, $name);
        } catch (Exception $e) {
            error_log("Mailer Error: " . $e->getMessage());
        }
        return $this;
    }

    /**
     * Add CC recipient
     * 
     * @param string $email
     * @return Mailer
     */
    public function addCC($email) {
        try {
            $this->mail->addCC($email);
        } catch (Exception $e) {
            error_log("Mailer Error: " . $e->getMessage());
        }
        return $this;
    }

    /**
     * Set email subject
     * 
     * @param string $subject
     * @return Mailer
     */
    public function setSubject($subject) {
        $this->mail->Subject = $subject;
        return $this;
    }

    /**
     * Set email body (HTML)
     * 
     * @param string $body
     * @return Mailer
     */
    public function setBody($body) {
        $this->mail->isHTML(true);
        $this->mail->Body = $body;
        return $this;
    }

    /**
     * Send email verification
     * 
     * @param string $email
     * @param string $name
     * @param string $token
     * @return bool
     */
    public function sendVerification($email, $name, $token) {
        try {
            $this->mail->clearAddresses();
            $this->mail->setFrom($this->from_email, $this->from_name);
            $this->mail->addAddress($email, $name);
            $this->mail->Subject = APP_NAME . ' - Email Verification';
            
            $verificationLink = APP_URL . '/verify-email?token=' . $token;
            
            $body = $this->getEmailTemplate('verification', [
                'name' => $name,
                'link' => $verificationLink,
                'expiry' => EMAIL_VERIFICATION_EXPIRY
            ]);
            
            $this->mail->isHTML(true);
            $this->mail->Body = $body;
            
            return $this->mail->send();
        } catch (Exception $e) {
            error_log("Email Verification Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send password reset email
     * 
     * @param string $email
     * @param string $name
     * @param string $token
     * @return bool
     */
    public function sendPasswordReset($email, $name, $token) {
        try {
            $this->mail->clearAddresses();
            $this->mail->setFrom($this->from_email, $this->from_name);
            $this->mail->addAddress($email, $name);
            $this->mail->Subject = APP_NAME . ' - Password Reset Request';
            
            $resetLink = APP_URL . '/reset-password?token=' . $token;
            
            $body = $this->getEmailTemplate('password-reset', [
                'name' => $name,
                'link' => $resetLink,
                'expiry' => PASSWORD_RESET_TOKEN_EXPIRY
            ]);
            
            $this->mail->isHTML(true);
            $this->mail->Body = $body;
            
            return $this->mail->send();
        } catch (Exception $e) {
            error_log("Password Reset Email Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send welcome email
     * 
     * @param string $email
     * @param string $name
     * @return bool
     */
    public function sendWelcome($email, $name) {
        try {
            $this->mail->clearAddresses();
            $this->mail->setFrom($this->from_email, $this->from_name);
            $this->mail->addAddress($email, $name);
            $this->mail->Subject = 'Welcome to ' . APP_NAME;
            
            $body = $this->getEmailTemplate('welcome', [
                'name' => $name,
                'app_url' => APP_URL
            ]);
            
            $this->mail->isHTML(true);
            $this->mail->Body = $body;
            
            return $this->mail->send();
        } catch (Exception $e) {
            error_log("Welcome Email Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send rental application notification to owner
     * 
     * @param string $ownerEmail
     * @param string $ownerName
     * @param string $tenantName
     * @param string $propertyTitle
     * @param int $applicationId
     * @return bool
     */
    public function sendApplicationNotification($ownerEmail, $ownerName, $tenantName, $propertyTitle, $applicationId) {
        try {
            $this->mail->clearAddresses();
            $this->mail->setFrom($this->from_email, $this->from_name);
            $this->mail->addAddress($ownerEmail, $ownerName);
            $this->mail->Subject = 'New Rental Application for ' . $propertyTitle;
            
            $applicationLink = APP_URL . '/owner/applications/' . $applicationId;
            
            $body = $this->getEmailTemplate('application-notification', [
                'owner_name' => $ownerName,
                'tenant_name' => $tenantName,
                'property_title' => $propertyTitle,
                'link' => $applicationLink
            ]);
            
            $this->mail->isHTML(true);
            $this->mail->Body = $body;
            
            return $this->mail->send();
        } catch (Exception $e) {
            error_log("Application Notification Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send rating notification to owner
     * 
     * @param string $ownerEmail
     * @param string $ownerName
     * @param string $tenantName
     * @param string $propertyTitle
     * @param float $rating
     * @return bool
     */
    public function sendRatingNotification($ownerEmail, $ownerName, $tenantName, $propertyTitle, $rating) {
        try {
            $this->mail->clearAddresses();
            $this->mail->setFrom($this->from_email, $this->from_name);
            $this->mail->addAddress($ownerEmail, $ownerName);
            $this->mail->Subject = 'New Review for ' . $propertyTitle;
            
            $body = $this->getEmailTemplate('rating-notification', [
                'owner_name' => $ownerName,
                'tenant_name' => $tenantName,
                'property_title' => $propertyTitle,
                'rating' => $rating
            ]);
            
            $this->mail->isHTML(true);
            $this->mail->Body = $body;
            
            return $this->mail->send();
        } catch (Exception $e) {
            error_log("Rating Notification Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get email template
     * 
     * @param string $template
     * @param array $data
     * @return string
     */
    private function getEmailTemplate($template, $data = []) {
        $templatePath = VIEWS_PATH . '/emails/' . $template . '.html';
        
        if (!file_exists($templatePath)) {
            // Return default template if specific template not found
            return $this->getDefaultTemplate($template, $data);
        }
        
        ob_start();
        include $templatePath;
        $content = ob_get_clean();
        
        return $content;
    }

    /**
     * Get default email template
     * 
     * @param string $type
     * @param array $data
     * @return string
     */
    private function getDefaultTemplate($type, $data = []) {
        $name = isset($data['name']) ? htmlspecialchars($data['name']) : 'User';
        
        $html = '
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #4CAF50; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
                .content { background-color: #f9f9f9; padding: 20px; }
                .footer { background-color: #f0f0f0; padding: 10px; text-align: center; font-size: 12px; border-radius: 0 0 5px 5px; }
                .button { display: inline-block; background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-top: 10px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>' . APP_NAME . '</h1>
                </div>
                <div class="content">
                    <p>Hello ' . $name . ',</p>
        ';
        
        switch ($type) {
            case 'verification':
                $html .= '
                    <p>Thank you for signing up! Please verify your email address to activate your account.</p>
                    <a href="' . $data['link'] . '" class="button">Verify Email</a>
                    <p style="font-size: 12px; color: #666;">This link will expire in ' . $data['expiry'] . ' hours.</p>
                ';
                break;
                
            case 'password-reset':
                $html .= '
                    <p>You requested a password reset. Click the button below to reset your password.</p>
                    <a href="' . $data['link'] . '" class="button">Reset Password</a>
                    <p style="font-size: 12px; color: #666;">This link will expire in ' . $data['expiry'] . ' hour(s).</p>
                ';
                break;
                
            case 'welcome':
                $html .= '
                    <p>Welcome to ' . APP_NAME . '! Your account has been successfully created.</p>
                    <p>You can now log in and start using our services.</p>
                    <a href="' . $data['app_url'] . '/login" class="button">Go to Login</a>
                ';
                break;
                
            default:
                $html .= '<p>Thank you for using ' . APP_NAME . '!</p>';
        }
        
        $html .= '
                </div>
                <div class="footer">
                    <p>&copy; ' . date('Y') . ' ' . APP_NAME . '. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ';
        
        return $html;
    }

    /**
     * Send custom email
     * 
     * @param string $to
     * @param string $subject
     * @param string $body
     * @param string $toName
     * @return bool
     */
    public function send($to, $subject, $body, $toName = '') {
        try {
            $this->mail->clearAddresses();
            $this->mail->setFrom($this->from_email, $this->from_name);
            $this->mail->addAddress($to, $toName);
            $this->mail->Subject = $subject;
            $this->mail->isHTML(true);
            $this->mail->Body = $body;
            
            return $this->mail->send();
        } catch (Exception $e) {
            error_log("Email Send Error: " . $e->getMessage());
            return false;
        }
    }
}
