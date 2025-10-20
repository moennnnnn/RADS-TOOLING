// ==========================================
// GLOBAL VARIABLES
// ==========================================
let currentFilter = 'all';
let allOrders = [];
let filteredOrders = [];
let orderCounts = {
    all: 0,
    pending: 0,
    processing: 0,
    completed: 0,
    cancelled: 0
};

// ==========================================
// LOAD ORDERS
// ==========================================
async function loadCustomerOrders(status = 'all') {
    currentFilter = status;
    const container = document.getElementById('ordersContainer');

    // Show loading
    container.innerHTML = `
        <div class="loading-state">
            <div class="spinner"></div>
            <p>Loading your orders...</p>
        </div>
    `;

    try {
        // Load ALL orders first to get counts
        const url = `/RADS-TOOLING/backend/api/customer_orders.php?action=list&status=all`;
        const response = await fetch(url, {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' }
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        const result = await response.json();

        if (!result.success) {
            throw new Error(result.message || 'Failed to load orders');
        }

        allOrders = result.data || [];

        // Calculate counts
        orderCounts.all = allOrders.length;
        orderCounts.pending = allOrders.filter(o => o.status.toLowerCase() === 'pending').length;
        orderCounts.processing = allOrders.filter(o => o.status.toLowerCase() === 'processing').length;
        orderCounts.completed = allOrders.filter(o => o.status.toLowerCase() === 'completed').length;
        orderCounts.cancelled = allOrders.filter(o => o.status.toLowerCase() === 'cancelled').length;

        // Filter orders based on current status
        if (status === 'all') {
            filteredOrders = allOrders;
        } else {
            filteredOrders = allOrders.filter(o => o.status.toLowerCase() === status);
        }

        displayOrders(filteredOrders);
        updateBadgeCounts(); // Update badges with correct counts

    } catch (error) {
        console.error('Load orders error:', error);
        container.innerHTML = `
            <div class="empty-state">
                <span class="material-symbols-rounded" style="color: #dc3545;">error</span>
                <h3>Failed to load orders</h3>
                <p>${escapeHtml(error.message)}</p>
                <button class="btn-view-details" onclick="loadCustomerOrders('${status}')" style="margin-top: 1rem;">
                    <span class="material-symbols-rounded">refresh</span>
                    Retry
                </button>
            </div>
        `;
    }
}

// ==========================================
// DISPLAY ORDERS
// ==========================================
function displayOrders(orders) {
    const container = document.getElementById('ordersContainer');

    if (!orders || orders.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <span class="material-symbols-rounded">shopping_bag</span>
                <h3>No orders found</h3>
                <p>${currentFilter === 'all' ? 'You haven\'t placed any orders yet.' : `No ${currentFilter} orders.`}</p>
            </div>
        `;
        return;
    }

    container.innerHTML = orders.map(order => generateOrderCard(order)).join('');
}

// ==========================================
// GENERATE ORDER CARD HTML (WITH PAYMENT BUTTON)
// ==========================================
function generateOrderCard(order) {
    const statusClass = getOrderStatusClass(order.status);
    const paymentText = getPaymentStatusText(order);
    const paymentClass = getPaymentStatusClass(order);

    // Check if order needs initial payment
    const needsPayment = order.payment_status === 'Pending' &&
        (!order.payment_verification_status || order.payment_verification_status === 'REJECTED');

    // Installment section
    let installmentHTML = '';
    if (order.is_installment == 1 && order.installments && order.installments.length > 0) {
        const installments = order.installments;
        const totalInstallments = installments.length;
        const paidInstallments = installments.filter(i => i.status === 'PAID').length;
        const currentInstallment = installments.find(i => i.status === 'PENDING');

        installmentHTML = `
            <div class="installment-info">
                <h4>💳 Installment Payment Plan</h4>
                <div class="installment-progress">
                    ${installments.map((inst, idx) => `
                        <div class="installment-step ${inst.status === 'PAID' ? 'paid' : idx === paidInstallments ? 'current' : ''}"></div>
                    `).join('')}
                </div>
                <div class="installment-details">
                    <span>Payment ${paidInstallments} of ${totalInstallments} completed</span>
                    <strong>₱${parseFloat(currentInstallment?.amount_due || 0).toLocaleString()} next payment</strong>
                </div>
            </div>
        `;
    }

    // Show "Pay Balance" button if has unpaid installments
    const hasUnpaid = order.is_installment == 1 &&
        order.installments &&
        order.installments.some(i => i.status === 'PENDING' || i.status === 'UNPAID');


    return `
        <div class="order-card" data-order-id="${order.id}">
            <div class="order-card-header">
                <div class="order-code-section">
                    <h3>${escapeHtml(order.order_code)}</h3>
                    <div class="order-date">Ordered on ${formatDate(order.order_date)}</div>
                </div>
                <div class="order-status-badge status-${statusClass}">
                    ${escapeHtml(order.status)}
                </div>
            </div>
            
            <div class="order-card-body">
                <div class="order-items-list">
                    <strong>📦 Order Items:</strong>
                    ${escapeHtml(order.items || 'N/A')}
                </div>
                <div class="order-summary">
                    <div class="order-total">₱${parseFloat(order.total_amount).toLocaleString()}</div>
                    <div class="payment-info payment-${paymentClass}">
                        ${paymentText}
                    </div>
                </div>
            </div>
            
            ${installmentHTML}
            
            <div class="order-card-footer">
    <div class="delivery-mode">
        ${order.mode === 'delivery' ?
            '<span class="material-symbols-rounded">local_shipping</span> Delivery' :
            '<span class="material-symbols-rounded">store</span> Pickup'}
    </div>
    <div class="order-actions">
        <button class="btn-view-details" onclick="viewOrderDetails(${order.id})">
            <span class="material-symbols-rounded">visibility</span>
            View Details
        </button>
        ${needsPayment ? `
            <button class="btn-pay-balance" onclick="openPaymentModal(${order.id})">
                <span class="material-symbols-rounded">payments</span>
                Submit Payment
            </button>
        ` : ''}
        ${hasUnpaid ? `
            <button class="btn-pay-balance" onclick="openSplitPayment(${order.id})">
                <span class="material-symbols-rounded">payments</span>
                Pay Balance
            </button>
        ` : ''}
        ${canCompleteOrder(order) ? `
            <button class="btn-pay-balance" onclick="openCompleteOrderModal(${order.id})" style="background: var(--success);">
                <span class="material-symbols-rounded">check_circle</span>
                Complete Order
            </button>
        ` : ''}
        ${(order.payment_status === 'Partially Paid') ? `
            <button class="btn-pay-balance" onclick="openSplitPayment(${order.id})">
                <span class="material-symbols-rounded">payments</span>
                Pay Remaining
            </button>
        ` : ''}
    </div>
    </div>
        </div>
    `;
}

// ==========================================
// CHECK IF ORDER CAN BE COMPLETED BY CUSTOMER
// ==========================================
function canCompleteOrder(order) {
    // Can complete if:
    // 1. Status is "Delivered" or "Ready for Pickup"
    // 2. Payment is fully paid
    // 3. Not already completed

    const completableStatuses = ['delivered', 'ready for pickup'];
    const isCompletableStatus = completableStatuses.includes(order.status.toLowerCase());
    const isFullyPaid = order.payment_status === 'Fully Paid';
    const notCompleted = order.status.toLowerCase() !== 'completed';

    return isCompletableStatus && isFullyPaid && notCompleted;
}

// ==========================================
// OPEN COMPLETE ORDER MODAL
// ==========================================
function openCompleteOrderModal(orderId) {
    const order = allOrders.find(o => o.id === orderId);
    if (!order) return;

    const modal = document.getElementById('completeOrderModal');
    const content = document.getElementById('completeOrderContent');

    const completionType = order.mode === 'delivery' ? 'delivery' : 'pickup';

    content.innerHTML = `
        <div style="display: grid; gap: 2rem;">
            <!-- Instructions -->
            <div style="background: #e3f2fd; padding: 1.5rem; border-radius: 8px; border-left: 4px solid var(--info);">
                <h4 style="color: var(--brand); margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                    <span class="material-symbols-rounded">info</span>
                    ${completionType === 'delivery' ? 'Delivery Confirmation' : 'Pickup Confirmation'}
                </h4>
                <p style="color: #1976d2; line-height: 1.6;">
                    ${completionType === 'delivery'
            ? 'Please confirm that you have received your order. You can upload a photo or video to document the condition of the item upon delivery.'
            : 'Please confirm that you have picked up your order. You can upload a photo to document your purchase.'}
                </p>
            </div>
            
            <!-- Order Info -->
            <div>
                <h4 style="color: var(--brand); margin-bottom: 1rem;">Order Information</h4>
                <p><strong>Order Code:</strong> ${escapeHtml(order.order_code)}</p>
                <p><strong>Item:</strong> ${escapeHtml(order.items || 'N/A')}</p>
                <p><strong>Total:</strong> ₱${parseFloat(order.total_amount).toLocaleString()}</p>
            </div>
            
            <!-- Upload Form -->
            <form id="completeOrderForm" onsubmit="submitOrderCompletion(event, ${orderId}, '${completionType}')">
                <div style="display: grid; gap: 1.5rem;">
                    
                    <!-- Photo Upload (Optional) -->
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: var(--brand);">
                            <span class="material-symbols-rounded" style="vertical-align: middle;">add_a_photo</span>
                            Upload Photo (Optional)
                        </label>
                        <input type="file" id="completionPhoto" accept="image/*" style="width: 100%; padding: 0.75rem; border: 2px dashed var(--border-color); border-radius: 8px; font-family: 'Poppins', sans-serif;">
                        <small style="color: var(--text-secondary); display: block; margin-top: 0.5rem;">
                            ${completionType === 'delivery'
            ? 'Document the condition of your cabinet upon delivery'
            : 'Take a photo of your cabinet after pickup'}
                        </small>
                    </div>
                    
                    <!-- Notes (Optional) -->
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: var(--brand);">
                            <span class="material-symbols-rounded" style="vertical-align: middle;">note</span>
                            Additional Notes (Optional)
                        </label>
                        <textarea id="completionNotes" rows="3" placeholder="Any comments or observations..." style="width: 100%; padding: 0.75rem; border: 2px solid var(--border-color); border-radius: 8px; font-family: 'Poppins', sans-serif; resize: vertical;"></textarea>
                    </div>
                    
                    <!-- Submit Button -->
                    <button type="submit" class="btn-pay-balance" style="width: 100%; justify-content: center; padding: 1rem; font-size: 1.1rem;">
                        <span class="material-symbols-rounded">check_circle</span>
                        Confirm Order Completion
                    </button>
                </div>
            </form>
        </div>
    `;

    modal.classList.add('active');
}

// ==========================================
// SUBMIT ORDER COMPLETION
// ==========================================
async function submitOrderCompletion(event, orderId, completionType) {
    event.preventDefault();

    const form = event.target;
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalHTML = submitBtn.innerHTML;

    submitBtn.disabled = true;
    submitBtn.innerHTML = `
        <span class="spinner" style="width: 20px; height: 20px; border-width: 2px;"></span>
        Processing...
    `;

    try {
        const formData = new FormData();
        formData.append('order_id', orderId);
        formData.append('completion_type', completionType);

        const photo = document.getElementById('completionPhoto').files[0];
        if (photo) {
            formData.append('photo', photo);
        }

        const notes = document.getElementById('completionNotes').value.trim();
        if (notes) {
            formData.append('notes', notes);
        }

        const response = await fetch('/RADS-TOOLING/backend/api/complete_order.php', {
            method: 'POST',
            credentials: 'same-origin',
            body: formData
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        const result = await response.json();

        if (!result.success) {
            throw new Error(result.message || 'Failed to complete order');
        }

        showNotification('Order completed successfully! Thank you for your purchase.', 'success');
        closeCompleteOrderModal();

        // Reload orders
        setTimeout(() => {
            loadCustomerOrders(currentFilter);
        }, 1000);

    } catch (error) {
        console.error('Complete order error:', error);
        showNotification('Failed to complete order: ' + error.message, 'error');

        submitBtn.disabled = false;
        submitBtn.innerHTML = originalHTML;
    }
}

// ==========================================
// CLOSE COMPLETE ORDER MODAL
// ==========================================
function closeCompleteOrderModal() {
    document.getElementById('completeOrderModal').classList.remove('active');
}

// ==========================================
// FILTER ORDERS
// ==========================================
function filterOrders(status) {
    currentFilter = status;

    // Update active menu item
    document.querySelectorAll('.menu-item').forEach(item => {
        item.classList.remove('active');
    });
    document.querySelector(`.menu-item[data-status="${status}"]`)?.classList.add('active');

    // Load orders
    loadCustomerOrders(status);
}

// ==========================================
// SEARCH ORDERS
// ==========================================
function searchOrders() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase().trim();

    if (!searchTerm) {
        filteredOrders = allOrders;
    } else {
        filteredOrders = allOrders.filter(order => {
            return order.order_code.toLowerCase().includes(searchTerm) ||
                (order.items && order.items.toLowerCase().includes(searchTerm));
        });
    }

    displayOrders(filteredOrders);
}

// ==========================================
// UPDATE BADGE COUNTS (FIXED - DOESN'T ZERO OUT)
// ==========================================
function updateBadgeCounts() {
    // Update all badges with pre-calculated counts
    document.getElementById('badge-all').textContent = orderCounts.all;
    document.getElementById('badge-pending').textContent = orderCounts.pending;
    document.getElementById('badge-processing').textContent = orderCounts.processing;
    document.getElementById('badge-completed').textContent = orderCounts.completed;
    document.getElementById('badge-cancelled').textContent = orderCounts.cancelled;
}

// ==========================================
// VIEW ORDER DETAILS MODAL
// ==========================================
async function viewOrderDetails(orderId) {
    const modal = document.getElementById('orderDetailsModal');
    const content = document.getElementById('orderDetailsContent');

    // Show modal with loading
    modal.classList.add('active');
    content.innerHTML = `
        <div class="loading-state">
            <div class="spinner"></div>
            <p>Loading order details...</p>
        </div>
    `;

    try {
        const response = await fetch(`/RADS-TOOLING/backend/api/customer_orders.php?action=details&id=${orderId}`, {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' }
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        const result = await response.json();

        if (!result.success) {
            throw new Error(result.message || 'Failed to load order details');
        }

        const { order, items, installments } = result.data;
        content.innerHTML = generateOrderDetailsHTML(order, items, installments);

    } catch (error) {
        console.error('View order details error:', error);
        content.innerHTML = `
            <div class="empty-state">
                <span class="material-symbols-rounded" style="color: #dc3545;">error</span>
                <h3>Failed to load order details</h3>
                <p>${escapeHtml(error.message)}</p>
                <button class="btn-view-details" onclick="closeOrderDetailsModal()" style="margin-top: 1rem;">Close</button>
            </div>
        `;
    }
}

// ==========================================
// GENERATE ORDER DETAILS HTML
// ==========================================
function generateOrderDetailsHTML(order, items, installments) {
    // Installment table (if applicable)
    let installmentHTML = '';
    if (installments && installments.length > 0) {
        installmentHTML = `
            <div style="margin-top: 2rem; padding-top: 2rem; border-top: 2px solid #e9ecef;">
                <h3 style="color: var(--brand); margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                    <span class="material-symbols-rounded">credit_card</span>
                    Installment Payment Schedule
                </h3>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                                <th style="padding: 1rem; text-align: left; font-weight: 600;">Payment #</th>
                                <th style="padding: 1rem; text-align: right; font-weight: 600;">Amount Due</th>
                                <th style="padding: 1rem; text-align: right; font-weight: 600;">Amount Paid</th>
                                <th style="padding: 1rem; text-align: center; font-weight: 600;">Status</th>
                                <th style="padding: 1rem; text-align: center; font-weight: 600;">Verified</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${installments.map((inst, idx) => `
                                <tr style="border-bottom: 1px solid #e9ecef;">
                                    <td style="padding: 1rem;">
                                        <strong>${idx === 0 ? '💰 Deposit' : `📋 Payment ${inst.installment_number}`}</strong>
                                    </td>
                                    <td style="padding: 1rem; text-align: right; font-weight: 500;">
                                        ₱${parseFloat(inst.amount_due).toLocaleString()}
                                    </td>
                                    <td style="padding: 1rem; text-align: right; font-weight: 500; color: ${inst.amount_paid > 0 ? '#28a745' : '#6c757d'};">
                                        ₱${parseFloat(inst.amount_paid).toLocaleString()}
                                    </td>
                                    <td style="padding: 1rem; text-align: center;">
                                        <span class="order-status-badge status-${inst.status === 'PAID' ? 'completed' : 'pending'}" style="font-size: 0.75rem; padding: 0.35rem 0.85rem;">
                                            ${inst.status}
                                        </span>
                                    </td>
                                    <td style="padding: 1rem; text-align: center;">
                                        ${inst.verified_at ?
                `<span style="color: #28a745;">✓ ${formatDate(inst.verified_at)}</span>` :
                '<span style="color: #6c757d;">—</span>'}
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                        <tfoot>
                            <tr style="background: #f8f9fa; font-weight: 700; border-top: 2px solid var(--brand);">
                                <td style="padding: 1rem;">TOTAL</td>
                                <td style="padding: 1rem; text-align: right; color: var(--brand);">
                                    ₱${installments.reduce((sum, i) => sum + parseFloat(i.amount_due), 0).toLocaleString()}
                                </td>
                                <td style="padding: 1rem; text-align: right; color: #28a745;">
                                    ₱${installments.reduce((sum, i) => sum + parseFloat(i.amount_paid), 0).toLocaleString()}
                                </td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        `;
    }

    return `
        <div style="display: grid; gap: 2rem;">
            <!-- Order Info Grid -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                <div>
                    <h3 style="color: var(--brand); margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                        <span class="material-symbols-rounded">shopping_cart</span>
                        Order Information
                    </h3>
                    <div style="display: grid; gap: 0.75rem;">
                        <p><strong>Order Code:</strong> ${escapeHtml(order.order_code)}</p>
                        <p><strong>Order Date:</strong> ${formatDate(order.order_date)}</p>
                        <p><strong>Status:</strong> <span class="order-status-badge status-${getOrderStatusClass(order.status)}">${escapeHtml(order.status)}</span></p>
                        <p><strong>Payment Status:</strong> <span class="payment-info payment-${getPaymentStatusClass(order)}">${getPaymentStatusText(order)}</span></p>
                        <p><strong>Delivery Mode:</strong> ${order.mode === 'delivery' ? '🚚 Delivery' : '📦 Store Pickup'}</p>
                    </div>
                </div>
                
                ${order.mode === 'delivery' ? `
                    <div>
                        <h3 style="color: var(--brand); margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                            <span class="material-symbols-rounded">location_on</span>
                            Delivery Address
                        </h3>
                        <div style="display: grid; gap: 0.5rem; color: #495057;">
                            <p><strong>${escapeHtml(order.first_name || '')} ${escapeHtml(order.last_name || '')}</strong></p>
                            <p>${escapeHtml(order.phone || 'N/A')}</p>
                            <p>${escapeHtml(order.street || '')}</p>
                            <p>${escapeHtml(order.barangay || '')}</p>
                            <p>${escapeHtml(order.city || '')}, ${escapeHtml(order.province || '')} ${escapeHtml(order.postal || '')}</p>
                        </div>
                    </div>
                ` : `
                    <div>
                        <h3 style="color: var(--brand); margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                            <span class="material-symbols-rounded">store</span>
                            Pickup Information
                        </h3>
                        <div style="display: grid; gap: 0.75rem; color: #495057;">
                            <p><strong>📍 Pickup Location:</strong></p>
                            <p>RADS Tooling Store</p>
                            <p>Green Breeze, Piela</p>
                            <p>Dasmariñas, Cavite</p>
                            <p style="margin-top: 1rem;"><strong>⏰ Store Hours:</strong></p>
                            <p>Mon-Sat: 8:00 AM - 5:00 PM</p>
                        </div>
                    </div>
                `}
            </div>
            
            <!-- Order Items -->
            <div>
                <h3 style="color: var(--brand); margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                    <span class="material-symbols-rounded">inventory_2</span>
                    Order Items
                </h3>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; border: 1px solid #dee2e6;">
                        <thead>
                            <tr style="background: linear-gradient(135deg, var(--brand) 0%, var(--brand-dark) 100%); color: white;">
                                <th style="padding: 1rem; text-align: left;">Item</th>
                                <th style="padding: 1rem; text-align: center;">Qty</th>
                                <th style="padding: 1rem; text-align: right;">Unit Price</th>
                                <th style="padding: 1rem; text-align: right;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${items.map((item, idx) => `
                                <tr style="border-bottom: 1px solid #dee2e6; background: ${idx % 2 === 0 ? 'white' : '#f8f9fa'};">
                                    <td style="padding: 1rem;">${escapeHtml(item.name)}</td>
                                    <td style="padding: 1rem; text-align: center; font-weight: 600;">${item.qty}</td>
                                    <td style="padding: 1rem; text-align: right;">₱${parseFloat(item.unit_price).toLocaleString()}</td>
                                    <td style="padding: 1rem; text-align: right; font-weight: 600;">₱${parseFloat(item.line_total).toLocaleString()}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                        <tfoot>
                            <tr style="border-top: 1px solid #dee2e6;">
                                <td colspan="3" style="padding: 1rem; text-align: right; font-weight: 600;">Subtotal:</td>
                                <td style="padding: 1rem; text-align: right; font-weight: 600;">₱${parseFloat(order.subtotal).toLocaleString()}</td>
                            </tr>
                            <tr>
                                <td colspan="3" style="padding: 1rem; text-align: right;">VAT (12%):</td>
                                <td style="padding: 1rem; text-align: right;">₱${parseFloat(order.vat).toLocaleString()}</td>
                            </tr>
                            <tr style="background: linear-gradient(135deg, var(--brand-light) 0%, #d4e4f7 100%); border-top: 3px solid var(--brand);">
                                <td colspan="3" style="padding: 1.25rem; text-align: right; font-weight: 700; color: var(--brand); font-size: 1.2rem;">
                                    TOTAL AMOUNT:
                                </td>
                                <td style="padding: 1.25rem; text-align: right; font-weight: 700; color: var(--brand); font-size: 1.5rem;">
                                    ₱${parseFloat(order.total_amount).toLocaleString()}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            
            ${installmentHTML}
        </div>
    `;
}

// ==========================================
// OPEN PAYMENT MODAL (FOR INITIAL PAYMENT)
// ==========================================
async function openPaymentModal(orderId) {
    const modal = document.getElementById('paymentModal');
    const content = document.getElementById('paymentModalContent');

    modal.classList.add('active');
    content.innerHTML = `
        <div class="loading-state">
            <div class="spinner"></div>
            <p>Loading payment options...</p>
        </div>
    `;

    try {
        // Get order details
        const response = await fetch(`/RADS-TOOLING/backend/api/customer_orders.php?action=details&id=${orderId}`, {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' }
        });

        if (!response.ok) throw new Error('Failed to fetch order details');

        const result = await response.json();
        if (!result.success) throw new Error(result.message);

        const { order } = result.data;

        // Generate payment decision form
        content.innerHTML = generateInitialPaymentForm(order);

    } catch (error) {
        console.error('Open payment modal error:', error);
        content.innerHTML = `
            <div class="empty-state">
                <span class="material-symbols-rounded" style="color: #dc3545;">error</span>
                <h3>Error</h3>
                <p>${escapeHtml(error.message)}</p>
                <button class="btn-view-details" onclick="closePaymentModal()" style="margin-top: 1rem;">Close</button>
            </div>
        `;
    }
}

// ==========================================
// GENERATE INITIAL PAYMENT FORM
// ==========================================
function generateInitialPaymentForm(order) {
    return `
        <div style="display: grid; gap: 2rem;">
            <!-- Order Summary -->
            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 2rem; border-radius: 12px;">
                <h3 style="margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                    <span class="material-symbols-rounded">receipt</span>
                    Order Summary
                </h3>
                <div style="display: grid; gap: 0.75rem; font-size: 1.1rem;">
                    <p><strong>Order Code:</strong> ${escapeHtml(order.order_code)}</p>
                    <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid rgba(255,255,255,0.3);">
                        <p style="font-size: 0.9rem; opacity: 0.9;">Total Amount:</p>
                        <p style="font-size: 2.5rem; font-weight: 700; margin-top: 0.25rem;">₱${parseFloat(order.total_amount).toLocaleString()}</p>
                    </div>
                </div>
            </div>
            
            <!-- Payment Options -->
            <div>
                <h3 style="color: var(--brand); margin-bottom: 1.5rem;">Select Payment Option</h3>
                
                <div style="display: grid; gap: 1rem;">
                    <label class="payment-option-card">
                        <input type="radio" name="depositRate" value="100" checked>
                        <div class="option-content">
                            <div>
                                <h4>💰 Full Payment</h4>
                                <p>Pay 100% now</p>
                            </div>
                            <div class="option-price">₱${parseFloat(order.total_amount).toLocaleString()}</div>
                        </div>
                    </label>
                    
                    <label class="payment-option-card">
                        <input type="radio" name="depositRate" value="50">
                        <div class="option-content">
                            <div>
                                <h4>💳 50% Down Payment</h4>
                                <p>Pay 50% now, 50% later</p>
                            </div>
                            <div class="option-price">₱${(parseFloat(order.total_amount) * 0.5).toLocaleString()}</div>
                        </div>
                    </label>
                    
                    <label class="payment-option-card">
                        <input type="radio" name="depositRate" value="30">
                        <div class="option-content">
                            <div>
                                <h4>📋 30% Down Payment</h4>
                                <p>Pay 30% now, 70% later</p>
                            </div>
                            <div class="option-price">₱${(parseFloat(order.total_amount) * 0.3).toLocaleString()}</div>
                        </div>
                    </label>
                </div>
            </div>
            
            <!-- Submit Button -->
            <button class="btn-pay-balance" onclick="submitPaymentDecision(${order.id})" style="width: 100%; justify-content: center; padding: 1rem; font-size: 1.1rem;">
                <span class="material-symbols-rounded">arrow_forward</span>
                Proceed to Payment
            </button>
        </div>
    `;
}

// ==========================================
// SUBMIT PAYMENT DECISION (FIXED)
// ==========================================
async function submitPaymentDecision(orderId) {
    const depositRate = document.querySelector('input[name="depositRate"]:checked')?.value;

    if (!depositRate) {
        showNotification('Please select a payment option', 'error');
        return;
    }

    const submitBtn = event?.target;
    const originalHTML = submitBtn?.innerHTML;

    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = `
            <span class="spinner" style="width: 20px; height: 20px; border-width: 2px;"></span>
            Processing...
        `;
    }

    try {
        // Step 1: Submit payment decision
        const response = await fetch('/RADS-TOOLING/backend/api/payment_decision.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                order_id: orderId,
                method: 'gcash', // Default, user will choose later
                deposit_rate: parseInt(depositRate)
            })
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        const result = await response.json();

        if (!result.success) {
            throw new Error(result.message || 'Failed to process payment decision');
        }

        // Step 2: Open the split payment (proof upload) modal
        closePaymentModal();
        openSplitPayment(orderId);

    } catch (error) {
        console.error('Payment decision error:', error);
        showNotification('Failed to process payment: ' + error.message, 'error');

        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalHTML;
        }
    }
}

// ==========================================
// OPEN SPLIT PAYMENT MODAL
// ==========================================
async function openSplitPayment(orderId) {
    const modal = document.getElementById('splitPaymentModal');
    const content = document.getElementById('splitPaymentContent');

    modal.classList.add('active');
    content.innerHTML = `
        <div class="loading-state">
            <div class="spinner"></div>
            <p>Loading payment information...</p>
        </div>
    `;

    try {
        // Get order details with installments
        const response = await fetch(`/RADS-TOOLING/backend/api/customer_orders.php?action=details&id=${orderId}`, {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' }
        });

        if (!response.ok) throw new Error('Failed to fetch order details');

        const result = await response.json();
        if (!result.success) throw new Error(result.message);

        const { order, installments } = result.data;
        let nextPayment = installments?.find(i => i.status === 'PENDING');

        if (!nextPayment) {
            const unpaid = installments?.find(i => i.status === 'UNPAID');
            if (unpaid) {
                // promote to PENDING
                const prep = await fetch('/RADS-TOOLING/backend/api/installment_prepare.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ order_id: orderId, installment_id: unpaid.id })
                });
                const prepRaw = await prep.text();
                if (!prep.ok) throw new Error(`Prep HTTP ${prep.status}: ${prepRaw.slice(0, 200)}`);
                const prepJson = JSON.parse(prepRaw);
                if (!prepJson.success) throw new Error(prepJson.message || 'Failed to prepare installment');

                // re-fetch details after promote
                const response2 = await fetch(`/RADS-TOOLING/backend/api/customer_orders.php?action=details&id=${orderId}`, { credentials: 'same-origin' });
                const result2 = await response2.json();
                if (!result2.success) throw new Error(result2.message);
                const { order: o2, installments: inst2 } = result2.data;
                nextPayment = inst2?.find(i => i.status === 'PENDING');
                order = o2; // update local reference if needed
            }
        }

        if (!nextPayment) {
            throw new Error('No pending payments found');
        }

        content.innerHTML = generateSplitPaymentForm(order, nextPayment);


    } catch (error) {
        console.error('Open split payment error:', error);
        content.innerHTML = `
            <div class="empty-state">
                <span class="material-symbols-rounded" style="color: #dc3545;">error</span>
                <h3>Error</h3>
                <p>${escapeHtml(error.message)}</p>
                <button class="btn-view-details" onclick="closeSplitPaymentModal()" style="margin-top: 1rem;">Close</button>
            </div>
        `;
    }
}

// ==========================================
// GENERATE SPLIT PAYMENT FORM
// ==========================================
function generateSplitPaymentForm(order, installment) {
    return `
        <div style="display: grid; gap: 2rem;">
            <!-- Payment Summary -->
            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 2rem; border-radius: 12px;">
                <h3 style="margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                    <span class="material-symbols-rounded">receipt</span>
                    Payment Summary
                </h3>
                <div style="display: grid; gap: 0.75rem; font-size: 1.1rem;">
                    <p><strong>Order Code:</strong> ${escapeHtml(order.order_code)}</p>
                    <p><strong>Payment #:</strong> ${installment.installment_number} of ${order.installments?.length || 0}</p>
                    <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid rgba(255,255,255,0.3);">
                        <p style="font-size: 0.9rem; opacity: 0.9;">Amount Due:</p>
                        <p style="font-size: 2.5rem; font-weight: 700; margin-top: 0.25rem;">₱${parseFloat(installment.amount_due).toLocaleString()}</p>
                    </div>
                </div>
            </div>
            
            <!-- Payment Instructions -->
            <div style="background: #fff3cd; padding: 1.5rem; border-radius: 8px; border-left: 4px solid #ffc107;">
                <h4 style="color: #856404; margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                    <span class="material-symbols-rounded">info</span>
                    Important Instructions
                </h4>
                <ul style="color: #856404; margin-left: 1.25rem; line-height: 1.8;">
                    <li>Make payment via GCash or BPI</li>
                    <li>Take a clear screenshot of your payment receipt</li>
                    <li>Upload the screenshot below</li>
                    <li>Wait for admin verification (usually within 24 hours)</li>
                </ul>
            </div>
            
            <!-- Payment Form -->
            <form id="splitPaymentForm" onsubmit="submitSplitPayment(event, ${order.id}, ${installment.id})">
                <div style="display: grid; gap: 1.5rem;">
                    
                    <!-- Payment Method -->
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: var(--brand);">
                            <span class="material-symbols-rounded" style="vertical-align: middle;">account_balance</span>
                            Payment Method *
                        </label>
                        <select id="splitPaymentMethod" required style="width: 100%; padding: 0.75rem; border: 2px solid #dee2e6; border-radius: 8px; font-family: 'Poppins', sans-serif; font-size: 1rem;">
                            <option value="">Select payment method</option>
                            <option value="gcash">GCash</option>
                            <option value="bpi">BPI</option>
                        </select>
                    </div>
                    
                    <!-- Account Name -->
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: var(--brand);">
                            <span class="material-symbols-rounded" style="vertical-align: middle;">person</span>
                            Account Name *
                        </label>
                        <input type="text" id="splitAccountName" required placeholder="Juan Dela Cruz" style="width: 100%; padding: 0.75rem; border: 2px solid #dee2e6; border-radius: 8px; font-family: 'Poppins', sans-serif; font-size: 1rem;">
                    </div>
                    
                    <!-- Account Number (optional for GCash) -->
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: var(--brand);">
                            <span class="material-symbols-rounded" style="vertical-align: middle;">tag</span>
                            Account/Mobile Number (Optional)
                        </label>
                        <input type="text" id="splitAccountNumber" placeholder="09XXXXXXXXX" style="width: 100%; padding: 0.75rem; border: 2px solid #dee2e6; border-radius: 8px; font-family: 'Poppins', sans-serif; font-size: 1rem;">
                    </div>
                    
                    <!-- Reference Number -->
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: var(--brand);">
                            <span class="material-symbols-rounded" style="vertical-align: middle;">confirmation_number</span>
                            Reference/Transaction Number *
                        </label>
                        <input type="text" id="splitReferenceNumber" required placeholder="REF123456789" style="width: 100%; padding: 0.75rem; border: 2px solid #dee2e6; border-radius: 8px; font-family: 'Poppins', sans-serif; font-size: 1rem;">
                    </div>
                    
                    <!-- Amount Paid -->
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: var(--brand);">
                            <span class="material-symbols-rounded" style="vertical-align: middle;">payments</span>
                            Amount Paid *
                        </label>
                        <input type="number" id="splitAmountPaid" required step="0.01" min="${installment.amount_due}" value="${installment.amount_due}" placeholder="0.00" style="width: 100%; padding: 0.75rem; border: 2px solid #dee2e6; border-radius: 8px; font-family: 'Poppins', sans-serif; font-size: 1.25rem; font-weight: 600; color: var(--brand);">
                        <small style="color: #6c757d; display: block; margin-top: 0.5rem;">Minimum: ₱${parseFloat(installment.amount_due).toLocaleString()}</small>
                    </div>
                    
                    <!-- Screenshot Upload -->
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: var(--brand);">
                            <span class="material-symbols-rounded" style="vertical-align: middle;">upload_file</span>
                            Payment Screenshot *
                        </label>
                        <input type="file" id="splitScreenshot" required accept="image/*" style="width: 100%; padding: 0.75rem; border: 2px dashed #dee2e6; border-radius: 8px; font-family: 'Poppins', sans-serif;">
                        <small style="color: #6c757d; display: block; margin-top: 0.5rem;">Accepted: JPG, PNG (Max 5MB)</small>
                    </div>
                    
                    <!-- Submit Button -->
                    <button type="submit" class="btn-pay-balance" style="width: 100%; justify-content: center; padding: 1rem; font-size: 1.1rem; margin-top: 1rem;">
                        <span class="material-symbols-rounded">send</span>
                        Submit Payment Proof
                    </button>
                </div>
            </form>
        </div>
    `;
}

// ==========================================
// SUBMIT SPLIT PAYMENT
// ==========================================
async function submitSplitPayment(event, orderId, installmentId) {
    event.preventDefault();

    const form = event.target;
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalBtnText = submitBtn.innerHTML;

    // Disable button and show loading
    submitBtn.disabled = true;
    submitBtn.innerHTML = `
        <span class="spinner" style="width: 20px; height: 20px; border-width: 2px;"></span>
        Processing...
    `;

    try {
        // Gather form data
        const formData = new FormData();
        formData.append('order_id', orderId);
        formData.append('installment_id', installmentId);
        formData.append('method', document.getElementById('splitPaymentMethod').value);
        formData.append('account_name', document.getElementById('splitAccountName').value);
        formData.append('account_number', document.getElementById('splitAccountNumber').value || '');
        formData.append('reference_number', document.getElementById('splitReferenceNumber').value);
        formData.append('amount_paid', document.getElementById('splitAmountPaid').value);

        const screenshot = document.getElementById('splitScreenshot').files[0];
        if (screenshot) {
            formData.append('screenshot', screenshot);
        }

        // Submit to backend
        const response = await fetch('/RADS-TOOLING/backend/api/installment_pay.php', {
            method: 'POST',
            credentials: 'same-origin',
            body: formData
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        const result = await response.json();

        if (!result.success) {
            throw new Error(result.message || 'Failed to submit payment');
        }

        // Success!
        showNotification('Payment submitted successfully! Wait for admin verification.', 'success');
        closeSplitPaymentModal();

        // Reload orders to reflect changes
        setTimeout(() => {
            loadCustomerOrders(currentFilter);
        }, 1000);

    } catch (error) {
        console.error('Submit split payment error:', error);
        showNotification('Failed to submit payment: ' + error.message, 'error');

        // Re-enable button
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnText;
    }
}

// ==========================================
// CLOSE MODALS
// ==========================================
function closeOrderDetailsModal() {
    document.getElementById('orderDetailsModal').classList.remove('active');
}

function closeSplitPaymentModal() {
    document.getElementById('splitPaymentModal').classList.remove('active');
}

function closePaymentModal() {
    document.getElementById('paymentModal').classList.remove('active');
}

// ==========================================
// HELPER FUNCTIONS
// ==========================================
function getOrderStatusClass(status) {
    const statusMap = {
        'pending': 'pending',
        'processing': 'processing',
        'completed': 'completed',
        'cancelled': 'cancelled'
    };
    return statusMap[status?.toLowerCase()] || 'pending';
}

function getPaymentStatusText(order) {
    if (order.payment_status === 'Fully Paid') {
        return '✅ Fully Paid';
    }
    if (order.payment_status === 'Partially Paid') {
        return `💳 Partially Paid (${order.deposit_rate || 0}% deposit)`;
    }
    return '⏳ Awaiting Payment';
}

function getPaymentStatusClass(order) {
    if (order.payment_status === 'Fully Paid') return 'verified';
    if (order.payment_status === 'Partially Paid') return 'pending';
    return 'pending';
}

function formatDate(dateString) {
    if (!dateString) return 'N/A';
    try {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    } catch (e) {
        return dateString;
    }
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// ==========================================
// NOTIFICATION SYSTEM
// ==========================================
function showNotification(message, type = 'info') {
    // Remove existing notification
    const existing = document.querySelector('.notification-toast');
    if (existing) existing.remove();

    // Create notification
    const notification = document.createElement('div');
    notification.className = `notification-toast notification-${type}`;
    notification.innerHTML = `
        <span class="material-symbols-rounded">
            ${type === 'success' ? 'check_circle' : type === 'error' ? 'error' : 'info'}
        </span>
        <span>${escapeHtml(message)}</span>
    `;

    // Add to body
    document.body.appendChild(notification);

    // Show with animation
    setTimeout(() => notification.classList.add('show'), 100);

    // Auto remove after 5 seconds
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 5000);
}

// Add notification styles dynamically
const notificationStyles = document.createElement('style');
notificationStyles.textContent = `
    .notification-toast {
        position: fixed;
        top: 90px;
        right: 2rem;
        background: white;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        display: flex;
        align-items: center;
        gap: 0.75rem;
        z-index: 10001;
        transform: translateX(400px);
        opacity: 0;
        transition: all 0.3s ease;
        border-left: 4px solid;
    }
    
    .notification-toast.show {
        transform: translateX(0);
        opacity: 1;
    }
    
    .notification-toast .material-symbols-rounded {
        font-size: 1.5rem;
    }
    
    .notification-success {
        border-left-color: #28a745;
    }
    
    .notification-success .material-symbols-rounded {
        color: #28a745;
    }
    
    .notification-error {
        border-left-color: #dc3545;
    }
    
    .notification-error .material-symbols-rounded {
        color: #dc3545;
    }
    
    .notification-info {
        border-left-color: #17a2b8;
    }
    
    .notification-info .material-symbols-rounded {
        color: #17a2b8;
    }
`;
document.head.appendChild(notificationStyles);