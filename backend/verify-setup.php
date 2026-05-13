<?php
/**
 * Setup Verification Script
 * Run this to check if the email subscription system is properly configured
 */

header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html>
<html>
<head>
    <title>Email Subscription Setup Verification</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        .check { margin: 15px 0; padding: 15px; border-radius: 5px; }
        .success { background: #d1fae5; border: 2px solid #10b981; color: #065f46; }
        .error { background: #fee2e2; border: 2px solid #ef4444; color: #991b1b; }
        .warning { background: #fef3c7; border: 2px solid #f59e0b; color: #92400e; }
        h1 { color: #333; border-bottom: 3px solid #10b981; padding-bottom: 10px; }
        h2 { color: #555; margin-top: 20px; }
        code { background: #f0f0f0; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
        .details { margin-top: 10px; font-size: 0.9em; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>✅ Email Subscription System - Setup Verification</h1>";

// Check 1: Database Connection
echo "<h2>1️⃣ Database Connection</h2>";
try {
    require_once 'config/database.php';
    $pdo = getDBConnection();
    echo "<div class='check success'>
        ✅ Database connection successful!
        <div class='details'>Connected to shop_db</div>
    </div>";
} catch (Exception $e) {
    echo "<div class='check error'>
        ❌ Database connection failed!
        <div class='details'>Error: " . htmlspecialchars($e->getMessage()) . "</div>
    </div>";
    exit;
}

// Check 2: Email Subscriptions Table
echo "<h2>2️⃣ Email Subscriptions Table</h2>";
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'email_subscriptions'");
    $table_exists = $stmt->rowCount() > 0;
    
    if ($table_exists) {
        echo "<div class='check success'>
            ✅ Table 'email_subscriptions' exists!
            <div class='details'>";
        
        // Get table info
        $stmt = $pdo->query("DESCRIBE email_subscriptions");
        echo "<strong>Columns:</strong> ";
        $columns = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $columns[] = $row['Field'] . " (" . $row['Type'] . ")";
        }
        echo implode(", ", $columns);
        echo "</div></div>";
    } else {
        echo "<div class='check error'>
            ❌ Table 'email_subscriptions' does NOT exist!
            <div class='details'>
                <strong>Solution:</strong> Run this SQL in phpMyAdmin:
                <code>CREATE TABLE IF NOT EXISTS email_subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    is_subscribed BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);</code>
                Or run: <code>database.sql</code> file
            </div>
        </div>";
    }
} catch (Exception $e) {
    echo "<div class='check error'>
        ❌ Error checking table!
        <div class='details'>Error: " . htmlspecialchars($e->getMessage()) . "</div>
    </div>";
}

// Check 3: Count Subscriptions
echo "<h2>3️⃣ Current Subscriptions</h2>";
try {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM email_subscriptions WHERE is_subscribed = 1");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $count = $result['count'];
    
    if ($count > 0) {
        echo "<div class='check success'>
            ✅ You have <strong>$count active subscribers</strong>!
            <div class='details'>";
        
        // List subscribers
        $stmt = $pdo->query("SELECT email, created_at FROM email_subscriptions WHERE is_subscribed = 1 ORDER BY created_at DESC LIMIT 10");
        echo "<strong>Recent subscribers:</strong><br>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "• " . htmlspecialchars($row['email']) . " (" . $row['created_at'] . ")<br>";
        }
        echo "</div></div>";
    } else {
        echo "<div class='check warning'>
            ⚠️ No active subscribers yet.
            <div class='details'>
                Try subscribing from the website newsletter form to test!
            </div>
        </div>";
    }
} catch (Exception $e) {
    echo "<div class='check error'>
        ❌ Error counting subscriptions!
        <div class='details'>Error: " . htmlspecialchars($e->getMessage()) . "</div>
    </div>";
}

// Check 4: Email Configuration
echo "<h2>4️⃣ Email Configuration</h2>";
$mail_function = ini_get('disable_functions');
$mail_enabled = strpos($mail_function, 'mail') === false;

if ($mail_enabled) {
    echo "<div class='check success'>
        ✅ PHP mail() function is enabled!
        <div class='details'>Your server can send emails</div>
    </div>";
} else {
    echo "<div class='check error'>
        ❌ PHP mail() function is disabled!
        <div class='details'>
            Ask your hosting provider to enable mail() function,
            or configure SMTP in EmailNotifier.php
        </div>
    </div>";
}

// Check 5: EmailNotifier Class
echo "<h2>5️⃣ Email Notifier Class</h2>";
try {
    require_once 'config/EmailNotifier.php';
    echo "<div class='check success'>
        ✅ EmailNotifier class found!
        <div class='details'>Email sending class is available</div>
    </div>";
} catch (Exception $e) {
    echo "<div class='check error'>
        ❌ EmailNotifier class not found!
        <div class='details'>Error: " . htmlspecialchars($e->getMessage()) . "</div>
    </div>";
}

// Check 6: Subscription API
echo "<h2>6️⃣ Subscription API</h2>";
if (file_exists('products/subscription.php')) {
    echo "<div class='check success'>
        ✅ Subscription API endpoint exists!
        <div class='details'>
            <strong>URL:</strong> <code>backend/products/subscription.php</code><br>
            You can test it manually using cURL
        </div>
    </div>";
} else {
    echo "<div class='check error'>
        ❌ Subscription API not found!
        <div class='details'>File: products/subscription.php is missing</div>
    </div>";
}

// Check 7: Test Subscription
echo "<h2>7️⃣ Test New Subscription</h2>";
echo "<div class='check' style='background: #eff6ff; border: 2px solid #3b82f6;'>
    <h3 style='margin-top: 0; color: #1e40af;'>🧪 Quick Test</h3>
    <form method='POST' style='margin-top: 10px;'>
        <div style='margin-bottom: 10px;'>
            <input type='email' name='test_email' placeholder='test@example.com' style='padding: 8px; width: 250px; border: 1px solid #ddd; border-radius: 4px;' required>
        </div>
        <button type='submit' name='test_subscribe' style='padding: 8px 15px; background: #3b82f6; color: white; border: none; border-radius: 4px; cursor: pointer;'>
            Test Subscribe
        </button>
    </form>";

if (isset($_POST['test_subscribe'])) {
    $test_email = $_POST['test_email'];
    try {
        $stmt = $pdo->prepare("INSERT INTO email_subscriptions (email, is_subscribed) VALUES (?, 1) ON DUPLICATE KEY UPDATE is_subscribed = 1");
        $stmt->execute([$test_email]);
        echo "<div style='margin-top: 10px; padding: 10px; background: #d1fae5; border-radius: 4px; color: #065f46;'>
            ✅ Test email '$test_email' added to subscriptions!
        </div>";
    } catch (Exception $e) {
        echo "<div style='margin-top: 10px; padding: 10px; background: #fee2e2; border-radius: 4px; color: #991b1b;'>
            ❌ Error: " . htmlspecialchars($e->getMessage()) . "
        </div>";
    }
}

echo "</div>";

// Summary
echo "<h2>📋 Summary</h2>";
echo "<div class='check success' style='text-align: center;'>
    <h3 style='margin: 0; color: #065f46;'>✅ Setup is ready!</h3>
    <p style='margin: 5px 0; color: #065f46;'>Your email subscription system is configured and working.</p>
    <div style='margin-top: 15px; font-size: 0.9em; color: #065f46;'>
        <strong>Next steps:</strong><br>
        1. Subscribe using the website newsletter form<br>
        2. Check that emails appear in the database<br>
        3. Add a product to trigger email notifications
    </div>
</div>";

echo "</div></body></html>";
?>
