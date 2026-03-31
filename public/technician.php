<?php
session_start();
require_once __DIR__ . '/../config/koneksi.php';

// Set current page for navbar
$currentPage = 'technician';

// Get id_cabang from session or GET parameter
$id_cabang = isset($_GET['id_cabang']) ? (int) $_GET['id_cabang'] : (isset($_SESSION['id_cabang']) ? $_SESSION['id_cabang'] : 0);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Technician Service</title>
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

        /* Inactive technician card styles */
        .technician-item.inactive {
            opacity: 0.5;
        }

        .technician-item.inactive .action-button {
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
            <a href="javascript:history.back()" class="text-slate-900 dark:text-slate-100 flex size-12 shrink-0 items-center cursor-pointer">
                <span class="material-symbols-outlined text-2xl font-bold">arrow_back</span>
            </a>

            <!-- Page title - hides when search is active -->
            <h2 id="page-title" class="text-slate-900 dark:text-slate-100 text-lg font-bold leading-tight tracking-tight flex-1 text-center transition-all duration-300">
                Technician Service
            </h2>

            <!-- Search container - expands to replace title -->
            <div id="search-container" class="flex-1 max-w-md transition-all duration-300 ease-in-out hidden">
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">search</span>
                    <input
                        type="text"
                        id="search-input"
                        placeholder="Search by specialization..."
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
        <main class="flex-1 overflow-y-auto pb-24">
            <div class="px-4 py-4">
                <!-- Header Section -->
                <section class="mb-4">
                    <h2 class="text-xl font-bold mb-2 px-0 text-primary">
                        Available Technicians
                    </h2>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mb-4">Contact our technical team for maintenance or repair assistance.</p>
                </section>

                <div id="technician-list" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Technician cards will be loaded here -->
                </div>
            </div>
        </main>

        <?php include 'navbar.php'; ?>
    </div>

    <!-- WhatsApp Call Confirmation Modal -->
    <div id="call-modal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[100] hidden" onclick="closeCallModal()">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl max-w-sm w-full overflow-hidden transform transition-all"
                onclick="event.stopPropagation()">
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-4 border-b border-slate-200 dark:border-slate-700">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-slate-100">Confirm WhatsApp Call</h3>
                    <button onclick="closeCallModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <!-- Modal Content -->
                <div class="p-6 text-center">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4"
                        style="background-color: rgba(37, 211, 102, 0.1);">
                        <span class="material-symbols-outlined text-3xl" style="color: #25D366;">call</span>
                    </div>
                    <p class="text-slate-600 dark:text-slate-400 text-sm mb-2">
                        You are about to initiate a WhatsApp call to:
                    </p>
                    <p id="modal-phone-number" class="text-primary font-bold text-lg mb-4">
                        +1 (555) 010-1234
                    </p>
                    <p class="text-slate-500 dark:text-slate-400 text-xs">
                        This will open WhatsApp and start a call with the technician.
                    </p>
                </div>

                <!-- Modal Footer -->
                <div class="flex gap-3 p-4 bg-slate-50 dark:bg-slate-800/50">
                    <button onclick="closeCallModal()"
                        class="flex-1 px-4 py-3 text-sm font-semibold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 rounded-lg hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
                        Cancel
                    </button>
                    <button onclick="confirmCall()"
                        class="flex-1 px-4 py-3 text-sm font-bold text-white rounded-lg transition-colors flex items-center justify-center gap-2 hover:opacity-90"
                        style="background-color: #25D366;">
                        <span class="material-symbols-outlined text-sm text-white">call</span>
                        Call Now
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Store id_cabang for JS to use
        window.ID_CABANG = <?php echo $id_cabang; ?>;
        console.log('ID_CABANG set to:', window.ID_CABANG);

        // Global variable to store all technician data
        let allTechnicianData = [];

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
            '0': 'bg-red-500'         // tidak aktif
        };

        // Fetch technician data
        async function fetchTechnicians() {
            try {
                const url = window.ID_CABANG
                    ? `../api/teknisi/list.php?id_cabang=${window.ID_CABANG}`
                    : '../api/teknisi/list.php';

                const response = await fetch(url);
                const result = await response.json();

                if (result.success && result.data) {
                    allTechnicianData = result.data;
                    renderTechnicians(result.data);
                } else {
                    document.getElementById('technician-list').innerHTML = `
                        <div class="col-span-full text-center py-8 text-slate-500 dark:text-slate-400">
                            <p>No technicians available</p>
                        </div>
                    `;
                }
            } catch (error) {
                console.error('Error fetching technician data:', error);
                document.getElementById('technician-list').innerHTML = `
                    <div class="col-span-full text-center py-8 text-red-500">
                        <p>Failed to load technician data</p>
                    </div>
                `;
            }
        }

        // Render technician list
        function renderTechnicians(data) {
            const technicianList = document.getElementById('technician-list');

            if (data.length === 0) {
                technicianList.innerHTML = `
                    <div class="col-span-full text-center py-8 text-slate-500 dark:text-slate-400">
                        <span class="material-symbols-outlined text-6xl text-slate-300 dark:text-slate-600 mb-4 block">engineering</span>
                        <p>No technicians available at the moment.</p>
                    </div>
                `;
                return;
            }

            technicianList.innerHTML = data.map(tech => {
                const isActive = tech.aktif == '1';
                const isMale = tech.jenis_kelamin == '1';
                const avatarColor = isMale 
                    ? 'bg-blue-200 dark:bg-blue-900 text-blue-500' 
                    : 'bg-pink-200 dark:bg-pink-900 text-pink-500';
                const statusColor = statusColors[tech.aktif] || 'bg-gray-400';
                const statusLabel = isActive ? 'Active' : 'Inactive';
                const statusClass = isActive ? 'bg-green-100 text-green-700' : 'bg-slate-200 dark:bg-slate-700 text-slate-500 dark:text-slate-400';
                const inactiveClass = isActive ? '' : 'inactive';
                const genderIcon = isMale ? 'man' : 'woman';
                const cardBorder = isActive ? '' : 'border border-dashed border-slate-300 dark:border-slate-600';

                return `
                    <div class="technician-item ${inactiveClass} group bg-white dark:bg-slate-900 rounded-xl p-1 transition-all hover:shadow-[0_20px_40px_rgba(0,0,0,0.08)] border border-slate-100 dark:border-slate-800" data-spesialis="${escapeHtml(tech.spesialis || '').toLowerCase()}" data-nama="${escapeHtml(tech.nama_teknisi).toLowerCase()}">
                        <div class="p-5 bg-background-light dark:bg-slate-800 rounded-lg h-full ${cardBorder}">
                            <div class="flex justify-between items-start mb-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-xl ${avatarColor} flex items-center justify-center">
                                        <span class="material-symbols-outlined">${genderIcon}</span>
                                    </div>
                                    <div>
                                        <span class="text-[10px] font-bold text-primary tracking-widest uppercase">${escapeHtml(tech.kode_teknisi)}</span>
                                        <h3 class="text-lg font-bold leading-none text-slate-900 dark:text-slate-100 technician-name">${escapeHtml(tech.nama_teknisi)}</h3>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="px-3 py-1 ${statusClass} text-[10px] font-bold rounded-full uppercase tracking-tighter">${statusLabel}</span>
                                </div>
                            </div>
                            <div class="space-y-4 mb-6">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-[10px] text-slate-500 dark:text-slate-400/60 font-bold uppercase tracking-wider mb-1">Job Title</p>
                                        <p class="text-sm font-semibold text-slate-900 dark:text-slate-100 technician-jabatan">${escapeHtml(tech.jabatan)}</p>
                                    </div>
                                    <div>
                                        <p class="text-[10px] text-slate-500 dark:text-slate-400/60 font-bold uppercase tracking-wider mb-1">Specialization</p>
                                        <p class="text-sm font-semibold text-slate-900 dark:text-slate-100 technician-spesialis">${escapeHtml(tech.spesialis || 'N/A')}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="pt-4 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between">
                                ${isActive ? `
                                <div class="flex items-center gap-2 w-full">
                                    <a href="https://wa.me/${formatWhatsApp(tech.wa)}" target="_blank"
                                        class="action-button flex-1 flex items-center justify-center gap-2 py-2 rounded-lg bg-[#25D366] text-white text-xs font-bold hover:opacity-90 transition-opacity">
                                        <span class="material-symbols-outlined text-sm">chat</span>Chat
                                    </a>
                                    <button onclick="openCallModal('${escapeHtml(tech.wa)}')"
                                        class="action-button flex-1 flex items-center justify-center gap-2 py-2 rounded-lg border border-primary text-primary text-xs font-bold hover:bg-primary/5 transition-colors">
                                        <span class="material-symbols-outlined text-sm">call</span>Call
                                    </button>
                                </div>
                                ` : `
                                <div class="flex flex-col gap-2 w-full">
                                    <div class="flex items-center gap-2 w-full">
                                        <button class="action-button flex-1 flex items-center justify-center gap-2 py-2 rounded-lg bg-slate-200 dark:bg-slate-700 text-slate-400 dark:text-slate-500 text-xs font-bold cursor-not-allowed" disabled>
                                            <span class="material-symbols-outlined text-sm">chat</span>Chat
                                        </button>
                                        <button class="action-button flex-1 flex items-center justify-center gap-2 py-2 rounded-lg border border-slate-300 dark:border-slate-600 text-slate-400 dark:text-slate-500 text-xs font-bold cursor-not-allowed" disabled>
                                            <span class="material-symbols-outlined text-sm">call</span>Call
                                        </button>
                                    </div>
                                    <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400/60 italic text-center">Access Revoked</span>
                                </div>
                                `}
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        }

        // Format WhatsApp number
        function formatWhatsApp(phoneNumber) {
            const cleanNumber = phoneNumber.replace(/[^\d+]/g, '');
            return cleanNumber.startsWith('0') ? '62' + cleanNumber.substring(1) : cleanNumber;
        }

        // Escape HTML to prevent XSS
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Open WhatsApp call confirmation modal
        function openCallModal(phoneNumber) {
            currentPhoneNumber = phoneNumber;
            const modal = document.getElementById('call-modal');
            const modalPhone = document.getElementById('modal-phone-number');

            // Format phone number for display
            const cleanNumber = phoneNumber.replace(/[^\d+]/g, '');
            const formattedNumber = cleanNumber.replace(/(\+\d{1})(\d{3})(\d{3})(\d{4})/, '$1 ($2) $3-$4');
            modalPhone.textContent = formattedNumber || phoneNumber;

            modal.classList.remove('hidden');
        }

        // Close modal
        function closeCallModal() {
            const modal = document.getElementById('call-modal');
            modal.classList.add('hidden');
            currentPhoneNumber = '';
        }

        // Confirm call - open WhatsApp
        function confirmCall() {
            if (currentPhoneNumber) {
                const cleanNumber = currentPhoneNumber.replace(/[^\d+]/g, '');
                const waNumber = cleanNumber.startsWith('0') ? '62' + cleanNumber.substring(1) : cleanNumber;
                window.open(`https://wa.me/${waNumber}`, '_blank');
                closeCallModal();
            }
        }

        let currentPhoneNumber = '';

        // Close modal on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeCallModal();
            }
        });

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
                filterTechnicians('');
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
            filterTechnicians('');
        };

        // Filter technicians based on search - searches in spesialis and nama
        window.filterTechnicians = function (searchTerm) {
            const technicianList = document.getElementById('technician-list');
            const cards = technicianList.querySelectorAll('.technician-item');
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
                const noResultsMsg = technicianList.querySelector('.no-results-message');
                if (noResultsMsg) noResultsMsg.remove();

                // Remove search info
                const searchInfo = technicianList.querySelector('.search-info');
                if (searchInfo) searchInfo.remove();

                // Show all cards and re-render to remove highlights
                cards.forEach((card) => {
                    card.classList.remove('hidden');
                    card.style.display = '';
                });

                if (allTechnicianData.length > 0) {
                    renderTechnicians(allTechnicianData);
                }
                return;
            }

            const searchLower = searchTerm.toLowerCase().trim();
            let visibleCount = 0;
            const matchedSpesialis = [];

            cards.forEach((card) => {
                const spesialis = card.dataset.spesialis || '';
                const nama = card.dataset.nama || '';

                const matchesSpesialis = spesialis.includes(searchLower);
                const matchesNama = nama.includes(searchLower);
                const matchesSearch = matchesSpesialis || matchesNama;

                if (matchesSearch) {
                    card.classList.remove('hidden');
                    card.style.display = '';
                    visibleCount++;

                    // Track matched spesialis
                    const spesialisText = card.querySelector('.technician-spesialis')?.textContent?.trim() || '';
                    if (spesialisText && !matchedSpesialis.includes(spesialisText)) {
                        matchedSpesialis.push(spesialisText);
                    }

                    // Highlight matching text in spesialis
                    const spesialisEl = card.querySelector('.technician-spesialis');
                    if (spesialisEl && spesialisEl.textContent.toLowerCase().includes(searchLower)) {
                        spesialisEl.innerHTML = highlightText(spesialisEl.textContent, searchTerm);
                    }
                } else {
                    card.classList.add('hidden');
                    card.style.display = 'none';
                }
            });

            // Remove existing search info and no-results message
            const existingSearchInfo = technicianList.querySelector('.search-info');
            const noResultsMsg = technicianList.querySelector('.no-results-message');
            if (existingSearchInfo) existingSearchInfo.remove();
            if (noResultsMsg) noResultsMsg.remove();

            // Show search info or no results message
            if (visibleCount === 0) {
                const newNoResultsMsg = document.createElement('div');
                newNoResultsMsg.className = 'no-results-message col-span-full flex flex-col items-center justify-center py-12';
                newNoResultsMsg.innerHTML = `
                    <span class="material-symbols-outlined text-slate-400 text-5xl mb-4">search_off</span>
                    <p class="text-slate-500 dark:text-slate-400">No technicians found matching "${escapeHtml(searchTerm)}"</p>
                    <p class="text-slate-400 dark:text-slate-500 text-sm mt-2">Try different specialization or name keywords</p>
                `;
                technicianList.appendChild(newNoResultsMsg);
            } else if (visibleCount > 0) {
                // Show search info
                const searchInfoEl = document.createElement('div');
                searchInfoEl.className = 'search-info col-span-full flex items-center justify-between bg-slate-100 dark:bg-slate-800 rounded-lg px-4 py-3 mb-4';
                searchInfoEl.innerHTML = `
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-lg">check_circle</span>
                        <p class="text-sm text-slate-700 dark:text-slate-300">
                            <span class="font-bold text-primary">${visibleCount}</span> technician${visibleCount > 1 ? 's' : ''} found
                        </p>
                    </div>
                    ${matchedSpesialis.length > 0 ? `
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        ${matchedSpesialis.slice(0, 3).join(', ')}${matchedSpesialis.length > 3 ? '...' : ''}
                    </p>
                    ` : ''}
                `;
                technicianList.insertBefore(searchInfoEl, technicianList.firstChild);
            }

            console.log(`Showing ${visibleCount} of ${cards.length} technicians`);
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
                    filterTechnicians(e.target.value);
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
        fetchTechnicians();
    </script>
</body>

</html>
