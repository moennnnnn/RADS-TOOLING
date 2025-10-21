// /RADS-TOOLING/assets/JS/customize.js
(() => {
    // ======= DOM =======
    const mediaBox = document.getElementById('mediaBox');
    const priceBox = document.getElementById('priceBox');
    const prodTitle = document.getElementById('prodTitle');

    const texList = document.getElementById('textures');
    const colList = document.getElementById('colors');
    const handleList = document.getElementById('handles');

    const stepPrev = document.getElementById('stepPrev');
    const stepNext = document.getElementById('stepNext');
    const stepLabel = document.getElementById('stepLabel');

    // sliders + number inputs
    const wSl = document.getElementById('wSlider');
    const hSl = document.getElementById('hSlider');
    const dSl = document.getElementById('dSlider');
    const wIn = document.getElementById('wInput');
    const hIn = document.getElementById('hInput');
    const dIn = document.getElementById('dInput');

    const unitSel = document.getElementById('unitSel');

    // ======= Constants / Paths =======
    const PID = Number(window.PID || 0);
    const MODEL_DIR = '/RADS-TOOLING/uploads/models/';
    const TEX_DIR = '/RADS-TOOLING/uploads/textures/';
    const HANDLE_DIR = '/RADS-TOOLING/uploads/handles/';

    const UNIT_TO_CM = { cm: 1, mm: 0.1, inch: 2.54, ft: 30.48, meter: 100 };

    const MATERIAL_MAP = {
        body: 'mat_body',
        door: 'mat_doors',
        inside: 'mat_inside',
        handle: 'mat_handle'
    };

    // --- precision helpers ---
    function decimalsOf(step) {
        const s = String(step);
        if (!s.includes('.')) return 0;
        return s.length - s.indexOf('.') - 1;
    }
    function roundToStep(v, step) {
        const p = Math.pow(10, decimalsOf(step));
        return Math.round((Number(v) / step) * p) / p * step;
    }
    function fmtUnit(v, unit, step = 1) {
        const d = Math.max(0, decimalsOf(step));
        const n = Number(v);
        if (!Number.isFinite(n)) return String(v);
        return n.toFixed(d).replace(/\.0+$/, '');
    }

    // ======= State =======
    let productData = null;
    let baseSize = { w: 80, h: 180, d: 45 }; // cm
    let activePart = 'body';
    let chosen = {
        size: { w: 80, h: 180, d: 45 }, // cm
        door: { mode: null, id: null },
        body: { mode: null, id: null },
        inside: { mode: null, id: null },
        handle: { id: null }
    };

    // ======= Steps UI =======
    const STEPS = ['sec-size', 'sec-textures', 'sec-colors', 'sec-handles'];
    let stepIndex = 0;

    function syncStepUI() {
        STEPS.forEach((id, i) => {
            const el = document.getElementById(id);
            if (el) el.hidden = (i !== stepIndex);
        });
        const labels = ['Step 1 · Size', 'Step 2 · Textures', 'Step 3 · Colors', 'Step 4 · Handles'];
        if (stepLabel) stepLabel.textContent = labels[stepIndex] || 'Customize';

        if (stepPrev) stepPrev.disabled = stepIndex === 0;
        if (stepNext) stepNext.disabled = stepIndex === STEPS.length - 1;
    }
    stepPrev?.addEventListener('click', () => { if (stepIndex > 0) { stepIndex--; syncStepUI(); } });
    stepNext?.addEventListener('click', () => { if (stepIndex < STEPS.length - 1) { stepIndex++; syncStepUI(); } });

    // ======= Boot =======
    document.addEventListener('DOMContentLoaded', init);

    async function init() {
        if (!PID) return;
        productData = await loadProduct(PID);

        prodTitle && (prodTitle.textContent = productData.title || '—');

        // Viewer
        renderModel(productData.model_url);
        await waitForModelViewer();

        // Size
        applySizeConfs(productData.size_confs);
        initSizeReadouts();
        applyScaleFromControls();
        refreshPrice();

        // Unit switching
        unitSel?.addEventListener('change', () => {
            syncSlidersToUnit();
            applyScaleFromControls();
            refreshPrice();
            updateReadouts();
            updateUnitLabels();
            buildSizeGuide();
        });

        // Pair bindings (slider <-> input)
        bindPair(wSl, wIn);
        bindPair(hSl, hIn);
        bindPair(dSl, dIn);

        // Tabs
        bindPartTabs('#sec-textures .part-tabs');
        bindPartTabs('#sec-colors .part-tabs');

        // Size guide
        document.getElementById('btnSizeGuide')?.addEventListener('click', openSizeGuide);

        syncStepUI();
        updateUnitLabels();
        buildSizeGuide();
    }

    // ======= Data Loading =======
    async function loadProduct(id) {
        const url = `/RADS-TOOLING/backend/api/admin_products.php?action=view&id=${id}`;
        const r = await fetch(url, { credentials: 'same-origin' });
        if (!r.ok) throw new Error(`admin_products.php returned ${r.status}`);
        const js = await r.json();
        return await normalizeProduct(js);
    }

    async function normalizeProduct(js) {
        const d = (js?.data ?? js) || {};
        // model
        let model_url = '';
        if (typeof d.model_3d === 'string' && d.model_3d.trim()) {
            model_url = d.model_3d.startsWith('/RADS-TOOLING/') ? d.model_3d : (MODEL_DIR + d.model_3d.replace(/^\/+/, ''));
        } else if (typeof d.model_url === 'string' && d.model_url.trim()) {
            model_url = d.model_url.startsWith('/RADS-TOOLING/') ? d.model_url : (MODEL_DIR + d.model_url.replace(/^\/+/, ''));
        } else if (typeof d.model_path === 'string' && d.model_path.trim()) {
            model_url = MODEL_DIR + d.model_path.replace(/^\/+/, '');
        } else if (typeof d.model === 'string' && d.model.trim()) {
            model_url = MODEL_DIR + d.model.replace(/^\/+/, '');
        }
        if (model_url && !(await urlExists(model_url))) model_url = '';

        // size_confs
        let size_confs = Array.isArray(d.size_confs) ? d.size_confs : [];
        if (!size_confs.length && Array.isArray(d.size_config)) {
            size_confs = d.size_config.map(sc => ({
                dimension: (sc.dimension_type || '').toLowerCase(), // width|height|depth
                min: Number(sc.min_value || 0),
                max: Number(sc.max_value || 0),
                default: Number(sc.default_value || 0),
                step: Number(sc.step_value || 1),
                price_per_unit: Number(sc.price_per_unit || 0), // per sc.measurement_unit
                unit: sc.measurement_unit || 'cm',
                price_block_cm: Number(sc.price_block_cm || 0),     // e.g. 10 (cm per block)
                price_per_block: Number(sc.price_per_block || 0),   // e.g. 200 (₱ per block)
            }));
        }

        const allowed = d.allowed || {
            door: { textures: d.door_textures || [], colors: d.door_colors || [] },
            body: { textures: d.body_textures || [], colors: d.body_colors || [] },
            inside: { textures: d.inside_textures || [], colors: d.inside_colors || [] },
            handle: { handles: d.handles || [] }
        };

        const pricing = d.pricing || {
            base_price: parsePeso(d.base_price ?? d.price ?? 0),
            per_cm: { w: Number(d.price_per_cm_w || 0), h: Number(d.price_per_cm_h || 0), d: Number(d.price_per_cm_d || 0) },
            textures: d.texture_prices || {},
            colors: d.color_prices || {},
            handles: d.handle_prices || {}
        };

        return {
            title: d.title || d.name || d.product_name || 'Cabinet',
            model_url,
            size_confs,
            allowed,
            pricing
        };
    }

    async function urlExists(url) {
        try { const r = await fetch(url, { method: 'GET', cache: 'no-store' }); return r.ok; }
        catch { return false; }
    }
    function parsePeso(v) {
        if (typeof v === 'number') return v;
        if (typeof v !== 'string') return Number(v || 0);
        return Number(v.replace(/[₱,\s]/g, '')) || 0;
    }

    // ======= Viewer =======
    function renderModel(modelURL) {
        mediaBox.innerHTML = `
      <model-viewer id="mv"
        style="width:100%;height:100%;display:block;--poster-color:transparent;background:#f7f7f7"
        src="${modelURL}"
        camera-controls auto-rotate bounds="tight" reveal="auto"
        environment-image="neutral" shadow-intensity="1" exposure="1">
      </model-viewer>`;
    }
    function getMV() { return document.getElementById('mv'); }
    function waitForModelViewer() {
        return new Promise(res => {
            const mv = getMV();
            if (!mv) return res(null);
            if (mv?.model) return res(mv);
            mv.addEventListener('load', () => res(mv), { once: true });
        });
    }
    function getMaterial(mv, name) {
        const mats = mv?.model?.materials || [];
        return mats.find(m => m.name === name) || null;
    }
    function highlightPart(partKey) {
        const mv = getMV();
        if (!mv?.model?.materials) return;
        mv.model.materials.forEach(m => m.setEmissiveFactor([0, 0, 0]));
        const mat = getMaterial(mv, MATERIAL_MAP[partKey]);
        if (mat) mat.setEmissiveFactor([0.10, 0.25, 0.6]);
    }

    // ======= Size / Scale =======
    const DEF_DIM = { min: 40, max: 300, default: 80, step: 1, price_per_unit: 0, unit: 'cm' };
    function dimCfg(dim) {
        const it = (productData.size_confs || []).find(x => x.dimension === dim);
        return it ? { ...DEF_DIM, ...it } : { ...DEF_DIM };
    }

    function applySizeConfs() {
        const w = dimCfg('width'), h = dimCfg('height'), d = dimCfg('depth');

        // base defaults in cm
        baseSize = {
            w: w.default * (UNIT_TO_CM[w.unit] || 1),
            h: h.default * (UNIT_TO_CM[h.unit] || 1),
            d: d.default * (UNIT_TO_CM[d.unit] || 1)
        };

        // initial chosen = mins in cm
        chosen.size = {
            w: w.min * (UNIT_TO_CM[w.unit] || 1),
            h: h.min * (UNIT_TO_CM[h.unit] || 1),
            d: d.min * (UNIT_TO_CM[d.unit] || 1)
        };

        syncSlidersToUnit();
        updateReadouts();
    }

    function setPairFromCfg(sl, inp, cfg, targetUnit, valueInTarget) {
        if (!sl || !inp) return;
        const k = UNIT_TO_CM[cfg.unit] || 1;   // cfg.unit -> cm
        const t = UNIT_TO_CM[targetUnit] || 1; // cm -> target
        const toTarget = v => (v * k) / t;

        const min = toTarget(cfg.min);
        const max = toTarget(cfg.max);
        const step = toTarget(cfg.step);

        sl.min = String(min); sl.max = String(max); sl.step = String(step);
        inp.min = String(min); inp.max = String(max); inp.step = String(step);

        const v = Math.min(max, Math.max(min, roundToStep(valueInTarget, step)));
        sl.value = String(v);
        inp.value = fmtUnit(v, targetUnit, step);
    }

    function bindPair(sl, inp) {
        if (!sl || !inp) return;

        const mirror = (src) => {
            if (src === sl) {
                inp.value = fmtUnit(sl.value, unitSel?.value || 'cm', Number(inp.step) || 1);
            } else {
                const v = Number(inp.value);
                if (Number.isFinite(v)) sl.value = String(v);
            }
            applyScaleFromControls();
            refreshPrice();
            updateReadouts();
        };

        const clampAndSnap = () => {
            const min = Number(inp.min), max = Number(inp.max), step = Number(inp.step) || 1;
            let v = Number(inp.value);
            if (!Number.isFinite(v)) v = min;
            v = Math.min(max, Math.max(min, roundToStep(v, step)));
            sl.value = String(v);
            inp.value = fmtUnit(v, unitSel?.value || 'cm', step);
            applyScaleFromControls();
            refreshPrice();
            updateReadouts();
        };

        sl.addEventListener('input', () => mirror(sl));
        inp.addEventListener('input', () => mirror(inp));
        inp.addEventListener('change', clampAndSnap);
        inp.addEventListener('blur', clampAndSnap);
    }

    function syncSlidersToUnit() {
        const unit = unitSel?.value || 'cm';
        const t = UNIT_TO_CM[unit] || 1;
        setPairFromCfg(wSl, wIn, dimCfg('width'), unit, chosen.size.w / t);
        setPairFromCfg(hSl, hIn, dimCfg('height'), unit, chosen.size.h / t);
        setPairFromCfg(dSl, dIn, dimCfg('depth'), unit, chosen.size.d / t);
    }

    function applyScaleFromControls() {
        const unit = unitSel?.value || 'cm';
        const t = UNIT_TO_CM[unit] || 1;

        const w_cm = Number(wSl?.value || 0) * t;
        const h_cm = Number(hSl?.value || 0) * t;
        const d_cm = Number(dSl?.value || 0) * t;
        chosen.size = { w: w_cm, h: h_cm, d: d_cm };

        const mv = getMV();
        if (mv?.model) {
            const root = (mv.model && mv.model.scene) ? mv.model.scene : mv.model;
            const sx = w_cm / baseSize.w, sy = h_cm / baseSize.h, sz = d_cm / baseSize.d;
            if (root?.scale?.set) root.scale.set(sx, sy, sz);
        }
    }

    // ======= Readouts / Unit labels =======
    function initSizeReadouts() {
        makeReadout(wSl, 'wVal');
        makeReadout(hSl, 'hVal');
        makeReadout(dSl, 'dVal');
        updateReadouts();
    }
    function makeReadout(sl, id) {
        if (!sl) return;
        const span = document.createElement('span');
        span.id = id; span.className = 'cz-val';
        span.style.marginLeft = '8px'; span.style.fontWeight = '700';
        sl.parentElement?.appendChild(span);
    }
    function updateReadouts() {
        const unit = unitSel?.value || 'cm';
        const wOut = document.getElementById('wVal');
        const hOut = document.getElementById('hVal');
        const dOut = document.getElementById('dVal');

        const wStep = Number(wIn?.step || 1);
        const hStep = Number(hIn?.step || 1);
        const dStep = Number(dIn?.step || 1);

        if (wOut && wSl) wOut.textContent = `${fmtUnit(wSl.value, unit, wStep)} ${unit}`;
        if (hOut && hSl) hOut.textContent = `${fmtUnit(hSl.value, unit, hStep)} ${unit}`;
        if (dOut && dSl) dOut.textContent = `${fmtUnit(dSl.value, unit, dStep)} ${unit}`;
    }
    function updateUnitLabels() {
        const unit = unitSel?.value || 'cm';
        document.querySelectorAll('.cz-unit-label').forEach(sp => sp.textContent = unit);
    }

    // ======= Tabs / Active Part =======
    function bindPartTabs(sel) {
        const tabs = document.querySelector(sel);
        if (!tabs) return;
        tabs.addEventListener('click', (e) => {
            const btn = e.target.closest('button[data-part]');
            if (!btn) return;
            tabs.querySelectorAll('button').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            setActivePart(btn.getAttribute('data-part'));
        });
    }
    function setActivePart(k) { activePart = k; highlightPart(k); }

    // ======= Apply Texture / Color =======
    async function applyTextureToPart(partKey, url, id) {
        const mv = getMV(); if (!mv) return;
        try {
            const tex = await mv.createTexture(url);
            const mat = getMaterial(mv, MATERIAL_MAP[partKey]); if (!mat) return;
            mat.pbrMetallicRoughness.setBaseColorFactor([1, 1, 1, 1]);
            mat.pbrMetallicRoughness.baseColorTexture.setTexture(tex);
            setChosen(partKey, { mode: 'texture', id });
            refreshPrice();
        } catch (err) { console.error('Texture apply failed:', err); }
    }
    function applyColorToPart(partKey, hex, id) {
        const mv = getMV(); if (!mv) return;
        const mat = getMaterial(mv, MATERIAL_MAP[partKey]); if (!mat) return;
        mat.pbrMetallicRoughness.baseColorTexture.setTexture(null);
        const [r, g, b, a] = hexToRGBA01(hex);
        mat.pbrMetallicRoughness.setBaseColorFactor([r, g, b, a]);
        setChosen(partKey, { mode: 'color', id });
        refreshPrice();
    }
    function setChosen(partKey, data) {
        if (partKey === 'handle') { chosen.handle.id = data?.id ?? null; return; }
        chosen[partKey].mode = data?.mode ?? null;
        chosen[partKey].id = data?.id ?? null;
    }
    function hexToRGBA01(hex) {
        const m = hex.replace('#', ''); const v = parseInt(m, 16);
        const r = (v >> 16) & 255, g = (v >> 8) & 255, b = v & 255; return [r / 255, g / 255, b / 255, 1];
    }

    // ======= Build Options (Allowed) =======
    function rebuildTextureOptions() {
        if (!texList) return; texList.innerHTML = '';
        const allow = productData?.allowed?.[getActiveTabPart('#sec-textures .part-tabs')] || { textures: [] };
        (allow.textures || []).forEach(t => {
            const btn = document.createElement('button');
            btn.className = 'cz-item';
            btn.innerHTML = `
        <div class="cz-swatch"><img src="${TEX_DIR + t.file}" alt="${t.name || 'texture'}"></div>
        <div class="cz-item-name">${t.name || 'Texture'}</div>
        ${priceBadge(getSurcharge('textures', t.id))}
      `;
            btn.addEventListener('click', () => { applyTextureToPart(activePart, TEX_DIR + t.file, t.id); selectExclusive(texList, btn); });
            texList.appendChild(btn);
        });
    }
    function rebuildColorOptions() {
        if (!colList) return; colList.innerHTML = '';
        const allow = productData?.allowed?.[getActiveTabPart('#sec-colors .part-tabs')] || { colors: [] };
        (allow.colors || []).forEach(c => {
            const btn = document.createElement('button');
            btn.className = 'cz-item';
            btn.innerHTML = `
        <div class="cz-swatch" style="background:${c.hex || '#ccc'}"></div>
        <div class="cz-item-name">${c.name || (c.hex || 'Color')}</div>
        ${priceBadge(getSurcharge('colors', c.id))}
      `;
            btn.addEventListener('click', () => { applyColorToPart(activePart, c.hex || '#cccccc', c.id); selectExclusive(colList, btn); });
            colList.appendChild(btn);
        });
    }
    function rebuildHandleOptions() {
        if (!handleList) return; handleList.innerHTML = '';
        const allow = productData?.allowed?.handle || { handles: [] };
        (allow.handles || []).forEach(h => {
            const btn = document.createElement('button');
            btn.className = 'cz-item';
            btn.innerHTML = `
        <div class="cz-swatch"><img src="${HANDLE_DIR + (h.preview || h.file || '')}" alt="${h.name || 'Handle'}"></div>
        <div class="cz-item-name">${h.name || 'Handle'}</div>
        ${priceBadge(getSurcharge('handles', h.id))}
      `;
            btn.addEventListener('click', () => { chosen.handle.id = h.id; selectExclusive(handleList, btn); refreshPrice(); });
            handleList.appendChild(btn);
        });
    }
    function selectExclusive(listEl, btn) {
        listEl.querySelectorAll('.cz-item').forEach(x => x.classList.remove('is-active'));
        btn.classList.add('is-active');
    }
    function getActiveTabPart(selector) {
        const tabs = document.querySelector(selector);
        const active = tabs?.querySelector('button.active')?.getAttribute('data-part');
        return active || 'door';
    }
    function priceBadge(surcharge) {
        if (!Number.isFinite(surcharge) || surcharge === 0) return '';
        const sign = surcharge > 0 ? '+' : '–';
        const abs = Math.abs(surcharge);
        return `<div class="cz-price-badge">${sign} ₱${fmt(abs)}</div>`;
    }

    // ======= Pricing =======
    function refreshPrice() {
        const p = computePrice();
        if (priceBox) priceBox.textContent = '₱ ' + fmt(p);
    }

    function computePrice() {
        let total = Number(productData?.pricing?.base_price || 0);

        // Baseline is ADMIN MIN (not defaults)
        const wCfg = dimCfg('width'), hCfg = dimCfg('height'), dCfg = dimCfg('depth');
        const baseW = wCfg.min * (UNIT_TO_CM[wCfg.unit] || 1);
        const baseH = hCfg.min * (UNIT_TO_CM[hCfg.unit] || 1);
        const baseD = dCfg.min * (UNIT_TO_CM[dCfg.unit] || 1);

        total += dimSurcharge(chosen.size.w, baseW, wCfg);
        total += dimSurcharge(chosen.size.h, baseH, hCfg);
        total += dimSurcharge(chosen.size.d, baseD, dCfg);

        // option surcharges
        ['door', 'body', 'inside'].forEach(part => {
            const mode = chosen[part].mode, id = chosen[part].id;
            if (!id) return;
            if (mode === 'texture') total += getSurcharge('textures', id);
            else if (mode === 'color') total += getSurcharge('colors', id);
        });
        if (chosen.handle.id) total += getSurcharge('handles', chosen.handle.id);

        return Math.max(0, Math.round(total));
    }

    // Prefer block pricing if provided; else per-unit
    function dimSurcharge(sizeCm, baseCm, cfg) {
        const over = Math.max(0, sizeCm - baseCm);
        if (over <= 0) return 0;

        const blockCm = Number(cfg.price_block_cm || 0);
        const pricePerBlock = Number(cfg.price_per_block || 0);

        if (blockCm > 0 && pricePerBlock > 0) {
            // “every 10 cm add ₱X” → charge per full block
            const blocks = Math.floor(over / blockCm);
            return blocks * pricePerBlock;
        }

        // fallback: per-unit (cfg.unit) → convert to ₱/cm
        const perUnit = Number(cfg.price_per_unit || 0);
        const perCm = perUnit / (UNIT_TO_CM[cfg.unit] || 1);
        return over * perCm;
    }

    function getSurcharge(kind, id) {
        const map = (productData?.pricing?.[kind]) || {};
        const key = String(id);
        const val = map[key] ?? map[id] ?? 0;
        return Number(val || 0);
    }
    function fmt(n) { return Number(n || 0).toLocaleString('en-PH', { maximumFractionDigits: 0 }); }

    // ======= Size Guide (modal) =======
    function openSizeGuide() {
        const dlg = ensureSizeGuideHost();
        buildSizeGuide();
        dlg.showModal?.();
    }
    function ensureSizeGuideHost() {
        let dlg = document.getElementById('sizeGuide');
        if (dlg) return dlg;
        dlg = document.createElement('dialog');
        dlg.id = 'sizeGuide';
        dlg.style.padding = '0';
        dlg.style.border = '0';
        dlg.style.borderRadius = '12px';
        dlg.style.maxWidth = '560px';
        dlg.style.margin = 'auto';
        dlg.style.width = '92vw';
        dlg.style.boxShadow = '0 12px 32px rgba(15,23,42,.18)';
        dlg.innerHTML = `
      <div style="padding:18px 18px 8px;border-bottom:1px solid #eef2f7;font-weight:700">Size guide</div>
      <div id="sizeGuideBody" style="padding:16px"></div>
      <div style="display:flex;justify-content:flex-end;padding:12px 16px;border-top:1px solid #eef2f7">
        <button id="sizeGuideClose" class="btn">Close</button>
      </div>`;
        document.body.appendChild(dlg);
        dlg.querySelector('#sizeGuideClose').addEventListener('click', () => dlg.close?.());
        return dlg;
    }
    function buildSizeGuide() {
        const body = document.getElementById('sizeGuideBody'); if (!body) return;
        const unit = unitSel?.value || 'cm';
        const t = UNIT_TO_CM[unit] || 1;

        const dims = ['width', 'height', 'depth'].map(dim => {
            const c = dimCfg(dim);
            const k = UNIT_TO_CM[c.unit] || 1;
            const toTarget = v => (v * k) / t;
            return {
                dim,
                range: `${fmtUnit(toTarget(c.min), unit, toTarget(c.step))} – ${fmtUnit(toTarget(c.max), unit, toTarget(c.step))} ${unit}`,
                step: `${fmtUnit(toTarget(c.step), unit, toTarget(c.step))} ${unit}`,
                price:
                    (Number(c.price_block_cm) > 0 && Number(c.price_per_block) > 0)
                        ? `₱ ${fmt(c.price_per_block)} / every ${fmtUnit((UNIT_TO_CM[c.unit] || 1) ? (c.price_block_cm / (UNIT_TO_CM['cm'] || 1)) : c.price_block_cm, 'cm', c.step)} cm`
                        : `₱ ${fmt(c.price_per_unit || 0)} / ${c.unit}`
            };
        });

        // current value in cm -> conversions
        const cur = {
            width_cm: Number(wSl?.value || 0) * (UNIT_TO_CM[unit] || 1),
            height_cm: Number(hSl?.value || 0) * (UNIT_TO_CM[unit] || 1),
            depth_cm: Number(dSl?.value || 0) * (UNIT_TO_CM[unit] || 1)
        };
        const U = ['mm', 'cm', 'inch', 'ft', 'meter'];
        const conv = (cm, u) => {
            const to = { mm: 10, cm: 1, inch: 1 / 2.54, ft: 1 / 30.48, meter: 1 / 100 }[u];
            return fmtUnit(cm * to, u, 0.01);
        };

        const rowsMain = dims.map(d => `
      <tr>
        <td style="padding:6px 10px;font-weight:600;text-transform:capitalize">${d.dim}</td>
        <td style="padding:6px 10px">${d.range}</td>
        <td style="padding:6px 10px">${d.step}</td>
        <td style="padding:6px 10px">${d.price}</td>
      </tr>`).join('');

        const rowsConv = `
      <tr><th></th>${U.map(u => `<th>${u}</th>`).join('')}</tr>
      <tr>
        <td style="text-transform:capitalize">width</td>
        ${U.map(u => `<td>${conv(cur.width_cm, u)}</td>`).join('')}
      </tr>
      <tr>
        <td style="text-transform:capitalize">height</td>
        ${U.map(u => `<td>${conv(cur.height_cm, u)}</td>`).join('')}
      </tr>
      <tr>
        <td style="text-transform:capitalize">depth</td>
        ${U.map(u => `<td>${conv(cur.depth_cm, u)}</td>`).join('')}
      </tr>
    `;

        body.innerHTML = `
      <div style="color:#6b7280;font-size:13px;margin-bottom:8px">
        Ranges and increments are controlled by the admin for each product.
      </div>

      <table style="width:100%;border-collapse:collapse;margin-bottom:14px">
        <thead>
          <tr style="background:#f8fafc">
            <th style="text-align:left;padding:8px 10px">Dimension</th>
            <th style="text-align:left;padding:8px 10px">Range</th>
            <th style="text-align:left;padding:8px 10px">Increment</th>
            <th style="text-align:left;padding:8px 10px">Price per unit</th>
          </tr>
        </thead>
        <tbody>${rowsMain}</tbody>
      </table>

      <div style="font-weight:600;margin:10px 0 6px">Current selection (converted)</div>
      <table style="width:100%;border-collapse:collapse">
        <thead>
          <tr style="background:#f8fafc">
            <th style="text-align:left;padding:8px 10px"></th>
            ${U.map(u => `<th style="text-align:left;padding:8px 10px">${u}</th>`).join('')}
          </tr>
        </thead>
        <tbody>
          <tr>
            <td style="padding:6px 10px;text-transform:capitalize">width</td>
            ${U.map(u => `<td style="padding:6px 10px">${conv(cur.width_cm, u)}</td>`).join('')}
          </tr>
          <tr>
            <td style="padding:6px 10px;text-transform:capitalize">height</td>
            ${U.map(u => `<td style="padding:6px 10px">${conv(cur.height_cm, u)}</td>`).join('')}
          </tr>
          <tr>
            <td style="padding:6px 10px;text-transform:capitalize">depth</td>
            ${U.map(u => `<td style="padding:6px 10px">${conv(cur.depth_cm, u)}</td>`).join('')}
          </tr>
        </tbody>
      </table>
    `;
    }

    // ======= Helpers =======
    function getActiveTabPart(selector) {
        const tabs = document.querySelector(selector);
        return tabs?.querySelector('button.active')?.getAttribute('data-part') || 'door';
    }

})();
