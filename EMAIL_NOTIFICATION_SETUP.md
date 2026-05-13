# Email Notification System for New Products - Implementation Guide

## Overview

Your e-commerce platform now has a complete email notification system that automatically sends product alerts to all subscribed users whenever a new item is added to the shop.

---

## 🎯 How It Works

```
User subscribes (enters email)
        ↓
Email saved to database
        ↓
Admin adds new product
        ↓
System automatically fetches all subscribed emails
        ↓
Beautiful HTML emails sent to all subscribers
        ↓
Emails arrive in inbox with product details
```

---

## 📦 Components Implemented

### 1. **Database Table: `email_subscriptions`**
Stores all user email subscriptions

```sql
CREATE TABLE IF NOT EXISTS email_subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    is_subscribed BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### 2. **Subscription API: `subscription.php`**
Handles subscription management with 4 methods:

- **POST** - Subscribe user to emails
- **GET** - Check subscription status
- **PUT** - Unsubscribe user
- **DELETE** - Remove subscription

### 3. **Email Utility: `EmailNotifier.php`**
Sends beautiful HTML emails with:
- Product information
- Pricing details
- Product images
- Call-to-action buttons
- Mobile-responsive design

### 4. **Enhanced Product API: `list.php`**
Modified to:
- Accept new products
- Fetch all subscribed users
- Send emails to each subscriber
- Return email sending status

---

## 🚀 Setup Instructions

### Step 1: Update Database

Run this SQL to create the subscriptions table:

```sql
-- Email Subscriptions table for product notifications
CREATE TABLE IF NOT EXISTS email_subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    is_subscribed BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

Or run the updated `database.sql` file which now includes this table.

### Step 2: Configure Email Settings (Optional)

If you want to use SMTP instead of PHP's default mail function, edit `backend/config/EmailNotifier.php`:

```php
$this->smtpConfig = [
    'host' => 'smtp.gmail.com',      // Your SMTP host
    'port' => 587,                    // SMTP port
    'username' => 'your-email@gmail.com',  // SMTP username
    'password' => 'your-password'    // SMTP password
];
```

### Step 3: Test the System

#### Test 1: Subscribe a User

```bash
curl -X POST http://localhost/xampp/htdocs/E\ commerce/New-folder/backend/products/subscription.php \
  -H "Content-Type: application/json" \
  -d '{"email": "user@example.com"}'
```

Expected response:
```json
{
  "success": true,
  "message": "Successfully subscribed! You will receive email notifications about new items."
}
```

#### Test 2: Add New Product & Send Emails

```bash
curl -X POST http://localhost/xampp/htdocs/E\ commerce/New-folder/backend/products/list.php \
  -H "Content-Type: application/json" \
  -d '{
    "adminToken": "your-token",
    "name": "New Amazing Product",
    "category": "electronics",
    "price": 299.99,
    "tag": "New Arrival",
    "image": "https://example.com/product.jpg",
    "description": "Amazing new product just arrived!",
    "stock": 50
  }'
```

Expected response:
```json
{
  "success": true,
  "message": "Product added successfully",
  "productId": 10,
  "emailsSent": 3,
  "emailsFailed": 0,
  "totalSubscribers": 3
}
```

---

## 📧 Email Features

### Email Template Includes:
✅ Beautiful HTML design
✅ Product image
✅ Product name & category
✅ Price in LKR
✅ Product description
✅ Product tag (New, Hot Deal, etc.)
✅ "View in Shop" button
✅ Mobile responsive
✅ Professional footer

### Fallback Methods:
- **Primary**: PHPMailer (if installed)
- **Secondary**: PHP mail() function (built-in)
- **Graceful Error Handling**: Logs errors if email fails

---

## 🔌 API Endpoints

### Subscribe User

**POST** `/backend/products/subscription.php`

**Request:**
```json
{
  "email": "user@example.com"
}
```

**Response (201 Created):**
```json
{
  "success": true,
  "message": "Successfully subscribed! You will receive email notifications about new items."
}
```

**Response (200 OK - Already Subscribed):**
```json
{
  "success": true,
  "message": "You are already subscribed to our notifications!"
}
```

### Check Subscription Status

**GET** `/backend/products/subscription.php?email=user@example.com`

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "email": "user@example.com",
    "is_subscribed": true,
    "created_at": "2026-05-11 10:30:45"
  }
}
```

**Response (200 - Not Subscribed):**
```json
{
  "success": false,
  "data": null,
  "message": "Not subscribed"
}
```

### Unsubscribe User

**PUT** `/backend/products/subscription.php`

**Request:**
```json
{
  "email": "user@example.com"
}
```

**Response:**
```json
{
  "success": true,
  "message": "You have been unsubscribed"
}
```

### Delete Subscription

**DELETE** `/backend/products/subscription.php`

**Request:**
```json
{
  "email": "user@example.com"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Subscription removed"
}
```

---

## 🎨 Frontend Integration

The newsletter form on your website is now fully integrated:

1. User enters email in "Stay in the loop" form
2. Clicks "Notify me" button
3. Email is validated
4. Sent to subscription API
5. User receives confirmation message
6. Email added to database
7. User will receive emails on all future new products

### Form HTML (Already in index.html):
```html
<form id="newsletterForm">
  <input type="email" id="newsletterEmail" placeholder="Email address" required />
  <button class="primary-btn" type="submit">Notify me</button>
</form>
<span id="newsletterMsg"></span>
```

---

## 🔐 Security Features

✅ **Email Validation** - Uses `filter_var()` with FILTER_VALIDATE_EMAIL
✅ **SQL Injection Prevention** - All queries use prepared statements
✅ **Admin Token** - Required for adding products
✅ **Error Handling** - Graceful error messages
✅ **CORS Headers** - Proper cross-origin handling

---

## 📊 Database Queries

### Get All Subscribers

```sql
SELECT email FROM email_subscriptions WHERE is_subscribed = 1;
```

### Get Subscriber Count

```sql
SELECT COUNT(*) as subscriber_count FROM email_subscriptions WHERE is_subscribed = 1;
```

### Get Subscription History

```sql
SELECT * FROM email_subscriptions ORDER BY created_at DESC;
```

### Find Specific Subscriber

```sql
SELECT * FROM email_subscriptions WHERE email = 'user@example.com';
```

### Unsubscribe All

```sql
UPDATE email_subscriptions SET is_subscribed = 0;
```

---

## 🚨 Troubleshooting

### Problem: "Emails not being sent"

**Solution 1: Check PHP mail configuration**
```php
// In a PHP file, check if mail function works
if (ini_get('sendmail_path')) {
    echo "Mail is configured to use: " . ini_get('sendmail_path');
} else {
    echo "Using SMTP (check php.ini)";
}
```

**Solution 2: Enable PHP mail function (in php.ini)**
```ini
; Enable the mail function
mail.function = On

; Set sendmail program path
sendmail_path = "/usr/sbin/sendmail -t -i"
```

**Solution 3: Check error logs**
```php
// Add this to EmailNotifier.php to log errors
error_log("Email send result: " . ($result ? "Success" : "Failed"));
```

### Problem: "Connection to database failed"

**Solution:**
- Verify database credentials in `backend/config/database.php`
- Ensure MySQL is running
- Check database name is `shop_db`

### Problem: "CORS error when subscribing"

**Solution:**
- Check `subscription.php` has CORS headers
- Verify the endpoint URL is correct: `/backend/products/subscription.php`

---

## ✨ Advanced Features

### Custom Email From Address

Edit in `EmailNotifier.php`:
```php
private $fromEmail = 'noreply@buylk.com';
private $fromName = 'BUY LK - New Product Alert';
```

### Batch Email Sending Limit

Add in `EmailNotifier.php` to avoid overwhelming mail server:
```php
const BATCH_SIZE = 50; // Send max 50 emails per request

foreach (array_chunk($emails, self::BATCH_SIZE) as $batch) {
    $this->sendBatchNotifications($batch, $product);
    sleep(1); // Wait 1 second between batches
}
```

### Unsubscribe Link in Email

Add to `generateProductEmailHTML()`:
```html
<a href="http://yoursite.com/unsubscribe.php?email=<?php echo urlencode($toEmail); ?>">
  Unsubscribe from these emails
</a>
```

---

## 📈 Performance Tips

1. **Use Background Jobs**: For large subscriber lists (1000+), send emails asynchronously
2. **Add Database Indexes**:
```sql
ALTER TABLE email_subscriptions ADD INDEX idx_subscribed (is_subscribed);
ALTER TABLE email_subscriptions ADD INDEX idx_email (email);
```

3. **Implement Caching**:
```php
// Cache subscriber count
$subscriberCount = apcu_fetch('subscriber_count');
if ($subscriberCount === false) {
    $subscriberCount = $pdo->query("SELECT COUNT(*) FROM email_subscriptions")->fetchColumn();
    apcu_store('subscriber_count', $subscriberCount, 3600); // Cache for 1 hour
}
```

---

## 🎯 Usage Workflow

### For Customers:
1. Visit any page on the website
2. Scroll to "Stay in the loop" section (bottom)
3. Enter email address
4. Click "Notify me"
5. Receive confirmation: "✅ Successfully subscribed!"
6. Start receiving emails for new products

### For Admins:
1. Add new product via admin panel (using product API)
2. System automatically sends emails to all subscribers
3. Admin receives count of emails sent
4. Customers receive beautiful product notification email

---

## 📱 Email Preview

When a new product is added, subscribers receive:

```
Email Subject: 🆕 New Item Added: [Product Name] - BUY LK

Email Body:
┌─────────────────────────────────┐
│ 🆕 NEW PRODUCT ALERT!           │
│                                 │
│ [Product Image]                 │
│                                 │
│ Product Name                    │
│ Category: Electronics           │
│ Price: LKR 4,999.99             │
│ Description...                  │
│                                 │
│ [VIEW PRODUCT BUTTON]           │
│                                 │
│ BUY LK Team                     │
│ Unsubscribe link                │
└─────────────────────────────────┘
```

---

## 🔄 Automating Email Sending

### Option 1: Cron Job (Linux)

Add to crontab to send pending emails:
```bash
*/5 * * * * php /path/to/send-pending-emails.php
```

### Option 2: Scheduled Task (Windows)

Create `send-pending-emails.php`:
```php
<?php
require_once 'backend/config/database.php';
require_once 'backend/config/EmailNotifier.php';

$pdo = getDBConnection();

// Get pending emails from a queue table
$stmt = $pdo->prepare("
    SELECT * FROM email_queue 
    WHERE sent = 0 
    ORDER BY created_at 
    LIMIT 100
");
$stmt->execute();
$pendingEmails = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($pendingEmails as $email) {
    // Send email
    $notifier = new EmailNotifier();
    $result = $notifier->sendNewProductNotification($email['to_email'], json_decode($email['product_data'], true));
    
    // Mark as sent
    $updateStmt = $pdo->prepare("UPDATE email_queue SET sent = 1 WHERE id = ?");
    $updateStmt->execute([$email['id']]);
}
?>
```

---

## 📞 Support & FAQ

**Q: Can users manage their subscription preferences?**
A: Yes! Users can unsubscribe via the API (PUT method) or by clicking a link in the email (if added).

**Q: What if a subscriber's email bounces?**
A: Implement bounce handling to automatically mark those emails as invalid.

**Q: Can we send different products to different subscriber groups?**
A: Yes! Extend the `email_subscriptions` table to include category preferences.

**Q: How many emails per product are being sent?**
A: All active subscribers receive one email per new product. Check the API response `emailsSent` count.

---

## ✅ Testing Checklist

- [ ] Database table `email_subscriptions` created
- [ ] Can subscribe via `/backend/products/subscription.php` POST
- [ ] Can check status via GET with email parameter
- [ ] Can unsubscribe via PUT method
- [ ] Newsletter form works and calls subscription API
- [ ] Can add product and emails are sent
- [ ] Response shows correct email sent count
- [ ] Emails arrive in inbox with proper HTML formatting
- [ ] Product image displays in email
- [ ] Price, name, category all visible in email
- [ ] Click button in email works
- [ ] Mobile view of email looks good

---

## Files Modified/Created

✅ **Created:**
- `backend/products/subscription.php` - Subscription management API
- `backend/config/EmailNotifier.php` - Email sending utility

✅ **Modified:**
- `backend/database.sql` - Added `email_subscriptions` table
- `backend/products/list.php` - Sends emails when product added
- `front/script.js` - Newsletter form calls subscription API

---

**Your email notification system is now ready to use! Start subscribing users and sending them beautiful product notifications! 🎉**
