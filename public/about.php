<?php
// /public/index.php – PUBLIC landing page (no auth required)
require_once __DIR__ . '/../backend/config/app.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user = $_SESSION['user'] ?? null;
$isCustomer = $user && (($user['aud'] ?? '') === 'customer');

// If customer is logged in, redirect to customer homepage
if ($isCustomer) {
    header('Location: /RADS-TOOLING/customer/homepage.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>RADS TOOLING - Custom Cabinet Solutions</title>
    <link rel="stylesheet" href="/RADS-TOOLING/assets/CSS/Homepage.css" />
    <link rel="stylesheet" href="/RADS-TOOLING/assets/CSS/about.css">
    <link rel="stylesheet" href="/RADS-TOOLING/assets/CSS/chat-widget.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <div class="page-wrapper">

        <!-- HEADER -->
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
                <a href="/RADS-TOOLING/public/index.php" class="nav-menu-item ">Home</a>
                <a href="/RADS-TOOLING/public/about.php" class="nav-menu-item active">About Us</a>
                <a href="/RADS-TOOLING/public/products.php" class="nav-menu-item">Products</a>
                <a href="#testimonials" class="nav-menu-item">Testimonials</a>
            </nav>
        </header>


        <!-- Hero Section -->
        <section class="sp-hero">
            <div class="sp-container">
                <h1 class="sp-hero-title">About RADS Tooling</h1>
                <p class="sp-hero-subtitle">Your trusted partner in precision tooling and industrial solutions</p>
                <div class="sp-hero-buttons">
                    <a href="signup.php" class="sp-btn sp-btn-primary">Get Started</a>
                    <a href="products.php" class="sp-btn sp-btn-secondary">Browse Products</a>
                </div>
            </div>
        </section>

        <!-- Store Info and Hours Cards -->
        <section class="sp-section">
            <div class="sp-container">
                <div class="sp-cards-grid">
                    <!-- Store Information Card -->
                    <div class="sp-card">
                        <h2 class="sp-card-title">Store Information</h2>
                        <div class="sp-info-list">
                            <div class="sp-info-item">
                                <span class="sp-icon">📍</span>
                                <div>
                                    <strong>Location</strong>
                                    <p>Green Breeze, Piela<br>
                                        Dasmariñas, Cavite, Philippines</p>
                                </div>
                            </div>
                            <div class="sp-info-item">
                                <span class="sp-icon">📞</span>
                                <div>
                                    <strong>Phone</strong>
                                    <p>+63 (976) 228-4270</p>
                                </div>
                            </div>
                            <div class="sp-info-item">
                                <span class="sp-icon">✉️</span>
                                <div>
                                    <strong>Email</strong>
                                    <p>RadsTooling@gmail.com</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Operating Hours Card -->
                    <div class="sp-card">
                        <h2 class="sp-card-title">Operating Hours</h2>
                        <div class="sp-hours-table">
                            <div class="sp-hours-row">
                                <span>Monday - Saturday</span>
                                <strong>8:00 AM - 5:00 PM</strong>
                            </div>
                            <div class="sp-hours-row">
                                <span>Sunday</span>
                                <strong>Closed</strong>
                            </div>
                        </div>
                        <div class="sp-notice">
                            <span class="sp-icon">📢</span>
                            <p>Special hours may apply during holidays. Please contact us for confirmation.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Mission & Vision Section -->
        <section class="sp-section sp-section-alt">
            <div class="sp-container">
                <div class="sp-mv-grid">
                    <div class="sp-mv-panel">
                        <div class="sp-mv-icon">🎯</div>
                        <h2>Our Mission</h2>
                        <p>To provide high-quality tooling solutions that empower businesses to achieve precision, efficiency, and innovation in their manufacturing processes. We are committed to delivering exceptional products and services that meet the evolving needs of our customers while maintaining the highest standards of quality and reliability.</p>
                    </div>
                    <div class="sp-mv-panel">
                        <div class="sp-mv-icon">🔮</div>
                        <h2>Our Vision</h2>
                        <p>To become the leading provider of industrial tooling solutions in the Philippines and Southeast Asia, recognized for our commitment to innovation, quality, and customer satisfaction. We envision a future where RADS Tooling is the first choice for businesses seeking reliable, cutting-edge tooling solutions that drive their success.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Company Story Section -->
        <section class="sp-section">
            <div class="sp-container">
                <div class="sp-story">
                    <h2>Our Story</h2>
                    <p>Founded with a passion for precision and innovation, RADS Tooling has been serving the industrial community for over a decade. What started as a small workshop has grown into a comprehensive tooling solutions provider, offering everything from standard tools to custom-designed equipment tailored to specific manufacturing needs.</p>
                    <p>Our journey has been marked by continuous learning, adaptation, and an unwavering commitment to quality. We believe in building lasting relationships with our clients, understanding their unique challenges, and providing solutions that not only meet but exceed their expectations.</p>
                    <p>Today, RADS Tooling stands as a testament to the power of dedication and expertise. Our team of skilled professionals works tirelessly to ensure that every product leaving our facility meets the highest standards of quality and precision. As we look to the future, we remain committed to innovation, sustainability, and the continued success of our valued customers.</p>
                </div>
            </div>
        </section>

        <!-- RADS-TOOLING Chat Support Widget (Guest Mode) -->
        <button id="rtChatBtn" class="rt-chat-btn">
            <span class="material-symbols-rounded">chat</span>
            Need Help?
        </button>

        <div id="rtChatPopup" class="rt-chat-popup">
            <div class="rt-chat-header">
                <span>Rads Tooling - Chat Support</span>
            </div>

            <!-- Message: Please Login -->
            <div class="rt-chat-messages" style="padding: 40px 20px; text-align: center;">
                <div style="margin-bottom: 20px;">
                    <i class="fas fa-lock" style="font-size: 48px; color: #1f4e74; opacity: 0.6;"></i>
                </div>
                <h3 style="color: #333; margin-bottom: 10px;">Chat Support Unavailable</h3>
                <p style="color: #666; margin-bottom: 20px;">
                    Please login to chat with our support team and get instant answers to your questions.
                </p>
                <a href="/RADS-TOOLING/customer/cust_login.php"
                    style="display: inline-block; padding: 12px 24px; background: #1f4e74; color: white; text-decoration: none; border-radius: 6px; font-weight: 600;">
                    <i class="fas fa-sign-in-alt"></i> Login Now
                </a>
            </div>
        </div>

        <script>
            // Simple toggle for public page (no chat functionality until login)
            document.addEventListener('DOMContentLoaded', function() {
                const chatBtn = document.getElementById('rtChatBtn');
                const chatPopup = document.getElementById('rtChatPopup');
                const chatClose = document.getElementById('rtChatClose');

                if (chatBtn && chatPopup && chatClose) {
                    chatBtn.addEventListener('click', function() {
                        chatPopup.style.display = 'flex';
                        chatBtn.style.display = 'none';
                    });

                    chatClose.addEventListener('click', function() {
                        chatPopup.style.display = 'none';
                        chatBtn.style.display = 'flex';
                    });
                }
            });
        </script>

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
                        <li><a href="/RADS-TOOLING/public/about.php">About Us</a></li>
                        <li><a href="/RADS-TOOLING/public/products.php">Products</a></li>
                        <li><a href="/RADS-TOOLING/customer/register.php">Sign Up</a></li>
                        <li><a href="/RADS-TOOLING/customer/cust_login.php">Login</a></li>
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
                    <a href="/RADS-TOOLING/public/privacy.php">Privacy Policy</a>
                    <a href="/RADS-TOOLING/public/terms.php">Terms & Conditions</a>
                </div>
            </div>
        </footer>
        <script src="/RADS-TOOLING/assets/JS/chat_widget.js"></script>
</body>

</html>