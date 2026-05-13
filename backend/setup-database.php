<?php
/**
 * Database Setup & Verification Script
 * Run this FIRST to set up the email subscription system
 */

header('Content-Type: text/html; charset=utf-8');

// Security check
$isPost = $_SERVER['REQUEST_METHOD'] === 'POST';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Email Subscription System - Setup</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container { 
            max-width: 700px;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        h1 { 
            color: #333;
            margin-bottom: 20px;
            border-bottom: 3px solid #667eea;
            padding-bottom: 15px;
            font-size: 1.8em;
        }
        .section {
            margin-bottom: 25px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }
        .section h2 {
            color: #333;
            font-size: 1.2em;
            margin-bottom: 15px;
        }
        .status {
            padding: 15px;
            margin: 15px 0;
            border-radius: 6px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1em;
        }
        .status.success {
            background: #d1fae5;
            border: 2px solid #10b981;
            color: #065f46;
        }
        .status.error {
            background: #fee2e2;
            border: 2px solid #ef4444;
            color: #991b1b;
        }
        .status.warning {
            background: #fef3c7;
            border: 2px solid #f59e0b;
            color: #92400e;
        }
        .status-icon { font-size: 1.3em; }
        button {
            background: #667eea;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1em;
            transition: background 0.3s;
        }
        button:hover { background: #764ba2; }
        button:disabled {
            background: #999;
            cursor: not-allowed;
        }
        .code-block {
            background: #2d3748;
            color: #e2e8f0;
            padding: 15px;
            border-radius: 6px;
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
            overflow-x: auto;
            margin: 10px 0;
        }
        .email-test {
            margin-top: 15px;
            padding: 15px;
            background: #eff6ff;
            border-radius: 6px;
            border: 1px solid #3b82f6;
        }
        .email-test input {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            width: 100%;
            margin: 10px 0;
            font-size: 0.9em;
        }
        .test-results {
            margin-top: 15px;
            padding: 15px;
            background: white;
            border-radius: 6px;
            border: 1px solid #e5e7eb;
        }
        .subscriber-list {
            max-height: 300px;
            overflow-y: auto;
        }
        .subscriber-list li {
            padding: 8px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 0.9em;
        }
        .subscriber-list li:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Email Subscription System Setup</h1>

        <?php
        // Try to connect to database
        require_once 'config/database.php';
        $dbConnected = false;
        $tableExists = false;
        $subscriberCount = 0;
        $errorMsg = '';
        
        try {
            $pdo = getDBConnection();
            $dbConnected = true;
            
            // Check if table exists
            $tableCheck = $pdo->query("SHOW TABLES LIKE 'email_subscriptions'");
            $tableExists = $tableCheck->rowCount() > 0;
            
            if ($tableExists) {
                $countStmt = $pdo->query("SELECT COUNT(*) as count FROM email_subscriptions WHERE is_subscribed = 1");
                $subscriberCount = $countStmt->fetch(PDO::FETCH_ASSOC)['count'];
            }
        } catch (Exception $e) {
            $errorMsg = $e->getMessage();
        }
        
        // Step 1: Database Connection
        echo '<div class="section">';
        echo '<h2>Step 1: Database Connection</h2>';
        
        if ($dbConnected) {
            echo '<div class="status success">';
            echo '<span class="status-icon">✅</span>';
            echo '<span>Database connected successfully!</span>';
            echo '</div>';
        } else {
            echo '<div class="status error">';
            echo '<span class="status-icon">❌</span>';
            echo '<span>Database connection failed: ' . htmlspecialchars($errorMsg) . '</span>';
            echo '</div>';
        }
        echo '</div>';
        
        if (!$dbConnected) {
            echo '<div class="section" style="background: #fee2e2; border-left-color: #ef4444;">';
            echo '<h2>⚠️ Cannot continue</h2>';
            echo '<p>Fix the database connection error first. Check:</p>';
            echo '<ul style="margin: 15px 0 0 20px; line-height: 1.8;">';
            echo '<li>PHP version 5.7 or higher</li>';
            echo '<li>MySQL/MariaDB is running</li>';
            echo '<li>Database user "root" exists</li>';
            echo '<li>Database "shop_db" exists</li>';
            echo '<li>Check config/database.php settings</li>';
            echo '</ul>';
            echo '</div>';
        } else {
            // Step 2: Email Subscriptions Table
            echo '<div class="section">';
            echo '<h2>Step 2: Email Subscriptions Table</h2>';
            
            if ($tableExists) {
                echo '<div class="status success">';
                echo '<span class="status-icon">✅</span>';
                echo '<span>Table "email_subscriptions" exists!</span>';
                echo '</div>';
            } else {
                echo '<div class="status error">';
                echo '<span class="status-icon">❌</span>';
                echo '<span>Table does NOT exist. Click button below to create it.</span>';
                echo '</div>';
                
                echo '<form method="POST">';
                echo '<input type="hidden" name="action" value="create_table">';
                echo '<button type="submit" style="margin-top: 15px;">🔧 Create Table Now</button>';
                echo '</form>';
            }
            echo '</div>';
            
            // Step 3: Current Subscriptions
            echo '<div class="section">';
            echo '<h2>Step 3: Current Subscriptions</h2>';
            
            if ($tableExists) {
                echo '<div class="status ' . ($subscriberCount > 0 ? 'success' : 'warning') . '">';
                echo '<span class="status-icon">' . ($subscriberCount > 0 ? '✅' : '⚠️') . '</span>';
                echo '<span><strong>' . $subscriberCount . ' active subscriber(s)</strong></span>';
                echo '</div>';
                
                if ($subscriberCount > 0) {
                    echo '<div class="subscriber-list">';
                    echo '<h3 style="margin: 10px 0; color: #333;">Recent Subscribers:</h3>';
                    echo '<ul style="list-style: none; padding: 0;">';
                    
                    $stmt = $pdo->query("SELECT email, created_at FROM email_subscriptions WHERE is_subscribed = 1 ORDER BY created_at DESC LIMIT 10");
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo '<li>📧 ' . htmlspecialchars($row['email']) . ' - ' . $row['created_at'] . '</li>';
                    }
                    echo '</ul>';
                    echo '</div>';
                }
            }
            echo '</div>';
            
            // Step 4: Test Subscription
            if ($tableExists) {
                echo '<div class="section">';
                echo '<h2>Step 4: 🧪 Test Subscription</h2>';
                echo '<p style="margin-bottom: 15px;">Try adding a test email to verify the system works:</p>';
                
                echo '<form method="POST" class="email-test">';
                echo '<input type="hidden" name="action" value="test_subscribe">';
                echo '<input type="email" name="test_email" placeholder="test@example.com" required>';
                echo '<button type="submit">Test Subscribe</button>';
                echo '</form>';
                
                echo '</div>';
            }
            
            // Step 5: Manual Table Creation (alternative)
            if (!$tableExists) {
                echo '<div class="section" style="background: #f0f9ff; border-left-color: #3b82f6;">';
                echo '<h2>📋 Alternative: Manual SQL</h2>';
                echo '<p style="margin-bottom: 10px;">If the automatic button doesn\'t work, run this SQL in phpMyAdmin:</p>';
                echo '<div class="code-block">CREATE TABLE IF NOT EXISTS email_subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    is_subscribed BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);</div>';
                echo '</div>';
            }
        }
        
        // Process actions
        if ($isPost && $dbConnected) {
            $action = $_POST['action'] ?? '';
            
            if ($action === 'create_table') {
                try {
                    $sql = "CREATE TABLE IF NOT EXISTS email_subscriptions (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        email VARCHAR(100) NOT NULL UNIQUE,
                        is_subscribed BOOLEAN DEFAULT 1,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                    )";
                    $pdo->exec($sql);
                    echo '<div class="section" style="background: #d1fae5; border-left-color: #10b981;">';
                    echo '<div class="status success">';
                    echo '<span class="status-icon">✅</span>';
                    echo '<span>Table created successfully! Refresh the page to see the changes.</span>';
                    echo '</div>';
                    echo '</div>';
                } catch (Exception $e) {
                    echo '<div class="section" style="background: #fee2e2; border-left-color: #ef4444;">';
                    echo '<div class="status error">';
                    echo '<span class="status-icon">❌</span>';
                    echo '<span>Error: ' . htmlspecialchars($e->getMessage()) . '</span>';
                    echo '</div>';
                    echo '</div>';
                }
            }
            
            if ($action === 'test_subscribe') {
                $testEmail = $_POST['test_email'] ?? '';
                if (filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
                    try {
                        $stmt = $pdo->prepare("INSERT INTO email_subscriptions (email, is_subscribed) VALUES (?, 1) ON DUPLICATE KEY UPDATE is_subscribed = 1, updated_at = NOW()");
                        $stmt->execute([$testEmail]);
                        echo '<div class="section" style="background: #d1fae5; border-left-color: #10b981;">';
                        echo '<div class="status success">';
                        echo '<span class="status-icon">✅</span>';
                        echo '<span>Test email added! Refresh to see it in the list above.</span>';
                        echo '</div>';
                        echo '</div>';
                    } catch (Exception $e) {
                        echo '<div class="section" style="background: #fee2e2; border-left-color: #ef4444;">';
                        echo '<div class="status error">';
                        echo '<span class="status-icon">❌</span>';
                        echo '<span>Error: ' . htmlspecialchars($e->getMessage()) . '</span>';
                        echo '</div>';
                        echo '</div>';
                    }
                }
            }
        }
        
        ?>

        <!-- Final Steps -->
        <div class="section" style="background: #f0fdf4; border-left-color: #10b981;">
            <h2>✅ Next Steps</h2>
            <ol style="line-height: 1.8; margin-left: 20px; margin-top: 10px;">
                <li><strong>Verify Setup:</strong> This page should show ✅ for all checks</li>
                <li><strong>Test on Website:</strong> Go to index.html, scroll to footer, enter email in Newsletter section</li>
                <li><strong>Check Database:</strong> Refresh this page to see your email in the subscriber list</li>
                <li><strong>Add Product:</strong> Use cURL or API to add a product and trigger email notifications</li>
                <li><strong>Check Email:</strong> Verify that notification emails are being sent</li>
            </ol>
        </div>

    </div>
</body>
</html>
