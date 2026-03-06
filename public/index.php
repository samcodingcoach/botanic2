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
        body {
            font-family: 'Inter', sans-serif;
        }

        body {
            min-height: max(884px, 100dvh);
        }

        /* Account Dropdown Menu */
        .account-dropdown {
            position: relative;
        }

        .dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            margin-top: 0.5rem;
            min-width: 160px;
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(0, 0, 0, 0.1);
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.2s ease;
            z-index: 50;
        }

        .dropdown-menu.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .dropdown-menu a {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1rem;
            color: #334155;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            transition: background-color 0.15s ease;
        }

        .dropdown-menu a:hover {
            background-color: #f1f5f9;
        }

        .dropdown-menu a:first-child {
            border-radius: 0.5rem 0.5rem 0 0;
        }

        .dropdown-menu a:last-child {
            border-radius: 0 0 0.5rem 0.5rem;
            color: #ef4444;
        }

        .dropdown-menu a:last-child:hover {
            background-color: #fef2f2;
        }

        /* Profile Modal Styles */
        .profile-modal {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(4px);
            z-index: 50;
            display: none;
            align-items: flex-end;
            justify-content: center;
        }

        .profile-modal.active {
            display: flex;
        }

        .profile-modal-content {
            width: 100%;
            background: #f6f6f8;
            border-radius: 1.5rem 1.5rem 0 0;
            box-shadow: 0 -10px 40px rgba(0, 0, 0, 0.15);
            max-height: 85vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .dark .profile-modal-content {
            background: #101622;
        }

        @media (min-width: 640px) {
            .profile-modal {
                align-items: center;
            }
            .profile-modal-content {
                border-radius: 1.5rem;
                max-height: 85vh;
            }
        }

        /* Loading Spinner */
        .spinner {
            border: 3px solid rgba(19, 91, 236, 0.1);
            border-radius: 50%;
            border-top-color: #135bec;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
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
                        <a href="logout.php">
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
        </main>
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

    <script>
        // Toggle account dropdown
        function toggleDropdown() {
            const menu = document.getElementById('dropdown-menu');
            menu.classList.toggle('show');
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            const dropdown = document.querySelector('.account-dropdown');
            const menu = document.getElementById('dropdown-menu');
            if (dropdown && !dropdown.contains(e.target)) {
                menu.classList.remove('show');
            }
        });

        // Profile Modal Functions
        const profileModal = document.getElementById('profile-modal');
        let currentGuestData = null;

        // Open profile modal and load data
        async function openProfileModal() {
            toggleDropdown(); // Close dropdown
            profileModal.classList.add('active');
            document.body.style.overflow = 'hidden';
            
            // Show loading state
            const submitBtn = document.getElementById('btn-update-profile');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="material-symbols-outlined spinner text-sm">sync</span><span>Loading...</span>';
            
            try {
                const response = await fetch('../api/guest/detail.php');
                const result = await response.json();
                
                if (result.success) {
                    currentGuestData = result.data;
                    // Populate form
                    document.getElementById('profile-nama').value = result.data.nama_lengkap || '';
                    document.getElementById('profile-email').value = result.data.email || '';
                    document.getElementById('profile-wa').value = result.data.wa || '';
                    document.getElementById('profile-kota').value = result.data.kota || '';
                    document.getElementById('profile-points').textContent = (result.data.total_point || 0).toLocaleString();
                    document.getElementById('profile-password').value = '';
                    
                    // Reset button
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<span class="material-symbols-outlined text-sm">save</span><span>Update Profile</span>';
                } else {
                    showToast('Failed to load profile data', 'error');
                    closeProfileModal();
                }
            } catch (err) {
                showToast('Error loading profile', 'error');
                closeProfileModal();
            }
        }

        // Close profile modal
        function closeProfileModal() {
            profileModal.classList.remove('active');
            document.body.style.overflow = '';
            currentGuestData = null;
        }

        // Close modal when clicking outside
        profileModal.addEventListener('click', function(e) {
            if (e.target === profileModal) {
                closeProfileModal();
            }
        });

        // Close modal on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && profileModal.classList.contains('active')) {
                closeProfileModal();
            }
        });

        // Toggle password visibility
        document.getElementById('toggle-profile-password').addEventListener('click', function() {
            const passwordInput = document.getElementById('profile-password');
            const icon = this.querySelector('span');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.textContent = 'visibility_off';
            } else {
                passwordInput.type = 'password';
                icon.textContent = 'visibility';
            }
        });

        // Update profile handler
        document.getElementById('btn-update-profile').addEventListener('click', async function() {
            const nama = document.getElementById('profile-nama').value.trim();
            const email = document.getElementById('profile-email').value.trim();
            const wa = document.getElementById('profile-wa').value.trim();
            const kota = document.getElementById('profile-kota').value.trim();
            const password = document.getElementById('profile-password').value.trim();
            
            // Validation
            if (!nama || !wa) {
                showToast('Name and WhatsApp are required', 'error');
                return;
            }
            
            if (!password) {
                showToast('Password is required for verification', 'error');
                return;
            }
            
            if (!currentGuestData) {
                showToast('Profile data not loaded', 'error');
                return;
            }
            
            const submitBtn = this;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="material-symbols-outlined spinner text-sm">sync</span><span>Updating...</span>';
            
            const formData = new FormData();
            formData.append('id_guest', currentGuestData.id_guest);
            formData.append('nama_lengkap', nama);
            formData.append('email', email);
            formData.append('wa', wa);
            formData.append('kota', kota);
            formData.append('password', password);
            
            try {
                const response = await fetch('../api/guest/update.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                
                if (result.success) {
                    showToast('Profile updated successfully!', 'success');
                    closeProfileModal();
                } else {
                    showToast(result.message || 'Failed to update profile', 'error');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<span class="material-symbols-outlined text-sm">save</span><span>Update Profile</span>';
                }
            } catch (err) {
                showToast('Error updating profile', 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<span class="material-symbols-outlined text-sm">save</span><span>Update Profile</span>';
            }
        });

        // Toast notification function
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `fixed top-4 right-4 z-[60] flex items-center gap-3 px-5 py-4 rounded-lg shadow-lg transform transition-all duration-300 translate-x-full ${
                type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
            }`;
            
            const icon = type === 'success' ? 'check_circle' : 'error';
            toast.innerHTML = `
                <span class="material-symbols-outlined">${icon}</span>
                <span class="font-medium text-sm">${message}</span>
            `;
            
            document.body.appendChild(toast);
            
            setTimeout(() => toast.classList.remove('translate-x-full'), 10);
            setTimeout(() => {
                toast.classList.add('translate-x-full');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        // Add click handler for Account menu item
        document.querySelector('#dropdown-menu a[href="#"]').addEventListener('click', function(e) {
            e.preventDefault();
            openProfileModal();
        });

        // Load branches from API
        async function loadBranches() {
            const loading = document.getElementById('loading');
            const error = document.getElementById('error');
            const container = document.getElementById('branches-container');

            try {
                const response = await fetch('../api/cabang/list.php');
                const result = await response.json();

                loading.classList.add('hidden');
                error.classList.add('hidden');

                if (result.success && result.data && result.data.length > 0) {
                    container.innerHTML = result.data.map(branch => `
                        <div class="bg-white dark:bg-slate-900 rounded-xl overflow-hidden shadow-sm border border-slate-200 dark:border-slate-800 branch-card" data-name="${branch.nama_cabang.toLowerCase()}">
                            <div class="relative h-48 w-full bg-slate-200 dark:bg-slate-800 bg-center bg-cover"
                                style="background-image: url('../images/${branch.foto || 'default-branch.jpg'}');">
                                <div class="absolute bottom-3 left-3 px-2 py-1 bg-primary text-white text-xs font-bold rounded">
                                    ${branch.kode_cabang || 'N/A'}
                                </div>
                            </div>
                            <div class="p-4">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <h3 class="text-lg font-bold leading-tight">${branch.nama_cabang}</h3>
                                        <p class="text-slate-500 text-sm flex items-center gap-1">
                                            <span class="material-symbols-outlined text-xs">location_on</span>
                                            ${branch.alamat || 'No address available'}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3 mt-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                                    <a class="flex-1 flex items-center justify-center gap-2 py-2.5 bg-slate-100 dark:bg-slate-800 rounded-lg text-slate-700 dark:text-slate-200 text-sm font-semibold"
                                        href="${branch.gps ? 'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(branch.gps) : '#'}"
                                        target="_blank">
                                        <span class="material-symbols-outlined text-primary text-lg">map</span>
                                        View Map
                                    </a>
                                    <a class="flex-1 flex items-center justify-center gap-2 py-2.5 bg-green-500/10 dark:bg-green-500/20 rounded-lg text-green-600 dark:text-green-400 text-sm font-semibold"
                                        href="${branch.hp ? 'https://wa.me/' + branch.hp.replace(/[^0-9]/g, '') : '#'}"
                                        target="_blank">
                                        <span class="material-symbols-outlined text-lg">chat</span>
                                        WhatsApp
                                    </a>
                                </div>
                            </div>
                        </div>
                    `).join('');
                } else {
                    container.innerHTML = `
                        <div class="flex flex-col items-center justify-center py-12">
                            <span class="material-symbols-outlined text-slate-400 text-5xl mb-4">folder_open</span>
                            <p class="text-slate-500 dark:text-slate-400">No branches found</p>
                        </div>
                    `;
                }
            } catch (err) {
                loading.classList.add('hidden');
                error.classList.remove('hidden');
                error.classList.add('flex');
                document.getElementById('error-message').textContent = 'Failed to load branches. Please check your connection.';
                container.innerHTML = '';
            }
        }

        // Search functionality
        document.getElementById('search-input').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const cards = document.querySelectorAll('.branch-card');

            cards.forEach(card => {
                const name = card.dataset.name;
                if (name.includes(searchTerm)) {
                    card.classList.remove('hidden');
                } else {
                    card.classList.add('hidden');
                }
            });
        });

        // Filter buttons
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.filter-btn').forEach(b => {
                    b.classList.remove('bg-primary', 'text-white');
                    b.classList.add('bg-slate-200', 'dark:bg-slate-800');
                });
                this.classList.remove('bg-slate-200', 'dark:bg-slate-800');
                this.classList.add('bg-primary', 'text-white');

                if (this.dataset.filter === 'near') {
                    // For now, just show all - can be enhanced with geolocation
                    alert('Near Me feature requires location access');
                }
            });
        });

        // Load branches on page load
        document.addEventListener('DOMContentLoaded', loadBranches);
    </script>
</body>

</html>
