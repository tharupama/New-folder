# BUY LK - Frontend & Backend Integration Guide

## Overview
The frontend and backend have been successfully connected with the following features:
- User Authentication (Login & Signup)
- Contact Form Submission
- Session Management

## Setup Instructions

### Prerequisites
- WAMP/LAMP server running
- MySQL database configured
- PHP 7.4 or higher

### Database Setup

1. Create a database named `shop_db`:
```sql
CREATE DATABASE shop_db;
```

2. Run the SQL file to set up tables:
```bash
mysql -u root -p1234 shop_db < database.sql
```

3. Expected tables:
   - `users` - User accounts and authentication
   - `contact_messages` - Contact form submissions

## API Configuration

The API configuration is in [api.js](front/api.js):

```javascript
const API_BASE_URL = 'http://localhost/New%20folder/backend';

const API_ENDPOINTS = {
  login: `${API_BASE_URL}/auth/login.php`,
  signup: `${API_BASE_URL}/auth/signup.php`,
  logout: `${API_BASE_URL}/auth/logout.php`,
  contact: `${API_BASE_URL}/contact/submit.php`
};
```

## API Endpoints

### 1. User Login
**Endpoint:** `POST /backend/auth/login.php`

**Request:**
```json
{
  "email": "user@example.com",
  "password": "password123"
}
```

**Response (Success):**
```json
{
  "success": true,
  "message": "Login successful",
  "user": {
    "id": 1,
    "username": "username",
    "email": "user@example.com"
  }
}
```

### 2. User Signup
**Endpoint:** `POST /backend/auth/signup.php`

**Request:**
```json
{
  "username": "john_doe",
  "email": "john@example.com",
  "password": "SecurePass123",
  "confirmPassword": "SecurePass123"
}
```

**Response (Success):**
```json
{
  "success": true,
  "message": "User registered successfully",
  "userId": 2
}
```

### 3. User Logout
**Endpoint:** `POST /backend/auth/logout.php`

**Response:**
```json
{
  "success": true,
  "message": "Logged out successfully"
}
```

### 4. Contact Form Submission
**Endpoint:** `POST /backend/contact/submit.php`

**Request:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "subject": "Product Inquiry",
  "message": "I have a question about your products."
}
```

**Response (Success):**
```json
{
  "success": true,
  "message": "Message sent successfully"
}
```

## Frontend Pages and Features

### 1. Index Page (`index.html`)
- Product showcase
- Category filtering
- Add to cart functionality
- Cart management
- Theme toggle (light/dark)

### 2. Shop Page (`shop.html`)
- Full product catalog
- Search, filter, and sort
- Wishlist functionality
- Cart integration

### 3. Login Page (`login.html`)
- Email/password login form
- Client-side and server-side validation
- Error message display
- Remember me option
- Redirect to index on success

### 4. Signup Page (`signup.html`)
- User registration form
- Password strength indicator
- Password confirmation
- Terms acceptance checkbox
- Newsletter signup option

### 5. Contact Page (`contact.html`)
- Contact form with name, email, subject, and message
- Form validation
- Success/error message display

### 6. About Page (`about.html`)
- Company information
- Newsletter signup

## JavaScript Files

### api.js
Contains all API communication functions:
- `loginUser(email, password)` - Handle user login
- `signupUser(username, email, password, confirmPassword)` - Register new user
- `logoutUser()` - Logout user
- `submitContact(name, email, subject, message)` - Submit contact form
- `apiCall(endpoint, method, data)` - Generic API request handler

### script.js
Main application logic:
- Product filtering and search
- Cart management
- User authentication UI
- Form handling
- Theme toggling

## Authentication Flow

### Login Flow
1. User enters email and password on login page
2. Form validation performed on client-side
3. API call made to `login.php`
4. Backend validates credentials against database
5. On success: User data stored in localStorage, redirect to index
6. On error: Display error message

### Signup Flow
1. User fills registration form
2. Client-side validation checks:
   - All fields filled
   - Valid email format
   - Password minimum 6 characters
   - Passwords match
   - Terms accepted
3. API call made to `signup.php`
4. Backend checks for existing username/email
5. Password hashed with PASSWORD_DEFAULT
6. User inserted into database
7. On success: Store user in localStorage, redirect to index

### Logout Flow
1. User clicks logout button
2. API call made to `logout.php`
3. Server-side session destroyed
4. localStorage cleared
5. UI updated to show login/signup buttons
6. Redirect to index page

## Error Handling

All API endpoints return structured error responses:
```json
{
  "success": false,
  "message": "Error description"
}
```

Frontend handles errors with:
- User-friendly error messages
- Console logging for debugging
- Automatic retry capability on network errors

## Local Storage

User data is stored in localStorage for client-side persistence:
```json
{
  "user": {
    "id": 1,
    "username": "john_doe",
    "email": "john@example.com",
    "fullname": "John Doe",
    "loggedIn": true
  }
}
```

## CORS Configuration

All backend PHP files have CORS headers enabled:
```php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
```

This allows requests from any origin (suitable for development; restrict in production).

## Security Considerations

### Current Implementation
- Password hashing with PHP's PASSWORD_DEFAULT
- Server-side input validation
- Email format validation
- CORS enabled for development

### Recommendations for Production
1. Add HTTPS/SSL encryption
2. Implement CSRF tokens
3. Rate limiting on authentication endpoints
4. Add reCAPTCHA for form protection
5. Restrict CORS to specific domains
6. Add SQL injection protection (already using prepared statements)
7. Implement proper session timeout
8. Add password reset functionality
9. Add email verification for signups
10. Implement request logging and monitoring

## Troubleshooting

### 404 Errors on API Calls
- Check that WAMP is running
- Verify the path: `/New%20folder/backend/`
- Check file names match exactly

### Login/Signup Fails
- Check MySQL is running
- Verify database `shop_db` exists
- Check database connection details in `config/database.php`
- Check user has proper permissions on database

### Contact Form Not Sending
- Verify `contact_messages` table exists
- Check email field is valid
- Review browser console for error messages

### CORS Errors
- Ensure PHP files have correct CORS headers
- Check browser console for specific errors
- Temporarily disable CORS restrictions for debugging

## Testing the API

### Using cURL (Command Line)
```bash
# Test Login
curl -X POST http://localhost/New%20folder/backend/auth/login.php \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password123"}'

# Test Signup
curl -X POST http://localhost/New%20folder/backend/auth/signup.php \
  -H "Content-Type: application/json" \
  -d '{"username":"john","email":"john@example.com","password":"Pass123","confirmPassword":"Pass123"}'

# Test Contact
curl -X POST http://localhost/New%20folder/backend/contact/submit.php \
  -H "Content-Type: application/json" \
  -d '{"name":"John","email":"john@example.com","subject":"Test","message":"Test message"}'
```

### Using Browser Developer Tools
1. Open DevTools (F12)
2. Go to Network tab
3. Perform action (login, signup, etc.)
4. Click on the API request to see details
5. Check Request/Response tabs

## File Structure

```
/backend
├── auth/
│   ├── login.php
│   ├── signup.php
│   └── logout.php
├── config/
│   └── database.php
├── contact/
│   └── submit.php
├── database.sql
└── README.md

/front
├── index.html
├── shop.html
├── login.html
├── signup.html
├── contact.html
├── about.html
├── api.js         (NEW - API Configuration)
├── script.js      (UPDATED - API integration)
└── styles.css
```

## Next Steps

1. **Database Setup** - Run `database.sql` to create tables
2. **Test API** - Use cURL or Postman to test endpoints
3. **Frontend Testing** - Test login, signup, and contact forms
4. **User Testing** - Have users test the complete flow
5. **Security Review** - Implement production recommendations
6. **Performance** - Monitor API response times and optimize

## Support

For issues or questions:
1. Check console errors (F12 → Console)
2. Check network tab for failed requests
3. Review PHP error logs
4. Verify database connectivity
5. Check file permissions on server

---

**Last Updated:** February 2026
**API Version:** 1.0
**Frontend Version:** 1.0
