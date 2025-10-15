<?php
// customer/profile.php
require_once dirname(__DIR__) . '/includes/guard.php';

guard_require_customer();

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$customer_id = $_SESSION['user']['id'];
$csrf_token = $_SESSION['csrf_token'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account - RADS Tooling</title>
    <link rel="stylesheet" href="/RADS-TOOLING/assets/css/profile.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
    
    <!-- Main Container -->
    <div class="profile-wrapper">
        <div class="profile-layout">
            <!-- Sidebar -->
            <aside class="profile-sidebar">
                <div class="sidebar-profile">
                    <div class="sidebar-avatar" id="sidebar-avatar"></div>
                    <div class="sidebar-info">
                        <div class="sidebar-name" id="sidebar-name"></div>
                        <a href="#" class="sidebar-edit">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                            </svg>
                            Edit Profile
                        </a>
                    </div>
                </div>

                <nav class="sidebar-menu">
                    <div class="menu-section">
                        <div class="menu-title">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                            My Account
                        </div>
                        <a href="#profile" class="menu-item active" data-tab="profile">Profile</a>
                        <a href="#address" class="menu-item" data-tab="address">Address</a>
                        <a href="#password" class="menu-item" data-tab="password">Change Password</a>
                    </div>

                    <div class="menu-section">
                        <div class="menu-title">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                                <line x1="3" y1="6" x2="21" y2="6"></line>
                            </svg>
                            My Orders
                        </div>
                        <a href="/RADS-TOOLING/customer/orders.php" class="menu-item">All Orders</a>
                        <a href="/RADS-TOOLING/customer/orders.php?status=pending" class="menu-item">Pending</a>
                        <a href="/RADS-TOOLING/customer/orders.php?status=processing" class="menu-item">Processing</a>
                        <a href="/RADS-TOOLING/customer/orders.php?status=completed" class="menu-item">Completed</a>
                    </div>
                </nav>
            </aside>

            <!-- Main Content Area -->
            <main class="profile-content">
                <!-- Loading State -->
                <div id="loading" class="loading-state" style="display: none;">
                    <div class="spinner"></div>
                    <p>Loading...</p>
                </div>

                <!-- Message Container -->
                <div id="message-container"></div>

                <!-- Profile Tab Content -->
                <div id="profile-tab" class="tab-content active">
                    <div class="content-header">
                        <h2>My Profile</h2>
                        <p>Manage and protect your account</p>
                    </div>

                    <div class="content-body">
                        <form id="profile-form" class="profile-form" onsubmit="updateProfile(event)">
                            <div class="form-split">
                                <div class="form-section">
                                    <div class="form-group">
                                        <label>Username</label>
                                        <input type="text" id="username" disabled>
                                    </div>

                                    <div class="form-group">
                                        <label>Full Name</label>
                                        <input type="text" id="full_name" name="full_name" required>
                                    </div>

                                    <div class="form-group">
                                        <label>Email</label>
                                        <input type="email" id="email" disabled>
                                    </div>

                                    <div class="form-group">
                                        <label>Phone Number</label>
                                        <input type="tel" id="phone" name="phone" placeholder="+63 912 345 6789">
                                    </div>

                                    <div class="form-actions">
                                        <button type="submit" class="btn btn-primary" id="save-btn">
                                            <span class="btn-text">Save Changes</span>
                                            <span class="btn-spinner" style="display: none;">
                                                <div class="mini-spinner"></div>
                                            </span>
                                        </button>
                                    </div>
                                </div>

                                <div class="form-divider"></div>

                                <div class="avatar-section">
                                    <div class="avatar-upload">
                                        <div class="avatar-preview" id="avatar-preview"></div>
                                        <button type="button" class="btn btn-outline" onclick="document.getElementById('profile_image_input').click()">
                                            Select Image
                                        </button>
                                        <input type="file" id="profile_image_input" accept="image/jpeg,image/png,image/jpg" style="display: none;" onchange="uploadProfileImage(event)">
                                        <p class="upload-hint">Maximum file size: 5MB<br>Format: JPG, PNG</p>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Address Tab Content -->
                <div id="address-tab" class="tab-content">
                    <div class="content-header">
                        <h2>My Address</h2>
                        <p>Manage your delivery address</p>
                    </div>

                    <div class="content-body">
                        <form id="address-form" class="address-form" onsubmit="updateAddress(event)">
                            <div class="form-group">
                                <label>Complete Address</label>
                                <textarea id="address" name="address" rows="4" placeholder="House No., Street, Barangay, City, Province"></textarea>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">Save Address</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Password Tab Content -->
                <div id="password-tab" class="tab-content">
                    <div class="content-header">
                        <h2>Change Password</h2>
                        <p>For your account security, please do not share your password with others</p>
                    </div>

                    <div class="content-body">
                        <div class="info-box">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="16" x2="12" y2="12"></line>
                                <line x1="12" y1="8" x2="12.01" y2="8"></line>
                            </svg>
                            <div>
                                <strong>Password reset required</strong>
                                <p>For security reasons, password changes must be done through the forgot password process.</p>
                            </div>
                        </div>
                        <a href="/RADS-TOOLING/customer/forgot-password.php" class="btn btn-primary">Request Password Reset</a>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        const API_BASE = '/RADS-TOOLING/backend/api';
        const CSRF_TOKEN = '<?php echo $csrf_token; ?>';

        let customerData = null;

        // Tab switching
        document.querySelectorAll('[data-tab]').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const tabName = link.dataset.tab;
                switchTab(tabName);
            });
        });

        function switchTab(tabName) {
            // Update active menu item
            document.querySelectorAll('.menu-item').forEach(item => {
                item.classList.remove('active');
            });
            document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');

            // Update active content
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            document.getElementById(`${tabName}-tab`).classList.add('active');
        }

        function showLoading() {
            document.getElementById('loading').style.display = 'flex';
        }

        function hideLoading() {
            document.getElementById('loading').style.display = 'none';
        }

        function showMessage(message, type = 'success') {
            const container = document.getElementById('message-container');
            const icon = type === 'success' ?
                '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>' :
                '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>';

            container.innerHTML = `
                <div class="alert alert-${type}">
                    ${icon}
                    ${message}
                </div>
            `;

            setTimeout(() => {
                container.innerHTML = '';
            }, 5000);

            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        async function loadProfile() {
            showLoading();

            try {
                const response = await fetch(`${API_BASE}/customer_profile.php`);

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                const result = await response.json();

                if (!result.success) {
                    if (result.redirect) {
                        window.location.href = result.redirect;
                        return;
                    }
                    throw new Error(result.message || 'Failed to load profile');
                }

                customerData = result.data.customer;
                renderProfile(customerData);
                hideLoading();

            } catch (error) {
                console.error('Load profile error:', error);
                showMessage(error.message || 'Failed to load profile data', 'error');
                hideLoading();
            }
        }

        function renderProfile(customer) {
            const profileImage = customer.profile_image ?
                `/RADS-TOOLING/${customer.profile_image}` :
                null;
            const initials = customer.full_name.substring(0, 2).toUpperCase();

            // Update navigation
            document.getElementById('nav-username').textContent = customer.full_name;

            // Update sidebar
            const sidebarAvatar = document.getElementById('sidebar-avatar');
            if (profileImage) {
                sidebarAvatar.innerHTML = `<img src="${profileImage}" alt="Profile">`;
            } else {
                sidebarAvatar.innerHTML = `<div class="avatar-placeholder">${initials}</div>`;
            }
            document.getElementById('sidebar-name').textContent = customer.full_name;

            // Update form fields
            document.getElementById('username').value = customer.username;
            document.getElementById('full_name').value = customer.full_name;
            document.getElementById('email').value = customer.email;
            document.getElementById('phone').value = customer.phone || '';
            document.getElementById('address').value = customer.address || '';

            // Update avatar preview
            const avatarPreview = document.getElementById('avatar-preview');
            if (profileImage) {
                avatarPreview.innerHTML = `<img src="${profileImage}" alt="Profile">`;
            } else {
                avatarPreview.innerHTML = `<div class="avatar-placeholder-large">${initials}</div>`;
            }
        }

        async function updateProfile(event) {
            event.preventDefault();

            const saveBtn = document.getElementById('save-btn');
            const btnText = saveBtn.querySelector('.btn-text');
            const btnSpinner = saveBtn.querySelector('.btn-spinner');

            saveBtn.disabled = true;
            btnText.style.display = 'none';
            btnSpinner.style.display = 'inline-block';

            const formData = {
                csrf_token: CSRF_TOKEN,
                full_name: document.getElementById('full_name').value.trim(),
                phone: document.getElementById('phone').value.trim(),
                address: document.getElementById('address').value.trim()
            };

            try {
                const response = await fetch(`${API_BASE}/customer_profile.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });

                const result = await response.json();

                if (!result.success) {
                    throw new Error(result.message || 'Failed to update profile');
                }

                customerData.full_name = result.data.full_name;
                renderProfile(customerData);
                showMessage(result.message, 'success');

            } catch (error) {
                console.error('Update profile error:', error);
                showMessage(error.message || 'Failed to update profile', 'error');
            } finally {
                saveBtn.disabled = false;
                btnText.style.display = 'inline';
                btnSpinner.style.display = 'none';
            }
        }

        async function updateAddress(event) {
            event.preventDefault();

            const formData = {
                csrf_token: CSRF_TOKEN,
                full_name: customerData.full_name,
                phone: customerData.phone || '',
                address: document.getElementById('address').value.trim()
            };

            try {
                const response = await fetch(`${API_BASE}/customer_profile.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });

                const result = await response.json();

                if (!result.success) {
                    throw new Error(result.message || 'Failed to update address');
                }

                showMessage('Address updated successfully', 'success');

            } catch (error) {
                console.error('Update address error:', error);
                showMessage(error.message || 'Failed to update address', 'error');
            }
        }

        async function uploadProfileImage(event) {
            const file = event.target.files[0];
            if (!file) return;

            const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
            if (!allowedTypes.includes(file.type)) {
                showMessage('Only JPG, JPEG, and PNG files are allowed', 'error');
                return;
            }

            if (file.size > 5 * 1024 * 1024) {
                showMessage('File size must be less than 5MB', 'error');
                return;
            }

            const formData = new FormData();
            formData.append('profile_image', file);
            formData.append('csrf_token', CSRF_TOKEN);

            try {
                const response = await fetch(`${API_BASE}/upload_profile_image.php`, {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (!result.success) {
                    throw new Error(result.message || 'Failed to upload image');
                }

                customerData.profile_image = result.data.profile_image;
                renderProfile(customerData);
                showMessage(result.message, 'success');

            } catch (error) {
                console.error('Upload image error:', error);
                showMessage(error.message || 'Failed to upload profile picture', 'error');
            }

            event.target.value = '';
        }

        document.addEventListener('DOMContentLoaded', loadProfile);
    </script>
</body>

</html>