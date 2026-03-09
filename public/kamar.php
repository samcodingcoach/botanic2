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
    <title>Room List - Botanic Groups</title>
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
                        "DEFAULT": "0.5rem",
                        "lg": "1rem",
                        "xl": "1.5rem",
                        "full": "9999px"
                    },
                },
            },
        }
    </script>
    <style type="text/tailwindcss">
        body {
            min-height: 100dvh;
        }
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
    <link rel="stylesheet" href="css/kamar.css" />
</head>

<body class="bg-background-light dark:bg-background-dark font-display text-slate-900 dark:text-slate-100 min-h-screen">
    <div class="relative flex h-full min-h-screen w-full flex-col bg-background-light dark:bg-background-dark overflow-x-hidden">
        <!-- Header -->
        <div class="flex items-center bg-white/80 dark:bg-slate-900/80 backdrop-blur-md p-4 pb-2 justify-between sticky top-0 z-50 border-b border-slate-200 dark:border-slate-700">
            <a href="index.php" class="text-slate-900 dark:text-slate-100 flex size-12 shrink-0 items-center cursor-pointer">
                <span class="material-symbols-outlined text-2xl font-bold">arrow_back</span>
            </a>
            <h2 id="branch-name" class="text-slate-900 dark:text-slate-100 text-lg font-bold leading-tight tracking-[-0.015em] flex-1 text-center truncate px-4">
                Loading...
            </h2>
            <div class="flex w-12 items-center justify-end">
                <button id="search-btn" onclick="openSearchModal()" class="flex cursor-pointer items-center justify-center rounded-xl h-12 bg-transparent text-slate-900 dark:text-slate-100 p-0 pointer-events-auto relative z-10">
                    <span class="material-symbols-outlined text-2xl font-bold">search</span>
                </button>
            </div>
        </div>

        <!-- Search Modal -->
        <div id="search-modal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[100] hidden" style="display: none;" onclick="if(event.target === this) closeSearchModal()">
            <div class="absolute top-0 left-0 right-0 bg-white dark:bg-slate-900 shadow-lg">
                <div class="flex items-center gap-3 p-4 border-b border-slate-200 dark:border-slate-700">
                    <span class="material-symbols-outlined text-slate-400">search</span>
                    <input 
                        type="text" 
                        id="search-input"
                        placeholder="Search room type..." 
                        class="flex-1 bg-transparent border-none outline-none text-slate-900 dark:text-slate-100 placeholder-slate-400"
                        autocomplete="off"
                        oninput="filterRooms(this.value)"
                    />
                    <button id="clear-search" onclick="clearSearch()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <div class="p-2 bg-primary/5 dark:bg-primary/10">
                    <p class="text-xs text-slate-600 dark:text-slate-400 px-2">
                        <span class="font-semibold">Search by:</span> Nama Tipe, Keterangan (1), Keterangan Akomodasi
                    </p>
                </div>
            </div>
            <div class="pt-[130px] px-4" id="search-results-container">
                <!-- Search results will be shown here -->
            </div>
        </div>

        <!-- Main Content -->
        <main class="flex-1 pb-24 px-4 space-y-6 mt-4" id="rooms-container">
            <!-- Loading State -->
            <div id="loading" class="flex flex-col items-center justify-center py-12">
                <div class="spinner w-10 h-10 mb-4"></div>
                <p class="text-slate-500 dark:text-slate-400">Loading rooms...</p>
            </div>

            <!-- Error State -->
            <div id="error" class="hidden flex-col items-center justify-center py-12">
                <span class="material-symbols-outlined text-red-500 text-5xl mb-4">error</span>
                <p class="text-slate-500 dark:text-slate-400 text-center" id="error-message"></p>
                <button onclick="loadRooms()" class="mt-4 px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary/90">
                    Retry
                </button>
            </div>
        </main>

        <!-- Bottom Navigation Bar -->
        <div class="fixed bottom-0 left-0 right-0 bg-white/95 dark:bg-slate-900/95 backdrop-blur-xl border-t border-slate-200 dark:border-slate-700 px-2 py-2 flex items-center justify-around z-50">
            <button class="flex flex-col items-center gap-1 p-2 text-primary">
                <span class="material-symbols-outlined text-[26px] fill-1">bed</span>
                <span class="text-[10px] font-semibold">Room</span>
            </button>
            <button class="flex flex-col items-center gap-1 p-2 text-slate-400">
                <span class="material-symbols-outlined text-[26px]">pool</span>
                <span class="text-[10px] font-medium">Facility</span>
            </button>
            <button class="flex flex-col items-center gap-1 p-2 text-slate-400">
                <span class="material-symbols-outlined text-[26px]">concierge</span>
                <span class="text-[10px] font-medium">Receptionist</span>
            </button>
            <button class="flex flex-col items-center gap-1 p-2 text-slate-400">
                <span class="material-symbols-outlined text-[26px]">cleaning_services</span>
                <span class="text-[10px] font-medium">Housekeeping</span>
            </button>
            <button class="flex flex-col items-center gap-1 p-2 text-slate-400">
                <span class="material-symbols-outlined text-[26px]">more_horiz</span>
                <span class="text-[10px] font-medium">More</span>
            </button>
        </div>
    </div>

    <script>
        // Store id_cabang for JS to use - MUST be before kamar.js
        window.ID_CABANG = <?php echo $id_cabang; ?>;
        console.log('ID_CABANG set to:', window.ID_CABANG);
        
        // Immediate function for onclick handler
        function openSearchModal() {
            const modal = document.getElementById('search-modal');
            const searchInput = document.getElementById('search-input');
            if (modal) {
                modal.classList.remove('hidden');
                modal.style.display = 'block';
            }
            if (searchInput) {
                setTimeout(() => searchInput.focus(), 100);
            }
        }
        
        function closeSearchModal() {
            const modal = document.getElementById('search-modal');
            const searchInput = document.getElementById('search-input');
            const resultsContainer = document.getElementById('search-results-container');
            if (modal) {
                modal.classList.add('hidden');
                modal.style.display = 'none';
            }
            if (searchInput) {
                searchInput.value = '';
            }
            if (resultsContainer) {
                resultsContainer.innerHTML = '';
            }
        }
        
        function clearSearch() {
            const searchInput = document.getElementById('search-input');
            const resultsContainer = document.getElementById('search-results-container');
            if (searchInput) {
                searchInput.value = '';
            }
            if (resultsContainer) {
                resultsContainer.innerHTML = '';
            }
            if (searchInput) {
                searchInput.focus();
            }
        }
        
        // Basic filter function - will be enhanced by kamar.js
        function filterRooms(searchTerm) {
            console.log('Search:', searchTerm);
            // This will be overridden by kamar.js with full functionality
        }
    </script>
    <script src="script/kamar.js"></script>
</body>

</html>
