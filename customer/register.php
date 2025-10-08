<?php
require_once __DIR__ . '/../backend/config/app.php';
session_start();
if (!empty($_SESSION['user']) && ($_SESSION['user']['aud'] ?? '') === 'customer') {
  header('Location: /customer/index.php'); exit;
}
$next = $_GET['next'] ?? '/RADS-TOOLING/customer/homepage.php';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Customer Registration | Rads Tooling</title>
  <link rel="stylesheet" href="/RADS-TOOLING/assets/CSS/login.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
  <style>
    .auth-form.register-form {
      max-width: 800px;
    }
    
    .form-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1rem 1.5rem;
      margin-bottom: 1.5rem;
    }
    
    .form-grid .form-group {
      margin-bottom: 0;
    }
    
    .form-grid .full-width {
      grid-column: 1 / -1;
    }
    
    /* Password strength indicator styles */
    .password-strength {
      margin-top: 0.5rem;
      height: 4px;
      background: #e8f0fe;
      border-radius: 2px;
      overflow: hidden;
      display: none;
    }
    
    .password-strength.show {
      display: block;
    }
    
    .password-strength-bar {
      height: 100%;
      transition: width 0.3s ease, background 0.3s ease;
      width: 0;
    }
    
    .password-strength-bar.weak {
      width: 33%;
      background: #f56565;
    }
    
    .password-strength-bar.medium {
      width: 66%;
      background: #f6ad55;
    }
    
    .password-strength-bar.strong {
      width: 100%;
      background: #48bb78;
    }
    
    /* Validation states */
    .form-group.has-success input {
      border-color: #48bb78;
    }
    
    .form-group.has-error input {
      border-color: #f56565;
    }
    
    @media (max-width: 768px) {
      .auth-form.register-form {
        max-width: 420px;
      }
      
      .form-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
      }
      
      .form-grid .form-group {
        margin-bottom: 1rem;
      }
    }
  </style>
</head>
<body>
  <main class="auth-wrap">
    <div class="auth-form register-form">
      <div class="logo-container">
        <span class="login-logo">R</span><span class="logo-text">ADS</span>
        <span class="login-logo">T</span><span class="logo-text">OOLING</span>
      </div>
      <p class="subtitle">Create your account to get started</p>
      
      <form id="registerForm">
        <div class="form-grid">
          <div class="form-group">
            <label for="firstName">First Name</label>
            <input id="firstName" name="first_name" placeholder="Enter your first name" required>
          </div>
          
          <div class="form-group">
            <label for="lastName">Last Name</label>
            <input id="lastName" name="last_name" placeholder="Enter your last name" required>
          </div>
          
          <div class="form-group">
            <label for="email">Email Address</label>
            <input id="email" name="email" type="email" placeholder="your.email@example.com" required>
          </div>
          
          <div class="form-group">
            <label for="phone">Phone Number</label>
            <input id="phone" name="phone" type="tel" placeholder="+63 9XX XXX XXXX" required>
          </div>
          
          <div class="form-group full-width">
            <label for="username">Username</label>
            <input id="username" name="username" placeholder="Choose a unique username" required>
          </div>
          
          <div class="form-group">
            <label for="password">Password</label>
            <input id="password" name="password" type="password" placeholder="Min. 6 characters" required>
            <div class="password-strength">
              <div class="password-strength-bar"></div>
            </div>
          </div>
          
          <div class="form-group">
            <label for="confirmPassword">Confirm Password</label>
            <input id="confirmPassword" name="confirm_password" type="password" placeholder="Re-enter password" required>
          </div>
        </div>
        
        <input type="hidden" id="next" value="<?= htmlspecialchars($next) ?>">
        
        <button type="submit" class="btn-signup-primary" id="signupBtn">
          <span class="material-symbols-rounded">person_add</span>
          <span>Create Free Account</span>
        </button>    

        <div class="error-message" id="errorMessage"></div>
        <div class="loading" id="loadingMessage">Creating your account...</div>
      </form>
      
      <div class="auth-links">
        <p>Already have an account? <a href="/RADS-TOOLING/customer/cust_login.php?next=<?= urlencode($next) ?>">Sign in here</a></p>
      </div>
    </div>
  </main>
  
  <!-- Use external JS file -->
  <script src="/RADS-TOOLING/assets/JS/register.js"></script>
</body>
</html>