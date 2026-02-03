# Products Database Setup Guide

## Overview
Products are now stored in the database instead of hardcoded in JavaScript. You can manage products through the admin panel.

## Database Setup

### 1. Update Database Tables
Run this SQL to add the products table with all necessary fields:

```sql
USE shop_db;

-- Drop the old products table if it exists
DROP TABLE IF EXISTS products;

-- Create new products table with all fields
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    image VARCHAR(255),
    stock INT DEFAULT 50,
    category VARCHAR(50) NOT NULL,
    rating DECIMAL(3, 1) DEFAULT 4.5,
    tag VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert sample products
INSERT INTO products (name, category, price, rating, tag, image, description, stock) VALUES
('Aurora Smart Speaker', 'tech', 129.99, 4.7, 'Bestseller', 'https://images.unsplash.com/photo-1512446816042-444d641267d4?auto=format&fit=crop&w=800&q=80', 'Premium wireless speaker with superior sound quality', 72),
('Nimbus Air Purifier', 'home', 249.00, 4.9, 'Eco', 'https://images.unsplash.com/photo-1581578731548-c64695cc6952?auto=format&fit=crop&w=800&q=80', 'Advanced air purification for cleaner air', 45),
('Pulse Fitness Band', 'fitness', 89.50, 4.6, 'New', 'https://images.unsplash.com/photo-1518441902117-fc8b0c201d19?auto=format&fit=crop&w=800&q=80', 'Track your fitness with precision', 58),
('Lumen Desk Lamp', 'home', 58.00, 4.4, 'Limited', 'https://images.unsplash.com/photo-1481277542470-605612bd2d61?auto=format&fit=crop&w=800&q=80', 'Energy-efficient LED desk lamp', 34),
('Stride Eco Sneakers', 'fashion', 145.00, 4.5, 'Trending', 'https://images.unsplash.com/photo-1525966222134-fcfa99b8ae77?auto=format&fit=crop&w=800&q=80', 'Sustainable and comfortable footwear', 62),
('Vista Travel Backpack', 'fashion', 110.00, 4.3, 'Travel', 'https://images.unsplash.com/photo-1514474959185-1472d4e46f4e?auto=format&fit=crop&w=800&q=80', 'Perfect companion for your travels', 28),
('Halo Aroma Diffuser', 'home', 64.99, 4.8, 'Relax', 'https://images.unsplash.com/photo-1501004318641-b39e6451bec6?auto=format&fit=crop&w=800&q=80', 'Create a peaceful atmosphere at home', 50),
('Zen Smart Watch', 'tech', 219.00, 4.7, 'Editor pick', 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?auto=format&fit=crop&w=800&q=80', 'Advanced health and fitness tracking', 41);
```

### 2. Verify Products Loaded
```sql
SELECT * FROM products;
-- Should show 8 sample products
```

## Using the Admin Panel

### Access Admin Panel
1. Navigate to: `http://localhost/New%20folder/front/admin.html`
2. Or from any page, look for Admin link in navigation

### Add New Product
1. Fill in the form:
   - **Admin Key:** `admin123` (default, change in `backend/products/list.php`)
   - **Product Name:** e.g., "Wireless Headphones"
   - **Category:** Choose from Tech, Home, Fitness, Fashion
   - **Price:** Enter price (e.g., 129.99)
   - **Rating:** Leave as 4.5 or customize (0-5)
   - **Tag:** Optional (e.g., "New", "Bestseller", "Limited")
   - **Stock:** Number of items available (default 50)
   - **Description:** Product description
   - **Image URL:** Link to product image

2. Click "Add Product"
3. Success message appears and product shows in the list below
4. Product immediately appears in the shop

### View Current Products
The admin panel displays all current products below the form with:
- Product name
- Category
- Price
- Rating
- Stock count
- Tag (if applicable)

## API Endpoints

### Get All Products
```
GET /backend/products/list.php

Response:
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Aurora Smart Speaker",
      "category": "tech",
      "price": "129.99",
      "rating": "4.7",
      "tag": "Bestseller",
      "image": "https://...",
      "description": "...",
      "stock": 72
    },
    ...
  ]
}
```

### Add New Product
```
POST /backend/products/list.php

Request:
{
  "adminKey": "admin123",
  "name": "Product Name",
  "category": "tech",
  "price": 99.99,
  "rating": 4.5,
  "tag": "New",
  "image": "https://...",
  "description": "...",
  "stock": 50
}

Response:
{
  "success": true,
  "message": "Product added successfully",
  "productId": 9
}
```

## Frontend Integration

### How It Works
1. **Script.js** calls `loadProducts()` on page load
2. **api.js** has `getProducts()` function that fetches from database
3. Products array is populated from database (not hardcoded)
4. All filtering, search, and shopping cart features work the same

### Example Usage in JavaScript
```javascript
// Get all products from database
const response = await getProducts();
if (response.success) {
  const products = response.data;
  // Use products...
}
```

## Security Notes

### Current Security
- Basic admin key authentication (change the key!)
- Prepared statements to prevent SQL injection
- CORS properly configured

### To Improve Security (Production)
1. Change admin key in `backend/products/list.php` line 30:
```php
if ($adminKey !== 'admin123') {  // Change 'admin123' to strong password
```

2. Implement proper authentication (login required for admin)
3. Use HTTPS for all admin panel access
4. Add rate limiting on product creation

## Troubleshooting

### Products Not Showing in Shop
1. Check database has products table with correct fields
2. Check MySQL is running
3. Check browser console for errors
4. Verify `loadProducts()` is called in script.js

### Can't Add Product in Admin Panel
1. Verify admin key is correct: `admin123`
2. Check required fields (name, category, price)
3. Check browser console for error messages
4. Verify backend/products/list.php exists

### Database Connection Error
1. Check MySQL is running on port 3309
2. Verify database credentials in config/database.php
3. Verify products table exists: `SHOW TABLES;`
4. Check table structure: `DESCRIBE products;`

## Sample Product Data

Pre-loaded products:
| ID | Name | Category | Price | Rating | Tag |
|----|------|----------|-------|--------|-----|
| 1 | Aurora Smart Speaker | tech | $129.99 | 4.7 | Bestseller |
| 2 | Nimbus Air Purifier | home | $249.00 | 4.9 | Eco |
| 3 | Pulse Fitness Band | fitness | $89.50 | 4.6 | New |
| 4 | Lumen Desk Lamp | home | $58.00 | 4.4 | Limited |
| 5 | Stride Eco Sneakers | fashion | $145.00 | 4.5 | Trending |
| 6 | Vista Travel Backpack | fashion | $110.00 | 4.3 | Travel |
| 7 | Halo Aroma Diffuser | home | $64.99 | 4.8 | Relax |
| 8 | Zen Smart Watch | tech | $219.00 | 4.7 | Editor pick |

## File Structure

```
backend/
└── products/
    └── list.php          (NEW - API for products)

front/
├── admin.html            (NEW - Admin panel)
├── api.js                (UPDATED - Added getProducts)
└── script.js             (UPDATED - Load from database)

database.sql              (UPDATED - Products table)
```

## Next Steps

1. ✅ Run SQL to create products table
2. ✅ Insert sample products
3. ✅ Test by visiting shop - should show products from database
4. ✅ Visit admin panel and add new products
5. ✅ Verify products appear in shop immediately

## Admin Key

Default: `admin123`

⚠️ **IMPORTANT:** Change this in production!

Edit `backend/products/list.php` line 30 and change the admin key to something secure.

---

**Status:** ✅ Products now stored in database with admin management!
