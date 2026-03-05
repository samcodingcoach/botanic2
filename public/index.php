<?php
session_start();

// Check if user or guest is logged in
$isUser = isset($_SESSION['id_users']) && isset($_SESSION['username']);
$isGuest = isset($_SESSION['id_guest']) && isset($_SESSION['nama_lengkap']);

if (!$isUser && !$isGuest) {
    header('Location: login.php');
    exit;
}

// Get display name based on session type
$displayName = $isUser ? $_SESSION['username'] : $_SESSION['nama_lengkap'];
$userId = $isUser ? $_SESSION['id_users'] : $_SESSION['id_guest'];
$userType = $isUser ? 'User' : 'Guest';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Dashboard - SecureApp</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@100..700,0..1&amp;display=swap" rel="stylesheet" />
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
</head>

<body class="bg-background-light dark:bg-background-dark font-display text-slate-900 dark:text-slate-100 min-h-screen">
    <div class="relative flex h-full min-h-screen w-full flex-col">
        <!-- Top Navigation -->
        <header class="bg-white dark:bg-slate-800 shadow-sm border-b border-slate-200 dark:border-slate-700">
            <div class="flex items-center justify-between px-6 py-4">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-primary text-3xl">shield_lock</span>
                    <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100">SecureApp</h1>
                </div>
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-slate-500">account_circle</span>
                        <span class="text-sm font-medium text-slate-700 dark:text-slate-300"><?php echo htmlspecialchars($displayName); ?></span>
                    </div>
                    <a href="logout.php" class="bg-red-500 hover:bg-red-600 text-white text-sm font-semibold px-4 py-2 rounded-lg transition-colors flex items-center gap-2">
                        <span class="material-symbols-outlined text-sm">logout</span>
                        Logout
                    </a>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 px-6 py-8">
            <div class="max-w-4xl mx-auto">
                <!-- Welcome Card -->
                <div class="bg-primary/10 dark:bg-primary/20 rounded-xl border border-primary/20 p-8 mb-6">
                    <h2 class="text-2xl font-bold text-slate-900 dark:text-slate-100 mb-2">Welcome, <?php echo htmlspecialchars($displayName); ?>!</h2>
                    <p class="text-slate-600 dark:text-slate-400">You have successfully logged in to the system.</p>
                </div>

                <!-- Info Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6">
                        <div class="flex items-center gap-3 mb-3">
                            <span class="material-symbols-outlined text-primary">person</span>
                            <h3 class="font-semibold text-slate-900 dark:text-slate-100"><?php echo $userType; ?> ID</h3>
                        </div>
                        <p class="text-2xl font-bold text-slate-700 dark:text-slate-300"><?php echo $userId; ?></p>
                    </div>

                    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6">
                        <div class="flex items-center gap-3 mb-3">
                            <span class="material-symbols-outlined text-green-500">check_circle</span>
                            <h3 class="font-semibold text-slate-900 dark:text-slate-100">Status</h3>
                        </div>
                        <p class="text-lg font-medium text-green-600 dark:text-green-400">Active Session</p>
                    </div>

                    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6">
                        <div class="flex items-center gap-3 mb-3">
                            <span class="material-symbols-outlined text-blue-500">security</span>
                            <h3 class="font-semibold text-slate-900 dark:text-slate-100">Security</h3>
                        </div>
                        <p class="text-lg font-medium text-slate-600 dark:text-slate-400">Protected Page</p>
                    </div>
                </div>

                <!-- Session Info -->
                <div class="mt-6 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6">
                    <h3 class="font-bold text-lg text-slate-900 dark:text-slate-100 mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">info</span>
                        Session Information
                    </h3>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between py-2 border-b border-slate-200 dark:border-slate-700">
                            <span class="text-slate-500 dark:text-slate-400"><?php echo $isUser ? 'Username' : 'Name'; ?></span>
                            <span class="font-medium text-slate-900 dark:text-slate-100"><?php echo htmlspecialchars($displayName); ?></span>
                        </div>
                        <div class="flex justify-between py-2 border-b border-slate-200 dark:border-slate-700">
                            <span class="text-slate-500 dark:text-slate-400"><?php echo $userType; ?> ID</span>
                            <span class="font-medium text-slate-900 dark:text-slate-100"><?php echo $userId; ?></span>
                        </div>
                        <?php if ($isGuest): ?>
                        <div class="flex justify-between py-2 border-b border-slate-200 dark:border-slate-700">
                            <span class="text-slate-500 dark:text-slate-400">Email</span>
                            <span class="font-medium text-slate-900 dark:text-slate-100"><?php echo htmlspecialchars($_SESSION['email']); ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="flex justify-between py-2">
                            <span class="text-slate-500 dark:text-slate-400">Session Active</span>
                            <span class="font-medium text-green-600 dark:text-green-400">Yes</span>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="border-t border-slate-200 dark:border-slate-700 py-4 px-6">
            <p class="text-center text-sm text-slate-500 dark:text-slate-400">
                &copy; <?php echo date('Y'); ?> SecureApp. All rights reserved.
            </p>
        </footer>
    </div>
</body>

</html>
