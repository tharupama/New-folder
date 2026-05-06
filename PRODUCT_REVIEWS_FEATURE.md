# Product Reviews Feature

## Overview
This feature allows customers to view product details with customer reviews and submit their own reviews for products.

## Implementation Summary

### 1. Database Schema
- **Table:** `product_reviews`
  - Stores product reviews with rating (1-5), comment, user name, and timestamp
  - Foreign key relationship with products table
  - Automatically updates product average rating on new review submission

### 2. Backend API Endpoints

#### Get Reviews
- **Endpoint:** `backend/reviews/get.php`
- **Method:** GET
- **Parameters:** `product_id` (query parameter)
- **Returns:** List of reviews, average rating, and total review count

#### Add Review
- **Endpoint:** `backend/reviews/add.php`
- **Method:** POST
- **Body:** JSON with `product_id`, `user_name`, `rating`, `comment`
- **Validation:** 
  - Rating must be 1-5
  - Comment must be at least 10 characters
  - Product must exist

### 3. Frontend Components

#### Modal Structure
- Product image and details on the left
- Reviews section on the right with:
  - Average rating display
  - Review submission form with star rating input
  - List of existing reviews

#### Files Modified/Created
- `front/modals.html` - Added product details modal HTML
- `front/styles.css` - Added modal and review styling
- `front/product-modal.js` - New file for modal functionality
- `front/api.js` - Added review API functions
- `front/script.js` - Updated to open modal on product click
- `front/index.html` - Added modal HTML and script
- `front/shop.html` - Added modal HTML and script
- `backend/database.sql` - Added reviews table and sample data
- `backend/reviews/get.php` - New API endpoint
- `backend/reviews/add.php` - New API endpoint

## How to Use

### For Customers:
1. Click on any product card to open the product details modal
2. View existing customer reviews and ratings
3. Submit a review by:
   - Selecting a star rating (1-5 stars)
   - Entering your name
   - Writing your review (minimum 10 characters)
   - Clicking "Submit Review"
4. Add product to cart or wishlist directly from the modal

### Testing:
1. Run the updated database.sql to create the reviews table
2. Open the shop page (shop.html or index.html)
3. Click on any product to open the details modal
4. View sample reviews for products that have them
5. Submit a new review and see it appear immediately

## Features:
- ✅ Product details modal with image and description
- ✅ Customer reviews display with ratings
- ✅ Average rating calculation
- ✅ Interactive star rating input
- ✅ Review submission form with validation
- ✅ Real-time review updates
- ✅ Responsive design for mobile and desktop
- ✅ Smooth animations and transitions
- ✅ Add to cart/wishlist from modal
- ✅ XSS protection with HTML escaping

## Database Setup:
Run the updated `backend/database.sql` file to:
1. Create the `product_reviews` table
2. Add sample reviews for testing
