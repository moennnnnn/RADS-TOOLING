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
        accountNavItem.addEventListener('click', function() {
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
        customerNavItem.addEventListener('click', function() {
            setTimeout(() => loadCustomers(), 100);
        });
    }

    const customerSearch = document.getElementById('customer-search');
    if (customerSearch) {
        let searchTimeout;
        customerSearch.addEventListener('input', function() {
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
        orderNavItem.addEventListener('click', function() {
            setTimeout(() => loadOrders(), 100);
        });
    }

    const orderSearch = document.getElementById('order-search');
    if (orderSearch) {
        let searchTimeout;
        orderSearch.addEventListener('input', function() {
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
            <td><span class="badge badge-${getPaymentBadgeClass(order.payment_status)}">${order.payment_status || 'Unknown'}</span></td>
            <td><span class="badge badge-${getStatusBadgeClass(order.status)}">${order.status || 'Unknown'}</span></td>
            <td>
                <button class="btn-action btn-view" title="View Details">
                    <span class="material-symbols-rounded">visibility</span>
                </button>
                ${(['Owner', 'Admin'].includes(currentUserRole)) ? `
                    <button class="btn-action btn-verify" title="Verify Order">
                        <span class="material-symbols-rounded">verified</span>
                    </button>
                ` : ''}
            </td>
        </tr>
    `).join('');
}

function getStatusBadgeClass(status) {
    switch (status?.toLowerCase()) {
        case 'pending': return 'pending';
        case 'processing': return 'processing';
        case 'completed': return 'completed';
        case 'cancelled': return 'cancelled';
        default: return 'pending';
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
        return new Date(dateString).toLocaleDateString();
    } catch (e) {
        return dateString;
    }
}

// ============================================================================
// DASHBOARD
// ============================================================================

function initializeDashboard() {
    initializeChart();
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
                        <td><span class="badge badge-${getStatusBadgeClass(order.status)}">${order.status}</span></td>
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

function initializeChart() {
    const ctx = document.getElementById('salesChart');
    if (!ctx || typeof Chart === 'undefined') return;
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Sales',
                data: [12000, 19000, 15000, 25000, 22000, 30000],
                borderColor: '#3db36b',
                backgroundColor: 'rgba(61,179,107,.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
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
}

function setupReports() {
    const generateReportBtn = document.getElementById('generateReportBtn');
    if (generateReportBtn) {
        generateReportBtn.addEventListener('click', () => {
            showNotification('Report generated successfully!', 'success');
        });
    }
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

console.log('Enhanced Admin Dashboard Script Loaded Successfully!');