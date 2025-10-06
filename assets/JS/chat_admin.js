// RADS-TOOLING Admin Chat Console
(function() {
  if (window.__RT_CHAT_ADMIN_LOADED__) return;
  window.__RT_CHAT_ADMIN_LOADED__ = true;

  const API_BASE = '/RADS-TOOLING/backend/api/chat.php';

  let selectedCode = null;
  let lastId = 0;
  let pollTimer = null;

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
    const name = t.customer_name || t.customer_email || t.customer_phone || t.thread_code;
    const time = t.last_message_at ? new Date(t.last_message_at).toLocaleString() : '';
    
    return `
      <div class="rt-thread-item" data-code="${t.thread_code}">
        <div class="rt-thread-name">${escapeHtml(name)}</div>
        <div class="rt-thread-time">${time}</div>
      </div>
    `;
  }

  async function loadThreads() {
    const list = document.getElementById('rtThreadList');
    try {
      const r = await fetch(`${API_BASE}?action=threads`);
      const j = await r.json();

      if (!j.success) {
        list.innerHTML = `<div style="padding:12px;color:#999;">Error loading threads</div>`;
        return;
      }

      if (!j.data || j.data.length === 0) {
        list.innerHTML = `<div style="padding:12px;color:#999;">No threads yet</div>`;
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
      list.innerHTML = `<div style="padding:12px;color:#dc3545;">Network error</div>`;
    }
  }

  async function openConv(code) {
    selectedCode = code;
    lastId = 0;
    document.getElementById('rtAdminConv').innerHTML = '';
    if (pollTimer) clearInterval(pollTimer);
    await poll();
    pollTimer = setInterval(poll, 2000);
  }

  async function poll() {
    if (!selectedCode) return;
    try {
      const r = await fetch(`${API_BASE}?action=messages_fetch&thread_code=${encodeURIComponent(selectedCode)}&after_id=${lastId}`);
      const j = await r.json();
      if (!j.success) return;

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
    const text = input.value.trim();
    if (!text || !selectedCode) return;

    input.value = '';
    appendIfNew('admin', text);

    try {
      await fetch(`${API_BASE}?action=send_admin`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          thread_code: selectedCode,
          body: text
        })
      });
    } catch (err) {
      console.error('Send error:', err);
    }
  }

  // ========== FAQs ==========
  async function loadFaqs() {
    const tbody = document.getElementById('rtFaqTbody');
    try {
      const r = await fetch(`${API_BASE}?action=faqs_list`);
      const j = await r.json();

      if (!j.success) {
        tbody.innerHTML = `<tr><td colspan="2">Error loading FAQs</td></tr>`;
        return;
      }

      if (!j.data || j.data.length === 0) {
        tbody.innerHTML = `<tr><td colspan="2">No FAQs yet</td></tr>`;
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
          document.getElementById('rtFaqQ').value = btn.getAttribute('data-q');
          document.getElementById('rtFaqA').value = btn.getAttribute('data-a');
          document.getElementById('rtFaqQ').dataset.editId = btn.getAttribute('data-id');
        });
      });

      // Delete handlers
      tbody.querySelectorAll('.rt-btn-danger').forEach(btn => {
        btn.addEventListener('click', (e) => {
          e.stopPropagation();
          deleteFaq(btn.getAttribute('data-id'));
        });
      });

    } catch (e) {
      console.error('FAQ load error:', e);
      tbody.innerHTML = `<tr><td colspan="2">Error: ${e.message}</td></tr>`;
    }
  }

  async function deleteFaq(id) {
    if (!confirm('Delete this FAQ permanently?')) return;

    try {
      const r = await fetch(`${API_BASE}?action=faqs_delete`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: parseInt(id) })
      });

      const j = await r.json();
      if (!j.success) {
        alert(j.message || 'Failed to delete FAQ');
        return;
      }

      loadFaqs();
    } catch (err) {
      console.error('Delete error:', err);
      alert('Error deleting FAQ');
    }
  }

  async function saveFaq() {
    const qInput = document.getElementById('rtFaqQ');
    const aInput = document.getElementById('rtFaqA');
    const q = qInput.value.trim();
    const a = aInput.value.trim();

    if (!q || !a) {
      alert('Please enter both question and answer');
      return;
    }

    const editId = qInput.dataset.editId || null;

    try {
      const payload = { question: q, answer: a };
      if (editId) payload.id = parseInt(editId);

      const r = await fetch(`${API_BASE}?action=faqs_save`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });

      const j = await r.json();
      if (!j.success) {
        alert(j.message || 'Failed to save FAQ');
        return;
      }

      qInput.value = '';
      aInput.value = '';
      delete qInput.dataset.editId;

      loadFaqs();
    } catch (err) {
      console.error('Save error:', err);
      alert('Error saving FAQ');
    }
  }

  // ========== HELPERS ==========
  function escapeHtml(s) {
    return (s || '').replace(/[&<>"']/g, c => ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    }[c]));
  }

  // ========== EVENT LISTENERS ==========
  document.getElementById('rtAdminSend').addEventListener('click', send);
  document.getElementById('rtFaqSave').addEventListener('click', saveFaq);

  document.getElementById('rtAdminMsg').addEventListener('keydown', (e) => {
    if (e.key === 'Enter') send();
  });

  // Search threads
  document.getElementById('rtThreadSearch').addEventListener('input', (e) => {
    const search = e.target.value.toLowerCase();
    document.querySelectorAll('.rt-thread-item').forEach(item => {
      const name = item.querySelector('.rt-thread-name').textContent.toLowerCase();
      item.style.display = name.includes(search) ? 'block' : 'none';
    });
  });

  // ========== INITIALIZE ==========
  loadThreads();
  loadFaqs();

  console.log('RADS-TOOLING Admin Chat loaded');
})();