/**
 * Chat Notification System - Admin Dashboard
 * Shows red dot when new customer messages need response
 */
(function () {
    if (!document.querySelector('.nav-item[data-section="chat"]')) {
        return; // Not on admin dashboard
    }

    const API_BASE = '/RADS-TOOLING/backend/api/chat.php';
    let checkInterval = null;

    async function checkUnreadMessages() {
        try {
            const response = await fetch(`${API_BASE}?action=threads`, {
                credentials: 'same-origin'
            });

            if (!response.ok) return;

            const result = await response.json();

            if (result.success && result.data) {
                // Check if ANY thread has unanswered messages (excluding bot replies)
                const hasUnread = result.data.some(t => t.has_unanswered);

                const chatNav = document.querySelector('.nav-item[data-section="chat"]');
                if (chatNav) {
                    let dot = chatNav.querySelector('.rt-notification-dot');

                    if (hasUnread && !dot) {
                        // Add notification dot
                        dot = document.createElement('span');
                        dot.className = 'rt-notification-dot';
                        chatNav.style.position = 'relative';
                        chatNav.appendChild(dot);
                    } else if (!hasUnread && dot) {
                        // Remove notification dot
                        dot.remove();
                    }
                }
            }
        } catch (error) {
            console.error('Failed to check unread messages:', error);
        }
    }

    // Check immediately
    checkUnreadMessages();

    // Check every 10 seconds
    checkInterval = setInterval(checkUnreadMessages, 10000);

    // When navigating to chat section, remove dot
    const chatNav = document.querySelector('.nav-item[data-section="chat"]');
    if (chatNav) {
        chatNav.addEventListener('click', () => {
            setTimeout(() => {
                const dot = chatNav.querySelector('.rt-notification-dot');
                if (dot) dot.remove();
            }, 1000);
        });
    }

    console.log('Chat notification system loaded');
})();