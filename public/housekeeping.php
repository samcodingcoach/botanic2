<?php
session_start();

// Check if user or guest is logged in
if (!isset($_SESSION['id_users']) && !isset($_SESSION['id_guest'])) {
    header('Location: login.php');
    exit;
}

// Get id_cabang from URL
$id_cabang = isset($_GET['id_cabang']) ? (int) $_GET['id_cabang'] : 0;

if ($id_cabang <= 0) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Housekeeping Service Chat</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap"
        rel="stylesheet" />
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
                        "primary": "#135bec",
                        "background-light": "#f6f6f8",
                        "background-dark": "#101622",
                    },
                    fontFamily: {
                        "display": ["Inter"]
                    },
                    borderRadius: { "DEFAULT": "0.5rem", "lg": "1rem", "xl": "1.5rem", "full": "9999px" },
                },
            },
        }
    </script>
    <style type="text/tailwindcss">
        body {
            min-height: 100dvh;
        }

        /* Floating header shadow on scroll */
        #main-header.scrolled {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        /* Highlight text for search */
        .highlight-text {
            background-color: rgba(255, 255, 0, 0.4);
            padding: 1px 4px;
            border-radius: 2px;
            color: inherit;
        }

        /* Inactive staff card styles */
        .staff-item.inactive {
            opacity: 0.5;
        }
        
        .staff-item.inactive .chat-button {
            opacity: 0.4;
            cursor: not-allowed;
            pointer-events: none;
        }
    </style>
</head>

<body class="bg-background-light dark:bg-background-dark font-display text-slate-900 dark:text-slate-100">
    <div class="relative flex min-h-screen w-full flex-col overflow-x-hidden">
        <!-- Floating Header -->
        <header id="main-header" class="fixed top-0 left-0 right-0 flex items-center bg-background-light/80 dark:bg-background-dark/80 backdrop-blur-md p-4 pb-2 justify-between z-50 border-b border-slate-200 dark:border-slate-800 transition-shadow duration-300">
            <a href="index.php" class="text-slate-900 dark:text-slate-100 flex size-12 shrink-0 items-center cursor-pointer">
                <span class="material-symbols-outlined text-2xl font-bold">arrow_back</span>
            </a>

            <!-- Page title - hides when search is active -->
            <h2 id="page-title" class="text-slate-900 dark:text-slate-100 text-lg font-bold leading-tight tracking-tight flex-1 text-center transition-all duration-300">
                Housekeeping
            </h2>

            <!-- Search container - expands to replace title -->
            <div id="search-container" class="flex-1 max-w-md transition-all duration-300 ease-in-out hidden">
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">search</span>
                    <input
                        type="text"
                        id="search-input"
                        placeholder="Search by position..."
                        class="w-full bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg pl-10 pr-10 py-2.5 text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary/50"
                        autocomplete="off"
                    />
                    <button id="clear-search" onclick="clearSearch()" class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 p-1">
                        <span class="material-symbols-outlined text-lg">close</span>
                    </button>
                </div>
            </div>

            <!-- Search toggle button -->
            <button id="search-btn" onclick="toggleSearch()" class="flex cursor-pointer items-center justify-center rounded-xl h-12 w-12 bg-transparent text-slate-900 dark:text-slate-100 p-0 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors shrink-0">
                <span class="material-symbols-outlined text-2xl font-bold">search</span>
            </button>
        </header>

        <!-- Spacer for fixed header -->
        <div class="h-[73px] shrink-0"></div>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto pb-20">
            <div class="px-4 py-4">
                <div id="staff-list" class="space-y-4">
                    <!-- Staff items will be loaded here -->
                </div>
            </div>
        </main>
        <?php include 'navbar.php'; ?>
    </div>

    <script>
        // Store id_cabang for JS to use
        window.ID_CABANG = <?php echo $id_cabang; ?>;
        console.log('ID_CABANG set to:', window.ID_CABANG);

        // Global variable to store all housekeeping data
        let allStaffData = [];

        // Floating header shadow on scroll
        window.addEventListener('scroll', () => {
            const header = document.getElementById('main-header');
            if (window.scrollY > 10) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });

        // Status color mapping
        const statusColors = {
            '1': 'bg-emerald-500',    // aktif
            '0': 'bg-red-500',        // tidak aktif
            '2': 'bg-yellow-500',     // sibuk
            '3': 'bg-gray-400'        // offline
        };

        // Fetch housekeeping data
        async function fetchHousekeeping() {
            const loading = document.getElementById('loading');
            const error = document.getElementById('error');

            try {
                const url = window.ID_CABANG
                    ? `../api/hk/list.php?id_cabang=${window.ID_CABANG}`
                    : '../api/hk/list.php';

                const response = await fetch(url);
                const result = await response.json();

                if (result.success && result.data) {
                    allStaffData = result.data;
                    renderStaff(result.data);
                } else {
                    document.getElementById('staff-list').innerHTML = `
                        <div class="text-center py-8 text-slate-500 dark:text-slate-400">
                            <p>No staff available</p>
                        </div>
                    `;
                }
            } catch (error) {
                console.error('Error fetching housekeeping data:', error);
                document.getElementById('staff-list').innerHTML = `
                    <div class="text-center py-8 text-red-500">
                        <p>Failed to load staff data</p>
                    </div>
                `;
            }
        }

        // Render staff list
        function renderStaff(data) {
            const staffList = document.getElementById('staff-list');

            if (data.length === 0) {
                staffList.innerHTML = `
                    <div class="text-center py-8 text-slate-500 dark:text-slate-400">
                        <p>No staff available</p>
                    </div>
                `;
                return;
            }

            staffList.innerHTML = data.map(staff => {
                const avatarBg = staff.jenis_kelamin == '1'
                    ? 'bg-slate-200 dark:bg-slate-700'
                    : 'bg-pink-200 dark:bg-pink-900';
                const avatarIconColor = staff.jenis_kelamin == '1'
                    ? 'text-slate-400 dark:text-slate-500'
                    : 'text-pink-400 dark:text-pink-500';
                const statusColor = statusColors[staff.aktif] || 'bg-gray-400';
                const isInactive = staff.aktif != '1';
                const inactiveClass = isInactive ? 'inactive' : '';

                return `
                    <div class="staff-item ${inactiveClass} flex gap-4 bg-white dark:bg-slate-900 p-4 rounded-lg shadow-sm border border-slate-100 dark:border-slate-800 items-center" data-jabatan="${escapeHtml(staff.jabatan).toLowerCase()}">
                        <div class="shrink-0 relative">
                            <div class="size-14 rounded-full ${avatarBg} border-2 border-primary/20 flex items-center justify-center">
                                <span class="material-symbols-outlined ${avatarIconColor}">person</span>
                            </div>
                            <div class="absolute bottom-0 right-0 size-3 ${statusColor} border-2 border-white dark:border-slate-900 rounded-full" title="Status: ${getStatusText(staff.aktif)}"></div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-bold truncate staff-name">${escapeHtml(staff.nama_lengkap)}</p>
                            <p class="text-slate-500 dark:text-slate-400 text-xs flex items-center gap-1">
                                <span class="material-symbols-outlined text-xs">badge</span> <span class="staff-jabatan">${escapeHtml(staff.jabatan)}</span>
                            </p>
                        </div>
                        <div class="shrink-0">
                            <button ${isInactive ? 'disabled' : ''} onclick="${isInactive ? 'return false;' : `openWhatsApp('${escapeHtml(staff.wa)}')`}"
                                class="chat-button flex items-center justify-center gap-2 bg-[#25D366] text-white px-4 py-2 rounded-xl text-sm font-semibold hover:opacity-90 transition-colors">
                                <span class="material-symbols-outlined text-sm">chat</span>
                                <span>${isInactive ? 'Unavailable' : 'Chat'}</span>
                            </button>
                        </div>
                    </div>
                `;
            }).join('');
        }

        // Get status text
        function getStatusText(status) {
            const statusMap = {
                '1': 'Aktif',
                '0': 'Tidak Aktif',
                '2': 'Sibuk',
                '3': 'Offline'
            };
            return statusMap[status] || 'Unknown';
        }

        // Escape HTML to prevent XSS
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Open WhatsApp chat
        function openWhatsApp(phoneNumber) {
            if (phoneNumber) {
                const cleanNumber = phoneNumber.replace(/[^\d+]/g, '');
                window.open(`https://wa.me/${cleanNumber}`, '_blank');
            }
        }

        // Toggle search - expand/collapse search input
        window.toggleSearch = function () {
            const searchContainer = document.getElementById('search-container');
            const pageTitle = document.getElementById('page-title');
            const searchInput = document.getElementById('search-input');
            const searchBtn = document.getElementById('search-btn');

            if (!searchContainer || !pageTitle || !searchInput) return;

            const isExpanded = !searchContainer.classList.contains('hidden');

            if (isExpanded) {
                // Close search - show page title, hide search input
                searchContainer.classList.add('hidden');
                pageTitle.classList.remove('hidden');
                searchInput.value = '';
                filterStaff('');
                searchBtn.innerHTML = '<span class="material-symbols-outlined text-2xl font-bold">search</span>';
            } else {
                // Open search - hide page title, show search input
                pageTitle.classList.add('hidden');
                searchContainer.classList.remove('hidden');
                setTimeout(() => searchInput.focus(), 100);
                searchBtn.innerHTML = '<span class="material-symbols-outlined text-2xl font-bold">close</span>';
            }
        };

        // Clear search
        window.clearSearch = function () {
            const searchInput = document.getElementById('search-input');
            if (searchInput) {
                searchInput.value = '';
                searchInput.focus();
            }
            filterStaff('');
        };

        // Filter staff based on search - searches only in jabatan
        window.filterStaff = function (searchTerm) {
            const staffList = document.getElementById('staff-list');
            const cards = staffList.querySelectorAll('.staff-item');
            const clearBtn = document.getElementById('clear-search');

            // Show/hide clear button
            if (clearBtn) {
                if (searchTerm && searchTerm.trim()) {
                    clearBtn.classList.remove('hidden');
                } else {
                    clearBtn.classList.add('hidden');
                }
            }

            // If no search term, show all cards
            if (!searchTerm || !searchTerm.trim()) {
                // Remove no-results message
                const noResultsMsg = staffList.querySelector('.no-results-message');
                if (noResultsMsg) noResultsMsg.remove();

                // Remove search info
                const searchInfo = staffList.querySelector('.search-info');
                if (searchInfo) searchInfo.remove();

                // Show all cards and re-render to remove highlights
                cards.forEach((card) => {
                    card.classList.remove('hidden');
                    card.style.display = '';
                });

                if (allStaffData.length > 0) {
                    renderStaff(allStaffData);
                }
                return;
            }

            const searchLower = searchTerm.toLowerCase().trim();
            let visibleCount = 0;
            const matchedPositions = [];

            cards.forEach((card) => {
                const jabatan = card.dataset.jabatan || '';

                const matchesSearch = jabatan.includes(searchLower);

                if (matchesSearch) {
                    card.classList.remove('hidden');
                    card.style.display = '';
                    visibleCount++;

                    // Track matched position
                    const positionText = card.querySelector('.staff-jabatan')?.textContent?.trim() || '';
                    if (!matchedPositions.includes(positionText)) {
                        matchedPositions.push(positionText);
                    }

                    // Highlight matching text in jabatan
                    const jabatanEl = card.querySelector('.staff-jabatan');
                    if (jabatanEl && jabatanEl.textContent.toLowerCase().includes(searchLower)) {
                        jabatanEl.innerHTML = highlightText(jabatanEl.textContent, searchTerm);
                    }
                } else {
                    card.classList.add('hidden');
                    card.style.display = 'none';
                }
            });

            // Remove existing search info and no-results message
            const existingSearchInfo = staffList.querySelector('.search-info');
            const noResultsMsg = staffList.querySelector('.no-results-message');
            if (existingSearchInfo) existingSearchInfo.remove();
            if (noResultsMsg) noResultsMsg.remove();

            // Show search info or no results message
            if (visibleCount === 0) {
                const newNoResultsMsg = document.createElement('div');
                newNoResultsMsg.className = 'no-results-message flex flex-col items-center justify-center py-12 mx-4';
                newNoResultsMsg.innerHTML = `
                    <span class="material-symbols-outlined text-slate-400 text-5xl mb-4">search_off</span>
                    <p class="text-slate-500 dark:text-slate-400">No staff found matching "${searchTerm}"</p>
                    <p class="text-slate-400 dark:text-slate-500 text-sm mt-2">Try different position keywords</p>
                `;
                staffList.appendChild(newNoResultsMsg);
            } else if (visibleCount > 0) {
                // Show search info
                const searchInfoEl = document.createElement('div');
                searchInfoEl.className = 'search-info flex items-center justify-between bg-slate-100 dark:bg-slate-800 rounded-lg px-4 py-3 mb-4 mx-4';
                searchInfoEl.innerHTML = `
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-lg">check_circle</span>
                        <p class="text-sm text-slate-700 dark:text-slate-300">
                            <span class="font-bold text-primary">${visibleCount}</span> staff member${visibleCount > 1 ? 's' : ''} found
                        </p>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        ${matchedPositions.join(', ')}
                    </p>
                `;
                staffList.insertBefore(searchInfoEl, staffList.firstChild);
            }

            console.log(`Showing ${visibleCount} of ${cards.length} staff`);
        };

        // Highlight text function
        function highlightText(text, searchTerm) {
            if (!searchTerm) return text;
            const regex = new RegExp(
                `(${searchTerm.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`,
                'gi'
            );
            return text.replace(regex, '<span class="highlight-text">$1</span>');
        }

        // Initialize search on page load
        document.addEventListener('DOMContentLoaded', () => {
            const searchInput = document.getElementById('search-input');
            if (searchInput) {
                searchInput.addEventListener('input', (e) => {
                    filterStaff(e.target.value);
                });

                // Close search on Escape key
                searchInput.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape') {
                        toggleSearch();
                    }
                });
            }
        });

        // Initialize
        fetchHousekeeping();
    </script>
</body>

</html>
