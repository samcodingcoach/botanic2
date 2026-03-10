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
    <title>Hotel Facilities - Botanic Groups</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap"
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
        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        /* Floating header shadow on scroll */
        #main-header.scrolled {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
    </style>
    <script>
        // Slider navigation function
        function slideImage(sliderId, direction) {
            const slider = document.getElementById(sliderId);
            const scrollAmount = slider.offsetWidth;
            slider.scrollBy({
                left: direction * scrollAmount,
                behavior: 'smooth'
            });
        }

        // Open image preview modal
        function openImagePreview(imageUrl, altText) {
            const modal = document.getElementById('image-preview-modal');
            const modalImage = document.getElementById('modal-image');
            const modalAlt = document.getElementById('modal-alt');
            
            modalImage.style.backgroundImage = `url("${imageUrl}")`;
            modalAlt.textContent = altText || '';
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        // Close image preview modal
        function closeImagePreview() {
            const modal = document.getElementById('image-preview-modal');
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }
    </script>
</head>

<body class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-slate-100 antialiased">
    <div class="relative flex min-h-screen w-full flex-col bg-background-light dark:bg-background-dark overflow-x-hidden">
        <!-- Floating Header -->
        <header id="main-header" class="fixed top-0 left-0 right-0 flex items-center bg-white/80 dark:bg-slate-900/80 backdrop-blur-md p-4 pb-2 justify-between z-50 border-b border-slate-200 dark:border-slate-700 transition-shadow duration-300">
            <a href="index.php" class="text-slate-900 dark:text-slate-100 flex size-12 shrink-0 items-center cursor-pointer">
                <span class="material-symbols-outlined text-2xl font-bold">arrow_back</span>
            </a>
            <h2 id="branch-name" class="text-slate-900 dark:text-slate-100 text-lg font-bold leading-tight tracking-[-0.015em] flex-1 text-center truncate px-4 transition-all duration-300">
                Loading...
            </h2>
            <div class="flex w-12 items-center justify-end">
                <span class="material-symbols-outlined text-2xl font-bold text-slate-400">search</span>
            </div>
        </header>

        <!-- Spacer for fixed header -->
        <div class="h-[73px] shrink-0"></div>

        <!-- Main Content -->
        <main class="flex-1 pb-24 space-y-6 mt-4" id="facilities-container">
            <!-- Loading State -->
            <div id="loading" class="flex flex-col items-center justify-center py-12">
                <div class="spinner w-10 h-10 mb-4"></div>
                <p class="text-slate-500 dark:text-slate-400">Loading facilities...</p>
            </div>

            <!-- Error State -->
            <div id="error" class="hidden flex-col items-center justify-center py-12">
                <span class="material-symbols-outlined text-red-500 text-5xl mb-4">error</span>
                <p class="text-slate-500 dark:text-slate-400 text-center" id="error-message"></p>
                <button onclick="loadFacilities()" class="mt-4 px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary/90">
                    Retry
                </button>
            </div>
        </main>

        <?php include __DIR__ . '/navbar.php'; ?>
    </div>

    <!-- Image Preview Modal -->
    <div id="image-preview-modal" class="fixed inset-0 bg-black/95 z-[100] hidden" onclick="closeImagePreview()">
        <div class="relative w-full h-full flex items-center justify-center p-4">
            <!-- Close button -->
            <button onclick="closeImagePreview()"
                class="absolute top-4 right-4 w-12 h-12 flex items-center justify-center rounded-full bg-white/20 backdrop-blur-sm hover:bg-white/30 transition-all z-10">
                <span class="material-symbols-outlined text-white text-3xl">close</span>
            </button>
            <!-- Image -->
            <div id="modal-image" class="max-w-full max-h-full bg-center bg-contain bg-no-repeat"
                style="max-width: 90vw; max-height: 90vh;"></div>
            <!-- Alt text -->
            <p id="modal-alt"
                class="absolute bottom-8 left-1/2 -translate-x-1/2 text-white text-center text-sm font-medium max-w-md">
            </p>
        </div>
    </div>

    <script>
        // Store id_cabang for JS to use
        window.ID_CABANG = <?php echo $id_cabang; ?>;
        console.log('ID_CABANG set to:', window.ID_CABANG);

        // Floating header shadow on scroll
        window.addEventListener('scroll', () => {
            const header = document.getElementById('main-header');
            if (window.scrollY > 10) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
    </script>
    <script src="script/fasilitas.js"></script>
</body>

</html>
