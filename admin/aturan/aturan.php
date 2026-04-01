<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['id_users'])) {
    header('Location: ../login.php');
    exit;
}

// Fetch data from API
$apiUrl = 'http://localhost/botanic/api/aturan/list.php';
$apiResponse = file_get_contents($apiUrl);
$apiData = json_decode($apiResponse, true);

$aturanList = [];
$categories = [];
$countByCategory = [];
$message = '';

if ($apiData && $apiData['success']) {
    $aturanList = $apiData['data'];
    $categories = $apiData['categories'];
    $countByCategory = $apiData['count_by_category'];
    $message = $apiData['message'];
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Admin Panel - Manajemen Aturan</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&amp;display=swap"
        rel="stylesheet" />
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap"
        rel="stylesheet" />
    <link rel="stylesheet" href="../css/style.css" />
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
                    <h2 class="text-lg font-semibold text-slate-800 dark:text-white">Manajemen Aturan</h2>
                </div>
                <div class="flex items-center gap-4">
                    <div class="relative hidden sm:block">
                        <span
                            class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xl leading-none">search</span>
                        <input id="searchInput"
                            class="pl-10 pr-4 py-2 bg-slate-100 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary text-sm w-64"
                            placeholder="Cari aturan..." type="text" onkeyup="filterData()" />
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
                            Aturan</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Kelola aturan dan ketentuan untuk
                            tamu secara terpusat.</p>
                    </div>
                    <button id="btnTambahAturan"
                        class="flex items-center gap-2 px-5 py-2.5 bg-primary text-white font-bold rounded-lg hover:bg-primary/90 transition-all shadow-sm w-full sm:w-auto justify-center">
                        <span class="material-symbols-outlined">add</span>
                        <span>Tambah Aturan</span>
                    </button>
                </div>

                <!-- Category Tabs -->
                <div class="bg-white dark:bg-background-dark rounded-xl border border-slate-200 dark:border-slate-800 p-4">
                    <div class="flex flex-wrap gap-2" id="categoryTabs">
                        <button class="category-tab active" data-category="all">
                            Semua (<span id="totalCount">0</span>)
                        </button>
                        <button class="category-tab" data-category="0">
                            Check-in/out (<span id="countCat0">0</span>)
                        </button>
                        <button class="category-tab" data-category="1">
                            Denda (<span id="countCat1">0</span>)
                        </button>
                        <button class="category-tab" data-category="2">
                            Larangan (<span id="countCat2">0</span>)
                        </button>
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
                                        Nama Aturan</th>
                                    <th
                                        class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                        Deskripsi</th>
                                    <th
                                        class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-center">
                                        Denda</th>
                                    <th
                                        class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-center">
                                        Status</th>
                                    <th
                                        class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-center">
                                        Dibuat</th>
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
                        <span class="material-symbols-outlined text-6xl text-slate-300 dark:text-slate-600 mb-4">gavel</span>
                        <p class="text-slate-500 dark:text-slate-400">Tidak ada data aturan</p>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Add Aturan Modal -->
    <div class="fixed inset-0 z-50 flex items-center justify-center hidden" id="add-aturan-modal">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        <!-- Modal Content -->
        <div
            class="relative bg-white dark:bg-background-dark w-full max-w-lg mx-4 rounded-xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
            <!-- Header (Fixed) -->
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between flex-shrink-0">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Tambah Aturan Baru</h3>
                <button class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 btn-close-modal">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <!-- Scrollable Content -->
            <div class="overflow-y-auto px-6 py-4 space-y-4 scrollbar-thin scrollbar-thumb-slate-300 dark:scrollbar-thumb-slate-600">
                <form id="formTambahAturan" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Kategori
                            <span class="text-red-500">*</span></label>
                        <select id="kategori" name="kategori"
                            class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                            required>
                            <option value="">Pilih Kategori</option>
                            <option value="0">Ketentuan Check-in & Check-out</option>
                            <option value="1">Denda & Biaya Tambahan</option>
                            <option value="2">Larangan Keras (Tanpa Toleransi)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Nama Aturan
                            <span class="text-red-500">*</span></label>
                        <input id="nama_aturan" name="nama_aturan"
                            class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                            placeholder="Contoh: Waktu Check-in" type="text" required />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Deskripsi
                            <span class="text-red-500">*</span></label>
                        <textarea id="deskripsi" name="deskripsi"
                            class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm h-24"
                            placeholder="Deskripsi lengkap aturan..." required></textarea>
                    </div>
                    <div>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input id="denda" name="denda" type="checkbox" value="1"
                                class="w-4 h-4 text-primary bg-slate-100 border-slate-300 rounded focus:ring-primary" />
                            <span class="text-sm text-slate-700 dark:text-slate-300">Denda</span>
                        </label>
                    </div>
                    <div>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input id="aktif" name="aktif" type="checkbox" value="1" checked
                                class="w-4 h-4 text-primary bg-slate-100 border-slate-300 rounded focus:ring-primary" />
                            <span class="text-sm text-slate-700 dark:text-slate-300">Aktif</span>
                        </label>
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

    <!-- Edit Aturan Modal -->
    <div class="fixed inset-0 z-50 flex items-center justify-center hidden" id="edit-aturan-modal">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        <!-- Modal Content -->
        <div
            class="relative bg-white dark:bg-background-dark w-full max-w-lg mx-4 rounded-xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
            <!-- Header (Fixed) -->
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between flex-shrink-0">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Edit Aturan</h3>
                <button class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 btn-close-edit-modal">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <!-- Scrollable Content -->
            <div class="overflow-y-auto px-6 py-4 space-y-4 scrollbar-thin scrollbar-thumb-slate-300 dark:scrollbar-thumb-slate-600">
                <form id="formEditAturan" class="space-y-4">
                    <input type="hidden" id="edit_id_aturan" name="id_aturan" />
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Kategori
                            <span class="text-red-500">*</span></label>
                        <select id="edit_kategori" name="kategori"
                            class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                            required>
                            <option value="0">Ketentuan Check-in & Check-out</option>
                            <option value="1">Denda & Biaya Tambahan</option>
                            <option value="2">Larangan Keras (Tanpa Toleransi)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Nama Aturan
                            <span class="text-red-500">*</span></label>
                        <input id="edit_nama_aturan" name="nama_aturan"
                            class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                            placeholder="Contoh: Waktu Check-in" type="text" required />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Deskripsi
                            <span class="text-red-500">*</span></label>
                        <textarea id="edit_deskripsi" name="deskripsi"
                            class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm h-24"
                            placeholder="Deskripsi lengkap aturan..." required></textarea>
                    </div>
                    <div>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input id="edit_denda" name="denda" type="checkbox" value="1"
                                class="w-4 h-4 text-primary bg-slate-100 border-slate-300 rounded focus:ring-primary" />
                            <span class="text-sm text-slate-700 dark:text-slate-300">Denda</span>
                        </label>
                    </div>
                    <div>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input id="edit_aktif" name="aktif" type="checkbox" value="1"
                                class="w-4 h-4 text-primary bg-slate-100 border-slate-300 rounded focus:ring-primary" />
                            <span class="text-sm text-slate-700 dark:text-slate-300">Aktif</span>
                        </label>
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
        const API_LIST = 'http://localhost/botanic/api/aturan/list.php';
        const API_NEW = 'http://localhost/botanic/api/aturan/new.php';
        const API_UPDATE = 'http://localhost/botanic/api/aturan/update.php';

        // State
        let allData = [];
        let filteredData = [];
        let currentCategory = 'all';
        let currentPage = 1;
        const itemsPerPage = 10;

        // Format date
        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('id-ID', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            });
        }

        // Toast Notification
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

            setTimeout(() => toast.classList.add('show'), 10);
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        // Load data from API
        async function loadData() {
            try {
                const response = await fetch(API_LIST);
                const result = await response.json();

                if (result.success) {
                    // Flatten grouped data
                    allData = [];
                    for (const catKey in result.data) {
                        result.data[catKey].forEach(item => {
                            allData.push(item);
                        });
                    }

                    // Update counts
                    document.getElementById('totalCount').textContent = allData.length;
                    document.getElementById('countCat0').textContent = result.count_by_category['0'] || 0;
                    document.getElementById('countCat1').textContent = result.count_by_category['1'] || 0;
                    document.getElementById('countCat2').textContent = result.count_by_category['2'] || 0;

                    filterData();
                } else {
                    showToast(result.message, 'error');
                }
            } catch (error) {
                showToast('Gagal memuat data: ' + error.message, 'error');
            }
        }

        // Filter data by search and category
        function filterData() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();

            filteredData = allData.filter(item => {
                const matchesSearch = item.nama_aturan.toLowerCase().includes(searchTerm) ||
                    item.deskripsi.toLowerCase().includes(searchTerm);
                const matchesCategory = currentCategory === 'all' || item.kategori.toString() === currentCategory;

                return matchesSearch && matchesCategory;
            });

            currentPage = 1;
            renderTable();
        }

        // Render table
        function renderTable() {
            const tableBody = document.getElementById('tableBody');
            const mobileView = document.getElementById('mobileView');
            const noData = document.getElementById('noData');

            if (filteredData.length === 0) {
                tableBody.innerHTML = '';
                mobileView.innerHTML = '';
                noData.classList.remove('hidden');
                return;
            }

            noData.classList.add('hidden');

            // Desktop table
            tableBody.innerHTML = filteredData.map(item => `
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                    <td class="px-6 py-4 text-sm font-semibold text-slate-900 dark:text-white">${escapeHtml(item.nama_aturan)}</td>
                    <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">${escapeHtml(item.deskripsi)}</td>
                    <td class="px-6 py-4 text-center">
                        <span class="text-sm font-medium ${item.denda == 1 ? 'text-red-600 dark:text-red-400' : 'text-slate-400'}">
                            ${item.denda == 1 ? 'Ya' : 'Tidak'}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${item.aktif == 1 ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-300'}">
                            ${item.aktif == 1 ? 'Aktif' : 'Nonaktif'}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center text-sm text-slate-500 dark:text-slate-400">
                        ${formatDate(item.created_date)}
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <button onclick="editAturan(${item.id_aturan})"
                                class="p-2 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/20 text-blue-600 dark:text-blue-400 transition-colors"
                                title="Edit">
                                <span class="material-symbols-outlined text-sm">edit</span>
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('');

            // Mobile cards
            mobileView.innerHTML = filteredData.map(item => `
                <div class="p-4 space-y-3 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${item.aktif == 1 ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-300'}">
                                    ${item.aktif == 1 ? 'Aktif' : 'Nonaktif'}
                                </span>
                            </div>
                            <h4 class="text-base font-semibold text-slate-900 dark:text-white">${escapeHtml(item.nama_aturan)}</h4>
                            <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">${escapeHtml(item.deskripsi)}</p>
                            <div class="flex items-center gap-4 mt-3 text-xs text-slate-500 dark:text-slate-400">
                                <span>Denda: ${item.denda == 1 ? 'Ya' : 'Tidak'}</span>
                                <span>${formatDate(item.created_date)}</span>
                            </div>
                        </div>
                        <button onclick="editAturan(${item.id_aturan})"
                            class="p-2 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/20 text-blue-600 dark:text-blue-400 transition-colors flex-shrink-0">
                            <span class="material-symbols-outlined text-sm">edit</span>
                        </button>
                    </div>
                </div>
            `).join('');
        }

        // Escape HTML
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Toggle search (mobile)
        function toggleSearch() {
            const searchInput = document.getElementById('searchInput');
            searchInput.classList.toggle('hidden');
            if (!searchInput.classList.contains('hidden')) {
                searchInput.focus();
            }
        }

        // Open add modal
        document.getElementById('btnTambahAturan').addEventListener('click', function() {
            document.getElementById('add-aturan-modal').classList.remove('hidden');
        });

        // Close modals
        document.querySelectorAll('.btn-close-modal').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('add-aturan-modal').classList.add('hidden');
                document.getElementById('formTambahAturan').reset();
            });
        });

        document.querySelectorAll('.btn-close-edit-modal').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('edit-aturan-modal').classList.add('hidden');
                document.getElementById('formEditAturan').reset();
            });
        });

        // Category tabs
        document.querySelectorAll('.category-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                document.querySelectorAll('.category-tab').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                currentCategory = this.dataset.category;
                filterData();
            });
        });

        // Save new aturan
        document.getElementById('btnSimpan').addEventListener('click', async function() {
            const form = document.getElementById('formTambahAturan');
            const formData = new FormData(form);

            const kategori = formData.get('kategori');
            const nama_aturan = formData.get('nama_aturan');
            const deskripsi = formData.get('deskripsi');

            if (!kategori) {
                showToast('Kategori harus dipilih', 'error');
                return;
            }
            if (!nama_aturan.trim()) {
                showToast('Nama aturan wajib diisi', 'error');
                return;
            }
            if (!deskripsi.trim()) {
                showToast('Deskripsi wajib diisi', 'error');
                return;
            }

            try {
                const response = await fetch(API_NEW, {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.success) {
                    showToast(result.message);
                    document.getElementById('add-aturan-modal').classList.add('hidden');
                    form.reset();
                    loadData();
                } else {
                    showToast(result.message, 'error');
                }
            } catch (error) {
                showToast('Gagal menyimpan data: ' + error.message, 'error');
            }
        });

        // Edit aturan
        async function editAturan(id_aturan) {
            try {
                const response = await fetch(API_LIST);
                const result = await response.json();

                if (result.success) {
                    let foundItem = null;
                    for (const catKey in result.data) {
                        const item = result.data[catKey].find(i => i.id_aturan == id_aturan);
                        if (item) {
                            foundItem = item;
                            break;
                        }
                    }

                    if (foundItem) {
                        document.getElementById('edit_id_aturan').value = foundItem.id_aturan;
                        document.getElementById('edit_kategori').value = foundItem.kategori;
                        document.getElementById('edit_nama_aturan').value = foundItem.nama_aturan;
                        document.getElementById('edit_deskripsi').value = foundItem.deskripsi;
                        document.getElementById('edit_denda').checked = foundItem.denda == 1;
                        document.getElementById('edit_aktif').checked = foundItem.aktif == 1;

                        document.getElementById('edit-aturan-modal').classList.remove('hidden');
                    }
                }
            } catch (error) {
                showToast('Gagal memuat data: ' + error.message, 'error');
            }
        }

        // Update aturan
        document.getElementById('btnUpdate').addEventListener('click', async function() {
            const form = document.getElementById('formEditAturan');
            const formData = new FormData(form);

            const id_aturan = formData.get('id_aturan');
            const kategori = formData.get('kategori');
            const nama_aturan = formData.get('nama_aturan');
            const deskripsi = formData.get('deskripsi');

            if (!id_aturan) {
                showToast('ID aturan tidak valid', 'error');
                return;
            }
            if (!kategori) {
                showToast('Kategori harus dipilih', 'error');
                return;
            }
            if (!nama_aturan.trim()) {
                showToast('Nama aturan wajib diisi', 'error');
                return;
            }
            if (!deskripsi.trim()) {
                showToast('Deskripsi wajib diisi', 'error');
                return;
            }

            try {
                const response = await fetch(API_UPDATE, {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.success) {
                    showToast(result.message);
                    document.getElementById('edit-aturan-modal').classList.add('hidden');
                    form.reset();
                    loadData();
                } else {
                    showToast(result.message, 'error');
                }
            } catch (error) {
                showToast('Gagal mengupdate data: ' + error.message, 'error');
            }
        });

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            loadData();
        });
    </script>

    <style>
        /* Category tab styles */
        .category-tab {
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: #64748b;
            background-color: #f1f5f9;
            transition: all 0.2s;
        }

        .dark .category-tab {
            color: #94a3b8;
            background-color: #1e293b;
        }

        .category-tab:hover {
            background-color: #e2e8f0;
        }

        .dark .category-tab:hover {
            background-color: #334155;
        }

        .category-tab.active {
            background-color: #4b774d;
            color: white;
        }

        /* Toast styles */
        #toastContainer {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .toast {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border-left: 4px solid #4b774d;
            transform: translateX(100%);
            opacity: 0;
            transition: all 0.3s ease;
        }

        .dark .toast {
            background-color: #1e293b;
        }

        .toast.show {
            transform: translateX(0);
            opacity: 1;
        }

        .toast-icon {
            color: #4b774d;
            font-size: 1.25rem;
        }

        .toast-error {
            border-left-color: #ef4444;
        }

        .toast-error .toast-icon {
            color: #ef4444;
        }
    </style>
</body>

</html>
