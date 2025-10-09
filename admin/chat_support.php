<?php
// RADS-TOOLING Admin Chat Support
if (session_status() === PHP_SESSION_NONE) {
    if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
}
// Check admin authentication
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_id'] <= 0) {
    header('Location: /RADS-TOOLING/admin/login.php');
    exit;
}

$admin_name = $_SESSION['username'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Chat Support - Admin | RADS-TOOLING</title>
  <link rel="stylesheet" href="/RADS-TOOLING/assets/CSS/chat-admin.css">
</head>
<body class="rt-admin-body">
  
  <header class="rt-admin-header">
    <h1>Chat Support</h1>
    <div class="rt-user-info">Logged in as: <?php echo htmlspecialchars($admin_name); ?></div>
  </header>

  <div class="rt-admin-container">
    <!-- Left: Threads -->
    <aside class="rt-admin-sidebar">
      <div class="rt-admin-box">
        <h3>Threads</h3>
        <input type="text" id="rtThreadSearch" class="rt-admin-search" placeholder="Search threads..." />
        <div class="rt-thread-list" id="rtThreadList">
          <div style="padding:12px;color:#999;">Loading...</div>
        </div>
      </div>
    </aside>

    <!-- Middle: Conversation -->
    <main class="rt-admin-main">
      <div class="rt-admin-box">
        <h3>Conversation</h3>
        <div class="rt-admin-conv" id="rtAdminConv"></div>
        <div class="rt-input-row">
          <input type="text" id="rtAdminMsg" placeholder="Type a reply..." />
          <button id="rtAdminSend" class="rt-btn rt-btn-primary">Send</button>
        </div>
      </div>

      <!-- Bottom: Manage FAQs -->
      <div class="rt-admin-box">
        <h3>Manage FAQs</h3>
        <div class="rt-input-row">
          <input type="text" id="rtFaqQ" placeholder="Question" />
          <input type="text" id="rtFaqA" placeholder="Answer" />
          <button id="rtFaqSave" class="rt-btn rt-btn-primary">Save</button>
        </div>
        <table class="rt-faq-table">
          <thead>
            <tr>
              <th>Question</th>
              <th style="width:180px;">Actions</th>
            </tr>
          </thead>
          <tbody id="rtFaqTbody">
            <tr><td colspan="2">Loading...</td></tr>
          </tbody>
        </table>
      </div>
    </main>
  </div>

  <script src="/RADS-TOOLING/assets/JS/chat_admin.js"></script>
</body>
</html>