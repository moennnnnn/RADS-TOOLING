<?php
// /public/index.php – PUBLIC landing page (no auth required)
require_once __DIR__ . '/../backend/config/app.php';
session_start();

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
                <a href="/RADS-TOOLING/public/index.php" class="nav-menu-item">Home</a>
                <a href="/RADS-TOOLING/public/about.php" class="nav-menu-item">About Us</a>
                <a href="/RADS-TOOLING/public/products.php" class="nav-menu-item">Products</a>
                <a href="#testimonials" class="nav-menu-item">Testimonials</a>
            </nav>
        </header>


        <!-- Main Content -->
        <main class="rt-policy-main-content">
            <div class="rt-container">
                <div class="rt-about-narrative" style="margin-top: 3rem;">
                    <div class="rt-policy-content">
                        <h1>Privacy Policy</h1>
                        <p><em>Last updated: January 2024</em></p>

                        <h2>1. Introduction</h2>
                        <p>Welcome to RADS Tooling. We are committed to protecting your personal information and your right to privacy. This Privacy Policy explains what information we collect, how we use it, and what rights you have in relation to it.</p>

                        <h2>2. Information We Collect</h2>
                        <h3>Personal Information</h3>
                        <p>We collect personal information that you voluntarily provide to us when you:</p>
                        <ul>
                            <li>Register for an account on our website</li>
                            <li>Place an order for our products</li>
                            <li>Request custom tooling solutions</li>
                            <li>Subscribe to our newsletter</li>
                            <li>Contact us for support or inquiries</li>
                        </ul>

                        <p>This information may include:</p>
                        <ul>
                            <li>Name and contact information (email, phone number, address)</li>
                            <li>Account credentials (username and password)</li>
                            <li>Payment information (credit card details, billing address)</li>
                            <li>Order history and preferences</li>
                            <li>Custom design specifications and requirements</li>
                        </ul>

                        <h3>Automatically Collected Information</h3>
                        <p>When you visit our website, we automatically collect certain information about your device, including:</p>
                        <ul>
                            <li>IP address and location data</li>
                            <li>Browser type and version</li>
                            <li>Device type and operating system</li>
                            <li>Pages visited and time spent on our site</li>
                            <li>Referring website or source</li>
                        </ul>

                        <h2>3. How We Use Your Information</h2>
                        <p>We use the information we collect to:</p>
                        <ul>
                            <li>Process and fulfill your orders</li>
                            <li>Provide customer support and respond to inquiries</li>
                            <li>Send order confirmations and shipping updates</li>
                            <li>Create and manage your account</li>
                            <li>Customize your experience on our website</li>
                            <li>Send marketing communications (with your consent)</li>
                            <li>Improve our products and services</li>
                            <li>Prevent fraud and enhance security</li>
                            <li>Comply with legal obligations</li>
                        </ul>

                        <h2>4. Cookies and Tracking Technologies</h2>
                        <p>We use cookies and similar tracking technologies to:</p>
                        <ul>
                            <li>Keep you logged in to your account</li>
                            <li>Remember your preferences and settings</li>
                            <li>Analyze website traffic and usage patterns</li>
                            <li>Improve website functionality and user experience</li>
                            <li>Deliver targeted advertisements (if applicable)</li>
                        </ul>

                        <p>You can control cookies through your browser settings. However, disabling cookies may limit some features of our website.</p>

                        <h2>5. Data Sharing and Disclosure</h2>
                        <p>We do not sell, trade, or rent your personal information to third parties. We may share your information with:</p>
                        <ul>
                            <li><strong>Service Providers:</strong> Third-party vendors who help us operate our business (e.g., payment processors, shipping companies, email service providers)</li>
                            <li><strong>Business Partners:</strong> Trusted partners for custom tooling projects (with your consent)</li>
                            <li><strong>Legal Requirements:</strong> When required by law or to protect our rights and safety</li>
                            <li><strong>Business Transfers:</strong> In connection with a merger, acquisition, or sale of assets</li>
                        </ul>

                        <h2>6. Data Security</h2>
                        <p>We implement appropriate technical and organizational security measures to protect your personal information against unauthorized access, alteration, disclosure, or destruction. These measures include:</p>
                        <ul>
                            <li>Encryption of sensitive data in transit and at rest</li>
                            <li>Regular security assessments and updates</li>
                            <li>Limited access to personal information on a need-to-know basis</li>
                            <li>Employee training on data protection and privacy</li>
                        </ul>

                        <p>However, no method of transmission over the Internet or electronic storage is 100% secure, and we cannot guarantee absolute security.</p>

                        <h2>7. Your Rights and Choices</h2>
                        <p>You have the right to:</p>
                        <ul>
                            <li><strong>Access:</strong> Request a copy of your personal information</li>
                            <li><strong>Correction:</strong> Update or correct inaccurate information</li>
                            <li><strong>Deletion:</strong> Request deletion of your personal information</li>
                            <li><strong>Opt-out:</strong> Unsubscribe from marketing communications</li>
                            <li><strong>Data Portability:</strong> Request your data in a portable format</li>
                            <li><strong>Withdraw Consent:</strong> Withdraw consent for data processing where applicable</li>
                        </ul>

                        <p>To exercise these rights, please contact us using the information provided below.</p>

                        <h2>8. Children's Privacy</h2>
                        <p>Our services are not intended for individuals under the age of 18. We do not knowingly collect personal information from children. If you believe we have collected information from a child, please contact us immediately.</p>

                        <h2>9. International Data Transfers</h2>
                        <p>Your information may be transferred to and processed in countries other than your country of residence. These countries may have different data protection laws. We ensure appropriate safeguards are in place to protect your information in accordance with this Privacy Policy.</p>

                        <h2>10. Changes to This Policy</h2>
                        <p>We may update this Privacy Policy from time to time to reflect changes in our practices or legal requirements. We will notify you of any material changes by posting the new Privacy Policy on this page and updating the "Last updated" date.</p>

                        <h2>11. Contact Us</h2>
                        <p>If you have questions, concerns, or requests regarding this Privacy Policy or our data practices, please contact us at:</p>
                        <ul>
                            <li><strong>Email:</strong> RadsTooling@gmail.com</li>
                            <li><strong>Phone:</strong> +63 (976) 228-4270</li>
                            <li><strong>Address:</strong> Green Breeze, Piela, Dasmariñas, Cavite, Philippines</li>
                        </ul>

                        <h2>12. Consent</h2>
                        <p>By using our website and services, you consent to the collection and use of your information as described in this Privacy Policy.</p>
                    </div>
                </div>
            </div>
        </main>

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

        <!-- Mobile Menu Script -->
        <script>
            document.querySelector('.rt-nav-toggle').addEventListener('click', function() {
                document.querySelector('.rt-nav-menu').classList.toggle('active');
            });
        </script>
        <script>
            // ========== CHAT BUTTON ==========
            (function() {
                const chatBtn = document.getElementById('chatBtn');
                const chatPopup = document.getElementById('chatPopup');
                const chatClose = document.getElementById('chatClose');

                chatBtn?.addEventListener('click', () => {
                    chatPopup.style.display = 'flex';
                    chatBtn.style.display = 'none';
                });

                chatClose?.addEventListener('click', () => {
                    chatPopup.style.display = 'none';
                    chatBtn.style.display = 'flex';
                });
            })();

            // ========== SMOOTH SCROLL ==========
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });
        </script>
        <!-- Add before closing </body> tag -->
        <script src="/RADS-TOOLING/assets/JS/chat_widget.js"></script>
        <script src="/RADS-TOOLING/assets/js/policy.js"></script>
</body>

</html>