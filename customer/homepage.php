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
        <a href="/RADS-TOOLING/public/about.php" class="nav-menu-item">About</a>
        <a href="/RADS-TOOLING/public/products.php" class="nav-menu-item">Products</a>
        <a href="/RADS-TOOLING/customer/orders.php" class="nav-menu-item">Orders</a>
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
            <img src="/RADS-TOOLING/assets/images/cabinet-hero.jpg" alt="Custom Cabinets"
              onerror="this.src='https://via.placeholder.com/500x400?text=Cabinet+Preview'">
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

      <!-- CHAT SUPPORT -->
      <button class="chat-fab" id="chatBtn" aria-label="Chat Support">
        <span class="material-symbols-rounded">chat</span>
      </button>

      <div id="chatPopup" class="chat-popup" style="display:none;">
        <div class="chat-header">
          <span>Chat Support</span>
          <button id="chatClose" class="chat-close-btn" type="button">
            <span class="material-symbols-rounded">close</span>
          </button>
        </div>
        <div class="chat-body" id="chatMessages">
          <div class="chat-message system">
            <p>Hello <?= $customerName ?>! How can we help you today?</p>
          </div>
        </div>
        <div class="chat-input-wrapper">
          <input type="text" id="chatInput" placeholder="Type your message..." />
          <button id="chatSend" type="button">
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
    <footer class="custom-footer">
      <div class="footer-content">
        <div class="footer-columns">
          <div class="footer-col">
            <h3 class="footer-heading">RADS TOOLING</h3>
            <p class="footer-desc"> Premium custom cabinet manufacturer serving clients since 2007. Quality craftsmanship, affordable prices, and exceptional service.</p>
            <div class="footer-social">
              <a href="https://facebook.com/RadsTooling" target="_blank" rel="noopener"><i class="fab fa-facebook-f"></i></a>
              <a href="https://instagram.com/RadsTooling" target="_blank" rel="noopener"><i class="fab fa-instagram"></i></a>
              <a href="mailto:RadsTooling@gmail.com"><i class="fas fa-envelope"></i></a>
            </div>
          </div>

          <div class="footer-col">
            <h3 class="footer-heading">Quick Links</h3>
            <ul class="footer-links">
              <li><a href="/RADS-TOOLING/customer/profile.php">My Account</a></li>
              <li><a href="/RADS-TOOLING/customer/orders.php">Orders</a></li>
              <li><a href="/RADS-TOOLING/customer/customize.php">Customize</a></li>
              <li><a href="/RADS-TOOLING/public/about.php">About Us</a></li>
            </ul>
          </div>

          <div class="footer-col">
            <h3 class="footer-heading">Support</h3>
            <ul class="footer-links">
              <li><a href="/RADS-TOOLING/help.php">Help Center</a></li>
              <li><a href="/RADS-TOOLING/faqs.php">FAQs</a></li>
              <li><a href="/RADS-TOOLING/customer/feedback.php">Feedback</a></li>
            </ul>
          </div>

          <div class="footer-col">
            <h3 class="footer-heading">Contact</h3>
            <ul class="footer-contact">
              <li><i class="fas fa-map-marker-alt"></i><span>Piela, Dasmariñas, Cavite</span></li>
              <li><i class="fas fa-envelope"></i><a href="mailto:RadsTooling@gmail.com">RadsTooling@gmail.com</a></li>
              <li><i class="fas fa-clock"></i><span>Mon-Sat: 8 AM - 5 PM</span></li>
            </ul>
          </div>
        </div>

        <div class="footer-bottom">
          <p>&copy; <?= date('Y') ?> RADS TOOLING INC. All rights reserved.</p>
          <div class="footer-legal">
            <a href="/RADS-TOOLING/privacy.php">Privacy Policy</a>
            <a href="/RADS-TOOLING/terms.php">Terms & Conditions</a>
          </div>
        </div>
      </div>
    </footer>

  </div><!-- /.page-wrapper -->

  <style>
    /* Customer Homepage Specific Styles */
    .customer-welcome {
      background: linear-gradient(135deg, #1f4e74 0%, #3a77b8 100%);
      /* Changed from purple */
      padding: 60px 5%;
      color: white;
    }

    .welcome-content {
      max-width: 1200px;
      margin: 0 auto;
    }

    .welcome-text h1 {
      font-size: 2.5rem;
      margin-bottom: 10px;
    }

    .welcome-text p {
      font-size: 1.2rem;
      opacity: 0.9;
      margin-bottom: 30px;
    }

    .welcome-stats {
      display: flex;
      gap: 20px;
      flex-wrap: wrap;
    }

    .stat-card {
      background: rgba(255, 255, 255, 0.15);
      backdrop-filter: blur(10px);
      padding: 20px 30px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      gap: 15px;
      flex: 1;
      min-width: 200px;
    }

    .stat-card i {
      font-size: 2.5rem;
      opacity: 0.9;
    }

    .stat-number {
      display: block;
      font-size: 2rem;
      font-weight: 700;
    }

    .stat-label {
      display: block;
      font-size: 0.9rem;
      opacity: 0.9;
    }

    .quick-actions {
      padding: 60px 5%;
      background: white;
    }

    .quick-actions h2 {
      text-align: center;
      font-size: 2rem;
      margin-bottom: 40px;
      color: #1a1a1a;
    }

    .actions-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 30px;
      max-width: 1200px;
      margin: 0 auto;
    }

    .action-card {
      background: white;
      padding: 30px;
      border-radius: 12px;
      text-align: center;
      text-decoration: none;
      color: #1a1a1a;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
      transition: all 0.3s ease;
      border: 2px solid transparent;
    }

    .action-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
      border-color: #1f4e74;
    }

    .action-icon {
      width: 70px;
      height: 70px;
      margin: 0 auto 20px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 1.8rem;
    }

    .action-card h3 {
      font-size: 1.3rem;
      margin-bottom: 10px;
      color: #1a1a1a;
    }

    .action-card p {
      color: #666;
      font-size: 0.95rem;
    }

    .recent-orders {
      padding: 60px 5%;
      background: #f8f9fa;
    }

    .section-header-inline {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 30px;
      max-width: 1200px;
      margin-left: auto;
      margin-right: auto;
    }

    .section-header-inline h2 {
      font-size: 2rem;
      color: #1a1a1a;
      margin: 0;
    }

    .view-all-link {
      color: #1f4e74;
      text-decoration: none;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 8px;
      transition: gap 0.3s ease;
    }

    .view-all-link:hover {
      gap: 12px;
    }

    .orders-list {
      max-width: 1200px;
      margin: 0 auto;
    }

    .order-item {
      background: white;
      padding: 20px;
      border-radius: 10px;
      margin-bottom: 15px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
      transition: all 0.3s ease;
    }

    .order-item:hover {
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .order-info h4 {
      margin: 0 0 8px;
      color: #1a1a1a;
      font-size: 1.1rem;
    }

    .order-info p {
      margin: 0;
      color: #666;
      font-size: 0.9rem;
    }

    .order-status {
      padding: 8px 16px;
      border-radius: 20px;
      font-size: 0.85rem;
      font-weight: 600;
      text-transform: uppercase;
    }

    .order-status.pending {
      background: #fff3cd;
      color: #856404;
    }

    .order-status.processing {
      background: #cfe2ff;
      color: #084298;
    }

    .order-status.completed {
      background: #d1e7dd;
      color: #0f5132;
    }

    .featured-products {
      padding: 60px 5%;
      background: white;
    }

    .featured-products h2 {
      text-align: center;
      font-size: 2rem;
      margin-bottom: 40px;
      color: #1a1a1a;
    }

    .products-carousel {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 30px;
      max-width: 1200px;
      margin: 0 auto;
    }

    .product-card {
      background: white;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
      transition: all 0.3s ease;
      text-decoration: none;
      color: inherit;
    }

    .product-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }

    .product-image {
      width: 100%;
      height: 250px;
      object-fit: cover;
    }

    .product-details {
      padding: 20px;
    }

    .product-details h3 {
      margin: 0 0 10px;
      font-size: 1.2rem;
      color: #1a1a1a;
    }

    .product-details p {
      margin: 0 0 15px;
      color: #666;
      font-size: 0.9rem;
      line-height: 1.4;
    }

    .product-price {
      font-size: 1.3rem;
      font-weight: 700;
      color: #1f4e74;
    }

    .user-dropdown {
      position: relative;
    }

    .user-menu-toggle {
      background: rgba(31, 78, 116, 0.1);
      border: 2px solid #1f4e74;
      padding: 10px 15px;
      border-radius: 8px;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 8px;
      font-weight: 600;
      color: #1f4e74;
      transition: all 0.3s ease;
    }

    .user-menu-toggle:hover {
      background: #1f4e74;
      color: white;
    }

    .user-dropdown-menu {
      display: none;
      position: absolute;
      top: 100%;
      right: 0;
      margin-top: 10px;
      background: white;
      border-radius: 10px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
      min-width: 220px;
      overflow: hidden;
      z-index: 100;
    }

    .user-dropdown-menu.show {
      display: block;
    }

    .user-dropdown-menu a,
    .user-dropdown-menu button {
      display: block;
      padding: 12px 20px;
      text-decoration: none;
      color: #333;
      transition: all 0.3s ease;
      border: none;
      background: none;
      width: 100%;
      text-align: left;
      cursor: pointer;
      font-size: 1rem;
    }

    .user-dropdown-menu a:hover,
    .user-dropdown-menu button:hover {
      background: #f8f9fa;
      color: #1f4e74;
    }

    .dropdown-logout {
      border-top: 1px solid #e9ecef;
      color: #dc3545 !important;
    }

    .dropdown-logout:hover {
      background: #fff5f5 !important;
    }

    .cart-link {
      position: relative;
      color: #1f4e74;
      font-size: 1.5rem;
      text-decoration: none;
      padding: 10px;
    }

    .cart-badge {
      position: absolute;
      top: 0;
      right: 0;
      background: #dc3545;
      color: white;
      border-radius: 50%;
      width: 22px;
      height: 22px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.75rem;
      font-weight: 700;
    }

    .loading {
      text-align: center;
      padding: 40px;
      color: #666;
      font-size: 1.1rem;
    }

    .loading i {
      font-size: 2rem;
      color: #1f4e74;
      animation: spin 1s linear infinite;
    }

    @keyframes spin {
      0% {
        transform: rotate(0deg);
      }

      100% {
        transform: rotate(360deg);
      }
    }

    .chat-input-container {
      display: flex;
      border-top: 1px solid #e9ecef;
      padding: 15px;
      gap: 10px;
    }

    #chatInput {
      flex: 1;
      padding: 10px 15px;
      border: 1px solid #ddd;
      border-radius: 20px;
      outline: none;
    }

    #chatInput:focus {
      border-color: #1f4e74;
    }

    .chat-send-btn {
      background: #1f4e74;
      color: white;
      border: none;
      width: 45px;
      height: 45px;
      border-radius: 50%;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.3s ease;
    }

    .chat-send-btn:hover {
      background: #154162;
      transform: scale(1.05);
    }

    @media (max-width: 768px) {
      .welcome-text h1 {
        font-size: 1.8rem;
      }

      .welcome-stats {
        flex-direction: column;
      }

      .stat-card {
        min-width: 100%;
      }

      .actions-grid {
        grid-template-columns: 1fr;
      }

      .section-header-inline {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
      }

      .products-carousel {
        grid-template-columns: 1fr;
      }

      .order-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
      }
    }

    /* Custom Modal Styles */
    .custom-modal {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: 10000;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .custom-modal-overlay {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      backdrop-filter: blur(4px);
    }

    .custom-modal-content {
      position: relative;
      background: white;
      border-radius: 16px;
      max-width: 450px;
      width: 90%;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
      animation: modalSlideIn 0.3s ease;
    }

    @keyframes modalSlideIn {
      from {
        opacity: 0;
        transform: translateY(-30px) scale(0.95);
      }

      to {
        opacity: 1;
        transform: translateY(0) scale(1);
      }
    }

    .custom-modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 24px 28px;
      border-bottom: 1px solid #e9ecef;
    }

    .custom-modal-header h3 {
      margin: 0;
      font-size: 1.4rem;
      color: #1a1a1a;
    }

    .modal-close-btn {
      background: none;
      border: none;
      cursor: pointer;
      padding: 4px;
      color: #666;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 50%;
      transition: all 0.3s ease;
    }

    .modal-close-btn:hover {
      background: #f0f0f0;
      color: #1a1a1a;
    }

    .custom-modal-body {
      padding: 32px 28px;
      text-align: center;
    }

    .modal-icon {
      width: 80px;
      height: 80px;
      margin: 0 auto 24px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .modal-icon.warning {
      background: #fff3cd;
      color: #856404;
    }

    .modal-icon.success {
      background: #d1e7dd;
      color: #0f5132;
    }

    .modal-icon .material-symbols-rounded {
      font-size: 3rem;
    }

    .custom-modal-body p {
      font-size: 1.1rem;
      color: #555;
      margin: 0;
    }

    .custom-modal-footer {
      display: flex;
      gap: 12px;
      padding: 20px 28px;
      border-top: 1px solid #e9ecef;
    }

    .btn-modal-cancel,
    .btn-modal-confirm {
      flex: 1;
      padding: 12px 24px;
      border: none;
      border-radius: 8px;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .btn-modal-cancel {
      background: #f8f9fa;
      color: #495057;
    }

    .btn-modal-cancel:hover {
      background: #e9ecef;
    }

    .btn-modal-confirm {
      background: #dc3545;
      color: white;
    }

    .btn-modal-confirm:hover {
      background: #c82333;
    }

    /* Enhanced User Dropdown */
    .user-menu-toggle {
      background: white;
      border: 2px solid #e9ecef;
      padding: 8px 16px;
      border-radius: 10px;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 10px;
      font-weight: 600;
      color: #1a1a1a;
      transition: all 0.3s ease;
    }

    .user-menu-toggle:hover {
      border-color: #1f4e74;
      box-shadow: 0 4px 12px rgba(31, 78, 116, 0.15);
    }

    .user-menu-toggle .material-symbols-rounded {
      font-size: 24px;
    }

    .user-menu-toggle .dropdown-arrow {
      font-size: 20px;
      transition: transform 0.3s ease;
    }

    .user-menu-toggle:hover .dropdown-arrow {
      transform: rotate(180deg);
    }

    .user-name {
      max-width: 150px;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    .user-dropdown-menu {
      display: none;
      position: absolute;
      top: calc(100% + 10px);
      right: 0;
      background: white;
      border-radius: 12px;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
      min-width: 280px;
      overflow: hidden;
      z-index: 100;
      animation: dropdownSlide 0.3s ease;
    }

    @keyframes dropdownSlide {
      from {
        opacity: 0;
        transform: translateY(-10px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .user-dropdown-menu.show {
      display: block;
    }

    .dropdown-header {
      padding: 20px;
      display: flex;
      align-items: center;
      gap: 15px;
      background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    }

    .dropdown-avatar {
      width: 50px;
      height: 50px;
      border-radius: 50%;
      background: linear-gradient(135deg, #1f4e74, #3a77b8);
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.5rem;
      font-weight: 700;
    }

    .dropdown-name {
      font-weight: 700;
      color: #1a1a1a;
      font-size: 1.05rem;
    }

    .dropdown-email {
      font-size: 0.85rem;
      color: #666;
      margin-top: 2px;
    }

    .dropdown-divider {
      height: 1px;
      background: #e9ecef;
      margin: 8px 0;
    }

    .user-dropdown-menu a,
    .user-dropdown-menu button {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 14px 20px;
      text-decoration: none;
      color: #333;
      transition: all 0.3s ease;
      border: none;
      background: none;
      width: 100%;
      text-align: left;
      cursor: pointer;
      font-size: 0.95rem;
    }

    .user-dropdown-menu a:hover,
    .user-dropdown-menu button:hover {
      background: #f8f9fa;
      color: #1f4e74;
    }

    .user-dropdown-menu .material-symbols-rounded {
      font-size: 20px;
    }

    .dropdown-logout {
      color: #dc3545 !important;
    }

    .dropdown-logout:hover {
      background: #fff5f5 !important;
    }

    /* ========== PRODUCT CARDS ========== */
.product-card {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  text-decoration: none;
  color: #2c3e50;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
  transition: all 0.3s ease;
}

.product-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
}

.product-image {
  width: 100%;
  height: 200px;
  overflow: hidden;
  background: #f8f9fa;
}

.product-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.product-card:hover .product-image img {
  transform: scale(1.05);
}

.product-info {
  padding: 1.25rem;
}

.product-info h3 {
  font-size: 1.1rem;
  margin-bottom: 0.5rem;
  color: #2c3e50;
}

.product-type {
  color: #7f8c8d;
  font-size: 0.9rem;
  margin-bottom: 0.75rem;
}

.product-price {
  font-size: 1.25rem;
  font-weight: 700;
  color: #2f5b88;
}
  </style>

  <script>
    // ========== USER DROPDOWN MENU ==========
    const userMenuBtn = document.getElementById('userMenuBtn');
    const userDropdownMenu = document.getElementById('userDropdownMenu');

    userMenuBtn.addEventListener('click', function(e) {
      e.stopPropagation();
      userDropdownMenu.classList.toggle('show');
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
      if (!userMenuBtn.contains(e.target) && !userDropdownMenu.contains(e.target)) {
        userDropdownMenu.classList.remove('show');
      }
    });

    // ========== LOAD USER STATISTICS ==========
    fetch('/RADS-TOOLING/backend/api/customer_stats.php', {
        credentials: 'same-origin'
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          document.getElementById('totalOrders').textContent = data.stats.total || 0;
          document.getElementById('pendingOrders').textContent = data.stats.pending || 0;
          document.getElementById('completedOrders').textContent = data.stats.completed || 0;
        } else {
          document.getElementById('totalOrders').textContent = '0';
          document.getElementById('pendingOrders').textContent = '0';
          document.getElementById('completedOrders').textContent = '0';
        }
      })
      .catch(err => {
        console.error('Failed to load stats:', err);
        document.getElementById('totalOrders').textContent = '0';
        document.getElementById('pendingOrders').textContent = '0';
        document.getElementById('completedOrders').textContent = '0';
      });

    // ========== LOAD RECENT ORDERS ==========
    const ordersContainer = document.getElementById('recentOrdersContainer');

    fetch('/RADS-TOOLING/backend/api/recent_orders.php?limit=3', {
        credentials: 'same-origin'
      })
      .then(res => res.json())
      .then(data => {
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
      })
      .catch(() => {
        ordersContainer.innerHTML = '<p style="text-align:center;color:#dc3545;padding:40px;">Failed to load orders</p>';
      });

    // ========== LOAD RECOMMENDED PRODUCTS ==========
    const productsContainer = document.getElementById('recommendedProducts');

    fetch('/RADS-TOOLING/backend/api/products.php?limit=4', {
        credentials: 'same-origin'
      })
      .then(res => res.json())
      .then(data => {
        if (data.success && data.products.length > 0) {
          productsContainer.innerHTML = data.products.map(product => `
      <a href="/RADS-TOOLING/public/products.php?id=${product.id}" class="product-card">
        <img src="${escapeHtml(product.image || '/RADS-TOOLING/assets/images/placeholder.jpg')}" 
             alt="${escapeHtml(product.name)}" 
             class="product-image"
             onerror="this.src='https://via.placeholder.com/280x250?text=${encodeURIComponent(product.name)}'">
        <div class="product-details">
          <h3>${escapeHtml(product.name)}</h3>
          <p>${escapeHtml((product.description || 'Premium quality cabinet').substring(0, 80))}...</p>
          <div class="product-price">₱${parseFloat(product.price).toLocaleString()}</div>
        </div>
      </a>
    `).join('');
        } else {
          productsContainer.innerHTML = '<p style="text-align:center;color:#666;padding:40px;grid-column:1/-1;">No products available</p>';
        }
      })
      .catch(() => {
        productsContainer.innerHTML = '<p style="text-align:center;color:#dc3545;padding:40px;grid-column:1/-1;">Failed to load products</p>';
      });

    // ========== CART COUNT ==========
    let cartItems = JSON.parse(localStorage.getItem('cart') || '[]');
    document.getElementById('cartCount').textContent = cartItems.length;

    // ========== CHAT FUNCTIONALITY ==========
    const chatBtn = document.getElementById('chatBtn');
    const chatPopup = document.getElementById('chatPopup');
    const chatClose = document.getElementById('chatClose');
    const chatInput = document.getElementById('chatInput');
    const chatSend = document.getElementById('chatSend');
    const chatMessages = document.getElementById('chatMessages');

    chatBtn.addEventListener('click', () => {
      chatPopup.style.display = 'flex';
      chatBtn.style.display = 'none';
    });

    chatClose.addEventListener('click', () => {
      chatPopup.style.display = 'none';
      chatBtn.style.display = 'flex';
    });

    chatSend.addEventListener('click', sendMessage);
    chatInput.addEventListener('keypress', (e) => {
      if (e.key === 'Enter') sendMessage();
    });

    function sendMessage() {
      const message = chatInput.value.trim();
      if (!message) return;

      const msgDiv = document.createElement('div');
      msgDiv.className = 'chat-message user';
      msgDiv.innerHTML = `<p>${escapeHtml(message)}</p>`;
      chatMessages.appendChild(msgDiv);

      chatInput.value = '';
      chatMessages.scrollTop = chatMessages.scrollHeight;

      // Auto-reply for now (replace with real chat API later)
      setTimeout(() => {
        const replyDiv = document.createElement('div');
        replyDiv.className = 'chat-message system';
        replyDiv.innerHTML = `<p>Thank you for your message. Our support team will assist you shortly!</p>`;
        chatMessages.appendChild(replyDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
      }, 1000);
    }

    // ========== LOGOUT FUNCTION ==========
    // ========== LOGOUT MODAL ==========
    function showLogoutModal() {
      document.getElementById('logoutModal').style.display = 'flex';
      document.getElementById('userDropdownMenu').classList.remove('show');
    }

    function closeLogoutModal() {
      document.getElementById('logoutModal').style.display = 'none';
    }

    async function confirmLogout() {
      try {
        const response = await fetch('/RADS-TOOLING/backend/api/auth.php?action=logout', {
          method: 'POST',
          credentials: 'same-origin'
        });
        const result = await response.json();

        if (result.success) {
          localStorage.removeItem('cart');
          window.location.href = '/RADS-TOOLING/public/index.php';
        } else {
          alert('Logout failed. Please try again.');
        }
      } catch (error) {
        console.error('Logout error:', error);
        localStorage.removeItem('cart');
        window.location.href = '/RADS-TOOLING/public/index.php';
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

    document.addEventListener('DOMContentLoaded', function() {
      const profileToggle = document.getElementById('profileToggle');
      const profileDropdown = document.getElementById('profileDropdown');

      if (profileToggle && profileDropdown) {
        profileToggle.addEventListener('click', function(e) {
          e.stopPropagation();
          profileDropdown.classList.toggle('show');
        });

        document.addEventListener('click', function(e) {
          if (!profileToggle.contains(e.target) && !profileDropdown.contains(e.target)) {
            profileDropdown.classList.remove('show');
          }
        });
      }

      // Load user email
      loadUserEmail();

      // Load recommended products
      loadRecommendedProducts();

      // Initialize chat
      initializeChat();
    });

    // ========== LOAD USER EMAIL ==========
    async function loadUserEmail() {
      try {
        const response = await fetch('/RADS-TOOLING/backend/api/customer_profile.php?action=get_profile', {
          credentials: 'same-origin',
          headers: {
            'Accept': 'application/json'
          }
        });

        const result = await response.json();

        if (result.success && result.data && result.data.email) {
          const emailDisplay = document.getElementById('userEmailDisplay');
          if (emailDisplay) {
            emailDisplay.textContent = result.data.email;
          }
        }
      } catch (error) {
        console.error('Failed to load user email:', error);
        const emailDisplay = document.getElementById('userEmailDisplay');
        if (emailDisplay) {
          emailDisplay.textContent = 'customer@rads.com';
        }
      }
    }

    // ========== LOGOUT MODAL ==========
    function showLogoutModal() {
      const modal = document.getElementById('logoutModal');
      if (modal) {
        modal.style.display = 'flex';
      }
    }

    function closeLogoutModal() {
      const modal = document.getElementById('logoutModal');
      if (modal) {
        modal.style.display = 'none';
      }
    }

    async function confirmLogout() {
      try {
        const response = await fetch('/RADS-TOOLING/backend/api/auth.php?action=logout', {
          method: 'POST',
          credentials: 'same-origin'
        });

        const result = await response.json();

        if (result.success) {
          localStorage.removeItem('cart');
          window.location.href = '/RADS-TOOLING/public/index.php';
        } else {
          alert('Logout failed. Please try again.');
        }
      } catch (error) {
        console.error('Logout error:', error);
        localStorage.removeItem('cart');
        window.location.href = '/RADS-TOOLING/public/index.php';
      }
    }

    // ========== LOAD RECOMMENDED PRODUCTS ==========
    async function loadRecommendedProducts() {
      try {
        const response = await fetch('/RADS-TOOLING/backend/api/products.php?action=list&limit=4', {
          credentials: 'same-origin',
          headers: {
            'Accept': 'application/json'
          }
        });

        const result = await response.json();

        if (result.success && result.data && result.data.products) {
          displayProducts(result.data.products);
        } else {
          showNoProducts();
        }
      } catch (error) {
        console.error('Failed to load products:', error);
        showNoProducts();
      }
    }

    function displayProducts(products) {
      const container = document.getElementById('recommendedProducts');
      if (!container) return;

      if (products.length === 0) {
        showNoProducts();
        return;
      }

      container.innerHTML = products.map(product => `
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
    }

    function showNoProducts() {
      const container = document.getElementById('recommendedProducts');
      if (container) {
        container.innerHTML = '<div class="loading-state">No products available</div>';
      }
    }

    function escapeHtml(text) {
      const div = document.createElement('div');
      div.textContent = text;
      return div.innerHTML;
    }

    // ========== CHAT FUNCTIONALITY ==========
    function initializeChat() {
      const chatBtn = document.getElementById('chatBtn');
      const chatPopup = document.getElementById('chatPopup');
      const chatClose = document.getElementById('chatClose');
      const chatSend = document.getElementById('chatSend');
      const chatInput = document.getElementById('chatInput');

      if (chatBtn && chatPopup) {
        chatBtn.addEventListener('click', function() {
          chatPopup.style.display = 'flex';
          chatBtn.style.display = 'none';
        });
      }

      if (chatClose && chatPopup && chatBtn) {
        chatClose.addEventListener('click', function() {
          chatPopup.style.display = 'none';
          chatBtn.style.display = 'flex';
        });
      }

      if (chatSend && chatInput) {
        chatSend.addEventListener('click', sendMessage);
        chatInput.addEventListener('keypress', function(e) {
          if (e.key === 'Enter') {
            sendMessage();
          }
        });
      }
    }

    function sendMessage() {
      const chatInput = document.getElementById('chatInput');
      const chatMessages = document.getElementById('chatMessages');

      if (!chatInput || !chatMessages) return;

      const message = chatInput.value.trim();
      if (!message) return;

      const messageEl = document.createElement('div');
      messageEl.className = 'chat-message user';
      messageEl.innerHTML = `<p>${escapeHtml(message)}</p>`;
      chatMessages.appendChild(messageEl);

      chatInput.value = '';
      chatMessages.scrollTop = chatMessages.scrollHeight;

      setTimeout(() => {
        const replyEl = document.createElement('div');
        replyEl.className = 'chat-message system';
        replyEl.innerHTML = '<p>Thank you for your message. Our team will respond shortly.</p>';
        chatMessages.appendChild(replyEl);
        chatMessages.scrollTop = chatMessages.scrollHeight;
      }, 1000);
    }

    // ========== CART COUNT ==========
    function updateCartCount() {
      const cart = JSON.parse(localStorage.getItem('cart') || '[]');
      const cartCount = document.getElementById('cartCount');
      if (cartCount) {
        cartCount.textContent = cart.length;
      }
    }

    updateCartCount();
  </script>
</body>

</html>