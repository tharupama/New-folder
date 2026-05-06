-- Run this SQL in phpMyAdmin to create the reviews table
-- Select shop_db database first, then run this entire script

USE shop_db;

-- Create product_reviews table if it doesn't exist
CREATE TABLE IF NOT EXISTS product_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_name VARCHAR(100) NOT NULL,
    user_id INT DEFAULT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Add sample reviews for testing
INSERT INTO product_reviews (product_id, user_name, rating, comment) VALUES
(1, 'John Doe', 5, 'Excellent sound quality! Best speaker I have ever owned.'),
(1, 'Sarah Smith', 4, 'Great product but a bit pricey. Worth it though!'),
(2, 'Mike Johnson', 5, 'This air purifier changed my life. Highly recommend!'),
(3, 'Emily Brown', 4, 'Good fitness band, accurate tracking and long battery life.'),
(5, 'David Wilson', 5, 'Most comfortable sneakers ever! Love the eco-friendly design.');

-- Verify the table was created
SELECT 'Table created successfully!' as Status;

-- Show the structure
DESCRIBE product_reviews;

-- Show the sample data
SELECT * FROM product_reviews;

-- Show count
SELECT COUNT(*) as TotalReviews FROM product_reviews;
