<?php
/**
 * Email Utility for sending product notifications
 * Uses PHPMailer if available, falls back to PHP mail()
 */

class EmailNotifier {
    private $fromEmail = 'kingpython1431@gmail.com';
    private $fromName = 'E-Commerce Shop - New Product Alert';
    private $sendgridApiKey = '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';
    private $smtpConfig = null;
    
    public function __construct() {
        // SMTP configuration for Gmail (backup)
        $this->smtpConfig = [
            'host' => 'smtp.gmail.com',
            'port' => 587,
            'username' => 'kingpython1431@gmail.com',
            'password' => 'needforspeed'
        ];
    }
    
    /**
     * Send new product notification email
     * @param string $toEmail - Recipient email
     * @param array $product - Product details
     * @return bool - Success/failure
     */
    public function sendNewProductNotification($toEmail, $product) {
        if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        
        $subject = "🆕 New Item Added: {$product['name']} - BUY LK";
        
        $htmlBody = $this->generateProductEmailHTML($product);
        $plainTextBody = $this->generateProductEmailPlainText($product);
        
        return $this->sendEmail($toEmail, $subject, $htmlBody, $plainTextBody);
    }
    
    /**
     * Send batch email notifications to multiple subscribers
     * @param array $emails - Array of email addresses
     * @param array $product - Product details
     * @return array - Results array with success count
     */
    public function sendBatchNotifications($emails, $product) {
        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => []
        ];
        
        foreach ($emails as $email) {
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                if ($this->sendNewProductNotification($email, $product)) {
                    $results['success']++;
                } else {
                    $results['failed']++;
                    $results['errors'][] = "Failed to send to: $email";
                }
            } else {
                $results['failed']++;
                $results['errors'][] = "Invalid email: $email";
            }
        }
        
        return $results;
    }
    
    /**
     * Generate HTML email body for product notification
     */
    private function generateProductEmailHTML($product) {
        $productName = htmlspecialchars($product['name']);
        $productPrice = htmlspecialchars($product['price']);
        $productCategory = htmlspecialchars($product['category']);
        $productDescription = htmlspecialchars($product['description']);
        $productImage = htmlspecialchars($product['image'] ?? 'https://via.placeholder.com/300x200?text=New+Product');
        $productTag = htmlspecialchars($product['tag'] ?? 'New');
        
        $shopUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/E%20commerce/New-folder/front/shop.html';
        
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Product Alert - BUY LK</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #FF8C00;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #FF8C00;
            margin: 0;
            font-size: 28px;
        }
        .header p {
            color: #666;
            margin: 5px 0 0 0;
            font-size: 14px;
        }
        .product-section {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #FF8C00;
        }
        .product-image {
            text-align: center;
            margin-bottom: 20px;
        }
        .product-image img {
            max-width: 100%;
            height: auto;
            border-radius: 6px;
            max-height: 250px;
        }
        .product-details {
            margin: 20px 0;
        }
        .product-name {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        .product-tag {
            display: inline-block;
            background-color: #FF8C00;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .product-category {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
        }
        .product-description {
            color: #555;
            font-size: 14px;
            margin-bottom: 15px;
            line-height: 1.6;
        }
        .product-price {
            font-size: 22px;
            color: #FF8C00;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .cta-button {
            display: inline-block;
            background-color: #FF8C00;
            color: white;
            text-decoration: none;
            padding: 12px 30px;
            border-radius: 6px;
            font-weight: bold;
            font-size: 16px;
            text-align: center;
            width: 100%;
            box-sizing: border-box;
        }
        .cta-button:hover {
            background-color: #E67E00;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            font-size: 12px;
            color: #999;
        }
        .unsubscribe {
            margin-top: 15px;
            font-size: 11px;
        }
        .unsubscribe a {
            color: #FF8C00;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🆕 New Product Alert!</h1>
            <p>A new item has been added to BUY LK</p>
        </div>
        
        <div class="product-section">
            <div class="product-image">
                <img src="$productImage" alt="$productName">
            </div>
            
            <div class="product-details">
                <span class="product-tag">$productTag</span>
                
                <div class="product-name">$productName</div>
                
                <div class="product-category">Category: $productCategory</div>
                
                <div class="product-description">$productDescription</div>
                
                <div class="product-price">LKR $productPrice</div>
                
                <a href="$shopUrl" class="cta-button">View Product in Shop</a>
            </div>
        </div>
        
        <div class="footer">
            <p>Thanks for staying updated with BUY LK!</p>
            <p>We regularly add new products to our shop. This is one of them.</p>
            <div class="unsubscribe">
                <p>If you no longer wish to receive these notifications, you can unsubscribe anytime.</p>
            </div>
        </div>
    </div>
</body>
</html>
HTML;
    }
    
    /**
     * Generate plain text email body for product notification
     */
    private function generateProductEmailPlainText($product) {
        $shopUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/E%20commerce/New-folder/front/shop.html';
        
        return <<<TEXT
NEW PRODUCT ALERT - BUY LK

We're excited to announce a new item in our shop!

PRODUCT DETAILS:
================
Name: {$product['name']}
Category: {$product['category']}
Price: LKR {$product['price']}
Tag: {$product['tag']}

Description: {$product['description']}

View the product and shop now at: $shopUrl

Thanks for being a valued subscriber!
BUY LK Team

---
If you no longer wish to receive these emails, you can unsubscribe anytime.
TEXT;
    }
    
    /**
     * Send email using Sendgrid API (primary), Gmail SMTP (secondary), or PHP mail (fallback)
     */
    private function sendEmail($toEmail, $subject, $htmlBody, $plainTextBody) {
        try {
            // Try Sendgrid API first (primary method)
            if (!empty($this->sendgridApiKey)) {
                $result = $this->sendViaSendgrid($toEmail, $subject, $htmlBody);
                if ($result) {
                    return true;
                }
                // If Sendgrid fails, fall through to try other methods
            }
            
            // Try Gmail SMTP (secondary method)
            if (!empty($this->smtpConfig['username']) && !empty($this->smtpConfig['password'])) {
                return $this->sendViaGmailSMTP($toEmail, $subject, $htmlBody);
            }
            
            // Fallback to PHP mail()
            return $this->sendViaPhpMail($toEmail, $subject, $htmlBody, $plainTextBody);
        } catch (Exception $e) {
            // Log the error but don't throw - email failures shouldn't block product creation
            error_log("Email send error for $toEmail: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send email using Gmail SMTP
     */
    private function sendViaGmailSMTP($toEmail, $subject, $htmlBody) {
        try {
            require_once __DIR__ . '/GmailSMTP.php';
            
            $smtp = new GmailSMTP(
                $this->smtpConfig['username'],
                $this->smtpConfig['password']
            );
            
            return $smtp->sendEmail($toEmail, $subject, $htmlBody, $this->fromEmail, $this->fromName);
        } catch (Exception $e) {
            error_log("Gmail SMTP error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send email using Sendgrid API
     */
    private function sendViaSendgrid($toEmail, $subject, $htmlBody) {
        try {
            require_once __DIR__ . '/SendgridAPI.php';
            
            $sendgrid = new SendgridAPI($this->sendgridApiKey);
            $result = $sendgrid->sendEmail($toEmail, $subject, $htmlBody, $this->fromEmail, $this->fromName);
            
            if ($result) {
                error_log("Sendgrid: Email sent successfully to $toEmail");
            } else {
                error_log("Sendgrid: Failed to send email to $toEmail");
            }
            
            return $result;
        } catch (Exception $e) {
            error_log("Sendgrid API error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send email using PHPMailer (requires installation)
     */
    private function sendViaPhpMailer($toEmail, $subject, $htmlBody, $plainTextBody) {
        require_once __DIR__ . '/../../vendor/autoload.php';
        
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        
        try {
            // Configure SMTP if credentials provided
            if (!empty($this->smtpConfig['username'])) {
                $mail->isSMTP();
                $mail->Host = $this->smtpConfig['host'];
                $mail->SMTPAuth = true;
                $mail->Username = $this->smtpConfig['username'];
                $mail->Password = $this->smtpConfig['password'];
                $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = $this->smtpConfig['port'];
            }
            
            $mail->setFrom($this->fromEmail, $this->fromName);
            $mail->addAddress($toEmail);
            $mail->Subject = $subject;
            $mail->Body = $htmlBody;
            $mail->AltBody = $plainTextBody;
            $mail->isHTML(true);
            
            return $mail->send();
        } catch (Exception $e) {
            error_log("PHPMailer error: " . $mail->ErrorInfo);
            return false;
        }
    }
    
    /**
     * Send email using PHP mail() function
     * Suppresses warnings since mail server may not be configured
     */
    private function sendViaPhpMail($toEmail, $subject, $htmlBody, $plainTextBody) {
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: {$this->fromName} <{$this->fromEmail}>\r\n";
        $headers .= "Reply-To: {$this->fromEmail}\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
        
        // Encode subject
        $subject = "=?UTF-8?B?" . base64_encode($subject) . "?=";
        
        // Use @ to suppress mail server warnings (product creation shouldn't fail due to mail config)
        $result = @mail($toEmail, $subject, $htmlBody, $headers);
        
        if (!$result) {
            // Log if mail() failed but don't throw exception
            error_log("mail() failed for $toEmail - mail server may not be configured");
        }
        
        return $result;
    }
}

?>
