// File: /assets/JS/register.js
// CUSTOMER REGISTRATION WITH EMAIL VERIFICATION

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('registerForm');
    const errorDiv = document.getElementById('errorMessage');
    const loadingDiv = document.getElementById('loadingMessage');
    const submitBtn = form.querySelector('.btn-primary');

    // Form submission
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        // Reset states
        errorDiv.style.display = 'none';
        loadingDiv.style.display = 'block';
        submitBtn.disabled = true;
        
        // Get form data
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());
        
        // Validate passwords match
        if (data.password !== data.confirm_password) {
            showError('Passwords do not match');
            return;
        }
        
        // Validate password length
        if (data.password.length < 6) {
            showError('Password must be at least 6 characters long');
            return;
        }
        
        // Validate phone number format (Philippine format: +63XXXXXXXXX - 11 digits total)
        const phoneRegex = /^\+63\d{10}$/;
        if (!phoneRegex.test(data.phone)) {
            showError('Invalide Phone Number');
            return;
        }
        
        // Add audience for customer registration
        data.audience = 'customer';
        
        try {
            const res = await fetch('/RADS-TOOLING/backend/api/auth.php?action=register', {
                method: 'POST', 
                headers: {'Content-Type':'application/json'}, 
                credentials:'same-origin',
                body: JSON.stringify(data)
            });
            
            const result = await res.json();
            
            if (result.success) {
                // Store email for verification page
                sessionStorage.setItem('verify_email', data.email);
                
                // Show success and redirect to verify page
                alert('Account created successfully! Please check your email for verification code.');
                window.location.href = `/RADS-TOOLING/customer/verify.php?email=${encodeURIComponent(data.email)}`;
            } else {
                throw new Error(result.message || 'Registration failed');
            }
            
        } catch (err) { 
            console.error('Registration error:', err);
            showError(err.message || 'Registration failed. Please try again.');
        } finally {
            loadingDiv.style.display = 'none';
            submitBtn.disabled = false;
        }
    });

    // Helper function to show errors
    function showError(message) {
        errorDiv.textContent = message;
        errorDiv.style.display = 'block';
        loadingDiv.style.display = 'none';
        submitBtn.disabled = false;
    }

    // Helper function to clear error
    function clearError() {
        errorDiv.style.display = 'none';
        errorDiv.textContent = '';
    }

    // Password strength indicator
    const passwordInput = document.getElementById('password');
    const strengthContainer = document.querySelector('.password-strength');
    const strengthBar = document.querySelector('.password-strength-bar');
    
    passwordInput.addEventListener('input', function() {
        const password = this.value;
        
        if (password.length === 0) {
            strengthContainer.classList.remove('show');
            return;
        }
        
        strengthContainer.classList.add('show');
        
        // Calculate strength
        let strength = 0;
        if (password.length >= 8) strength++;
        if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
        if (password.match(/[0-9]/)) strength++;
        if (password.match(/[^a-zA-Z0-9]/)) strength++;
        
        // Update bar
        strengthBar.className = 'password-strength-bar';
        if (strength <= 1) {
            strengthBar.classList.add('weak');
        } else if (strength === 2 || strength === 3) {
            strengthBar.classList.add('medium');
        } else {
            strengthBar.classList.add('strong');
        }
    });

    // Real-time password confirmation validation
    document.getElementById('confirmPassword').addEventListener('input', function() {
        const password = document.getElementById('password').value;
        const confirmPassword = this.value;
        const formGroup = this.closest('.form-group');
        
        if (confirmPassword) {
            if (password === confirmPassword) {
                formGroup.classList.remove('has-error');
                formGroup.classList.add('has-success');
            } else {
                formGroup.classList.remove('has-success');
                formGroup.classList.add('has-error');
            }
        } else {
            formGroup.classList.remove('has-success', 'has-error');
        }
    });

    // Email validation and check for existing email
    let emailTimeout;
    document.getElementById('email').addEventListener('input', function() {
        const email = this.value.trim();
        const formGroup = this.closest('.form-group');
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        // Clear any existing error for email
        if (errorDiv.textContent.includes('Email')) {
            clearError();
        }
        
        if (!email) {
            formGroup.classList.remove('has-success', 'has-error');
            return;
        }
        
        if (!emailRegex.test(email)) {
            formGroup.classList.remove('has-success');
            formGroup.classList.add('has-error');
            return;
        }
        
        // Check if email exists (debounced)
        clearTimeout(emailTimeout);
        emailTimeout = setTimeout(async () => {
            try {
                const res = await fetch('/RADS-TOOLING/backend/api/auth.php?action=check_email', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    credentials: 'same-origin',
                    body: JSON.stringify({ email })
                });
                const result = await res.json();
                
                if (result.data && result.data.available) {
                    formGroup.classList.remove('has-error');
                    formGroup.classList.add('has-success');
                } else {
                    formGroup.classList.remove('has-success');
                    formGroup.classList.add('has-error');
                    showError('Email is already registered');
                }
            } catch (err) {
                console.error('Email check failed:', err);
            }
        }, 500);
    });

    // Phone number validation
    document.getElementById('phone').addEventListener('input', function() {
        const phone = this.value.trim();
        const formGroup = this.closest('.form-group');
        const phoneRegex = /^\+63\d{10}$/;
        
        // Clear phone-related errors when typing
        if (errorDiv.textContent.includes('Phone number')) {
            clearError();
        }
        
        if (!phone) {
            formGroup.classList.remove('has-success', 'has-error');
            return;
        }
        
        if (phoneRegex.test(phone)) {
            formGroup.classList.remove('has-error');
            formGroup.classList.add('has-success');
        } else {
            formGroup.classList.remove('has-success');
            formGroup.classList.add('has-error');
        }
    });

    // Username availability check
    let usernameTimeout;
    document.getElementById('username').addEventListener('input', function() {
        const username = this.value.trim();
        const formGroup = this.closest('.form-group');
        
        // Clear username-related errors when typing
        if (errorDiv.textContent === 'Username is already taken') {
            clearError();
        }
        
        if (username.length < 3) {
            formGroup.classList.remove('has-success', 'has-error');
            return;
        }
        
        clearTimeout(usernameTimeout);
        usernameTimeout = setTimeout(async () => {
            try {
                const res = await fetch('/RADS-TOOLING/backend/api/auth.php?action=check_username', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    credentials: 'same-origin',
                    body: JSON.stringify({ username })
                });
                const result = await res.json();
                
                if (result.data && result.data.available) {
                    formGroup.classList.remove('has-error');
                    formGroup.classList.add('has-success');
                } else {
                    formGroup.classList.remove('has-success');
                    formGroup.classList.add('has-error');
                    showError('Username is already taken');
                }
            } catch (err) {
                console.error('Username check failed:', err);
            }
        }, 500);
    });

    // Basic field validation
    document.querySelectorAll('input[required]').forEach(input => {
        if (!['password', 'confirmPassword', 'email', 'username', 'phone'].includes(input.id)) {
            input.addEventListener('blur', function() {
                const formGroup = this.closest('.form-group');
                if (this.value.trim()) {
                    formGroup.classList.remove('has-error');
                    formGroup.classList.add('has-success');
                } else {
                    formGroup.classList.remove('has-success');
                }
            });
        }
    });
});