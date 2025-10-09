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
                        <h1>Terms & Conditions</h1>
                        <p><em>Effective Date: January 2024</em></p>

                        <h2>1. Acceptance of Terms</h2>
                        <p>By accessing and using the RADS Tooling website and services, you accept and agree to be bound by these Terms & Conditions. If you do not agree to these terms, please do not use our services.</p>

                        <p>These terms apply to all visitors, users, customers, and others who access or use our services, including but not limited to browsing our website, placing orders, or engaging with our custom tooling services.</p>

                        <h2>2. Accounts and Registration</h2>
                        <h3>Account Creation</h3>
                        <p>To access certain features of our services, you may be required to create an account. When creating an account, you must:</p>
                        <ul>
                            <li>Provide accurate, current, and complete information</li>
                            <li>Maintain and promptly update your account information</li>
                            <li>Keep your password secure and confidential</li>
                            <li>Accept responsibility for all activities under your account</li>
                            <li>Notify us immediately of any unauthorized use</li>
                        </ul>

                        <h3>Account Termination</h3>
                        <p>We reserve the right to suspend or terminate accounts that violate these terms, engage in fraudulent activity, or remain inactive for an extended period.</p>

                        <h2>3. Orders and Payments</h2>
                        <h3>Product Orders</h3>
                        <p>When you place an order through our website:</p>
                        <ul>
                            <li>You are making an offer to purchase products subject to these terms</li>
                            <li>All orders are subject to acceptance and availability</li>
                            <li>We reserve the right to refuse or cancel any order for any reason</li>
                            <li>Prices are subject to change without notice</li>
                            <li>You agree to pay all charges associated with your order</li>
                        </ul>

                        <h3>Payment Terms</h3>
                        <p>Payment must be made in full at the time of order unless otherwise agreed. We accept the following payment methods:</p>
                        <ul>
                            <li>Credit and debit cards (Visa, MasterCard, American Express)</li>
                            <li>Bank transfers for large orders</li>
                            <li>Cash on delivery (for local customers only)</li>
                            <li>Corporate accounts (subject to credit approval)</li>
                        </ul>

                        <h3>Pricing Errors</h3>
                        <p>In the event of a pricing error, we reserve the right to cancel orders placed at the incorrect price. We will notify you promptly and offer you the option to purchase at the correct price.</p>

                        <h2>4. Shipping, Delivery, and Pickup</h2>
                        <h3>Shipping Policy</h3>
                        <p>We offer shipping within the Philippines and to select international destinations. Shipping terms include:</p>
                        <ul>
                            <li>Shipping costs are calculated based on weight, size, and destination</li>
                            <li>Delivery times are estimates and not guaranteed</li>
                            <li>Risk of loss transfers to you upon delivery to the carrier</li>
                            <li>International orders may be subject to customs duties and taxes</li>
                        </ul>

                        <h3>Local Pickup</h3>
                        <p>Customers may opt to pick up orders at our facility during operating hours:</p>
                        <ul>
                            <li>Monday to Saturday: 8:00 AM - 5:00 PM</li>
                            <li>Sunday: Closed</li>
                            <li>Valid ID required for pickup</li>
                            <li>Orders must be collected within 30 days of notification</li>
                        </ul>

                        <h2>5. Custom Products and Services</h2>
                        <h3>Custom Tooling Solutions</h3>
                        <p>For custom tooling projects:</p>
                        <ul>
                            <li>Specifications must be approved in writing before production</li>
                            <li>A deposit of 50% may be required before work begins</li>
                            <li>Changes to specifications after approval may incur additional charges</li>
                            <li>Delivery times for custom products are estimates only</li>
                            <li>Custom products are non-returnable unless defective</li>
                        </ul>

                        <h3>Intellectual Property</h3>
                        <p>You retain ownership of any designs you provide for custom work. However, you grant us a license to use such designs for the purpose of fulfilling your order. We retain ownership of any tooling, processes, or methods we develop.</p>

                        <h2>6. Returns and Cancellations</h2>
                        <h3>Return Policy</h3>
                        <p>We accept returns under the following conditions:</p>
                        <ul>
                            <li>Standard products may be returned within 30 days of receipt</li>
                            <li>Products must be unused and in original packaging</li>
                            <li>Customer is responsible for return shipping costs</li>
                            <li>Refunds will be processed within 10 business days of receipt</li>
                            <li>Custom products are non-returnable except for defects</li>
                        </ul>

                        <h3>Cancellation Policy</h3>
                        <p>Orders may be cancelled:</p>
                        <ul>
                            <li>Within 24 hours of placement for standard products</li>
                            <li>Before production begins for custom products</li>
                            <li>Cancellation fees may apply for custom orders</li>
                            <li>Deposits on custom orders are non-refundable after production begins</li>
                        </ul>

                        <h2>7. Product Warranty</h2>
                        <h3>Standard Warranty</h3>
                        <p>We warrant that our products will be free from material defects for a period of:</p>
                        <ul>
                            <li>12 months for standard tools</li>
                            <li>6 months for consumable items</li>
                            <li>As agreed for custom products</li>
                        </ul>

                        <h3>Warranty Exclusions</h3>
                        <p>This warranty does not cover:</p>
                        <ul>
                            <li>Normal wear and tear</li>
                            <li>Damage from misuse or improper maintenance</li>
                            <li>Modifications made by the customer</li>
                            <li>Use beyond specified capacity or purpose</li>
                        </ul>

                        <h2>8. Limitation of Liability</h2>
                        <p>To the maximum extent permitted by law:</p>
                        <ul>
                            <li>Our liability is limited to the purchase price of the product</li>
                            <li>We are not liable for indirect, consequential, or punitive damages</li>
                            <li>We are not responsible for lost profits or business interruption</li>
                            <li>These limitations apply regardless of the legal theory</li>
                        </ul>

                        <h2>9. Indemnification</h2>
                        <p>You agree to indemnify, defend, and hold harmless RADS Tooling, its officers, directors, employees, and agents from any claims, damages, losses, or expenses arising from:</p>
                        <ul>
                            <li>Your violation of these terms</li>
                            <li>Your use or misuse of our products</li>
                            <li>Your violation of any law or third-party rights</li>
                            <li>Content or designs you provide for custom products</li>
                        </ul>

                        <h2>10. Intellectual Property Rights</h2>
                        <p>All content on our website, including text, graphics, logos, images, and software, is the property of RADS Tooling or its licensors and is protected by intellectual property laws. You may not:</p>
                        <ul>
                            <li>Copy, modify, or distribute our content without permission</li>
                            <li>Use our trademarks without written consent</li>
                            <li>Reverse engineer or attempt to extract source code</li>
                            <li>Use automated systems to access our website</li>
                        </ul>

                        <h2>11. Privacy and Data Protection</h2>
                        <p>Your use of our services is also governed by our Privacy Policy, which is incorporated into these terms by reference. By using our services, you consent to our collection and use of your information as described in the Privacy Policy.</p>

                        <h2>12. Dispute Resolution</h2>
                        <h3>Governing Law</h3>
                        <p>These terms are governed by the laws of the Republic of the Philippines, without regard to conflict of law principles.</p>

                        <h3>Arbitration</h3>
                        <p>Any disputes arising from these terms or your use of our services shall be resolved through binding arbitration in accordance with the rules of the Philippine Dispute Resolution Center, unless otherwise agreed by both parties.</p>

                        <h2>13. Modifications to Terms</h2>
                        <p>We reserve the right to modify these Terms & Conditions at any time. Changes will be effective immediately upon posting to our website. Your continued use of our services after any modifications constitutes acceptance of the updated terms.</p>

                        <p>We will make reasonable efforts to notify registered users of material changes via email or website notification.</p>

                        <h2>14. Severability</h2>
                        <p>If any provision of these terms is found to be unenforceable or invalid, that provision shall be limited or eliminated to the minimum extent necessary, and the remaining provisions shall remain in full force and effect.</p>

                        <h2>15. Entire Agreement</h2>
                        <p>These Terms & Conditions, together with our Privacy Policy and any other agreements explicitly referenced herein, constitute the entire agreement between you and RADS Tooling regarding the use of our services.</p>

                        <h2>16. Contact Information</h2>
                        <p>For questions or concerns regarding these Terms & Conditions, please contact us:</p>
                        <ul>
                            <li><strong>Email:</strong> RadsTooling@gmail.com</li>
                            <li><strong>Phone:</strong> +63 (976) 228-4270</li>
                            <li><strong>Address:</strong> Green Breeze, Piela, Dasmariñas, Cavite, Philippines</li>
                            <li><strong>Business Hours:</strong> Monday-Saturday, 8:00 AM - 5:00 PM</li>
                        </ul>

                        <h2>17. Acknowledgment</h2>
                        <p>By using RADS Tooling's website and services, you acknowledge that you have read, understood, and agree to be bound by these Terms & Conditions.</p>
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