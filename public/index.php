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
            <section id="your-stay-section" class="mt-12 mb-4 py-2">
                <div class="flex items-center justify-between mb-4 px-0">
                    <div>
                        <p class="text-slate-500 dark:text-slate-400 text-xs font-semibold uppercase tracking-wide mb-1">Journey Timeline</p>
                        <h2 class="text-lg font-bold">Your Stay Overview</h2>
                    </div>
                    <button id="view-all-stay" class="text-[10px] text-slate-500 dark:text-slate-400 font-medium hover:text-primary transition-colors mr-2">
                        View All
                    </button>
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

            <!-- House Rules Section -->
            <section id="house-rules-section" class="mt-8 mb-4">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="text-slate-500 dark:text-slate-400 text-xs font-semibold uppercase tracking-wide mb-1">Guest Agreement</p>
                        <h2 class="text-lg font-bold">House Rules & Regulations</h2>
                    </div>
                    <span id="rules-count" class="text-[10px] text-slate-500 dark:text-slate-400 font-medium mr-2">0 Rules</span>
                </div>
                
                <!-- Loading State -->
                <div id="rules-loading" class="flex flex-col items-center justify-center py-8">
                    <div class="spinner w-8 h-8 mb-3"></div>
                    <p class="text-slate-500 dark:text-slate-400 text-sm">Loading house rules...</p>
                </div>
                
                <!-- Error State -->
                <div id="rules-error" class="hidden flex-col items-center justify-center py-8">
                    <span class="material-symbols-outlined text-red-500 text-4xl mb-2">error</span>
                    <p class="text-slate-500 dark:text-slate-400 text-sm text-center" id="rules-error-message"></p>
                </div>
                
                <!-- Rules Content -->
                <div id="rules-container" class="hidden space-y-4">
                    <!-- Hero Section -->
                    <div class="relative rounded-xl overflow-hidden h-48">
                        <img class="absolute inset-0 w-full h-full object-cover opacity-90"
                            src="https://lh3.googleusercontent.com/aida-public/AB6AXuAVAa0HzDnJOdh4RpQ8Dr0TGGzFPRu8bDTXnBhy_mD7Q1hD8G5oChMsVN0CK6SDMgSWTV1Jf9GgngjIPcgqSN9wNZ0t05O9GSGRg59S-oijeVbnFC2FqbFjBimfEBekQWN2dbM9aMspRaqnpSjFvlf88x96BFHy09JX6kDrHlV3srPaDkQVUtLOucAeVlQDxVVAsTMjKKIDUlGRvNIfxQg1pE9loN1MhnA3fbSt9_pkZZhzP-93wvxXeKbkR2L_cKOI1ccGtZoDhmiF"
                            alt="Hotel lobby" />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent flex flex-col justify-end p-6">
                            <span class="text-white/80 text-xs uppercase tracking-widest mb-1">Botanic Hotel</span>
                            <h3 class="text-white font-display text-2xl font-bold">Approval of overnight guests</h3>
                        </div>
                    </div>
                    
                    <!-- Tab Navigation -->
                    <nav id="rules-tabs" class="flex space-x-1 bg-slate-100 dark:bg-slate-800 p-1.5 rounded-full">
                        <!-- Tabs will be loaded here -->
                    </nav>
                    
                    <!-- Rules Content by Category -->
                    <div id="rules-content">
                        <!-- Content will be loaded here -->
                    </div>
                    
                    <!-- Info Note -->
                    <div class="mt-8 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-100 dark:border-blue-800 flex gap-3">
                        <span class="material-symbols-outlined text-blue-700 dark:text-blue-400 text-xl">info</span>
                        <p class="text-xs text-blue-800 dark:text-blue-300 leading-tight">By checking these items, you acknowledge that you have read and understood the residency protocols for this section.</p>
                    </div>
                </div>

                <!-- Bottom Navigation Bar (Fixed) -->
                <footer id="rules-footer" class="fixed bottom-0 left-0 right-0 bg-white dark:bg-slate-900 rounded-t-2xl z-[100] shadow-[0_-8px_24px_rgba(0,0,0,0.06)] border-t border-slate-100 dark:border-slate-800">
                    <div class="flex justify-around items-center px-4 py-3">
                        <!-- Decline Tab -->
                        <button id="btn-decline"
                            class="flex flex-col items-center justify-center text-slate-500 dark:text-slate-400 px-4 py-2 hover:opacity-90 active:scale-[0.98] duration-200">
                            <span class="material-symbols-outlined text-2xl mb-1" data-icon="cancel">cancel</span>
                            <span class="text-xs font-semibold">Decline</span>
                        </button>
                        <!-- I Agree Tab (Active) -->
                        <button id="btn-agree"
                            class="flex flex-col items-center justify-center bg-primary text-white rounded-full px-8 py-3 mx-2 w-full hover:opacity-90 active:scale-[0.98] duration-200">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-xl" data-icon="check_circle"
                                    style="font-variation-settings: 'FILL' 1;">check_circle</span>
                                <span class="text-sm font-bold tracking-wide">I Agree</span>
                            </div>
                        </button>
                    </div>
                </footer>
            </section>

            <!-- Follow Us Section -->
            <section id="follow-us-section" class="mt-8 mb-4">
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

    <!-- Stay Details Bottom Sheet -->
    <div id="stay-bs-overlay" class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-[2px] hidden"></div>
    <div id="stay-bottom-sheet" class="fixed bottom-0 left-0 right-0 z-50 bg-surface-container-lowest rounded-t-[24px] shadow-[0_-12px_40px_rgba(0,0,0,0.08)] transform transition-transform duration-500 translate-y-full max-h-[85vh] overflow-hidden flex flex-col">
        <!-- Handle -->
        <div class="flex justify-center pt-3 pb-1 flex-shrink-0">
            <div class="w-10 h-1 bg-slate-300 dark:bg-slate-700 rounded-full"></div>
        </div>
        <!-- Sheet Header -->
        <div class="px-4 py-3 flex items-center justify-between border-b border-slate-100 dark:border-slate-800 flex-shrink-0">
            <h2 class="font-display font-bold text-lg tracking-tight text-primary">Reservation Details</h2>
            <button onclick="closeStayBottomSheet()" class="p-2 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-full transition-colors">
                <span class="material-symbols-outlined text-slate-500">close</span>
            </button>
        </div>
        <div class="px-4 py-4 overflow-y-auto flex-1 pb-24">
            <!-- Property Hero Card -->
            <div class="relative w-full h-40 rounded-xl overflow-hidden mb-6 flex-shrink-0">
                <img id="bs-foto" alt="Property Image" class="w-full h-full object-cover" src="" />
                <div class="absolute inset-0 bg-gradient-to-t from-slate-900/60 to-transparent"></div>
                <div class="absolute bottom-3 left-3">
                    <span id="bs-status" class="bg-red-500 text-white px-2 py-0.5 rounded-full text-[9px] font-bold tracking-widest uppercase mb-1 inline-block">STAYING</span>
                    <h3 id="bs-nama-cabang" class="text-white font-display font-bold text-xl"></h3>
                </div>
            </div>
            <!-- Details Grid -->
            <div class="grid grid-cols-2 gap-y-6 gap-x-3">
                <!-- Room Type -->
                <div class="space-y-0.5">
                    <p class="text-[9px] font-bold tracking-widest text-slate-500 dark:text-slate-400 uppercase">Room Type</p>
                    <p id="bs-nama-tipe" class="font-display font-semibold text-slate-900 dark:text-slate-100 text-sm"></p>
                </div>
                <!-- Travel Agent -->
                <div class="space-y-0.5">
                    <p class="text-[9px] font-bold tracking-widest text-slate-500 dark:text-slate-400 uppercase">Travel Agent</p>
                    <p id="bs-ota" class="font-display font-semibold text-primary text-sm"></p>
                </div>
                <!-- Room Number -->
                <div class="space-y-0.5">
                    <p class="text-[9px] font-bold tracking-widest text-slate-500 dark:text-slate-400 uppercase">Room Number</p>
                    <p id="bs-nomor-kamar" class="font-display font-bold text-slate-900 dark:text-slate-100 text-base"></p>
                </div>
                <!-- Dates -->
                <div class="space-y-0.5">
                    <p class="text-[9px] font-bold tracking-widest text-slate-500 dark:text-slate-400 uppercase">Date Reservation</p>
                    <p id="bs-tanggal" class="font-display font-semibold text-slate-900 dark:text-slate-100 text-sm"></p>
                </div>
                <!-- Reservation Number -->
                <div class="space-y-0.5">
                    <p class="text-[9px] font-bold tracking-widest text-slate-500 dark:text-slate-400 uppercase">Reservation ID</p>
                    <p id="bs-kode-booking" class="font-body text-slate-500 dark:text-slate-400 text-xs"></p>
                </div>
                <!-- Status -->
                <div class="space-y-0.5">
                    <p class="text-[9px] font-bold tracking-widest text-slate-500 dark:text-slate-400 uppercase">Current Status</p>
                    <div class="flex items-center gap-1.5">
                        <div id="bs-status-dot" class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse"></div>
                        <p id="bs-status-text" class="font-display font-bold text-red-500 text-sm"></p>
                    </div>
                </div>
            </div>
            <!-- Concierge PIC -->
            <div class="mt-8 p-3 bg-slate-100 dark:bg-slate-800 rounded-xl flex items-center gap-3">
                <div class="w-10 h-10 rounded-full overflow-hidden border-2 border-primary flex-shrink-0 bg-slate-200 dark:bg-slate-700 flex items-center justify-center">
                    <span class="material-symbols-outlined text-primary text-lg">person</span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-[9px] font-bold tracking-widest text-slate-500 dark:text-slate-400 uppercase">Personal in charge</p>
                    <p id="bs-username" class="font-display font-bold text-slate-900 dark:text-slate-100 text-sm truncate"></p>
                </div>
                <button class="w-9 h-9 rounded-full bg-primary flex items-center justify-center text-white shadow-lg flex-shrink-0">
                    <span class="material-symbols-outlined text-base">chat</span>
                </button>
            </div>
            <!-- Action Button -->
            <div class="mt-6">
                <a id="bs-receipt-btn" href="#" target="_blank" class="w-full bg-[#2e7d32] hover:bg-[#1b5e20] text-white font-display font-bold py-3.5 rounded-full flex items-center justify-center gap-2 shadow-xl transition-all active:scale-95">
                    <span class="material-symbols-outlined text-base">download</span>
                    Download Receipt
                </a>
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

        // House Rules Section - Cookie Management (for future use)
        const COOKIE_NAME = 'guest_agreement_accepted';
        const COOKIE_EXPIRY_DAYS = 365;

        // Set cookie
        function setCookie(name, value, days) {
            const expires = new Date();
            expires.setTime(expires.getTime() + (days * 24 * 60 * 60 * 1000));
            document.cookie = `${name}=${value};expires=${expires.toUTCString()};path=/`;
        }

        // Get cookie
        function getCookie(name) {
            const nameEQ = name + '=';
            const ca = document.cookie.split(';');
            for (let i = 0; i < ca.length; i++) {
                let c = ca[i];
                while (c.charAt(0) === ' ') c = c.substring(1, c.length);
                if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
            }
            return null;
        }

        // Show toast notification
        function showToast(message, type = 'success') {
            const toastContainer = document.getElementById('toastContainer') || createToastContainer();
            const toast = document.createElement('div');
            toast.className = `fixed top-4 left-1/2 -translate-x-1/2 z-[200] px-6 py-3 rounded-lg shadow-lg flex items-center gap-2 transition-all duration-300 ${
                type === 'success' 
                    ? 'bg-green-500 text-white' 
                    : 'bg-red-500 text-white'
            }`;
            
            const icon = type === 'success' ? 'check_circle' : 'error';
            toast.innerHTML = `
                <span class="material-symbols-outlined">${icon}</span>
                <span class="text-sm font-semibold">${message}</span>
            `;
            
            toastContainer.appendChild(toast);
            
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translate(-50%, -20px)';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        // Create toast container if not exists
        function createToastContainer() {
            const container = document.createElement('div');
            container.id = 'toastContainer';
            document.body.appendChild(container);
            return container;
        }

        // House Rules Section
        let currentRulesCategory = 0;
        let rulesData = null;

        // Load house rules
        async function loadHouseRules() {
            const loading = document.getElementById('rules-loading');
            const error = document.getElementById('rules-error');
            const container = document.getElementById('rules-container');
            const errorMessage = document.getElementById('rules-error-message');

            try {
                const response = await fetch('../api/aturan/list.php');
                const result = await response.json();

                if (result.success && result.data) {
                    rulesData = result.data;
                    renderHouseRules(result);
                    loading.classList.add('hidden');
                    container.classList.remove('hidden');
                } else {
                    throw new Error(result.message || 'Failed to load house rules');
                }
            } catch (error) {
                console.error('Error loading house rules:', error);
                loading.classList.add('hidden');
                error.classList.remove('hidden');
                errorMessage.textContent = error.message;
            }
        }

        // Render house rules
        function renderHouseRules(result) {
            const tabsContainer = document.getElementById('rules-tabs');
            const contentContainer = document.getElementById('rules-content');
            const rulesCount = document.getElementById('rules-count');

            // Update rules count (sum of all categories)
            const totalRules = Object.values(result.data).reduce((sum, arr) => sum + arr.length, 0);
            rulesCount.textContent = `${totalRules} Rules`;

            // Render tabs
            tabsContainer.innerHTML = result.categories.map((cat, index) => `
                <button
                    onclick="switchRulesCategory(${cat.key})"
                    class="flex-1 py-2 px-4 rounded-full text-sm font-semibold transition-all ${index === 0 ? 'bg-white text-primary shadow-sm' : 'text-slate-500 hover:bg-white/50'}"
                    data-category="${cat.key}">
                    ${cat.label}
                </button>
            `).join('');

            // Render content for first category
            renderRulesContent(0);
            
            // Show rules container
            document.getElementById('rules-container').classList.remove('hidden');
        }

        // Switch rules category
        function switchRulesCategory(categoryKey) {
            currentRulesCategory = categoryKey;
            
            // Update tabs
            document.querySelectorAll('#rules-tabs button').forEach(btn => {
                const isActive = parseInt(btn.dataset.category) === categoryKey;
                btn.className = `flex-1 py-2 px-4 rounded-full text-sm font-semibold transition-all ${isActive ? 'bg-white text-primary shadow-sm' : 'text-slate-500 hover:bg-white/50'}`;
            });

            // Render content
            renderRulesContent(categoryKey);
        }

        // Render rules content
        function renderRulesContent(categoryKey) {
            const contentContainer = document.getElementById('rules-content');
            const rules = rulesData[categoryKey] || [];
            const categories = [
                { label: 'Ketentuan Check-in & Check-out', icon: 'schedule' },
                { label: 'Denda & Biaya Tambahan', icon: 'payments' },
                { label: 'Larangan Keras', icon: 'block' }
            ];
            const category = categories[categoryKey];

            if (rules.length === 0) {
                contentContainer.innerHTML = `
                    <div class="text-center py-8">
                        <span class="material-symbols-outlined text-slate-300 dark:text-slate-600 text-4xl mb-2">rule</span>
                        <p class="text-slate-500 dark:text-slate-400 text-sm">No rules in this category</p>
                    </div>
                `;
                return;
            }

            contentContainer.innerHTML = `
                <div class="flex items-center gap-3 mb-2">
                    <span class="material-symbols-outlined text-blue-700 dark:text-blue-400">${category.icon}</span>
                    <h3 class="font-display font-bold text-lg text-slate-900 dark:text-slate-100">${category.label}</h3>
                </div>
                <p class="text-sm text-slate-600 dark:text-slate-400 mb-6 leading-relaxed">Please review and acknowledge the policies to ensure a smooth transition for all our guests.</p>
                <div class="space-y-4">
                    ${rules.map(rule => `
                        <label class="group flex items-start gap-4 p-4 rounded-xl bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 hover:border-blue-100 dark:hover:border-blue-800 transition-all cursor-pointer shadow-sm active:scale-[0.99]">
                            <div class="relative flex items-center pt-1">
                                <input class="peer h-6 w-6 rounded-md border-slate-300 text-primary focus:ring-primary transition-colors" type="checkbox" />
                            </div>
                            <div class="flex flex-col gap-1">
                                <span class="font-semibold text-slate-900 dark:text-slate-100 group-hover:text-primary transition-colors">${escapeHtml(rule.nama_aturan)}</span>
                                <span class="text-sm text-slate-500 dark:text-slate-400">${escapeHtml(rule.deskripsi)}</span>
                            </div>
                        </label>
                    `).join('')}
                </div>
            `;
        }

        // Escape HTML to prevent XSS
        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Initialize house rules on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Load house rules
            loadHouseRules();

            // Add event listeners for Agree/Decline buttons
            const btnAgree = document.getElementById('btn-agree');
            const btnDecline = document.getElementById('btn-decline');
            
            if (btnAgree) {
                btnAgree.addEventListener('click', handleAgree);
            }
            
            if (btnDecline) {
                btnDecline.addEventListener('click', handleDecline);
            }
        });

        // Handle Agree button
        function handleAgree() {
            setCookie(COOKIE_NAME, 'true', COOKIE_EXPIRY_DAYS);
            
            // Show success message
            showToast('Guest agreement accepted. Thank you!', 'success');
            
            // Hide footer
            const rulesFooter = document.getElementById('rules-footer');
            rulesFooter.classList.add('hidden');
            
            // Hide house rules section
            const rulesSection = document.getElementById('house-rules-section');
            rulesSection.classList.add('hidden');
            
            // Scroll to top
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // Handle Decline button
        function handleDecline() {
            // Redirect to logout
            if (confirm('If you decline the guest agreement, you will be logged out. Continue?')) {
                window.location.href = 'logout.php';
            }
        }
    </script>
</body>

</html>
