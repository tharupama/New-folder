<?php
// Simple installer to check and create the reviews table
header('Content-Type: text/html; charset=utf-8');

require_once 'config/database.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Review System Installer</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; max-width: 800px; margin: 0 auto; }
        .success { color: green; padding: 10px; background: #d4edda; border: 1px solid #c3e6cb; margin: 10px 0; }
        .error { color: red; padding: 10px; background: #f8d7da; border: 1px solid #f5c6cb; margin: 10px 0; }
        .info { color: blue; padding: 10px; background: #d1ecf1; border: 1px solid #bee5eb; margin: 10px 0; }
        pre { background: #f5f5f5; padding: 10px; overflow-x: auto; }
    </style>
</head>
<body>
<h1>🛠️ Review System Installer</h1>";

try {
    $database = new Database();
    $db = $database->getConnection();
    
    echo "<div class='success'>✓ Database connection successful!</div>";
    
    // Check if table exists
    $query = "SHOW TABLES LIKE 'product_reviews'";
    $stmt = $db->query($query);
    $tableExists = $stmt->rowCount() > 0;
    
    if ($tableExists) {
        echo "<div class='info'>ℹ️ Table 'product_reviews' already exists</div>";
        
        // Count reviews
        $countStmt = $db->query("SELECT COUNT(*) as count FROM product_reviews");
        $count = $countStmt->fetch(PDO::FETCH_ASSOC)['count'];
        echo "<div class='info'>Current reviews in database: {$count}</div>";
        
        // Show structure
        $structStmt = $db->query("DESCRIBE product_reviews");
        $structure = $structStmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<h3>Table Structure:</h3><pre>";
        foreach ($structure as $col) {
            echo "{$col['Field']} - {$col['Type']}\n";
        }
        echo "</pre>";
        
    } else {
        echo "<div class='error'>✗ Table 'product_reviews' does NOT exist</div>";
        echo "<div class='info'>Creating table...</div>";
        
        // Create table
        $createSQL = "CREATE TABLE IF NOT EXISTS product_reviews (
            id INT AUTO_INCREMENT PRIMARY KEY,
            product_id INT NOT NULL,
            user_name VARCHAR(100) NOT NULL,
            user_id INT DEFAULT NULL,
            rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
            comment TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        )";
        
        $db->exec($createSQL);
        echo "<div class='success'>✓ Table created successfully!</div>";
        
        // Add sample data
        echo "<div class='info'>Adding sample reviews...</div>";
        $insertSQL = "INSERT INTO product_reviews (product_id, user_name, rating, comment) VALUES
            (1, 'John Doe', 5, 'Excellent sound quality! Best speaker I have ever owned.'),
            (1, 'Sarah Smith', 4, 'Great product but a bit pricey. Worth it though!'),
            (2, 'Mike Johnson', 5, 'This air purifier changed my life. Highly recommend!'),
            (3, 'Emily Brown', 4, 'Good fitness band, accurate tracking and long battery life.'),
            (5, 'David Wilson', 5, 'Most comfortable sneakers ever! Love the eco-friendly design.')";
        
        $db->exec($insertSQL);
        echo "<div class='success'>✓ Sample reviews added!</div>";
        
        // Verify
        $countStmt = $db->query("SELECT COUNT(*) as count FROM product_reviews");
        $count = $countStmt->fetch(PDO::FETCH_ASSOC)['count'];
        echo "<div class='success'>✓ Total reviews: {$count}</div>";
    }
    
    echo "<h3>✅ Installation Complete!</h3>";
    echo "<p><a href='error-checker.html' style='padding: 10px 20px; background: #6d5dfc; color: white; text-decoration: none; border-radius: 5px;'>Test the System</a></p>";
    
} catch (PDOException $e) {
    echo "<div class='error'>✗ Database Error: " . $e->getMessage() . "</div>";
} catch (Exception $e) {
    echo "<div class='error'>✗ Error: " . $e->getMessage() . "</div>";
}

echo "</body></html>";
?>
