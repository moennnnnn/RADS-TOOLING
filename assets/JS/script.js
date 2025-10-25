// Enhanced Admin Dashboard JavaScript with Complete Role-Based Access Control
console.log('Enhanced Admin Dashboard Script Loading...');

// Global state
let currentUserRole = null;
let currentUserData = null;

document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM Content Loaded');

    initializeSession()
        .then(() => {
            initializeNavigation();
            initializeProfileDropdown();
            initializeModals();
            initializeProfileActions();
            initializeActionBindings();
            initializeDashboard();
            initializeAccountManagement();
            initializeOrderManagement();
            initializeCustomerManagement();
            initializeFeedbackManagement(); // ADD THIS LINE
            setupFloatingPwToggle();
            setupLogout();
        })
        .catch(error => {
            console.error('Initialization failed:', error);
            window.location.href = '/RADS-TOOLING/public/index.php';
        });
});

// ============================================================================
// SESSION MANAGEMENT & ROLE-BASED ACCESS
// ============================================================================

async function initializeSession() {
    try {
        const response = await fetch('/RADS-TOOLING/backend/api/auth.php?action=check_session', {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' }
        });

        const result = await response.json();

        if (!result.success || !result.data.user) {
            throw new Error('No valid session');
        }

        currentUserData = result.data.user;
        currentUserRole = result.data.user.role;

        updateUserInterface();
        configureSidebarForRole();

        console.log('Session initialized for:', currentUserRole);

    } catch (error) {
        console.error('Session check failed:', error);
        throw error;
    }
}

function updateUserInterface() {
    if (!currentUserData) return;

    const adminNameEl = document.querySelector('.admin-name');
    if (adminNameEl) {
        adminNameEl.textContent = `Welcome, ${currentUserData.name}`;
    }

    const profileAvatar = document.getElementById('profileIcon');
    if (profileAvatar && currentUserData.avatar) {
        profileAvatar.src = '/RADS-TOOLING/' + currentUserData.avatar;
    }
}

function configureSidebarForRole() {
    const allNavItems = document.querySelectorAll('.nav-item');

    const rolePermissions = {
        'Owner': ['dashboard', 'account', 'customer', 'products', 'orders', 'reports', 'content', 'feedback', 'chat', 'payment'],
        'Admin': ['dashboard', 'account', 'customer', 'products', 'orders', 'reports', 'content', 'feedback', 'chat', 'payment'],
        'Secretary': ['dashboard', 'orders', 'reports', 'feedback', 'chat']
    };

    const allowedSections = rolePermissions[currentUserRole] || [];

    allNavItems.forEach(item => {
        const section = item.getAttribute('data-section');
        if (!allowedSections.includes(section)) {
            item.style.display = 'none';
        }
    });

    const currentActive = document.querySelector('.nav-item.active');
    if (currentActive && !allowedSections.includes(currentActive.getAttribute('data-section'))) {
        switchToSection('dashboard');
    }
}

function switchToSection(sectionName) {
    document.querySelectorAll('.main-section').forEach(s => {
        s.style.display = 'none';
        s.classList.remove('show');
    });

    const targetSection = document.querySelector(`.main-section[data-section="${sectionName}"]`);
    if (targetSection) {
        targetSection.style.display = 'block';
        targetSection.classList.add('show');
    }

    document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
    const targetNav = document.querySelector(`.nav-item[data-section="${sectionName}"]`);
    if (targetNav) {
        targetNav.classList.add('active');
    }
}

// ============================================================================
// PROFILE MANAGEMENT
// ============================================================================

function initializeProfileActions() {
    document.getElementById('btnEditProfile')?.addEventListener('click', async () => {
        await loadCurrentProfile();
        openModal('editProfileModal');
    });

    document.getElementById('btnChangePassword')?.addEventListener('click', () => {
        openModal('changePasswordModal');
    });

    const editProfileForm = document.getElementById('editProfileForm');
    if (editProfileForm) {
        editProfileForm.addEventListener('submit', handleEditProfile);
    }

    const changePasswordForm = document.getElementById('changePasswordForm');
    if (changePasswordForm) {
        changePasswordForm.addEventListener('submit', handleChangePassword);
    }

    const avatarInput = document.getElementById('editProfilePic');
    if (avatarInput) {
        avatarInput.addEventListener('change', handleAvatarUpload);
    }
}

async function loadCurrentProfile() {
    try {
        const response = await fetch('/RADS-TOOLING/backend/api/admin_profile.php?action=get_profile', {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' }
        });

        const result = await response.json();

        if (result.success && result.data) {
            const profile = result.data;
            document.getElementById('ep-fullname').value = profile.full_name || '';
            document.getElementById('ep-username').value = profile.username || '';

            const avatarImg = document.getElementById('editProfileAvatar');
            if (avatarImg) {
                avatarImg.src = profile.profile_image ?
                    '/RADS-TOOLING/' + profile.profile_image :
                    '/RADS-TOOLING/assets/images/profile.png';
            }
        }
    } catch (error) {
        console.error('Failed to load profile:', error);
        showNotification('Failed to load profile data', 'error');
    }
}

async function handleEditProfile(e) {
    e.preventDefault();

    const formData = new FormData(e.target);
    const data = {
        full_name: formData.get('full_name'),
        username: formData.get('username')
    };

    try {
        const response = await fetch('/RADS-TOOLING/backend/api/admin_profile.php?action=update_profile', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
            showNotification('Profile updated successfully!', 'success');
            closeModal('editProfileModal');

            currentUserData.name = data.full_name;
            currentUserData.username = data.username;

            updateUserInterface();

            showLoadingOverlay();
            setTimeout(async () => {
                try {
                    await initializeSession();
                    hideLoadingOverlay();
                } catch (error) {
                    hideLoadingOverlay();
                    console.error('Session refresh failed:', error);
                }
            }, 1000);
        } else {
            showNotification(result.message || 'Failed to update profile', 'error');
        }
    } catch (error) {
        console.error('Profile update error:', error);
        showNotification('Failed to update profile', 'error');
    }
}

async function handleChangePassword(e) {
    e.preventDefault();

    const currentPassword = document.getElementById('cp-old').value;
    const newPassword = document.getElementById('cp-new').value;
    const confirmPassword = document.getElementById('cp-confirm').value;

    if (newPassword !== confirmPassword) {
        showNotification('New passwords do not match', 'error');
        return;
    }

    const data = {
        current_password: currentPassword,
        new_password: newPassword,
        confirm_password: confirmPassword
    };

    try {
        const response = await fetch('/RADS-TOOLING/backend/api/admin_profile.php?action=change_password', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
            showNotification('Password changed successfully!', 'success');
            closeModal('changePasswordModal');
            e.target.reset();
        } else {
            showNotification(result.message || 'Failed to change password', 'error');
        }
    } catch (error) {
        console.error('Password change error:', error);
        showNotification('Failed to change password', 'error');
    }
}

async function handleAvatarUpload(e) {
    const file = e.target.files[0];
    if (!file) return;

    const formData = new FormData();
    formData.append('avatar', file);

    try {
        const response = await fetch('/RADS-TOOLING/backend/api/admin_profile.php?action=upload_avatar', {
            method: 'POST',
            credentials: 'same-origin',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            showNotification('Avatar updated successfully!', 'success');

            const avatarImg = document.getElementById('editProfileAvatar');
            const topbarAvatar = document.getElementById('profileIcon');

            if (avatarImg && result.data.avatar_url) {
                avatarImg.src = result.data.avatar_url;
            }
            if (topbarAvatar && result.data.avatar_url) {
                topbarAvatar.src = result.data.avatar_url;
            }
        } else {
            showNotification(result.message || 'Failed to upload avatar', 'error');
        }
    } catch (error) {
        console.error('Avatar upload error:', error);
        showNotification('Failed to upload avatar', 'error');
    }
}

function showLoadingOverlay() {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) {
        overlay.style.display = 'flex';
    }
}

function hideLoadingOverlay() {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) {
        overlay.style.display = 'none';
    }
}

// ============================================================================
// ACCOUNT MANAGEMENT
// ============================================================================

function initializeAccountManagement() {
    if (!['Owner', 'Admin'].includes(currentUserRole)) {
        return;
    }

    const accountNavItem = document.querySelector('[data-section="account"]');
    if (accountNavItem) {
        accountNavItem.addEventListener('click', function () {
            setTimeout(() => loadUsers(), 100);
        });
    }

    const addUserForm = document.getElementById('addUserForm');
    if (addUserForm) {
        addUserForm.addEventListener('submit', handleAddUser);
    }

    const currentSection = document.querySelector('.nav-item.active');
    if (currentSection && currentSection.dataset.section === 'account') {
        loadUsers();
    }
}

async function loadUsers() {
    try {
        const response = await fetch('/RADS-TOOLING/backend/api/admin_accounts.php?action=list', {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' }
        });

        const result = await response.json();

        if (result.success) {
            displayUsers(result.data.users);
            updateAddUserButton(result.data.permissions);
        } else {
            showNotification(result.message || 'Failed to load users', 'error');
        }
    } catch (error) {
        console.error('Failed to load users:', error);
        showNotification('Failed to load users', 'error');
    }
}

function displayUsers(users) {
    const tbody = document.getElementById('userTableBody');
    if (!tbody) return;

    if (!users || users.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:#666;">No users found</td></tr>';
        return;
    }

    tbody.innerHTML = users.map(user => `
        <tr data-user-id="${user.id}">
            <td>${user.id}</td>
            <td>${escapeHtml(user.username)}</td>
            <td>${escapeHtml(user.full_name)}</td>
            <td><span class="badge badge-${user.role.toLowerCase()}">${user.role}</span></td>
            <td>
                ${(currentUserRole === 'Owner') ? `
                    <button class="btn-action btn-reset-password" data-user-id="${user.id}" title="Reset Password">
                        <span class="material-symbols-rounded">key</span>
                    </button>
                ` : ''}
                ${user.can_delete ? `
                    <button class="btn-action btn-delete-user" data-user-id="${user.id}" title="Delete">
                        <span class="material-symbols-rounded">delete</span>
                    </button>
                ` : ''}
                ${!user.can_delete && currentUserRole !== 'Owner' ? '<span style="color: #999;">No actions</span>' : ''}
            </td>
        </tr>
    `).join('');
}

function updateAddUserButton(permissions) {
    const addButton = document.querySelector('.btn-add-user');
    if (!addButton) return;

    if (!permissions.can_create_owner && !permissions.can_create_admin && !permissions.can_create_secretary) {
        addButton.style.display = 'none';
    } else {
        addButton.style.display = 'flex';

        const roleSelect = document.getElementById('au-role');
        if (roleSelect) {
            roleSelect.innerHTML = '<option value="">Select Role</option>';

            if (permissions.can_create_owner) {
                roleSelect.innerHTML += '<option value="Owner">Owner</option>';
            }
            if (permissions.can_create_admin) {
                roleSelect.innerHTML += '<option value="Admin">Admin</option>';
            }
            if (permissions.can_create_secretary) {
                roleSelect.innerHTML += '<option value="Secretary">Secretary</option>';
            }
        }
    }
}

async function handleAddUser(e) {
    e.preventDefault();

    const formData = new FormData(e.target);
    const data = {
        username: formData.get('username'),
        full_name: formData.get('full_name'),
        role: formData.get('role'),
        password: formData.get('password')
    };

    try {
        const response = await fetch('/RADS-TOOLING/backend/api/admin_accounts.php?action=create', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
            showNotification('User created successfully!', 'success');
            closeModal('addUserModal');
            e.target.reset();
            loadUsers();
        } else {
            showNotification(result.message || 'Failed to create user', 'error');
        }
    } catch (error) {
        console.error('Create user error:', error);
        showNotification('Failed to create user', 'error');
    }
}

async function resetUserPassword(userId) {
    try {
        const response = await fetch('/RADS-TOOLING/backend/api/admin_accounts.php?action=list', {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' }
        });

        const result = await response.json();

        if (result.success) {
            const user = result.data.users.find(u => u.id === userId);
            if (user) {
                createResetPasswordModal(user);
                openModal('resetPasswordModal');
            }
        }
    } catch (error) {
        console.error('Error loading user for password reset:', error);
        showNotification('Failed to load user data', 'error');
    }
}

function createResetPasswordModal(user) {
    const existingModal = document.getElementById('resetPasswordModal');
    if (existingModal) {
        existingModal.remove();
    }

    const modalHtml = `
        <div class="modal" id="resetPasswordModal">
            <div class="modal-content">
                <button class="modal-close" aria-label="Close">×</button>
                <h2>Reset Password for ${escapeHtml(user.full_name)}</h2>
                <form id="resetPasswordForm">
                    <input type="hidden" id="rp-id" value="${user.id}">
                    <input id="rp-new-password" type="password" placeholder="New Password" required />
                    <input id="rp-confirm-password" type="password" placeholder="Confirm New Password" required />
                    <div style="display:flex;gap:.5rem;justify-content:flex-end">
                        <button type="submit" class="primary">Reset Password</button>
                    </div>
                </form>
            </div>
        </div>
    `;

    document.body.insertAdjacentHTML('beforeend', modalHtml);
    document.getElementById('resetPasswordForm').addEventListener('submit', handleResetPassword);
}

async function handleResetPassword(e) {
    e.preventDefault();

    const newPassword = document.getElementById('rp-new-password').value;
    const confirmPassword = document.getElementById('rp-confirm-password').value;

    if (newPassword !== confirmPassword) {
        showNotification('Passwords do not match', 'error');
        return;
    }

    if (newPassword.length < 6) {
        showNotification('Password must be at least 6 characters long', 'error');
        return;
    }

    const data = {
        id: parseInt(document.getElementById('rp-id').value),
        new_password: newPassword
    };

    try {
        const response = await fetch('/RADS-TOOLING/backend/api/admin_accounts.php?action=reset_password', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
            showNotification('Password reset successfully!', 'success');
            closeModal('resetPasswordModal');
            document.getElementById('resetPasswordModal').remove();
        } else {
            showNotification(result.message || 'Failed to reset password', 'error');
        }
    } catch (error) {
        console.error('Reset password error:', error);
        showNotification('Failed to reset password', 'error');
    }
}

// ============================================================================
// CUSTOMER MANAGEMENT (View and Delete only - Edit removed)
// ============================================================================

function initializeCustomerManagement() {
    const customerNavItem = document.querySelector('[data-section="customer"]');
    if (customerNavItem) {
        customerNavItem.addEventListener('click', function () {
            setTimeout(() => loadCustomers(), 100);
        });
    }

    const customerSearch = document.getElementById('customer-search');
    if (customerSearch) {
        let searchTimeout;
        customerSearch.addEventListener('input', function () {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                loadCustomers(this.value.trim());
            }, 300);
        });
    }

    const currentSection = document.querySelector('.nav-item.active');
    if (currentSection && currentSection.dataset.section === 'customer') {
        loadCustomers();
    }
}

async function loadCustomers(search = '') {
    try {
        const url = `/RADS-TOOLING/backend/api/admin_customers.php?action=list${search ? `&search=${encodeURIComponent(search)}` : ''}`;

        const response = await fetch(url, {
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });

        const responseText = await response.text();
        let result;
        try {
            result = JSON.parse(responseText);
        } catch (parseError) {
            console.error('JSON Parse Error:', parseError);
            throw new Error('Server returned invalid JSON response');
        }

        if (!result.success) {
            throw new Error(result.message || 'Failed to load customers');
        }

        displayCustomers(result.data.customers);
        updateDashboardStats();
    } catch (error) {
        console.error('Error loading customers:', error);
        showNotification('Failed to load customers: ' + error.message, 'error');
    }
}

function displayCustomers(customers) {
    const tbody = document.getElementById('customerTableBody');
    if (!tbody) return;

    if (!customers || customers.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:#666;">No customers found</td></tr>';
        return;
    }

    tbody.innerHTML = customers.map(customer => `
        <tr data-customer-id="${customer.id}">
            <td>${customer.id}</td>
            <td>${escapeHtml(customer.username || 'N/A')}</td>
            <td>${escapeHtml(customer.full_name || 'N/A')}</td>
            <td>${escapeHtml(customer.email || 'N/A')}</td>
            <td>${escapeHtml(customer.phone || 'N/A')}</td>
            <td>
                <button class="btn-action btn-view-customer" data-customer-id="${customer.id}" title="View Details">
                    <span class="material-symbols-rounded">visibility</span>
                </button>
                ${(['Owner', 'Admin'].includes(currentUserRole)) ? `
                    <button class="btn-action btn-delete-customer" data-customer-id="${customer.id}" title="Delete">
                        <span class="material-symbols-rounded">delete</span>
                    </button>
                ` : ''}
            </td>
        </tr>
    `).join('');
}

async function viewCustomer(id) {
    try {
        const response = await fetch(`/RADS-TOOLING/backend/api/admin_customers.php?action=view&id=${id}`, {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' }
        });

        const responseText = await response.text();
        let result;
        try {
            result = JSON.parse(responseText);
        } catch (parseError) {
            throw new Error('Server returned invalid response');
        }

        if (!result.success) {
            throw new Error(result.message || 'Failed to load customer details');
        }

        const customer = result.data;

        const existingModal = document.getElementById('viewCustomerModal');
        if (existingModal) {
            existingModal.remove();
        }

        const modalHtml = `
            <div class="modal" id="viewCustomerModal" style="display: flex;">
                <div class="modal-content">
                    <button class="modal-close" onclick="closeViewCustomerModal()">×</button>
                    <h2>Customer Details</h2>
                    <div class="customer-details" style="display: grid; gap: 0.5rem;">
                        <div class="detail-row"><strong>ID:</strong> ${customer.id}</div>
                        <div class="detail-row"><strong>Username:</strong> ${escapeHtml(customer.username)}</div>
                        <div class="detail-row"><strong>Full Name:</strong> ${escapeHtml(customer.full_name)}</div>
                        <div class="detail-row"><strong>Email:</strong> ${escapeHtml(customer.email)}</div>
                        <div class="detail-row"><strong>Phone:</strong> ${escapeHtml(customer.phone || 'N/A')}</div>
                        <div class="detail-row"><strong>Address:</strong> ${escapeHtml(customer.address || 'N/A')}</div>
                        <div class="detail-row"><strong>Email Verified:</strong> ${customer.email_verified ? 'Yes' : 'No'}</div>
                        <div class="detail-row"><strong>Member Since:</strong> ${formatDate(customer.created_at)}</div>
                        <div class="detail-row"><strong>Total Orders:</strong> ${customer.order_count || 0}</div>
                        <div class="detail-row"><strong>Total Spent:</strong> ₱${(customer.total_spent || 0).toLocaleString()}</div>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHtml);

    } catch (error) {
        console.error('Error viewing customer:', error);
        showNotification('Failed to load customer details: ' + error.message, 'error');
    }
}

function closeViewCustomerModal() {
    const modal = document.getElementById('viewCustomerModal');
    if (modal) {
        modal.style.display = 'none';
        modal.remove();
    }
}

// ============================================================================
// ORDER MANAGEMENT
// ============================================================================

function initializeOrderManagement() {
    const orderNavItem = document.querySelector('[data-section="orders"]');
    if (orderNavItem) {
        orderNavItem.addEventListener('click', function () {
            setTimeout(() => loadOrders(), 100);
        });
    }

    const orderSearch = document.getElementById('order-search');
    if (orderSearch) {
        let searchTimeout;
        orderSearch.addEventListener('input', function () {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                loadOrders(this.value.trim());
            }, 300);
        });
    }

    const statusFilter = document.getElementById('statusFilter');
    const paymentFilter = document.getElementById('paymentFilter');

    [statusFilter, paymentFilter].forEach(filter => {
        if (filter) {
            filter.addEventListener('change', () => loadOrders());
        }
    });

    const currentSection = document.querySelector('.nav-item.active');
    if (currentSection && currentSection.dataset.section === 'orders') {
        loadOrders();
    }
}

async function loadOrders(search = '') {
    try {
        const statusFilter = document.getElementById('statusFilter')?.value || '';
        const paymentFilter = document.getElementById('paymentFilter')?.value || '';

        const params = new URLSearchParams();
        if (search) params.append('search', search);
        if (statusFilter) params.append('status', statusFilter);
        if (paymentFilter) params.append('payment_status', paymentFilter);

        const url = `/RADS-TOOLING/backend/api/admin_orders.php?action=list&${params.toString()}`;

        const response = await fetch(url, {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' }
        });

        const result = await response.json();

        if (result.success) {
            displayOrders(result.data.orders);
        } else {
            showNotification(result.message || 'Failed to load orders', 'error');
        }
    } catch (error) {
        console.error('Error loading orders:', error);
        showNotification('Failed to load orders: ' + error.message, 'error');
    }
}
async function viewAdminOrderDetails(orderId) {
    try {
        const response = await fetch(`/RADS-TOOLING/backend/api/admin_orders.php?action=details&id=${orderId}`, {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' }
        });

        const result = await response.json();

        if (!result.success) {
            throw new Error(result.message || 'Failed to load order details');
        }

        const { order, items } = result.data;

        // Create modal content
        const existingModal = document.getElementById('adminOrderDetailsModal');
        if (existingModal) {
            existingModal.remove();
        }

        const modalHtml = `
            <div class="modal" id="adminOrderDetailsModal" style="display: flex;">
                <div class="modal-content" style="max-width: 900px;">
                    <button class="modal-close" onclick="closeAdminOrderModal()">×</button>
                    <h2>Order Details - ${escapeHtml(order.order_code)}</h2>
                    
                    <div style="display: grid; gap: 1.5rem;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div>
                                <h3 style="color: var(--brand); margin-bottom: 0.5rem;">Customer Information</h3>
                                <p><strong>Name:</strong> ${escapeHtml(order.customer_name)}</p>
                                <p><strong>Email:</strong> ${escapeHtml(order.customer_email)}</p>
                                <p><strong>Phone:</strong> ${escapeHtml(order.customer_phone || 'N/A')}</p>
                            </div>
                            
                            <div>
                                <h3 style="color: var(--brand); margin-bottom: 0.5rem;">Order Information</h3>
                                <p><strong>Order Date:</strong> ${formatDate(order.order_date)}</p>
                                <p><strong>Delivery Mode:</strong> ${escapeHtml(order.mode)}</p>
                                <p><strong>Status:</strong> <span class="badge badge-${getStatusBadgeClass(order.status)}">${escapeHtml(order.status)}</span></p>
                                <p><strong>Payment Status:</strong> <span class="badge badge-${getPaymentBadgeClass(order.payment_status)}">${escapeHtml(order.payment_status)}</span></p>
                            </div>
                        </div>

                        <div>
                            <h3 style="color: var(--brand); margin-bottom: 0.5rem;">Order Items</h3>
                            <table style="width: 100%; border-collapse: collapse;">
                                <thead>
                                    <tr style="border-bottom: 2px solid #e3e3e3;">
                                        <th style="text-align: left; padding: 0.5rem;">Item</th>
                                        <th style="text-align: center; padding: 0.5rem;">Qty</th>
                                        <th style="text-align: right; padding: 0.5rem;">Price</th>
                                        <th style="text-align: right; padding: 0.5rem;">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${items.map(item => `
                                        <tr style="border-bottom: 1px solid #e3e3e3;">
                                            <td style="padding: 0.5rem;">${escapeHtml(item.name)}</td>
                                            <td style="text-align: center; padding: 0.5rem;">${item.qty}</td>
                                            <td style="text-align: right; padding: 0.5rem;">₱${parseFloat(item.unit_price).toLocaleString()}</td>
                                            <td style="text-align: right; padding: 0.5rem;">₱${parseFloat(item.line_total).toLocaleString()}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                                <tfoot>
                                    <tr style="border-top: 2px solid var(--brand);">
                                        <td colspan="3" style="text-align: right; padding: 0.5rem; font-weight: 700;">Total:</td>
                                        <td style="text-align: right; padding: 0.5rem; font-weight: 700; font-size: 1.2rem; color: var(--brand);">₱${parseFloat(order.total_amount).toLocaleString()}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        ${order.payment_method ? `
                            <div>
                                <h3 style="color: var(--brand); margin-bottom: 0.5rem;">Payment Information</h3>
                                <p><strong>Method:</strong> ${escapeHtml(order.payment_method)}</p>
                                <p><strong>Deposit Rate:</strong> ${order.deposit_rate}%</p>
                                <p><strong>Amount Paid:</strong> ₱${parseFloat(order.amount_paid || 0).toLocaleString()}</p>
                                <p><strong>Verification Status:</strong> <span class="badge badge-${order.payment_verification_status === 'APPROVED' ? 'completed' : 'pending'}">${escapeHtml(order.payment_verification_status || 'PENDING')}</span></p>
                            </div>
                        ` : ''}
                    </div>

                    <div style="margin-top: 1.5rem; display: flex; gap: 1rem; justify-content: flex-end;">
                        <button onclick="closeAdminOrderModal()" class="btn-secondary">Close</button>
                        ${(['Owner', 'Admin'].includes(currentUserRole)) ? `
                            <button onclick="updateOrderStatus(${order.id}, '${escapeHtml(order.status)}')" class="btn-primary">Update Status</button>
                        ` : ''}
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHtml);

    } catch (error) {
        console.error('Error loading order details:', error);
        showNotification('Failed to load order details: ' + error.message, 'error');
    }
}

function closeAdminOrderModal() {
    const modal = document.getElementById('adminOrderDetailsModal');
    if (modal) {
        modal.style.display = 'none';
        modal.remove();
    }
}

function updateOrderStatus(orderId, currentStatus) {
    // Close detail modal if open
    closeAdminOrderModal();

    // Create status update modal
    const existingModal = document.getElementById('updateStatusModal');
    if (existingModal) {
        existingModal.remove();
    }

    const modalHtml = `
        <div class="modal" id="updateStatusModal" style="display: flex;">
            <div class="modal-content" style="max-width: 500px;">
                <button class="modal-close" onclick="closeUpdateStatusModal()">×</button>
                <h2>Update Order Status</h2>
                
                <form id="updateStatusForm">
                    <input type="hidden" id="update-order-id" value="${orderId}">
                    
                    <div style="margin: 1.5rem 0;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Current Status:</label>
                        <p style="margin: 0; padding: 0.5rem; background: #f7fafc; border-radius: 6px;">
                            <span class="badge badge-${getStatusBadgeClass(currentStatus)}">${escapeHtml(currentStatus)}</span>
                        </p>
                    </div>

                    <div style="margin: 1.5rem 0;">
                        <label for="new-status" style="display: block; margin-bottom: 0.5rem; font-weight: 600;">New Status:</label>
                        <select id="new-status" required style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 6px;">
                            <option value="">Select Status</option>
                            <option value="Pending" ${currentStatus === 'Pending' ? 'selected' : ''}>Pending</option>
                            <option value="Processing" ${currentStatus === 'Processing' ? 'selected' : ''}>Processing</option>
                            <option value="Completed" ${currentStatus === 'Completed' ? 'selected' : ''}>Completed</option>
                            <option value="Cancelled" ${currentStatus === 'Cancelled' ? 'selected' : ''}>Cancelled</option>
                        </select>
                    </div>

                    <div style="background: #e8f4f8; padding: 1rem; border-radius: 8px; margin: 1rem 0;">
                        <p style="margin: 0; font-size: 0.9rem; color: #2f5b88;">
                            <strong>Note:</strong> Changing the order status will notify the customer and update their order tracking.
                        </p>
                    </div>

                    <div style="display: flex; gap: 0.5rem; justify-content: flex-end; margin-top: 1.5rem;">
                        <button type="button" onclick="closeUpdateStatusModal()" class="btn-secondary">Cancel</button>
                        <button type="submit" class="btn-primary">Update Status</button>
                    </div>
                </form>
            </div>
        </div>
    `;

    document.body.insertAdjacentHTML('beforeend', modalHtml);

    // Add form submit handler
    document.getElementById('updateStatusForm').addEventListener('submit', handleStatusUpdate);
}

function closeUpdateStatusModal() {
    const modal = document.getElementById('updateStatusModal');
    if (modal) {
        modal.style.display = 'none';
        modal.remove();
    }
}

async function handleStatusUpdate(e) {
    e.preventDefault();

    const orderId = parseInt(document.getElementById('update-order-id').value);
    const newStatus = document.getElementById('new-status').value;

    if (!newStatus) {
        showNotification('Please select a status', 'error');
        return;
    }

    try {
        const response = await fetch('/RADS-TOOLING/backend/api/admin_orders.php?action=update_status', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                order_id: orderId,
                status: newStatus
            })
        });

        const result = await response.json();

        if (result.success) {
            showNotification('Order status updated successfully!', 'success');
            closeUpdateStatusModal();
            loadOrders(); // Reload orders table
        } else {
            showNotification(result.message || 'Failed to update status', 'error');
        }
    } catch (error) {
        console.error('Error updating order status:', error);
        showNotification('Failed to update order status', 'error');
    }
}

function displayOrders(orders) {
    const tbody = document.getElementById('orderTableBody');
    if (!tbody) return;

    if (!orders || orders.length === 0) {
        tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;color:#666;">No orders found</td></tr>';
        return;
    }

    tbody.innerHTML = orders.map(order => `
        <tr data-order-id="${order.id}">
            <td>${escapeHtml(order.order_code || order.id)}</td>
            <td>${escapeHtml(order.product_name || 'N/A')}</td>
            <td>${escapeHtml(order.customer_name || 'Unknown')}</td>
            <td>${formatDate(order.order_date)}</td>
            <td>₱${parseFloat(order.total_amount || 0).toLocaleString()}</td>
            <td><span class="badge badge-${getPaymentBadgeClass(order.payment_status)}">${order.payment_status || 'With Balance'}</span></td>
<td><span class="badge badge-${getStatusBadgeClass(order.status)}">${order.status || 'Pending'}</span></td>
<td>${formatDate(order.order_date)}</td>

            <td>
                <button class="btn-action btn-view" onclick="viewAdminOrderDetails(${order.id})" title="View Details">
                    <span class="material-symbols-rounded">visibility</span>
                </button>
                ${(['Owner', 'Admin'].includes(currentUserRole)) ? `
                    <button class="btn-action btn-edit" onclick="updateOrderStatus(${order.id}, '${escapeHtml(order.status)}')" title="Update Status">
                        <span class="material-symbols-rounded">edit</span>
                    </button>
                ` : ''}
            </td>
        </tr>
    `).join('');
}

function getStatusBadgeClass(status) {
  const s = (status || '').toLowerCase();
  switch (s) {
    case 'pending': return 'pending';
    case 'processing': return 'processing';
    case 'completed': return 'completed';
    case 'cancelled': return 'cancelled';
    default: return 'pending'; // unknown/null -> pending
  }
}


function getPaymentBadgeClass(paymentStatus) {
    switch (paymentStatus?.toLowerCase()) {
        case 'fully paid': return 'paid';
        case 'with balance': return 'partial';
        default: return 'partial';
    }
}

function formatDate(dateString) {
    if (!dateString) return 'N/A';
    try {
        const d = new Date(dateString);
        const date = d.toLocaleDateString(undefined, { year: 'numeric', month: '2-digit', day: '2-digit' });
        // 12-hr with AM/PM
        const time = d.toLocaleTimeString(undefined, { hour: 'numeric', minute: '2-digit', hour12: true });
        return `${date} ${time}`;
    } catch (e) {
        return dateString || 'N/A';
    }
}


// /assets/JS/checkout.js
document.addEventListener('DOMContentLoaded', function () {
    const proceedBtn = document.getElementById('proceedToPayBtn') || document.querySelector('.btn-proceed');

    if (!proceedBtn) return;

    proceedBtn.addEventListener('click', async function (e) {
        e.preventDefault();

        // Get selected deposit rate
        const selectedDeposit = document.querySelector('input[name="deposit"]:checked');
        if (!selectedDeposit) {
            alert('Please select a deposit option (30%, 50%, or 100%)');
            return;
        }

        const depositRate = parseInt(selectedDeposit.value);

        // Determine if delivery or pickup mode
        const isDelivery = window.location.pathname.includes('checkout_delivery');

        // Get form values
        const firstName = document.getElementById('firstName')?.value || '';
        const lastName = document.getElementById('lastName')?.value || '';
        const phone = document.getElementById('phone')?.value || '';
        const email = document.getElementById('email')?.value || '';

        // Validate required fields
        if (!firstName || !lastName || !phone || !email) {
            alert('Please fill in all required fields');
            return;
        }

        // Additional validation for delivery
        if (isDelivery) {
            const province = document.getElementById('province')?.value || '';
            const city = document.getElementById('city')?.value || '';
            const barangay = document.getElementById('barangay')?.value || '';
            const street = document.getElementById('street')?.value || '';

            if (!province || !city || !barangay || !street) {
                alert('Please fill in all delivery address fields');
                return;
            }
        }

        // Get product and pricing info
        const urlParams = new URLSearchParams(window.location.search);
        const pid = urlParams.get('pid') || '';
        const totalAmount = parseFloat(document.getElementById('totalAmount')?.textContent?.replace(/[₱,]/g, '') || '0');
        const subtotalAmount = parseFloat(document.getElementById('subtotalAmount')?.textContent?.replace(/[₱,]/g, '') || '0');
        const vatAmount = parseFloat(document.getElementById('vatAmount')?.textContent?.replace(/[₱,]/g, '') || '0');

        if (!pid || totalAmount <= 0) {
            alert('Invalid order information');
            return;
        }

        try {
            const orderData = {
                pid: pid,
                qty: 1,
                subtotal: subtotalAmount,
                vat: vatAmount,
                total: totalAmount,
                mode: isDelivery ? 'delivery' : 'pickup',
                deposit_rate: depositRate,
                info: {
                    first_name: firstName,
                    last_name: lastName,
                    phone: phone,
                    email: email,
                    province: document.getElementById('province')?.value || '',
                    city: document.getElementById('city')?.value || '',
                    barangay: document.getElementById('barangay')?.value || '',
                    street: document.getElementById('street')?.value || '',
                    postal: document.getElementById('postal')?.value || ''
                }
            };

            console.log('Sending order data:', orderData);

            const response = await fetch('/RADS-TOOLING/backend/api/order_create.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify(orderData)
            });

            const result = await response.json();
            console.log('Server response:', result);

            if (!result.success) {
                if (result.redirect) {
                    alert(result.message);
                    window.location.href = result.redirect;
                    return;
                }
                throw new Error(result.message || 'Failed to create order');
            }

            // Success - redirect to payment submission page
            window.location.href = '/RADS-TOOLING/customer/payment_submit.php?order_id=' + result.order_id + '&deposit_rate=' + depositRate;

        } catch (error) {
            console.error('Order creation error:', error);
            alert('Failed to create order: ' + error.message);
        }
    });
});
// ============================================================================
// DASHBOARD
// ============================================================================

function initializeDashboard() {
    initializeChart('week');
    setupReports();
    updateDashboardStats();
    loadRecentOrders();
    loadRecentFeedback();
    console.log('Dashboard initialized');
}

async function updateDashboardStats() {
    try {
        const response = await fetch('/RADS-TOOLING/backend/api/dashboard.php?action=stats', {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' }
        });

        const result = await response.json();

        if (result.success && result.data) {
            const ordersEl = document.getElementById('dash-orders');
            const salesEl = document.getElementById('dash-sales');
            const customersEl = document.getElementById('dash-customers');
            const feedbackEl = document.getElementById('dash-feedback');

            if (ordersEl) ordersEl.textContent = result.data.total_orders.toLocaleString();
            if (salesEl) salesEl.textContent = '₱' + result.data.total_sales.toLocaleString();
            if (customersEl) customersEl.textContent = result.data.total_customers.toLocaleString();
            if (feedbackEl) feedbackEl.textContent = result.data.total_feedback.toLocaleString();
        }
    } catch (error) {
        console.error('Error updating dashboard stats:', error);
        const ordersEl = document.getElementById('dash-orders');
        const salesEl = document.getElementById('dash-sales');
        const customersEl = document.getElementById('dash-customers');
        const feedbackEl = document.getElementById('dash-feedback');

        if (ordersEl && ordersEl.textContent === 'Loading...') ordersEl.textContent = '0';
        if (salesEl && salesEl.textContent === 'Loading...') salesEl.textContent = '₱0';
        if (customersEl && customersEl.textContent === 'Loading...') customersEl.textContent = '0';
        if (feedbackEl && feedbackEl.textContent === 'Loading...') feedbackEl.textContent = '0';
    }
}

async function loadRecentOrders() {
    try {
        const response = await fetch('/RADS-TOOLING/backend/api/dashboard.php?action=recent_orders', {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' }
        });

        const result = await response.json();

        if (result.success && result.data) {
            const tbody = document.getElementById('dashRecentOrders');
            if (tbody) {
                tbody.innerHTML = result.data.length > 0 ? result.data.map(order => `
                    <tr>
                        <td>${escapeHtml(order.order_code)}</td>
                        <td>${escapeHtml(order.customer_name)}</td>
                        <td>${formatDate(order.order_date)}</td>
                        <td><span class="badge badge-${getStatusBadgeClass(order.status)}">${order.status || 'Pending'}</span></td>
                        <td>₱${parseFloat(order.total_amount || 0).toLocaleString()}</td>
                    </tr>
                `).join('') : '<tr><td colspan="5" style="text-align:center;">No recent orders</td></tr>';
            }
        }
    } catch (error) {
        console.error('Error loading recent orders:', error);
        const tbody = document.getElementById('dashRecentOrders');
        if (tbody) {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;">Error loading orders</td></tr>';
        }
    }
}

async function loadRecentFeedback() {
    try {
        const response = await fetch('/RADS-TOOLING/backend/api/dashboard.php?action=recent_feedback', {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' }
        });

        const result = await response.json();

        if (result.success && result.data) {
            const ul = document.getElementById('dashFeedbackList');
            if (ul) {
                ul.innerHTML = result.data.length > 0 ? result.data.map(feedback => `
                    <li>
                        <span class="feedback-customer">${escapeHtml(feedback.customer_name)}</span>
                        <span class="feedback-rating">${'★'.repeat(feedback.rating || 0)}</span>
                        <span class="feedback-comment">${escapeHtml(feedback.comment || 'No comment')}</span>
                    </li>
                `).join('') : '<li>No recent feedback</li>';
            }
        }
    } catch (error) {
        console.error('Error loading recent feedback:', error);
        const ul = document.getElementById('dashFeedbackList');
        if (ul) {
            ul.innerHTML = '<li>Error loading feedback</li>';
        }
    }
}

let salesChart = null;

function initializeChart(period = 'week') {
    const ctx = document.getElementById('salesChart');
    if (!ctx || typeof Chart === 'undefined') return;

    // Add event listeners to period buttons
    document.querySelectorAll('.period-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.period-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            loadSalesChart(this.dataset.period);
        });
    });

    // Load initial chart
    loadSalesChart(period);
}

async function loadSalesChart(period) {
    try {
        const response = await fetch(`/RADS-TOOLING/backend/api/dashboard.php?action=sales_chart&period=${period}`, {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' }
        });

        const result = await response.json();

        if (!result.success) {
            throw new Error(result.message || 'Failed to load chart data');
        }

        const ctx = document.getElementById('salesChart');

        // Destroy existing chart if it exists
        if (salesChart) {
            salesChart.destroy();
        }

        // Create new chart
        salesChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: result.data.labels,
                datasets: [{
                    label: 'Sales',
                    data: result.data.values,
                    borderColor: '#3db36b',
                    backgroundColor: 'rgba(61,179,107,.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return 'Sales: ₱' + context.parsed.y.toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function (value) {
                                return '₱' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    } catch (error) {
        console.error('Error loading sales chart:', error);
        showNotification('Failed to load sales chart', 'error');
    }
}

function setupReports() {
    // Set default dates
    const fromInput = document.getElementById('report-from');
    const toInput = document.getElementById('report-to');

    if (fromInput && toInput) {
        // Set default to current month
        const today = new Date();
        const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);

        fromInput.valueAsDate = firstDay;
        toInput.valueAsDate = today;
    }

    const generateReportBtn = document.getElementById('generateReportBtn');
    if (generateReportBtn) {
        generateReportBtn.addEventListener('click', generateReport);
    }

    const exportPdfBtn = document.getElementById('exportPdfBtn');
    if (exportPdfBtn) {
        exportPdfBtn.addEventListener('click', exportReportPdf);
    }
}

async function generateReport() {
    const fromDate = document.getElementById('report-from').value;
    const toDate = document.getElementById('report-to').value;

    if (!fromDate || !toDate) {
        showNotification('Please select both From and To dates', 'error');
        return;
    }

    if (new Date(fromDate) > new Date(toDate)) {
        showNotification('From date must be before To date', 'error');
        return;
    }

    try {
        // Fetch report data
        const response = await fetch(`/RADS-TOOLING/backend/api/report_data.php?from=${fromDate}&to=${toDate}`, {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' }
        });

        const result = await response.json();

        if (!result.success) {
            throw new Error(result.message || 'Failed to generate report');
        }

        // Update summary cards
        updateReportSummary(result.data);
        showNotification('Report generated successfully!', 'success');

    } catch (error) {
        console.error('Error generating report:', error);
        showNotification('Failed to generate report: ' + error.message, 'error');
    }
}

function updateReportSummary(data) {
    // Update each summary card
    const updates = {
        'rg-total-sales': '₱' + (data.total_sales || 0).toLocaleString(),
        'rg-total-orders': (data.total_orders || 0).toLocaleString(),
        'rg-avg-order': '₱' + (data.avg_order || 0).toLocaleString(),
        'rg-fully-paid': (data.fully_paid || 0).toLocaleString(),
        'rg-cancelled': (data.cancelled || 0).toLocaleString(),
        'rg-pending': (data.pending || 0).toLocaleString(),
        'rg-new-customers': (data.new_customers || 0).toLocaleString(),
        'rg-feedbacks': (data.feedbacks || 0).toLocaleString(),
        'rg-most-item': data.most_ordered_item || '—'
    };

    Object.entries(updates).forEach(([id, value]) => {
        const element = document.getElementById(id);
        if (element) {
            element.textContent = value;
        }
    });
}

function exportReportPdf() {
    const fromDate = document.getElementById('report-from').value;
    const toDate = document.getElementById('report-to').value;

    if (!fromDate || !toDate) {
        showNotification('Please select both From and To dates', 'error');
        return;
    }

    if (new Date(fromDate) > new Date(toDate)) {
        showNotification('From date must be before To date', 'error');
        return;
    }

    // Show loading notification
    showNotification('Generating PDF report...', 'info');

    // Open PDF in new window (will trigger download)
    window.open(
        `/RADS-TOOLING/backend/api/generate_report.php?from=${fromDate}&to=${toDate}`,
        '_blank'
    );

    setTimeout(() => {
        showNotification('PDF report downloaded!', 'success');
    }, 1500);
}

// ============================================================================
// CORE FUNCTIONALITY
// ============================================================================

function initializeNavigation() {
    const sidebarNav = document.querySelector('.sidebar-nav');
    if (!sidebarNav) return;

    sidebarNav.addEventListener('click', (e) => {
        const navItem = e.target.closest('.nav-item');
        if (!navItem) return;

        e.preventDefault();

        const targetSection = navItem.getAttribute('data-section');
        if (!targetSection) return;

        if (navItem.style.display === 'none') return;

        switchToSection(targetSection);

        setTimeout(() => {
            if (targetSection === 'account') loadUsers();
            else if (targetSection === 'customer') loadCustomers();
            else if (targetSection === 'orders') loadOrders();
            else if (targetSection === 'payment') loadPaymentVerifications();
            else if (targetSection === 'feedback') loadFeedback(); // ✅ ADD THIS LINE
            else if (targetSection === 'dashboard') {
                updateDashboardStats();
                loadRecentOrders();
                loadRecentFeedback();
            }
        }, 100);
    });
}
function initializeProfileDropdown() {
    const profileBtn = document.getElementById('profileIcon');
    const profileDropdown = document.getElementById('profileDropdown');
    if (!profileBtn || !profileDropdown) return;

    profileBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        profileDropdown.classList.toggle('show');
    });

    document.addEventListener('click', (e) => {
        if (!profileBtn.contains(e.target) && !profileDropdown.contains(e.target)) {
            profileDropdown.classList.remove('show');
        }
    });

    profileDropdown.addEventListener('click', (e) => {
        if (e.target.tagName === 'BUTTON' || e.target.closest('button')) {
            profileDropdown.classList.remove('show');
        }
    });
}

function initializeModals() {
    window.openModal = function (id) {
        const m = document.getElementById(id);
        if (!m) return;
        m.classList.add('show');
        m.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    };

    window.closeModal = function (id) {
        const m = document.getElementById(id);
        if (!m) return;
        m.classList.remove('show');
        m.style.display = 'none';
        document.body.style.overflow = '';
    };

    document.addEventListener('click', (e) => {
        if (e.target.classList && e.target.classList.contains('modal')) {
            const id = e.target.id;
            if (id) closeModal(id);
        }
        if (e.target.closest('.modal-close') || e.target.closest('[data-modal-close]')) {
            const modal = e.target.closest('.modal');
            if (modal && modal.id) closeModal(modal.id);
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal.show').forEach(m => closeModal(m.id));
        }
    });
}

function initializeActionBindings() {
    // Delete user handler
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.btn-delete-user');
        if (!btn) return;
        e.preventDefault();

        const userId = btn.getAttribute('data-user-id');
        const row = btn.closest('tr');
        const userName = row?.querySelector('td:nth-child(3)')?.textContent?.trim() || 'this user';

        showConfirm({
            title: 'Delete User',
            message: `Are you sure you want to delete <b>${userName}</b>? This action cannot be undone.`,
            okText: 'Delete',
            onConfirm: async () => {
                try {
                    const response = await fetch('/RADS-TOOLING/backend/api/admin_accounts.php?action=delete', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({ id: parseInt(userId) })
                    });

                    const result = await response.json();

                    if (!result.success) {
                        throw new Error(result.message || 'Failed to delete user');
                    }

                    showNotification('User deleted successfully', 'success');
                    loadUsers();

                } catch (error) {
                    console.error('Error deleting user:', error);
                    showNotification('Failed to delete user: ' + error.message, 'error');
                }
            }
        });
    });

    // Reset password handler
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.btn-reset-password');
        if (!btn) return;
        e.preventDefault();

        const userId = btn.getAttribute('data-user-id');
        resetUserPassword(parseInt(userId));
    });

    // Customer action handlers
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.btn-view-customer');
        if (btn) {
            e.preventDefault();
            const customerId = btn.getAttribute('data-customer-id');
            viewCustomer(parseInt(customerId));
        }
    });

    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.btn-delete-customer');
        if (!btn) return;
        e.preventDefault();

        const customerId = btn.getAttribute('data-customer-id');
        const row = btn.closest('tr');
        const customerName = row?.querySelector('td:nth-child(3)')?.textContent?.trim() || 'this customer';

        showConfirm({
            title: 'Delete Customer',
            message: `Are you sure you want to delete <b>${customerName}</b>? This action cannot be undone.`,
            okText: 'Delete',
            onConfirm: async () => {
                try {
                    const response = await fetch('/RADS-TOOLING/backend/api/admin_customers.php?action=delete', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({ id: parseInt(customerId) })
                    });

                    const result = await response.json();

                    if (!result.success) {
                        throw new Error(result.message || 'Failed to delete customer');
                    }

                    showNotification('Customer deleted successfully', 'success');
                    loadCustomers();

                } catch (error) {
                    console.error('Error deleting customer:', error);
                    showNotification('Failed to delete customer: ' + error.message, 'error');
                }
            }
        });
    });

    // Order verify handler
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.btn-verify');
        if (!btn) return;
        e.preventDefault();

        const row = btn.closest('tr');
        const orderId = row?.querySelector('td:first-child')?.textContent?.trim() || 'this order';

        showConfirm({
            title: 'Verify Order',
            message: `Mark ${orderId} as verified?`,
            okText: 'Verify',
            onConfirm: () => {
                const statusBadge = row?.querySelector('td:nth-child(7) .badge');
                if (statusBadge) {
                    statusBadge.textContent = 'Processing';
                    statusBadge.className = 'badge badge-processing';
                }
                showNotification('Order verified.', 'success');
            }
        });
    });

    // View order handler
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.btn-view');
        if (!btn) return;
        e.preventDefault();

        const row = btn.closest('tr');
        if (!row) return;

        document.getElementById('vo-code').textContent = row.children[0]?.textContent?.trim() || '—';
        document.getElementById('vo-customer').textContent = row.children[2]?.textContent?.trim() || '—';
        document.getElementById('vo-date').textContent = row.children[3]?.textContent?.trim() || '—';
        document.getElementById('vo-total').textContent = row.children[4]?.textContent?.trim() || '₱0';
        document.getElementById('vo-status').textContent = row.children[6]?.textContent?.trim() || '—';
        document.getElementById('vo-payment').textContent = row.children[5]?.textContent?.trim() || '—';

        openModal('viewOrderModal');
    });
}

function setupLogout() {
    const logoutBtn = document.getElementById('logoutBtn');
    if (!logoutBtn) return;

    const newLogoutBtn = logoutBtn.cloneNode(true);
    logoutBtn.parentNode.replaceChild(newLogoutBtn, logoutBtn);

    newLogoutBtn.addEventListener('click', function (e) {
        e.preventDefault();
        showConfirm({
            title: 'Logout',
            message: 'Do you really want to log out?',
            okText: 'Logout',
            onConfirm: async () => {
                try {
                    sessionStorage.removeItem('rads_admin_session');
                    await fetch('/RADS-TOOLING/backend/api/auth.php?action=logout', {
                        method: 'POST',
                        credentials: 'same-origin'
                    });
                } catch (_) {
                    // ignore network errors during logout 
                }
                location.href = '/RADS-TOOLING/public/index.php';
            }
        });
    });
}

function setupFloatingPwToggle() {
    const eye = document.createElement('span');
    eye.className = 'material-symbols-rounded';
    eye.textContent = 'visibility';
    Object.assign(eye.style, {
        position: 'fixed',
        display: 'none',
        fontSize: '22px',
        color: '#6b7b93',
        cursor: 'pointer',
        zIndex: '2001',
        userSelect: 'none',
        padding: '2px',
        background: '#fff',
        borderRadius: '12px',
        lineHeight: '1'
    });
    eye.style.fontFamily = '"Material Symbols Rounded"';

    document.body.appendChild(eye);

    let activeInput = null;

    function placeEyeFor(input) {
        const r = input.getBoundingClientRect();
        const insetX = 36;
        const eyeH = 22;
        eye.style.left = `${r.right - insetX}px`;
        eye.style.top = `${r.top + (r.height - eyeH) / 2}px`;
    }

    function showEye(input) {
        activeInput = input;
        placeEyeFor(input);
        eye.style.display = 'block';
        eye.textContent = input.type === 'password' ? 'visibility' : 'visibility_off';
    }

    function hideEye() {
        eye.style.display = 'none';
        activeInput = null;
    }

    eye.addEventListener('click', () => {
        if (!activeInput) return;
        if (activeInput.type === 'password') {
            activeInput.type = 'text';
            eye.textContent = 'visibility_off';
        } else {
            activeInput.type = 'password';
            eye.textContent = 'visibility';
        }
        activeInput.focus();
        placeEyeFor(activeInput);
    });

    document.addEventListener('focusin', (e) => {
        const input = e.target.closest('input[type="password"], input[data-pw="1"]');
        if (!input) {
            hideEye();
            return;
        }
        input.setAttribute('data-pw', '1');
        showEye(input);
    });

    document.addEventListener('mousedown', (e) => {
        if (e.target === eye) return;
        if (activeInput && (e.target === activeInput || e.target.closest('input') === activeInput)) return;
        hideEye();
    });
}

// ============================================================================
// UTILITY FUNCTIONS
// ============================================================================

function showNotification(message, type = 'info') {
    const el = document.createElement('div');
    el.className = `notification notification-${type}`;
    el.textContent = message;

    let backgroundColor;
    switch (type) {
        case 'success': backgroundColor = '#3db36b'; break;
        case 'error': backgroundColor = '#e14d4d'; break;
        default: backgroundColor = '#2f5b88';
    }

    Object.assign(el.style, {
        position: 'fixed',
        right: '16px',
        bottom: '16px',
        background: backgroundColor,
        color: '#fff',
        padding: '10px 14px',
        borderRadius: '8px',
        boxShadow: '0 2px 12px rgba(0,0,0,.15)',
        zIndex: 2000
    });
    document.body.appendChild(el);
    setTimeout(() => el.remove(), 4000);
}

function showConfirm({ title = 'Confirm', message = 'Are you sure?', okText = 'OK', onConfirm } = {}) {
    const modal = document.getElementById('confirmModal');
    if (!modal) return console.warn('confirmModal not found in DOM.');

    modal.querySelector('#confirmTitle').textContent = title;
    const msgEl = modal.querySelector('#confirmMessage');
    msgEl.innerHTML = message;

    const okBtn = modal.querySelector('#confirmOkBtn');
    okBtn.textContent = okText;

    const newOkBtn = okBtn.cloneNode(true);
    okBtn.parentNode.replaceChild(newOkBtn, okBtn);
    newOkBtn.addEventListener('click', () => {
        if (typeof onConfirm === 'function') onConfirm();
        closeModal('confirmModal');
    });

    openModal('confirmModal');
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Check for unread messages and show notification dot
async function checkUnreadMessages() {
    try {
        const response = await fetch('/RADS-TOOLING/backend/api/chat.php?action=threads', {
            credentials: 'same-origin'
        });

        if (!response.ok) return;

        const result = await response.json();

        if (result.success && result.data) {
            const hasUnread = result.data.some(t => t.has_unanswered);

            const chatNav = document.querySelector('.nav-item[data-section="chat"]');
            if (chatNav) {
                let dot = chatNav.querySelector('.rt-notification-dot');

                if (hasUnread && !dot) {
                    dot = document.createElement('span');
                    dot.className = 'rt-notification-dot';
                    chatNav.appendChild(dot);
                } else if (!hasUnread && dot) {
                    dot.remove();
                }
            }
        }
    } catch (error) {
        console.error('Failed to check unread messages:', error);
    }
}

// Check every 10 seconds
if (document.querySelector('.nav-item[data-section="chat"]')) {
    setInterval(checkUnreadMessages, 10000);
    checkUnreadMessages(); // Initial check
}
// ============================================================================
// FEEDBACK MANAGEMENT
// ============================================================================

function initializeFeedbackManagement() {
    const feedbackNavItem = document.querySelector('[data-section="feedback"]');
    if (feedbackNavItem) {
        feedbackNavItem.addEventListener('click', function () {
            setTimeout(() => loadFeedback(), 100);
        });
    }

    const currentSection = document.querySelector('.nav-item.active');
    if (currentSection && currentSection.dataset.section === 'feedback') {
        loadFeedback();
    }
}

async function loadFeedback() {
    const tbody = document.getElementById('feedbackTableBody');
    if (!tbody) return;

    try {
        const resp = await fetch('/RADS-TOOLING/backend/api/feedback/admin_list.php', {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' }
        });

        // log status
        console.log('loadFeedback: HTTP', resp.status, resp.statusText);

        // always read raw text so we can see server error output
        const raw = await resp.text();
        console.log('loadFeedback: raw response:', raw.slice(0, 2000)); // show up to 2000 chars

        // try parse JSON if applicable
        let json = null;
        try {
            json = JSON.parse(raw);
        } catch (err) {
            console.warn('loadFeedback: JSON parse failed', err);
        }

        if (!resp.ok) {
            // resp is not OK (e.g. 500). show error with body preview.
            throw new Error(`Server returned HTTP ${resp.status}: ${raw.slice(0,200)}`);
        }

        if (!json || !json.success) {
            // either invalid JSON or success:false
            const msg = (json && json.message) ? json.message : 'Invalid or missing JSON response';
            throw new Error(msg + (raw ? ` — raw: ${raw.slice(0,200)}` : ''));
        }

        displayFeedback(json.data || []);

    } catch (error) {
        console.error('Error loading feedback:', error);
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:#999;">Error loading feedback</td></tr>';
        // optional: showNotification('Failed to load feedback: ' + error.message, 'error');
    }
}


function displayFeedback(feedbackList) {
    const tbody = document.getElementById('feedbackTableBody');
    if (!tbody) return;

    if (!feedbackList || feedbackList.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:#666;">No feedback found</td></tr>';
        return;
    }

    tbody.innerHTML = feedbackList.map(feedback => {
        const stars = '★'.repeat(feedback.rating) + '☆'.repeat(5 - feedback.rating);
        const statusBadge = feedback.is_released
            ? '<span class="badge badge-completed">Released</span>'
            : '<span class="badge badge-pending">Pending</span>';

        return `
            <tr data-feedback-id="${feedback.id}">
                <td>${feedback.id}</td>
                <td>${escapeHtml(feedback.customer_name)}</td>
                <td style="color: #fbbf24; font-size: 1.2rem;">${stars}</td>
                <td style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="${escapeHtml(feedback.comment || 'No comment')}">${escapeHtml(feedback.comment || 'No comment')}</td>
                <td>${escapeHtml(feedback.order_code)}</td>
                <td>${formatDate(feedback.created_at)}</td>
                <td>
                    ${feedback.is_released ? `
                        <button class="btn-action btn-delete" onclick="hideFeedback(${feedback.id})" title="Hide from Public">
                            <span class="material-symbols-rounded">visibility_off</span>
                        </button>
                    ` : `
                        <button class="btn-action btn-view" onclick="releaseFeedback(${feedback.id})" title="Release to Public">
                            <span class="material-symbols-rounded">publish</span>
                        </button>
                    `}
                </td>
            </tr>
        `;
    }).join('');
}

async function releaseFeedback(feedbackId) {
    try {
        const response = await fetch('/RADS-TOOLING/backend/api/feedback/release.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                action: 'release',
                feedback_id: feedbackId
            })
        });

        const result = await response.json();

        if (result.success) {
            showNotification('Feedback released to public testimonials!', 'success');
            loadFeedback();
        } else {
            showNotification(result.message || 'Failed to release feedback', 'error');
        }
    } catch (error) {
        console.error('Error releasing feedback:', error);
        showNotification('Failed to release feedback', 'error');
    }
}

async function hideFeedback(feedbackId) {
    showConfirm({
        title: 'Hide Feedback',
        message: 'Remove this feedback from public testimonials?',
        okText: 'Hide',
        onConfirm: async () => {
            try {
                const response = await fetch('/RADS-TOOLING/backend/api/feedback/release.php', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'hide',
                        feedback_id: feedbackId
                    })
                });

                const result = await response.json();

                if (result.success) {
                    showNotification('Feedback hidden from public', 'success');
                    loadFeedback();
                } else {
                    showNotification(result.message || 'Failed to hide feedback', 'error');
                }
            } catch (error) {
                console.error('Error hiding feedback:', error);
                showNotification('Failed to hide feedback', 'error');
            }
        }
    });
}
console.log('Enhanced Admin Dashboard Script Loaded Successfully!');

// ============================================================================
// PAYMENT VERIFICATION
// ============================================================================

function initializePaymentVerification() {
    const paymentNavItem = document.querySelector('[data-section="payment"]');
    if (paymentNavItem) {
        paymentNavItem.addEventListener('click', function () {
            setTimeout(() => loadPaymentVerifications(), 100);
        });
    }

    const currentSection = document.querySelector('.nav-item.active');
    if (currentSection && currentSection.dataset.section === 'payment') {
        loadPaymentVerifications();
    }
}

async function loadPaymentVerifications() {
    const tbody = document.getElementById('paymentsTableBody');

    // If element doesn't exist, don't try to load
    if (!tbody) {
        console.log('Payment table not found - skipping payment verification load');
        return;
    }

    try {
        const response = await fetch('/RADS-TOOLING/backend/api/payment_verification.php?action=list', {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' }
        });

        if (!response.ok) {
            throw new Error('Failed to load payment verifications');
        }

        const result = await response.json();

        if (!result.success) {
            throw new Error(result.message || 'Failed to load payments');
        }

        displayPaymentVerifications(result.data || []);

    } catch (error) {
        console.error('Error loading payment verifications:', error);
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:#999;">No payment verifications found</td></tr>';
    }
}

function displayPaymentVerifications(verifications) {
    const tbody = document.getElementById('paymentsTableBody');
    if (!tbody) return;

    if (!verifications || verifications.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;color:#666;">No payment verifications found</td></tr>';
        return;
    }

    tbody.innerHTML = verifications.map(payment => {
        const statusClass = payment.status === 'PENDING' ? 'pending' :
            payment.status === 'APPROVED' ? 'completed' : 'cancelled';
        const statusText = payment.status.charAt(0) + payment.status.slice(1).toLowerCase();

        return `
            <tr data-payment-id="${payment.id}">
                <td>${escapeHtml(payment.order_code)}</td>
                <td>${escapeHtml(payment.customer_name)}</td>
                <td>₱${parseFloat(payment.amount_reported || 0).toLocaleString()}</td>
                <td>${escapeHtml(payment.method.toUpperCase())}</td>
                <td><span class="badge badge-${statusClass}">${statusText}</span></td>
                <td>${formatDate(payment.created_at)}</td>
                <td>
                    <button class="btn-action btn-view" onclick="viewPaymentDetails(${payment.id})" title="View Details">
                        <span class="material-symbols-rounded">visibility</span>
                    </button>
                </td>
            </tr>
        `;
    }).join('');
}

async function viewPaymentDetails(verificationId) {
    try {
        const response = await fetch(`/RADS-TOOLING/backend/api/payment_verification.php?action=details&id=${verificationId}`, {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' }
        });

        const result = await response.json();

        if (!result.success) {
            throw new Error(result.message || 'Failed to load payment details');
        }

        const payment = result.data;

        const content = document.getElementById('paymentDetailsContent');
        content.innerHTML = `
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div>
                    <h3 style="margin-bottom: 0.5rem; color: var(--brand);">Order Information</h3>
                    <p><strong>Order Code:</strong> ${escapeHtml(payment.order_code)}</p>
                    <p><strong>Order Date:</strong> ${formatDate(payment.order_date)}</p>
                    <p><strong>Total Amount:</strong> ₱${parseFloat(payment.total_amount).toLocaleString()}</p>
                    <p><strong>Items:</strong> ${escapeHtml(payment.items || 'N/A')}</p>
                    <p><strong>Delivery Mode:</strong> ${escapeHtml(payment.delivery_mode)}</p>
                </div>

                <div>
                    <h3 style="margin-bottom: 0.5rem; color: var(--brand);">Customer Information</h3>
                    <p><strong>Name:</strong> ${escapeHtml(payment.customer_name)}</p>
                    <p><strong>Email:</strong> ${escapeHtml(payment.customer_email)}</p>
                    <p><strong>Phone:</strong> ${escapeHtml(payment.customer_phone || 'N/A')}</p>
                    ${payment.delivery_mode === 'delivery' ? `
                        <p><strong>Address:</strong> ${escapeHtml(payment.street || '')}, ${escapeHtml(payment.barangay || '')}, ${escapeHtml(payment.city || '')}, ${escapeHtml(payment.province || '')}</p>
                    ` : ''}
                </div>
            </div>

            <div style="background: #f7fafc; padding: 1rem; border-radius: 8px;">
                <h3 style="margin-bottom: 0.5rem; color: var(--brand);">Payment Details</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                    <p><strong>Payment Method:</strong> ${escapeHtml(payment.method.toUpperCase())}</p>
                    <p><strong>Account Name:</strong> ${escapeHtml(payment.account_name)}</p>
                    <p><strong>Account Number:</strong> ${escapeHtml(payment.account_number || 'N/A')}</p>
                    <p><strong>Reference Number:</strong> ${escapeHtml(payment.reference_number)}</p>
                    <p><strong>Amount Paid:</strong> ₱${parseFloat(payment.amount_reported).toLocaleString()}</p>
                    <p><strong>Deposit Rate:</strong> ${payment.deposit_rate}%</p>
                    <p><strong>Amount Due:</strong> ₱${parseFloat(payment.amount_due).toLocaleString()}</p>
                    <p><strong>Status:</strong> <span class="badge badge-${payment.status === 'PENDING' ? 'pending' : payment.status === 'APPROVED' ? 'completed' : 'cancelled'}">${payment.status}</span></p>
                </div>
            </div>

            ${payment.screenshot_path ? `
                <div>
                    <h3 style="margin-bottom: 0.5rem; color: var(--brand);">Payment Proof</h3>
                    <img src="/RADS-TOOLING/${escapeHtml(payment.screenshot_path)}" 
                         alt="Payment Screenshot" 
                         style="max-width: 100%; border-radius: 8px; border: 1px solid #e3edfb; cursor: pointer;"
                         onclick="window.open(this.src, '_blank')" />
                </div>
            ` : ''}
        `;

        // Store verification ID for approve/reject actions
        document.getElementById('btnApprovePayment').dataset.verificationId = verificationId;
        document.getElementById('btnRejectPayment').dataset.verificationId = verificationId;

        // Hide approve/reject buttons if already processed
        if (payment.status !== 'PENDING') {
            document.getElementById('btnApprovePayment').style.display = 'none';
            document.getElementById('btnRejectPayment').style.display = 'none';
        } else {
            document.getElementById('btnApprovePayment').style.display = 'inline-block';
            document.getElementById('btnRejectPayment').style.display = 'inline-block';
        }

        openModal('paymentDetailsModal');

    } catch (error) {
        console.error('Error viewing payment details:', error);
        showNotification('Failed to load payment details: ' + error.message, 'error');
    }
}

async function approvePayment(verificationId) {
    try {
        const response = await fetch('/RADS-TOOLING/backend/api/payment_verification.php?action=approve', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ id: verificationId })
        });

        const result = await response.json();

        if (result.success) {
            showNotification('Payment approved successfully!', 'success');
            closeModal('paymentDetailsModal');
            loadPaymentVerifications();
        } else {
            showNotification(result.message || 'Failed to approve payment', 'error');
        }
    } catch (error) {
        console.error('Error approving payment:', error);
        showNotification('Failed to approve payment', 'error');
    }
}

async function rejectPayment(verificationId, reason) {
    try {
        const response = await fetch('/RADS-TOOLING/backend/api/payment_verification.php?action=reject', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ id: verificationId, reason: reason })
        });

        const result = await response.json();

        if (result.success) {
            showNotification('Payment rejected', 'success');
            closeModal('rejectReasonModal');
            closeModal('paymentDetailsModal');
            loadPaymentVerifications();
        } else {
            showNotification(result.message || 'Failed to reject payment', 'error');
        }
    } catch (error) {
        console.error('Error rejecting payment:', error);
        showNotification('Failed to reject payment', 'error');
    }
}

// Event listeners for payment actions
document.getElementById('btnApprovePayment')?.addEventListener('click', function () {
    const verificationId = this.dataset.verificationId;
    if (!verificationId) return;

    showConfirm({
        title: 'Approve Payment',
        message: 'Are you sure you want to approve this payment? The order will be moved to Processing status.',
        okText: 'Approve',
        onConfirm: () => approvePayment(parseInt(verificationId))
    });
});

document.getElementById('btnRejectPayment')?.addEventListener('click', function () {
    const verificationId = this.dataset.verificationId;
    if (!verificationId) return;

    document.getElementById('btnConfirmReject').dataset.verificationId = verificationId;
    openModal('rejectReasonModal');
});

document.getElementById('btnConfirmReject')?.addEventListener('click', function () {
    const verificationId = this.dataset.verificationId;
    const reason = document.getElementById('rejectReason').value.trim();

    if (!reason) {
        showNotification('Please provide a reason for rejection', 'error');
        return;
    }

    rejectPayment(parseInt(verificationId), reason);
});