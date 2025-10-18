(() => {
    const API_TRY = [
        '/RADS-TOOLING/backend/api/admin_products.php?action=view&id='
    ];

    // Public asset paths
    const MODEL_DIR = '/RADS-TOOLING/uploads/models/';
    const TEX_DIR = '/RADS-TOOLING/uploads/textures/';
    const HANDLE_DIR = '/RADS-TOOLING/uploads/handles/';

    let PID = Number(window.PID || 0);
    if (!PID) {
        const qs = new URLSearchParams(location.search);
        PID = Number(qs.get('pid') || qs.get('id') || 0);
    }
    if (!PID) {
        // redirect na lang kesa alert + return
        location.href = '/RADS-TOOLING/backend/customer/products.php';
        throw new Error('No product id');
    }


    // DOM
    const mediaBox = document.getElementById('mediaBox');
    const prodTitle = document.getElementById('prodTitle');
    const priceBox = document.getElementById('priceBox');
    const breakdown = document.getElementById('breakdown');
    const stepPrev = document.getElementById('stepPrev');
    const stepNext = document.getElementById('stepNext');
    const stepLabel = document.getElementById('stepLabel');

    const sections = {
        size: document.getElementById('sec-size'),
        textures: document.getElementById('sec-textures'),
        colors: document.getElementById('sec-colors'),
        handles: document.getElementById('sec-handles')
    };

    const texturesUL = document.getElementById('textures');
    const colorsUL = document.getElementById('colors');
    const handlesUL = document.getElementById('handles');

    const wSlider = document.getElementById('w');
    const hSlider = document.getElementById('h');
    const dSlider = document.getElementById('d');
    const doorsSl = document.getElementById('doors');
    const layersSl = document.getElementById('layers');
    const toCartBtn = document.getElementById('toCart');

    // State
    let product = null;
    let mv = null;     // <model-viewer>
    let mats = {};     // {door, body, inside}
    let currentPart = 'door';
    const chosen = {
        textures: { door: null, body: null, inside: null },
        colors: { door: null, body: null, inside: null },
        handle: null,
        size: { w: null, h: null, d: null, doors: null, layers: null }
    };

    // STEP LOGIC
    const steps = ['size', 'textures', 'colors', 'handles'];
    const labels = { size: 'Step 1 · Size', textures: 'Step 2 · Texture', colors: 'Step 3 · Color', handles: 'Step 4 · Handles' };
    let stepIndex = 0;

    function showStep(i) {
        stepIndex = Math.max(0, Math.min(steps.length - 1, i));
        const current = steps[stepIndex];
        Object.entries(sections).forEach(([k, el]) => el.hidden = (k !== current));
        stepLabel.textContent = labels[current];
        stepPrev.disabled = (stepIndex === 0);
        stepNext.disabled = (stepIndex === steps.length - 1);
    }
    if (stepPrev) stepPrev.addEventListener('click', () => showStep(stepIndex - 1));
    if (stepNext) stepNext.addEventListener('click', () => showStep(stepIndex + 1));

    // keyboard ← → for step
    window.addEventListener('keydown', (e) => {
        if (['ArrowLeft', 'ArrowRight'].includes(e.key)) {
            e.preventDefault();
            if (e.key === 'ArrowLeft') showStep(stepIndex - 1);
            if (e.key === 'ArrowRight') showStep(stepIndex + 1);
        }
    });

    // Helpers for model-viewer material editing
    function hexToRgb01(hex) {
        const h = hex.replace('#', '');
        const n = parseInt(h.length === 3 ? h.split('').map(x => x + x).join('') : h, 16);
        return { r: ((n >> 16) & 255) / 255, g: ((n >> 8) & 255) / 255, b: (n & 255) / 255 };
    }
    async function setColor(material, hex) {
        if (!material) return;
        const c = hexToRgb01(hex);
        material.pbrMetallicRoughness.setBaseColorFactor([c.r, c.g, c.b, 1]);
        material.pbrMetallicRoughness.setMetallicFactor(0.0);
        material.pbrMetallicRoughness.setRoughnessFactor(0.45);
        material.pbrMetallicRoughness.baseColorTexture.setTexture(null);
    }
    async function setTexture(material, fileUrl) {
        if (!material) return;
        const tex = await mv.createTexture(fileUrl);
        material.pbrMetallicRoughness.baseColorTexture.setTexture(tex);
        material.pbrMetallicRoughness.setMetallicFactor(0.0);
        material.pbrMetallicRoughness.setRoughnessFactor(0.45);
    }
    const matOf = (p) => p === 'door' ? mats.door : (p === 'body' ? mats.body : mats.inside);

    // Part tabs (we have two identical bars in textures & colors)
    function bindPartbars() {
        document.querySelectorAll('.part-tabs').forEach(bar => {
            bar.addEventListener('click', (e) => {
                const b = e.target.closest('button[data-part]'); if (!b) return;
                bar.querySelectorAll('button').forEach(x => x.classList.remove('active'));
                b.classList.add('active'); currentPart = b.dataset.part;
            });
        });
    }

    // UI builders
    function markActive(container, el) { container.querySelectorAll('.cz-opt').forEach(x => x.classList.remove('active')); el.classList.add('active'); }

    function buildTextures(list) {
        texturesUL.innerHTML = '';
        list.forEach(t => {
            const li = document.createElement('button');
            li.className = 'cz-opt';
            li.innerHTML = `<img src="${TEX_DIR + t.texture_image}" alt="${t.texture_name}"><span>${t.texture_name}</span>`;
            li.addEventListener('click', async () => {
                chosen.textures[currentPart] = t.id; chosen.colors[currentPart] = null;
                markActive(texturesUL, li);
                await setTexture(matOf(currentPart), TEX_DIR + t.texture_image);
                recalcPrice();
            });
            texturesUL.appendChild(li);
        });
        sections.textures.hidden = list.length === 0;
    }
    function buildColors(list) {
        colorsUL.innerHTML = '';
        list.forEach(c => {
            const li = document.createElement('button');
            li.className = 'cz-opt';
            li.innerHTML = `<i class="swatch" style="background:${c.hex_value}"></i><span>${c.color_name}</span>`;
            li.addEventListener('click', async () => {
                chosen.colors[currentPart] = c.id; chosen.textures[currentPart] = null;
                markActive(colorsUL, li);
                await setColor(matOf(currentPart), c.hex_value);
                recalcPrice();
            });
            colorsUL.appendChild(li);
        });
        sections.colors.hidden = list.length === 0;
    }
    function buildHandles(list) {
        handlesUL.innerHTML = '';
        list.forEach(h => {
            const li = document.createElement('button');
            li.className = 'cz-opt';
            li.innerHTML = `<img src="${HANDLE_DIR + h.handle_image}" alt="${h.handle_name}"><span>${h.handle_name}</span>`;
            li.addEventListener('click', () => {
                chosen.handle = h.id; markActive(handlesUL, li); recalcPrice();
            });
            handlesUL.appendChild(li);
        });
        sections.handles.hidden = list.length === 0;
    }

    // Size sliders (Min/Max/PPU only)
    function initSizeUI(cfg) {
        const w = cfg.width, h = cfg.height, d = cfg.depth;
        if (w && wSlider) { wSlider.min = w.min_value || 0; wSlider.max = w.max_value || 0; wSlider.value = w.min_value || 0; wSlider.dataset.ppu = Number(w.price_per_unit || 0); }
        if (h && hSlider) { hSlider.min = h.min_value || 0; hSlider.max = h.max_value || 0; hSlider.value = h.min_value || 0; hSlider.dataset.ppu = Number(h.price_per_unit || 0); }
        if (d && dSlider) { dSlider.min = d.min_value || 0; dSlider.max = d.max_value || 0; dSlider.value = d.min_value || 0; dSlider.dataset.ppu = Number(d.price_per_unit || 0); }

        doorsSl && (doorsSl.value = doorsSl.min || 0);
        layersSl && (layersSl.value = layersSl.min || 0);

        [wSlider, hSlider, dSlider, doorsSl, layersSl].forEach(sl => {
            if (!sl) return;
            sl.addEventListener('input', () => {
                chosen.size = {
                    w: +wSlider.value || 0, h: +hSlider.value || 0, d: +dSlider.value || 0,
                    doors: +doorsSl.value || 0, layers: +layersSl.value || 0
                };
                recalcPrice();
            });
        });

        sections.size.hidden = !(w || h || d);
    }

    function recalcPrice() {
        if (!product) return;
        let total = Number(product.price) || 0;
        const lines = [];

        function addSize(label, sl) {
            if (!sl) return;
            const base = Number(sl.min || 0), val = Number(sl.value || base), ppu = Number(sl.dataset.ppu || 0);
            const diff = Math.max(0, val - base), add = diff * ppu;
            if (add > 0) { total += add; lines.push(`${label}: +₱${add.toFixed(2)} (${diff} × ₱${ppu.toFixed(2)})`); }
        }
        addSize('Width', wSlider); addSize('Height', hSlider); addSize('Depth', dSlider);

        function addOpt(group, id, label, nameKey) {
            if (!id) return;
            const arr = product[group] || [];
            const f = arr.find(x => x.id === id);
            if (f && Number(f.base_price) > 0) {
                const add = Number(f.base_price); total += add;
                lines.push(`${label}: +₱${add.toFixed(2)} (${f[nameKey]})`);
            }
        }
        addOpt('textures', chosen.textures.door, 'Door Texture', 'texture_name');
        addOpt('textures', chosen.textures.body, 'Body Texture', 'texture_name');
        addOpt('textures', chosen.textures.inside, 'Inside Texture', 'texture_name');
        addOpt('colors', chosen.colors.door, 'Door Color', 'color_name');
        addOpt('colors', chosen.colors.body, 'Body Color', 'color_name');
        addOpt('colors', chosen.colors.inside, 'Inside Color', 'color_name');
        addOpt('handles', chosen.handle, 'Handle', 'handle_name');

        priceBox.textContent = `₱ ${total.toFixed(2)}`;
        breakdown.innerHTML = lines.length ? `<ul><li>${lines.join('</li><li>')}</li></ul>` : '';
    }

    function resolveAsset(base, name) {
        if (!name) return null;
        // absolute URL
        if (/^https?:\/\//i.test(name)) return name;
        // absolute path already
        if (name.startsWith('/')) {
            // kung '/uploads/...' gawin '/RADS-TOOLING/uploads/...'
            if (name.startsWith('/uploads/')) return '/RADS-TOOLING' + name;
            return name;
        }
        // may dalang 'uploads/...' galing DB
        if (name.includes('/uploads/')) {
            return '/RADS-TOOLING/' + name.replace(/^\/+/, '');
        }
        // plain filename -> idikit sa base
        return base.replace(/\/+$/, '') + '/' + name.replace(/^\/+/, '');
    }

    async function headOK(url) {
        try {
            const r = await fetch(url, { method: 'HEAD' });
            return r.ok;
        } catch { return false; }
    }


    async function init() {
        // 1) Load product (try multiple endpoints)
        let js = null, lastStatus = 0, lastRaw = '';
        for (const base of API_TRY) {
            const res = await fetch(base + PID, { credentials: 'same-origin' });
            lastStatus = res.status;
            lastRaw = await res.text();
            try { js = JSON.parse(lastRaw); } catch { js = null; }
            if (res.ok && js && (js.success !== false) && (js.data || js.product)) {
                // some endpoints use {data:...}, others {product:...}
                product = js.data || js.product;
                console.log('Using endpoint:', base);
                break;
            }
        }

        // Hard fail if nothing worked
        if (!product) {
            console.error('API RAW:', lastRaw);
            throw new Error(`Failed to load product (status ${lastStatus})`);
        }

        // 2) Proceed now that product is set
        prodTitle.textContent = product.name || 'Customize';
        priceBox.textContent = `₱ ${(Number(product.price) || 0).toFixed(2)}`;

        const modelFile =
            product.model_3d || product.model || product.model_file ||
            product.model_path || product.glb || product.file;

        console.log('PRODUCT JSON:', product);
        console.log('modelFile raw:', modelFile);

        if (!modelFile) {
            throw new Error('No 3D model for this product');
        }

        // build possible URLs
        const candidates = [
            resolveAsset(MODEL_DIR, modelFile),
            resolveAsset('/RADS-TOOLING/public/uploads/models/', modelFile), // fallback kung public/ ang tama sa machine 
            resolveAsset('/', modelFile) // kung DB already stores '/RADS-TOOLING/uploads/...'
        ];

        let modelURL = null;
        for (const u of candidates) {
            if (await headOK(u)) { modelURL = u; break; }
        }
        console.log('Model URL candidates:', candidates, '→ chosen:', modelURL);

        if (!modelURL) {
            throw new Error('3D model not found on server (all candidates failed HEAD)');
        }

        mediaBox.innerHTML = `
  <model-viewer
    id="mv"
    style="width:100%;height:100%;display:block;--poster-color:transparent;background:#f7f7f7"
    src="${modelURL}"
    camera-controls
    auto-rotate
    bounds="tight"
    reveal="auto"
    environment-image="neutral"
    shadow-intensity="1"
    exposure="1">
  </model-viewer>
`;

        mv = document.getElementById('mv');
        mv.addEventListener('error', (e) => console.error('model-viewer error:', e));


        // 3) Map materials when loaded (rename if needed)
        mv.addEventListener('load', () => {
            const list = mv.model?.materials || [];
            mats.door = list.find(m => m.name === 'Mat_Door');
            mats.body = list.find(m => m.name === 'Mat_Body');
            mats.inside = list.find(m => m.name === 'Mat_Inside');
        });

        // 4) Build UI lists (allowed only) + partbars
        buildTextures(product.textures || []);
        buildColors(product.colors || []);
        buildHandles(product.handles || []);
        bindPartbars();

        // 5) Size config
        const map = { width: null, height: null, depth: null };
        if (Array.isArray(product.size_config)) product.size_config.forEach(c => map[c.dimension_type] = c);
        else if (product.size_config && typeof product.size_config === 'object') Object.assign(map, product.size_config);
        initSizeUI(map);

        recalcPrice();
        showStep(0); // start at Size

        // 6) Add to cart (payload sample)
        toCartBtn.addEventListener('click', () => {
            const payload = {
                product_id: PID,
                selections: {
                    part: {
                        door: chosen.textures.door ? { type: 'texture', id: chosen.textures.door } : (chosen.colors.door ? { type: 'color', id: chosen.colors.door } : null),
                        body: chosen.textures.body ? { type: 'texture', id: chosen.textures.body } : (chosen.colors.body ? { type: 'color', id: chosen.colors.body } : null),
                        inside: chosen.textures.inside ? { type: 'texture', id: chosen.textures.inside } : (chosen.colors.inside ? { type: 'color', id: chosen.colors.inside } : null)
                    },
                    handle: chosen.handle || null,
                    size: {
                        width: +wSlider?.value || null, height: +hSlider?.value || null, depth: +dSlider?.value || null,
                        doors: +doorsSl?.value || null, layers: +layersSl?.value || null
                    }
                }
            };
            console.log('ADD TO CART PAYLOAD', payload);
            alert('Design saved! (See console for payload)');
            // TODO: POST to your cart endpoint
        });
    }

    init().catch(e => { console.error(e); alert('Failed to initialize customization page.'); });
})();
