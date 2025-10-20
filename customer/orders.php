<?php
// ==========================================
// ORDERS PAGE - Customer Order Management
// ==========================================
session_start();

// Authentication check
if (empty($_SESSION['user']) || ($_SESSION['user']['aud'] ?? '') !== 'customer') {
    header('Location: /RADS-TOOLING/customer/login.php');
    exit;
}

// CSRF Token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$CSRF = $_SESSION['csrf_token'];

// Get customer info
$customerName = $_SESSION['user']['full_name'] ?? 'Customer';
$customerId = $_SESSION['user']['id'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - RADS Tooling</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Material Icons -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200">

    <!-- Stylesheets -->
    <link rel="stylesheet" href="/RADS-TOOLING/assets/css/orders.css">
</head>

<body>

    <!-- ==========================================
         MAIN CONTENT
         ========================================== -->
    <div class="orders-layout">

        <!-- SIDEBAR -->
        <aside class="orders-sidebar">
            <div class="sidebar-header">
                <h3>
                    <span class="material-symbols-rounded">filter_list</span>
                    Filter Orders
                </h3>
            </div>

            <nav class="sidebar-menu">
                <button class="menu-item active" data-status="all" onclick="filterOrders('all')">
                    <span class="material-symbols-rounded">list_alt</span>
                    <span>All Orders</span>
                    <span class="badge" id="badge-all">0</span>
                </button>

                <button class="menu-item" data-status="pending" onclick="filterOrders('pending')">
                    <span class="material-symbols-rounded">schedule</span>
                    <span>To Pay</span>
                    <span class="badge badge-pending" id="badge-pending">0</span>
                </button>

                <button class="menu-item" data-status="processing" onclick="filterOrders('processing')">
                    <span class="material-symbols-rounded">autorenew</span>
                    <span>Processing</span>
                    <span class="badge badge-processing" id="badge-processing">0</span>
                </button>

                <button class="menu-item" data-status="completed" onclick="filterOrders('completed')">
                    <span class="material-symbols-rounded">check_circle</span>
                    <span>Completed</span>
                    <span class="badge badge-completed" id="badge-completed">0</span>
                </button>

                <button class="menu-item" data-status="cancelled" onclick="filterOrders('cancelled')">
                    <span class="material-symbols-rounded">cancel</span>
                    <span>Cancelled</span>
                    <span class="badge badge-cancelled" id="badge-cancelled">0</span>
                </button>

                <div class="sidebar-footer">
                    <a href="/RADS-TOOLING/customer/homepage.php" class="home-link">
                        <span class="material-symbols-rounded">home</span>
                        Home
                    </a>
                </div>
            </nav>
        </aside>

        <!-- MAIN CONTENT AREA -->
        <main class="orders-main">
            <!-- Header -->
            <div class="orders-header">
                <div>
                    <h1>My Orders</h1>
                    <p class="subtitle">View and manage your orders</p>
                </div>

                <!-- Search Box -->
                <div class="search-box">
                    <span class="material-symbols-rounded">search</span>
                    <input type="text" id="searchInput" placeholder="Search orders by code or item..." onkeyup="searchOrders()">
                </div>
            </div>

            <!-- Orders Container -->
            <div id="ordersContainer" class="orders-container">
                <div class="loading-state">
                    <div class="spinner"></div>
                    <p>Loading your orders...</p>
                </div>
            </div>
        </main>
    </div>

    <!-- ==========================================
         ORDER DETAILS MODAL
         ========================================== -->
    <div id="orderDetailsModal" class="modal">
        <div class="modal-overlay" onclick="closeOrderDetailsModal()"></div>
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h2>
                    <span class="material-symbols-rounded">receipt_long</span>
                    Order Details
                </h2>
                <button class="modal-close" onclick="closeOrderDetailsModal()">
                    <span class="material-symbols-rounded">close</span>
                </button>
            </div>
            <div class="modal-body" id="orderDetailsContent">
                <!-- Populated by JS -->
            </div>
        </div>
    </div>

    <!-- ==========================================
         PAYMENT SUBMISSION MODAL
         ========================================== -->
    <div id="paymentModal" class="modal">
        <div class="modal-overlay" onclick="closePaymentModal()"></div>
        <div class="modal-content modal-medium">
            <div class="modal-header">
                <h2>
                    <span class="material-symbols-rounded">payments</span>
                    Submit Payment
                </h2>
                <button class="modal-close" onclick="closePaymentModal()">
                    <span class="material-symbols-rounded">close</span>
                </button>
            </div>
            <div class="modal-body" id="paymentModalContent">
                <!-- Populated by JS -->
            </div>
        </div>
    </div>

    <!-- ==========================================
         SPLIT PAYMENT MODAL
         ========================================== -->
    <div id="splitPaymentModal" class="modal">
        <div class="modal-overlay" onclick="closeSplitPaymentModal()"></div>
        <div class="modal-content modal-medium">
            <div class="modal-header">
                <h2>
                    <span class="material-symbols-rounded">payments</span>
                    Pay Remaining Balance
                </h2>
                <button class="modal-close" onclick="closeSplitPaymentModal()">
                    <span class="material-symbols-rounded">close</span>
                </button>
            </div>
            <div class="modal-body" id="splitPaymentContent">
                <!-- Populated by JS -->
            </div>
        </div>
    </div>

    <!-- ==========================================
         JAVASCRIPT
         ========================================== -->
    <script src="/RADS-TOOLING/assets/JS/orders.js"></script>

    <script>
        // Profile dropdown toggle
        document.querySelector('.profile-btn')?.addEventListener('click', function(e) {
            e.stopPropagation();
            this.parentElement.classList.toggle('active');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.profile-dropdown')) {
                document.querySelectorAll('.profile-dropdown').forEach(d => d.classList.remove('active'));
            }
        });

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            loadCustomerOrders('all');
        });
    </script>

    <!-- ==========================================
         COMPLETE ORDER MODAL
         ========================================== -->
    <div id="completeOrderModal" class="modal">
        <div class="modal-overlay" onclick="closeCompleteOrderModal()"></div>
        <div class="modal-content modal-medium">
            <div class="modal-header">
                <h2>
                    <span class="material-symbols-rounded">check_circle</span>
                    Complete Order
                </h2>
                <button class="modal-close" onclick="closeCompleteOrderModal()">
                    <span class="material-symbols-rounded">close</span>
                </button>
            </div>
            <div class="modal-body" id="completeOrderContent">
                <!-- Populated by JS -->
            </div>
        </div>
    </div>

</body>

</html>