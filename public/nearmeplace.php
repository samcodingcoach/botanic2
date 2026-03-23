<?php
session_start();

// Check if user or guest is logged in
$isUser = isset($_SESSION['id_users']) && isset($_SESSION['username']);
$isGuest = isset($_SESSION['id_guest']) && isset($_SESSION['nama_lengkap']);

if (!$isUser && !$isGuest) {
    header('Location: login.php');
    exit;
}

// Get id_cabang from URL parameter
$id_cabang = isset($_GET['id_cabang']) ? (int) $_GET['id_cabang'] : 0;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Near Me - Botanic Groups</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap"
        rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@100..700,0..1&amp;display=swap"
        rel="stylesheet" />
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap"
        rel="stylesheet" />
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }

        .filled-icon {
            font-variation-settings: 'FILL' 1;
        }
    </style>
    <style>
        body {
            min-height: max(884px, 100dvh);
        }

        /* Hide scrollbar for horizontal scroll */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
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
</head>

<body class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-slate-100">
    <div class="relative flex min-h-screen flex-col overflow-x-hidden">

        <main class="flex-1 px-4 py-2 space-y-4">
            <!-- Header -->
            <section class="mb-4">
                <h2 class="text-lg font-bold mb-4 px-0">Near Me</h2>
                <!-- Filter Chips -->
                <div class="flex gap-2 overflow-x-auto pb-2 no-scrollbar">
                    <button onclick="filterPlaces('all')"
                        class="filter-btn flex-none px-5 py-2 rounded-full bg-primary text-white font-semibold text-sm transition-all active:scale-95"
                        data-filter="all">All</button>
                    <button onclick="filterPlaces('Restaurant')"
                        class="filter-btn flex-none px-5 py-2 rounded-full bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-200 font-medium text-sm hover:bg-slate-300 dark:hover:bg-slate-700 transition-all active:scale-95"
                        data-filter="Restaurant">Restaurant</button>
                    <button onclick="filterPlaces('Hospital')"
                        class="filter-btn flex-none px-5 py-2 rounded-full bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-200 font-medium text-sm hover:bg-slate-300 dark:hover:bg-slate-700 transition-all active:scale-95"
                        data-filter="Hospital">Hospital</button>
                    <button onclick="filterPlaces('Shop')"
                        class="filter-btn flex-none px-5 py-2 rounded-full bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-200 font-medium text-sm hover:bg-slate-300 dark:hover:bg-slate-700 transition-all active:scale-95"
                        data-filter="Shop">Shop</button>
                </div>
            </section>

            <!-- Loading State -->
            <div id="loading" class="flex flex-col items-center justify-center py-12">
                <div class="spinner w-10 h-10 mb-4"></div>
                <p class="text-slate-500 dark:text-slate-400">Loading places...</p>
            </div>

            <!-- Error State -->
            <div id="error" class="hidden flex-col items-center justify-center py-12">
                <span class="material-symbols-outlined text-red-500 text-5xl mb-4">error</span>
                <p class="text-slate-500 dark:text-slate-400 text-center" id="error-message"></p>
                <button onclick="loadPlaces()" class="mt-4 px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary/90">
                    Retry
                </button>
            </div>

            <!-- Nearby Places Grid -->
            <div id="places-container" class="grid grid-cols-2 gap-4 hidden">
                <!-- Places will be loaded here -->
            </div>

            <!-- Empty State -->
            <div id="empty" class="hidden flex-col items-center justify-center py-12">
                <span class="material-symbols-outlined text-slate-400 text-5xl mb-4">folder_open</span>
                <p class="text-slate-500 dark:text-slate-400">No places found</p>
            </div>

            <div class="h-20"></div> <!-- Spacer for Bottom Nav -->
        </main>

        <!-- Bottom Navigation -->
        <?php include 'navbar.php'; ?>
    </div>

    <!-- Full Address Modal -->
    <div id="address-modal" class="profile-modal">
        <div class="profile-modal-content">
            <!-- BottomSheetHandle -->
            <div class="flex h-5 w-full items-center justify-center pt-2">
                <div class="h-1.5 w-12 rounded-full bg-slate-300 dark:bg-slate-700"></div>
            </div>
            <!-- Modal Content -->
            <div class="px-6 py-4">
                <h3 id="modal-place-name" class="text-lg font-bold text-slate-900 dark:text-slate-100 mb-4"></h3>
                <div class="flex items-start gap-3 mb-6">
                    <span class="material-symbols-outlined text-primary text-xl shrink-0 mt-0.5">location_on</span>
                    <p id="modal-full-address" class="text-slate-700 dark:text-slate-300 text-sm leading-relaxed"></p>
                </div>
                <button onclick="closeAddressModal()"
                    class="w-full py-3 bg-primary text-white font-semibold rounded-lg hover:bg-primary/90 transition-colors">
                    Close
                </button>
            </div>
        </div>
    </div>

    <style>
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
    </style>

    <script>
        let allPlaces = [];
        const id_cabang = <?php echo $id_cabang; ?>;
        const addressModal = document.getElementById('address-modal');

        // Show full address modal
        function showFullAddress(placeName, address) {
            document.getElementById('modal-place-name').textContent = placeName;
            document.getElementById('modal-full-address').textContent = address;
            addressModal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        // Close address modal
        function closeAddressModal() {
            addressModal.classList.remove('active');
            document.body.style.overflow = '';
        }

        // Close modal when clicking outside
        if (addressModal) {
            addressModal.addEventListener('click', function(e) {
                if (e.target === addressModal) {
                    closeAddressModal();
                }
            });
        }

        // Close modal on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && addressModal.classList.contains('active')) {
                closeAddressModal();
            }
        });

        // Load places from API
        async function loadPlaces() {
            const loading = document.getElementById('loading');
            const error = document.getElementById('error');
            const container = document.getElementById('places-container');
            const empty = document.getElementById('empty');

            try {
                const response = await fetch(`../api/nearme/list.php?id_cabang=${id_cabang}&aktif=1`);
                const result = await response.json();

                loading.classList.add('hidden');
                error.classList.add('hidden');

                if (result.success && result.data && result.data.length > 0) {
                    allPlaces = result.data;
                    container.classList.remove('hidden');
                    container.classList.add('grid');
                    renderPlaces(allPlaces);
                } else {
                    empty.classList.remove('hidden');
                    empty.classList.add('flex');
                }
            } catch (err) {
                loading.classList.add('hidden');
                error.classList.remove('hidden');
                error.classList.add('flex');
                document.getElementById('error-message').textContent = 'Failed to load places. Please check your connection.';
            }
        }

        // Render places
        function renderPlaces(places) {
            const container = document.getElementById('places-container');
            
            if (places.length === 0) {
                container.classList.add('hidden');
                container.classList.remove('grid');
                const empty = document.getElementById('empty');
                empty.classList.remove('hidden');
                empty.classList.add('flex');
                return;
            }

            container.innerHTML = places.map(place => {
                const badgeColor = getBadgeColor(place.jenis_area);
                const imageUrl = place.foto ? `../images/${place.foto}` : 'https://via.placeholder.com/300x300?text=No+Image';
                const gpsLink = place.gps ? `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(place.gps)}` : '#';

                return `
                <div class="bg-white dark:bg-slate-900 rounded-xl overflow-hidden shadow-sm border border-slate-200 dark:border-slate-800 group active:scale-[0.98] transition-all flex flex-col place-card" data-type="${place.jenis_area}">
                    <div class="relative aspect-square overflow-hidden">
                        <img class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                            alt="${place.nama_area}"
                            src="${imageUrl}" />
                        <div class="absolute top-2 left-2 ${badgeColor} backdrop-blur-md px-2 py-1 rounded-full flex items-center justify-center">
                            <span class="text-[9px] font-bold text-white uppercase tracking-wider leading-none">${place.jenis_area}</span>
                        </div>
                        <div class="absolute bottom-2 right-2 bg-white/60 backdrop-blur-md px-2 py-1 rounded-lg border border-primary/10 flex items-center justify-center">
                            <span class="text-[10px] font-bold text-primary leading-none">${place.jarak || '0km'}</span>
                        </div>
                    </div>
                    <div class="p-3 flex flex-col flex-1 justify-between">
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-slate-100 leading-tight line-clamp-1 mb-1">
                                ${place.nama_area}</h3>
                            <p class="address-text text-slate-500 dark:text-slate-400 text-[11px] line-clamp-2 flex items-start gap-0.5 cursor-pointer hover:text-primary transition-colors"
                                data-name="${place.nama_area.replace(/"/g, '&quot;')}"
                                data-address="${(place.alamat || 'No address available').replace(/"/g, '&quot;')}">
                                <span class="material-symbols-outlined text-[12px] shrink-0 mt-0.5">location_on</span>
                                <span class="line-clamp-2">${place.alamat || 'No address available'}</span>
                            </p>
                        </div>
                        <a href="${gpsLink}" target="_blank"
                            class="mt-3 w-full py-2 border border-primary/20 rounded-full flex items-center justify-center gap-1.5 text-primary font-bold text-[11px] hover:bg-primary/5 transition-colors">
                            <span class="material-symbols-outlined text-[16px]" style="font-variation-settings: 'FILL' 1;">directions</span>
                            Open Map
                        </a>
                    </div>
                </div>
            `}).join('');

            // Add click event listeners to all address elements
            document.querySelectorAll('.address-text').forEach(el => {
                el.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const name = this.dataset.name;
                    const address = this.dataset.address;
                    showFullAddress(name, address);
                });
            });
        }

        // Get badge color based on type
        function getBadgeColor(type) {
            switch (type) {
                case 'Hospital':
                    return 'bg-primary/90';
                case 'Shop':
                    return 'bg-slate-500/90';
                case 'Restaurant':
                    return 'bg-orange-500/90';
                default:
                    return 'bg-slate-500/90';
            }
        }

        // Filter places
        function filterPlaces(type) {
            // Update button styles
            document.querySelectorAll('.filter-btn').forEach(btn => {
                if (btn.dataset.filter === type) {
                    btn.classList.remove('bg-slate-200', 'dark:bg-slate-800', 'text-slate-700', 'dark:text-slate-200');
                    btn.classList.add('bg-primary', 'text-white');
                } else {
                    btn.classList.add('bg-slate-200', 'dark:bg-slate-800', 'text-slate-700', 'dark:text-slate-200');
                    btn.classList.remove('bg-primary', 'text-white');
                }
            });

            // Filter data
            if (type === 'all') {
                renderPlaces(allPlaces);
            } else {
                const filtered = allPlaces.filter(place => place.jenis_area === type);
                renderPlaces(filtered);
            }
        }

        // Load places on page load
        document.addEventListener('DOMContentLoaded', loadPlaces);
    </script>
</body>

</html>
