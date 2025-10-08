<?php
// /RADS-TOOLING/customer/homepage.php - Customer homepage with full access
require_once __DIR__ . '/../backend/config/app.php';
session_start();

// Ensure user is logged in as customer
$user = $_SESSION['user'] ?? null;
$isCustomer = $user && (($user['aud'] ?? '') === 'customer');

if (!$isCustomer) {
  header('Location: /RADS-TOOLING/customer/cust_login.php?next=' . urlencode($_SERVER['REQUEST_URI']));
  exit;
}

$customerName = htmlspecialchars($user['name'] ?? $user['username']);
$customerId = $user['id'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Rads Tooling - <?= $customerName ?></title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
  <link rel="stylesheet" href="/RADS-TOOLING/assets/CSS/Homepage.css" />
  <link rel="stylesheet" href="/RADS-TOOLING/assets/CSS/chat-widget.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
  <div class="page-wrapper">

    <!-- HEADER -->
    <header class="navbar">
      <div class="navbar-container">
        <div class="navbar-brand">
          <a href="/RADS-TOOLING/customer/homepage.php" class="logo-link">
            <span class="logo-text">R</span>ADS <span class="logo-text">T</span>OOLING
          </a>
        </div>

        <form class="search-container" action="/RADS-TOOLING/public/products.php" method="get">
          <input type="text" name="q" class="search-input" placeholder="Search cabinets..." />
          <button type="submit" class="search-btn" aria-label="Search">
            <span class="material-symbols-rounded">search</span>
          </button>
        </form>

        <div class="navbar-actions">
          <!-- Profile Dropdown -->
          <div class="profile-menu">
            <button class="profile-toggle" id="profileToggle" type="button">
              <div class="profile-avatar-wrapper">
                <div class="profile-avatar">
                  <?= strtoupper(substr($customerName, 0, 1)) ?>
                </div>
              </div>
              <div class="profile-info">
                <span class="profile-name"><?= $customerName ?></span>
                <span class="material-symbols-rounded dropdown-icon">expand_more</span>
              </div>
            </button>

            <div class="profile-dropdown" id="profileDropdown">
              <div class="profile-dropdown-header">
                <div class="dropdown-avatar">
                  <?= strtoupper(substr($customerName, 0, 1)) ?>
                </div>
                <div class="dropdown-user-info">
                  <div class="dropdown-name"><?= $customerName ?></div>
                  <div class="dropdown-email" id="userEmailDisplay">Loading...</div>
                </div>
              </div>
              <div class="dropdown-divider"></div>
              <a href="/RADS-TOOLING/customer/profile.php" class="dropdown-item">
                <span class="material-symbols-rounded">person</span>
                <span>My Profile</span>
              </a>
              <a href="/RADS-TOOLING/customer/orders.php" class="dropdown-item">
                <span class="material-symbols-rounded">receipt_long</span>
                <span>My Orders</span>
              </a>
              <a href="/RADS-TOOLING/customer/customizations.php" class="dropdown-item">
                <span class="material-symbols-rounded">palette</span>
                <span>My Designs</span>
              </a>
              <div class="dropdown-divider"></div>
              <button onclick="showLogoutModal()" class="dropdown-item dropdown-logout" type="button">
                <span class="material-symbols-rounded">logout</span>
                <span>Logout</span>
              </button>
            </div>
          </div>

          <!-- Cart -->
          <a href="/RADS-TOOLING/cart.php" class="cart-button">
            <span class="material-symbols-rounded">shopping_cart</span>
            <span id="cartCount" class="cart-badge">0</span>
          </a>
        </div>
      </div>

      <nav class="navbar-menu">
        <a href="/RADS-TOOLING/customer/homepage.php" class="nav-menu-item active">Home</a>
        <a href="/RADS-TOOLING/customer/about.php" class="nav-menu-item">About</a>
        <a href="/RADS-TOOLING/public/products.php" class="nav-menu-item">Products</a>
      </nav>
    </header>

    <main>
      <!-- WELCOME SECTION -->
      <section class="hero-section">
        <div class="hero-content-wrapper">
          <div class="hero-text-content">
            <h1>Welcome back, <span class="text-highlight"><?= $customerName ?></span>!</h1>
            <p class="hero-subtitle">Explore our latest cabinet designs and continue your projects</p>
            <div class="hero-actions">
              <a href="/RADS-TOOLING/customer/customize.php" class="btn-hero-primary">
                <span class="material-symbols-rounded">view_in_ar</span>
                <span>Start Designing</span>
              </a>
              <a href="/RADS-TOOLING/public/products.php" class="btn-hero-secondary">
                <span class="material-symbols-rounded">storefront</span>
                <span>Browse Products</span>
              </a>
            </div>
          </div>
          <div class="hero-image-content">
            <img src="/RADS-TOOLING/assets/images/cabinet-hero.jpg" alt="Custom Cabinets">
          </div>
        </div>
      </section>

      <!-- QUICK ACTIONS -->
      <section class="quick-actions-section">
        <h2 class="section-title">Quick Actions</h2>
        <div class="actions-grid">
          <a href="/RADS-TOOLING/customer/customize.php" class="action-card">
            <div class="action-icon" style="background: linear-gradient(135deg, #667eea, #764ba2);">
              <span class="material-symbols-rounded">view_in_ar</span>
            </div>
            <h3>Design Cabinet</h3>
            <p>Create custom 3D designs</p>
          </a>

          <a href="/RADS-TOOLING/public/products.php" class="action-card">
            <div class="action-icon" style="background: linear-gradient(135deg, #f093fb, #f5576c);">
              <span class="material-symbols-rounded">storefront</span>
            </div>
            <h3>Browse Gallery</h3>
            <p>Explore our collection</p>
          </a>

          <a href="/RADS-TOOLING/customer/orders.php" class="action-card">
            <div class="action-icon" style="background: linear-gradient(135deg, #4facfe, #00f2fe);">
              <span class="material-symbols-rounded">local_shipping</span>
            </div>
            <h3>Track Orders</h3>
            <p>View order status</p>
          </a>

          <a href="/RADS-TOOLING/cart.php" class="action-card">
            <div class="action-icon" style="background: linear-gradient(135deg, #43e97b, #38f9d7);">
              <span class="material-symbols-rounded">shopping_cart</span>
            </div>
            <h3>View Cart</h3>
            <p>Review checkout items</p>
          </a>
        </div>
      </section>

      <!-- FEATURED PRODUCTS -->
      <section class="featured-section">
        <div class="section-header-inline">
          <h2 class="section-title">Recommended For You</h2>
          <a href="/RADS-TOOLING/public/products.php" class="view-all-link">
            <span>View All</span>
            <span class="material-symbols-rounded">arrow_forward</span>
          </a>
        </div>
        <div class="products-grid" id="recommendedProducts">
          <div class="loading-state">
            <span class="material-symbols-rounded spinning">progress_activity</span>
            <span>Loading products...</span>
          </div>
        </div>
      </section>

      <!-- RADS-TOOLING Chat Support Widget -->
      <button id="rtChatBtn" class="rt-chat-btn">
        <span class="material-symbols-rounded">chat</span>
        Need Help?
      </button>

      <div id="rtChatPopup" class="rt-chat-popup">
        <div class="rt-chat-header">
          <span>Rads Tooling - Chat Support</span>
          <button id="rtClearChat" class="rt-clear-btn" type="button" title="Clear chat">
            <span class="material-symbols-rounded">delete</span>
          </button>
        </div>

        <!-- FAQ Container (Sticky at top) -->
        <div class="rt-faq-container" id="rtFaqContainer">
          <div class="rt-faq-toggle" id="rtFaqToggle">
            <span>Quick FAQs</span>
            <span class="rt-faq-icon">▼</span>
          </div>
          <div class="rt-faq-dropdown" id="rtFaqDropdown">
            <!-- FAQ chips will be injected here by chat_widget.js -->
            <div style="padding: 12px; color: #999; font-size: 13px;">Loading FAQs...</div>
          </div>
        </div>

        <!-- Messages Area -->
        <div id="rtChatMessages" class="rt-chat-messages"></div>

        <!-- Input Area -->
        <div class="rt-chat-input">
          <input id="rtChatInput" type="text" placeholder="Type your message…" />
          <button id="rtChatSend" class="rt-chat-send">
            <span class="material-symbols-rounded">send</span>
          </button>
        </div>
      </div>

      <!-- LOGOUT MODAL (Admin Style) -->
      <div class="modal" id="logoutModal" style="display:none;">
        <div class="modal-content modal-small">
          <button class="modal-close" onclick="closeLogoutModal()" type="button">
            <span class="material-symbols-rounded">close</span>
          </button>
          <div class="modal-icon-wrapper">
            <div class="modal-icon warning">
              <span class="material-symbols-rounded">logout</span>
            </div>
          </div>
          <h2 class="modal-title">Confirm Logout</h2>
          <p class="modal-message">Are you sure you want to logout?</p>
          <div class="modal-actions">
            <button onclick="closeLogoutModal()" class="btn-modal-secondary" type="button">Cancel</button>
            <button onclick="confirmLogout()" class="btn-modal-primary" type="button">Logout</button>
          </div>
        </div>
      </div>
    </main>

    <!-- FOOTER -->
    <footer class="footer">
      <div class="footer-content">
        <!-- About Section -->
        <div class="footer-section">
          <h3>About RADS TOOLING</h3>
          <p class="footer-description">
            Premium custom cabinet manufacturer serving clients since 2007.
            Quality craftsmanship, affordable prices, and exceptional service.
          </p>
          <div class="footer-social">
            <a href="#" class="social-icon" aria-label="Facebook">
              <span class="material-symbols-rounded">facebook</span>
            </a>
            <a href="#" class="social-icon" aria-label="Instagram">
              <span class="material-symbols-rounded">photo_camera</span>
            </a>
            <a href="mailto:RadsTooling@gmail.com" class="social-icon" aria-label="Email">
              <span class="material-symbols-rounded">mail</span>
            </a>
          </div>
        </div>

        <!-- Quick Links -->
        <div class="footer-section">
          <h3>Quick Links</h3>
          <ul class="footer-links">
            <li><a href="/RADS-TOOLING/customer/about.php">About Us</a></li>
            <li><a href="/RADS-TOOLING/public/products.php">Products</a></li>
          </ul>
        </div>

        <!-- Categories -->
        <div class="footer-section">
          <h3>Categories</h3>
          <ul class="footer-links">
            <li><a href="/RADS-TOOLING/public/products.php?type=Kitchen">Kitchen</a></li>
            <li><a href="/RADS-TOOLING/public/products.php?type=Bedroom">Bedroom</a></li>
            <li><a href="/RADS-TOOLING/public/products.php?type=Living Room">Living Room</a></li>
            <li><a href="/RADS-TOOLING/public/products.php?type=Bathroom">Bathroom</a></li>
            <li><a href="/RADS-TOOLING/public/products.php?type=Commercial">Commercial</a></li>
          </ul>
        </div>

        <!-- Contact Info -->
        <div class="footer-section">
          <h3>Contact Info</h3>
          <div class="contact-info-item">
            <span class="material-symbols-rounded">location_on</span>
            <span>Green Breeze, Piela, Dasmariñas, Cavite</span>
          </div>
          <div class="contact-info-item">
            <span class="material-symbols-rounded">mail</span>
            <a href="mailto:RadsTooling@gmail.com">RadsTooling@gmail.com</a>
          </div>
          <div class="contact-info-item">
            <span class="material-symbols-rounded">schedule</span>
            <span>Mon-Sat: 8:00 AM - 5:00 PM</span>
          </div>
        </div>
      </div>

      <div class="footer-bottom">
        <p class="footer-copyright">
          © 2025 RADS TOOLING INC. All rights reserved.
        </p>
        <div class="footer-legal">
          <a href="/RADS-TOOLING/customer/privacy.php">Privacy Policy</a>
          <a href="/RADS-TOOLING/customer/terms.php">Terms & Conditions</a>
        </div>
      </div>
    </footer>

  </div><!-- /.page-wrapper -->

  <script>
    // ========== INITIALIZE ON PAGE LOAD ==========
    document.addEventListener('DOMContentLoaded', function() {
      initProfileDropdown();
      loadUserEmail();
      loadUserStatistics();
      loadRecentOrders();
      loadRecommendedProducts();
      updateCartCount();
    });

    // ========== PROFILE DROPDOWN TOGGLE ==========
    function initProfileDropdown() {
      const profileToggle = document.getElementById('profileToggle');
      const profileDropdown = document.getElementById('profileDropdown');

      if (!profileToggle || !profileDropdown) {
        console.error('Profile elements not found');
        return;
      }

      profileToggle.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        profileDropdown.classList.toggle('show');
      });

      document.addEventListener('click', function(e) {
        if (!profileToggle.contains(e.target) && !profileDropdown.contains(e.target)) {
          profileDropdown.classList.remove('show');
        }
      });

      profileDropdown.querySelectorAll('a, button').forEach(item => {
        item.addEventListener('click', function() {
          profileDropdown.classList.remove('show');
        });
      });
    }

    // ========== LOAD USER EMAIL ==========
    async function loadUserEmail() {
      try {
        const response = await fetch('/RADS-TOOLING/backend/api/customer_profile.php?action=get_profile', {
          credentials: 'same-origin',
          headers: {
            'Accept': 'application/json'
          }
        });

        if (!response.ok) throw new Error('Network response failed');

        const result = await response.json();

        if (result.success && result.data && result.data.email) {
          const emailDisplay = document.getElementById('userEmailDisplay');
          if (emailDisplay) {
            emailDisplay.textContent = result.data.email;
          }
        }
      } catch (error) {
        console.error('Failed to load user email:', error);
      }
    }

    // ========== LOAD USER STATISTICS ==========
    async function loadUserStatistics() {
      try {
        const response = await fetch('/RADS-TOOLING/backend/api/customer_stats.php', {
          credentials: 'same-origin'
        });
        const data = await response.json();

        if (data.success) {
          document.getElementById('totalOrders').textContent = data.stats.total || 0;
          document.getElementById('pendingOrders').textContent = data.stats.pending || 0;
          document.getElementById('completedOrders').textContent = data.stats.completed || 0;
        } else {
          document.getElementById('totalOrders').textContent = '0';
          document.getElementById('pendingOrders').textContent = '0';
          document.getElementById('completedOrders').textContent = '0';
        }
      } catch (err) {
        console.error('Failed to load stats:', err);
        document.getElementById('totalOrders').textContent = '0';
        document.getElementById('pendingOrders').textContent = '0';
        document.getElementById('completedOrders').textContent = '0';
      }
    }

    // ========== LOAD RECENT ORDERS ==========
    async function loadRecentOrders() {
      const ordersContainer = document.getElementById('recentOrdersContainer');
      if (!ordersContainer) return;

      try {
        const response = await fetch('/RADS-TOOLING/backend/api/recent_orders.php?limit=3', {
          credentials: 'same-origin'
        });
        const data = await response.json();

        if (data.success && data.orders.length > 0) {
          ordersContainer.innerHTML = data.orders.map(order => `
        <div class="order-item">
          <div class="order-info">
            <h4>Order #${escapeHtml(order.order_code)}</h4>
            <p>${escapeHtml(order.product_name || 'Custom Cabinet')} - ₱${parseFloat(order.total_amount).toLocaleString()}</p>
            <p style="font-size:0.85rem;color:#999;">${formatDate(order.order_date)}</p>
          </div>
          <span class="order-status ${order.status.toLowerCase().replace(' ', '-')}">
            ${escapeHtml(order.status)}
          </span>
        </div>
      `).join('');
        } else {
          ordersContainer.innerHTML = '<p style="text-align:center;color:#666;padding:40px;">No orders yet. <a href="/RADS-TOOLING/customer/customize.php" style="color:#1f4e74;font-weight:600;">Start designing</a>!</p>';
        }
      } catch {
        ordersContainer.innerHTML = '<p style="text-align:center;color:#dc3545;padding:40px;">Failed to load orders</p>';
      }
    }

    // ========== LOGOUT MODAL ==========
    function showLogoutModal() {
      const modal = document.getElementById('logoutModal');
      if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
      }
      const dropdown = document.getElementById('profileDropdown');
      if (dropdown) dropdown.classList.remove('show');
    }

    function closeLogoutModal() {
      const modal = document.getElementById('logoutModal');
      if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
      }
    }

    async function confirmLogout() {
      try {
        // Stop chat polling and clear active chat UI only
        document.dispatchEvent(new Event('customer_logout'));

        await fetch('/RADS-TOOLING/backend/api/auth.php?action=logout', {
          method: 'POST',
          credentials: 'same-origin'
        });

        // Clear ONLY session-related data, NOT cart
        sessionStorage.clear(); //clears temporary UI state only

        // IMPORTANT: Do NOT clear localStorage cart - it will reload from database on next login

        window.location.href = '/RADS-TOOLING/public/index.php';

      } catch (error) {
        console.error('Logout error:', error);
        sessionStorage.clear();
        window.location.href = '/RADS-TOOLING/public/index.php';
      }
    }

    // ========== LOAD PRODUCTS ==========
    async function loadRecommendedProducts() {
      const productsContainer = document.getElementById('recommendedProducts');
      if (!productsContainer) return;

      try {
        const response = await fetch('/RADS-TOOLING/backend/api/products.php?action=list&limit=4', {
          credentials: 'same-origin',
          headers: {
            'Accept': 'application/json'
          }
        });

        const result = await response.json();

        if (result.success && result.data && result.data.products && result.data.products.length > 0) {
          productsContainer.innerHTML = result.data.products.map(product => `
        <a href="/RADS-TOOLING/public/product_detail.php?id=${product.id}" class="product-card">
          <div class="product-image">
            <img src="/RADS-TOOLING/${product.image || 'assets/images/placeholder.jpg'}" 
                 alt="${escapeHtml(product.name)}"
                 onerror="this.src='/RADS-TOOLING/assets/images/placeholder.jpg'">
          </div>
          <div class="product-info">
            <h3>${escapeHtml(product.name)}</h3>
            <p class="product-type">${escapeHtml(product.type || 'Cabinet')}</p>
            <p class="product-price">₱${parseFloat(product.price || 0).toLocaleString()}</p>
          </div>
        </a>
      `).join('');
        } else {
          productsContainer.innerHTML = '<div class="loading-state">No products available</div>';
        }
      } catch (error) {
        console.error('Failed to load products:', error);
        productsContainer.innerHTML = '<div class="loading-state">Failed to load products</div>';
      }
    }

    // ========== CART COUNT ==========
    function updateCartCount() {
      const cart = JSON.parse(localStorage.getItem('cart') || '[]');
      const cartCount = document.getElementById('cartCount');
      if (cartCount) {
        cartCount.textContent = cart.length;
      }
    }

    // ========== UTILITY FUNCTIONS ==========
    function escapeHtml(text) {
      const div = document.createElement('div');
      div.textContent = text;
      return div.innerHTML;
    }

    function formatDate(dateStr) {
      const date = new Date(dateStr);
      return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      });
    }
  </script>

  <script src="/RADS-TOOLING/assets/JS/chat_widget.js"></script>
  <script src="/RADS-TOOLING/assets/js/policy.js"></script>
</body>

</html>