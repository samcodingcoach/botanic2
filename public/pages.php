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

// Set current page for navbar
$currentPage = 'more';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Pages - Botanic Groups</title>
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

        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }

        .filled-icon {
            font-variation-settings: 'FILL' 1;
        }
    </style>
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

        /* Floating header shadow on scroll */
        #main-header.scrolled {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
    </style>
</head>

<body class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-slate-100">
    <div class="relative flex min-h-screen flex-col overflow-x-hidden">
        <!-- Floating Header -->
        <header id="main-header" class="fixed top-0 left-0 right-0 flex items-center bg-white/80 dark:bg-slate-900/80 backdrop-blur-md p-4 pb-2 justify-between z-50 border-b border-slate-200 dark:border-slate-700 transition-shadow duration-300">
            <a href="index.php" class="text-slate-900 dark:text-slate-100 flex size-12 shrink-0 items-center cursor-pointer">
                <span class="material-symbols-outlined text-2xl font-bold">arrow_back</span>
            </a>

            <!-- Branch name - hides when search is active -->
            <h2 id="branch-name" class="text-slate-900 dark:text-slate-100 text-lg font-bold leading-tight tracking-[-0.015em] flex-1 text-center truncate px-4 transition-all duration-300">
                Loading...
            </h2>

            <!-- Search container - expands to replace branch name -->
            <div id="search-container" class="flex-1 max-w-md transition-all duration-300 ease-in-out hidden">
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">search</span>
                    <input
                        type="text"
                        id="search-input"
                        placeholder="Search pages..."
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

        <main class="flex-1 px-4 py-2 space-y-4">
            <!-- Loading State -->
            <div id="loading" class="flex flex-col items-center justify-center py-12">
                <div class="spinner w-10 h-10 mb-4"></div>
                <p class="text-slate-500 dark:text-slate-400">Loading pages...</p>
            </div>

            <!-- Error State -->
            <div id="error" class="hidden flex-col items-center justify-center py-12">
                <span class="material-symbols-outlined text-red-500 text-5xl mb-4">error</span>
                <p class="text-slate-500 dark:text-slate-400 text-center" id="error-message"></p>
                <button onclick="loadPages()" class="mt-4 px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary/90">
                    Retry
                </button>
            </div>

            <!-- Pages Section -->
            <section id="pages-section" class="mt-4 mb-4 hidden">
                <h2 class="text-xl font-bold mb-2 px-0 text-primary">
                    Social Media &amp; Official Pages
                </h2>
                <p class="text-slate-500 dark:text-slate-400 text-sm mb-4">Follow our curated journey across all major
                    platforms.</p>
                <div id="pages-container" class="space-y-4">
                    <!-- Pages will be loaded here -->
                </div>
            </section>

            <!-- Empty State -->
            <div id="empty" class="hidden flex-col items-center justify-center py-12">
                <span class="material-symbols-outlined text-slate-400 text-5xl mb-4">folder_open</span>
                <p class="text-slate-500 dark:text-slate-400">No pages found</p>
            </div>

            <!-- Footer Info -->
            <div id="verified-footer" class="hidden mt-8 text-center pb-4">
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 dark:bg-slate-800 rounded-full">
                    <span class="material-symbols-outlined text-primary text-sm" style="font-variation-settings: 'FILL' 1;">verified</span>
                    <span class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">All accounts are verified</span>
                </div>
            </div>

            <div class="h-20"></div> <!-- Spacer for Bottom Nav -->
        </main>

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

        <!-- Image Preview Modal -->
        <div id="image-preview-modal" class="fixed inset-0 bg-black/95 z-[100] hidden" onclick="closeImagePreview()">
            <div class="relative w-full h-full flex items-center justify-center p-4">
                <!-- Close button -->
                <button onclick="closeImagePreview()"
                    class="absolute top-4 right-4 w-12 h-12 flex items-center justify-center rounded-full bg-white/20 backdrop-blur-sm hover:bg-white/30 transition-all z-10">
                    <span class="material-symbols-outlined text-white text-3xl">close</span>
                </button>
                <!-- Image Container -->
                <div class="relative max-w-full max-h-full flex items-center justify-center" style="max-width: 90vw; max-height: 90vh;">
                    <img id="modal-image" src="" alt="" class="max-w-full max-h-full object-contain rounded-lg" />
                </div>
                <!-- Alt text -->
                <p id="modal-alt"
                    class="absolute bottom-8 left-1/2 -translate-x-1/2 text-white text-center text-sm font-medium max-w-md bg-black/50 px-4 py-2 rounded-lg">
                </p>
            </div>
        </div>

        <?php include __DIR__ . '/navbar.php'; ?>
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
            z-index: 60;
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
        let allPages = [];
        const id_cabang = <?php echo $id_cabang; ?>;
        const linkModal = document.getElementById('link-modal');
        let pendingLinkUrl = '';

        // Toggle search visibility
        function toggleSearch() {
            const searchContainer = document.getElementById('search-container');
            const branchName = document.getElementById('branch-name');
            const searchBtn = document.getElementById('search-btn');

            if (searchContainer.classList.contains('hidden')) {
                searchContainer.classList.remove('hidden');
                branchName.classList.add('hidden');
                searchBtn.classList.add('hidden');
                document.getElementById('search-input').focus();
            } else {
                clearSearch();
            }
        }

        // Clear search
        function clearSearch() {
            const searchContainer = document.getElementById('search-container');
            const branchName = document.getElementById('branch-name');
            const searchBtn = document.getElementById('search-btn');
            const searchInput = document.getElementById('search-input');

            searchInput.value = '';
            searchContainer.classList.add('hidden');
            branchName.classList.remove('hidden');
            searchBtn.classList.remove('hidden');

            // Re-render all pages
            renderPages(allPages);
        }

        // Search functionality
        document.getElementById('search-input').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const filtered = allPages.filter(page =>
                page.nama_halaman.toLowerCase().includes(searchTerm) ||
                page.username_halaman.toLowerCase().includes(searchTerm)
            );
            renderPages(filtered);
        });

        // Load branch detail
        async function loadBranchDetail() {
            const branchNameEl = document.getElementById('branch-name');

            try {
                const response = await fetch(`../api/cabang/detail.php?id_cabang=${id_cabang}`);
                const result = await response.json();

                if (result.success && result.data) {
                    branchNameEl.textContent = result.data.nama_cabang;
                } else {
                    branchNameEl.textContent = 'Pages';
                }
            } catch (err) {
                branchNameEl.textContent = 'Pages';
            }
        }

        // Floating header shadow on scroll
        window.addEventListener('scroll', () => {
            const header = document.getElementById('main-header');
            if (window.scrollY > 10) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });

        // Open link modal
        function openLinkModal(url) {
            pendingLinkUrl = url;
            linkModal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        // Close link modal
        function closeLinkModal() {
            linkModal.classList.remove('active');
            document.body.style.overflow = '';
            pendingLinkUrl = '';
        }

        // Close modal when clicking outside
        if (linkModal) {
            linkModal.addEventListener('click', function(e) {
                if (e.target === linkModal) {
                    closeLinkModal();
                }
            });
        }

        // Close modal on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && linkModal.classList.contains('active')) {
                closeLinkModal();
            }
            if (e.key === 'Escape' && !imagePreviewModal.classList.contains('hidden')) {
                closeImagePreview();
            }
        });

        // Image Preview Modal
        const imagePreviewModal = document.getElementById('image-preview-modal');

        // Open image preview
        function openImagePreview(imageUrl, caption) {
            const modalImage = document.getElementById('modal-image');
            const modalAlt = document.getElementById('modal-alt');
            
            modalImage.src = imageUrl;
            modalImage.alt = caption;
            modalAlt.textContent = caption;
            imagePreviewModal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        // Close image preview
        function closeImagePreview() {
            const imagePreviewModal = document.getElementById('image-preview-modal');
            imagePreviewModal.classList.add('hidden');
            document.body.style.overflow = '';
        }

        // Handle confirm link button
        document.getElementById('confirm-link-btn').addEventListener('click', function(e) {
            e.preventDefault();
            if (pendingLinkUrl) {
                window.open(pendingLinkUrl, '_blank');
                closeLinkModal();
            }
        });

        // Load pages from API
        async function loadPages() {
            const loading = document.getElementById('loading');
            const error = document.getElementById('error');
            const section = document.getElementById('pages-section');
            const container = document.getElementById('pages-container');
            const empty = document.getElementById('empty');
            const verifiedFooter = document.getElementById('verified-footer');

            try {
                const response = await fetch(`../api/halaman/list.php?id_cabang=${id_cabang}&aktif=1`);
                const result = await response.json();

                loading.classList.add('hidden');
                error.classList.add('hidden');

                if (result.success && result.data && result.data.length > 0) {
                    allPages = result.data;
                    section.classList.remove('hidden');
                    verifiedFooter.classList.remove('hidden');
                    verifiedFooter.classList.add('flex');
                    renderPages(allPages);
                } else {
                    section.classList.add('hidden');
                    empty.classList.remove('hidden');
                    empty.classList.add('flex');
                }
            } catch (err) {
                loading.classList.add('hidden');
                error.classList.remove('hidden');
                error.classList.add('flex');
                document.getElementById('error-message').textContent = 'Failed to load pages. Please check your connection.';
            }
        }

        // Render pages
        function renderPages(pages) {
            const container = document.getElementById('pages-container');

            if (pages.length === 0) {
                const section = document.getElementById('pages-section');
                const empty = document.getElementById('empty');
                const verifiedFooter = document.getElementById('verified-footer');
                section.classList.add('hidden');
                empty.classList.remove('hidden');
                empty.classList.add('flex');
                verifiedFooter.classList.add('hidden');
                verifiedFooter.classList.remove('flex');
                return;
            }

            container.innerHTML = pages.map(page => {
                const imageUrl = page.logo ? `../images/${page.logo}` : 'https://via.placeholder.com/56x56?text=No+Image';
                
                return `
                <div class="group flex items-center p-4 bg-white dark:bg-slate-900 rounded-xl shadow-sm border border-slate-200 dark:border-slate-800 transition-all hover:bg-slate-50 dark:hover:bg-slate-800">
                    <div class="relative w-14 h-14 rounded-lg overflow-hidden flex-shrink-0 bg-slate-100 dark:bg-slate-800 cursor-pointer"
                        onclick="openImagePreview('${imageUrl}', '${page.nama_halaman.replace(/'/g, "\\'")}')">
                        <img alt="${page.nama_halaman}" class="w-full h-full object-cover"
                            src="${imageUrl}" />
                    </div>
                    <div class="ml-4 flex-grow cursor-pointer"
                        onclick="openLinkModal('${page.link.replace(/'/g, "\\'")}')">
                        <h3 class="font-semibold text-slate-900 dark:text-slate-100 text-base leading-none mb-1">
                            ${page.nama_halaman}</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">${page.username_halaman || 'Official Account'}</p>
                    </div>
                    <div class="flex items-center gap-2 cursor-pointer"
                        onclick="openLinkModal('${page.link.replace(/'/g, "\\'")}')">
                        <span class="text-xs text-slate-400 dark:text-slate-500 font-medium">Go to page</span>
                        <span class="material-symbols-outlined text-slate-400 group-hover:text-primary transition-colors text-lg">open_in_new</span>
                    </div>
                </div>
            `}).join('');
        }

        // Load pages on page load
        document.addEventListener('DOMContentLoaded', () => {
            loadBranchDetail();
            loadPages();
        });
    </script>
</body>

</html>
