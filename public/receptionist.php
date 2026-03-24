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
    <title>Receptionist Contacts - Botanic Groups</title>
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
        // Open WhatsApp call confirmation modal
        function openCallModal(phoneNumber) {
            currentPhoneNumber = phoneNumber;
            const modal = document.getElementById('call-modal');
            const modalPhone = document.getElementById('modal-phone-number');
            
            // Format phone number for display
            const formattedNumber = phoneNumber.replace(/(\+\d{1})(\d{3})(\d{3})(\d{4})/, '$1 ($2) $3-$4');
            modalPhone.textContent = formattedNumber;
            
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
                window.open(`https://wa.me/${cleanNumber}`, '_blank');
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
    </script>
</head>

<body class="bg-background-light dark:bg-background-dark font-display text-slate-900 dark:text-slate-100">
    <div class="relative flex min-h-screen w-full flex-col overflow-x-hidden">
        <!-- Floating Header -->
        <header id="main-header" class="fixed top-0 left-0 right-0 flex items-center bg-white/80 dark:bg-slate-900/80 backdrop-blur-md p-4 pb-2 justify-between z-50 border-b border-slate-200 dark:border-slate-700 transition-shadow duration-300">
            <a href="index.php" class="text-slate-900 dark:text-slate-100 flex size-12 shrink-0 items-center cursor-pointer">
                <span class="material-symbols-outlined text-2xl font-bold">arrow_back</span>
            </a>
            <h1 class="text-slate-900 dark:text-slate-100 text-lg font-bold leading-tight tracking-tight flex-1 text-center">
                Receptionist Contacts
            </h1>
           
        </header>

        <!-- Spacer for fixed header -->
        <div class="h-[73px] shrink-0"></div>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto pb-24" id="fo-container">
            <!-- Header Section -->
            <section class="px-4 py-4">
                <h2 class="text-xl font-bold mb-2 px-0 text-primary">
                    Receptionists
                </h2>
                <p class="text-slate-500 dark:text-slate-400 text-sm">List of active receptionists at this branch, select and chat using WhatsApp for hotel services.</p>
            </section>

            <!-- Loading State -->
            <div id="loading" class="flex flex-col items-center justify-center py-12">
                <div class="spinner w-10 h-10 mb-4"></div>
                <p class="text-slate-500 dark:text-slate-400">Loading receptionist contacts...</p>
            </div>

            <!-- Error State -->
            <div id="error" class="hidden flex-col items-center justify-center py-12">
                <span class="material-symbols-outlined text-red-500 text-5xl mb-4">error</span>
                <p class="text-slate-500 dark:text-slate-400 text-center" id="error-message"></p>
                <button onclick="loadFrontOffice()" class="mt-4 px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary/90">
                    Retry
                </button>
            </div>

            <!-- Front Office Cards Container -->
            <div id="fo-cards" class="space-y-4 px-4"></div>

            <!-- Emergency Info -->
            <div class="px-4 mt-4">
                <div class="bg-amber-100 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 p-4 rounded-xl flex items-start gap-3">
                    <span class="material-symbols-outlined text-amber-600 dark:text-amber-500">warning</span>
                    <div>
                        <p class="text-amber-800 dark:text-amber-400 font-semibold text-sm">Emergency Assistance</p>
                        <p class="text-amber-700 dark:text-amber-500/80 text-xs mt-1">
                            For urgent medical or safety concerns, please contact local emergency services immediately or use the SOS button in your room.
                        </p>
                    </div>
                </div>
            </div>
        </main>

        <?php include __DIR__ . '/navbar.php'; ?>
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
                        This will open WhatsApp and start a call with the receptionist.
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
    <script src="script/receptionist.js"></script>
</body>

</html>
