// admin/assets/JS/product_management.js

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

  // Intercept “Add Product” header button even if HTML still calls openModal('addProductModal')
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
  } catch (e) { console.warn(e); }
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
  } catch (e) { console.warn(e); }
}

// ===== Render =====
function displayProducts(products) {
  const tbody = document.getElementById('productTableBody');
  if (!tbody) return;

  if (!products.length) {
    tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;">No products found</td></tr>';
    return;
  }

  // NOTE: we intentionally DO NOT render stock/status columns anymore.
  // The two cells removed are the ones that printed `${product.stock || 0}` and the availability badge
  // (they caused the lonely “0” column). See previous version here: :contentReference[oaicite:1]{index=1}
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
          </button>` : ''}
          ${product.status === 'released' ? `
  <button class="btn-edit" title="Unrelease" onclick="toggleRelease(${product.id}, 'draft')">
    <span class="material-symbols-rounded">visibility_off</span>
  </button>
` : `
  <button class="btn-edit" title="Release" onclick="toggleRelease(${product.id}, 'released')">
    <span class="material-symbols-rounded">visibility</span>
  </button>
`}
        <button class="btn-delete" title="Delete Product" onclick="deleteProduct(${product.id})">
          <span class="material-symbols-rounded">delete</span>
        </button>
      </td>
    </tr>
  `).join('');
}

function filterProducts() {
  const q = document.getElementById('product-search')?.value.toLowerCase() || '';
  const typ = document.getElementById('product-filter')?.value || '';
  const out = allProducts.filter(p =>
    (p.name?.toLowerCase().includes(q) || p.description?.toLowerCase().includes(q)) &&
    (!typ || p.type === typ)
  );
  displayProducts(out);
}

// ===== Add / Update (same handler) =====
async function handleAddProduct(e) {
  e.preventDefault();

  const $ = (id) => document.getElementById(id);
  const v = (id) => $(id)?.value ?? '';
  const fN = (id, d = 0) => Number.isFinite(parseFloat(v(id))) ? parseFloat(v(id)) : d;


  const measurement_unit = $('measurementUnit') ? v('measurementUnit') : 'cm';

  const payload = {
    name: v('productName'),
    type: v('productType'),
    description: v('productDescription'),
    price: fN('productPrice', 0),
    stock: 0, // always 0 on create (no stock field in modal)
    measurement_unit,
    is_customizable: $('isCustomizable')?.checked ? 1 : 0,
    image: $('productImagePreview')?.dataset.filename || '',
    model_3d: $('productModelPreview')?.dataset.filename || ''
  };

  const form = $('addProductForm');
  const editingId = form?.dataset.editingId;
  const action = editingId ? `update&id=${editingId}` : 'add';

  try {
    const response = await fetch(`/RADS-TOOLING/backend/api/admin_products.php?action=${action}`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });

    const raw = await response.text();                     // <- correct object (fix) :contentReference[oaicite:2]{index=2}
    let data;
    try { data = JSON.parse(raw); }
    catch (e) {
      console.error('Add/Update raw response:', raw);
      throw new Error('Server returned a non-JSON response.');
    }

    if (data?.success) {
      showNotification('success', editingId ? 'Product updated successfully' : 'Product added successfully');
      resetAddProductForm();                                // clears dataset.editingId too
      closeModal('addProductModal');
      await loadProducts();
      return;
    }

    showNotification('error', data?.message || 'Failed to save product');
  } catch (err) {
    console.error('Add/Update product error:', err);
    showNotification('error', 'Failed to save product');
  }
}

// ===== Edit product (populate + switch modal to edit mode) =====
async function handleEditProduct(productId) {
  try {
    const res = await fetch(`/RADS-TOOLING/backend/api/admin_products.php?action=view&id=${productId}`);
    const data = await res.json();

    if (!data?.success || !data?.data) {
      showNotification('error', 'Failed to load product details');
      return;
    }
    const product = data.data;

    setField('productName', product.name);
    setField('productType', product.type);
    setField('productDescription', product.description || '');
    setField('productPrice', product.price);
    setField('isCustomizable', Number(product.is_customizable) === 1, true);

    // Image preview
    const imgPrev = document.getElementById('productImagePreview');
    if (imgPrev) {
      if (product.image) {
        imgPrev.src = `/RADS-TOOLING/uploads/products/${product.image}`;
        imgPrev.style.display = 'block';
        imgPrev.dataset.filename = product.image;
      } else {
        imgPrev.removeAttribute('data-filename'); imgPrev.src = ''; imgPrev.style.display = 'none';
      }
    }

    // 3D model preview
    const modelPrev = document.getElementById('productModelPreview');
    if (modelPrev) {
      if (product.model_3d) {
        modelPrev.textContent = product.model_3d;
        modelPrev.style.display = 'block';
        modelPrev.dataset.filename = product.model_3d;
      } else {
        modelPrev.removeAttribute('data-filename'); modelPrev.textContent = ''; modelPrev.style.display = 'none';
      }
    }

    // Switch modal into EDIT mode
    const form = document.getElementById('addProductForm');
    if (form) form.dataset.editingId = String(productId);

    document.querySelector('#addProductModal .modal-header h2')?.replaceChildren(document.createTextNode('Edit Product'));
    const submitBtn = document.querySelector('#addProductForm .btn-primary');
    if (submitBtn) submitBtn.textContent = 'Update Product';

    updateModelRequirement();
    openModal('addProductModal');
  } catch (err) {
    console.error('Edit product error:', err);
    showNotification('error', 'Failed to load product details');
  }
}

// Handy opener for a fresh Add screen
function openAddProductFresh() {
  resetAddProductForm(); // clears dataset.editingId and previews
  document.querySelector('#addProductModal .modal-header h2')?.replaceChildren(document.createTextNode('Add New Product'));
  const btn = document.querySelector('#addProductForm .btn-primary');
  if (btn) btn.textContent = 'Add Product';
  openModal('addProductModal');
}

// ===== Availability (optional) =====
async function toggleAvailability(productId, newStatus) {
  try {
    const data = await fetchJSON('/RADS-TOOLING/backend/api/admin_products.php?action=toggle_availability', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: productId, is_available: newStatus })
    });
    showNotification('success', data.message || 'Status updated');
    closeModal('addProductModal');
    await loadProducts();
  } catch (err) {
    console.error('Toggle availability error:', err);
    showNotification('error', 'Failed to update availability');
  }
}

// ===== Delete =====
async function deleteProduct(productId) {
  if (!confirm('Are you sure you want to delete this product?')) return;
  try {
    const data = await fetchJSON('/RADS-TOOLING/backend/api/admin_products.php?action=delete', {
      method: 'DELETE',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: productId })
    });
    showNotification('success', 'Product deleted successfully');
    loadProducts();
  } catch (err) {
    console.error('Delete product error:', err);
    showNotification('error', 'Failed to delete product');
  }
}

async function toggleRelease(productId, status) {
  try {
    const res = await fetch('/RADS-TOOLING/backend/api/admin_products.php?action=toggle_release', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ product_id: Number(productId), status })
    });
    const data = await res.json();
    if (!data.success) throw new Error(data.message || 'Failed');
    showNotification('success', status === 'released' ? 'Product released' : 'Product set to draft');
    await loadProducts(); // refresh table
  } catch (e) {
    console.error(e);
    showNotification('error', e.message || 'Server error');
  }
}


// ===== Uploads =====
async function handleImagePreview(e) {
  const file = e.target.files?.[0];
  if (!file) return;

  const name = file.name.toLowerCase();
  const isImg = file.type.startsWith('image/') || /\.(png|jpg|jpeg|webp|gif)$/i.test(name);
  if (!isImg) {
    showNotification('error', 'Please upload an image file (png, jpg, jpeg, webp, gif).');
    e.target.value = ''; return;
  }

  const formData = new FormData();
  formData.append('image', file);

  try {
    const data = await fetchJSON('/RADS-TOOLING/backend/api/admin_products.php?action=upload_image', {
      method: 'POST',
      body: formData
    });
    const preview = document.getElementById('productImagePreview');
    if (preview) {
      preview.src = `/RADS-TOOLING/uploads/products/${data.data.filename}`;
      preview.style.display = 'block';
      preview.style.maxWidth = '240px';
      preview.style.maxHeight = '180px';
      preview.style.objectFit = 'cover';
      preview.style.marginTop = '12px';
      preview.dataset.filename = data.data.filename;
    }
    showNotification('success', 'Image uploaded successfully');
  } catch (error) {
    console.error('Upload image error:', error);
    showNotification('error', 'Failed to upload image');
  }
}

async function handleModelPreview(e) {
  const file = e.target.files?.[0];
  if (!file) return;

  if (!file.name.toLowerCase().endsWith('.glb')) {
    showNotification('error', 'Please upload a .glb file only.');
    e.target.value = ''; return;
  }
  if (file.size > MAX_GLB_MB * 1024 * 1024) {
    showNotification('error', `3D model is too large (max ${MAX_GLB_MB} MB).`);
    e.target.value = ''; return;
  }

  const formData = new FormData();
  formData.append('model', file);

  try {
    const data = await fetchJSON('/RADS-TOOLING/backend/api/admin_products.php?action=upload_model', {
      method: 'POST',
      body: formData
    });
    const preview = document.getElementById('productModelPreview');
    if (preview) {
      preview.textContent = data.data.filename;
      preview.style.display = 'block';
      preview.dataset.filename = data.data.filename;
    }
    showNotification('success', '3D model uploaded successfully');
  } catch (error) {
    console.error('Upload model error:', error);
    showNotification('error', 'Failed to upload 3D model');
  }
}

// ===== Customization modal (unchanged except for helpers) =====
async function openCustomizationModal(productId) {
  try {
    const d = await fetchJSON(`/RADS-TOOLING/backend/api/admin_products.php?action=view&id=${productId}`);
    currentProduct = d.data;
    populateCustomizationModal(currentProduct);
    openModal('manageCustomizationModal');
  } catch (err) {
    console.error('Load customization error:', err);
    showNotification('error', 'Failed to load customization options');
  }
}

function populateCustomizationModal(product) {
  document.getElementById('customProductName').textContent = product.name;
  document.getElementById('customProductId').value = product.id;

  // size_config may be array or object; normalize muna
  const asMap = {};
  if (Array.isArray(product.size_config)) {
    product.size_config.forEach(c => { asMap[c.dimension_type] = c; });
  } else if (product.size_config && typeof product.size_config === 'object') {
    Object.assign(asMap, product.size_config);
  }

  // HEIGHT
  if (asMap.height) {
    document.getElementById('heightMinCustom').value = asMap.height.min_value ?? 0;
    document.getElementById('heightMaxCustom').value = asMap.height.max_value ?? 0;
    document.getElementById('heightPriceCustom').value = asMap.height.price_per_unit ?? 0;
  }
  // WIDTH
  if (asMap.width) {
    document.getElementById('widthMinCustom').value = asMap.width.min_value ?? 0;
    document.getElementById('widthMaxCustom').value = asMap.width.max_value ?? 0;
    document.getElementById('widthPriceCustom').value = asMap.width.price_per_unit ?? 0;
  }
  // DEPTH
  if (asMap.depth) {
    document.getElementById('depthMinCustom').value = asMap.depth.min_value ?? 0;
    document.getElementById('depthMaxCustom').value = asMap.depth.max_value ?? 0;
    document.getElementById('depthPriceCustom').value = asMap.depth.price_per_unit ?? 0;
  }

  // Lists
  populateTexturesList(product.textures || []);
  populateColorsList(product.colors || []);
  populateHandlesList(product.handles || []);
}



function populateTexturesList(assigned) {
  const container = document.getElementById('texturesListContainer');
  const ids = assigned.map(t => parseInt(t.texture_id));
  container.innerHTML = allTextures.map(t => `
    <div class="customization-option-item">
      <input type="checkbox" id="texture_${t.id}" value="${t.id}" ${ids.includes(t.id) ? 'checked' : ''}>
      <label for="texture_${t.id}">
        <img src="/RADS-TOOLING/uploads/textures/${t.texture_image}" alt="${t.texture_name}"
             style="width:40px;height:40px;object-fit:cover;border-radius:4px;margin-right:8px;">
        ${t.texture_name} (₱${parseFloat(t.base_price).toFixed(2)})
      </label>
    </div>`).join('');
}

function populateColorsList(assigned) {
  const container = document.getElementById('colorsListContainer');
  const ids = assigned.map(c => parseInt(c.color_id));
  container.innerHTML = allColors.map(c => `
    <div class="customization-option-item">
      <input type="checkbox" id="color_${c.id}" value="${c.id}" ${ids.includes(c.id) ? 'checked' : ''}>
      <label for="color_${c.id}">
        <div style="width:30px;height:30px;background:${c.hex_value};border-radius:4px;display:inline-block;margin-right:8px;border:1px solid #ddd;"></div>
        ${c.color_name} (₱${parseFloat(c.base_price).toFixed(2)})
      </label>
    </div>`).join('');
}

function populateHandlesList(assigned) {
  const container = document.getElementById('handlesListContainer');
  const ids = assigned.map(h => parseInt(h.handle_id));
  container.innerHTML = allHandles.map(h => `
    <div class="customization-option-item">
      <input type="checkbox" id="handle_${h.id}" value="${h.id}" ${ids.includes(h.id) ? 'checked' : ''}>
      <label for="handle_${h.id}">
        <img src="/RADS-TOOLING/uploads/handles/${h.handle_image}" alt="${h.handle_name}"
             style="width:40px;height:40px;object-fit:cover;border-radius:4px;margin-right:8px;">
        ${h.handle_name} (₱${parseFloat(h.base_price).toFixed(2)})
      </label>
    </div>`).join('');
}

async function saveCustomizationOptions() {
  const product_id = document.getElementById('customProductId').value;

  // keep only min, max, price_per_unit (+ measurement_unit for context)
  const sizeConfig = {
    width: {
      min_value: +document.getElementById('widthMinCustom').value,
      max_value: +document.getElementById('widthMaxCustom').value,
      price_per_unit: +document.getElementById('widthPriceCustom').value,
      measurement_unit: currentProduct?.measurement_unit || 'cm'
    },
    height: {
      min_value: +document.getElementById('heightMinCustom').value,
      max_value: +document.getElementById('heightMaxCustom').value,
      price_per_unit: +document.getElementById('heightPriceCustom').value,
      measurement_unit: currentProduct?.measurement_unit || 'cm'
    },
    depth: {
      min_value: +document.getElementById('depthMinCustom').value,
      max_value: +document.getElementById('depthMaxCustom').value,
      price_per_unit: +document.getElementById('depthPriceCustom').value,
      measurement_unit: currentProduct?.measurement_unit || 'cm'
    }
  };

  const texture_ids = Array.from(document.querySelectorAll('#texturesListContainer input:checked')).map(cb => +cb.value);
  const color_ids = Array.from(document.querySelectorAll('#colorsListContainer   input:checked')).map(cb => +cb.value);
  const handle_ids = Array.from(document.querySelectorAll('#handlesListContainer  input:checked')).map(cb => +cb.value);

  try {
    await fetchJSON('/RADS-TOOLING/backend/api/admin_customization.php?action=update_size_config', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ product_id, size_config: sizeConfig })
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
