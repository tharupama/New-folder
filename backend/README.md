# Backend Setup Instructions

## Database Configuration
- **Host:** localhost
- **Port:** 3309
- **Database Name:** shop_db
- **Username:** root
- **Password:** 1234

## Installation Steps

1. **Import the database:**
   ```bash
   mysql -u root -p1234 -P 3309 < backend/database.sql
   ```

2. **Start your PHP server:**
   ```bash
   cd backend
   php -S localhost:8000
   ```

## API Endpoints

### Authentication
- `POST /auth/signup.php` - User registration
- `POST /auth/login.php` - User login
- `POST /auth/logout.php` - User logout

### Contact
- `POST /contact/submit.php` - Submit contact form

## Folder Structure
```
backend/
├── config/
│   └── database.php        # Database connection configuration
├── auth/
│   ├── signup.php         # User registration endpoint
│   ├── login.php          # User login endpoint
│   └── logout.php         # User logout endpoint
├── contact/
│   └── submit.php         # Contact form submission
├── database.sql           # Database schema
└── README.md             # This file
```

## Usage

All endpoints accept JSON data and return JSON responses.

### Signup Example:
```javascript
fetch('http://localhost:8000/auth/signup.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
        username: 'john_doe',
        email: 'john@example.com',
        password: 'password123',
        confirmPassword: 'password123'
    })
});
```

### Login Example:
```javascript
fetch('http://localhost:8000/auth/login.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
        username: 'john_doe',
        password: 'password123'
    })
});
```
