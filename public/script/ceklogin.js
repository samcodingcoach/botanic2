// Login attempt tracking
const MAX_ATTEMPTS = 3;
const FREEZE_DURATION = 60000; // 1 minute in milliseconds

// Get stored attempts from localStorage
function getLoginAttempts() {
    const data = localStorage.getItem('loginAttempts');
    if (!data) return { count: 0, freezeUntil: 0 };
    return JSON.parse(data);
}

// Save login attempts to localStorage
function saveLoginAttempts(attempts) {
    localStorage.setItem('loginAttempts', JSON.stringify(attempts));
}

// Check if login is frozen
function isFrozen() {
    const attempts = getLoginAttempts();
    return attempts.freezeUntil > Date.now();
}

// Get freeze remaining time
function getFreezeRemaining() {
    const attempts = getLoginAttempts();
    const remaining = attempts.freezeUntil - Date.now();
    return remaining > 0 ? remaining : 0;
}

// Reset login attempts
function resetLoginAttempts() {
    saveLoginAttempts({ count: 0, freezeUntil: 0 });
}

// Increment login attempts and check if should freeze
function incrementLoginAttempts() {
    const attempts = getLoginAttempts();
    attempts.count++;
    if (attempts.count >= MAX_ATTEMPTS) {
        attempts.freezeUntil = Date.now() + FREEZE_DURATION;
    }
    saveLoginAttempts(attempts);
    return attempts.count;
}

// Toast notification function
function showToast(message, type = 'success') {
    const toastContainer = document.getElementById('toastContainer');
    if (!toastContainer) return;
    
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;

    const icon = type === 'success' ? 'check_circle' : (type === 'warning' ? 'warning' : 'error');
    toast.innerHTML = `
        <span class="material-symbols-outlined text-[20px]">${icon}</span>
        <span class="toast-message">${message}</span>
    `;

    toastContainer.appendChild(toast);

    setTimeout(() => toast.classList.add('show'), 10);

    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Update button with countdown
function updateButtonCountdown() {
    const submitBtn = document.getElementById('submit-btn');
    if (!submitBtn) return;
    
    const remaining = getFreezeRemaining();

    if (remaining > 0) {
        const seconds = Math.ceil(remaining / 1000);
        submitBtn.disabled = true;
        submitBtn.textContent = `Wait ${seconds}s...`;
        submitBtn.classList.add('opacity-50', 'cursor-not-allowed');

        setTimeout(updateButtonCountdown, 1000);
    } else {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Sign In';
        submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        resetLoginAttempts();
    }
}

// Initialize login functionality
function initLogin() {
    const passwordInput = document.getElementById('password');
    const toggleBtn = document.getElementById('toggle-password');
    const submitBtn = document.getElementById('submit-btn');
    const loginForm = document.getElementById('login-form');

    if (!passwordInput || !toggleBtn || !submitBtn || !loginForm) return;

    // Check if frozen on page load
    if (isFrozen()) {
        updateButtonCountdown();
    }

    // Password visibility toggle
    toggleBtn.addEventListener('click', function() {
        const isPassword = passwordInput.type === 'password';
        passwordInput.type = isPassword ? 'text' : 'password';
        toggleBtn.textContent = isPassword ? 'visibility_off' : 'visibility';
    });

    // Form submission handler
    loginForm.addEventListener('submit', async function(e) {
        e.preventDefault();

        const username = document.getElementById('username').value.trim();
        const password = document.getElementById('password').value;

        // Validation: Username cannot be empty
        if (!username) {
            showToast('Username required', 'error');
            return;
        }

        // Validation: Password minimum 8 characters
        if (password.length < 8) {
            showToast('Min. 8 characters', 'warning');
            return;
        }

        // Check if frozen
        if (isFrozen()) {
            const remaining = getFreezeRemaining();
            const seconds = Math.ceil(remaining / 1000);
            showToast(`Try again in ${seconds}s`, 'error');
            return;
        }

        // Disable button during submission
        submitBtn.disabled = true;
        submitBtn.textContent = 'Signing in...';

        const formData = new FormData(loginForm);
        const data = Object.fromEntries(formData.entries());

        try {
            const response = await fetch('../api/guest/cek_login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams(data).toString(),
                credentials: 'include'
            });

            const result = await response.json();

            if (result.success) {
                resetLoginAttempts();
                showToast('Login successful!', 'success');
                setTimeout(() => {
                    window.location.href = 'index.php';
                }, 1000);
            } else {
                const attemptCount = incrementLoginAttempts();

                if (attemptCount >= MAX_ATTEMPTS) {
                    showToast('Too many attempts. Wait 60s.', 'error');
                    updateButtonCountdown();
                } else {
                    const remainingAttempts = MAX_ATTEMPTS - attemptCount;
                    showToast(`${result.message}. ${remainingAttempts} left`, 'error');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Sign In';
                }
            }
        } catch (error) {
            const attemptCount = incrementLoginAttempts();

            if (attemptCount >= MAX_ATTEMPTS) {
                showToast('Too many attempts. Wait 60s.', 'error');
                updateButtonCountdown();
            } else {
                const remainingAttempts = MAX_ATTEMPTS - attemptCount;
                showToast(`Error. ${remainingAttempts} left`, 'error');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Sign In';
            }
        }
    });
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', initLogin);
