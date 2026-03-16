<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['id_users'])) {
    header('Location: ../login.php');
    exit;
}

// Get logged-in user data
$id_users_logged = $_SESSION['id_users'];
$username_logged = $_SESSION['username'] ?? '';

// Fetch data from API
$apiUrl = 'http://localhost/botanic/api/halaman/list.php';
$apiResponse = file_get_contents($apiUrl);
$apiData = json_decode($apiResponse, true);

$halamanList = [];
$totalCount = 0;
$message = '';

if ($apiData && $apiData['success']) {
    $halamanList = $apiData['data'];
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
    <title>Admin Panel - Manajemen Halaman</title>
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
                    <h2 class="text-lg font-semibold text-slate-800 dark:text-white">Manajemen Halaman</h2>
                </div>
                <div class="flex items-center gap-4">
                    <div class="relative hidden sm:block">
                        <span
                            class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xl leading-none">search</span>
                        <input id="searchInput"
                            class="pl-10 pr-4 py-2 bg-slate-100 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary text-sm w-64"
                            placeholder="Cari halaman..." type="text" onkeyup="filterData()" />
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
                            Halaman</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Kelola informasi halaman seluruh
                            kantor cabang secara terpusat.</p>
                    </div>
                    <button id="btnTambahHalaman"
                        class="flex items-center gap-2 px-5 py-2.5 bg-primary text-white font-bold rounded-lg hover:bg-primary/90 transition-all shadow-sm w-full sm:w-auto justify-center">
                        <span class="material-symbols-outlined">add</span>
                        <span>Tambah Halaman</span>
                    </button>
                </div>

                <!-- Filter Cabang -->
                <div class="flex items-center gap-4">
                    <div class="relative flex-1 max-w-xs">
                        <span
                            class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">business</span>
                        <select id="filterCabang" onchange="filterByCabang()"
                            class="w-full pl-10 pr-10 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none text-sm appearance-none cursor-pointer">
                            <option value="">-- Pilih Cabang --</option>
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
                                        class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                        Logo</th>
                                    <th
                                        class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                        Halaman</th>
                                    <th
                                        class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                        Cabang</th>
                                    <th
                                        class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                        Username</th>
                                    <th
                                        class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-center">
                                        Link</th>
                                    <th
                                        class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-center">
                                        Aktif</th>
                                    <th
                                        class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                        Created</th>
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
                        <span class="material-symbols-outlined text-6xl text-slate-300 dark:text-slate-600 mb-4">web</span>
                        <p class="text-slate-500 dark:text-slate-400">Tidak ada data halaman</p>
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

    <!-- Add Halaman Modal -->
    <div class="fixed inset-0 z-50 flex items-center justify-center hidden" id="add-halaman-modal">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        <!-- Modal Content -->
        <div
            class="relative bg-white dark:bg-background-dark w-full max-w-lg mx-4 rounded-xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
            <!-- Header (Fixed) -->
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between flex-shrink-0">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Tambah Halaman Baru</h3>
                <button class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 btn-close-modal">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <!-- Scrollable Content -->
            <div class="overflow-y-auto px-6 py-4 space-y-4 scrollbar-thin scrollbar-thumb-slate-300 dark:scrollbar-thumb-slate-600">
                <form id="formTambahHalaman" class="space-y-4" enctype="multipart/form-data">
                    <input type="hidden" id="id_users" name="id_users" value="<?php echo htmlspecialchars($id_users_logged); ?>" />
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Nama
                                Halaman <span class="text-red-500">*</span></label>
                            <input id="nama_halaman" name="nama_halaman"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                                placeholder="Contoh: Website Utama" type="text" required />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Cabang <span class="text-red-500">*</span></label>
                            <select id="id_cabang" name="id_cabang"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                                required>
                                <option value="">Pilih Cabang</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Username Halaman <span class="text-red-500">*</span></label>
                            <input id="username_halaman" name="username_halaman"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm font-mono text-primary dark:text-primary font-semibold"
                                placeholder="username_halaman" type="text" required />
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Link <span class="text-red-500">*</span></label>
                            <input id="link" name="link"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                                placeholder="https://example.com" type="url" required />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Status Aktif</label>
                            <select id="aktif" name="aktif"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm">
                                <option value="1">Aktif</option>
                                <option value="0">Nonaktif</option>
                            </select>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Logo
                                Halaman</label>
                            <div
                                class="mt-1 flex items-center gap-4 px-4 py-4 border-2 border-slate-300 dark:border-slate-700 border-dashed rounded-lg">
                                <!-- Preview Image -->
                                <div id="logoPreview" class="hidden w-20 h-20 rounded-lg overflow-hidden border border-slate-200 dark:border-slate-600 flex-shrink-0 bg-slate-100 dark:bg-slate-800">
                                    <img id="previewImg" src="" alt="Preview" class="w-full h-full object-cover" />
                                </div>
                                <div class="space-y-1 text-center flex-1">
                                    <span class="material-symbols-outlined text-slate-400 text-3xl">image</span>
                                    <div class="flex text-sm text-slate-600 dark:text-slate-400 justify-center">
                                        <label
                                            class="relative cursor-pointer bg-white dark:bg-background-dark rounded-md font-medium text-primary hover:text-primary/80 focus-within:outline-none"
                                            for="logo">
                                            <span>Upload file</span>
                                            <input class="sr-only" id="logo" name="logo" type="file" accept="image/*" />
                                        </label>
                                    </div>
                                    <p class="text-xs text-slate-500">PNG, JPG max 1MB</p>
                                    <p id="fileName" class="text-xs text-slate-400 truncate max-w-[200px]"></p>
                                </div>
                            </div>
                            <p id="logoError" class="text-xs text-red-500 mt-1 hidden"></p>
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

    <!-- Edit Halaman Modal -->
    <div class="fixed inset-0 z-50 flex items-center justify-center hidden" id="edit-halaman-modal">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        <!-- Modal Content -->
        <div
            class="relative bg-white dark:bg-background-dark w-full max-w-lg mx-4 rounded-xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
            <!-- Header (Fixed) -->
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between flex-shrink-0">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Edit Halaman</h3>
                <button class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 btn-close-edit-modal">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <!-- Scrollable Content -->
            <div class="overflow-y-auto px-6 py-4 space-y-4 scrollbar-thin scrollbar-thumb-slate-300 dark:scrollbar-thumb-slate-600">
                <form id="formEditHalaman" class="space-y-4" enctype="multipart/form-data">
                    <input type="hidden" id="edit_id_halaman" name="id_halaman" />
                    <input type="hidden" name="created_date" />
                    <input type="hidden" id="edit_id_users" name="id_users" value="<?php echo htmlspecialchars($id_users_logged); ?>" />
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Nama
                                Halaman <span class="text-red-500">*</span></label>
                            <input id="edit_nama_halaman" name="nama_halaman"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                                placeholder="Contoh: Website Utama" type="text" required />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Cabang <span class="text-red-500">*</span></label>
                            <select id="edit_id_cabang" name="id_cabang"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                                required>
                                <option value="">Pilih Cabang</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Username Halaman <span class="text-red-500">*</span></label>
                            <input id="edit_username_halaman" name="username_halaman"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm font-mono text-primary dark:text-primary font-semibold"
                                placeholder="username_halaman" type="text" required />
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Link <span class="text-red-500">*</span></label>
                            <input id="edit_link" name="link"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                                placeholder="https://example.com" type="url" required />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Status Aktif</label>
                            <select id="edit_aktif" name="aktif"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm">
                                <option value="1">Aktif</option>
                                <option value="0">Nonaktif</option>
                            </select>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Logo
                                Halaman</label>
                            <div
                                class="mt-1 flex items-center gap-4 px-4 py-4 border-2 border-slate-300 dark:border-slate-700 border-dashed rounded-lg">
                                <!-- Preview Image -->
                                <div id="edit_logo_preview_container" class="hidden w-20 h-20 rounded-lg overflow-hidden border border-slate-200 dark:border-slate-600 flex-shrink-0 bg-slate-100 dark:bg-slate-800">
                                    <img id="edit_preview_img" src="" alt="Preview" class="w-full h-full object-cover" />
                                </div>
                                <div class="space-y-1 text-center flex-1">
                                    <span class="material-symbols-outlined text-slate-400 text-3xl">image</span>
                                    <div class="flex text-sm text-slate-600 dark:text-slate-400 justify-center">
                                        <label
                                            class="relative cursor-pointer bg-white dark:bg-background-dark rounded-md font-medium text-primary hover:text-primary/80 focus-within:outline-none"
                                            for="edit_logo">
                                            <span>Upload file</span>
                                            <input class="sr-only" id="edit_logo" name="logo" type="file" accept="image/*" />
                                        </label>
                                    </div>
                                    <p class="text-xs text-slate-500">PNG, JPG max 1MB</p>
                                    <p id="edit_file_name" class="text-xs text-slate-400 truncate max-w-[200px]"></p>
                                </div>
                            </div>
                            <p id="edit_logo_error" class="text-xs text-red-500 mt-1 hidden"></p>
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

        // Confirmation Dialog
        function confirmSave() {
            return new Promise((resolve) => {
                const confirmed = confirm('Apakah Anda yakin ingin menyimpan data halaman ini?');
                resolve(confirmed);
            });
        }

        // Modal functions
        const modal = document.getElementById('add-halaman-modal');
        const btnTambah = document.getElementById('btnTambahHalaman');
        const btnClose = document.querySelectorAll('.btn-close-modal');
        const btnSimpan = document.getElementById('btnSimpan');
        const formTambah = document.getElementById('formTambahHalaman');

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
                const requiredFields = ['nama_halaman', 'id_cabang', 'username_halaman', 'link'];
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
                const confirmed = await confirmSave();
                if (!confirmed) return;

                // Show loading state
                btnSimpan.disabled = true;
                btnSimpan.innerHTML = '<span class="material-symbols-outlined spinner">sync</span><span>Menyimpan...</span>';

                // Prepare FormData
                const formData = new FormData(formTambah);

                // Send to API
                fetch('../../api/halaman/new.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Data halaman berhasil disimpan!', 'success');
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
        document.querySelectorAll('input, select, textarea').forEach(input => {
            input.addEventListener('input', function() {
                this.classList.remove('border-red-500');
            });
        });

        // Image Preview and Validation
        const logoInput = document.getElementById('logo');
        const logoPreview = document.getElementById('logoPreview');
        const previewImg = document.getElementById('previewImg');
        const fileName = document.getElementById('fileName');
        const logoError = document.getElementById('logoError');

        if (logoInput) {
            logoInput.addEventListener('change', function(e) {
                const file = e.target.files[0];

                // Reset error
                logoError.classList.add('hidden');
                logoInput.classList.remove('border-red-500');

                if (!file) {
                    logoPreview.classList.add('hidden');
                    fileName.textContent = '';
                    return;
                }

                // Validate file type
                const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                if (!validTypes.includes(file.type)) {
                    logoError.textContent = 'Tipe file tidak valid. Gunakan JPG, PNG, GIF, atau WEBP.';
                    logoError.classList.remove('hidden');
                    logoInput.classList.add('border-red-500');
                    logoInput.value = ''; // Clear input
                    logoPreview.classList.add('hidden');
                    fileName.textContent = '';
                    return;
                }

                // Validate file size (1MB)
                if (file.size > maxSize) {
                    logoError.textContent = 'Ukuran file terlalu besar. Maksimal 1MB.';
                    logoError.classList.remove('hidden');
                    logoInput.classList.add('border-red-500');
                    logoInput.value = ''; // Clear input
                    logoPreview.classList.add('hidden');
                    fileName.textContent = '';
                    return;
                }

                // Show preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    logoPreview.classList.remove('hidden');
                    fileName.textContent = file.name;
                };
                reader.readAsDataURL(file);
            });
        }

        // Edit Modal Functions
        const editModal = document.getElementById('edit-halaman-modal');
        const btnCloseEdit = document.querySelectorAll('.btn-close-edit-modal');
        const btnUpdate = document.getElementById('btnUpdate');
        const formEdit = document.getElementById('formEditHalaman');
        const editLogoInput = document.getElementById('edit_logo');
        const editLogoPreview = document.getElementById('edit_logo_preview_container');
        const editPreviewImg = document.getElementById('edit_preview_img');
        const editFileName = document.getElementById('edit_file_name');
        const editLogoError = document.getElementById('edit_logo_error');

        function openEditModal(id, namaHalaman, idCabang, usernameHalaman, link, aktif, logo, createdDate) {
            document.getElementById('edit_id_halaman').value = id;
            document.getElementById('edit_nama_halaman').value = namaHalaman;
            document.getElementById('edit_id_cabang').value = idCabang;
            document.getElementById('edit_username_halaman').value = usernameHalaman;
            document.getElementById('edit_link').value = link;
            document.getElementById('edit_aktif').value = aktif;
            document.querySelector('#formEditHalaman input[name="created_date"]').value = createdDate;

            // Show existing logo preview
            if (logo && logo !== '') {
                editPreviewImg.src = getImageUrl(logo);
                editLogoPreview.classList.remove('hidden');
                editFileName.textContent = logo;
            } else {
                editLogoPreview.classList.add('hidden');
                editFileName.textContent = '';
            }

            editModal.classList.remove('hidden');
        }

        function closeEditModal() {
            editModal.classList.add('hidden');
            formEdit.reset();
            editLogoPreview.classList.add('hidden');
            editFileName.textContent = '';
            editLogoError.classList.add('hidden');
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
            if (e.target.closest('.btn-edit-halaman')) {
                const btn = e.target.closest('.btn-edit-halaman');
                openEditModal(
                    btn.dataset.id,
                    btn.dataset.namaHalaman,
                    btn.dataset.idCabang,
                    btn.dataset.usernameHalaman,
                    btn.dataset.link,
                    btn.dataset.aktif,
                    btn.dataset.logo,
                    btn.dataset.createdDate
                );
            }
            // Handle edit button clicks (mobile)
            if (e.target.closest('.btn-edit-halaman-mobile')) {
                const btn = e.target.closest('.btn-edit-halaman-mobile');
                openEditModal(
                    btn.dataset.id,
                    btn.dataset.namaHalaman,
                    btn.dataset.idCabang,
                    btn.dataset.usernameHalaman,
                    btn.dataset.link,
                    btn.dataset.aktif,
                    btn.dataset.logo,
                    btn.dataset.createdDate
                );
            }
        });

        // Handle Update Button
        if (btnUpdate) {
            btnUpdate.addEventListener('click', async function() {
                // Validate form
                const requiredFields = ['edit_nama_halaman', 'edit_id_cabang', 'edit_username_halaman', 'edit_link'];
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
                const confirmed = confirm('Apakah Anda yakin ingin mengupdate data halaman ini?');
                if (!confirmed) return;

                // Show loading state
                btnUpdate.disabled = true;
                btnUpdate.innerHTML = '<span class="material-symbols-outlined spinner">sync</span><span>Updating...</span>';

                // Prepare FormData
                const formData = new FormData(formEdit);

                // Send to API
                fetch('../../api/halaman/update.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Data halaman berhasil diupdate!', 'success');
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
        document.querySelectorAll('#formEditHalaman input, #formEditHalaman select, #formEditHalaman textarea').forEach(input => {
            input.addEventListener('input', function() {
                this.classList.remove('border-red-500');
            });
        });

        // Image Preview and Validation for Edit Form
        if (editLogoInput) {
            editLogoInput.addEventListener('change', function(e) {
                const file = e.target.files[0];

                // Reset error
                editLogoError.classList.add('hidden');
                editLogoInput.classList.remove('border-red-500');

                if (!file) {
                    editLogoPreview.classList.add('hidden');
                    editFileName.textContent = '';
                    return;
                }

                // Validate file type
                const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                if (!validTypes.includes(file.type)) {
                    editLogoError.textContent = 'Tipe file tidak valid. Gunakan JPG, PNG, GIF, atau WEBP.';
                    editLogoError.classList.remove('hidden');
                    editLogoInput.classList.add('border-red-500');
                    editLogoInput.value = ''; // Clear input
                    editLogoPreview.classList.add('hidden');
                    editFileName.textContent = '';
                    return;
                }

                // Validate file size (1MB)
                if (file.size > maxSize) {
                    editLogoError.textContent = 'Ukuran file terlalu besar. Maksimal 1MB.';
                    editLogoError.classList.remove('hidden');
                    editLogoInput.classList.add('border-red-500');
                    editLogoInput.value = ''; // Clear input
                    editLogoPreview.classList.add('hidden');
                    editFileName.textContent = '';
                    return;
                }

                // Show preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    editPreviewImg.src = e.target.result;
                    editLogoPreview.classList.remove('hidden');
                    editFileName.textContent = file.name;
                };
                reader.readAsDataURL(file);
            });
        }

        // Data from PHP
        const allData = <?php echo json_encode($halamanList); ?>;
        const totalCount = <?php echo json_encode($totalCount); ?>;

        let currentPage = 1;
        const itemsPerPage = 10;
        let filteredData = [...allData];
        let totalPages = Math.ceil(filteredData.length / itemsPerPage);
        let selectedCabang = '';

        function filterByCabang() {
            selectedCabang = document.getElementById('filterCabang').value;
            filterData();
        }

        function toggleSearch() {
            const searchInput = document.getElementById('searchInput');
            searchInput.classList.toggle('hidden');
            if (!searchInput.classList.contains('hidden')) {
                searchInput.focus();
            }
        }

        function filterByCabang() {
            selectedCabang = document.getElementById('filterCabang').value;
            filterData();
        }

        function filterData() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            filteredData = allData.filter(item => {
                const matchesSearch = (
                    (item.nama_halaman && item.nama_halaman.toLowerCase().includes(searchTerm)) ||
                    (item.username_halaman && item.username_halaman.toLowerCase().includes(searchTerm)) ||
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

        function formatDate(dateString) {
            if (!dateString) return '-';
            const date = new Date(dateString);
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            return `${date.getDate()} ${months[date.getMonth()]} ${date.getFullYear()}`;
        }

        function getImageUrl(logo) {
            if (!logo || logo === '') {
                return 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 40 40"%3E%3Crect fill="%23e2e8f0" width="40" height="40"/%3E%3Ctext fill="%2394a3b8" font-family="sans-serif" font-size="20" text-anchor="middle" x="20" y="25"%3E🌐%3C/text%3E%3C/svg%3E';
            }
            // Check if logo already contains 'halaman/' prefix
            if (logo.startsWith('halaman/')) {
                return '../../images/' + logo;
            }
            return '../../images/halaman/' + logo;
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
                        <div class="w-10 h-10 rounded-lg overflow-hidden border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800">
                            <img class="w-full h-full object-cover" src="${getImageUrl(item.logo)}" alt="${item.nama_halaman || 'Halaman'}" />
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="font-semibold text-slate-900 dark:text-white">${item.nama_halaman || '-'}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-slate-600 dark:text-slate-400">${item.nama_cabang || '-'}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-slate-600 dark:text-slate-400">${item.username || '-'}</div>
                        <div class="text-xs text-slate-500">${item.username_halaman || '-'}</div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        ${item.link ? `
                        <a class="inline-flex items-center justify-center p-2 text-slate-400 hover:text-primary transition-colors" href="${item.link}" target="_blank" rel="noopener noreferrer" title="Buka ${item.nama_halaman || 'Halaman'}">
                            <span class="material-symbols-outlined">open_in_new</span>
                        </a>` : '<span class="text-slate-400">-</span>'}
                    </td>
                    <td class="px-6 py-4 text-center">
                        ${item.aktif == 1 ? `
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">
                            <span class="material-symbols-outlined text-xs">check_circle</span>
                            Aktif
                        </span>` : `
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-400">
                            <span class="material-symbols-outlined text-xs">cancel</span>
                            Nonaktif
                        </span>`}
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-500">${formatDate(item.created_date)}</td>
                    <td class="px-6 py-4 text-right space-x-2">
                        <button class="p-1.5 text-slate-400 hover:text-primary transition-colors btn-edit-halaman"
                            data-id="${item.id_halaman}"
                            data-nama-halaman="${item.nama_halaman || ''}"
                            data-id-cabang="${item.id_cabang || ''}"
                            data-username-halaman="${item.username_halaman || ''}"
                            data-link="${item.link || ''}"
                            data-aktif="${item.aktif || '1'}"
                            data-logo="${item.logo || ''}"
                            data-created-date="${item.created_date || ''}"
                            title="Edit">
                            <span class="material-symbols-outlined text-xl">edit_square</span>
                        </button>
                        <button class="p-1.5 text-slate-400 hover:text-red-500 transition-colors btn-delete-halaman"
                            data-id="${item.id_halaman}"
                            data-nama="${item.nama_halaman || ''}"
                            title="Hapus">
                            <span class="material-symbols-outlined text-xl">delete</span>
                        </button>
                    </td>
                </tr>
            `).join('');

            // Add delete functionality
            document.querySelectorAll('.btn-delete-halaman').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.dataset.id;
                    const nama = this.dataset.nama;
                    if (confirm(`Apakah Anda yakin ingin menghapus halaman "${nama}"?`)) {
                        deleteHalaman(id);
                    }
                });
            });
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
                    <div class="flex items-start gap-3">
                        <div class="w-16 h-16 rounded-lg overflow-hidden border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800 flex-shrink-0">
                            <img class="w-full h-full object-cover" src="${getImageUrl(item.logo)}" alt="${item.nama_halaman || 'Halaman'}" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="font-semibold text-slate-900 dark:text-white">${item.nama_halaman || '-'}</div>
                            <div class="text-xs text-slate-500">${item.nama_cabang || '-'}</div>
                        </div>
                        <div class="flex gap-1">
                            <button class="p-2 text-slate-400 hover:text-primary transition-colors btn-edit-halaman-mobile"
                                data-id="${item.id_halaman}"
                                data-nama-halaman="${item.nama_halaman || ''}"
                                data-id-cabang="${item.id_cabang || ''}"
                                data-username-halaman="${item.username_halaman || ''}"
                                data-link="${item.link || ''}"
                                data-aktif="${item.aktif || '1'}"
                                data-logo="${item.logo || ''}"
                                data-created-date="${item.created_date || ''}"
                                title="Edit">
                                <span class="material-symbols-outlined text-xl">edit_square</span>
                            </button>
                            <button class="p-2 text-slate-400 hover:text-red-500 transition-colors btn-delete-halaman-mobile"
                                data-id="${item.id_halaman}"
                                data-nama="${item.nama_halaman || ''}"
                                title="Hapus">
                                <span class="material-symbols-outlined text-xl">delete</span>
                            </button>
                        </div>
                    </div>
                    <div class="text-sm text-slate-600 dark:text-slate-400 space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm">person</span>
                            <span>${item.username || '-'}</span>
                            <span class="text-xs text-slate-500">(${item.username_halaman || '-'})</span>
                        </div>
                        ${item.link ? `
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm">link</span>
                            <a class="text-primary hover:text-primary/70 inline-flex items-center gap-1" href="${item.link}" target="_blank" rel="noopener noreferrer">
                                <span class="material-symbols-outlined text-sm">open_in_new</span>
                                <span>Buka Halaman</span>
                            </a>
                        </div>` : ''}
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm">calendar_today</span>
                            <span>${formatDate(item.created_date)}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm">${item.aktif == 1 ? 'check_circle' : 'cancel'}</span>
                            <span class="${item.aktif == 1 ? 'text-green-600 dark:text-green-400' : 'text-slate-500'}">${item.aktif == 1 ? 'Aktif' : 'Nonaktif'}</span>
                        </div>
                    </div>
                </div>
            `).join('');

            // Add delete functionality for mobile
            document.querySelectorAll('.btn-delete-halaman-mobile').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.dataset.id;
                    const nama = this.dataset.nama;
                    if (confirm(`Apakah Anda yakin ingin menghapus halaman "${nama}"?`)) {
                        deleteHalaman(id);
                    }
                });
            });
        }

        function renderPagination() {
            const paginationContainer = document.getElementById('paginationContainer');
            const showingText = document.getElementById('showingText');
            const paginationButtons = document.getElementById('paginationButtons');
            const noData = document.getElementById('noData');

            if (filteredData.length === 0) {
                paginationContainer.classList.add('hidden');
                noData.classList.remove('hidden');
                return;
            }

            noData.classList.add('hidden');
            paginationContainer.classList.remove('hidden');

            const start = (currentPage - 1) * itemsPerPage + 1;
            const end = Math.min(currentPage * itemsPerPage, filteredData.length);

            showingText.textContent = `Showing ${start}-${end} of ${filteredData.length} results`;

            // Clear previous buttons
            paginationButtons.innerHTML = '';

            // Previous button
            const prevBtn = document.createElement('button');
            prevBtn.className = `px-3 py-1.5 rounded-lg text-sm font-medium transition-colors ${currentPage === 1 ? 'text-slate-400 cursor-not-allowed' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800'}`;
            prevBtn.textContent = 'Previous';
            prevBtn.disabled = currentPage === 1;
            prevBtn.onclick = () => {
                if (currentPage > 1) {
                    currentPage--;
                    renderTable();
                    renderMobileView();
                    renderPagination();
                }
            };
            paginationButtons.appendChild(prevBtn);

            // Page numbers
            for (let i = 1; i <= totalPages; i++) {
                const pageBtn = document.createElement('button');
                pageBtn.className = `px-3 py-1.5 rounded-lg text-sm font-medium transition-colors ${i === currentPage ? 'bg-primary text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800'}`;
                pageBtn.textContent = i;
                pageBtn.onclick = () => {
                    currentPage = i;
                    renderTable();
                    renderMobileView();
                    renderPagination();
                };
                paginationButtons.appendChild(pageBtn);
            }

            // Next button
            const nextBtn = document.createElement('button');
            nextBtn.className = `px-3 py-1.5 rounded-lg text-sm font-medium transition-colors ${currentPage === totalPages ? 'text-slate-400 cursor-not-allowed' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800'}`;
            nextBtn.textContent = 'Next';
            nextBtn.disabled = currentPage === totalPages;
            nextBtn.onclick = () => {
                if (currentPage < totalPages) {
                    currentPage++;
                    renderTable();
                    renderMobileView();
                    renderPagination();
                }
            };
            paginationButtons.appendChild(nextBtn);
        }

        // Delete halaman function
        function deleteHalaman(id) {
            fetch('../../api/halaman/delete.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `id_halaman=${encodeURIComponent(id)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Data halaman berhasil dihapus!', 'success');
                    // Refresh page after short delay
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

        // Load cabang data for modal dropdowns
        function loadCabangData() {
            fetch('http://localhost/botanic/api/cabang/list.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const cabangSelect = document.getElementById('id_cabang');
                        const editCabangSelect = document.getElementById('edit_id_cabang');
                        
                        data.data.forEach(cabang => {
                            const option = document.createElement('option');
                            option.value = cabang.id_cabang;
                            option.textContent = cabang.nama_cabang;
                            cabangSelect.appendChild(option.cloneNode(true));
                            editCabangSelect.appendChild(option);
                        });
                    }
                })
                .catch(error => console.error('Error loading cabang:', error));
        }

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            loadCabangData();
            renderTable();
            renderMobileView();
            renderPagination();
        });
    </script>
</body>

</html>
