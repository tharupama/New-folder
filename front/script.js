console.log("script.js loaded");

const products = [];

// Initialize state with localStorage support
const initializeState = () => {
  // Load cart from localStorage
  const savedCart = localStorage.getItem("buyLkCart");
  const cartData = savedCart ? JSON.parse(savedCart) : {};
  // Convert string keys back to numbers
  const cartEntries = Object.entries(cartData).map(([key, value]) => [parseInt(key), value]);
  const cart = new Map(cartEntries);
  
  // Load wishlist from localStorage
  const savedWishlist = localStorage.getItem("buyLkWishlist");
  const wishlistData = savedWishlist ? JSON.parse(savedWishlist) : [];
  const wishlist = new Set(wishlistData);
  
  return {
    cart: cart,
    wishlist: wishlist,
    search: "",
    category: "all",
    sort: "featured",
    theme: localStorage.getItem("theme") || "light",
    stock: 72,
    user: JSON.parse(localStorage.getItem("user") || "null"),
  };
};

const state = initializeState();

// Save cart to localStorage
const saveCart = () => {
  const cartObj = Object.fromEntries(state.cart);
  localStorage.setItem("buyLkCart", JSON.stringify(cartObj));
};

// Save wishlist to localStorage
const saveWishlist = () => {
  const wishlistArray = Array.from(state.wishlist);
  localStorage.setItem("buyLkWishlist", JSON.stringify(wishlistArray));
};

// Load products from database
async function loadProducts() {
  try {
    console.log("Starting to load products...");
    const response = await getProducts();
    console.log("API Response:", response);
    if (response.success && Array.isArray(response.data)) {
      products.length = 0; // Clear array
      // Convert string values to proper types
      const convertedProducts = response.data.map(product => ({
        ...product,
        price: parseFloat(product.price),
        rating: parseFloat(product.rating),
        stock: parseInt(product.stock)
      }));
      console.log("Converted products:", convertedProducts);
      products.push(...convertedProducts); // Add products from database
      console.log("Products array after push:", products);
      applyFilters(); // Render products
      renderCart(); // Re-render cart with loaded products
      console.log("applyFilters called");
    } else {
      console.error("Failed to load products: Invalid response structure");
      showToast("Failed to load products");
    }
  } catch (error) {
    console.error("Error loading products:", error);
    showToast("Error loading products");
  }
}

const productGrid = document.getElementById("productGrid");
const cartCount = document.getElementById("cartCount");
const wishlistCount = document.getElementById("wishlistCount");
const cartDrawer = document.getElementById("cartDrawer");
const cartItems = document.getElementById("cartItems");
const cartSubtotal = document.getElementById("cartSubtotal");
const cartShipping = document.getElementById("cartShipping");
const cartTotal = document.getElementById("cartTotal");
const toast = document.getElementById("toast");
const searchInput = document.getElementById("searchInput");
const searchClear = document.getElementById("searchClear");
const categoryFilter = document.getElementById("categoryFilter");
const sortFilter = document.getElementById("sortFilter");
const countdown = document.getElementById("countdown");
const stockProgress = document.getElementById("stockProgress");
const newsletterForm = document.getElementById("newsletterForm");
const newsletterMsg = document.getElementById("newsletterMsg");

const currency = new Intl.NumberFormat("en-US", {
  style: "currency",
  currency: "USD",
});

const showToast = (message) => {
  if (!toast) return;
  toast.textContent = message;
  toast.classList.add("show");
  setTimeout(() => toast.classList.remove("show"), 2400);
};

const setTheme = (theme) => {
  document.documentElement.dataset.theme = theme;
  state.theme = theme;
  localStorage.setItem("theme", theme);
  const toggle = document.getElementById("themeToggle");
  if (toggle) {
    toggle.textContent = theme === "dark" ? "☀️" : "🌙";
  }
};

const applyFilters = () => {
  let filtered = [...products];

  if (state.search) {
    const keyword = state.search.toLowerCase();
    filtered = filtered.filter((product) =>
      product.name.toLowerCase().includes(keyword)
    );
  }

  if (state.category !== "all") {
    filtered = filtered.filter((product) => product.category === state.category);
  }

  if (state.sort === "price-asc") {
    filtered.sort((a, b) => a.price - b.price);
  } else if (state.sort === "price-desc") {
    filtered.sort((a, b) => b.price - a.price);
  } else if (state.sort === "rating") {
    filtered.sort((a, b) => b.rating - a.rating);
  }

  if (productGrid) {
    renderProducts(filtered);
  }
};

const renderProducts = (items) => {
  if (!productGrid) return;
  productGrid.innerHTML = items
    .map(
      (product) => `
    <article class="product-card" data-id="${product.id}">
      <span class="tag">${product.tag}</span>
      <img src="${product.image}" alt="${product.name}" />
      <h4>${product.name}</h4>
      <div class="product-meta">
        <span class="rating">★ ${product.rating}</span>
        <span class="price">${currency.format(product.price)}</span>
      </div>
      <div class="stock-status" style="font-size: 0.85rem; margin: 4px 0; font-weight: 500; color: ${product.is_available == 1 ? '#10b981' : '#ef4444'};">
        ${product.is_available == 1 ? '✓ In Stock' : '✗ Out of Stock'}
      </div>
      <div class="product-actions">
        <button class="ghost-btn" data-action="wishlist">${
          state.wishlist.has(product.id) ? "Saved" : "Wishlist"
        }</button>
        <button class="primary-btn" data-action="cart" ${product.is_available == 0 ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : ''}>
          ${product.is_available == 0 ? 'Unavailable' : 'Add'}
        </button>
      </div>
    </article>`
    )
    .join("");
};

const updateCounts = () => {
  if (cartCount) {
    cartCount.textContent = [...state.cart.values()].reduce(
    (sum, qty) => sum + qty,
    0
    );
  }
  if (wishlistCount) {
    wishlistCount.textContent = state.wishlist.size;
  }
};

const renderCart = () => {
  const entries = [...state.cart.entries()];
  if (!cartItems || !cartSubtotal || !cartShipping || !cartTotal) return;

  if (entries.length === 0) {
    cartItems.innerHTML = "<p>Your cart is empty.</p>";
    cartSubtotal.textContent = currency.format(0);
    cartShipping.textContent = currency.format(0);
    cartTotal.textContent = currency.format(0);
    return;
  }

  let subtotal = 0;
  cartItems.innerHTML = entries
    .map(([id, qty]) => {
      const product = products.find((item) => {
        // Handle both string and number comparisons
        return item.id == id || item.id === parseInt(id);
      });
      
      if (!product) {
        console.warn("Product not found for cart item:", id, "Available products:", products.length);
        return "";
      }
      
      const lineTotal = product.price * qty;
      subtotal += lineTotal;
      return `
      <div class="cart-item">
        <div>
          <h5>${product.name}</h5>
          <p>${currency.format(product.price)} · ${product.tag}</p>
        </div>
        <div class="cart-item-controls">
          <button class="icon-btn" data-cart-action="decrease" data-id="${id}">-</button>
          <span>${qty}</span>
          <button class="icon-btn" data-cart-action="increase" data-id="${id}">+</button>
        </div>
      </div>
      `;
    })
    .filter(html => html !== "") // Remove empty items
    .join("");

  const shipping = subtotal > 200 ? 0 : 12;
  cartSubtotal.textContent = currency.format(subtotal);
  cartShipping.textContent = currency.format(shipping);
  cartTotal.textContent = currency.format(subtotal + shipping);
};

const toggleCart = () => {
  if (!cartDrawer) return;
  cartDrawer.classList.toggle("open");
};

const updateCountdown = () => {
  const now = new Date();
  const target = new Date();
  target.setHours(now.getHours() + 2);
  const diff = target - now;
  if (diff <= 0) return;

  const hours = String(Math.floor(diff / 1000 / 60 / 60)).padStart(2, "0");
  const mins = String(Math.floor((diff / 1000 / 60) % 60)).padStart(2, "0");
  const secs = String(Math.floor((diff / 1000) % 60)).padStart(2, "0");
  if (countdown) {
    countdown.textContent = `${hours}:${mins}:${secs}`;
  }
};

const updateStock = () => {
  state.stock = Math.max(12, state.stock - Math.floor(Math.random() * 6));
  if (stockProgress) {
    stockProgress.style.width = `${state.stock}%`;
  }
};

if (productGrid) {
  productGrid.addEventListener("click", (event) => {
    const card = event.target.closest(".product-card");
    if (!card) return;

    const id = Number(card.dataset.id);
    if (event.target.dataset.action === "wishlist") {
      if (state.wishlist.has(id)) {
        state.wishlist.delete(id);
        showToast("Removed from wishlist");
      } else {
        state.wishlist.add(id);
        showToast("Saved to wishlist");
      }
      saveWishlist();
      applyFilters();
      updateCounts();
    }

    if (event.target.dataset.action === "cart") {
      // Check if product is available before adding to cart
      const product = products.find(p => p.id === id);
      if (product && product.is_available == 0) {
        showToast("This item is currently out of stock");
        return;
      }
      state.cart.set(id, (state.cart.get(id) || 0) + 1);
      saveCart();
      showToast("Added to cart");
      updateCounts();
      renderCart();
    }
  });
}

if (cartItems) {
  cartItems.addEventListener("click", (event) => {
    const id = Number(event.target.dataset.id);
    if (!id) return;

    if (event.target.dataset.cartAction === "increase") {
      state.cart.set(id, (state.cart.get(id) || 0) + 1);
    }

    if (event.target.dataset.cartAction === "decrease") {
      const current = state.cart.get(id) || 0;
      if (current <= 1) {
        state.cart.delete(id);
      } else {
        state.cart.set(id, current - 1);
      }
    }

    saveCart();
    updateCounts();
    renderCart();
  });
}

if (searchInput) {
  searchInput.addEventListener("input", (event) => {
    state.search = event.target.value;
    applyFilters();
  });
}

if (searchClear && searchInput) {
  searchClear.addEventListener("click", () => {
    searchInput.value = "";
    state.search = "";
    applyFilters();
  });
}

if (categoryFilter) {
  categoryFilter.addEventListener("change", (event) => {
    state.category = event.target.value;
    applyFilters();
  });
}

if (sortFilter) {
  sortFilter.addEventListener("change", (event) => {
    state.sort = event.target.value;
    applyFilters();
  });
}

Array.from(document.querySelectorAll(".category-grid button")).forEach((btn) => {
  btn.addEventListener("click", () => {
    state.category = btn.dataset.category;
    if (categoryFilter) {
      categoryFilter.value = state.category;
    }
    applyFilters();
  });
});

if (newsletterForm) {
  newsletterForm.addEventListener("submit", async (event) => {
    event.preventDefault();
    const email = document.getElementById("newsletterEmail")?.value.trim();
    
    if (!email) {
      if (newsletterMsg) {
        newsletterMsg.textContent = "Please enter your email.";
        newsletterMsg.style.color = "#ef4444";
      }
      return;
    }

    if (!email.includes("@")) {
      if (newsletterMsg) {
        newsletterMsg.textContent = "Please enter a valid email.";
        newsletterMsg.style.color = "#ef4444";
      }
      return;
    }

    try {
      // Submit as contact message if we're on contact page, otherwise just show thanks
      const isContactPage = window.location.pathname.includes("contact");
      
      if (isContactPage && typeof submitContact !== "undefined") {
        const response = await submitContact(
          "Newsletter Signup",
          email,
          "Newsletter Subscription",
          "User subscribed to newsletter"
        );
        
        if (response.success) {
          if (newsletterMsg) {
            newsletterMsg.textContent = "Thanks! You are subscribed.";
            newsletterMsg.style.color = "#10b981";
          }
        } else {
          if (newsletterMsg) {
            newsletterMsg.textContent = response.data.message || "Subscription failed. Try again.";
            newsletterMsg.style.color = "#ef4444";
          }
        }
      } else {
        if (newsletterMsg) {
          newsletterMsg.textContent = "Thanks! You are subscribed.";
          newsletterMsg.style.color = "#10b981";
        }
      }
      
      newsletterForm.reset();
    } catch (error) {
      console.error("Newsletter subscription error:", error);
      if (newsletterMsg) {
        newsletterMsg.textContent = "An error occurred. Please try again.";
        newsletterMsg.style.color = "#ef4444";
      }
    }
  });
}

// Contact form handler
const contactForm = document.getElementById("contactForm");
if (contactForm) {
  contactForm.addEventListener("submit", async (event) => {
    event.preventDefault();
    const name = document.getElementById("contactName")?.value.trim();
    const email = document.getElementById("contactEmail")?.value.trim();
    const subject = document.getElementById("contactSubject")?.value.trim() || "Contact Form";
    const message = document.getElementById("contactMessage")?.value.trim();
    const contactMsg = document.getElementById("contactMsg");

    if (!name || !email || !message) {
      if (contactMsg) {
        contactMsg.textContent = "Please fill in all required fields.";
        contactMsg.style.color = "#ef4444";
      }
      return;
    }

    if (!email.includes("@")) {
      if (contactMsg) {
        contactMsg.textContent = "Please enter a valid email address.";
        contactMsg.style.color = "#ef4444";
      }
      return;
    }

    // Show loading state
    const submitBtn = contactForm.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = "Sending...";
    submitBtn.disabled = true;

    try {
      if (typeof submitContact !== "undefined") {
        const response = await submitContact(name, email, subject, message);
        
        if (response.success) {
          if (contactMsg) {
            contactMsg.textContent = "Thank you! Your message has been sent successfully.";
            contactMsg.style.color = "#10b981";
          }
          contactForm.reset();
        } else {
          if (contactMsg) {
            contactMsg.textContent = response.data.message || "Failed to send message. Please try again.";
            contactMsg.style.color = "#ef4444";
          }
        }
      }
    } catch (error) {
      console.error("Contact form error:", error);
      if (contactMsg) {
        contactMsg.textContent = "An error occurred. Please try again.";
        contactMsg.style.color = "#ef4444";
      }
    } finally {
      submitBtn.textContent = originalText;
      submitBtn.disabled = false;
    }
  });
}

const cartBtn = document.getElementById("cartBtn");
if (cartBtn) {
  cartBtn.addEventListener("click", toggleCart);
}

const closeCart = document.getElementById("closeCart");
if (closeCart) {
  closeCart.addEventListener("click", toggleCart);
}

const menuToggle = document.getElementById("menuToggle");
if (menuToggle) {
  menuToggle.addEventListener("click", () => {
    menuToggle.classList.toggle("active");
    document.body.classList.toggle("mobile-menu-open");
  });

  const navLinks = document.querySelectorAll(".nav a");
  navLinks.forEach((link) => {
    link.addEventListener("click", () => {
      menuToggle.classList.remove("active");
      document.body.classList.remove("mobile-menu-open");
    });
  });
}

const themeToggle = document.getElementById("themeToggle");
if (themeToggle) {
  themeToggle.addEventListener("click", () => {
    setTheme(state.theme === "light" ? "dark" : "light");
  });
}

const shopNow = document.getElementById("shopNow");
if (shopNow) {
  shopNow.addEventListener("click", () => {
    const featured = document.getElementById("featured");
    if (featured) {
      featured.scrollIntoView({ behavior: "smooth" });
    }
  });
}

const viewCollections = document.getElementById("viewCollections");
if (viewCollections) {
  viewCollections.addEventListener("click", () => {
    const categories = document.getElementById("categories");
    if (categories) {
      categories.scrollIntoView({ behavior: "smooth" });
    }
  });
}

const bundleDeal = document.getElementById("bundleDeal");
if (bundleDeal) {
  bundleDeal.addEventListener("click", () => {
    state.cart.set(1, (state.cart.get(1) || 0) + 1);
    state.cart.set(8, (state.cart.get(8) || 0) + 1);
    saveCart();
    showToast("Bundle added to cart");
    updateCounts();
    renderCart();
  });
}

// ===== PAYMENT MODAL SYSTEM =====
function setupPaymentSystem() {
  console.log("Setting up payment system...");
  
  const paymentModal = document.getElementById("paymentModal");
  const closePaymentBtn = document.getElementById("closePayment");
  const paymentForm = document.getElementById("paymentForm");
  const checkoutBtn = document.getElementById("checkoutBtn");
  
  if (!paymentModal) {
    console.error("Payment modal element not found");
    return false;
  }
  
  // Open payment modal
  function openPayment() {
    console.log("Opening payment modal");
    if (state.cart.size === 0) {
      showToast("Your cart is empty");
      return;
    }
    
    // Calculate totals
    let subtotal = 0;
    state.cart.forEach((qty, productId) => {
      const product = products.find(p => p.id == productId);
      if (product) {
        subtotal += product.price * qty;
      }
    });
    
    const shipping = subtotal >= 200 ? 0 : 9.99;
    const total = subtotal + shipping;
    
    // Update amounts
    document.getElementById("paymentSubtotal").textContent = currency.format(subtotal);
    document.getElementById("paymentShipping").textContent = currency.format(shipping);
    document.getElementById("paymentTotal").textContent = currency.format(total);
    
    // Show modal
    paymentModal.classList.add("show");
    document.body.style.overflow = "hidden";
    console.log("Payment modal is now visible");
  }
  
  // Close payment modal
  function closePayment() {
    console.log("Closing payment modal");
    paymentModal.classList.remove("show");
    document.body.style.overflow = "auto";
  }
  
  // Checkout button click
  if (checkoutBtn) {
    checkoutBtn.addEventListener("click", openPayment);
    console.log("Checkout button listener added");
  }
  
  // Close button click
  if (closePaymentBtn) {
    closePaymentBtn.addEventListener("click", closePayment);
  }
  
  // Close on background click
  paymentModal.addEventListener("click", (e) => {
    if (e.target === paymentModal) {
      closePayment();
    }
  });
  
  // Payment method toggle
  const cardDetails = document.getElementById("cardDetails");
  const paymentMethodInputs = document.querySelectorAll('input[name="paymentMethod"]');
  paymentMethodInputs.forEach(input => {
    input.addEventListener("change", (e) => {
      if (cardDetails) {
        cardDetails.style.display = e.target.value === "card" ? "grid" : "none";
      }
    });
  });
  
  // Form submission
  if (paymentForm) {
    paymentForm.addEventListener("submit", (e) => {
      e.preventDefault();
      console.log("Payment form submitted");
      
      const submitBtn = paymentForm.querySelector('button[type="submit"]');
      const originalText = submitBtn.textContent;
      
      submitBtn.textContent = "Processing...";
      submitBtn.disabled = true;
      
      setTimeout(() => {
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
        
        // Clear cart
        state.cart.clear();
        saveCart();
        updateCounts();
        renderCart();
        closePayment();
        
        // Close cart drawer if open
        const cartDrawerEl = document.getElementById("cartDrawer");
        if (cartDrawerEl && cartDrawerEl.classList.contains("open")) {
          cartDrawerEl.classList.remove("open");
        }
        
        paymentForm.reset();
        showToast("✓ Payment successful! Thank you for your purchase.");
      }, 2000);
    });
  }
  
  console.log("Payment system setup complete");
  return true;
}

// Setup on document ready
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", setupPaymentSystem);
} else {
  setupPaymentSystem();
}

setTheme(state.theme);
loadProducts(); // Load products from database (includes renderCart)
updateCounts();
updateCountdown();
updateStock();

setInterval(updateCountdown, 1000);
setInterval(updateStock, 4000);

// Authentication UI update
const updateAuthUI = () => {
  const authLinks = document.getElementById("authLinks");
  const userMenu = document.getElementById("userMenu");
  const userBtn = document.getElementById("userBtn");

  if (state.user && state.user.loggedIn) {
    if (authLinks) authLinks.style.display = "none";
    if (userMenu) userMenu.style.display = "flex";
    if (userBtn) {
      const userName = state.user.fullname || state.user.email.split("@")[0];
      userBtn.textContent = `👤 ${userName}`;
    }
  } else {
    if (authLinks) authLinks.style.display = "flex";
    if (userMenu) userMenu.style.display = "none";
  }
};

const logoutBtn = document.getElementById("logoutBtn");
if (logoutBtn) {
  logoutBtn.addEventListener("click", async () => {
    try {
      // Call logout API if available
      if (typeof logoutUser !== "undefined") {
        await logoutUser();
      }
    } catch (error) {
      console.error("Logout error:", error);
    }
    
    // Clear local storage and update UI
    localStorage.removeItem("user");
    state.user = null;
    updateAuthUI();
    showToast("Logged out successfully");
    setTimeout(() => {
      window.location.href = "index.html";
    }, 1000);
  });
}

updateAuthUI();
