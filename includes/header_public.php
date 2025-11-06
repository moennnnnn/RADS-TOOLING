<?php
/**
 * PUBLIC HEADER - For non-authenticated users
 * Used by: public/index.php, public/about.php, public/products.php, public/testimonials.php, etc.
 */
?>
<header class="navbar">
  <div class="navbar-container">
    <div class="navbar-brand">
      <a href="/RADS-TOOLING/public/index.php" class="logo-link">
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
      <a href="/RADS-TOOLING/customer/cust_login.php" class="nav-link">
        <span class="material-symbols-rounded">login</span>
        <span>Login</span>
      </a>
      <a href="/RADS-TOOLING/customer/register.php" class="nav-link">
        <span class="material-symbols-rounded">person_add</span>
        <span>Sign Up</span>
      </a>
      <a href="/RADS-TOOLING/admin/login.php" class="nav-link-icon" title="Staff Login">
        <span class="material-symbols-rounded">admin_panel_settings</span>
      </a>
    </div>
  </div>

  <nav class="navbar-menu">
    <a href="/RADS-TOOLING/public/index.php" class="nav-menu-item <?= ($currentPage ?? '') === 'home' ? 'active' : '' ?>">Home</a>
    <a href="/RADS-TOOLING/public/about.php" class="nav-menu-item <?= ($currentPage ?? '') === 'about' ? 'active' : '' ?>">About Us</a>
    <a href="/RADS-TOOLING/public/products.php" class="nav-menu-item <?= ($currentPage ?? '') === 'products' ? 'active' : '' ?>">Products</a>
    <a href="/RADS-TOOLING/public/testimonials.php" class="nav-menu-item <?= ($currentPage ?? '') === 'testimonials' ? 'active' : '' ?>">Testimonials</a>
  </nav>
</header>
