<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['id_users'])) {
    header('Location: ../login.php');
    exit;
}

// Fetch data from API
$apiUrl = 'http://localhost/botanic/api/fo/list.php';
$apiResponse = file_get_contents($apiUrl);
$apiData = json_decode($apiResponse, true);

$foList = [];
$totalCount = 0;
$message = '';

if ($apiData && $apiData['success']) {
    $foList = $apiData['data'];
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
    <title>Admin Panel - Manajemen Front Office</title>
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
                    <h2 class="text-lg font-semibold text-slate-800 dark:text-white">Manajemen Front Office</h2>
                </div>
                <div class="flex items-center gap-4">
                    <div class="relative hidden sm:block">
                        <span
                            class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xl leading-none">search</span>
                        <input id="searchInput"
                            class="pl-10 pr-4 py-2 bg-slate-100 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary text-sm w-64"
                            placeholder="Cari front office..." type="text" onkeyup="filterData()" />
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
                            Front Office</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Kelola kontak WhatsApp front office di setiap cabang hotel.</p>
                    </div>
                    <button id="btnTambahFO"
                        class="flex items-center gap-2 px-5 py-2.5 bg-primary text-white font-bold rounded-lg hover:bg-primary/90 transition-all shadow-sm w-full sm:w-auto justify-center">
                        <span class="material-symbols-outlined">add</span>
                        <span>Tambah Front Office</span>
                    </button>
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
                                        No</th>
                                    <th
                                        class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                        Cabang</th>
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
                        <span class="material-symbols-outlined text-6xl text-slate-300 dark:text-slate-600 mb-4">phone_disabled</span>
                        <p class="text-slate-500 dark:text-slate-400">Tidak ada data front office</p>
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

    <!-- Add FO Modal -->
    <div class="fixed inset-0 z-50 flex items-center justify-center hidden" id="add-fo-modal">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        <!-- Modal Content -->
        <div
            class="relative bg-white dark:bg-background-dark w-full max-w-lg mx-4 rounded-xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
            <!-- Header (Fixed) -->
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between flex-shrink-0">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Tambah Front Office Baru</h3>
                <button class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 btn-close-modal">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <!-- Scrollable Content -->
            <div class="overflow-y-auto px-6 py-4 space-y-4 scrollbar-thin scrollbar-thumb-slate-300 dark:scrollbar-thumb-slate-600">
                <form id="formTambahFO" class="space-y-4" enctype="multipart/form-data">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Cabang
                                <span class="text-red-500">*</span></label>
                            <select id="id_cabang" name="id_cabang"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                                required>
                                <option value="">-- Pilih Cabang --</option>
                                <?php foreach ($cabangList as $cabang): ?>
                                <option value="<?php echo $cabang['id_cabang']; ?>">
                                    <?php echo htmlspecialchars($cabang['nama_cabang']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">WhatsApp
                                <span class="text-red-500">*</span></label>
                            <input id="wa" name="wa"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                                placeholder="Contoh: 081234567890" type="text" required />
                            <p class="text-xs text-slate-400 mt-1">Format: nomor WhatsApp tanpa tanda + atau spasi</p>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Status
                                Aktif</label>
                            <select id="aktif" name="aktif"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm">
                                <option value="1">Aktif</option>
                                <option value="0">Tidak Aktif</option>
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

    <!-- Edit FO Modal -->
    <div class="fixed inset-0 z-50 flex items-center justify-center hidden" id="edit-fo-modal">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        <!-- Modal Content -->
        <div
            class="relative bg-white dark:bg-background-dark w-full max-w-lg mx-4 rounded-xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
            <!-- Header (Fixed) -->
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between flex-shrink-0">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Edit Front Office</h3>
                <button class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 btn-close-edit-modal">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <!-- Scrollable Content -->
            <div class="overflow-y-auto px-6 py-4 space-y-4 scrollbar-thin scrollbar-thumb-slate-300 dark:scrollbar-thumb-slate-600">
                <form id="formEditFO" class="space-y-4" enctype="multipart/form-data">
                    <input type="hidden" id="edit_id_fo" name="id_fo" />
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Cabang
                                <span class="text-red-500">*</span></label>
                            <select id="edit_id_cabang" name="id_cabang"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                                required>
                                <option value="">-- Pilih Cabang --</option>
                                <?php foreach ($cabangList as $cabang): ?>
                                <option value="<?php echo $cabang['id_cabang']; ?>">
                                    <?php echo htmlspecialchars($cabang['nama_cabang']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">WhatsApp
                                <span class="text-red-500">*</span></label>
                            <input id="edit_wa" name="wa"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                                placeholder="Contoh: 081234567890" type="text" required />
                            <p class="text-xs text-slate-400 mt-1">Format: nomor WhatsApp tanpa tanda + atau spasi</p>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Status
                                Aktif</label>
                            <select id="edit_aktif" name="aktif"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm">
                                <option value="1">Aktif</option>
                                <option value="0">Tidak Aktif</option>
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

    <!-- Delete Confirmation Modal -->
    <div class="fixed inset-0 z-50 flex items-center justify-center hidden" id="delete-fo-modal">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        <!-- Modal Content -->
        <div
            class="relative bg-white dark:bg-background-dark w-full max-w-md mx-4 rounded-xl shadow-2xl overflow-hidden flex flex-col">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center gap-3">
                <span class="material-symbols-outlined text-red-500 text-3xl">warning</span>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Konfirmasi Hapus</h3>
            </div>
            <!-- Content -->
            <div class="px-6 py-4">
                <p class="text-slate-600 dark:text-slate-300">Apakah Anda yakin ingin menghapus data Front Office ini?</p>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-2">Tindakan ini tidak dapat dibatalkan.</p>
            </div>
            <!-- Footer -->
            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-3">
                <button
                    class="px-4 py-2 text-sm font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors btn-close-delete-modal"
                    type="button">
                    Batal
                </button>
                <button id="btnHapus"
                    class="px-6 py-2 text-sm font-bold text-white bg-red-500 hover:bg-red-600 rounded-lg shadow-sm transition-all flex items-center gap-2"
                    type="button">
                    <span class="material-symbols-outlined text-sm">delete</span>
                    <span>Hapus</span>
                </button>
            </div>
        </div>
    </div>

    <script>
        // Data storage
        let allData = <?php echo json_encode($foList); ?>;
        let filteredData = [...allData];
        let deleteId = null;

        // Pagination
        const itemsPerPage = 10;
        let currentPage = 1;

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            renderTable();
            setupEventListeners();
        });

        function setupEventListeners() {
            // Add modal
            document.getElementById('btnTambahFO').addEventListener('click', function() {
                document.getElementById('add-fo-modal').classList.remove('hidden');
            });

            document.querySelectorAll('.btn-close-modal').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.getElementById('add-fo-modal').classList.add('hidden');
                    document.getElementById('formTambahFO').reset();
                });
            });

            document.getElementById('btnSimpan').addEventListener('click', saveData);

            // Edit modal
            document.querySelectorAll('.btn-close-edit-modal').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.getElementById('edit-fo-modal').classList.add('hidden');
                    document.getElementById('formEditFO').reset();
                });
            });

            document.getElementById('btnUpdate').addEventListener('click', updateData);

            // Delete modal
            document.querySelectorAll('.btn-close-delete-modal').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.getElementById('delete-fo-modal').classList.add('hidden');
                });
            });

            document.getElementById('btnHapus').addEventListener('click', deleteData);

            // Close modals on backdrop click
            document.querySelectorAll('#add-fo-modal > div, #edit-fo-modal > div, #delete-fo-modal > div').forEach(backdrop => {
                backdrop.addEventListener('click', function(e) {
                    if (e.target === this) {
                        document.getElementById('add-fo-modal').classList.add('hidden');
                        document.getElementById('edit-fo-modal').classList.add('hidden');
                        document.getElementById('delete-fo-modal').classList.add('hidden');
                    }
                });
            });
        }

        function renderTable() {
            const tableBody = document.getElementById('tableBody');
            const mobileView = document.getElementById('mobileView');
            const noData = document.getElementById('noData');
            const paginationContainer = document.getElementById('paginationContainer');

            if (filteredData.length === 0) {
                tableBody.innerHTML = '';
                mobileView.innerHTML = '';
                noData.classList.remove('hidden');
                paginationContainer.classList.add('hidden');
                return;
            }

            noData.classList.add('hidden');
            paginationContainer.classList.remove('hidden');

            // Pagination
            const totalPages = Math.ceil(filteredData.length / itemsPerPage);
            if (currentPage > totalPages) currentPage = totalPages;
            if (currentPage < 1) currentPage = 1;

            const startIndex = (currentPage - 1) * itemsPerPage;
            const endIndex = startIndex + itemsPerPage;
            const paginatedData = filteredData.slice(startIndex, endIndex);

            // Render Desktop Table
            tableBody.innerHTML = paginatedData.map((item, index) => `
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                    <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300">${startIndex + index + 1}</td>
                    <td class="px-6 py-4 text-sm font-medium text-slate-900 dark:text-white">${escapeHtml(item.nama_cabang)}</td>
                    <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300">${escapeHtml(item.wa)}</td>
                    <td class="px-6 py-4 text-sm text-center">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold ${item.aktif == 1 ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'}">
                            ${item.aktif == 1 ? 'Aktif' : 'Tidak Aktif'}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <button onclick="editData(${item.id_fo})" class="p-2 text-slate-500 hover:text-primary dark:text-slate-400 dark:hover:text-primary transition-colors" title="Edit">
                                <span class="material-symbols-outlined text-lg">edit</span>
                            </button>
                            <button onclick="confirmDelete(${item.id_fo})" class="p-2 text-slate-500 hover:text-red-500 dark:text-slate-400 dark:hover:text-red-400 transition-colors" title="Hapus">
                                <span class="material-symbols-outlined text-lg">delete</span>
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('');

            // Render Mobile Cards
            mobileView.innerHTML = paginatedData.map((item, index) => `
                <div class="p-4 space-y-3 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <h4 class="text-base font-bold text-slate-900 dark:text-white truncate">${escapeHtml(item.nama_cabang)}</h4>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">${escapeHtml(item.wa)}</p>
                        </div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold flex-shrink-0 ${item.aktif == 1 ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'}">
                            ${item.aktif == 1 ? 'Aktif' : 'Tidak Aktif'}
                        </span>
                    </div>
                    <div class="flex items-center gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                        <button onclick="editData(${item.id_fo})" class="flex-1 flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium text-primary bg-primary/10 rounded-lg hover:bg-primary/20 transition-colors">
                            <span class="material-symbols-outlined text-lg">edit</span>
                            Edit
                        </button>
                        <button onclick="confirmDelete(${item.id_fo})" class="flex-1 flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium text-red-500 bg-red-50 dark:bg-red-900/20 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/30 transition-colors">
                            <span class="material-symbols-outlined text-lg">delete</span>
                            Hapus
                        </button>
                    </div>
                </div>
            `).join('');

            // Update pagination info
            const showingEnd = Math.min(endIndex, filteredData.length);
            document.getElementById('showingText').textContent = `Showing ${startIndex + 1}-${showingEnd} of ${filteredData.length} results`;

            // Render pagination buttons
            const paginationButtons = document.getElementById('paginationButtons');
            paginationButtons.innerHTML = '';

            if (currentPage > 1) {
                paginationButtons.innerHTML += `
                    <button onclick="changePage(${currentPage - 1})" class="px-3 py-1.5 text-sm font-medium text-slate-600 dark:text-slate-300 bg-white dark:bg-background-dark border border-slate-200 dark:border-slate-700 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                        Previous
                    </button>
                `;
            }

            for (let i = 1; i <= totalPages; i++) {
                if (i === currentPage) {
                    paginationButtons.innerHTML += `
                        <button class="px-3 py-1.5 text-sm font-bold text-white bg-primary rounded-lg">
                            ${i}
                        </button>
                    `;
                } else {
                    paginationButtons.innerHTML += `
                        <button onclick="changePage(${i})" class="px-3 py-1.5 text-sm font-medium text-slate-600 dark:text-slate-300 bg-white dark:bg-background-dark border border-slate-200 dark:border-slate-700 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                            ${i}
                        </button>
                    `;
                }
            }

            if (currentPage < totalPages) {
                paginationButtons.innerHTML += `
                    <button onclick="changePage(${currentPage + 1})" class="px-3 py-1.5 text-sm font-medium text-slate-600 dark:text-slate-300 bg-white dark:bg-background-dark border border-slate-200 dark:border-slate-700 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                        Next
                    </button>
                `;
            }
        }

        function changePage(page) {
            currentPage = page;
            renderTable();
        }

        function filterData() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            filteredData = allData.filter(item => {
                return item.nama_cabang.toLowerCase().includes(searchTerm) ||
                       item.wa.toLowerCase().includes(searchTerm);
            });
            currentPage = 1;
            renderTable();
        }

        function toggleSearch() {
            const searchInput = document.getElementById('searchInput');
            searchInput.classList.toggle('hidden');
            if (!searchInput.classList.contains('hidden')) {
                searchInput.focus();
            }
        }

        function saveData() {
            const formData = new FormData(document.getElementById('formTambahFO'));
            const data = Object.fromEntries(formData.entries());

            fetch('http://localhost/botanic/api/fo/new.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    showToast('success', 'Berhasil', result.message);
                    document.getElementById('add-fo-modal').classList.add('hidden');
                    document.getElementById('formTambahFO').reset();
                    loadData();
                } else {
                    showToast('error', 'Gagal', result.message);
                }
            })
            .catch(error => {
                showToast('error', 'Error', 'Terjadi kesalahan: ' + error.message);
            });
        }

        function editData(id) {
            const item = allData.find(d => d.id_fo === id);
            if (!item) return;

            document.getElementById('edit_id_fo').value = item.id_fo;
            document.getElementById('edit_id_cabang').value = item.id_cabang;
            document.getElementById('edit_wa').value = item.wa;
            document.getElementById('edit_aktif').value = item.aktif;

            document.getElementById('edit-fo-modal').classList.remove('hidden');
        }

        function updateData() {
            const formData = new FormData(document.getElementById('formEditFO'));

            fetch('http://localhost/botanic/api/fo/update.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    showToast('success', 'Berhasil', result.message);
                    document.getElementById('edit-fo-modal').classList.add('hidden');
                    document.getElementById('formEditFO').reset();
                    loadData();
                } else {
                    showToast('error', 'Gagal', result.message);
                }
            })
            .catch(error => {
                showToast('error', 'Error', 'Terjadi kesalahan: ' + error.message);
            });
        }

        function confirmDelete(id) {
            deleteId = id;
            document.getElementById('delete-fo-modal').classList.remove('hidden');
        }

        function deleteData() {
            const formData = new FormData();
            formData.append('id_fo', deleteId);

            fetch('http://localhost/botanic/api/fo/delete.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    showToast('success', 'Berhasil', result.message);
                    document.getElementById('delete-fo-modal').classList.add('hidden');
                    loadData();
                } else {
                    showToast('error', 'Gagal', result.message);
                }
            })
            .catch(error => {
                showToast('error', 'Error', 'Terjadi kesalahan: ' + error.message);
            });
        }

        function loadData() {
            fetch('http://localhost/botanic/api/fo/list.php')
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    allData = result.data;
                    filteredData = [...allData];
                    currentPage = 1;
                    renderTable();
                } else {
                    showToast('error', 'Gagal', result.message);
                }
            })
            .catch(error => {
                showToast('error', 'Error', 'Terjadi kesalahan: ' + error.message);
            });
        }

        function showToast(type, title, message) {
            const toastContainer = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-lg shadow-lg transform transition-all duration-300 translate-x-full opacity-0 ${
                type === 'success' ? 'bg-green-500' : 'bg-red-500'
            } text-white`;
            toast.innerHTML = `
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined">${type === 'success' ? 'check_circle' : 'error'}</span>
                    <div>
                        <p class="font-bold">${title}</p>
                        <p class="text-sm">${message}</p>
                    </div>
                </div>
            `;
            toastContainer.appendChild(toast);

            setTimeout(() => {
                toast.classList.remove('translate-x-full', 'opacity-0');
            }, 100);

            setTimeout(() => {
                toast.classList.add('translate-x-full', 'opacity-0');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text ? String(text).replace(/[&<>"']/g, m => map[m]) : '';
        }
    </script>
</body>

</html>
