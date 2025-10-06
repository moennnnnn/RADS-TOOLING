// RADS-TOOLING Customer Chat Widget
(function() {
  if (window.__RT_CHAT_WIDGET_LOADED__) return;
  window.__RT_CHAT_WIDGET_LOADED__ = true;

  const API_BASE = '/RADS-TOOLING/backend/api/chat.php';
  const SIMILARITY_THRESHOLD = 0.62;

  // Elements
  const btn = document.getElementById('rtChatBtn');
  const popup = document.getElementById('rtChatPopup');
  const messages = document.getElementById('rtChatMessages');
  const input = document.getElementById('rtChatInput');
  const sendBtn = document.getElementById('rtChatSend');
  const faqToggle = document.getElementById('rtFaqToggle');
  const faqDropdown = document.getElementById('rtFaqDropdown');

  let threadCode = localStorage.getItem('rt_thread_code') || null;
  let lastId = 0;
  let pollTimer = null;
  let faqsCache = [];
  let waitingNoticeSent = false;
  let sendCooldown = false;
  let faqOpen = false;

  // ========== KEYWORD SYNONYMS & STEMMING ==========
  const SYNONYMS = {
    'deliver': ['delivery', 'deliveries', 'delivering', 'ship', 'shipping', 'shipped', 'courier', 'send'],
    'ship': ['shipping', 'shipped', 'delivery', 'deliver', 'courier'],
    'customize': ['custom', 'customization', 'personalize', 'modify', 'change'],
    'size': ['dimension', 'dimensions', 'measurement'],
    'color': ['colour', 'colors', 'colours'],
    'lead': ['wait', 'waiting', 'timeline', 'time', 'duration'],
    'price': ['cost', 'pricing', 'rate', 'fee', 'charge'],
    'order': ['ordering', 'purchase', 'buy', 'buying']
  };

  function simpleStem(word) {
    return word.toLowerCase()
      .replace(/ies$/, 'y')
      .replace(/ing$/, '')
      .replace(/ed$/, '')
      .replace(/s$/, '');
  }

  function getRelatedWords(word) {
    const stemmed = simpleStem(word);
    const related = new Set([word, stemmed]);
    
    for (const [key, synonyms] of Object.entries(SYNONYMS)) {
      if (key === stemmed || synonyms.includes(stemmed)) {
        related.add(key);
        synonyms.forEach(syn => related.add(syn));
      }
    }
    
    return Array.from(related);
  }

  function keywordBonus(userText, faqQuestion) {
    const userWords = userText.toLowerCase().split(/\s+/).filter(w => w.length > 2);
    const faqWords = faqQuestion.toLowerCase().split(/\s+/).filter(w => w.length > 2);
    
    let matches = 0;
    
    for (const userWord of userWords) {
      const related = getRelatedWords(userWord);
      for (const rel of related) {
        if (faqWords.some(fw => simpleStem(fw) === simpleStem(rel))) {
          matches++;
          break;
        }
      }
    }
    
    return userWords.length > 0 ? matches / userWords.length : 0;
  }

  // ========== SIMILARITY MATCHING ==========
  function jaccardSimilarity(str1, str2) {
    const tokens1 = new Set(str1.toLowerCase().split(/\s+/).filter(w => w.length > 2));
    const tokens2 = new Set(str2.toLowerCase().split(/\s+/).filter(w => w.length > 2));
    
    if (tokens1.size === 0 && tokens2.size === 0) return 1;
    if (tokens1.size === 0 || tokens2.size === 0) return 0;
    
    const intersection = new Set([...tokens1].filter(x => tokens2.has(x)));
    const union = new Set([...tokens1, ...tokens2]);
    
    return intersection.size / union.size;
  }

  function levenshteinDistance(s1, s2) {
    const len1 = s1.length, len2 = s2.length;
    const dp = Array(len1 + 1).fill(null).map(() => Array(len2 + 1).fill(0));

    for (let i = 0; i <= len1; i++) dp[i][0] = i;
    for (let j = 0; j <= len2; j++) dp[0][j] = j;

    for (let i = 1; i <= len1; i++) {
      for (let j = 1; j <= len2; j++) {
        const cost = s1[i-1] === s2[j-1] ? 0 : 1;
        dp[i][j] = Math.min(dp[i-1][j] + 1, dp[i][j-1] + 1, dp[i-1][j-1] + cost);
      }
    }
    return dp[len1][len2];
  }

  function normalizedLevenshtein(s1, s2) {
    const maxLen = Math.max(s1.length, s2.length);
    if (maxLen === 0) return 1;
    return 1 - (levenshteinDistance(s1.toLowerCase(), s2.toLowerCase()) / maxLen);
  }

  function combinedSimilarity(userText, faqQuestion) {
    const jaccard = jaccardSimilarity(userText, faqQuestion);
    const leven = normalizedLevenshtein(userText, faqQuestion);
    const keyword = keywordBonus(userText, faqQuestion);
    
    return (jaccard * 0.3) + (leven * 0.3) + (keyword * 0.4);
  }

  function findBestFaq(userText) {
    if (!faqsCache.length) return null;
    
    let bestMatch = null;
    let bestScore = 0;

    for (const faq of faqsCache) {
      const score = combinedSimilarity(userText, faq.question);
      if (score > bestScore) {
        bestScore = score;
        bestMatch = faq;
      }
    }

    const keywordScore = keywordBonus(userText, bestMatch?.question || '');
    const threshold = keywordScore > 0.6 ? 0.50 : SIMILARITY_THRESHOLD;

    return (bestScore >= threshold) ? bestMatch : null;
  }

  // ========== TOAST NOTIFICATIONS ==========
  function showToast(message, type = 'info') {
    let container = document.querySelector('.rt-toast-container');
    if (!container) {
      container = document.createElement('div');
      container.className = 'rt-toast-container';
      document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = `rt-toast rt-${type}`;
    toast.innerHTML = `<div class="rt-toast-message">${message}</div>`;
    
    container.appendChild(toast);
    
    setTimeout(() => {
      toast.style.animation = 'rt-slideIn 0.3s ease reverse';
      setTimeout(() => toast.remove(), 300);
    }, 3000);
  }

  // ========== CLEAR BUTTON ==========
  const clearBtn = document.createElement('button');
  clearBtn.className = 'rt-chat-clear';
  clearBtn.textContent = 'Clear';
  clearBtn.title = 'Clear chat';
  document.querySelector('.rt-chat-header').appendChild(clearBtn);

  clearBtn.addEventListener('click', async () => {
    if (!threadCode) return;
    if (!confirm('Clear this conversation and start a new one?')) return;

    try {
      await fetch(`${API_BASE}?action=thread_close`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ thread_code: threadCode })
      });
    } catch(_) {}

    const oldCode = threadCode;
    localStorage.removeItem('rt_thread_code');
    localStorage.removeItem('rt_waiting_' + oldCode);
    threadCode = null;
    lastId = 0;
    messages.innerHTML = '';

    await ensureThread();
    await loadFaqs();
  });

  // ========== FAQ TOGGLE ==========
  faqToggle.addEventListener('click', () => {
    faqOpen = !faqOpen;
    faqDropdown.classList.toggle('rt-open', faqOpen);
    faqToggle.querySelector('.rt-faq-icon').classList.toggle('rt-open', faqOpen);
  });

  // ========== RENDER HELPERS ==========
  function createBubble(sender, text, isTemp = false) {
    const wrap = document.createElement('div');
    wrap.className = `rt-bubble-wrap rt-${sender}`;
    
    const bubble = document.createElement('div');
    bubble.className = 'rt-bubble' + (isTemp ? ' rt-temp' : '');
    bubble.textContent = text;
    bubble.dataset.body = text;
    
    wrap.appendChild(bubble);
    messages.appendChild(wrap);
    messages.scrollTop = messages.scrollHeight;
    
    return bubble;
  }

  function confirmOrAppend(sender, text) {
    const temps = messages.querySelectorAll(`.rt-bubble-wrap.rt-${sender} .rt-bubble.rt-temp`);
    for (const t of temps) {
      if (t.dataset.body === text) {
        t.classList.remove('rt-temp');
        return;
      }
    }
    createBubble(sender, text, false);
  }

  function renderFaqChips() {
    faqDropdown.innerHTML = '';
    
    if (!faqsCache.length) {
      faqDropdown.innerHTML = '<div style="padding:8px;color:#999;font-size:12px;">No FAQs available</div>';
      return;
    }

    faqsCache.forEach(faq => {
      const chip = document.createElement('button');
      chip.className = 'rt-faq-chip';
      chip.textContent = faq.question;
      chip.addEventListener('click', () => {
        faqOpen = false;
        faqDropdown.classList.remove('rt-open');
        faqToggle.querySelector('.rt-faq-icon').classList.remove('rt-open');
        sendCustomerText(faq.question, 'faq', faq.answer);
      });
      faqDropdown.appendChild(chip);
    });
  }

  // ========== PERSISTENCE ==========
  function saveWait() {
    localStorage.setItem('rt_waiting_' + threadCode, JSON.stringify(waitingNoticeSent));
  }

  function loadWait() {
    waitingNoticeSent = JSON.parse(localStorage.getItem('rt_waiting_' + threadCode) || 'false');
  }

  // ========== SEND MESSAGE ==========
  async function sendCustomerText(text, source = 'manual', presetAnswer = null) {
    text = (text || '').trim();
    if (!text) return;

    if (sendCooldown) return;
    sendCooldown = true;
    setTimeout(() => sendCooldown = false, 1500);

    createBubble('customer', text, true);

    try {
      await fetch(`${API_BASE}?action=send_customer`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          thread_code: threadCode,
          sender: 'customer',
          body: text
        })
      });
    } catch(err) {
      console.error('Send error:', err);
      showToast('Failed to send message', 'error');
    }

    let answerToSend = presetAnswer;
    
    if (!answerToSend && source !== 'faq') {
      const bestFaq = findBestFaq(text);
      answerToSend = bestFaq ? bestFaq.answer : null;
    }

    if (answerToSend) {
      createBubble('bot', answerToSend, true);
      try {
        await fetch(`${API_BASE}?action=send_customer`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            thread_code: threadCode,
            sender: 'bot',
            body: answerToSend
          })
        });
      } catch(err) {
        console.error('Bot reply error:', err);
      }
      return;
    }

    if (!waitingNoticeSent) {
      const fallback = "Please wait for the admin.";
      createBubble('bot', fallback, true);
      try {
        await fetch(`${API_BASE}?action=send_customer`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            thread_code: threadCode,
            sender: 'bot',
            body: fallback
          })
        });
      } catch(err) {
        console.error('Fallback error:', err);
      }
      waitingNoticeSent = true;
      saveWait();
    }
  }

  // ========== THREAD/FAQS/POLL ==========
  async function ensureThread() {
    if (threadCode) {
      try {
        const r = await fetch(`${API_BASE}?action=thread_find&code=${encodeURIComponent(threadCode)}`);
        const j = await r.json();
        if (j.success) {
          loadWait();
          return;
        }
      } catch(_) {}
    }

    try {
      const r = await fetch(`${API_BASE}?action=thread_create`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({})
      });
      const j = await r.json();
      if (!j.success) throw new Error('Cannot create thread');

      threadCode = j.data.thread_code;
      localStorage.setItem('rt_thread_code', threadCode);
      waitingNoticeSent = false;
      saveWait();
    } catch(err) {
      console.error('Thread error:', err);
      showToast('Failed to initialize chat', 'error');
      throw err;
    }
  }

  async function loadFaqs() {
    try {
      const r = await fetch(`${API_BASE}?action=faqs_list`);
      const j = await r.json();
      if (j.success) {
        faqsCache = j.data || [];
        renderFaqChips();
      }
    } catch(err) {
      console.error('FAQ load error:', err);
    }
  }

  async function poll() {
    if (!threadCode) return;

    try {
      const r = await fetch(`${API_BASE}?action=messages_fetch&thread_code=${encodeURIComponent(threadCode)}&after_id=${lastId}`);
      const j = await r.json();
      if (!j.success) return;

      j.data.forEach(m => {
        lastId = Math.max(lastId, Number(m.id));
        confirmOrAppend(m.sender_type, m.body);

        if (m.sender_type === 'admin') {
          waitingNoticeSent = false;
          saveWait();
        }
      });
    } catch(err) {
      console.error('Poll error:', err);
    }
  }

  // ========== UI EVENTS ==========
  btn.addEventListener('click', async () => {
    const isOpen = popup.classList.contains('rt-open');
    popup.classList.toggle('rt-open', !isOpen);

    if (!isOpen) {
      try {
        await ensureThread();
        await loadFaqs();

        if (!pollTimer) {
          pollTimer = setInterval(poll, 2000);
        }
      } catch(e) {
        console.error('Init error:', e);
        createBubble('bot', 'Sorry, chat is unavailable right now.');
      }
    }
  });

  sendBtn.addEventListener('click', () => {
    sendCustomerText(input.value, 'manual');
    input.value = '';
  });

  input.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') {
      sendCustomerText(input.value, 'manual');
      input.value = '';
    }
  });

  console.log('RADS-TOOLING Chat Widget loaded');
})();