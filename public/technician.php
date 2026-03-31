<?php
session_start();
require_once __DIR__ . '/../config/koneksi.php';

// Set current page for navbar
$currentPage = 'technician';

// Get id_cabang from session or GET parameter
$id_cabang = isset($_GET['id_cabang']) ? (int) $_GET['id_cabang'] : (isset($_SESSION['id_cabang']) ? $_SESSION['id_cabang'] : 0);

// Fetch data directly from database
$technicians = [];

if ($id_cabang > 0) {
    $query = "SELECT
        teknisi.id_teknisi,
        teknisi.kode_teknisi,
        teknisi.nama_teknisi,
        teknisi.id_cabang,
        cabang.nama_cabang,
        teknisi.jabatan,
        teknisi.jenis_kelamin,
        teknisi.wa,
        teknisi.aktif,
        teknisi.created_date,
        teknisi.spesialis
    FROM
        teknisi
    INNER JOIN
        cabang
    ON
        teknisi.id_cabang = cabang.id_cabang
    WHERE
        teknisi.id_cabang = ?
    ORDER BY teknisi.id_teknisi ASC";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_cabang);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $technicians[] = $row;
    }
    
    $stmt->close();
}
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
                    borderRadius: { "DEFAULT": "0.25rem", "lg": "0.5rem", "xl": "0.75rem", "full": "9999px" },
                },
            },
        }
    </script>
    <style>
        body {
            min-height: max(884px, 100dvh);
        }
    </style>
</head>

<body class="bg-background-light dark:bg-background-dark font-display text-slate-900 dark:text-slate-100">
    <div class="relative flex min-h-screen w-full flex-col overflow-x-hidden">
        <!-- Header -->
        <div
            class="flex items-center bg-white/80 backdrop-blur-md p-4 pb-2 justify-between sticky top-0 z-50 border-b border-slate-200">
            <div class="text-slate-900 flex size-12 shrink-0 items-center cursor-pointer" onclick="window.history.back()">
                <span class="material-symbols-outlined text-2xl font-bold">arrow_back</span>
            </div>
            <h2 class="text-slate-900 text-lg font-bold leading-tight tracking-[-0.015em] flex-1 text-center">
                Technician Service
            </h2>
            <div class="flex w-12 items-center justify-end">
                <button
                    class="flex cursor-pointer items-center justify-center rounded-xl h-12 bg-transparent text-slate-900 p-0">
                    <span class="material-symbols-outlined text-2xl font-bold">search</span>
                </button>
            </div>
        </div>


        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto pb-24">
            <div class="px-4 py-4">
                <h2 class="text-xl font-bold mb-2 px-0 text-primary">Available Technicians</h2>
                <p class="text-slate-500 dark:text-slate-400 text-sm mb-6">Contact our technical team for maintenance
                    or repair assistance.</p>
            </div>
            
            <?php if (empty($technicians)): ?>
            <div class="px-4 py-8 text-center">
                <span class="material-symbols-outlined text-6xl text-slate-300 dark:text-slate-600 mb-4">engineering</span>
                <p class="text-slate-500 dark:text-slate-400 text-sm">No technicians available at the moment.</p>
            </div>
            <?php else: ?>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 px-4">
                <?php foreach ($technicians as $tech): ?>
                    <?php 
                        $isActive = $tech['aktif'] == 1;
                        $isMale = $tech['jenis_kelamin'] == 1;
                        $avatarColor = $isMale ? 'bg-blue-200 dark:bg-blue-900 text-blue-500' : 'bg-pink-200 dark:bg-pink-900 text-pink-500';
                        $statusColor = $isActive ? 'bg-green-100 text-green-700' : 'bg-slate-200 dark:bg-slate-700 text-slate-500 dark:text-slate-400';
                        $statusLabel = $isActive ? 'Active' : 'Inactive';
                        $cardClass = $isActive ? '' : 'opacity-60 filter grayscale-[0.3]';
                        $genderIcon = $isMale ? 'man' : 'woman';
                    ?>
                <!-- Card -->
                <div
                    class="group bg-white dark:bg-slate-900 rounded-xl p-1 transition-all hover:shadow-[0_20px_40px_rgba(0,0,0,0.08)] border border-slate-100 dark:border-slate-800 <?= $cardClass ?>">
                    <div class="p-5 bg-background-light dark:bg-slate-800 rounded-lg h-full <?= !$isActive ? 'border border-dashed border-slate-300 dark:border-slate-600' : '' ?>">
                        <div class="flex justify-between items-start mb-6">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-12 h-12 rounded-xl <?= $avatarColor ?> flex items-center justify-center">
                                    <span class="material-symbols-outlined"><?= $genderIcon ?></span>
                                </div>
                                <div>
                                    <span
                                        class="text-[10px] font-bold text-primary tracking-widest uppercase"><?= htmlspecialchars($tech['kode_teknisi']) ?></span>
                                    <h3 class="text-lg font-bold leading-none text-slate-900 dark:text-slate-100">
                                        <?= htmlspecialchars($tech['nama_teknisi']) ?></h3>
                                </div>
                            </div>
                            <span
                                class="px-3 py-1 <?= $statusColor ?> text-[10px] font-bold rounded-full uppercase tracking-tighter"><?= $statusLabel ?></span>
                        </div>
                        <div class="space-y-4 mb-6">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p
                                        class="text-[10px] text-slate-500 dark:text-slate-400/60 font-bold uppercase tracking-wider mb-1">
                                        Job Title</p>
                                    <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                                        <?= htmlspecialchars($tech['jabatan']) ?>
                                    </p>
                                </div>
                                <div>
                                    <p
                                        class="text-[10px] text-slate-500 dark:text-slate-400/60 font-bold uppercase tracking-wider mb-1">
                                        Specialization</p>
                                    <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                                        <?= htmlspecialchars($tech['spesialis'] ?? 'N/A') ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div
                            class="pt-4 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between">
                            <?php if ($isActive): ?>
                            <div class="flex items-center gap-2 w-full">
                                <a href="https://wa.me/<?= preg_replace('/^0/', '62', $tech['wa']) ?>"
                                    target="_blank"
                                    class="flex-1 flex items-center justify-center gap-2 py-2 rounded-lg bg-[#25D366] text-white text-xs font-bold hover:opacity-90 transition-opacity">
                                    <span class="material-symbols-outlined text-sm">chat</span>Chat
                                </a>
                                <button onclick="openCallModal('<?= $tech['wa'] ?>')"
                                    class="flex-1 flex items-center justify-center gap-2 py-2 rounded-lg border border-primary text-primary text-xs font-bold hover:bg-primary/5 transition-colors">
                                    <span class="material-symbols-outlined text-sm">call</span>Call
                                </button>
                            </div>
                            <?php else: ?>
                            <div class="flex flex-col gap-2 w-full">
                                <div class="flex items-center gap-2 w-full">
                                    <button
                                        class="flex-1 flex items-center justify-center gap-2 py-2 rounded-lg bg-slate-200 dark:bg-slate-700 text-slate-400 dark:text-slate-500 text-xs font-bold cursor-not-allowed"
                                        disabled>
                                        <span class="material-symbols-outlined text-sm">chat</span>Chat
                                    </button>
                                    <button
                                        class="flex-1 flex items-center justify-center gap-2 py-2 rounded-lg border border-slate-300 dark:border-slate-600 text-slate-400 dark:text-slate-500 text-xs font-bold cursor-not-allowed"
                                        disabled>
                                        <span class="material-symbols-outlined text-sm">call</span>Call
                                    </button>
                                </div>
                                <span
                                    class="text-[10px] font-bold text-slate-500 dark:text-slate-400/60 italic text-center">Access
                                    Revoked</span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

        </main>
    </div>

    <?php include __DIR__ . '/navbar.php'; ?>

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
        let currentPhoneNumber = '';

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

        // Close modal on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeCallModal();
            }
        });
    </script>
</body>

</html>
