<?php
// Test Gmail SMTP connection
require_once 'config/GmailSMTP.php';

$smtp = new GmailSMTP('kingpython1431@gmail.com', 'needforspeed');

echo "Testing Gmail SMTP Connection...\n\n";

$result = $smtp->sendEmail(
    'kingpython1431@gmail.com',
    'Test Email from Shop',
    '<h1>Test Email</h1><p>If you received this, Gmail SMTP is working!</p>',
    'kingpython1431@gmail.com',
    'E-Commerce Shop'
);

if ($result) {
    echo "✅ SUCCESS: Email sent successfully!\n";
} else {
    echo "❌ FAILED: Email could not be sent.\n";
    echo "Check PHP error log for details.\n";
}

echo "\nCheck your Gmail inbox for the test email.\n";
?>
