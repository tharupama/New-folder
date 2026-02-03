// API Configuration
const API_BASE_URL = 'http://localhost/New%20folder/backend';

// API endpoints
const API_ENDPOINTS = {
  login: `${API_BASE_URL}/auth/login.php`,
  signup: `${API_BASE_URL}/auth/signup.php`,
  logout: `${API_BASE_URL}/auth/logout.php`,
  contact: `${API_BASE_URL}/contact/submit.php`
};

// Helper function to make API requests
async function apiCall(endpoint, method = 'POST', data = null) {
  try {
    const options = {
      method,
      headers: {
        'Content-Type': 'application/json'
      }
    };

    if (data && method !== 'GET') {
      options.body = JSON.stringify(data);
    }

    const response = await fetch(endpoint, options);
    const result = await response.json();

    return {
      success: response.ok,
      status: response.status,
      data: result
    };
  } catch (error) {
    console.error('API Error:', error);
    return {
      success: false,
      status: 0,
      data: { message: 'Network error. Please try again.' }
    };
  }
}

// Login function
async function loginUser(email, password) {
  return apiCall(API_ENDPOINTS.login, 'POST', {
    email,
    password
  });
}

// Signup function
async function signupUser(username, email, password, confirmPassword) {
  return apiCall(API_ENDPOINTS.signup, 'POST', {
    username,
    email,
    password,
    confirmPassword
  });
}

// Contact function
async function submitContact(name, email, subject, message) {
  return apiCall(API_ENDPOINTS.contact, 'POST', {
    name,
    email,
    subject,
    message
  });
}

// Get products function
async function getProducts() {
  return apiCall(`${API_BASE_URL}/products/list.php`, 'GET');
}

// Logout function
async function logoutUser() {
  return apiCall(API_ENDPOINTS.logout, 'POST');
}
