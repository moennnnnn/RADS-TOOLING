<?php
require_once __DIR__ . '/../backend/config/app.php';
require_once __DIR__ . '/../includes/guard.php';
guard_require_staff();

// Initialize user session data
$isLoggedIn = false;
$adminName = 'Admin';
$userRole = 'Secretary'; // Default role

if (isset($_SESSION['staff'])) {
    $isLoggedIn = true;
    $adminName = $_SESSION['staff']['full_name'] ?? 'Admin';
    $userRole = $_SESSION['staff']['role'] ?? 'Secretary';
} elseif (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    $isLoggedIn = true;
    $adminName = $_SESSION['admin_name'] ?? 'Admin';
    $userRole = 'Owner'; // Legacy session defaults to Owner
}

// If not logged in, redirect to login
if (!$isLoggedIn) {
    header('Location: login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Dashboard | Rads Tooling</title>
    <!-- Icons & Fonts -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" />
    <!-- Quill Rich Text Editor (No API Key Required) -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <!-- Main CSS -->
    <link rel="stylesheet" href="/RADS-TOOLING/assets/CSS/style.css" />
    <link rel="stylesheet" href="/RADS-TOOLING/assets/CSS/chat-admin.css" />
    <link rel="stylesheet" href="/RADS-TOOLING/assets/CSS/content_mgmt.css" />
    <style>
        /* Enhanced modal and loading styles */
        .modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .25);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal.show {
            display: flex;
        }

        .modal-content {
            background: #fff;
            border-radius: 16px;
            padding: 1.5rem 2rem;
            max-width: 700px;
            width: min(700px, 92vw);
            max-height: 80vh;
            overflow: auto;
            position: relative;
        }

        .modal-close {
            position: absolute;
            right: .75rem;
            top: .5rem;
            font-size: 1.5rem;
            border: 0;
            background: none;
            cursor: pointer;
        }

        .loading-overlay {
            position: fixed;
            inset: 0;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            flex-direction: column;
            gap: 1rem;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
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

        /* Role-based badge styles */
        .badge-owner {
            background: #8e44ad;
        }

        .badge-admin {
            background: #3498db;
        }

        .badge-secretary {
            background: #95a5a6;
        }

        /* Button action styles */
        .btn-action {
            background: none;
            border: none;
            cursor: pointer;
            padding: 6px;
            font-size: 1.2em;
            margin-right: 4px;
            border-radius: 6px;
            transition: background .15s, color .15s;
        }

        .btn-action.btn-edit {
            color: #3498db;
        }

        .btn-action.btn-edit:hover {
            background: #e3edfb;
            color: #17416d;
        }

        .btn-action.btn-delete {
            color: #e74c3c;
        }

        .btn-action.btn-delete:hover {
            background: #faeaea;
            color: #a90f0f;
        }

        .btn-action.btn-view {
            color: #27ae60;
        }

        .btn-action.btn-view:hover {
            background: #eafaf1;
            color: #1e8449;
        }

        /* Enhanced form styles for profile editing */
        .edit-profile-pic-group {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 1rem;
        }

        .edit-profile-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 0.5rem;
            border: 3px solid var(--brand);
        }

        /* ===== CHAT SUPPORT INTEGRATION STYLES ===== */

        /* Override chat container for dashboard */
        section[data-section="chat"] .rt-admin-container {
            padding: 0 !important;
            margin: 0 !important;
            max-width: 100% !important;
        }

        /* Adjust chat boxes for dashboard */
        section[data-section="chat"] .rt-admin-box {
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        /* Make thread list scrollable */
        section[data-section="chat"] .rt-thread-list {
            max-height: 500px;
            overflow-y: auto;
        }

        /* Responsive adjustments */
        @media (max-width: 1200px) {
            section[data-section="chat"] .rt-admin-container {
                flex-direction: column;
            }

            section[data-section="chat"] .rt-admin-sidebar {
                width: 100%;
            }
        }

        /* Improve button visibility in dashboard */
        section[data-section="chat"] .rt-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        /* Smooth transitions */
        section[data-section="chat"] .rt-thread-item {
            transition: all 0.2s ease;
        }

        section[data-section="chat"] .rt-faq-row {
            transition: background 0.2s ease;
        }

        section[data-section="chat"] .rt-faq-row:hover {
            background: #f9fbfd;
        }
    </style>
</head>

<body>
    <!-- Loading overlay for initialization -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner"></div>
        <p>Loading dashboard...</p>
    </div>

    <!-- ===== SIDEBAR ===== -->
    <aside class="sidebar">
        <div class="sidebar-logo">
            <span class="logo-text">R</span>ADS <span class="logo-text">T</span>OOLING
        </div>
        <div class="sidebar-nav">
            <!-- Dashboard - Always visible -->
            <span class="nav-item active" data-section="dashboard">
                <a href="#">
                    <span class="material-symbols-rounded">home</span>
                    <h2>Dashboard</h2>
                </a>
            </span>

            <!-- Account Management - Owner and Admin only -->
            <span class="nav-item" data-section="account">
                <a href="#">
                    <span class="material-symbols-rounded">person</span>
                    <h2>Account Management</h2>
                </a>
            </span>

            <!-- Customer Management - Owner and Admin only -->
            <span class="nav-item" data-section="customer">
                <a href="#">
                    <span class="material-symbols-rounded">groups</span>
                    <h2>Customer Management</h2>
                </a>
            </span>

            <!-- Products Management - Owner and Admin only -->
            <span class="nav-item" data-section="products">
                <a href="#">
                    <span class="material-symbols-rounded">door_sliding</span>
                    <h2>Products Management</h2>
                </a>
            </span>

            <!-- Order Management - All roles -->
            <span class="nav-item" data-section="orders">
                <a href="#">
                    <span class="material-symbols-rounded">list_alt</span>
                    <h2>Order Management</h2>
                </a>
            </span>

            <!-- Report Generation - All roles -->
            <span class="nav-item" data-section="reports">
                <a href="#">
                    <span class="material-symbols-rounded">insights</span>
                    <h2>Report Generation</h2>
                </a>
            </span>

            <!-- Content Management - Owner and Admin only -->
            <span class="nav-item" data-section="content">
                <a href="#">
                    <span class="material-symbols-rounded">campaign</span>
                    <h2>Content Management</h2>
                </a>
            </span>

            <!-- Feedback & Ratings - All roles -->
            <span class="nav-item" data-section="feedback">
                <a href="#">
                    <span class="material-symbols-rounded">star</span>
                    <h2>Feedback & Ratings</h2>
                </a>
            </span>

            <!-- Chat Support - All roles -->
            <span class="nav-item" data-section="chat">
                <a href="#">
                    <span class="material-symbols-rounded">chat</span>
                    <h2>Chat Support</h2>
                </a>
            </span>

            <!-- Payment Verification - Owner and Admin only -->
            <span class="nav-item" data-section="payment">
                <a href="#">
                    <span class="material-symbols-rounded">credit_card</span>
                    <h2>Payment Verification</h2>
                </a>
            </span>
        </div>
    </aside>

    <!-- ===== MAIN CONTENT ===== -->
    <div class="main-content">
        <!-- ===== TOPBAR ===== -->
        <header class="topbar">
            <div class="topbar-profile">
                <span class="admin-name">Welcome, <?php echo htmlspecialchars($adminName); ?></span>
                <div class="profile-menu">
                    <img src="/RADS-TOOLING/assets/images/profile.png" alt="Admin" class="profile-avatar" id="profileIcon" />
                    <div class="profile-dropdown" id="profileDropdown">
                        <button id="btnEditProfile">
                            <span class="material-symbols-rounded">person</span>
                            Edit Profile
                        </button>
                        <button id="btnChangePassword">
                            <span class="material-symbols-rounded">key</span>
                            Change Password
                        </button>
                        <button id="logoutBtn">
                            <span class="material-symbols-rounded">logout</span>
                            Logout
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <!-- ===== DASHBOARD ===== -->
        <section class="main-section show" data-section="dashboard">
            <div class="dashboard-widgets">
                <div class="dashboard-card card-orders">
                    <div class="card-title">Total Orders</div>
                    <div class="card-value" id="dash-orders">Loading...</div>
                </div>
                <div class="dashboard-card card-sales">
                    <div class="card-title">Total Sales</div>
                    <div class="card-value" id="dash-sales">Loading...</div>
                </div>
                <div class="dashboard-card card-customers">
                    <div class="card-title">Customers</div>
                    <div class="card-value" id="dash-customers">Loading...</div>
                </div>
                <div class="dashboard-card card-feedback">
                    <div class="card-title">Feedback Received</div>
                    <div class="card-value" id="dash-feedback">Loading...</div>
                </div>
            </div>

            <div class="dashboard-row">
                <div class="dashboard-chart">
                    <h2>Sales Overview</h2>
                    <canvas id="salesChart"></canvas>
                </div>
                <div class="dashboard-orders">
                    <h2>Recent Orders</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody id="dashRecentOrders">
                            <tr>
                                <td colspan="5" style="text-align:center;">Loading...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="dashboard-feedback">
                <h2>Latest Feedback</h2>
                <ul id="dashFeedbackList">
                    <li>Loading feedback...</li>
                </ul>
            </div>
        </section>

        <!-- ===== ACCOUNT MANAGEMENT ===== -->
        <section class="main-section" data-section="account">
            <div class="section-header">
                <h1>Account Management</h1>
                <button class="btn-add-user" onclick="openModal('addUserModal')">
                    <span class="material-symbols-rounded">person_add</span> Add Staff/Admin
                </button>
            </div>
            <div class="account-table-container">
                <table class="account-table">
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Username</th>
                            <th>Full Name</th>
                            <th>Role</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="userTableBody">
                        <tr>
                            <td colspan="6" style="text-align:center;">Loading users...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- ===== CUSTOMER MANAGEMENT ===== -->
        <section class="main-section" data-section="customer">
            <div class="section-header">
                <h1>Customer Management</h1>
            </div>
            <div class="customer-controls" style="margin-bottom:1rem">
                <input type="text" id="customer-search" placeholder="Search by name, email, or username..." />
            </div>
            <div class="customer-table-container">
                <table class="customer-table">
                    <thead>
                        <tr>
                            <th>Customer ID</th>
                            <th>Username</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="customerTableBody">
                        <tr>
                            <td colspan="6" style="text-align:center;">Loading customers...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- ===== ORDER MANAGEMENT ===== -->
        <section class="main-section" data-section="orders">
            <div class="section-header">
                <h1>Order Management</h1>
            </div>
            <div class="order-controls">
                <input type="text" class="order-search" id="order-search" placeholder="Search orders..." />
                <select class="order-filter" id="statusFilter">
                    <option value="">All Status</option>
                    <option>Pending</option>
                    <option>Processing</option>
                    <option>Completed</option>
                    <option>Cancelled</option>
                </select>
                <select class="order-filter" id="paymentFilter">
                    <option value="">All Payment Status</option>
                    <option>Fully Paid</option>
                    <option>With Balance</option>
                </select>
            </div>
            <div class="orders-table-container">
                <table class="orders-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Item</th>
                            <th>Customer</th>
                            <th>Date Ordered</th>
                            <th>Total</th>
                            <th>Payment Status</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="orderTableBody">
                        <tr>
                            <td colspan="8" style="text-align:center;">Loading orders...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- ===== PRODUCTS MANAGEMENT ===== -->
        <section class="main-section" data-section="products">
            <div class="section-header">
                <h1>Product Management</h1>
                <div class="product-header-actions">
                    <button class="btn-manage-customization" onclick="openModal('manageCustomizationModal')">
                        <span class="material-symbols-rounded">tune</span> Manage Customization Options
                    </button>
                    <button class="btn-add-product" onclick="openModal('addProductModal')">
                        <span class="material-symbols-rounded">add_circle</span> Add Product
                    </button>
                </div>
            </div>

            <div class="products-controls">
                <input type="text" class="products-search" id="product-search"
                    placeholder="Search by name or description..." />
                <select class="products-filter" id="product-filter">
                    <option value="">All Types</option>
                    <option>Kitchen</option>
                    <option>Bathroom</option>
                    <option>Living Room</option>
                    <option>Bedroom</option>
                    <option>Office</option>
                </select>
            </div>

            <div class="products-table-container">
                <table class="products-table">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Product Name</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="productTableBody">
                        <tr>
                            <td colspan="7" style="text-align:center;">Loading products...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- ===== CONTENT MANAGEMENT ===== -->
        <section class="main-section" data-section="content">
            <div class="section-header">
                <h1>Content Management</h1>
                <button class="btn-edit-content" id="btnEditContent">
                    <span class="material-symbols-rounded">edit</span> Edit Content
                </button>
            </div>

            <!-- Homepage Type Selector -->
            <div class="cm-page-selector">
                <label for="homepageType">Editing:</label>
                <select id="homepageType" class="homepage-type-select">
                    <option value="home_public">Public Landing Page</option>
                    <option value="home_customer">Customer Homepage</option>
                </select>
            </div>

            <!-- Tab Navigation -->
            <div class="cm-tabs">
                <button class="cm-tab active" data-page="home_public">
                    <span class="material-symbols-rounded">home</span>
                    Homepage
                </button>
                <button class="cm-tab" data-page="about">
                    <span class="material-symbols-rounded">info</span>
                    About Us
                </button>
                <button class="cm-tab" data-page="privacy">
                    <span class="material-symbols-rounded">shield</span>
                    Privacy Policy
                </button>
                <button class="cm-tab" data-page="terms">
                    <span class="material-symbols-rounded">gavel</span>
                    Terms & Conditions
                </button>
            </div>

            <!-- Content Preview Area -->
            <div class="cm-preview-container">
                <div class="cm-preview-card" id="previewCard">
                    <iframe id="previewIframe" style="width:100%; height:600px; border:1px solid #e3edfb; border-radius:8px;"></iframe>
                </div>
            </div>
        </section>

        <!-- ===== REPORT GENERATION ===== -->
        <section class="main-section" data-section="reports">
            <div class="section-header">
                <h1>Report Generation</h1>
                <button class="btn-export" id="exportReportBtn">
                    <span class="material-symbols-rounded">download</span> Export Report
                </button>
            </div>

            <div class="report-controls">
                <label>From: <input type="date" class="report-date" id="report-from" /></label>
                <label>To: <input type="date" class="report-date" id="report-to" /></label>
                <button class="btn-generate" id="generateReportBtn">Generate Report</button>
            </div>

            <!-- Visual Summary Cards (backend-ready placeholders) -->
            <div class="report-summary">
                <div class="summary-card card-total-sales" style="cursor:pointer">
                    <div class="summary-icon" style="background:#3db36b1a;"><span class="material-symbols-rounded"
                            style="color:#3db36b;">payments</span></div>
                    <div class="summary-title">Total Sales</div>
                    <div class="summary-value"><span id="rg-total-sales">₱0</span></div>
                </div>
                <div class="summary-card card-total-orders" style="cursor:pointer">
                    <div class="summary-icon" style="background:#3498db1a;"><span class="material-symbols-rounded"
                            style="color:#3498db;">shopping_cart</span></div>
                    <div class="summary-title">Total Orders</div>
                    <div class="summary-value"><span id="rg-total-orders">0</span></div>
                </div>
                <div class="summary-card card-average-order" style="cursor:pointer">
                    <div class="summary-icon" style="background:#f7b7311a;"><span class="material-symbols-rounded"
                            style="color:#f7b731;">equalizer</span></div>
                    <div class="summary-title">Avg. Order Value</div>
                    <div class="summary-value"><span id="rg-avg-order">₱0</span></div>
                </div>
                <div class="summary-card card-fully-paid" style="cursor:pointer">
                    <div class="summary-icon" style="background:#2f5b881a;"><span class="material-symbols-rounded"
                            style="color:#2f5b88;">verified</span></div>
                    <div class="summary-title">Fully Paid Orders</div>
                    <div class="summary-value"><span id="rg-fully-paid">0</span></div>
                </div>
                <div class="summary-card card-cancelled" style="cursor:pointer">
                    <div class="summary-icon" style="background:#e14d4d1a;"><span class="material-symbols-rounded"
                            style="color:#e14d4d;">block</span></div>
                    <div class="summary-title">Cancelled Orders</div>
                    <div class="summary-value"><span id="rg-cancelled">0</span></div>
                </div>
                <div class="summary-card card-pending" style="cursor:pointer">
                    <div class="summary-icon" style="background:#ffc4001a;"><span class="material-symbols-rounded"
                            style="color:#ffc400;">pending</span></div>
                    <div class="summary-title">Pending Orders</div>
                    <div class="summary-value"><span id="rg-pending">0</span></div>
                </div>
                <div class="summary-card card-new-customer" style="cursor:pointer">
                    <div class="summary-icon" style="background:#8e44ad1a;"><span class="material-symbols-rounded"
                            style="color:#8e44ad;">group_add</span></div>
                    <div class="summary-title">New Customers</div>
                    <div class="summary-value"><span id="rg-new-customers">0</span></div>
                </div>
                <div class="summary-card card-feedbacks" style="cursor:pointer">
                    <div class="summary-icon" style="background:#2980b91a;"><span class="material-symbols-rounded"
                            style="color:#2980b9;">star</span></div>
                    <div class="summary-title">Feedbacks</div>
                    <div class="summary-value"><span id="rg-feedbacks">0</span></div>
                </div>
                <div class="summary-card card-most-ordered" style="cursor:pointer">
                    <div class="summary-icon" style="background:#26c6da1a;"><span class="material-symbols-rounded"
                            style="color:#26c6da;">category</span></div>
                    <div class="summary-title">Most Ordered Item</div>
                    <div class="summary-value"><span id="rg-most-item">—</span></div>
                </div>
            </div>
        </section>

        <!-- ===== FEEDBACK ===== -->
        <section class="main-section" data-section="feedback">
            <div class="section-header">
                <h1>Feedback & Ratings</h1>
            </div>
            <div class="feedback-table-container">
                <table class="feedback-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Rating</th>
                            <th>Comment</th>
                            <th>Order</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody id="feedbackTableBody">
                        <tr>
                            <td colspan="6" style="text-align:center;">Loading feedback...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- ===== CHAT SUPPORT ===== -->
        <section class="main-section" data-section="chat">
            <div class="section-header">
                <h1>Chat Support</h1>
                <div class="rt-user-info" style="font-size: 14px; color: #666;">
                    Manage customer inquiries and FAQs
                </div>
            </div>

            <div class="rt-admin-container" style="padding: 0; margin: 0; max-width: 100%;">
                <!-- Left: Threads Sidebar -->
                <aside class="rt-admin-sidebar">
                    <div class="rt-admin-box">
                        <h3>Customer Threads</h3>
                        <input type="text" id="rtThreadSearch" class="rt-admin-search" placeholder="Search threads..." />
                        <div class="rt-thread-list" id="rtThreadList">
                            <div style="padding:12px;color:#999;">Loading threads...</div>
                        </div>
                    </div>
                </aside>

                <!-- Right: Main Chat Area -->
                <main class="rt-admin-main">
                    <!-- Conversation Area -->
                    <div class="rt-admin-box">
                        <h3>Conversation</h3>
                        <div class="rt-admin-conv" id="rtAdminConv"></div>
                        <div class="rt-input-row">
                            <input type="text" id="rtAdminMsg" placeholder="Type a reply..." />
                            <button id="rtAdminSend" class="rt-btn rt-btn-primary">
                                <span class="material-symbols-rounded" style="vertical-align: middle; font-size: 18px;">send</span>
                                Send
                            </button>
                        </div>
                    </div>

                    <!-- FAQ Management -->
                    <div class="rt-admin-box">
                        <h3>Manage FAQs (Auto-Reply System)</h3>
                        <div class="rt-input-row">
                            <input type="text" id="rtFaqQ" placeholder="Question (e.g., Do you deliver?)" style="flex: 1;" />
                            <input type="text" id="rtFaqA" placeholder="Answer (e.g., Yes, we deliver within Cavite...)" style="flex: 2;" />
                            <button id="rtFaqSave" class="rt-btn rt-btn-primary">
                                <span class="material-symbols-rounded" style="vertical-align: middle; font-size: 18px;">save</span>
                                Save FAQ
                            </button>
                        </div>
                        <table class="rt-faq-table">
                            <thead>
                                <tr>
                                    <th>Question</th>
                                    <th style="width:180px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="rtFaqTbody">
                                <tr>
                                    <td colspan="2" style="text-align: center; padding: 20px; color: #999;">Loading FAQs...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </main>
            </div>
        </section>

        <!-- ===== PAYMENT VERIFICATION ===== -->
        <section class="main-section" data-section="payment">
            <div class="section-header">
                <h1>Payment Verification</h1>
            </div>
            <div class="payments-table-container">
                <table class="payments-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Proof</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="paymentsTableBody">
                        <tr>
                            <td colspan="6" style="text-align:center;">Loading payments...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- ===== MODALS ===== -->

        <!-- Edit Profile Modal -->
        <div class="modal" id="editProfileModal">
            <div class="modal-content">
                <button class="modal-close" aria-label="Close">×</button>
                <h2>Edit Profile</h2>
                <form id="editProfileForm">
                    <div class="edit-profile-pic-group">
                        <img id="editProfileAvatar" class="edit-profile-avatar"
                            src="/RADS-TOOLING/assets/images/profile.png" alt="Avatar" />
                        <label class="edit-pic-label" for="editProfilePic">Change Photo</label>
                        <input id="editProfilePic" type="file" accept="image/*" hidden />
                    </div>
                    <input id="ep-fullname" name="full_name" placeholder="Full Name" required />
                    <input id="ep-username" name="username" placeholder="Username" required />
                    <div style="display:flex;gap:.5rem;justify-content:flex-end">
                        <button type="submit" class="primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>


        <!-- Change Password Modal -->
        <div class="modal" id="changePasswordModal">
            <div class="modal-content">
                <button class="modal-close" aria-label="Close">×</button>
                <h2>Change Password</h2>
                <form id="changePasswordForm">
                    <input id="cp-old" type="password" placeholder="Current Password" required />
                    <input id="cp-new" type="password" placeholder="New Password" required />
                    <input id="cp-confirm" type="password" placeholder="Confirm New Password" required />
                    <div style="display:flex;gap:.5rem;justify-content:flex-end">
                        <button type="submit" class="primary">Update Password</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Add User Modal -->
        <div class="modal" id="addUserModal">
            <div class="modal-content">
                <button class="modal-close" aria-label="Close">×</button>
                <h2>Add Staff/Admin</h2>
                <form id="addUserForm">
                    <input id="au-username" name="username" placeholder="Username" required />
                    <input id="au-fullname" name="full_name" placeholder="Full Name" required />
                    <select id="au-role" name="role" required>
                        <option value="">Select Role</option>
                        <option value="Owner">Owner</option>
                        <option value="Admin">Admin</option>
                        <option value="Secretary">Secretary</option>
                    </select>
                    <input id="au-password" type="password" name="password" placeholder="Temporary Password" required />
                    <div style="display:flex;gap:.5rem;justify-content:flex-end">
                        <button type="submit" class="primary">Create User</button>
                    </div>
                </form>
            </div>
        </div>


        <!-- Add Product Modal (skeleton) -->
        <div class="modal" id="addProductModal">
            <div class="modal-content">
                <button class="modal-close" onclick="closeModal('addProductModal')">×</button>
                <h2>Add Product</h2>
                <form id="addProductForm">
                    <input id="ap-name" name="name" placeholder="Product Name" required />
                    <input id="ap-type" name="category" placeholder="Type/Category" />
                    <textarea id="ap-desc" name="description" placeholder="Description"></textarea>
                    <input id="ap-price" name="price" type="number" step="0.01" placeholder="Price" required />
                    <input id="ap-stock" name="stock" type="number" placeholder="Stock" required />
                    <label class="edit-pic-label" for="ap-image">Upload Photo</label>
                    <input id="ap-image" name="image" type="file" accept="image/*" hidden />
                    <button type="submit">Save Product</button>
                </form>
            </div>
        </div>

        <!-- Manage Customization Modal (placeholder) -->
        <div class="modal" id="manageCustomizationModal">
            <div class="modal-content">
                <button class="modal-close" onclick="closeModal('manageCustomizationModal')">×</button>
                <h2>Manage Customization Options</h2>
                <div id="customizationPanel"><!-- DB-READY --></div>
            </div>
        </div>

        <!-- View Order Modal (skeleton targets) -->
        <div class="modal" id="viewOrderModal">
            <div class="modal-content">
                <button class="modal-close" aria-label="Close">×</button>
                <h2>Order Details</h2>
                <p>Order: <span id="vo-code">—</span></p>
                <p>Customer: <span id="vo-customer">—</span></p>
                <p>Date: <span id="vo-date">—</span></p>
                <p>Total: <span id="vo-total">₱0</span></p>
                <p>Status: <span id="vo-status">—</span></p>
                <p>Payment: <span id="vo-payment">—</span></p>
            </div>
        </div>

        <!-- Universal Confirm Modal (instant, no cancel) -->
        <div class="modal" id="confirmModal">
            <div class="modal-content" style="max-width:360px;text-align:center;">
                <button class="modal-close" aria-label="Close">×</button>
                <h2 id="confirmTitle">Confirm</h2>
                <p id="confirmMessage">Are you sure?</p>
                <button id="confirmOkBtn" class="primary" type="button">OK</button>
            </div>
        </div>

        <!-- ===== Scripts ===== -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="/RADS-TOOLING/assets/JS/script.js"></script>

        <script>
            // Hide loading overlay once everything is loaded
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(() => {
                    const loadingOverlay = document.getElementById('loadingOverlay');
                    if (loadingOverlay) {
                        loadingOverlay.style.display = 'none';
                    } else if (targetSection === 'content') {
                        // Initialize Content Management module when tab is clicked
                        if (typeof CM !== 'undefined') {
                            setTimeout(() => CM.init(), 100);
                        }
                    }
                }, 1000); // Small delay to ensure proper initialization
            });
        </script>

        <!-- Chat-specific modals -->
        <div class="modal" id="chatDeleteModal" style="display:none;">
            <div class="modal-content modal-small">
                <button class="modal-close" onclick="closeChatModal('chatDeleteModal')">
                    <span class="material-symbols-rounded">close</span>
                </button>
                <div class="modal-icon-wrapper">
                    <div class="modal-icon warning">
                        <span class="material-symbols-rounded">delete</span>
                    </div>
                </div>
                <h2 class="modal-title">Delete FAQ</h2>
                <p class="modal-message">Delete this FAQ permanently? This action cannot be undone.</p>
                <div class="modal-actions">
                    <button onclick="closeChatModal('chatDeleteModal')" class="btn-modal-secondary">Cancel</button>
                    <button id="chatDeleteConfirm" class="btn-modal-danger">Delete</button>
                </div>
            </div>
        </div>

        <div class="modal" id="chatSuccessModal" style="display:none;">
            <div class="modal-content modal-small">
                <button class="modal-close" onclick="closeChatModal('chatSuccessModal')">
                    <span class="material-symbols-rounded">close</span>
                </button>
                <div class="modal-icon-wrapper">
                    <div class="modal-icon success">
                        <span class="material-symbols-rounded">check_circle</span>
                    </div>
                </div>
                <h2 class="modal-title" id="chatSuccessTitle">Success</h2>
                <p class="modal-message" id="chatSuccessMessage">Operation completed successfully</p>
                <div class="modal-actions">
                    <button onclick="closeChatModal('chatSuccessModal')" class="btn-modal-primary">OK</button>
                </div>
            </div>
        </div>

        <div class="modal" id="chatErrorModal" style="display:none;">
            <div class="modal-content modal-small">
                <button class="modal-close" onclick="closeChatModal('chatErrorModal')">
                    <span class="material-symbols-rounded">close</span>
                </button>
                <div class="modal-icon-wrapper">
                    <div class="modal-icon error">
                        <span class="material-symbols-rounded">error</span>
                    </div>
                </div>
                <h2 class="modal-title">Error</h2>
                <p class="modal-message" id="chatErrorMessage">An error occurred</p>
                <div class="modal-actions">
                    <button onclick="closeChatModal('chatErrorModal')" class="btn-modal-primary">OK</button>
                </div>
            </div>
        </div>

        <style>
            /* Modal Styles */
            .modal {
                display: none;
                position: fixed;
                inset: 0;
                background: rgba(0, 0, 0, 0.5);
                z-index: 9999;
                align-items: center;
                justify-content: center;
            }

            .modal.show {
                display: flex;
            }

            .modal-dialog {
                width: 95vw;
                height: 95vh;
                margin: auto;
            }

            .modal-fullscreen .modal-content {
                width: 100%;
                height: 100%;
                background: white;
                border-radius: 8px;
                display: flex;
                flex-direction: column;
            }

            .modal-header {
                padding: 20px;
                border-bottom: 1px solid #e0e0e0;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .modal-header h2 {
                margin: 0;
                font-size: 1.5rem;
            }

            .modal-close {
                background: none;
                border: none;
                cursor: pointer;
                padding: 8px;
                border-radius: 4px;
            }

            .modal-close:hover {
                background: #f5f5f5;
            }

            .modal-body {
                flex: 1;
                overflow: hidden;
            }

            .editor-layout {
                display: grid;
                grid-template-columns: 500px 1fr;
                height: 100%;
                gap: 0;
            }

            /* LEFT Panel */
            .editor-panel {
                border-right: 1px solid #e0e0e0;
                display: flex;
                flex-direction: column;
                background: #fafafa;
            }

            .editor-header {
                padding: 16px;
                border-bottom: 1px solid #e0e0e0;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .preview-status {
                padding: 4px 12px;
                border-radius: 12px;
                font-size: 0.85rem;
                font-weight: 600;
            }

            .status-draft {
                background: #fff3cd;
                color: #856404;
            }

            .status-published {
                background: #d4edda;
                color: #155724;
            }

            .editor-fields {
                flex: 1;
                overflow-y: auto;
                padding: 20px;
            }

            .editor-section {
                background: white;
                padding: 20px;
                border-radius: 8px;
                margin-bottom: 20px;
            }

            .editor-section h3 {
                display: flex;
                align-items: center;
                gap: 8px;
                margin-bottom: 16px;
                font-size: 1.1rem;
            }

            .editor-section label {
                display: block;
                margin: 12px 0 6px;
                font-weight: 600;
                font-size: 0.9rem;
            }

            .quill-container {
                height: 150px;
                margin-bottom: 20px;
            }

            .form-input {
                width: 100%;
                padding: 10px;
                border: 1px solid #ddd;
                border-radius: 4px;
                font-size: 0.95rem;
            }

            .btn-upload {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                padding: 10px 16px;
                background: #1f4e74;
                color: white;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                margin-top: 10px;
            }

            .btn-upload:hover {
                background: #163a56;
            }

            .alert {
                padding: 12px;
                border-radius: 6px;
                margin-bottom: 16px;
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .alert-info {
                background: #cfe2ff;
                color: #084298;
            }

            .help-text {
                font-size: 0.85rem;
                color: #666;
                margin-top: 6px;
            }

            /* RIGHT Panel */
            .preview-panel {
                display: flex;
                flex-direction: column;
                background: white;
            }

            .preview-header {
                padding: 16px;
                border-bottom: 1px solid #e0e0e0;
            }

            .preview-iframe-container {
                flex: 1;
                position: relative;
                overflow: hidden;
            }

            .preview-iframe-container iframe {
                width: 100%;
                height: 100%;
                border: none;
            }

            /* Modal Footer */
            .modal-footer {
                padding: 16px 20px;
                border-top: 1px solid #e0e0e0;
                display: flex;
                gap: 12px;
                justify-content: flex-end;
            }

            .btn {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                padding: 10px 20px;
                border: none;
                border-radius: 6px;
                cursor: pointer;
                font-weight: 600;
            }

            .btn-secondary {
                background: #6c757d;
                color: white;
            }

            .btn-primary {
                background: #0d6efd;
                color: white;
            }

            .btn-success {
                background: #28a745;
                color: white;
            }

            .btn:hover {
                opacity: 0.9;
            }

            /* Carousel Manager */
            .carousel-item-edit {
                display: flex;
                gap: 12px;
                align-items: center;
                padding: 12px;
                background: #f8f9fa;
                border-radius: 6px;
                margin-bottom: 12px;
            }

            .carousel-item-edit img {
                width: 80px;
                height: 80px;
                object-fit: cover;
                border-radius: 4px;
            }

            .carousel-info {
                flex: 1;
                display: flex;
                flex-direction: column;
                gap: 8px;
            }

            .carousel-info input {
                padding: 6px;
                border: 1px solid #ddd;
                border-radius: 4px;
            }

            .carousel-actions {
                display: flex;
                gap: 6px;
            }

            .carousel-actions button {
                padding: 6px;
                border: none;
                background: #fff;
                border-radius: 4px;
                cursor: pointer;
            }

            .carousel-actions button:hover {
                background: #e9ecef;
            }

            .btn-delete {
                color: #dc3545;
            }

            /* Toast Notifications */
            .toast-container {
                position: fixed;
                bottom: 20px;
                right: 20px;
                z-index: 99999;
            }

            .toast {
                padding: 12px 20px;
                margin-top: 10px;
                border-radius: 6px;
                color: white;
                opacity: 0;
                transform: translateY(20px);
                transition: all 0.3s;
            }

            .toast.show {
                opacity: 1;
                transform: translateY(0);
            }

            .toast-info {
                background: #0dcaf0;
            }

            .toast-success {
                background: #28a745;
            }

            .toast-error {
                background: #dc3545;
            }
        </style>

        <script>
            function closeChatModal(modalId) {
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.style.display = 'none';
                }
            }

            function showChatSuccess(title, message) {
                const modal = document.getElementById('chatSuccessModal');
                const titleEl = document.getElementById('chatSuccessTitle');
                const messageEl = document.getElementById('chatSuccessMessage');

                if (titleEl) titleEl.textContent = title;
                if (messageEl) messageEl.textContent = message;
                if (modal) modal.style.display = 'flex';
            }

            function showChatError(message) {
                const modal = document.getElementById('chatErrorModal');
                const messageEl = document.getElementById('chatErrorMessage');

                if (messageEl) messageEl.textContent = message;
                if (modal) modal.style.display = 'flex';
            }
        </script>

        <!-- ===== CONTENT MANAGEMENT EDIT MODAL ===== -->
        <div class="modal" id="editModal">
            <div class="modal-dialog modal-fullscreen">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 id="modalTitle">Edit Content</h2>
                        <button class="modal-close" id="btnCloseModal">
                            <span class="material-symbols-rounded">close</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <!-- Split Layout: Editor LEFT | Preview RIGHT -->
                        <div class="editor-layout">
                            <!-- LEFT: Editable Fields -->
                            <div class="editor-panel">
                                <div class="editor-header">
                                    <h3>Edit Fields</h3>
                                    <div class="editor-status">
                                        <span id="previewStatus" class="preview-status status-published">Published</span>
                                    </div>
                                </div>

                                <!-- Notice for Customer Homepage -->
                                <div id="customerNotice" class="alert alert-info" style="display:none;">
                                    <span class="material-symbols-rounded">info</span>
                                    <p>Use <code>{{customer_name}}</code> as placeholder for customer's name</p>
                                </div>

                                <!-- Dynamic Editor Container -->
                                <div id="editorContainer" class="editor-fields">
                                    <!-- Fields will be injected here by JS -->
                                </div>
                            </div>

                            <!-- RIGHT: Live Preview -->
                            <div class="preview-panel">
                                <div class="preview-header">
                                    <h3>Live Preview</h3>
                                </div>
                                <div class="preview-iframe-container">
                                    <iframe id="livePreviewIframe" frameborder="0"></iframe>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button id="btnDiscard" class="btn btn-secondary">
                            <span class="material-symbols-rounded">delete</span> Discard Changes
                        </button>
                        <button id="btnSaveDraft" class="btn btn-primary">
                            <span class="material-symbols-rounded">save</span> Save Draft
                        </button>
                        <button id="btnPublish" class="btn btn-success">
                            <span class="material-symbols-rounded">publish</span> Publish
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Toast Container -->
        <div id="toastContainer" class="toast-container"></div>

        <script src="/RADS-TOOLING/assets/JS/chat_admin.js"></script>
        <script src="/RADS-TOOLING/assets/JS/chat-notification.js"></script>
        <script src="/RADS-TOOLING/assets/JS/content_mgmt.js"></script>
</body>

</html>