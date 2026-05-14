# Email Notification System - Quick Testing Guide

## ⚡ 5-Minute Quick Start

### Step 1: Update Database
Run this SQL in phpMyAdmin or MySQL client:

```sql
-- If not done already, run database.sql or add this table:
CREATE TABLE IF NOT EXISTS email_subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    is_subscribed BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### Step 2: Verify Files Exist

Check these files in your project:
```
✅ backend/products/subscription.php
✅ backend/config/EmailNotifier.php
✅ Updated: backend/database.sql
✅ Updated: backend/products/list.php
✅ Updated: front/script.js
✅ Updated: front/index.html (newsletter form)
```

### Step 3: Test Subscription (Browser)

1. Open `index.html` in your browser
2. Scroll to **"Stay in the loop"** section (footer)
3. Enter email: `test@example.com`
4. Click **"Notify me"**
5. ✅ Should see: **"✅ Successfully subscribed!"** message (green)

**Verify in Database:**
```sql
SELECT * FROM email_subscriptions WHERE email = 'test@example.com';
-- Should return: 1 row with is_subscribed = 1
```

### Step 4: Test Email Sending

#### Using cURL (Command Line):

```bash
curl -X POST http://localhost/xampp/htdocs/E\ commerce/New-folder/backend/products/list.php \
  -H "Content-Type: application/json" \
  -d '{
    "adminToken": "test-admin",
    "name": "Test Product",
    "category": "electronics",
    "price": 99.99,
    "tag": "New",
    "image": "https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=500",
    "description": "This is a test product",
    "stock": 50
  }'
```

#### Expected Response:
```json
{
  "success": true,
  "message": "Product added successfully",
  "productId": 10,
  "emailsSent": 1,
  "emailsFailed": 0,
  "totalSubscribers": 1
}
```

✅ **Check your email inbox for the notification!**

---

## 🧪 Detailed Testing

### Test 1: Multiple Subscribers

**Add 3 subscribers:**

```bash
# Subscriber 1
curl -X POST http://localhost/xampp/htdocs/E\ commerce/New-folder/backend/products/subscription.php \
  -H "Content-Type: application/json" \
  -d '{"email": "user1@example.com"}'

# Subscriber 2
curl -X POST http://localhost/xampp/htdocs/E\ commerce/New-folder/backend/products/subscription.php \
  -H "Content-Type: application/json" \
  -d '{"email": "user2@example.com"}'

# Subscriber 3
curl -X POST http://localhost/xampp/htdocs/E\ commerce/New-folder/backend/products/subscription.php \
  -H "Content-Type: application/json" \
  -d '{"email": "user3@example.com"}'
```

**Add product - emails sent to all 3:**

```bash
curl -X POST http://localhost/xampp/htdocs/E\ commerce/New-folder/backend/products/list.php \
  -H "Content-Type: application/json" \
  -d '{
    "adminToken": "test",
    "name": "Wireless Mouse",
    "category": "electronics",
    "price": 49.99,
    "tag": "Hot Deal",
    "image": "https://images.unsplash.com/photo-1527814050087-3793815479db?w=500",
    "description": "High precision wireless mouse",
    "stock": 100
  }'
```

**Expected Response:**
```json
{
  "emailsSent": 3,
  "totalSubscribers": 3,
  ...
}
```

✅ Check emails - all 3 users should receive the product notification

---

### Test 2: Subscription Management

#### Check if email is subscribed:

```bash
curl "http://localhost/xampp/htdocs/E\ commerce/New-folder/backend/products/subscription.php?email=user1@example.com"
```

Response:
```json
{
  "success": true,
  "data": {
    "id": 1,
    "email": "user1@example.com",
    "is_subscribed": true,
    "created_at": "2026-05-11 10:30:00"
  }
}
```

#### Unsubscribe user:

```bash
curl -X PUT http://localhost/xampp/htdocs/E\ commerce/New-folder/backend/products/subscription.php \
  -H "Content-Type: application/json" \
  -d '{"email": "user1@example.com"}'
```

Response:
```json
{
  "success": true,
  "message": "You have been unsubscribed"
}
```

#### Verify unsubscribed:

```bash
curl "http://localhost/xampp/htdocs/E\ commerce/New-folder/backend/products/subscription.php?email=user1@example.com"
```

Response:
```json
{
  "data": {
    "is_subscribed": false
  }
}
```

✅ Unsubscribed users should NOT receive future emails

---

### Test 3: Email Format Validation

#### Invalid email:

```bash
curl -X POST http://localhost/xampp/htdocs/E\ commerce/New-folder/backend/products/subscription.php \
  -H "Content-Type: application/json" \
  -d '{"email": "invalid-email"}'
```

Response:
```json
{
  "success": false,
  "message": "Invalid email format"
}
```

✅ Invalid emails rejected

#### Empty email:

```bash
curl -X POST http://localhost/xampp/htdocs/E\ commerce/New-folder/backend/products/subscription.php \
  -H "Content-Type: application/json" \
  -d '{"email": ""}'
```

Response:
```json
{
  "success": false,
  "message": "Email is required"
}
```

✅ Empty emails rejected

---

### Test 4: Duplicate Subscription

Subscribe same email twice:

```bash
# First subscription
curl -X POST http://localhost/xampp/htdocs/E\ commerce/New-folder/backend/products/subscription.php \
  -H "Content-Type: application/json" \
  -d '{"email": "test@example.com"}'

# Response: 201 Created

# Second subscription (same email)
curl -X POST http://localhost/xampp/htdocs/E\ commerce/New-folder/backend/products/subscription.php \
  -H "Content-Type: application/json" \
  -d '{"email": "test@example.com"}'

# Response: 200 OK with message "already subscribed"
```

✅ Duplicate subscriptions handled gracefully

---

## 📧 Email Testing

### Check Email Inbox

After adding a product with subscribers:

1. Check email inbox for: `noreply@buylk.com`
2. Subject should contain: `🆕 New Item Added: [Product Name]`
3. Email should show:
   - ✅ Product image
   - ✅ Product name
   - ✅ Category
   - ✅ Price
   - ✅ Description
   - ✅ "View Product" button
   - ✅ Professional HTML formatting
   - ✅ Mobile responsive

### Email Not Arriving?

**Check these things:**

1. **Check spam/junk folder**
2. **Verify email configuration** - Edit `EmailNotifier.php`:
   ```php
   // Check SMTP settings
   echo ini_get('sendmail_path');
   echo ini_get('SMTP');
   echo ini_get('smtp_port');
   ```

3. **Enable PHP mail() in php.ini**:
   ```ini
   mail.function = On
   sendmail_path = "/usr/sbin/sendmail -t -i"
   ```

4. **Check error logs**:
   ```bash
   # Check PHP error log
   tail -f /var/log/php-errors.log
   
   # Check mail log
   tail -f /var/log/mail.log
   ```

5. **Test mail() function directly**:
   ```php
   <?php
   $to = "test@example.com";
   $subject = "Test Email";
   $message = "This is a test";
   $headers = "From: noreply@buylk.com";
   
   if (mail($to, $subject, $message, $headers)) {
       echo "Email sent successfully";
   } else {
       echo "Failed to send email";
   }
   ?>
   ```

---

## 🔍 Database Inspection

### View All Subscribers

```sql
SELECT * FROM email_subscriptions;
```

### Count Active Subscribers

```sql
SELECT COUNT(*) as total FROM email_subscriptions WHERE is_subscribed = 1;
```

### Find Specific Subscriber

```sql
SELECT * FROM email_subscriptions WHERE email = 'user@example.com';
```

### View Subscription Timeline

```sql
SELECT email, created_at, updated_at, is_subscribed 
FROM email_subscriptions 
ORDER BY created_at DESC;
```

### Unsubscribe Specific User

```sql
UPDATE email_subscriptions SET is_subscribed = 0 WHERE email = 'user@example.com';
```

---

## 🛠️ Browser Console Testing

Open browser (F12 → Console) and test subscription:

```javascript
// Test subscription API
fetch('../backend/products/subscription.php', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ email: 'test123@example.com' })
})
.then(r => r.json())
.then(data => console.log(data));

// Check subscription status
fetch('../backend/products/subscription.php?email=test123@example.com')
.then(r => r.json())
.then(data => console.log(data));
```

---

## ✅ Full Testing Checklist

- [ ] Database table created
- [ ] Subscribe via browser form works
- [ ] Subscription saved to database
- [ ] Check subscription status works
- [ ] Can unsubscribe user
- [ ] Cannot subscribe invalid email
- [ ] Cannot subscribe empty email
- [ ] Duplicate email handled correctly
- [ ] Can add product via API
- [ ] Response shows email count
- [ ] Emails sent to all subscribers
- [ ] Email format is HTML
- [ ] Email includes all product details
- [ ] Email is mobile responsive
- [ ] Links in email work
- [ ] No unsubscribed users receive emails

---

## 🎓 Troubleshooting Commands

### Clear all subscriptions (for testing):
```sql
TRUNCATE TABLE email_subscriptions;
```

### Reset auto-increment:
```sql
ALTER TABLE email_subscriptions AUTO_INCREMENT = 1;
```

### Check table structure:
```sql
DESCRIBE email_subscriptions;
```

### View all products:
```sql
SELECT id, name, price, created_at FROM products ORDER BY created_at DESC;
```

---

## 📊 Performance Check

### Email sending time for 100 subscribers:
```php
<?php
$start = microtime(true);

// Your email sending code here

$end = microtime(true);
$time = ($end - $start);

echo "Time to send 100 emails: " . $time . " seconds";
echo "Average per email: " . ($time / 100) . " seconds";
?>
```

---

## 🎉 Success Indicators

You'll know it's working when:

✅ Subscribe form shows green success message
✅ Email appears in database immediately
✅ New product API returns `"emailsSent": X`
✅ Users receive beautiful HTML emails
✅ Unsubscribe works and stops emails
✅ No console errors in browser
✅ No PHP errors in logs
✅ Emails arrive within seconds

---

**Ready to test? Start with Test 1 - Multiple Subscribers! 🚀**
