# BUY LK E-Commerce Platform - Setup Instructions

## Quick Start Guide

### Step 1: Database Setup

1. Open phpMyAdmin or MySQL command line
2. Create database:
```sql
CREATE DATABASE shop_db;
USE shop_db;
```

3. Create users table:
```sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(255) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

4. Create contact_messages table:
```sql
CREATE TABLE contact_messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    subject VARCHAR(255),
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Step 2: Verify Configuration

Check [backend/config/database.php](backend/config/database.php):
- Host: `localhost`
- Port: `3309` (WAMP default, may be different for you)
- Database: `shop_db`
- User: `root`
- Password: `1234`

Adjust if needed for your setup.

### Step 3: Start Services

1. Start WAMP Server
2. Ensure MySQL is running (port 3309)
3. Ensure Apache is running (port 80)

### Step 4: Test the Application

1. Navigate to: `http://localhost/New%20folder/front/index.html`
2. Test Sign Up: Click "Sign up" → Fill form → Submit
3. Test Login: Click "Sign in" → Enter credentials → Submit
4. Test Contact: Go to Contact page → Fill form → Submit

## Features Implemented

✅ **User Authentication**
- Sign Up with validation
- Login with credentials
- Logout functionality
- Session management

✅ **Contact Form**
- Name, email, subject, message
- Server-side validation
- Database storage

✅ **Frontend UI**
- Product catalog
- Shopping cart
- Search & filter
- Theme toggle (light/dark)
- Responsive design

## API Endpoints

| Method | Endpoint | Purpose |
|--------|----------|---------|
| POST | `/backend/auth/login.php` | User login |
| POST | `/backend/auth/signup.php` | User registration |
| POST | `/backend/auth/logout.php` | User logout |
| POST | `/backend/contact/submit.php` | Contact form submission |

## Files Modified/Created

### New Files
- `front/api.js` - API configuration and functions
- `API_INTEGRATION_GUIDE.md` - Complete integration documentation

### Modified Files
- `front/login.html` - API integration for login
- `front/signup.html` - API integration for signup
- `front/contact.html` - Enhanced contact form
- `front/script.js` - API integration for logout and forms
- `front/index.html` - Added api.js script
- `front/shop.html` - Added api.js script
- `front/about.html` - Added api.js script

### Unchanged Files
- All backend PHP files work as-is
- All CSS and styling remains the same

## Troubleshooting

### API Returns 404
- Check WAMP is running
- Verify MySQL database exists
- Check file paths are correct

### Login/Signup Fails
- Check MySQL connection in config/database.php
- Verify database tables exist
- Check for PHP errors in browser console

### CORS Errors
- Ensure all PHP files have CORS headers
- Check browser console for details

## Testing Credentials

After signup:
```
Username: test@example.com (your email)
Password: Password123 (or whatever you set)
```

## Next Steps

1. ✅ Connect frontend and backend
2. 📋 Test all forms (login, signup, contact)
3. 🔒 Verify database storage
4. 🚀 Deploy to production
5. 🔐 Implement security measures (SSL, HTTPS, etc.)

## Support

Check the browser console (F12) for error messages if something doesn't work.

---

**Last Updated:** February 2026
**Status:** Ready for Testing ✅
