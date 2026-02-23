<?php
session_start();

// Redirect if already logged in
if (isset($_SESSION['id_users'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>

<html class="light" lang="id">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Botanic - Masuk</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap"
        rel="stylesheet" />
    <!-- Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@100..700,0..1&amp;display=swap"
        rel="stylesheet" />
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap"
        rel="stylesheet" />
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#4b774d",
                        "background-light": "#f7f7f7",
                        "background-dark": "#171b17",
                    },
                    fontFamily: {
                        "display": ["Inter", "sans-serif"]
                    },
                    borderRadius: {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "full": "9999px"
                    },
                },
            },
        }
    </script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .checkbox-custom:checked {
            background-image: url("data:image/svg+xml,%3csvg viewBox='0 0 16 16' fill='white' xmlns='http://www.w3.org/2000/svg'%3e%3cpath d='M12.207 4.793a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0l-2-2a1 1 0 011.414-1.414L6.5 9.086l4.293-4.293a1 1 0 011.414 0z'/%3e%3c/svg%3e");
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

        .toast-icon {
            font-size: 20px;
        }
    </style>
</head>

<body class="bg-background-light dark:bg-background-dark min-h-screen flex items-center justify-center p-4">
    <!-- Toast Notification Container -->
    <div id="toastContainer"></div>

    <!-- Main Login Card -->
    <div
        class="w-full max-w-md bg-white dark:bg-[#1e231e] rounded-xl shadow-sm border border-primary/10 overflow-hidden">
        <!-- Brand Header Section -->
        <div class="pt-10 pb-6 px-8 flex flex-col items-center">
            <div class="flex items-center gap-2 mb-6">
                <div class="bg-primary/10 p-2 rounded-lg">
                    <span class="material-symbols-outlined text-primary text-3xl">account_balance</span>
                </div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-slate-100 tracking-tight">Botanic</h1>
            </div>
            <div class="text-center">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Selamat Datang</h2>
                <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Silakan masuk ke akun Botanic Anda</p>
            </div>
        </div>
        <!-- Login Form -->
        <form id="loginForm" class="px-8 pb-10 space-y-5">
            <!-- Username Field -->
            <div class="space-y-1.5">
                <label class="text-sm font-medium text-slate-700 dark:text-slate-300" for="username">Username</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                        <span class="material-symbols-outlined text-slate-400 text-[20px]">person</span>
                    </div>
                    <input
                        class="block w-full pl-11 pr-4 py-3 bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 placeholder:text-slate-400 focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all text-sm"
                        id="username" name="username" placeholder="username" required="" type="text" autocomplete="username" />
                </div>
            </div>
            <!-- Password Field -->
            <div class="space-y-1.5">
                <label class="text-sm font-medium text-slate-700 dark:text-slate-300" for="password">Password</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                        <span class="material-symbols-outlined text-slate-400 text-[20px]">lock</span>
                    </div>
                    <input
                        class="block w-full pl-11 pr-12 py-3 bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-slate-700 rounded-lg text-slate-900 dark:text-slate-100 placeholder:text-slate-400 focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all text-sm"
                        id="password" name="password" placeholder="••••••••" required="" type="password" autocomplete="current-password" />
                    <button
                        class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
                        type="button" id="togglePassword">
                        <span class="material-symbols-outlined text-[20px]">visibility</span>
                    </button>
                </div>
            </div>
            <!-- Remember Me -->
            <div class="flex items-center justify-between py-1">
                <label class="flex items-center gap-2 cursor-pointer group">
                    <input
                        class="checkbox-custom h-4 w-4 rounded border-slate-300 dark:border-slate-600 text-primary focus:ring-primary focus:ring-offset-0 bg-transparent"
                        type="checkbox" id="remember" />
                    <span
                        class="text-sm text-slate-600 dark:text-slate-400 group-hover:text-slate-900 dark:group-hover:text-slate-200 transition-colors">Ingat
                        Saya</span>
                </label>
            </div>
            <!-- Login Button -->
            <button
                id="btnLogin"
                class="w-full bg-primary hover:bg-primary/90 text-white font-semibold py-3.5 rounded-lg shadow-sm shadow-primary/20 flex items-center justify-center gap-2 transition-all active:scale-[0.98]"
                type="submit">
                Masuk
                <span class="material-symbols-outlined text-[18px]">login</span>
            </button>
        </form>
    </div>

    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('span');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.textContent = 'visibility_off';
            } else {
                passwordInput.type = 'password';
                icon.textContent = 'visibility';
            }
        });

        // Toast notification function
        function showToast(message, type = 'success') {
            const toastContainer = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;

            const icon = type === 'success' ? 'check_circle' : 'error';
            toast.innerHTML = `
                <span class="material-symbols-outlined toast-icon">${icon}</span>
                <span class="toast-message">${message}</span>
            `;

            toastContainer.appendChild(toast);

            setTimeout(() => toast.classList.add('show'), 10);

            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        // Handle login form submission
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            const remember = document.getElementById('remember').checked;

            if (!username || !password) {
                showToast('Username dan password wajib diisi', 'error');
                return;
            }

            const btnLogin = document.getElementById('btnLogin');
            const originalContent = btnLogin.innerHTML;
            btnLogin.disabled = true;
            btnLogin.innerHTML = '<span class="material-symbols-outlined text-[18px] animate-spin">progress_activity</span><span>Memproses...</span>';

            try {
                const formData = new FormData();
                formData.append('username', username);
                formData.append('password', password);

                const response = await fetch('http://localhost/botanic/api/users/cek_login.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    showToast(result.message);
                    
                    // Store user data in session
                    sessionStorage.setItem('id_users', result.data.id_users);
                    sessionStorage.setItem('username', result.data.username);
                    
                    if (remember) {
                        localStorage.setItem('id_users', result.data.id_users);
                        localStorage.setItem('username', result.data.username);
                    }

                    // Redirect to index.php after short delay
                    setTimeout(() => {
                        window.location.href = 'index.php';
                    }, 1000);
                } else {
                    showToast(result.message, 'error');
                    btnLogin.disabled = false;
                    btnLogin.innerHTML = originalContent;
                }
            } catch (error) {
                showToast('Terjadi kesalahan. Silakan coba lagi.', 'error');
                btnLogin.disabled = false;
                btnLogin.innerHTML = originalContent;
            }
        });

        // Check if user is already logged in via localStorage
        window.addEventListener('DOMContentLoaded', function() {
            const storedUsername = localStorage.getItem('username');
            if (storedUsername) {
                document.getElementById('username').value = storedUsername;
                document.getElementById('remember').checked = true;
            }
        });
    </script>
</body>

</html>
