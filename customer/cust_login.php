<?php
require_once __DIR__ . '/../backend/config/app.php';

if (session_status() === PHP_SESSION_NONE) {
    if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
}

if (!empty($_SESSION['user']) && ($_SESSION['user']['aud'] ?? '') === 'customer') {
  header('Location: /RADS-TOOLING/customer/homepage.php'); exit;
}
$next = $_GET['next'] ?? '/RADS-TOOLING/customer/homepage.php';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Customer Login | Rads Tooling</title>
  <link rel="stylesheet" href="/RADS-TOOLING/assets/CSS/login.css">
</head>
<body>
  <main class="auth-wrap">
    <div class="auth-form">
      <div class="logo-container">
        <span class="login-logo">R</span><span class="logo-text">ADS</span>
        <span class="login-logo">T</span><span class="logo-text">OOLING</span>
      </div>
      <p class="subtitle">Login to your account</p>
      
      <form id="loginForm">
        <div class="form-group">
          <label for="username">Username</label>
          <input id="username" name="username" placeholder="Enter your username" required>
        </div>
        
        <div class="form-group">
          <label for="password">Password</label>
          <input id="password" name="password" type="password" placeholder="Enter your password" required>
        </div>
        
        <input type="hidden" id="next" value="<?= htmlspecialchars($next) ?>">
        <button class="btn-primary" type="submit">Sign In</button>
        
        <div class="error-message" id="errorMessage"></div>
        <div class="loading" id="loadingMessage">Logging in...</div>
      </form>
      
      <div class="auth-links">
        <p>
          <a href="#" id="forgotLink">Forgot password?</a>
        </p>
        <p>
          No account? <a href="/RADS-TOOLING/customer/register.php?next=<?= urlencode($next) ?>">Create one</a>
        </p>
      </div>
    </div>
  </main>

  <!-- Forgot Password Modal: Step 1 (request code) -->
  <div class="modal" id="fpModal">
    <div class="modal-content">
      <h3>Reset Password</h3>
      <p>We'll email a 6-digit code valid for 30 minutes.</p>
      <form id="fpRequestForm">
        <input name="email" type="email" placeholder="Your account email" required>
        <button type="submit">Send Code</button>
        <button type="button" class="close-btn" data-close>Cancel</button>
      </form>
    </div>
  </div>

  <!-- Forgot Password Modal: Step 2 (enter code + new password) -->
  <div class="modal" id="fpResetModal">
    <div class="modal-content">
      <h3>Enter Code</h3>
      <form id="fpResetForm">
        <input name="email" type="email" placeholder="Email" required>
        <input name="code" placeholder="6-digit code" maxlength="6" required>
        <input name="new_password" type="password" placeholder="New password" required>
        <input name="confirm" type="password" placeholder="Confirm new password" required>
        <button type="submit">Reset Password</button>
        <button type="button" class="close-btn" data-close>Cancel</button>
      </form>
    </div>
  </div>

  <script>
  /* --- Login --- */
  document.getElementById('loginForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const errorDiv = document.getElementById('errorMessage');
    const loadingDiv = document.getElementById('loadingMessage');
    const submitBtn = e.target.querySelector('.btn-primary');
    
    // Reset states
    errorDiv.style.display = 'none';
    loadingDiv.style.display = 'block';
    submitBtn.disabled = true;
    
    // Get form data
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());
    const next = document.getElementById('next').value || '/RADS-TOOLING/customer/homepage.php';
    
    // Add audience for customer login
    data.audience = 'customer';
    
    try {
      const res = await fetch('/RADS-TOOLING/backend/api/auth.php?action=login', {
        method: 'POST', 
        headers: {'Content-Type':'application/json'}, 
        credentials:'same-origin',
        body: JSON.stringify(data)
      });
      
      const responseText = await res.text();
      console.log('Raw response:', responseText);
      
      let j;
      try {
        j = JSON.parse(responseText);
      } catch (parseError) {
        console.error('JSON Parse Error:', parseError);
        throw new Error('Server returned invalid response.');
      }
      
      if (!res.ok || !j.success) {
        throw new Error(j.message || 'Login failed');
      }
      
      // Successful login - redirect
      window.location.href = next;
      
    } catch (err) { 
      console.error('Login error:', err);
      errorDiv.textContent = err.message || 'Login failed';
      errorDiv.style.display = 'block';
    } finally {
      loadingDiv.style.display = 'none';
      submitBtn.disabled = false;
    }
  });

  /* --- Forgot Password Modals --- */
  const $$ = s => document.querySelector(s);
  const open = el => (el.style.display='flex');
  const close = el => (el.style.display='none');

  $$('#forgotLink')?.addEventListener('click', (e)=>{ 
    e.preventDefault(); 
    open($$('#fpModal')); 
  });
  
  document.querySelectorAll('[data-close]').forEach(b => b.addEventListener('click', () => {
    close($$('#fpModal')); 
    close($$('#fpResetModal'));
  }));

  // Step 1: Request code
  $$('#fpRequestForm')?.addEventListener('submit', async (e)=>{
    e.preventDefault();
    const email = new FormData(e.target).get('email');
    try {
      const r = await fetch('/RADS-TOOLING/backend/api/password.php?action=request', {
        method:'POST', 
        headers:{'Content-Type':'application/json'}, 
        credentials:'same-origin',
        body: JSON.stringify({ email })
      });
      const j = await r.json();
      if(!r.ok || !j.success) throw new Error(j.message||'Failed to send code');
      alert('We sent a code to your email.');
      close($$('#fpModal'));
      // Prefill email in step 2
      $$('#fpResetForm [name=email]').value = email;
      open($$('#fpResetModal'));
    } catch(err) { 
      alert(err.message||'Error'); 
    }
  });

  // Step 2: Submit code + new password
  $$('#fpResetForm')?.addEventListener('submit', async (e)=>{
    e.preventDefault();
    const fd = new FormData(e.target);
    const payload = Object.fromEntries(fd.entries());
    if (payload.new_password !== payload.confirm) { 
      alert('Passwords do not match'); 
      return; 
    }
    try {
      const r = await fetch('/RADS-TOOLING/backend/api/password.php?action=reset', {
        method:'POST', 
        headers:{'Content-Type':'application/json'}, 
        credentials:'same-origin',
        body: JSON.stringify(payload)
      });
      const j = await r.json();
      if(!r.ok || !j.success) throw new Error(j.message||'Reset failed');
      alert('Password updated. Please login.');
      close($$('#fpResetModal'));
    } catch(err) { 
      alert(err.message||'Error'); 
    }
  });
  </script>
</body>
</html>