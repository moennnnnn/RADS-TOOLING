
<?php
// Verification Code Page
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../backend/config/database.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email - RADS Tooling</title>
    <link rel="stylesheet" href="/RADS-TOOLING/assets/CSS/login.css">
    <style>
        .verify-container {
            max-width: 450px;
            margin: 50px auto;
            padding: 2.5rem;
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(47, 91, 136, 0.1);
            text-align: center;
        }
        
        .code-inputs {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin: 2rem 0;
        }
        
        .code-input {
            width: 50px;
            height: 60px;
            text-align: center;
            font-size: 1.5rem;
            border: 2px solid #e3edfb;
            border-radius: 10px;
            transition: all 0.3s;
        }
        
        .code-input:focus {
            border-color: var(--brand);
            box-shadow: 0 0 0 3px rgba(47, 91, 136, 0.1);
            outline: none;
        }
        
        .timer {
            color: #718096;
            font-size: 0.9rem;
            margin: 1rem 0;
        }
        
        .resend-btn {
            background: none;
            border: 1px solid var(--brand);
            color: var(--brand);
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 1rem;
        }
        
        .resend-btn:hover:not(:disabled) {
            background: var(--brand);
            color: white;
        }
        
        .resend-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <main class="auth-wrap">
        <div class="verify-container">
            <h2 style="color: var(--brand); margin-bottom: 1rem;">Email Verification</h2>
            <p style="color: #666; margin-bottom: 2rem;">
                Enter the 6-digit code we sent to your email
            </p>
            
            <form id="verifyForm">
                <div class="code-inputs">
                    <input type="text" class="code-input" maxlength="1" pattern="[0-9]" required>
                    <input type="text" class="code-input" maxlength="1" pattern="[0-9]" required>
                    <input type="text" class="code-input" maxlength="1" pattern="[0-9]" required>
                    <input type="text" class="code-input" maxlength="1" pattern="[0-9]" required>
                    <input type="text" class="code-input" maxlength="1" pattern="[0-9]" required>
                    <input type="text" class="code-input" maxlength="1" pattern="[0-9]" required>
                </div>
                
                <input type="hidden" id="email" value="">
                
                <button type="submit" class="btn-primary" style="width: 100%;">Verify Email</button>
                
                <div class="timer" id="timer">Resend code in <span id="countdown">600</span> seconds</div>
                <button type="button" class="resend-btn" id="resendBtn" disabled>Resend Code</button>
                
                <div class="error-message" id="errorMessage" style="margin-top: 1rem;"></div>
                <div class="loading" id="loadingMessage" style="margin-top: 1rem;">Verifying...</div>
            </form>
            
            <p style="margin-top: 2rem; font-size: 0.9rem;">
                <a href="/RADS-TOOLING/customer/register.php" style="color: var(--brand);">Back to Registration</a>
            </p>
        </div>
    </main>

    <script>
    // Auto-focus next input
    const inputs = document.querySelectorAll('.code-input');
    inputs.forEach((input, index) => {
        input.addEventListener('input', (e) => {
            if (e.target.value && index < inputs.length - 1) {
                inputs[index + 1].focus();
            }
        });
        
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && !e.target.value && index > 0) {
                inputs[index - 1].focus();
            }
        });
    });

    // Timer for resend
    let timeLeft = 180; // 3 minutes
    const timerElement = document.getElementById('countdown');
    const resendBtn = document.getElementById('resendBtn');
    const timerContainer = document.getElementById('timer');

    function updateTimer() {
        if (timeLeft > 0) {
            timeLeft--;
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            timerElement.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
        } else {
            clearInterval(timerInterval);
            timerContainer.style.display = 'none';
            resendBtn.disabled = false;
        }
    }

    const timerInterval = setInterval(updateTimer, 1000);

    // Get email from URL or session
    const urlParams = new URLSearchParams(window.location.search);
    const email = urlParams.get('email') || sessionStorage.getItem('verify_email');
    if (email) {
        document.getElementById('email').value = email;
    }

    // Verify form submission
    document.getElementById('verifyForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const code = Array.from(inputs).map(input => input.value).join('');
        if (code.length !== 6) {
            document.getElementById('errorMessage').textContent = 'Please enter all 6 digits';
            document.getElementById('errorMessage').style.display = 'block';
            return;
        }
        
        const errorDiv = document.getElementById('errorMessage');
        const loadingDiv = document.getElementById('loadingMessage');
        const submitBtn = e.target.querySelector('.btn-primary');
        
        errorDiv.style.display = 'none';
        loadingDiv.style.display = 'block';
        submitBtn.disabled = true;
        
        try {
            const response = await fetch('/RADS-TOOLING/backend/api/auth.php?action=verify_email', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                credentials: 'same-origin',
                body: JSON.stringify({
                    email: document.getElementById('email').value,
                    code: code
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                alert('Email verified successfully! You can now login.');
                window.location.href = '/RADS-TOOLING/customer/cust_login.php';
            } else {
                throw new Error(result.message || 'Verification failed');
            }
        } catch (error) {
            errorDiv.textContent = error.message;
            errorDiv.style.display = 'block';
        } finally {
            loadingDiv.style.display = 'none';
            submitBtn.disabled = false;
        }
    });

    // Resend code
    document.getElementById('resendBtn').addEventListener('click', async () => {
        const email = document.getElementById('email').value;
        if (!email) {
            alert('Email not found. Please register again.');
            return;
        }
        
        try {
            const response = await fetch('/RADS-TOOLING/backend/api/auth.php?action=resend_verification', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ email })
            });
            
            const result = await response.json();
            
            if (result.success) {
                alert('New verification code sent to your email!');
                // Reset timer
                timeLeft = 300;
                timerContainer.style.display = 'block';
                resendBtn.disabled = true;
            } else {
                alert(result.message || 'Failed to resend code');
            }
        } catch (error) {
            alert('Error resending code. Please try again.');
        }
    });
    </script>
</body>
</html>