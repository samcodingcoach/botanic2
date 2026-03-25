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
    <title>Botanic Groups - Branches</title>
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
        /* Hide scrollbar for horizontal scroll */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
    <link rel="stylesheet" href="css/index.css" />
</head>

<body class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-slate-100">
    <div class="relative flex min-h-screen flex-col overflow-x-hidden">
        <!-- Header & Search Section -->
        <header class="sticky top-0 z-10 bg-background-light/80 dark:bg-background-dark/80 backdrop-blur-md px-4 pt-4 pb-2">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-3xl">hotel</span>
                    <h1 class="text-xl font-bold tracking-tight">Botanic Groups</h1>
                </div>
                <!-- Account Dropdown -->
                <div class="account-dropdown">
                    <button id="account-btn" onclick="toggleDropdown()" class="p-2 rounded-full hover:bg-primary/10">
                        <span class="material-symbols-outlined">account_circle</span>
                    </button>
                    <div id="dropdown-menu" class="dropdown-menu">
                        <a href="#">
                            <span class="material-symbols-outlined text-lg">person</span>
                            <span>Account</span>
                        </a>
                        <a href="#" id="logout-btn">
                            <span class="material-symbols-outlined text-lg">logout</span>
                            <span>Logout</span>
                        </a>
                    </div>
                </div>
            </div>
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                    <span class="material-symbols-outlined text-slate-400 group-focus-within:text-primary">search</span>
                </div>
                <input id="search-input"
                    class="block w-full p-4 pl-12 text-sm border-none rounded-xl bg-slate-200/50 dark:bg-slate-800/50 focus:ring-2 focus:ring-primary focus:bg-white dark:focus:bg-slate-900 transition-all placeholder:text-slate-500"
                    placeholder="Search branch botanic hotels..." type="text" />
                <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                    <button class="p-1 rounded-lg hover:bg-primary/10">
                        <span class="material-symbols-outlined text-slate-400">tune</span>
                    </button>
                </div>
            </div>
            <!-- Quick Filters -->
            <div class="flex gap-2 py-3 overflow-x-auto no-scrollbar">
                <button class="filter-btn active flex h-9 shrink-0 items-center justify-center gap-x-2 rounded-full bg-primary text-white px-4 text-sm font-medium" data-filter="all">
                    All Branches
                </button>
                <button class="filter-btn flex h-9 shrink-0 items-center justify-center gap-x-2 rounded-full bg-slate-200 dark:bg-slate-800 px-4 text-sm font-medium" data-filter="near">
                    Near Me
                </button>
            </div>
        </header>

        <!-- Listing Body -->
        <main class="flex-1 px-4 py-2 space-y-4">
            <!-- Loading State -->
            <div id="loading" class="flex flex-col items-center justify-center py-12">
                <div class="spinner w-10 h-10 mb-4"></div>
                <p class="text-slate-500 dark:text-slate-400">Loading branches...</p>
            </div>

            <!-- Error State -->
            <div id="error" class="hidden flex-col items-center justify-center py-12">
                <span class="material-symbols-outlined text-red-500 text-5xl mb-4">error</span>
                <p class="text-slate-500 dark:text-slate-400 text-center" id="error-message"></p>
                <button onclick="loadBranches()" class="mt-4 px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary/90">
                    Retry
                </button>
            </div>

            <!-- Branches Container -->
            <div id="branches-container" class="space-y-4"></div>

            <!-- Your Stay Section -->
            <section id="your-stay-section" class="mt-12 mb-4">
                <div class="flex items-center justify-between mb-4 px-0">
                    <div>
                        <p class="text-slate-500 dark:text-slate-400 text-xs font-semibold uppercase tracking-wide mb-1">Journey Timeline</p>
                        <h2 class="text-lg font-bold">Your Stay Overview</h2>
                    </div>
                    <span id="stay-count" class="text-[10px] text-slate-500 dark:text-slate-400 font-medium mr-2">0 Stays</span>
                </div>
                <div id="stay-loading" class="flex flex-col items-center justify-center py-8">
                    <div class="spinner w-8 h-8 mb-3"></div>
                    <p class="text-slate-500 dark:text-slate-400 text-sm">Loading your stays...</p>
                </div>
                <div id="stay-error" class="hidden flex-col items-center justify-center py-8">
                    <span class="material-symbols-outlined text-red-500 text-4xl mb-2">error</span>
                    <p class="text-slate-500 dark:text-slate-400 text-sm text-center" id="stay-error-message"></p>
                </div>
                <div id="stay-container" class="space-y-4"></div>
                <div id="stay-empty" class="hidden flex-col items-center justify-center py-8">
                    <span class="material-symbols-outlined text-slate-400 text-4xl mb-2">event_busy</span>
                    <p class="text-slate-500 dark:text-slate-400 text-sm">No stays found</p>
                </div>
            </section>

            <!-- Follow Us Section -->
            <section id="follow-us-section" class="mt-8 mb-4 hidden">
                <div class="flex items-center justify-between mb-4 px-0">
                    <h2 class="text-lg font-bold">Follow us</h2>
                    <span id="halaman-count" class="text-[10px] text-slate-500 dark:text-slate-400 font-medium mr-2">0 Pages</span>
                </div>
                <div class="relative">
                    <!-- Gradient Overlay Left -->
                    <div class="absolute left-0 top-0 bottom-0 w-8 bg-gradient-to-r from-background-light dark:from-background-dark to-transparent pointer-events-none z-10"></div>
                    <!-- Gradient Overlay Right -->
                    <div class="absolute right-0 top-0 bottom-0 w-8 bg-gradient-to-l from-background-light dark:from-background-dark to-transparent pointer-events-none z-10"></div>
                    <div id="halaman-container" class="flex gap-4 overflow-x-auto no-scrollbar pb-2 px-0">
                        <!-- Halaman items will be loaded here -->
                    </div>
                </div>
            </section>
            <!-- Footer -->
            <footer class="py-6 text-center">
                <p class="text-xs text-slate-500 dark:text-slate-400">&copy; 2026 Botanic Groups</p>
                <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-1">Developed by Mahakam Dharma Perkasa</p>
            </footer>
            <div class="h-20"></div> <!-- Spacer for Bottom Nav -->
        </main>
    </div>

    <!-- Link Confirmation Modal -->
    <div id="link-modal" class="profile-modal">
        <div class="profile-modal-content">
            <!-- BottomSheetHandle -->
            <div class="flex h-5 w-full items-center justify-center pt-2">
                <div class="h-1.5 w-12 rounded-full bg-slate-300 dark:bg-slate-700"></div>
            </div>
            <!-- Modal Content -->
            <div class="px-6 py-4 text-center">
                <div class="w-16 h-16 rounded-full bg-primary/10 dark:bg-primary/20 flex items-center justify-center mx-auto mb-4">
                    <span class="material-symbols-outlined text-primary text-3xl">open_in_new</span>
                </div>
                <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100 mb-2">Open Link?</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">You will be redirected to an external link.</p>
                <div class="flex gap-3 px-6">
                    <button onclick="closeLinkModal()" class="flex-1 px-4 py-3 text-sm font-semibold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
                        No
                    </button>
                    <a href="#" id="confirm-link-btn" class="flex-1 px-4 py-3 text-sm font-bold text-white bg-primary hover:bg-primary/90 rounded-lg transition-colors flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-sm">check</span>
                        Yes
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Profile Modal -->
    <div id="profile-modal" class="profile-modal">
        <div class="profile-modal-content">
            <!-- BottomSheetHandle -->
            <div class="flex h-5 w-full items-center justify-center pt-2">
                <div class="h-1.5 w-12 rounded-full bg-slate-300 dark:bg-slate-700"></div>
            </div>
            <!-- Modal Header -->
            <div class="flex items-center justify-between pt-4 pb-2 px-6">
                <div class="text-left">
                    <h2 class="text-slate-900 dark:text-slate-100 text-xl font-bold leading-tight tracking-tight">
                        Profile Settings</h2>
                    <p class="text-primary font-medium text-xs">Update your personal information</p>
                </div>
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2 p-2.5 rounded-lg bg-primary/5 dark:bg-primary/10 border border-primary/20">
                        <span class="material-symbols-outlined text-primary text-sm">stars</span>
                        <div class="text-right">
                            <span id="profile-points" class="font-bold text-slate-900 dark:text-slate-100 text-xs">0</span>
                            <p class="text-[10px] text-slate-500 dark:text-slate-400 font-medium">Points</p>
                        </div>
                    </div>
                    <button onclick="closeProfileModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
            </div>
            <!-- Form Content -->
            <div class="flex-1 overflow-y-auto px-6 py-4 space-y-4">
                <!-- Full Name -->
                <div class="flex flex-col gap-1.5">
                    <label class="text-slate-600 dark:text-slate-400 text-xs font-semibold uppercase tracking-wider">Full Name</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm">person</span>
                        <input id="profile-nama" class="w-full pl-11 pr-4 py-3 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all outline-none text-sm" placeholder="Enter your full name" type="text" />
                    </div>
                </div>
                <!-- Email -->
                <div class="flex flex-col gap-1.5">
                    <label class="text-slate-600 dark:text-slate-400 text-xs font-semibold uppercase tracking-wider">Email Address</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm">mail</span>
                        <input id="profile-email" class="w-full pl-11 pr-4 py-3 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all outline-none text-sm" placeholder="Email" type="email" />
                    </div>
                </div>
                <!-- Whatsapp -->
                <div class="flex flex-col gap-1.5">
                    <label class="text-slate-600 dark:text-slate-400 text-xs font-semibold uppercase tracking-wider">Whatsapp Number</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm">call</span>
                        <input id="profile-wa" class="w-full pl-11 pr-4 py-3 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all outline-none text-sm" placeholder="Whatsapp number" type="text" />
                    </div>
                </div>
                <!-- City -->
                <div class="flex flex-col gap-1.5">
                    <label class="text-slate-600 dark:text-slate-400 text-xs font-semibold uppercase tracking-wider">City</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm">location_on</span>
                        <input id="profile-kota" class="w-full pl-11 pr-4 py-3 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all outline-none text-sm" placeholder="Your City" type="text" />
                    </div>
                </div>
                <!-- Password -->
                <div class="flex flex-col gap-1.5">
                    <label class="text-slate-600 dark:text-slate-400 text-xs font-semibold uppercase tracking-wider">Password</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm">lock</span>
                        <input id="profile-password" class="w-full pl-11 pr-12 py-3 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all outline-none text-sm" placeholder="••••••••" type="password" />
                        <button type="button" id="toggle-profile-password" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                            <span class="material-symbols-outlined text-sm">visibility</span>
                        </button>
                    </div>
                    <p class="text-[10px] text-slate-500 dark:text-slate-400">Required to verify your identity</p>
                </div>
            </div>
            <!-- Footer Action -->
            <div class="p-4 bg-white dark:bg-slate-900/50 border-t border-slate-100 dark:border-slate-800">
                <button id="btn-update-profile" class="w-full bg-primary hover:bg-primary/90 text-white font-bold py-3 rounded-lg shadow-lg shadow-primary/30 transition-all flex items-center justify-center gap-2 text-sm">
                    <span class="material-symbols-outlined text-sm">save</span>
                    Update Profile
                </button>
            </div>
        </div>
    </div>

    <!-- Logout Confirmation Modal -->
    <div id="logout-modal" class="profile-modal">
        <div class="profile-modal-content">
            <!-- BottomSheetHandle -->
            <div class="flex h-5 w-full items-center justify-center pt-2">
                <div class="h-1.5 w-12 rounded-full bg-slate-300 dark:bg-slate-700"></div>
            </div>
            <!-- Modal Content -->
            <div class="px-6 py-4 text-center">
                <div class="w-16 h-16 rounded-full bg-red-100 dark:bg-red-900/20 flex items-center justify-center mx-auto mb-4">
                    <span class="material-symbols-outlined text-red-500 text-3xl">logout</span>
                </div>
                <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100 mb-2">Logout?</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">Are you sure you want to logout from your account?</p>
                <div class="flex gap-3 px-6">
                    <button onclick="closeLogoutModal()" class="flex-1 px-4 py-3 text-sm font-semibold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
                        Cancel
                    </button>
                    <a href="logout.php" id="confirm-logout-btn" class="flex-1 px-4 py-3 text-sm font-bold text-white bg-red-500 hover:bg-red-600 rounded-lg transition-colors flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-sm">logout</span>
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="script/index.js"></script>
    <script>
        // Pass guest ID from session to JavaScript
        <?php if ($isGuest): ?>
        window.sessionGuestId = <?php echo (int) $_SESSION['id_guest']; ?>;
        <?php else: ?>
        window.sessionGuestId = null;
        <?php endif; ?>
    </script>
</body>

</html>
