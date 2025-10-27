// admin/assets/JS/product_management.js
// ✅ UPDATED: Delete button removed from Actions column
// ✅ FIXED: Release confirmation now uses custom modal instead of browser alert
// ✅ FIXED: Better error handling for edit functionality
// ✅ FIXED: Added image display for Textures and Handles
// ✅ FIXED: Better null checking to prevent DOM errors

// ===== Global state =====
let allProducts = [];
let allTextures = [];
let allColors = [];
let allHandles = [];
let currentProduct = null;             // used by customization / edit
const MAX_GLB_MB = 80;                 // front-end cap for .glb

// ===== Boot =====
document.addEventListener('DOMContentLoaded', () => {
  injectPMStyles();
  initPMModalNotifier();
  initConfirmationModal();              // ✅ NEW: Initialize confirmation modal
  hideAddProductFields();              // if legacy stock/size blocks still exist

  loadProducts();
  loadTextures();
  loadColors();
  loadHandles();
  initializeEventListeners();

  // GLB input initial state
  updateModelRequirement();

  // Make backdrop clicks NOT close Add Product accidentally
 const addModal = document.getElementById('addProductModal');
  if (addModal) {
    addModal.addEventListener('click', (e) => {
      if (e.target === addModal) e.stopPropagation();
    }, true);
  }

  // FIXED: Add same protection for manageCustomizationModal
  const customModal = document.getElementById('manageCustomizationModal');
  if (customModal) {
    customModal.addEventListener('click', (e) => {
      if (e.target === customModal) e.stopPropagation();
    }, true);
  }

  // FIXED: Add explicit event listener for Save Changes button
  const saveCustomBtn = document.querySelector('#manageCustomizationModal .btn-primary');
  if (saveCustomBtn && !saveCustomBtn.hasAttribute('data-listener-added')) {
    saveCustomBtn.setAttribute('data-listener-added', 'true');
    saveCustomBtn.addEventListener('click', async (e) => {
      e.preventDefault();
      e.stopPropagation();
      await saveCustomizationOptions();
    });
  }
});
// ===== Helpers =====
async function fetchJSON(url, opts) {
  const res = await fetch(url, opts);
  const text = await res.text();
  try {
    const data = JSON.parse(text);
    if (!res.ok || data.success === false) throw new Error(data.message || res.statusText);
    return data;
  } catch (err) {
    console.error('❌ Bad JSON or HTML from:', url, '\nRaw:\n', text);
    throw err;
  }
}

function hideAddProductFields() {
  ['stockGroup', 'stockField', 'stockRow'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.style.display = 'none';
  });
  const stockInput = document.getElementById('productStock');
  if (stockInput) { stockInput.value = '0'; stockInput.disabled = true; }

  ['sizeConfigSection', 'sizeSliderSection', 'sizeConfigCard'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.style.display = 'none';
  });
}

// ===== Events =====
function initializeEventListeners() {
  document.getElementById('product-search')?.addEventListener('input', filterProducts);
  document.getElementById('product-filter')?.addEventListener('change', filterProducts);

  // Submit (add or update, decided at runtime)
  document.getElementById('addProductForm')?.addEventListener('submit', handleAddProduct);

  // Uploads
  document.getElementById('productImage')?.addEventListener('change', handleImagePreview);
  document.getElementById('productModel')?.addEventListener('change', handleModelPreview);

  // Customizable checkbox -> enable/disable .glb
  document.getElementById('isCustomizable')?.addEventListener('change', updateModelRequirement);

  // Cancel in the modal: clear + close + friendly info toast
  const cancelBtn =
    document.getElementById('addProductCancelBtn') ||
    document.querySelector('#addProductModal .btn-cancel') ||
    document.querySelector('#addProductModal [data-role="cancelAddProduct"]');

  if (cancelBtn) {
    cancelBtn.addEventListener('click', (e) => {
      e.preventDefault();
      resetAddProductForm(true); // also closes
      showNotification('info', 'New order has been canceled');
    });
  }

  // Intercept "Add Product" header button even if HTML still calls openModal('addProductModal')
  document.querySelector('.btn-add-product')?.addEventListener('click', (e) => {
    e.preventDefault();
    openAddProductFresh();
  });

  // Optional: availability toggle if you show it inside edit
  document.getElementById('btnToggleAvailability')?.addEventListener('click', async (e) => {
    e.preventDefault();
    if (!currentProduct) return;
    const makeAvailable = currentProduct.is_available == 0 ? 1 : 0;
    await toggleAvailability(currentProduct.id, makeAvailable);
  });
}

// ===== Fetch data =====
async function loadProducts() {
  try {
    const data = await fetchJSON('/RADS-TOOLING/backend/api/admin_products.php?action=list');
    allProducts = data.data || [];
    displayProducts(allProducts);
  } catch (err) {
    console.error('Load products error:', err);
    showNotification('error', 'Failed to load products');
  }
}
async function loadTextures() {
  try {
    const d = await fetchJSON('/RADS-TOOLING/backend/api/admin_customization.php?action=list_textures');
    allTextures = d.data || [];
    // Debug: Log first texture to see actual structure
    if (allTextures.length > 0) {
      console.log('📦 Texture data structure:', allTextures[0]);
    }
  } catch (e) { console.warn('Failed to load textures:', e); }
}
async function loadColors() {
  try {
    const d = await fetchJSON('/RADS-TOOLING/backend/api/admin_customization.php?action=list_colors');
    allColors = d.data || [];
  } catch (e) { console.warn(e); }
}
async function loadHandles() {
  try {
    const d = await fetchJSON('/RADS-TOOLING/backend/api/admin_customization.php?action=list_handles');
    allHandles = d.data || [];
    // Debug: Log first handle to see actual structure
    if (allHandles.length > 0) {
      console.log('🔧 Handle data structure:', allHandles[0]);
    }
  } catch (e) { console.warn('Failed to load handles:', e); }
}

// ===== Render =====
// ✅ UPDATED: Removed delete button from this function
function displayProducts(products) {
  const tbody = document.getElementById('productTableBody');
  if (!tbody) return;

  if (!products.length) {
    tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;">No products found</td></tr>';
    return;
  }

  // ✅ DELETE BUTTON HAS BEEN REMOVED FROM ACTIONS COLUMN
  tbody.innerHTML = products.map((product) => `
    <tr>
      <td>
      <img
        src="/RADS-TOOLING/uploads/products/${product.image || 'placeholder.jpg'}"
        alt="${product.name}"
        class="product-img"
        onerror="this.onerror=null; this.src='/RADS-TOOLING/assets/images/placeholder.png'">
      </td>
      <td><strong>${product.name}</strong></td>
      <td><span class="badge badge-info">${product.type}</span></td>
      <td>${product.description || 'N/A'}</td>
      <td>₱${parseFloat(product.price).toFixed(2)}</td>
      <td>
        <span class="badge ${Number(product.is_customizable) === 1 ? 'badge-success' : 'badge-secondary'}">
          ${Number(product.is_customizable) === 1 ? 'Yes' : 'No'}
        </span>
      </td>
      <td>
  <span class="badge ${product.status === 'released' ? 'badge-active' : 'badge-inactive'}">
    ${product.status === 'released' ? 'released' : 'draft'}
  </span>
</td>

      <td>
        <button class="btn-edit" title="Edit Product" onclick="handleEditProduct(${product.id})">
          <span class="material-symbols-rounded">edit</span>
        </button>
        ${Number(product.is_customizable) === 1 ? `
          <button class="btn-edit" title="Manage Customization" onclick="openCustomizationModal(${product.id})">
            <span class="material-symbols-rounded">tune</span>
          </button>
        ` : ''}
        <button 
          class="btn-edit" 
          title="${product.status === 'released' ? 'Set to Draft' : 'Release Product'}" 
          onclick="toggleProductRelease(${product.id}, '${product.status === 'released' ? 'draft' : 'released'}')">
          <span class="material-symbols-rounded">
            ${product.status === 'released' ? 'visibility_off' : 'visibility'}
          </span>
        </button>
      </td>
    </tr>
  `).join('');
}

// ===== Filter =====
function filterProducts() {
  const searchTerm = document.getElementById('product-search')?.value?.toLowerCase() || '';
  const filterType = document.getElementById('product-filter')?.value || '';

  const filtered = allProducts.filter(p => {
    const matchesSearch = !searchTerm ||
      (p.name?.toLowerCase().includes(searchTerm)) ||
      (p.description?.toLowerCase().includes(searchTerm));

    const matchesType = !filterType || p.type === filterType;

    return matchesSearch && matchesType;
  });

  displayProducts(filtered);
}

// ===== Add/Edit Product =====
function openAddProductFresh() {
  resetAddProductForm(false);
  const titleEl = document.getElementById('addProductModalTitle');
  if (titleEl) titleEl.textContent = 'Add New Product';
  
  const form = document.getElementById('addProductForm');
  if (form) delete form.dataset.editingId;
  openModal('addProductModal');
}

async function handleEditProduct(id) {
  try {
    // ✅ IMPROVED: Better error handling with more specific error messages
    console.log('Fetching product details for ID:', id);
    
    const data = await fetchJSON(`/RADS-TOOLING/backend/api/admin_products.php?action=view&id=${id}`);
    
    if (!data || !data.data) {
      throw new Error('No product data returned from server');
    }
    
    const product = data.data;
    currentProduct = product;

    // ✅ FIXED: Added null check before setting textContent
    const titleEl = document.getElementById('addProductModalTitle');
    if (titleEl) {
      titleEl.textContent = 'Edit Product';
    }
    
    const form = document.getElementById('addProductForm');
    if (form) form.dataset.editingId = id;

    setField('productName', product.name);
    setField('productType', product.type);
    setField('productDescription', product.description);
    setField('productPrice', product.price);
    setField('isCustomizable', Number(product.is_customizable) === 1, true);

    // Show existing product image if available
    if (product.image) {
      const imgPrev = document.getElementById('productImagePreview');
      if (imgPrev) {
        imgPrev.src = `/RADS-TOOLING/uploads/products/${product.image}`;
        imgPrev.style.display = 'block';
        imgPrev.dataset.filename = product.image;
      }
    }

    // Show existing model file name if available
    if (product.model_3d) {
      const modelPrev = document.getElementById('productModelPreview');
      if (modelPrev) {
        modelPrev.textContent = `📦 Current: ${product.model_3d}`;
        modelPrev.style.display = 'block';
        modelPrev.dataset.filename = product.model_3d;
      }
    }

    openModal('addProductModal');
  } catch (err) {
    console.error('Edit product error:', err);
    // ✅ IMPROVED: More descriptive error message
    showNotification('error', `Failed to load product details: ${err.message || 'Unknown error'}`);
  }
}

async function handleAddProduct(e) {
  e.preventDefault();

  const form = e.target;
  const editingId = form.dataset.editingId;
  const isEdit = !!editingId;

  const imgPrev = document.getElementById('productImagePreview');
  const modelPrev = document.getElementById('productModelPreview');

  const imageFilename = imgPrev?.dataset?.filename || '';
  const modelFilename = modelPrev?.dataset?.filename || '';

  const formData = {
    name: document.getElementById('productName')?.value?.trim(),
    type: document.getElementById('productType')?.value,
    description: document.getElementById('productDescription')?.value?.trim(),
    price: document.getElementById('productPrice')?.value,
    is_customizable: document.getElementById('isCustomizable')?.checked ? 1 : 0,
    image: imageFilename,
    model_3d: modelFilename
  };

  if (!formData.name || !formData.type || !formData.price) {
    showNotification('error', 'Please fill in all required fields');
    return;
  }

  try {
    const endpoint = isEdit
      ? `/RADS-TOOLING/backend/api/admin_products.php?action=update&id=${editingId}`
      : '/RADS-TOOLING/backend/api/admin_products.php?action=add';

    await fetchJSON(endpoint, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(formData)
    });

    showNotification('success', isEdit ? 'Product updated successfully' : 'Product added successfully');
    closeModal('addProductModal');
    resetAddProductForm(false);
    loadProducts();
  } catch (err) {
    console.error('Save product error:', err);
    showNotification('error', err.message || 'Failed to save product');
  }
}

// ===== Image / Model upload =====
async function handleImagePreview(e) {
  const file = e.target.files?.[0];
  if (!file) return;

  const formData = new FormData();
  formData.append('image', file);

  try {
    const data = await fetch('/RADS-TOOLING/backend/api/admin_products.php?action=upload_image', {
      method: 'POST',
      body: formData
    }).then(r => r.json());

    if (!data.success) throw new Error(data.message);

    const prev = document.getElementById('productImagePreview');
    if (prev) {
      prev.src = `/RADS-TOOLING/uploads/products/${data.data.filename}`;
      prev.style.display = 'block';
      prev.dataset.filename = data.data.filename;
    }

    showNotification('success', 'Image uploaded successfully');
  } catch (err) {
    console.error('Image upload error:', err);
    showNotification('error', 'Failed to upload image');
  }
}

async function handleModelPreview(e) {
  const file = e.target.files?.[0];
  if (!file) return;

  const sizeMB = file.size / (1024 * 1024);
  if (sizeMB > MAX_GLB_MB) {
    showNotification('error', `Model file too large. Max ${MAX_GLB_MB}MB.`);
    e.target.value = '';
    return;
  }

  const formData = new FormData();
  formData.append('model', file);

  try {
    const data = await fetch('/RADS-TOOLING/backend/api/admin_products.php?action=upload_model', {
      method: 'POST',
      body: formData
    }).then(r => r.json());

    if (!data.success) throw new Error(data.message);

    const prev = document.getElementById('productModelPreview');
    if (prev) {
      prev.textContent = `📦 ${file.name}`;
      prev.style.display = 'block';
      prev.dataset.filename = data.data.filename;
    }

    showNotification('success', '3D model uploaded successfully');
  } catch (err) {
    console.error('Model upload error:', err);
    showNotification('error', 'Failed to upload model');
  }
}

// ===== Release toggle =====
// ✅ FIXED: Replaced browser confirm() with custom modal confirmation
async function toggleProductRelease(productId, newStatus) {
  const actionText = newStatus === 'released' ? 'release' : 'set to draft';
  const message = `Are you sure you want to ${actionText} this product?`;
  
  const confirmed = await showConfirmation(message);
  if (!confirmed) return;

  try {
    await fetchJSON('/RADS-TOOLING/backend/api/admin_products.php?action=toggle_release', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ product_id: productId, status: newStatus })
    });

    showNotification('success', `Product ${newStatus === 'released' ? 'released' : 'set to draft'} successfully`);
    loadProducts();
  } catch (err) {
    console.error('Toggle release error:', err);
    showNotification('error', 'Failed to update product status');
  }
}

// ===== Customization Modal =====
async function openCustomizationModal(productId) {
  try {
    const data = await fetchJSON(`/RADS-TOOLING/backend/api/admin_products.php?action=view&id=${productId}`);
    const product = data.data;
    currentProduct = product;

    // Debug: Log product customization data
    console.log('🎨 Product customization data:', {
      textures: product.textures,
      colors: product.colors,
      handles: product.handles
    });

    const productIdEl = document.getElementById('customProductId');
    if (productIdEl) productIdEl.value = productId;
    
    const productNameEl = document.getElementById('customProductName');
    if (productNameEl) productNameEl.textContent = product.name;

    populateSizeConfig(product.size_config || []);
    populateTexturesList(product.textures || []);
    populateColorsList(product.colors || []);
    populateHandlesList(product.handles || []);

    openModal('manageCustomizationModal');
  } catch (error) {
    console.error('Open customization modal error:', error);
    showNotification('error', 'Failed to load customization options');
  }
}

function populateSizeConfig(sizes) {
  const sizeMap = {};
  sizes.forEach(s => { sizeMap[s.dimension_type] = s; });

  ['width', 'height', 'depth'].forEach(dim => {
    const s = sizeMap[dim];
    if (!s) return;

    setField(`${dim}MinCustom`, s.min_value);
    setField(`${dim}MaxCustom`, s.max_value);
    setField(`${dim}DefaultCustom`, s.default_value);
    setField(`${dim}StepCustom`, s.step_value);
    setField(`${dim}Unit`, s.measurement_unit);

    const mode = s.pricing_mode || 'percm';
    const radios = document.getElementsByName(`${dim}PricingMode`);
    radios.forEach(r => { r.checked = (r.value === mode); });

    setField(`${dim}PPU`, s.price_per_unit);
    setField(`${dim}BlockCM`, s.price_block_cm);
    setField(`${dim}PerBlock`, s.price_per_block);
  });
}

// ✅ FIXED: Added image display for textures with better error handling
async function populateTexturesList(productTextures) {
  const container = document.getElementById('texturesListContainer');
  if (!container) return;
  container.innerHTML = '';

  // Load existing texture parts assignments for this product
  let existingParts = {};
  try {
    if (currentProduct?.id) {
      const response = await fetchJSON(`/RADS-TOOLING/backend/api/admin_customization.php?action=list_product_textures_parts&product_id=${currentProduct.id}`);
      if (response.success && response.data) {
        response.data.forEach(texture => {
          existingParts[texture.id] = texture.allowed_parts || [];
        });
      }
    }
  } catch (error) {
    console.warn('Failed to load existing texture parts:', error);
  }

  // Inline SVG placeholder to avoid 404 errors
  const placeholderSVG = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 40 40"%3E%3Crect fill="%23e5e7eb" width="40" height="40"/%3E%3Ctext x="50%25" y="50%25" dominant-baseline="middle" text-anchor="middle" font-family="Arial" font-size="12" fill="%239ca3af"%3ENo Img%3C/text%3E%3C/svg%3E';

  const selectedIds = productTextures.map(t => +t.texture_id);
  allTextures.forEach(texture => {
    const checked = selectedIds.includes(+texture.id);
    
    // Try multiple possible property names for image and name
    const textureName = texture.name || texture.texture_name || texture.title || 'Unnamed Texture';
    const textureImage = texture.image || texture.texture_image || texture.file_path || texture.filename;
    const texturePrice = texture.price || texture.texture_price || 0;
    
    const imageUrl = textureImage 
      ? `/RADS-TOOLING/uploads/textures/${textureImage}` 
      : placeholderSVG;

    // Get existing parts for this texture
    const textureParts = existingParts[texture.id] || [];
    const bodyChecked = textureParts.includes('body') ? 'checked' : '';
    const doorChecked = textureParts.includes('door') ? 'checked' : '';
    const interiorChecked = textureParts.includes('interior') ? 'checked' : '';
    
    container.innerHTML += `
      <div style="border:1px solid #e5e7eb; border-radius:8px; margin:8px 0; padding:12px; background: ${checked ? '#f0f9ff' : 'white'}; transition: all 0.2s;">
        <label style="display:flex; align-items:center; gap:12px; cursor:pointer;" onmouseover="this.parentElement.style.backgroundColor='#f9fafb'" onmouseout="this.parentElement.style.backgroundColor='${checked ? '#f0f9ff' : 'white'}'">
          <input type="checkbox" value="${texture.id}" ${checked ? 'checked' : ''} style="width:18px; height:18px; cursor:pointer; flex-shrink:0;" onchange="toggleTexturePartOptions(this)">
          <img src="${imageUrl}" 
               alt="${textureName}" 
               style="width:50px; height:50px; object-fit:cover; border-radius:6px; border:1px solid #d1d5db; flex-shrink:0;"
               onerror="this.src='${placeholderSVG}'">
          <div style="flex:1; min-width:0;">
            <div style="font-weight:600; color:#111827; font-size:14px;">${textureName}</div>
            ${texturePrice > 0 ? `<div style="color:#6b7280; font-size:13px; margin-top:2px;">₱${parseFloat(texturePrice).toFixed(2)}</div>` : ''}
          </div>
        </label>
        
        <!-- Part Selection Checkboxes -->
        <div class="texture-parts" data-texture-id="${texture.id}" style="margin-top:12px; padding-left:6px; ${checked ? 'display:block' : 'display:none'};">
          <div style="font-size:12px; color:#6b7280; margin-bottom:6px; font-weight:500;">Apply to Parts:</div>
          <div style="display:flex; gap:16px; flex-wrap:wrap;">
            <label style="display:flex; align-items:center; gap:6px; font-size:13px; cursor:pointer;">
              <input type="checkbox" class="texture-part-checkbox" data-part="body" ${bodyChecked} style="width:14px; height:14px;">
              <span style="color:#374151;">Body/Frame</span>
            </label>
            <label style="display:flex; align-items:center; gap:6px; font-size:13px; cursor:pointer;">
              <input type="checkbox" class="texture-part-checkbox" data-part="door" ${doorChecked} style="width:14px; height:14px;">
              <span style="color:#374151;">Door</span>
            </label>
            <label style="display:flex; align-items:center; gap:6px; font-size:13px; cursor:pointer;">
              <input type="checkbox" class="texture-part-checkbox" data-part="interior" ${interiorChecked} style="width:14px; height:14px;">
              <span style="color:#374151;">Interior</span>
            </label>
          </div>
        </div>
      </div>`;
  });
}

function toggleTexturePartOptions(mainCheckbox) {
  const textureId = mainCheckbox.value;
  const partsDiv = document.querySelector(`.texture-parts[data-texture-id="${textureId}"]`);
  if (partsDiv) {
    partsDiv.style.display = mainCheckbox.checked ? 'block' : 'none';
    
    // If unchecking main checkbox, also uncheck all part checkboxes
    if (!mainCheckbox.checked) {
      const partCheckboxes = partsDiv.querySelectorAll('.texture-part-checkbox');
      partCheckboxes.forEach(cb => cb.checked = false);
    }
  }
}

function populateColorsList(productColors) {
  const container = document.getElementById('colorsListContainer');
  if (!container) return;
  container.innerHTML = '';

  const selectedIds = productColors.map(c => +c.color_id);
  allColors.forEach(color => {
    const checked = selectedIds.includes(+color.id);
    const colorName = color.name || color.color_name || 'Unnamed Color';
    const colorPrice = color.price || color.color_price || 0;
    const hexCode = color.hex_code || color.hex || color.color || '#cccccc';
    
    container.innerHTML += `
      <label style="display:flex; align-items:center; gap:12px; margin:8px 0; padding:12px; border:1px solid #e5e7eb; border-radius:8px; cursor:pointer; transition: all 0.2s;" onmouseover="this.style.backgroundColor='#f9fafb'" onmouseout="this.style.backgroundColor='transparent'">
        <input type="checkbox" value="${color.id}" ${checked ? 'checked' : ''} style="width:18px; height:18px; cursor:pointer; flex-shrink:0;">
        <div style="width:50px; height:50px; background:${hexCode}; border:2px solid #d1d5db; border-radius:6px; flex-shrink:0;"></div>
        <div style="flex:1; min-width:0;">
          <div style="font-weight:600; color:#111827; font-size:14px;">${colorName}</div>
          ${colorPrice > 0 ? `<div style="color:#6b7280; font-size:13px; margin-top:2px;">₱${parseFloat(colorPrice).toFixed(2)}</div>` : ''}
        </div>
      </label>`;
  });
}

// ✅ FIXED: Added image display for handles with better error handling
function populateHandlesList(productHandles) {
  const container = document.getElementById('handlesListContainer');
  if (!container) return;
  container.innerHTML = '';

  // Inline SVG placeholder to avoid 404 errors
  const placeholderSVG = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 40 40"%3E%3Crect fill="%23e5e7eb" width="40" height="40"/%3E%3Ctext x="50%25" y="50%25" dominant-baseline="middle" text-anchor="middle" font-family="Arial" font-size="12" fill="%239ca3af"%3ENo Img%3C/text%3E%3C/svg%3E';

  const selectedIds = productHandles.map(h => +h.handle_id);
  allHandles.forEach(handle => {
    const checked = selectedIds.includes(+handle.id);
    
    // Try multiple possible property names for image and name
    const handleName = handle.name || handle.handle_name || handle.title || 'Unnamed Handle';
    const handleImage = handle.image || handle.handle_image || handle.file_path || handle.filename;
    const handlePrice = handle.price || handle.handle_price || 0;
    
    const imageUrl = handleImage 
      ? `/RADS-TOOLING/uploads/handles/${handleImage}` 
      : placeholderSVG;
    
    container.innerHTML += `
      <label style="display:flex; align-items:center; gap:12px; margin:8px 0; padding:12px; border:1px solid #e5e7eb; border-radius:8px; cursor:pointer; transition: all 0.2s;" onmouseover="this.style.backgroundColor='#f9fafb'" onmouseout="this.style.backgroundColor='transparent'">
        <input type="checkbox" value="${handle.id}" ${checked ? 'checked' : ''} style="width:18px; height:18px; cursor:pointer; flex-shrink:0;">
        <img src="${imageUrl}" 
             alt="${handleName}" 
             style="width:50px; height:50px; object-fit:cover; border-radius:6px; border:1px solid #d1d5db; flex-shrink:0;"
             onerror="this.src='${placeholderSVG}'">
        <div style="flex:1; min-width:0;">
          <div style="font-weight:600; color:#111827; font-size:14px;">${handleName}</div>
          ${handlePrice > 0 ? `<div style="color:#6b7280; font-size:13px; margin-top:2px;">₱${parseFloat(handlePrice).toFixed(2)}</div>` : ''}
        </div>
      </label>`;
  });
}

function collectSizeConfig() {
  const getDim = (dim) => {
    const unit = (document.getElementById(`${dim}Unit`)?.value) || 'cm';
    const radios = document.getElementsByName(`${dim}PricingMode`);
    const pricing_mode = [...radios].find(r => r.checked)?.value || 'percm';

    const price_per_unit = Number(document.getElementById(`${dim}PPU`)?.value || 0);
    const price_block_cm = Number(document.getElementById(`${dim}BlockCM`)?.value || 0);
    const price_per_block = Number(document.getElementById(`${dim}PerBlock`)?.value || 0);

    return {
      min_value: Number(document.getElementById(`${dim}MinCustom`)?.value || 0),
      max_value: Number(document.getElementById(`${dim}MaxCustom`)?.value || 300),
      default_value: Number(document.getElementById(`${dim}DefaultCustom`)?.value || 100),
      step_value: Number(document.getElementById(`${dim}StepCustom`)?.value || 1),
      measurement_unit: unit,
      pricing_mode,
      price_per_unit,
      price_block_cm,
      price_per_block
    };
  };

  return { width: getDim('width'), height: getDim('height'), depth: getDim('depth') };
}

async function saveCustomizationOptions() {
  const el = id => document.getElementById(id);
  const valNum = id => Number(el(id)?.value || 0);
  const valStr = id => (el(id)?.value || '');

  const getDim = (dim) => {
    const min = valNum(`${dim}MinCustom`);
    const max = valNum(`${dim}MaxCustom`);
    const def = (el(`${dim}DefaultCustom`) || el(`${dim}DefCustom`)) ? Number((el(`${dim}DefaultCustom`) || el(`${dim}DefCustom`)).value || 0) : undefined;
    const step = el(`${dim}StepCustom`) ? Number(el(`${dim}StepCustom`).value || 1) : 1;
    const unit = (el(`${dim}Unit`)?.value) || (currentProduct?.measurement_unit || 'cm');

    const ppu = el(`${dim}PPU`) ? Number(el(`${dim}PPU`).value || 0)
      : el(`${dim}PriceCustom`) ? Number(el(`${dim}PriceCustom`).value || 0) : 0;
    const bSize = el(`${dim}BlockCM`) ? Number(el(`${dim}BlockCM`).value || 0)
      : el(`${dim}BlockSize`) ? Number(el(`${dim}BlockSize`).value || 0) : 0;
    const bCost = el(`${dim}PerBlock`) ? Number(el(`${dim}PerBlock`).value || 0)
      : el(`${dim}BlockPrice`) ? Number(el(`${dim}BlockPrice`).value || 0) : 0;

    const radios = document.getElementsByName(`${dim}PricingMode`);
    const mode = [...radios].find(r => r.checked)?.value || 'percm';

    const pricing_mode = (mode === 'block') ? 'block' : 'percm';
    const price_block_cm = (pricing_mode === 'percm') ? 0 : bSize;
    const price_per_block = (pricing_mode === 'percm') ? 0 : bCost;
    const price_per_unit = ppu;

    return {
      min_value: min,
      max_value: max,
      ...(def !== undefined ? { default_value: def } : {}),
      step_value: step,
      measurement_unit: unit,
      pricing_mode: pricing_mode,
      price_per_unit: price_per_unit,
      price_block_cm: price_block_cm,
      price_per_block: price_per_block
    };
  };

  const product_id = el('customProductId')?.value;
  if (!product_id) {
    showNotification('error', 'Product ID not found');
    return;
  }

  const size_config = {
    width: getDim('width'),
    height: getDim('height'),
    depth: getDim('depth')
  };

  const texture_ids = Array.from(document.querySelectorAll('#texturesListContainer input:checked')).map(cb => +cb.value);
  const color_ids = Array.from(document.querySelectorAll('#colorsListContainer   input:checked')).map(cb => +cb.value);
  const handle_ids = Array.from(document.querySelectorAll('#handlesListContainer  input:checked')).map(cb => +cb.value);

  try {
    await fetchJSON('/RADS-TOOLING/backend/api/admin_customization.php?action=update_size_config', {
      method: 'POST', headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ product_id, size_config })
    });
    await fetchJSON('/RADS-TOOLING/backend/api/admin_customization.php?action=assign_textures', {
      method: 'POST', headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ product_id, texture_ids })
    });
    await fetchJSON('/RADS-TOOLING/backend/api/admin_customization.php?action=assign_colors', {
      method: 'POST', headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ product_id, color_ids })
    });
    await fetchJSON('/RADS-TOOLING/backend/api/admin_customization.php?action=assign_handles', {
      method: 'POST', headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ product_id, handle_ids })
    });

    showNotification('success', 'Customization options saved successfully');
    closeModal('manageCustomizationModal');
    loadProducts();
  } catch (error) {
    console.error('Save customization error:', error);
    showNotification('error', 'Failed to save customization options');
  }
}


// ===== Small utilities / UI glue =====
function initPMModalNotifier() {
  if (document.getElementById('pm-toast-modal')) return;

  const wrap = document.createElement('div');
  wrap.innerHTML = `
    <div id="pm-toast-modal" style="position:fixed; inset:0; display:none; align-items:center; justify-content:center; z-index:9999; backdrop-filter: blur(2px); background: rgba(0,0,0,.35);">
      <div id="pm-toast-box" style="min-width:320px; max-width:520px; background:#111827; color:#e5e7eb; border-radius:12px; box-shadow:0 12px 24px rgba(0,0,0,.25); overflow:hidden;">
        <div id="pm-toast-head" style="display:flex; align-items:center; gap:10px; padding:14px 16px; border-bottom:1px solid rgba(255,255,255,.08); background:#1e3a5f;">
          <span id="pm-toast-title" style="font-weight:700;">Notice</span>
        </div>
        <div id="pm-toast-body" style="padding:14px 16px; line-height:1.5;"></div>
        <div style="padding:12px 16px; display:flex; justify-content:flex-end; gap:8px;">
          <button id="pm-toast-ok" style="background:#67e8f9; color:#0f172a; border:0; padding:8px 14px; border-radius:8px; font-weight:600; cursor:pointer;">OK</button>
        </div>
      </div>
    </div>`;
  document.body.appendChild(wrap);

  const modal = document.getElementById('pm-toast-modal');
  const title = document.getElementById('pm-toast-title');
  const body = document.getElementById('pm-toast-body');

  function open(type, message) {
    title.textContent = type === 'error' ? 'Error' : type === 'success' ? 'Success' : 'Notice';
    body.textContent = message;
    modal.style.display = 'flex';
  }
  function close() { modal.style.display = 'none'; }

  document.getElementById('pm-toast-ok').addEventListener('click', close);
  modal.addEventListener('click', (e) => { if (e.target === modal) close(); });

  // Simple shim used everywhere in this file
  window.showNotification = (type, message) => open(type, message);
}

// ✅ NEW: Custom confirmation modal to replace browser alert
function initConfirmationModal() {
  if (document.getElementById('pm-confirm-modal')) return;

  const wrap = document.createElement('div');
  wrap.innerHTML = `
    <div id="pm-confirm-modal" style="position:fixed; inset:0; display:none; align-items:center; justify-content:center; z-index:9999; backdrop-filter: blur(2px); background: rgba(0,0,0,.35);">
      <div id="pm-confirm-box" style="min-width:320px; max-width:520px; background:#111827; color:#e5e7eb; border-radius:12px; box-shadow:0 12px 24px rgba(0,0,0,.25); overflow:hidden;">
        <div id="pm-confirm-head" style="display:flex; align-items:center; gap:10px; padding:14px 16px; border-bottom:1px solid rgba(255,255,255,.08); background:#1e3a5f;">
          <span id="pm-confirm-title" style="font-weight:700;">Confirm Action</span>
        </div>
        <div id="pm-confirm-body" style="padding:14px 16px; line-height:1.5;"></div>
        <div style="padding:12px 16px; display:flex; justify-content:flex-end; gap:8px;">
          <button id="pm-confirm-cancel" style="background:#374151; color:#e5e7eb; border:0; padding:8px 14px; border-radius:8px; font-weight:600; cursor:pointer;">Cancel</button>
          <button id="pm-confirm-ok" style="background:#67e8f9; color:#0f172a; border:0; padding:8px 14px; border-radius:8px; font-weight:600; cursor:pointer;">OK</button>
        </div>
      </div>
    </div>`;
  document.body.appendChild(wrap);

  const modal = document.getElementById('pm-confirm-modal');
  const body = document.getElementById('pm-confirm-body');
  let resolvePromise = null;

  function open(message) {
    return new Promise((resolve) => {
      resolvePromise = resolve;
      body.textContent = message;
      modal.style.display = 'flex';
    });
  }

  function close(result) {
    modal.style.display = 'none';
    if (resolvePromise) {
      resolvePromise(result);
      resolvePromise = null;
    }
  }

  document.getElementById('pm-confirm-ok').addEventListener('click', () => close(true));
  document.getElementById('pm-confirm-cancel').addEventListener('click', () => close(false));
  modal.addEventListener('click', (e) => { 
    if (e.target === modal) close(false); 
  });

  // Global function for use throughout the file
  window.showConfirmation = (message) => open(message);
}

function injectPMStyles() {
  if (document.getElementById('pm-style-patch')) return;
  const style = document.createElement('style');
  style.id = 'pm-style-patch';
  style.textContent = `
    /* Checkbox + label inline */
    .customizable-row { display:inline-flex; align-items:center; gap:8px; margin:10px 0; }

    /* Image preview styling */
    #productImagePreview { display:block; max-width:240px; max-height:180px; object-fit:cover;
      margin-top:12px; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,.08); }

    /* Modal content scrolls; footer not sticky (prevents overlap) */
    #addProductModal .modal-content { max-height:90vh; overflow:auto; }
  `;
  document.head.appendChild(style);
}

// Modal open/close that play nice with page scroll
function openModal(id) { const m = document.getElementById(id); if (m) { m.classList.add('show'); document.body.style.overflow = 'hidden'; } }
function closeModal(id) { const m = document.getElementById(id); if (m) { m.classList.remove('show'); document.body.style.overflow = ''; } }

// Enable/disable .glb input on checkbox
function updateModelRequirement() {
  const cb = document.getElementById('isCustomizable');
  const model = document.getElementById('productModel');
  const prev = document.getElementById('productModelPreview');
  if (!cb || !model) return;

  model.disabled = !cb.checked;
  model.required = false;
  if (!cb.checked) {
    model.value = '';
    if (prev) { prev.textContent = ''; prev.style.display = 'none'; delete prev.dataset.filename; }
  }
}

// Reset Add form (and optionally close)
function resetAddProductForm(closeAfter = false) {
  const form = document.getElementById('addProductForm');
  form?.reset();
  if (form) delete form.dataset.editingId;

  const imgPrev = document.getElementById('productImagePreview');
  if (imgPrev) { imgPrev.removeAttribute('data-filename'); imgPrev.src = ''; imgPrev.style.display = 'none'; }

  const modelPrev = document.getElementById('productModelPreview');
  if (modelPrev) { modelPrev.removeAttribute('data-filename'); modelPrev.textContent = ''; modelPrev.style.display = 'none'; }

  const cb = document.getElementById('isCustomizable'); if (cb) cb.checked = false;
  const model = document.getElementById('productModel');
  if (model) { model.disabled = true; model.required = false; model.value = ''; }

  if (closeAfter) closeModal('addProductModal');
}

// Safe field setter
function setField(id, value, isCheckbox = false) {
  const el = document.getElementById(id);
  if (!el) return;
  if (isCheckbox) {
    el.checked = !!value;
    el.dispatchEvent(new Event('change'));
  } else {
    el.value = value ?? '';
    el.dispatchEvent(new Event('input'));
  }
}