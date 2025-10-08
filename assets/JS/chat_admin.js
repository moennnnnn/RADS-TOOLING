// RADS-TOOLING Admin Chat Console
(function () {
  if (window.__RT_CHAT_ADMIN_LOADED__) return;
  window.__RT_CHAT_ADMIN_LOADED__ = true;

  const API_BASE = '/RADS-TOOLING/backend/api/chat.php';

  let selectedCode = null;
  let lastId = 0;
  let pollTimer = null;

  // ========== MODAL HELPERS ==========
  function showDeleteModal(id) {
    const modal = document.getElementById('chatDeleteModal');
    const confirmBtn = document.getElementById('chatDeleteConfirm');

    if (!modal || !confirmBtn) {
      if (confirm('Delete this FAQ permanently?')) {
        performDelete(id);
      }
      return;
    }

    modal.style.display = 'flex';

    confirmBtn.onclick = async () => {
      modal.style.display = 'none';
      await performDelete(id);
    };
  }

  // ========== DEDUPLICATION ==========
  const hash = (sender, text) => {
    let s = sender + '|' + text, h = 0;
    for (let i = 0; i < s.length; i++) {
      h = ((h << 5) - h) + s.charCodeAt(i);
      h |= 0;
    }
    return 'h' + h;
  };

  function appendIfNew(who, text) {
    const conv = document.getElementById('rtAdminConv');
    if (!conv) return;

    const h = hash(who, text);
    if (conv.querySelector(`[data-hash="${h}"]`)) return;

    const wrap = document.createElement('div');
    wrap.className = `rt-bubble-wrap rt-${who}`;

    const bubble = document.createElement('div');
    bubble.className = 'rt-bubble';
    bubble.textContent = text;
    bubble.setAttribute('data-hash', h);

    wrap.appendChild(bubble);
    conv.appendChild(wrap);
    conv.scrollTop = conv.scrollHeight;
  }

  // ========== THREADS ==========
  function threadHtml(t) {
    // Use display_name from backend
    const name = t.display_name || t.customer_name || t.customer_email || t.thread_code;
    const time = t.last_message_at ? new Date(t.last_message_at).toLocaleString() : '';

    const unreadCount = parseInt(t.unread_count) || 0;
    const displayCount = unreadCount > 5 ? '5+' : unreadCount;

    const badgeHtml = unreadCount > 0
      ? `<span class="rt-unread-badge">${displayCount}</span>`
      : '';

    return `
    <div class="rt-thread-item ${t.has_unread ? 'rt-has-unread' : ''}" 
         data-code="${escapeHtml(t.thread_code)}">
      <div class="rt-thread-header">
        <div class="rt-thread-name">
          ${escapeHtml(name)}
          ${badgeHtml}
        </div>
        <div class="rt-thread-time">${time}</div>
      </div>
    </div>
  `;
  }

  async function loadThreads() {
    const list = document.getElementById('rtThreadList');
    if (!list) return;

    try {
      const r = await fetch(`${API_BASE}?action=threads`, {
        credentials: 'same-origin'
      });

      if (!r.ok) {
        throw new Error(`HTTP ${r.status}`);
      }

      const j = await r.json();

      if (!j.success) {
        list.innerHTML = `<div style="padding:12px;color:#dc3545;">Error: ${escapeHtml(j.message || 'Failed to load threads')}</div>`;
        return;
      }

      if (!j.data || j.data.length === 0) {
        list.innerHTML = `<div style="padding:12px;color:#999;">No customer threads yet</div>`;
        return;
      }

      list.innerHTML = j.data.map(threadHtml).join('');

      list.querySelectorAll('.rt-thread-item').forEach(item => {
        item.addEventListener('click', () => {
          list.querySelectorAll('.rt-thread-item').forEach(i => i.classList.remove('rt-active'));
          item.classList.add('rt-active');
          openConv(item.getAttribute('data-code'));
        });
      });
    } catch (err) {
      console.error('Load threads error:', err);
      list.innerHTML = `<div style="padding:12px;color:#dc3545;">Network error: ${escapeHtml(err.message)}</div>`;
    }
  }

  async function openConv(code) {
    selectedCode = code;
    lastId = 0;
    const conv = document.getElementById('rtAdminConv');
    if (conv) conv.innerHTML = '';
    if (pollTimer) clearInterval(pollTimer);

    // CRITICAL: Remove unread badge immediately when thread is opened
    const threadItem = document.querySelector(`.rt-thread-item[data-code="${code}"]`);
    if (threadItem) {
      threadItem.classList.remove('rt-has-unread');
      const badge = threadItem.querySelector('.rt-unread-badge');
      if (badge) badge.remove();
    }

    await poll();
    pollTimer = setInterval(poll, 2000);
  }

  async function poll() {
    if (!selectedCode) return;
    try {
      const r = await fetch(`${API_BASE}?action=messages_fetch&thread_code=${encodeURIComponent(selectedCode)}&after_id=${lastId}`, {
        credentials: 'same-origin'
      });

      if (!r.ok) return;

      const j = await r.json();
      if (!j.success || !j.data) return;

      j.data.forEach(m => {
        lastId = Math.max(lastId, Number(m.id));
        appendIfNew(m.sender_type, m.body);
      });
    } catch (err) {
      console.error('Poll error:', err);
    }
  }

  async function send() {
    const input = document.getElementById('rtAdminMsg');
    if (!input) return;

    const text = input.value.trim();
    if (!text || !selectedCode) return;

    input.value = '';
    appendIfNew('admin', text);

    try {
      const r = await fetch(`${API_BASE}?action=send_admin`, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          thread_code: selectedCode,
          body: text
        })
      });

      if (!r.ok) throw new Error(`HTTP ${r.status}`);

      const j = await r.json();
      if (!j.success) {
        console.error('Send failed:', j.message);
      }
    } catch (err) {
      console.error('Send error:', err);
    }
  }

  // ========== FAQs ==========
  async function loadFaqs() {
    const tbody = document.getElementById('rtFaqTbody');
    if (!tbody) return;

    try {
      const r = await fetch(`${API_BASE}?action=faqs_list`, {
        credentials: 'same-origin'
      });

      if (!r.ok) {
        throw new Error(`HTTP ${r.status}`);
      }

      const j = await r.json();

      if (!j.success) {
        tbody.innerHTML = `<tr><td colspan="2" style="color:#dc3545;">Error: ${escapeHtml(j.message || 'Failed to load FAQs')}</td></tr>`;
        return;
      }

      if (!j.data || j.data.length === 0) {
        tbody.innerHTML = `<tr><td colspan="2" style="text-align:center;color:#999;padding:20px;">No FAQs yet. Create one above!</td></tr>`;
        return;
      }

      tbody.innerHTML = j.data.map(f => `
        <tr class="rt-faq-row" data-id="${f.id}">
          <td>
            <div class="rt-faq-question">${escapeHtml(f.question)}</div>
            <div class="rt-faq-answer-preview" id="rtPreview${f.id}">
              <div class="rt-faq-answer-label">Answer:</div>
              ${escapeHtml(f.answer)}
            </div>
          </td>
          <td>
            <button class="rt-btn rt-btn-edit" data-id="${f.id}" data-q="${escapeHtml(f.question)}" data-a="${escapeHtml(f.answer)}">Edit</button>
            <button class="rt-btn rt-btn-danger" data-id="${f.id}">Delete</button>
          </td>
        </tr>
      `).join('');

      // Toggle answer preview
      tbody.querySelectorAll('.rt-faq-question').forEach(q => {
        q.addEventListener('click', () => {
          const row = q.closest('.rt-faq-row');
          const id = row.getAttribute('data-id');
          const preview = document.getElementById(`rtPreview${id}`);

          tbody.querySelectorAll('.rt-faq-answer-preview').forEach(p => {
            if (p.id !== `rtPreview${id}`) p.classList.remove('rt-show');
          });

          preview.classList.toggle('rt-show');
        });
      });

      // Edit handlers
      tbody.querySelectorAll('.rt-btn-edit').forEach(btn => {
        btn.addEventListener('click', (e) => {
          e.stopPropagation();
          const qInput = document.getElementById('rtFaqQ');
          const aInput = document.getElementById('rtFaqA');
          if (qInput && aInput) {
            qInput.value = decodeHtml(btn.getAttribute('data-q'));
            aInput.value = decodeHtml(btn.getAttribute('data-a'));
            qInput.dataset.editId = btn.getAttribute('data-id');
            qInput.focus();
          }
        });
      });

      // Delete handlers
      tbody.querySelectorAll('.rt-btn-danger').forEach(btn => {
        btn.addEventListener('click', (e) => {
          e.stopPropagation();
          showDeleteModal(btn.getAttribute('data-id'));
        });
      });

    } catch (e) {
      console.error('FAQ load error:', e);
      tbody.innerHTML = `<tr><td colspan="2" style="color:#dc3545;">Error: ${escapeHtml(e.message)}</td></tr>`;
    }
  }

  async function performDelete(id) {
    try {
      const r = await fetch(`${API_BASE}?action=faqs_delete`, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: parseInt(id) })
      });

      if (!r.ok) throw new Error(`HTTP ${r.status}`);

      const j = await r.json();
      if (!j.success) {
        showChatError(j.message || 'Failed to delete FAQ');
        return;
      }

      showChatSuccess('Success', 'FAQ deleted successfully!');
      loadFaqs();
    } catch (err) {
      console.error('Delete error:', err);
      showChatError('Error deleting FAQ: ' + err.message);
    }
  }

  async function saveFaq() {
    const qInput = document.getElementById('rtFaqQ');
    const aInput = document.getElementById('rtFaqA');

    if (!qInput || !aInput) return;

    const q = qInput.value.trim();
    const a = aInput.value.trim();

    if (!q || !a) {
      showChatError('Please enter both question and answer');
      return;
    }

    const editId = qInput.dataset.editId || null;

    try {
      const payload = { question: q, answer: a };
      if (editId) {
        payload.id = parseInt(editId);
      }

      const r = await fetch(`${API_BASE}?action=faqs_save`, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });

      if (!r.ok) throw new Error(`HTTP ${r.status}`);

      const j = await r.json();
      if (!j.success) {
        showChatError(j.message || 'Failed to save FAQ');
        return;
      }

      qInput.value = '';
      aInput.value = '';
      delete qInput.dataset.editId;

      loadFaqs();
      showChatSuccess('Success', editId ? 'FAQ updated successfully!' : 'FAQ created successfully!');
    } catch (err) {
      console.error('Save error:', err);
      showChatError('Error saving FAQ: ' + err.message);
    }
  }

  // ========== HELPERS ==========
  function escapeHtml(s) {
    if (!s) return '';
    return String(s).replace(/[&<>"']/g, c => ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    }[c]));
  }

  function decodeHtml(html) {
    const txt = document.createElement('textarea');
    txt.innerHTML = html;
    return txt.value;
  }

  // ========== EVENT LISTENERS ==========
  const sendBtn = document.getElementById('rtAdminSend');
  const faqSaveBtn = document.getElementById('rtFaqSave');
  const msgInput = document.getElementById('rtAdminMsg');
  const searchInput = document.getElementById('rtThreadSearch');

  if (sendBtn) {
    sendBtn.addEventListener('click', send);
  }

  if (faqSaveBtn) {
    faqSaveBtn.addEventListener('click', saveFaq);
  }

  if (msgInput) {
    msgInput.addEventListener('keypress', (e) => {
      if (e.key === 'Enter') {
        e.preventDefault();
        send();
      }
    });
  }

  // Search threads
  if (searchInput) {
    searchInput.addEventListener('input', (e) => {
      const search = e.target.value.toLowerCase();
      document.querySelectorAll('.rt-thread-item').forEach(item => {
        const name = item.querySelector('.rt-thread-name').textContent.toLowerCase();
        item.style.display = name.includes(search) ? 'block' : 'none';
      });
    });
  }

  // ========== INITIALIZE ==========
  loadThreads();
  loadFaqs();

  console.log('RADS-TOOLING Admin Chat loaded');
})();