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
    <link rel="stylesheet" href="/RADS-TOOLING/assets/CSS/about.css">
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
                <a href="/RADS-TOOLING/customer/homepage.php" class="nav-menu-item ">Home</a>
                <a href="/RADS-TOOLING/customer/about.php" class="nav-menu-item">About</a>
                <a href="/RADS-TOOLING/public/products.php" class="nav-menu-item">Products</a>
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
                        <li><a href="/RADS-TOOLING/customer/homepage.php">Home</a></li>
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
                </div>
            </div>
        </footer>
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
                    await fetch('/RADS-TOOLING/backend/api/auth.php?action=logout', {
                        method: 'POST',
                        credentials: 'same-origin'
                    });

                    localStorage.removeItem('cart');
                    window.location.href = '/RADS-TOOLING/public/index.php';

                } catch (error) {
                    console.error('Logout error:', error);
                    localStorage.removeItem('cart');
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