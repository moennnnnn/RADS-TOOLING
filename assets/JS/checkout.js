(() => {
  // Modal elements
  const modal = document.getElementById('buyChoiceModal');
  const closeBtn = modal?.querySelector('[data-close]');
  const btnDeliver = document.getElementById('chooseDeliver');
  const btnPickup  = document.getElementById('choosePickup');

  let selected = null; // { pid, name, price, qty }

  const openModal = (data) => { selected = data; modal.hidden = false; };
  const closeModal = () => { modal.hidden = true; };

  closeBtn?.addEventListener('click', closeModal);
  modal?.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });

  // Helper to build querystring
  const qs = (o) => new URLSearchParams(o).toString();

  // Handle clicks from any Buy Now on the page
  document.addEventListener('click', (e) => {
    const el = e.target.closest('.js-buynow, [data-act="buynow"]');
    if (!el) return;

    // Prefer data-attrs (from the change in products.php)
    let pid   = el.dataset.pid;
    let name  = el.dataset.name;
    let price = el.dataset.price;
    let qty   = el.dataset.qty || '1';

    // Fallback: derive from card DOM if missing
    if (!pid) {
      const card = el.closest('.rt-card');
      pid   = card?.dataset?.id || '';
      name  = name || card?.dataset?.name || card?.querySelector?.('.rt-name')?.textContent?.trim() || '';
      const priceText = card?.querySelector?.('.rt-price')?.textContent || '';
      price = price || (priceText.replace(/[^\d.]/g, '') || '');
    }

    if (!pid || !price) return; // guard
    openModal({ pid, name: name || 'Item', price, qty });
  });

  // Route to next pages
  btnDeliver?.addEventListener('click', () => {
    if (!selected) return;
    location.href = `/RADS-TOOLING/customer/checkout_delivery.php?${qs(selected)}`;
  });

  btnPickup?.addEventListener('click', () => {
    if (!selected) return;
    location.href = `/RADS-TOOLING/customer/checkout_pickup.php?${qs(selected)}`;
  });
})();
