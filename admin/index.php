<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['id_users'])) {
    header('Location: login.php');
    exit;
}

$username = $_SESSION['username'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Admin Panel - Dashboard</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&amp;display=swap"
        rel="stylesheet" />
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap"
        rel="stylesheet" />
    <link rel="stylesheet" href="css/style.css"/>
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
</head>

<body class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-slate-100 font-display">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <?php include __DIR__ . '/sidebar.php'; ?>
        <!-- Main Content -->
        <main class="flex-1 flex flex-col overflow-hidden bg-background-light dark:bg-background-dark">
            <!-- Header -->
            <header
                class="bg-white dark:bg-background-dark border-b border-slate-200 dark:border-slate-800 px-4 md:px-8 py-4 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <!-- Hamburger Menu (Mobile) -->
                    <button onclick="toggleSidebar()"
                        class="md:hidden p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                        <span class="material-symbols-outlined">menu</span>
                    </button>
                    <h2 class="text-lg font-semibold text-slate-800 dark:text-white">Dashboard</h2>
                </div>
                <div class="flex items-center gap-4">
                    <span id="displayUsername" class="text-sm text-slate-600 dark:text-slate-400"></span>
                    <button id="btnLogout" class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" title="Logout">
                        <span class="material-symbols-outlined text-slate-600 dark:text-slate-400">logout</span>
                    </button>
                </div>
            </header>
            <!-- Dashboard Content -->
            <div class="flex-1 overflow-y-auto p-4 md:p-8 space-y-8">
                <!-- Welcome Section -->
                <div class="bg-white dark:bg-background-dark rounded-xl border border-slate-200 dark:border-slate-800 p-6 md:p-8">
                    <h3 class="text-2xl md:text-3xl font-black text-slate-900 dark:text-white tracking-tight">Selamat Datang, <span id="welcomeUsername"></span>!</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-2">Anda telah berhasil masuk ke panel admin Botanic.</p>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Get username from sessionStorage
        const username = sessionStorage.getItem('username') || '';
        
        // Display username
        document.getElementById('displayUsername').textContent = username;
        document.getElementById('welcomeUsername').textContent = username;

        // Handle logout
        document.getElementById('btnLogout').addEventListener('click', function() {
            sessionStorage.removeItem('id_users');
            sessionStorage.removeItem('username');
            localStorage.removeItem('id_users');
            localStorage.removeItem('username');
            window.location.href = 'login.php';
        });
    </script>
</body>

</html>
