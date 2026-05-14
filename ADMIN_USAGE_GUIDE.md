# Admin Guide: Sending Email Notifications

## How Admins Send Product Notifications

This guide explains how to add new products and automatically notify all subscribed users via email.

---

## 🎯 Complete Workflow

```
Admin adds new product
           ↓
System automatically fetches all subscribed emails
           ↓
Beautiful HTML email generated for each product
           ↓
Emails sent to all active subscribers
           ↓
Admin receives confirmation with email count
           ↓
Customers receive product notifications in inbox
```

---

## 📱 Method 1: Using Your Admin Dashboard

### Create Admin Form (HTML)

Add this form to your admin panel:

```html
<div class="admin-panel">
  <h2>Add New Product</h2>
  <form id="addProductForm">
    <div class="form-group">
      <label>Product Name *</label>
      <input type="text" name="name" required placeholder="e.g., Wireless Mouse">
    </div>

    <div class="form-group">
      <label>Category *</label>
      <select name="category" required>
        <option value="">Select Category</option>
        <option value="electronics">Electronics</option>
        <option value="fashion">Fashion</option>
        <option value="home">Home</option>
        <option value="sports">Sports</option>
        <option value="food">Food</option>
      </select>
    </div>

    <div class="form-group">
      <label>Price (LKR) *</label>
      <input type="number" name="price" required step="0.01" min="0" placeholder="0.00">
    </div>

    <div class="form-group">
      <label>Product Tag</label>
      <input type="text" name="tag" placeholder="e.g., New, Hot Deal, Limited">
    </div>

    <div class="form-group">
      <label>Image URL</label>
      <input type="url" name="image" placeholder="https://...">
    </div>

    <div class="form-group">
      <label>Description</label>
      <textarea name="description" rows="4" placeholder="Product details..."></textarea>
    </div>

    <div class="form-group">
      <label>Stock Quantity</label>
      <input type="number" name="stock" value="50" min="0">
    </div>

    <button type="submit" class="btn-submit">Add Product & Notify Subscribers</button>
  </form>

  <div id="addProductStatus"></div>
</div>

<style>
.form-group {
  margin-bottom: 15px;
}
.form-group label {
  display: block;
  margin-bottom: 5px;
  font-weight: bold;
}
.form-group input,
.form-group select,
.form-group textarea {
  width: 100%;
  padding: 8px;
  border: 1px solid #ddd;
  border-radius: 4px;
}
.btn-submit {
  background: #10b981;
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 16px;
}
.btn-submit:hover {
  background: #059669;
}
#addProductStatus {
  margin-top: 15px;
  padding: 10px;
  border-radius: 4px;
}
.success { background: #d1fae5; color: #065f46; }
.error { background: #fee2e2; color: #991b1b; }
</style>
```

### Handle Form Submission (JavaScript)

```javascript
document.getElementById('addProductForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  
  const formData = new FormData(e.target);
  const productData = {
    ...Object.fromEntries(formData),
    adminToken: 'your-admin-token' // Replace with your actual token
  };
  
  const statusDiv = document.getElementById('addProductStatus');
  
  try {
    const response = await fetch('../backend/products/list.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(productData)
    });
    
    const result = await response.json();
    
    if (result.success) {
      statusDiv.className = 'success';
      statusDiv.innerHTML = `
        <h3>✅ Product Added Successfully!</h3>
        <p><strong>Product ID:</strong> ${result.productId}</p>
        <p><strong>Emails Sent:</strong> ${result.emailsSent} / ${result.totalSubscribers}</p>
        <p>All subscribers have been notified about this new product.</p>
      `;
      e.target.reset();
    } else {
      statusDiv.className = 'error';
      statusDiv.innerHTML = `<h3>❌ Error:</h3><p>${result.message}</p>`;
    }
  } catch (error) {
    statusDiv.className = 'error';
    statusDiv.innerHTML = `<h3>❌ Error:</h3><p>${error.message}</p>`;
  }
});
```

---

## 🔗 Method 2: Using API/cURL

### Basic Request

```bash
curl -X POST http://localhost/xampp/htdocs/E\ commerce/New-folder/backend/products/list.php \
  -H "Content-Type: application/json" \
  -d '{
    "adminToken": "your-admin-token",
    "name": "Product Name",
    "category": "electronics",
    "price": 299.99,
    "tag": "New",
    "image": "https://...",
    "description": "Product description",
    "stock": 50
  }'
```

### Full Product Request Example

```bash
curl -X POST http://localhost/xampp/htdocs/E\ commerce/New-folder/backend/products/list.php \
  -H "Content-Type: application/json" \
  -d '{
    "adminToken": "admin123",
    "name": "Premium Wireless Headphones",
    "category": "electronics",
    "price": 4999.99,
    "tag": "Best Seller",
    "image": "https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=500",
    "description": "High-quality wireless headphones with active noise cancellation and 30-hour battery life",
    "stock": 100
  }'
```

### Expected Response

```json
{
  "success": true,
  "message": "Product added successfully",
  "productId": 42,
  "emailsSent": 15,
  "emailsFailed": 0,
  "totalSubscribers": 15
}
```

---

## 🖥️ Method 3: Using Postman/Insomnia

### Setup

1. **Open Postman**
2. **Create New Request**
   - Method: POST
   - URL: `http://localhost/xampp/htdocs/E commerce/New-folder/backend/products/list.php`

3. **Headers Tab**
   - Key: `Content-Type`
   - Value: `application/json`

4. **Body Tab**
   - Select: `raw` → `JSON`
   - Paste this JSON:

```json
{
  "adminToken": "your-admin-token",
  "name": "New Product Name",
  "category": "electronics",
  "price": 99.99,
  "tag": "New Arrival",
  "image": "https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=500",
  "description": "Amazing new product description here",
  "stock": 50
}
```

5. **Click Send**
6. **Check Response** - Should show `emailsSent` count

---

## 📋 Required Fields

| Field | Required | Type | Example |
|-------|----------|------|---------|
| `adminToken` | ✅ Yes | String | "admin123" |
| `name` | ✅ Yes | String | "Wireless Mouse" |
| `category` | ✅ Yes | String | "electronics" |
| `price` | ✅ Yes | Number | 49.99 |
| `tag` | ❌ No | String | "New" |
| `image` | ❌ No | URL | "https://..." |
| `description` | ❌ No | String | "Product details" |
| `stock` | ❌ No | Number | 50 |

---

## 📊 Understanding the Response

### Successful Response

```json
{
  "success": true,
  "message": "Product added successfully",
  "productId": 42,
  "emailsSent": 15,
  "emailsFailed": 0,
  "totalSubscribers": 15
}
```

**Breakdown:**
- `success: true` - Product was added
- `productId: 42` - Use this to reference the product
- `emailsSent: 15` - Number of successful emails
- `emailsFailed: 0` - Number of failed emails
- `totalSubscribers: 15` - Total subscribed users

### Error Response

```json
{
  "success": false,
  "message": "Name, category, and valid price are required"
}
```

---

## 🎨 Email Appearance

When products are added, subscribers receive:

```
From: noreply@buylk.com
Subject: 🆕 New Item Added: Premium Wireless Headphones - BUY LK

┌────────────────────────────────────┐
│ 🆕 NEW PRODUCT ALERT!              │
│                                    │
│ [Beautiful Product Image]          │
│                                    │
│ Premium Wireless Headphones        │
│ ⭐ Best Seller                     │
│ 🏷️ Electronics                     │
│ Price: LKR 4,999.99                │
│                                    │
│ High-quality wireless headphones   │
│ with active noise cancellation...  │
│                                    │
│ [VIEW PRODUCT] button              │
│                                    │
│ Thanks for staying updated!        │
│ - BUY LK Team                      │
└────────────────────────────────────┘
```

---

## ✨ Best Practices for Admins

### ✅ DO:

1. **Use Clear Product Names**
   - Good: "Premium Wireless Headphones"
   - Bad: "item123"

2. **Add High-Quality Images**
   - Use professional product photos
   - Ensure images load properly

3. **Write Detailed Descriptions**
   - Highlight key features
   - Include specifications

4. **Use Appropriate Tags**
   - "New" - For new arrivals
   - "Hot Deal" - For discounted items
   - "Limited" - For limited stock
   - "Best Seller" - For popular items

5. **Set Accurate Pricing**
   - Double-check prices
   - Use proper currency (LKR)

6. **Maintain Stock Quantity**
   - Set accurate stock numbers
   - Update when stock runs low

### ❌ DON'T:

1. ❌ Leave description empty
2. ❌ Use broken image URLs
3. ❌ Set price to 0
4. ❌ Use duplicate product names
5. ❌ Send products without email notification
6. ❌ Forget admin token

---

## 🔍 Monitoring Email Delivery

### Check Email Statistics

```sql
-- Count how many times each category was added as new product
SELECT category, COUNT(*) as product_count 
FROM products 
WHERE created_at > DATE_SUB(NOW(), INTERVAL 1 DAY)
GROUP BY category;

-- See all products added in last 24 hours
SELECT id, name, price, created_at 
FROM products 
WHERE created_at > DATE_SUB(NOW(), INTERVAL 1 DAY)
ORDER BY created_at DESC;

-- Check subscriber count
SELECT COUNT(*) as active_subscribers 
FROM email_subscriptions 
WHERE is_subscribed = 1;
```

### Estimate Email Volume

```
Per day emails = (products added per day) × (active subscribers)

Example:
- 5 products added per day
- 100 active subscribers
- = 500 emails per day
```

---

## 🛠️ Troubleshooting

### Problem: "Unauthorized access" error

**Solution:** Verify admin token is correct and included in request

```json
{
  "adminToken": "your-correct-token"  // ← Make sure this matches
}
```

### Problem: "Emails not sent" (emailsSent: 0)

**Possible causes:**
1. No active subscribers - check `email_subscriptions` table
2. PHP mail not configured - check php.ini
3. Email function disabled - enable in server

**Check:**
```sql
SELECT COUNT(*) FROM email_subscriptions WHERE is_subscribed = 1;
```

### Problem: "Invalid category" error

**Use valid categories:**
- electronics
- fashion
- home
- sports
- food
- beauty
- books

### Problem: "Required field missing"

**Ensure all required fields included:**
```json
{
  "adminToken": "required",
  "name": "required",
  "category": "required",
  "price": "required"
}
```

---

## 📈 Performance Tips

### For Bulk Product Addition

If adding many products:

```bash
#!/bin/bash

for i in {1..10}; do
  curl -X POST http://localhost/backend/products/list.php \
    -H "Content-Type: application/json" \
    -d '{
      "adminToken": "token",
      "name": "Product '$i'",
      "category": "electronics",
      "price": '$((100 + i * 10))',
      "tag": "New"
    }'
  
  sleep 2  # Wait 2 seconds between requests
done
```

### Database Optimization

```sql
-- Add indexes for faster queries
ALTER TABLE email_subscriptions ADD INDEX idx_subscribed (is_subscribed);
ALTER TABLE products ADD INDEX idx_created (created_at);

-- Archive old products (optional)
DELETE FROM products 
WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 YEAR);
```

---

## 📞 Common Scenarios

### Scenario 1: Launch New Product Line

```bash
# Add 3 products at launch
curl ... -d '{"name": "Product A", ...}'
curl ... -d '{"name": "Product B", ...}'
curl ... -d '{"name": "Product C", ...}'

# All 3 products notify subscribers automatically
```

### Scenario 2: Weekend Special

```bash
# Add special weekend product with "Hot Deal" tag
curl ... -d '{
  "name": "Weekend Special Bundle",
  "tag": "Hot Deal",
  "price": 1999.99,
  ...
}'

# Subscribers notified of special offer
```

### Scenario 3: Restock Popular Item

```bash
# Add returning popular product
curl ... -d '{
  "name": "Back in Stock: Popular Item",
  "tag": "Best Seller",
  ...
}'

# Subscribers who missed it first time get notified
```

---

## 📊 Dashboard Metrics

Track these for your admin dashboard:

```sql
-- Total emails sent (all time)
SELECT COUNT(*) as total_emails_sent FROM products 
WHERE created_at IS NOT NULL;

-- Subscriber growth (monthly)
SELECT MONTH(created_at) as month, COUNT(*) as new_subscribers
FROM email_subscriptions
WHERE YEAR(created_at) = 2026
GROUP BY MONTH(created_at);

-- Most popular category added
SELECT category, COUNT(*) as count
FROM products
GROUP BY category
ORDER BY count DESC;

-- Active subscribers
SELECT COUNT(*) as active_subscribers
FROM email_subscriptions
WHERE is_subscribed = 1;
```

---

## ✅ Admin Checklist Before Launching

- [ ] Database table `email_subscriptions` created
- [ ] Admin token set for authentication
- [ ] Email sending method configured (mail() or SMTP)
- [ ] Test subscription works
- [ ] Test product addition works
- [ ] Test email is received
- [ ] Email HTML renders properly
- [ ] Images in email load correctly
- [ ] Links in email work
- [ ] Mobile view of email looks good

---

**Now you're ready to start adding products and notifying your subscribers! 🚀**

**Every time you add a product, all your subscribers automatically get a beautiful email notification!**
