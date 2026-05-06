# Troubleshooting: Reviews Not Submitting to Database

## Quick Diagnostic Steps

### 1. **Check if Database Table Exists**
Open phpMyAdmin (http://localhost/phpmyadmin) and verify:
- Database `shop_db` exists
- Table `product_reviews` exists with columns: id, product_id, user_name, user_id, rating, comment, created_at

**If table is missing, run this SQL:**
```sql
USE shop_db;

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
```

### 2. **Test the API Directly**
1. Open: `http://localhost/New%20folder/test-reviews.html`
2. Check browser console (F12) for errors
3. Look for API responses

### 3. **Check PHP Error Logs**
Location: `C:\wamp64\logs\php_error.log`
Look for recent errors related to reviews

### 4. **Verify WAMP is Running**
- Apache should be green
- MySQL should be green
- Click WAMP icon → Check services

### 5. **Test API Endpoints Manually**

**Test GET Reviews:**
Open in browser: `http://localhost/New%20folder/backend/reviews/get.php?product_id=1`

Expected response:
```json
{
  "success": true,
  "data": {
    "reviews": [...],
    "average_rating": 4.5,
    "total_reviews": 5
  }
}
```

**Test POST Review:**
Use Postman or curl:
```bash
curl -X POST http://localhost/New%20folder/backend/reviews/add.php \
  -H "Content-Type: application/json" \
  -d '{"product_id":1,"user_name":"Test User","rating":5,"comment":"This is a test review message"}'
```

### 6. **Check Browser Console**
1. Open shop.html or index.html
2. Click on a product
3. Fill review form and submit
4. Open browser console (F12)
5. Look for:
   - "Submit review called"
   - "Review data: {...}"
   - "API Response: {...}"
   - Any error messages

### 7. **Common Issues & Fixes**

#### Issue: "Table doesn't exist"
**Fix:** Run the SQL create table command in phpMyAdmin

#### Issue: "CORS error"
**Fix:** Already handled with headers in PHP files, but verify:
```php
header('Access-Control-Allow-Origin: *');
```

#### Issue: "Database connection failed"
**Fix:** Check `backend/config/database.php`:
```php
private $host = "localhost";
private $db_name = "shop_db";
private $username = "root";
private $password = "";
```

#### Issue: "Required fields missing"
**Fix:** Check console to see what data is being sent

#### Issue: "Network error"
**Fix:** Verify WAMP is running and URLs are correct

### 8. **Verify File Paths**
Check that these files exist:
- `backend/reviews/get.php`
- `backend/reviews/add.php`
- `front/product-modal.js`
- `front/api.js`

### 9. **Check API URL Configuration**
In `front/api.js`, verify:
```javascript
const API_BASE_URL = 'http://localhost/New%20folder/backend';
```

### 10. **Database Connection Test**
Create `backend/test-db.php`:
```php
<?php
require_once 'config/database.php';
try {
    $database = new Database();
    $db = $database->getConnection();
    echo "Database connection successful!";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
```
Access: `http://localhost/New%20folder/backend/test-db.php`

## Step-by-Step Debug Process

1. **Verify database table exists** (Step 1)
2. **Test database connection** (Step 10)
3. **Test GET API** (Step 5)
4. **Test POST API** (Step 5)
5. **Check browser console** (Step 6)
6. **Check PHP error logs** (Step 3)

## Still Not Working?

**Check the following in order:**

1. Is WAMP running? (green icon)
2. Can you access phpMyAdmin?
3. Does shop_db database exist?
4. Does product_reviews table exist?
5. Can you access the test page?
6. Do you see console logs when submitting?
7. What error appears in PHP logs?

## Expected Console Output (Working)
```
Submit review called
Review data: {userName: "John", comment: "Great product!", rating: 5, productId: 1}
Calling addProductReview API...
API Response: {success: true, data: {...}}
Reloading reviews...
Found X reviews for product 1
Reloading products...
```

## Contact Points
If still having issues, provide:
1. Browser console output (full)
2. PHP error log (last 50 lines)
3. Screenshot of phpMyAdmin showing tables
4. Response from test-reviews.html
