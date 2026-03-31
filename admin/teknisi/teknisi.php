<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['id_users'])) {
    header('Location: ../login.php');
    exit;
}

// Fetch data from API
$apiUrl = 'http://localhost/botanic/api/teknisi/list.php';
$apiResponse = file_get_contents($apiUrl);
$apiData = json_decode($apiResponse, true);

$teknisiList = [];
$totalCount = 0;
$message = '';

if ($apiData && $apiData['success']) {
    $teknisiList = $apiData['data'];
    $totalCount = $apiData['count'];
    $message = $apiData['message'];
}

// Fetch cabang list for dropdown
$cabangApiUrl = 'http://localhost/botanic/api/cabang/list.php';
$cabangApiResponse = file_get_contents($cabangApiUrl);
$cabangApiData = json_decode($cabangApiResponse, true);
$cabangList = [];
if ($cabangApiData && $cabangApiData['success']) {
    $cabangList = $cabangApiData['data'];
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Admin Panel - Manajemen Teknisi</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&amp;display=swap"
        rel="stylesheet" />
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap"
        rel="stylesheet" />
    <link rel="stylesheet" href="../css/style.css"/>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#4b774d",
                        "background-light": "#f7f7f7",
                        "background-dark": "#171b17",
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

<body class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-slate-100 font-display">
    <!-- Toast Notification Container -->
    <div id="toastContainer"></div>

    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <?php include __DIR__ . '/../sidebar.php'; ?>
        <!-- Main Content -->
        <main class="flex-1 flex flex-col overflow-hidden bg-background-light dark:bg-background-dark">
            <!-- Header -->
            <header
                class="bg-white dark:bg-background-dark border-b border-slate-200 dark:border-slate-800 px-4 md:px-8 py-4 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <!-- Hamburger Menu (Mobile) -->
                    <button onclick="toggleSidebar()"
                        class="md:hidden p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                        <span class="material-symbols-outlined">menu</span>
                    </button>
                    <h2 class="text-lg font-semibold text-slate-800 dark:text-white">Manajemen Teknisi</h2>
                </div>
                <div class="flex items-center gap-4">
                    <div class="relative hidden sm:block">
                        <span
                            class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xl leading-none">search</span>
                        <input id="searchInput"
                            class="pl-10 pr-4 py-2 bg-slate-100 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary text-sm w-64"
                            placeholder="Cari teknisi..." type="text" onkeyup="filterData()" />
                    </div>
                    <button class="sm:hidden p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                        onclick="toggleSearch()">
                        <span class="material-symbols-outlined">search</span>
                    </button>
                </div>
            </header>
            <!-- Dashboard Content -->
            <div class="flex-1 overflow-y-auto p-4 md:p-8 space-y-8">
                <!-- Title and CTA -->
                <div class="flex flex-col sm:flex-row items-start sm:items-end justify-between gap-4">
                    <div>
                        <h3 class="text-2xl md:text-3xl font-black text-slate-900 dark:text-white tracking-tight">Daftar
                            Teknisi</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Kelola data teknisi untuk maintenance
                            dan perbaikan fasilitas.</p>
                    </div>
                    <button id="btnTambahTeknisi"
                        class="flex items-center gap-2 px-5 py-2.5 bg-primary text-white font-bold rounded-lg hover:bg-primary/90 transition-all shadow-sm w-full sm:w-auto justify-center">
                        <span class="material-symbols-outlined">add</span>
                        <span>Tambah Teknisi</span>
                    </button>
                </div>

                <!-- Filter Cabang -->
                <div class="flex items-center gap-4">
                    <div class="relative flex-1 max-w-xs">
                        <span
                            class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">business</span>
                        <select id="filterCabang" onchange="filterByCabang()"
                            class="w-full pl-10 pr-10 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none text-sm appearance-none cursor-pointer">
                            <option value="">-- Semua Cabang --</option>
                            <?php foreach ($cabangList as $cabang): ?>
                            <option value="<?php echo $cabang['id_cabang']; ?>">
                                <?php echo htmlspecialchars($cabang['nama_cabang']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Table Container -->
                <div
                    class="bg-white dark:bg-background-dark rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
                    <!-- Desktop Table -->
                    <div class="hidden md:block overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr
                                    class="bg-slate-50 dark:bg-slate-800/50 border-b border-slate-200 dark:border-slate-800">
                                    <th
                                        class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-center">
                                        Teknisi</th>
                                    <th
                                        class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                        Kode</th>
                                    <th
                                        class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                        Jabatan</th>
                                    <th
                                        class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                        Spesialis</th>
                                    <th
                                        class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-center">
                                        Gender</th>
                                    <th
                                        class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                        WhatsApp</th>
                                    <th
                                        class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-center">
                                        Status</th>
                                    <th
                                        class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right">
                                        Actions</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody" class="divide-y divide-slate-100 dark:divide-slate-800">
                                <!-- Data will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                    <!-- Mobile Card View -->
                    <div id="mobileView" class="md:hidden divide-y divide-slate-100 dark:divide-slate-800">
                        <!-- Cards will be loaded here -->
                    </div>
                    <!-- No Data State -->
                    <div id="noData" class="hidden p-8 text-center">
                        <span class="material-symbols-outlined text-6xl text-slate-300 dark:text-slate-600 mb-4">engineering</span>
                        <p class="text-slate-500 dark:text-slate-400">Tidak ada data teknisi</p>
                    </div>
                    <!-- Pagination Footer -->
                    <div id="paginationContainer"
                        class="hidden bg-slate-50 dark:bg-slate-800/50 px-4 md:px-6 py-4 flex flex-col sm:flex-row items-center justify-between gap-4 border-t border-slate-200 dark:border-slate-800">
                        <p id="showingText" class="text-xs text-slate-500 dark:text-slate-400">Showing 0 of 0 results</p>
                        <div class="flex items-center gap-2" id="paginationButtons">
                            <!-- Pagination buttons will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Add Teknisi Modal -->
    <div class="fixed inset-0 z-50 flex items-center justify-center hidden" id="add-teknisi-modal">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        <!-- Modal Content -->
        <div
            class="relative bg-white dark:bg-background-dark w-full max-w-lg mx-4 rounded-xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
            <!-- Header (Fixed) -->
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between flex-shrink-0">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Tambah Teknisi Baru</h3>
                <button class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 btn-close-modal">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <!-- Scrollable Content -->
            <div class="overflow-y-auto px-6 py-4 space-y-4 scrollbar-thin scrollbar-thumb-slate-300 dark:scrollbar-thumb-slate-600">
                <form id="formTambahTeknisi" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Kode
                                Teknisi <span class="text-red-500">*</span></label>
                            <input id="kode_teknisi" name="kode_teknisi"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                                placeholder="Contoh: TCH-001" type="text" required />
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Nama
                                Teknisi <span class="text-red-500">*</span></label>
                            <input id="nama_teknisi" name="nama_teknisi"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                                placeholder="Nama lengkap teknisi" type="text" required />
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Cabang
                                <span class="text-red-500">*</span></label>
                            <select id="id_cabang" name="id_cabang"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                                required>
                                <option value="">Pilih Cabang</option>
                                <?php foreach ($cabangList as $cabang): ?>
                                <option value="<?php echo $cabang['id_cabang']; ?>">
                                    <?php echo htmlspecialchars($cabang['nama_cabang']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Jabatan
                                <span class="text-red-500">*</span></label>
                            <input id="jabatan" name="jabatan"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                                placeholder="Contoh: Senior Technician" type="text" required />
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Spesialis</label>
                            <input id="spesialis" name="spesialis"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                                placeholder="Contoh: AC & Electrical" type="text" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Jenis
                                Kelamin <span class="text-red-500">*</span></label>
                            <select id="jenis_kelamin" name="jenis_kelamin"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                                required>
                                <option value="">Pilih</option>
                                <option value="1">Pria</option>
                                <option value="0">Wanita</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">WhatsApp
                                <span class="text-red-500">*</span></label>
                            <input id="wa" name="wa"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                                placeholder="0812-xxxx-xxxx" type="text" required />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Status
                                Aktif</label>
                            <select id="aktif" name="aktif"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm">
                                <option value="1">Aktif</option>
                                <option value="0">Non Aktif</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <!-- Footer (Fixed) -->
            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-3 flex-shrink-0 bg-white dark:bg-background-dark">
                <button
                    class="px-4 py-2 text-sm font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors btn-close-modal"
                    type="button">
                    Batal
                </button>
                <button id="btnSimpan"
                    class="px-6 py-2 text-sm font-bold text-white bg-primary hover:bg-primary/90 rounded-lg shadow-sm transition-all flex items-center gap-2"
                    type="button">
                    <span class="material-symbols-outlined text-sm">save</span>
                    <span>Simpan</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Edit Teknisi Modal -->
    <div class="fixed inset-0 z-50 flex items-center justify-center hidden" id="edit-teknisi-modal">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        <!-- Modal Content -->
        <div
            class="relative bg-white dark:bg-background-dark w-full max-w-lg mx-4 rounded-xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
            <!-- Header (Fixed) -->
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between flex-shrink-0">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Edit Teknisi</h3>
                <button class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 btn-close-edit-modal">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <!-- Scrollable Content -->
            <div class="overflow-y-auto px-6 py-4 space-y-4 scrollbar-thin scrollbar-thumb-slate-300 dark:scrollbar-thumb-slate-600">
                <form id="formEditTeknisi" class="space-y-4">
                    <input type="hidden" id="edit_id_teknisi" name="id_teknisi" />
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Kode
                                Teknisi <span class="text-red-500">*</span></label>
                            <input id="edit_kode_teknisi" name="kode_teknisi"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                                placeholder="Contoh: TCH-001" type="text" required />
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Nama
                                Teknisi <span class="text-red-500">*</span></label>
                            <input id="edit_nama_teknisi" name="nama_teknisi"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                                placeholder="Nama lengkap teknisi" type="text" required />
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Cabang
                                <span class="text-red-500">*</span></label>
                            <select id="edit_id_cabang" name="id_cabang"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                                required>
                                <option value="">Pilih Cabang</option>
                                <?php foreach ($cabangList as $cabang): ?>
                                <option value="<?php echo $cabang['id_cabang']; ?>">
                                    <?php echo htmlspecialchars($cabang['nama_cabang']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Jabatan
                                <span class="text-red-500">*</span></label>
                            <input id="edit_jabatan" name="jabatan"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                                placeholder="Contoh: Senior Technician" type="text" required />
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Spesialis</label>
                            <input id="edit_spesialis" name="spesialis"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                                placeholder="Contoh: AC & Electrical" type="text" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Jenis
                                Kelamin <span class="text-red-500">*</span></label>
                            <select id="edit_jenis_kelamin" name="jenis_kelamin"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                                required>
                                <option value="">Pilih</option>
                                <option value="1">Pria</option>
                                <option value="0">Wanita</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">WhatsApp
                                <span class="text-red-500">*</span></label>
                            <input id="edit_wa" name="wa"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                                placeholder="0812-xxxx-xxxx" type="text" required />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Status
                                Aktif</label>
                            <select id="edit_aktif" name="aktif"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm">
                                <option value="1">Aktif</option>
                                <option value="0">Non Aktif</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <!-- Footer (Fixed) -->
            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-3 flex-shrink-0 bg-white dark:bg-background-dark">
                <button
                    class="px-4 py-2 text-sm font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors btn-close-edit-modal"
                    type="button">
                    Batal
                </button>
                <button id="btnUpdate"
                    class="px-6 py-2 text-sm font-bold text-white bg-primary hover:bg-primary/90 rounded-lg shadow-sm transition-all flex items-center gap-2"
                    type="button">
                    <span class="material-symbols-outlined text-sm">save</span>
                    <span>Update</span>
                </button>
            </div>
        </div>
    </div>

    <script>
        // Constants
        const maxSize = 1 * 1024 * 1024; // 1MB in bytes

        // Toast Notification Function
        function showToast(message, type = 'success') {
            const toastContainer = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;

            const icon = type === 'success' ? 'check_circle' : 'error';
            toast.innerHTML = `
                <span class="material-symbols-outlined toast-icon">${icon}</span>
                <span class="toast-message">${message}</span>
            `;

            toastContainer.appendChild(toast);

            // Animate in
            setTimeout(() => toast.classList.add('show'), 10);

            // Remove after 3 seconds
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        // Modal functions
        const modal = document.getElementById('add-teknisi-modal');
        const btnTambah = document.getElementById('btnTambahTeknisi');
        const btnClose = document.querySelectorAll('.btn-close-modal');
        const btnSimpan = document.getElementById('btnSimpan');
        const formTambah = document.getElementById('formTambahTeknisi');

        function openModal() {
            modal.classList.remove('hidden');
        }

        function closeModal() {
            modal.classList.add('hidden');
            formTambah.reset();
        }

        // Event listeners
        if (btnTambah) {
            btnTambah.addEventListener('click', openModal);
        }

        btnClose.forEach(btn => {
            btn.addEventListener('click', closeModal);
        });

        // Close modal when clicking on backdrop
        modal.addEventListener('click', function(e) {
            if (e.target === modal.querySelector('.absolute')) {
                closeModal();
            }
        });

        // Handle Save Button
        if (btnSimpan) {
            btnSimpan.addEventListener('click', async function() {
                // Validate form
                const requiredFields = ['kode_teknisi', 'nama_teknisi', 'id_cabang', 'jabatan', 'wa', 'jenis_kelamin'];
                let isValid = true;
                let emptyFields = [];

                requiredFields.forEach(field => {
                    const input = document.getElementById(field);
                    if (!input.value.trim()) {
                        isValid = false;
                        emptyFields.push(input.previousElementSibling.textContent.replace('*', '').trim());
                        input.classList.add('border-red-500');
                    } else {
                        input.classList.remove('border-red-500');
                    }
                });

                if (!isValid) {
                    showToast('Harap isi semua field yang wajib diisi: ' + emptyFields.join(', '), 'error');
                    return;
                }

                // Confirm before save
                const confirmed = confirm('Apakah Anda yakin ingin menyimpan data teknisi ini?');
                if (!confirmed) return;

                // Show loading state
                btnSimpan.disabled = true;
                btnSimpan.innerHTML = '<span class="material-symbols-outlined spinner">sync</span><span>Menyimpan...</span>';

                // Prepare FormData
                const formData = new FormData(formTambah);

                // Send to API
                fetch('../../api/teknisi/new.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Data teknisi berhasil disimpan!', 'success');
                        closeModal();
                        // Refresh page after short delay
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        showToast(data.message || 'Gagal menyimpan data', 'error');
                        // Reset button
                        btnSimpan.disabled = false;
                        btnSimpan.innerHTML = '<span class="material-symbols-outlined text-sm">save</span><span>Simpan</span>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Terjadi kesalahan saat menyimpan data', 'error');
                    // Reset button
                    btnSimpan.disabled = false;
                    btnSimpan.innerHTML = '<span class="material-symbols-outlined text-sm">save</span><span>Simpan</span>';
                });
            });
        }

        // Remove border highlight on input
        document.querySelectorAll('input, select').forEach(input => {
            input.addEventListener('input', function() {
                this.classList.remove('border-red-500');
            });
        });

        // Edit Modal Functions
        const editModal = document.getElementById('edit-teknisi-modal');
        const btnCloseEdit = document.querySelectorAll('.btn-close-edit-modal');
        const btnUpdate = document.getElementById('btnUpdate');
        const formEdit = document.getElementById('formEditTeknisi');

        function openEditModal(tech) {
            document.getElementById('edit_id_teknisi').value = tech.id_teknisi;
            document.getElementById('edit_kode_teknisi').value = tech.kode_teknisi;
            document.getElementById('edit_nama_teknisi').value = tech.nama_teknisi;
            document.getElementById('edit_id_cabang').value = tech.id_cabang;
            document.getElementById('edit_jabatan').value = tech.jabatan;
            document.getElementById('edit_spesialis').value = tech.spesialis || '';
            document.getElementById('edit_jenis_kelamin').value = tech.jenis_kelamin;
            document.getElementById('edit_wa').value = tech.wa;
            document.getElementById('edit_aktif').value = tech.aktif;

            editModal.classList.remove('hidden');
        }

        function closeEditModal() {
            editModal.classList.add('hidden');
            formEdit.reset();
        }

        // Event listeners for edit modal
        btnCloseEdit.forEach(btn => {
            btn.addEventListener('click', closeEditModal);
        });

        // Close modal when clicking on backdrop
        editModal.addEventListener('click', function(e) {
            if (e.target === editModal.querySelector('.absolute')) {
                closeEditModal();
            }
        });

        // Handle edit button clicks (desktop)
        document.addEventListener('click', function(e) {
            if (e.target.closest('.btn-edit-teknisi')) {
                const btn = e.target.closest('.btn-edit-teknisi');
                openEditModal({
                    id_teknisi: btn.dataset.id,
                    kode_teknisi: btn.dataset.kode,
                    nama_teknisi: btn.dataset.nama,
                    id_cabang: btn.dataset.idCabang,
                    jabatan: btn.dataset.jabatan,
                    spesialis: btn.dataset.spesialis,
                    jenis_kelamin: btn.dataset.jk,
                    wa: btn.dataset.wa,
                    aktif: btn.dataset.aktif
                });
            }
            // Handle edit button clicks (mobile)
            if (e.target.closest('.btn-edit-teknisi-mobile')) {
                const btn = e.target.closest('.btn-edit-teknisi-mobile');
                openEditModal({
                    id_teknisi: btn.dataset.id,
                    kode_teknisi: btn.dataset.kode,
                    nama_teknisi: btn.dataset.nama,
                    id_cabang: btn.dataset.idCabang,
                    jabatan: btn.dataset.jabatan,
                    spesialis: btn.dataset.spesialis,
                    jenis_kelamin: btn.dataset.jk,
                    wa: btn.dataset.wa,
                    aktif: btn.dataset.aktif
                });
            }
        });

        // Handle Update Button
        if (btnUpdate) {
            btnUpdate.addEventListener('click', async function() {
                // Validate form
                const requiredFields = ['edit_kode_teknisi', 'edit_nama_teknisi', 'edit_id_cabang', 'edit_jabatan', 'edit_wa', 'edit_jenis_kelamin'];
                let isValid = true;
                let emptyFields = [];

                requiredFields.forEach(fieldId => {
                    const input = document.getElementById(fieldId);
                    if (!input.value.trim()) {
                        isValid = false;
                        emptyFields.push(input.previousElementSibling.textContent.replace('*', '').trim());
                        input.classList.add('border-red-500');
                    } else {
                        input.classList.remove('border-red-500');
                    }
                });

                if (!isValid) {
                    showToast('Harap isi semua field yang wajib diisi: ' + emptyFields.join(', '), 'error');
                    return;
                }

                // Confirm before update
                const confirmed = confirm('Apakah Anda yakin ingin mengupdate data teknisi ini?');
                if (!confirmed) return;

                // Show loading state
                btnUpdate.disabled = true;
                btnUpdate.innerHTML = '<span class="material-symbols-outlined spinner">sync</span><span>Updating...</span>';

                // Prepare FormData
                const formData = new FormData(formEdit);

                // Send to API
                fetch('../../api/teknisi/update.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Data teknisi berhasil diupdate!', 'success');
                        closeEditModal();
                        // Refresh page after short delay
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        showToast(data.message || 'Gagal mengupdate data', 'error');
                        // Reset button
                        btnUpdate.disabled = false;
                        btnUpdate.innerHTML = '<span class="material-symbols-outlined text-sm">save</span><span>Update</span>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Terjadi kesalahan saat mengupdate data', 'error');
                    // Reset button
                    btnUpdate.disabled = false;
                    btnUpdate.innerHTML = '<span class="material-symbols-outlined text-sm">save</span><span>Update</span>';
                });
            });
        }

        // Remove border highlight on input for edit form
        document.querySelectorAll('#formEditTeknisi input, #formEditTeknisi select').forEach(input => {
            input.addEventListener('input', function() {
                this.classList.remove('border-red-500');
            });
        });

        // Data from PHP
        const allData = <?php echo json_encode($teknisiList); ?>;
        const totalCount = <?php echo json_encode($totalCount); ?>;

        let currentPage = 1;
        const itemsPerPage = 10;
        let filteredData = [...allData];
        let totalPages = Math.ceil(filteredData.length / itemsPerPage);

        function toggleSearch() {
            const searchInput = document.getElementById('searchInput');
            searchInput.classList.toggle('hidden');
            if (!searchInput.classList.contains('hidden')) {
                searchInput.focus();
            }
        }

        function filterData() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const selectedCabang = document.getElementById('filterCabang').value;
            
            filteredData = allData.filter(item => {
                const matchesSearch = (
                    (item.nama_teknisi && item.nama_teknisi.toLowerCase().includes(searchTerm)) ||
                    (item.kode_teknisi && item.kode_teknisi.toLowerCase().includes(searchTerm)) ||
                    (item.jabatan && item.jabatan.toLowerCase().includes(searchTerm)) ||
                    (item.spesialis && item.spesialis.toLowerCase().includes(searchTerm)) ||
                    (item.nama_cabang && item.nama_cabang.toLowerCase().includes(searchTerm))
                );
                
                const matchesCabang = selectedCabang === '' || item.id_cabang == selectedCabang;
                
                return matchesSearch && matchesCabang;
            });
            currentPage = 1;
            totalPages = Math.ceil(filteredData.length / itemsPerPage);
            renderTable();
            renderMobileView();
            renderPagination();
        }

        function filterByCabang() {
            filterData();
        }

        function formatDate(dateString) {
            if (!dateString) return '-';
            const date = new Date(dateString);
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            return `${date.getDate()} ${months[date.getMonth()]} ${date.getFullYear()}`;
        }

        function getGenderIcon(jk) {
            return jk == '1' ? 'man' : 'woman';
        }

        function getGenderText(jk) {
            return jk == '1' ? 'Pria' : 'Wanita';
        }

        function getStatusClass(aktif) {
            return aktif == '1' 
                ? 'bg-green-100 text-green-700 border-green-200' 
                : 'bg-slate-100 text-slate-600 border-slate-200';
        }

        function getStatusText(aktif) {
            return aktif == '1' ? 'Aktif' : 'Non Aktif';
        }

        function renderTable() {
            const tableBody = document.getElementById('tableBody');
            const start = (currentPage - 1) * itemsPerPage;
            const end = start + itemsPerPage;
            const pageData = filteredData.slice(start, end);

            if (pageData.length === 0) {
                tableBody.innerHTML = '';
                return;
            }

            tableBody.innerHTML = pageData.map(item => `
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                    <td class="px-6 py-4">
                        <div class="font-semibold text-slate-900 dark:text-white">${item.nama_teknisi || '-'}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-primary">${item.kode_teknisi || '-'}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-slate-600 dark:text-slate-400">${item.jabatan || '-'}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-slate-600 dark:text-slate-400">${item.spesialis || '-'}</div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="inline-flex items-center gap-1 text-xs text-slate-600 dark:text-slate-400">
                            <span class="material-symbols-outlined text-sm">${getGenderIcon(item.jenis_kelamin)}</span>
                            ${getGenderText(item.jenis_kelamin)}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-slate-600 dark:text-slate-400">${item.wa || '-'}</div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="px-3 py-1 text-xs font-bold rounded-full border ${getStatusClass(item.aktif)}">
                            ${getStatusText(item.aktif)}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right space-x-2">
                        <button class="p-1.5 text-slate-400 hover:text-primary transition-colors btn-edit-teknisi"
                            data-id="${item.id_teknisi}"
                            data-kode="${item.kode_teknisi || ''}"
                            data-nama="${item.nama_teknisi || ''}"
                            data-id-cabang="${item.id_cabang || ''}"
                            data-jabatan="${item.jabatan || ''}"
                            data-spesialis="${item.spesialis || ''}"
                            data-jk="${item.jenis_kelamin || ''}"
                            data-wa="${item.wa || ''}"
                            data-aktif="${item.aktif || ''}"
                            title="Edit">
                            <span class="material-symbols-outlined text-xl">edit_square</span>
                        </button>
                        <button class="p-1.5 text-slate-400 hover:text-red-500 transition-colors btn-delete-teknisi"
                            data-id="${item.id_teknisi}"
                            data-nama="${item.nama_teknisi || ''}"
                            title="Hapus">
                            <span class="material-symbols-outlined text-xl">delete</span>
                        </button>
                    </td>
                </tr>
            `).join('');
        }

        function renderMobileView() {
            const mobileView = document.getElementById('mobileView');
            const start = (currentPage - 1) * itemsPerPage;
            const end = start + itemsPerPage;
            const pageData = filteredData.slice(start, end);

            if (pageData.length === 0) {
                mobileView.innerHTML = '';
                return;
            }

            mobileView.innerHTML = pageData.map(item => `
                <div class="p-4 space-y-3">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1 min-w-0">
                            <div class="font-semibold text-slate-900 dark:text-white">${item.nama_teknisi || '-'}</div>
                            <div class="text-xs text-primary font-medium">${item.kode_teknisi || '-'}</div>
                        </div>
                        <div class="flex gap-1">
                            <button class="p-2 text-slate-400 hover:text-primary transition-colors btn-edit-teknisi-mobile"
                                data-id="${item.id_teknisi}"
                                data-kode="${item.kode_teknisi || ''}"
                                data-nama="${item.nama_teknisi || ''}"
                                data-id-cabang="${item.id_cabang || ''}"
                                data-jabatan="${item.jabatan || ''}"
                                data-spesialis="${item.spesialis || ''}"
                                data-jk="${item.jenis_kelamin || ''}"
                                data-wa="${item.wa || ''}"
                                data-aktif="${item.aktif || ''}"
                                title="Edit">
                                <span class="material-symbols-outlined text-xl">edit_square</span>
                            </button>
                            <button class="p-2 text-slate-400 hover:text-red-500 transition-colors btn-delete-teknisi-mobile"
                                data-id="${item.id_teknisi}"
                                data-nama="${item.nama_teknisi || ''}"
                                title="Hapus">
                                <span class="material-symbols-outlined text-xl">delete</span>
                            </button>
                        </div>
                    </div>
                    <div class="text-sm text-slate-600 dark:text-slate-400 space-y-2">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm">badge</span>
                            <span>${item.jabatan || '-'}</span>
                        </div>
                        ${item.spesialis ? `
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm">engineering</span>
                            <span>${item.spesialis}</span>
                        </div>` : ''}
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm">phone</span>
                            <span>${item.wa || '-'}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm">${getGenderIcon(item.jenis_kelamin)}</span>
                            <span>${getGenderText(item.jenis_kelamin)}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="px-3 py-1 text-xs font-bold rounded-full border ${getStatusClass(item.aktif)}">
                                ${getStatusText(item.aktif)}
                            </span>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        function renderPagination() {
            const paginationContainer = document.getElementById('paginationContainer');
            const showingText = document.getElementById('showingText');
            const paginationButtons = document.getElementById('paginationButtons');
            const noData = document.getElementById('noData');

            if (filteredData.length === 0) {
                paginationContainer.classList.add('hidden');
                noData.classList.remove('hidden');
                document.getElementById('tableBody').innerHTML = '';
                document.getElementById('mobileView').innerHTML = '';
                return;
            }

            noData.classList.add('hidden');
            paginationContainer.classList.remove('hidden');

            const start = (currentPage - 1) * itemsPerPage + 1;
            const end = Math.min(currentPage * itemsPerPage, filteredData.length);
            showingText.textContent = `Showing ${start}-${end} of ${filteredData.length} results`;

            let buttons = '';

            // Previous button
            buttons += `
                <button onclick="changePage(${currentPage - 1})"
                    class="p-2 rounded border border-slate-200 dark:border-slate-700 bg-white dark:bg-background-dark text-slate-400 disabled:opacity-50"
                    ${currentPage === 1 ? 'disabled' : ''}>
                    <span class="material-symbols-outlined text-sm">chevron_left</span>
                </button>
            `;

            // Page numbers
            const maxVisible = 5;
            let startPage = Math.max(1, currentPage - Math.floor(maxVisible / 2));
            let endPage = Math.min(totalPages, startPage + maxVisible - 1);

            if (endPage - startPage + 1 < maxVisible) {
                startPage = Math.max(1, endPage - maxVisible + 1);
            }

            for (let i = startPage; i <= endPage; i++) {
                if (i === currentPage) {
                    buttons += `
                        <button class="px-3 py-1 rounded border border-primary bg-primary text-white text-xs font-bold">${i}</button>
                    `;
                } else {
                    buttons += `
                        <button onclick="changePage(${i})"
                            class="px-3 py-1 rounded border border-slate-200 dark:border-slate-700 bg-white dark:bg-background-dark text-slate-600 dark:text-slate-300 text-xs font-bold">${i}</button>
                    `;
                }
            }

            // Next button
            buttons += `
                <button onclick="changePage(${currentPage + 1})"
                    class="p-2 rounded border border-slate-200 dark:border-slate-700 bg-white dark:bg-background-dark text-slate-400 disabled:opacity-50"
                    ${currentPage === totalPages ? 'disabled' : ''}>
                    <span class="material-symbols-outlined text-sm">chevron_right</span>
                </button>
            `;

            paginationButtons.innerHTML = buttons;
        }

        function changePage(page) {
            if (page < 1 || page > totalPages) return;
            currentPage = page;
            renderTable();
            renderMobileView();
            renderPagination();
        }

        // Handle delete
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.btn-delete-teknisi, .btn-delete-teknisi-mobile');
            if (btn) {
                const id = btn.dataset.id;
                const nama = btn.dataset.nama;
                
                if (confirm(`Apakah Anda yakin ingin menghapus teknisi "${nama}"?`)) {
                    fetch('../../api/teknisi/delete.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ id_teknisi: id })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showToast('Data teknisi berhasil dihapus!', 'success');
                            setTimeout(() => {
                                location.reload();
                            }, 1500);
                        } else {
                            showToast(data.message || 'Gagal menghapus data', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showToast('Terjadi kesalahan saat menghapus data', 'error');
                    });
                }
            }
        });

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            renderTable();
            renderMobileView();
            renderPagination();
        });
    </script>
</body>

</html>
