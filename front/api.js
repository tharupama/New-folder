// Supabase configuration
const SUPABASE_URL = 'https://iyyetrmcfnxrgicrtexi.supabase.co';
const SUPABASE_ANON_KEY = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Iml5eWV0cm1jZm54cmdpY3J0ZXhpIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NjczMjExODYsImV4cCI6MjA4Mjg5NzE4Nn0.Ms3mPKkAquP9PCMfS1v8ea1VLRn68_cJLrf7r-IVfhM';

let supabaseClient = null;

async function getSupabaseClient() {
  if (supabaseClient) {
    return supabaseClient;
  }

  const { createClient } = await import('https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2.38.4/+esm');
  supabaseClient = createClient(SUPABASE_URL, SUPABASE_ANON_KEY);
  return supabaseClient;
}

// PHP backend configuration for non-auth endpoints
const API_BASE_URL = 'http://localhost/New%20folder%20(2)/New-folder/backend';
const API_ENDPOINTS = {
  contact: `${API_BASE_URL}/contact/submit.php`,
  products: `${API_BASE_URL}/products/list.php`,
  reviews: {
    get: `${API_BASE_URL}/reviews/get.php`,
    add: `${API_BASE_URL}/reviews/add.php`
  }
};

// Helper function for PHP API calls (non-auth)
async function phpApiCall(endpoint, method = 'POST', data = null) {
  try {
    const options = {
      method,
      headers: { 'Content-Type': 'application/json' }
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
    console.error('PHP API Error:', error);
    return {
      success: false,
      status: 0,
      data: { message: 'Network error. Please try again.' }
    };
  }
}

function normalizeUser(user) {
  if (!user) {
    return null;
  }

  return {
    id: user.id ?? null,
    username: user.user_metadata?.username ?? user.email?.split('@')[0] ?? '',
    email: user.email ?? ''
  };
}

// Authentication functions using Supabase
async function loginUser(email, password) {
  try {
    const supabase = await getSupabaseClient();
    const { data, error } = await supabase.auth.signInWithPassword({
      email,
      password
    });

    if (error) {
      return {
        success: false,
        status: 401,
        data: {
          message: error.message || 'Login failed. Please check your credentials.'
        }
      };
    }

    // Check if email is confirmed
    if (!data.user?.email_confirmed_at) {
      return {
        success: false,
        status: 403,
        data: {
          message: 'Please confirm your email address before logging in. Check your inbox for the confirmation link.'
        }
      };
    }

    return {
      success: true,
      status: 200,
      data: {
        message: 'Login successful',
        user: normalizeUser(data.user)
      }
    };
  } catch (error) {
    console.error('Login Error:', error);
    return {
      success: false,
      status: 500,
      data: { message: error.message || 'An error occurred during login' }
    };
  }
}

// Signup function using Supabase
async function signupUser(username, email, password, confirmPassword) {
  if (password !== confirmPassword) {
    return {
      success: false,
      status: 400,
      data: { message: 'Passwords do not match' }
    };
  }

  if (password.length < 6) {
    return {
      success: false,
      status: 400,
      data: { message: 'Password must be at least 6 characters' }
    };
  }

  try {
    const supabase = await getSupabaseClient();
    const { data, error } = await supabase.auth.signUp({
      email,
      password,
      options: {
        data: {
          username: username
        }
      }
    });

    if (error) {
      // Handle rate limit errors
      if (error.message?.includes('rate') || error.message?.includes('too many')) {
        return {
          success: false,
          status: 429,
          data: {
            message: 'Too many signup attempts. Please wait a few minutes before trying again.'
          }
        };
      }
      
      // Handle other errors
      return {
        success: false,
        status: 400,
        data: {
          message: error.message || 'Signup failed. Please try again.'
        }
      };
    }

    return {
      success: true,
      status: 201,
      data: {
        message: data.user?.identities?.length === 0 
          ? 'Account already exists with this email'
          : 'Account created successfully! Check your email for verification.',
        userId: data.user?.id,
        user: {
          id: data.user?.id,
          username: username,
          email: email
        }
      }
    };
  } catch (error) {
    console.error('Signup Error:', error);
    return {
      success: false,
      status: 500,
      data: { message: error.message || 'An error occurred during signup' }
    };
  }
}

// Logout function using Supabase
async function logoutUser() {
  try {
    const supabase = await getSupabaseClient();
    const { error } = await supabase.auth.signOut();
    
    if (error) {
      return {
        success: false,
        status: 500,
        data: { message: error.message || 'Logout failed. Please try again.' }
      };
    }

    return {
      success: true,
      status: 200,
      data: { message: 'Logged out successfully' }
    };
  } catch (error) {
    console.error('Logout Error:', error);
    return {
      success: false,
      status: 500,
      data: { message: error.message || 'An error occurred during logout' }
    };
  }
}

// Get current authenticated user from localStorage
async function getCurrentUser() {
  try {
    const storedUser = JSON.parse(localStorage.getItem('user') || 'null');

    if (!storedUser) {
      return { success: false, user: null };
    }

    return {
      success: true,
      user: {
        id: storedUser.id ?? null,
        email: storedUser.email ?? '',
        username: storedUser.username || storedUser.fullname || (storedUser.email ? storedUser.email.split('@')[0] : '')
      }
    };
  } catch (error) {
    console.error('Get User Error:', error.message);
    return { success: false, user: null };
  }
}

// Listen for auth state changes using storage events
function onAuthStateChange(callback) {
  const emitCurrentState = () => {
    const storedUser = JSON.parse(localStorage.getItem('user') || 'null');
    callback(storedUser ? 'SIGNED_IN' : 'SIGNED_OUT', storedUser);
  };

  window.addEventListener('storage', emitCurrentState);
  emitCurrentState();

  return {
    data: {
      subscription: {
        unsubscribe() {
          window.removeEventListener('storage', emitCurrentState);
        }
      }
    }
  };
}

// ==================== NON-AUTH FUNCTIONS (KEEP USING PHP BACKEND) ====================

// Contact function - unchanged (still uses PHP)
async function submitContact(name, email, subject, message) {
  return phpApiCall(API_ENDPOINTS.contact, 'POST', {
    name,
    email,
    subject,
    message
  });
}

// Get products function - unchanged (still uses PHP)
async function getProducts() {
  return phpApiCall(API_ENDPOINTS.products, 'GET');
}

// Get product reviews - unchanged (still uses PHP)
async function getProductReviews(productId) {
  try {
    const response = await fetch(`${API_ENDPOINTS.reviews.get}?product_id=${productId}`);
    const result = await response.json();
    return result;
  } catch (error) {
    console.error('Error fetching reviews:', error);
    return {
      success: false,
      message: 'Failed to load reviews'
    };
  }
}

// Add product review - unchanged (still uses PHP)
async function addProductReview(productId, userName, rating, comment) {
  return phpApiCall(API_ENDPOINTS.reviews.add, 'POST', {
    product_id: productId,
    user_name: userName,
    rating: rating,
    comment: comment
  });
}

// Expose helpers globally for plain browser script tags
window.loginUser = loginUser;
window.signupUser = signupUser;
window.logoutUser = logoutUser;
window.getCurrentUser = getCurrentUser;
window.onAuthStateChange = onAuthStateChange;
window.submitContact = submitContact;
window.getProducts = getProducts;
window.getProductReviews = getProductReviews;
window.addProductReview = addProductReview;