# Quick Reference - Backend/Frontend API Integration

## 1. Quick Database Setup

```sql
-- Create database
CREATE DATABASE shop_db;

-- Create users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(255) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create contact messages table
CREATE TABLE contact_messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    subject VARCHAR(255),
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

## 2. API Functions in JavaScript

```javascript
// Login
loginUser(email, password)

// Signup
signupUser(username, email, password, confirmPassword)

// Logout
logoutUser()

// Contact
submitContact(name, email, subject, message)

// Generic call
apiCall(endpoint, method, data)
```

## 3. API Response Format

### Success
```json
{
  "success": true,
  "status": 200,
  "data": {
    "message": "Success message",
    "user": { ... }
  }
}
```

### Error
```json
{
  "success": false,
  "status": 400,
  "data": {
    "message": "Error message"
  }
}
```

## 4. Form Integration

### Login Form
- ID: `loginForm`
- Inputs: `email`, `password`
- Handler: Auto-integrated in `script.js`

### Signup Form
- ID: `signupForm`
- Inputs: `fullname`, `email`, `password`, `confirmPassword`, `agreeTerms`
- Handler: Auto-integrated in `script.js`

### Contact Form
- ID: `contactForm`
- Inputs: `contactName`, `contactEmail`, `contactSubject`, `contactMessage`
- Handler: Auto-integrated in `script.js`

## 5. User Data in localStorage

```javascript
// After login/signup
localStorage.getItem('user')

// Returns
{
  "id": 1,
  "username": "user",
  "email": "user@example.com",
  "loggedIn": true
}

// Clear on logout
localStorage.removeItem('user')
```

## 6. Common API Calls Examples

```javascript
// Login
const response = await loginUser('user@example.com', 'password123');
if (response.success) {
  console.log('Login successful', response.data.user);
}

// Signup
const response = await signupUser('john_doe', 'john@example.com', 'Pass123', 'Pass123');
if (response.success) {
  console.log('Signup successful, userId:', response.data.userId);
}

// Contact
const response = await submitContact('John', 'john@example.com', 'Question', 'Hello...');
if (response.success) {
  console.log('Message sent');
}

// Logout
const response = await logoutUser();
if (response.success) {
  console.log('Logged out');
}
```

## 7. Error Handling Pattern

```javascript
try {
  const response = await someApiFunction();
  
  if (response.success) {
    // Handle success
    showToast('Success!');
  } else {
    // Handle API error
    showError(response.data.message);
  }
} catch (error) {
  // Handle network/parsing error
  showError('Network error. Please try again.');
  console.error(error);
}
```

## 8. Validation Rules

### Email
- Must contain @
- Server-side format validation
- Must be unique for signup

### Password
- Minimum 6 characters
- Client-side strength indicator
- Must match confirmation

### Username
- Must be unique
- Required field

### Contact Form
- Name: Required
- Email: Required, valid format
- Subject: Optional
- Message: Required, minimum length

## 9. Page URLs

| Page | URL |
|------|-----|
| Home | `http://localhost/New%20folder/front/index.html` |
| Shop | `http://localhost/New%20folder/front/shop.html` |
| Login | `http://localhost/New%20folder/front/login.html` |
| Signup | `http://localhost/New%20folder/front/signup.html` |
| Contact | `http://localhost/New%20folder/front/contact.html` |
| About | `http://localhost/New%20folder/front/about.html` |

## 10. Testing with cURL

```bash
# Test login
curl -X POST http://localhost/New%20folder/backend/auth/login.php \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"pass123"}'

# Test signup
curl -X POST http://localhost/New%20folder/backend/auth/signup.php \
  -H "Content-Type: application/json" \
  -d '{"username":"john","email":"john@test.com","password":"Pass123","confirmPassword":"Pass123"}'

# Test contact
curl -X POST http://localhost/New%20folder/backend/contact/submit.php \
  -H "Content-Type: application/json" \
  -d '{"name":"John","email":"john@test.com","subject":"Test","message":"Hello"}'

# Test logout
curl -X POST http://localhost/New%20folder/backend/auth/logout.php \
  -H "Content-Type: application/json"
```

## 11. Debug Checklist

- [ ] Open DevTools (F12)
- [ ] Go to Network tab
- [ ] Perform action (login, signup, etc)
- [ ] Click API request
- [ ] Check Request body (correct data?)
- [ ] Check Response (status 200/201?)
- [ ] Check Response JSON (success: true?)
- [ ] Go to Console tab
- [ ] Look for red errors
- [ ] Check localStorage (user data stored?)

## 12. Common Issues & Fixes

| Issue | Solution |
|-------|----------|
| 404 API error | Check WAMP is running, verify URL path |
| Login fails | Check database exists, user in database, password correct |
| CORS error | Ensure PHP files have CORS headers |
| Form doesn't submit | Check form IDs match, api.js loaded |
| Data not saving | Check MySQL is running, tables exist |
| Session lost | Check localStorage, browser settings |

## 13. File Quick Links

| File | Purpose |
|------|---------|
| `front/api.js` | API configuration & functions |
| `front/script.js` | Main app logic |
| `backend/config/database.php` | Database connection |
| `backend/auth/login.php` | Login endpoint |
| `backend/auth/signup.php` | Signup endpoint |
| `backend/contact/submit.php` | Contact endpoint |
| `API_INTEGRATION_GUIDE.md` | Full documentation |
| `SETUP_GUIDE.md` | Setup instructions |

## 14. Making Changes

### To modify API endpoint
Edit `front/api.js`:
```javascript
const API_ENDPOINTS = {
  login: 'new-url',
  // ...
};
```

### To add new form
1. Create form in HTML with ID
2. Add handler in `script.js`
3. Call appropriate `apiCall()` function

### To add new API endpoint
1. Create PHP file in backend
2. Add to `API_ENDPOINTS` in `api.js`
3. Create function in `api.js`
4. Call from frontend

## 15. Production Checklist

- [ ] Use HTTPS (not HTTP)
- [ ] Implement CSRF tokens
- [ ] Add rate limiting
- [ ] Add request logging
- [ ] Set up monitoring
- [ ] Configure backups
- [ ] Test load/stress
- [ ] Security audit
- [ ] Performance test
- [ ] User acceptance test

---

**Quick Help:**
- API functions are in `front/api.js`
- Form handlers are in `front/script.js`
- All endpoints require POST method
- All responses include success flag
- Check browser console for errors

**Need Help?** Check API_INTEGRATION_GUIDE.md for detailed documentation.
