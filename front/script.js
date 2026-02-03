const products = [
  {
    id: 1,
    name: "Aurora Smart Speaker",
    category: "tech",
    price: 129.99,
    rating: 4.7,
    tag: "Bestseller",
    image: "https://images.unsplash.com/photo-1512446816042-444d641267d4?auto=format&fit=crop&w=800&q=80",
  },
  {
    id: 2,
    name: "Nimbus Air Purifier",
    category: "home",
    price: 249.0,
    rating: 4.9,
    tag: "Eco",
    image: "https://images.unsplash.com/photo-1581578731548-c64695cc6952?auto=format&fit=crop&w=800&q=80",
  },
  {
    id: 3,
    name: "Pulse Fitness Band",
    category: "fitness",
    price: 89.5,
    rating: 4.6,
    tag: "New",
    image: "https://images.unsplash.com/photo-1518441902117-fc8b0c201d19?auto=format&fit=crop&w=800&q=80",
  },
  {
    id: 4,
    name: "Lumen Desk Lamp",
    category: "home",
    price: 58.0,
    rating: 4.4,
    tag: "Limited",
    image: "https://images.unsplash.com/photo-1481277542470-605612bd2d61?auto=format&fit=crop&w=800&q=80",
  },
  {
    id: 5,
    name: "Stride Eco Sneakers",
    category: "fashion",
    price: 145.0,
    rating: 4.5,
    tag: "Trending",
    image: "https://images.unsplash.com/photo-1525966222134-fcfa99b8ae77?auto=format&fit=crop&w=800&q=80",
  },
  {
    id: 6,
    name: "Vista Travel Backpack",
    category: "fashion",
    price: 110.0,
    rating: 4.3,
    tag: "Travel",
    image: "https://images.unsplash.com/photo-1514474959185-1472d4e46f4e?auto=format&fit=crop&w=800&q=80",
  },
  {
    id: 7,
    name: "Halo Aroma Diffuser",
    category: "home",
    price: 64.99,
    rating: 4.8,
    tag: "Relax",
    image: "https://images.unsplash.com/photo-1501004318641-b39e6451bec6?auto=format&fit=crop&w=800&q=80",
  },
  {
    id: 8,
    name: "Zen Smart Watch",
    category: "tech",
    price: 219.0,
    rating: 4.7,
    tag: "Editor pick",
    image: "https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?auto=format&fit=crop&w=800&q=80",
  },
];

const state = {
  cart: new Map(),
  wishlist: new Set(),
  search: "",
  category: "all",
  sort: "featured",
  theme: localStorage.getItem("theme") || "light",
  stock: 72,
  user: JSON.parse(localStorage.getItem("user") || "null"),
};

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
      <div class="product-actions">
        <button class="ghost-btn" data-action="wishlist">${
          state.wishlist.has(product.id) ? "Saved" : "Wishlist"
        }</button>
        <button class="primary-btn" data-action="cart">Add</button>
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
      const product = products.find((item) => item.id === id);
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
      applyFilters();
      updateCounts();
    }

    if (event.target.dataset.action === "cart") {
      state.cart.set(id, (state.cart.get(id) || 0) + 1);
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
    showToast("Bundle added to cart");
    updateCounts();
    renderCart();
  });
}

const checkoutBtn = document.getElementById("checkoutBtn");
if (checkoutBtn) {
  checkoutBtn.addEventListener("click", () => {
    showToast("Checkout coming soon");
  });
}

setTheme(state.theme);
applyFilters();
updateCounts();
renderCart();
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
