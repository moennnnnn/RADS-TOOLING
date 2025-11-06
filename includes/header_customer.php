<?php
/**
 * CUSTOMER HEADER - For authenticated customers
 * Used by: customer/homepage.php, customer/about.php, customer/products.php, etc.
 * Requires: $customerName to be set
 */

// Fallback if customerName not set
if (!isset($customerName)) {
  $customerName = htmlspecialchars($_SESSION['user']['name'] ?? $_SESSION['user']['username'] ?? 'Customer');
}
?>
<header class="navbar">
  <div class="navbar-container">
    <div class="navbar-brand">
      <a href="/RADS-TOOLING/customer/homepage.php" class="logo-link">
        <span class="logo-text">R</span>ADS <span class="logo-text">T</span>OOLING
      </a>
    </div>

    <form class="search-container" action="/RADS-TOOLING/customer/products.php" method="get">
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
            <div class="profile-avatar" id="nav-avatar">
              <?= strtoupper(substr($customerName, 0, 1)) ?>
            </div>
          </div>
          <div class="profile-info">
            <span class="profile-name" id="nav-username"><?= htmlspecialchars($customerName) ?></span>
            <span class="material-symbols-rounded dropdown-icon">expand_more</span>
          </div>
        </button>

        <div class="profile-dropdown" id="profileDropdown">
          <div class="profile-dropdown-header">
            <div class="dropdown-avatar" id="dd-avatar">
              <?= strtoupper(substr($customerName, 0, 1)) ?>
            </div>
            <div class="dropdown-user-info">
              <div class="dropdown-name" id="dd-name"><?= htmlspecialchars($customerName) ?></div>
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
          <div class="dropdown-divider"></div>
          <button onclick="showLogoutModal()" class="dropdown-item dropdown-logout" type="button">
            <span class="material-symbols-rounded">logout</span>
            <span>Logout</span>
          </button>
        </div>
      </div>

      <!-- Cart -->
      <a href="/RADS-TOOLING/customer/cart.php" class="cart-button">
        <span class="material-symbols-rounded">shopping_cart</span>
        <span id="cartCount" class="cart-badge">0</span>
      </a>
    </div>
  </div>

  <nav class="navbar-menu">
    <a href="/RADS-TOOLING/customer/homepage.php" class="nav-menu-item <?= ($currentPage ?? '') === 'home' ? 'active' : '' ?>">Home</a>
    <a href="/RADS-TOOLING/customer/about.php" class="nav-menu-item <?= ($currentPage ?? '') === 'about' ? 'active' : '' ?>">About</a>
    <a href="/RADS-TOOLING/customer/products.php" class="nav-menu-item <?= ($currentPage ?? '') === 'about' ? 'active' : '' ?>">Products</a>
    <a href="/RADS-TOOLING/customer/testimonials.php" class="nav-menu-item <?= ($currentPage ?? '') === 'testimonials' ? 'active' : '' ?>">Testimonials</a>
  </nav>
</header>
