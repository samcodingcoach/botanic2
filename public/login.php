<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Login - SecureApp</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@100..700,0..1&amp;display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet" />
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#135bec",
                        "background-light": "#f6f6f8",
                        "background-dark": "#101622",
                    },
                    fontFamily: {
                        "display": ["Inter"]
                    },
                    borderRadius: { "DEFAULT": "0.25rem", "lg": "0.5rem", "xl": "0.75rem", "full": "9999px" },
                },
            },
        }
    </script>
    <style>
        body {
            min-height: max(884px, 100dvh);
        }

        /* Toast Notification Styles */
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 18px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transform: translateX(400px);
            transition: transform 0.3s ease;
            z-index: 9999;
        }

        .toast.show {
            transform: translateX(0);
        }

        .toast-success {
            background-color: #10b981;
            color: white;
        }

        .toast-error {
            background-color: #ef4444;
            color: white;
        }

        .toast-warning {
            background-color: #f59e0b;
            color: white;
        }
    </style>
</head>

<body class="bg-background-light dark:bg-background-dark font-display text-slate-900 dark:text-slate-100 min-h-screen">
    <!-- Toast Notification Container -->
    <div id="toastContainer"></div>

    <div class="relative flex h-full min-h-screen w-full flex-col group/design-root overflow-x-hidden">
        <!-- Top App Bar / Header -->
        <div class="flex items-center p-4 pb-2 justify-between">
            <div class="text-slate-900 dark:text-slate-100 flex size-12 shrink-0 items-center justify-start">
            </div>
        </div>
        <!-- Logo/Hero Area -->
        <div class="@container px-4 py-3">
            <div class="w-full bg-primary/10 dark:bg-primary/20 flex flex-col items-center justify-center overflow-hidden rounded-xl min-h-[180px] border border-primary/20">
                <div class="bg-primary text-white p-4 rounded-xl shadow-lg mb-4">
                    <span class="material-symbols-outlined text-5xl">shield_lock</span>
                </div>
                <p class="text-primary font-bold text-xl tracking-wider">SECUREAPP</p>
            </div>
        </div>
        <!-- Login Title -->
        <div class="px-4 pt-6 pb-2">
            <h1 class="text-slate-900 dark:text-slate-100 tracking-tight text-3xl font-bold leading-tight">Welcome Back</h1>
            <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Please enter your credentials to continue</p>
        </div>

        <!-- Form Section -->
        <form id="login-form" class="flex flex-col gap-4 px-4 py-3">
            <!-- Username Input -->
            <label class="flex flex-col w-full">
                <p class="text-slate-700 dark:text-slate-300 text-sm font-semibold leading-normal pb-2">Username</p>
                <div class="relative">
                    <input id="username" name="username"
                        class="form-input flex w-full rounded-lg text-slate-900 dark:text-slate-100 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 h-14 placeholder:text-slate-400 p-[15px] focus:border-primary focus:ring-1 focus:ring-primary transition-colors"
                        placeholder="Enter your username" type="text" autocomplete="username" required />
                </div>
            </label>
            <!-- Password Input -->
            <label class="flex flex-col w-full">
                <div class="flex justify-between items-center pb-2">
                    <p class="text-slate-700 dark:text-slate-300 text-sm font-semibold leading-normal">Password</p>
                </div>
                <div class="flex w-full items-stretch rounded-lg group">
                    <input id="password" name="password"
                        class="form-input flex w-full min-w-0 flex-1 rounded-l-lg text-slate-900 dark:text-slate-100 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 h-14 placeholder:text-slate-400 p-[15px] border-r-0 focus:border-primary focus:ring-0 transition-colors"
                        placeholder="••••••••" type="password" autocomplete="current-password" required />
                    <div class="text-slate-400 flex border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 items-center justify-center pr-4 rounded-r-lg border-l-0 group-focus-within:border-primary">
                        <span id="toggle-password" class="material-symbols-outlined cursor-pointer hover:text-primary">visibility</span>
                    </div>
                </div>
            </label>
            <!-- Action Button -->
            <div class="px-0 py-3">
                <button id="submit-btn" type="submit"
                    class="w-full bg-primary hover:bg-primary/90 text-white font-bold py-4 rounded-lg shadow-md transition-all active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed">
                    Sign In
                </button>
            </div>
        </form>
    </div>

    <script>
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

        // Password visibility toggle
        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('password');
            const toggleBtn = document.getElementById('toggle-password');
            const submitBtn = document.getElementById('submit-btn');
            
            // Check if frozen on page load
            if (isFrozen()) {
                updateButtonCountdown();
            }

            toggleBtn.addEventListener('click', function() {
                const isPassword = passwordInput.type === 'password';
                passwordInput.type = isPassword ? 'text' : 'password';
                toggleBtn.textContent = isPassword ? 'visibility_off' : 'visibility';
            });

            // Form submission handler
            const loginForm = document.getElementById('login-form');

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
        });
    </script>
</body>

</html>
