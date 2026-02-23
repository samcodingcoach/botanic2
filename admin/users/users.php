<?php
// Fetch data from API
$apiUrl = 'http://localhost/botanic/api/users/list.php';
$apiResponse = file_get_contents($apiUrl);
$apiData = json_decode($apiResponse, true);

$usersList = [];
$totalCount = 0;
$message = '';

if ($apiData && $apiData['success']) {
    $usersList = $apiData['data'] ?? [];
    $totalCount = $apiData['count'] ?? count($usersList);
    $message = $apiData['message'] ?? '';
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Admin Panel - Manajemen Users</title>
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
                    <h2 class="text-lg font-semibold text-slate-800 dark:text-white">Manajemen Users</h2>
                </div>
                <div class="flex items-center gap-4">
                    <div class="relative hidden sm:block">
                        <span
                            class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xl leading-none">search</span>
                        <input id="searchInput"
                            class="pl-10 pr-4 py-2 bg-slate-100 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary text-sm w-64"
                            placeholder="Cari user..." type="text" onkeyup="filterData()" />
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
                            Users</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Kelola akun pengguna sistem secara
                            terpusat.</p>
                    </div>
                    <button id="btnTambahUser"
                        class="flex items-center gap-2 px-5 py-2.5 bg-primary text-white font-bold rounded-lg hover:bg-primary/90 transition-all shadow-sm w-full sm:w-auto justify-center">
                        <span class="material-symbols-outlined">add</span>
                        <span>Tambah User</span>
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
                                        Username</th>
                                    <th
                                        class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-center">
                                        Status</th>
                                    <th
                                        class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                        Created</th>
                                    <th
                                        class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                        Last Login</th>
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
                        <span class="material-symbols-outlined text-6xl text-slate-300 dark:text-slate-600 mb-4">person_off</span>
                        <p class="text-slate-500 dark:text-slate-400">Tidak ada data user</p>
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

    <!-- Add User Modal -->
    <div class="fixed inset-0 z-50 flex items-center justify-center hidden" id="add-user-modal">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        <!-- Modal Content -->
        <div
            class="relative bg-white dark:bg-background-dark w-full max-w-lg mx-4 rounded-xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
            <!-- Header (Fixed) -->
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between flex-shrink-0">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Tambah User Baru</h3>
                <button class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 btn-close-modal">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <!-- Scrollable Content -->
            <div class="overflow-y-auto px-6 py-4 space-y-4 scrollbar-thin scrollbar-thumb-slate-300 dark:scrollbar-thumb-slate-600">
                <form id="formTambahUser" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Username
                                <span class="text-red-500">*</span></label>
                            <input id="username" name="username"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                                placeholder="Contoh: admin" type="text" required />
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Password
                                <span class="text-red-500">*</span></label>
                            <input id="password" name="password"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                                placeholder="Minimal 8 karakter" type="password" required minlength="8" />
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Status
                                Aktif</label>
                            <div class="flex items-center gap-2 mt-1">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" id="aktif" name="aktif" checked
                                        class="w-4 h-4 text-primary bg-slate-100 dark:bg-slate-700 border-0 rounded focus:ring-2 focus:ring-primary/20" />
                                    <span class="text-sm text-slate-600 dark:text-slate-400">User aktif</span>
                                </label>
                            </div>
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

    <!-- Edit User Modal -->
    <div class="fixed inset-0 z-50 flex items-center justify-center hidden" id="edit-user-modal">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        <!-- Modal Content -->
        <div
            class="relative bg-white dark:bg-background-dark w-full max-w-lg mx-4 rounded-xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
            <!-- Header (Fixed) -->
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between flex-shrink-0">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Edit User</h3>
                <button class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 btn-close-edit-modal">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <!-- Scrollable Content -->
            <div class="overflow-y-auto px-6 py-4 space-y-4 scrollbar-thin scrollbar-thumb-slate-300 dark:scrollbar-thumb-slate-600">
                <form id="formEditUser" class="space-y-4">
                    <input type="hidden" id="edit_id_users" name="id_users" />
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Username
                                Saat Ini <span class="text-red-500">*</span></label>
                            <input id="edit_username" name="username"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                                type="text" required readonly />
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Password
                                Verifikasi <span class="text-red-500">*</span></label>
                            <input id="edit_password" name="password"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                                placeholder="Masukkan password untuk verifikasi" type="password" required />
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Username
                                Baru</label>
                            <input id="edit_new_username" name="new_username"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                                placeholder="Kosongkan jika tidak ingin mengubah username" type="text" />
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Password
                                Baru</label>
                            <input id="edit_new_password" name="new_password"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                                placeholder="Kosongkan jika tidak ingin mengubah password" type="password" minlength="8" />
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Status
                                Aktif</label>
                            <div class="flex items-center gap-2 mt-1">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" id="edit_aktif" name="aktif"
                                        class="w-4 h-4 text-primary bg-slate-100 dark:bg-slate-700 border-0 rounded focus:ring-2 focus:ring-primary/20" />
                                    <span class="text-sm text-slate-600 dark:text-slate-400">User aktif</span>
                                </label>
                            </div>
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
    <div class="fixed inset-0 z-50 flex items-center justify-center hidden" id="delete-user-modal">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        <!-- Modal Content -->
        <div
            class="relative bg-white dark:bg-background-dark w-full max-w-md mx-4 rounded-xl shadow-2xl overflow-hidden flex flex-col">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Konfirmasi Hapus</h3>
                <button class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 btn-close-delete-modal">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <!-- Content -->
            <div class="px-6 py-6">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                        <span class="material-symbols-outlined text-red-500">warning</span>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500 dark:text-slate-400">User yang akan dihapus:</p>
                        <p id="deleteUsername" class="text-lg font-bold text-slate-900 dark:text-white"></p>
                    </div>
                </div>
                <p class="text-sm text-slate-600 dark:text-slate-400">
                    Apakah Anda yakin ingin menghapus user ini? Tindakan ini tidak dapat dibatalkan.
                </p>
            </div>
            <!-- Footer -->
            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-3 bg-white dark:bg-background-dark">
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

        // Data state
        let allData = <?php echo json_encode($usersList); ?>;
        let filteredData = [...allData];
        const itemsPerPage = 10;
        let currentPage = 1;
        let deleteId = null;

        // Format date helper
        function formatDate(dateString) {
            if (!dateString) return '-';
            const date = new Date(dateString);
            return date.toLocaleDateString('id-ID', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        // Render table
        function renderTable() {
            const tableBody = document.getElementById('tableBody');
            const mobileView = document.getElementById('mobileView');
            const noData = document.getElementById('noData');
            const paginationContainer = document.getElementById('paginationContainer');

            tableBody.innerHTML = '';
            mobileView.innerHTML = '';

            if (filteredData.length === 0) {
                noData.classList.remove('hidden');
                paginationContainer.classList.add('hidden');
                return;
            }

            noData.classList.add('hidden');
            paginationContainer.classList.remove('hidden');

            // Pagination
            const totalPages = Math.ceil(filteredData.length / itemsPerPage);
            const start = (currentPage - 1) * itemsPerPage;
            const end = start + itemsPerPage;
            const paginatedData = filteredData.slice(start, end);

            // Desktop Table
            paginatedData.forEach(user => {
                const row = document.createElement('tr');
                row.className = 'hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors';
                row.innerHTML = `
                    <td class="px-6 py-4">
                        <p class="font-semibold text-slate-900 dark:text-white">${user.username}</p>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${user.aktif === 1 ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-400'}">
                            ${user.aktif === 1 ? 'Aktif' : 'Nonaktif'}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">${formatDate(user.created_at)}</td>
                    <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">${formatDate(user.last_login)}</td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <button class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors btn-edit" data-id="${user.id_users}" data-username="${user.username}" data-aktif="${user.aktif}">
                                <span class="material-symbols-outlined text-slate-600 dark:text-slate-400">edit</span>
                            </button>
                            <button class="p-2 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/30 transition-colors btn-delete" data-id="${user.id_users}" data-username="${user.username}">
                                <span class="material-symbols-outlined text-red-500">delete</span>
                            </button>
                        </div>
                    </td>
                `;
                tableBody.appendChild(row);
            });

            // Mobile Cards
            paginatedData.forEach(user => {
                const card = document.createElement('div');
                card.className = 'p-4 space-y-3';
                card.innerHTML = `
                    <div class="flex items-center justify-between">
                        <p class="font-semibold text-slate-900 dark:text-white">${user.username}</p>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${user.aktif === 1 ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-400'}">
                            ${user.aktif === 1 ? 'Aktif' : 'Nonaktif'}
                        </span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <div>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Created</p>
                            <p class="text-slate-700 dark:text-slate-300">${formatDate(user.created_at)}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Last Login</p>
                            <p class="text-slate-700 dark:text-slate-300">${formatDate(user.last_login)}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                        <button class="flex-1 flex items-center justify-center gap-2 px-3 py-2 bg-slate-100 dark:bg-slate-800 rounded-lg text-sm font-medium btn-edit" data-id="${user.id_users}" data-username="${user.username}" data-aktif="${user.aktif}">
                            <span class="material-symbols-outlined text-sm">edit</span>
                            Edit
                        </button>
                        <button class="flex-1 flex items-center justify-center gap-2 px-3 py-2 bg-red-100 dark:bg-red-900/30 rounded-lg text-sm font-medium text-red-600 dark:text-red-400 btn-delete" data-id="${user.id_users}" data-username="${user.username}">
                            <span class="material-symbols-outlined text-sm">delete</span>
                            Hapus
                        </button>
                    </div>
                `;
                mobileView.appendChild(card);
            });

            // Update showing text
            document.getElementById('showingText').textContent = 
                `Showing ${start + 1} - ${Math.min(end, filteredData.length)} of ${filteredData.length} results`;

            // Pagination buttons
            const paginationButtons = document.getElementById('paginationButtons');
            paginationButtons.innerHTML = '';

            if (currentPage > 1) {
                const prevBtn = document.createElement('button');
                prevBtn.className = 'px-3 py-1.5 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-700';
                prevBtn.textContent = 'Previous';
                prevBtn.onclick = () => { currentPage--; renderTable(); };
                paginationButtons.appendChild(prevBtn);
            }

            for (let i = 1; i <= totalPages; i++) {
                const pageBtn = document.createElement('button');
                pageBtn.className = `px-3 py-1.5 rounded-lg text-sm font-medium ${i === currentPage ? 'bg-primary text-white' : 'bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700'}`;
                pageBtn.textContent = i;
                pageBtn.onclick = () => { currentPage = i; renderTable(); };
                paginationButtons.appendChild(pageBtn);
            }

            if (currentPage < totalPages) {
                const nextBtn = document.createElement('button');
                nextBtn.className = 'px-3 py-1.5 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-sm font-medium hover:bg-slate-50 dark:hover:bg-slate-700';
                nextBtn.textContent = 'Next';
                nextBtn.onclick = () => { currentPage++; renderTable(); };
                paginationButtons.appendChild(nextBtn);
            }

            // Attach event listeners
            attachEventListeners();
        }

        // Attach event listeners to buttons
        function attachEventListeners() {
            document.querySelectorAll('.btn-edit').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.dataset.id;
                    const username = this.dataset.username;
                    const aktif = parseInt(this.dataset.aktif);
                    openEditModal(id, username, aktif);
                });
            });

            document.querySelectorAll('.btn-delete').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.dataset.id;
                    const username = this.dataset.username;
                    openDeleteModal(id, username);
                });
            });
        }

        // Filter function
        function filterData() {
            const searchInput = document.getElementById('searchInput').value.toLowerCase();
            filteredData = allData.filter(user => 
                user.username.toLowerCase().includes(searchInput)
            );
            currentPage = 1;
            renderTable();
        }

        // Toggle search (mobile)
        function toggleSearch() {
            const searchInput = document.getElementById('searchInput');
            searchInput.classList.toggle('hidden');
            if (!searchInput.classList.contains('hidden')) {
                searchInput.focus();
            }
        }

        // Modal functions
        const addModal = document.getElementById('add-user-modal');
        const editModal = document.getElementById('edit-user-modal');
        const deleteModal = document.getElementById('delete-user-modal');

        document.getElementById('btnTambahUser').addEventListener('click', () => {
            addModal.classList.remove('hidden');
        });

        document.querySelectorAll('.btn-close-modal').forEach(btn => {
            btn.addEventListener('click', () => {
                addModal.classList.add('hidden');
                document.getElementById('formTambahUser').reset();
            });
        });

        document.querySelectorAll('.btn-close-edit-modal').forEach(btn => {
            btn.addEventListener('click', () => {
                editModal.classList.add('hidden');
                document.getElementById('formEditUser').reset();
            });
        });

        document.querySelectorAll('.btn-close-delete-modal').forEach(btn => {
            btn.addEventListener('click', () => {
                deleteModal.classList.add('hidden');
            });
        });

        // Open edit modal
        function openEditModal(id, username, aktif) {
            document.getElementById('edit_id_users').value = id;
            document.getElementById('edit_username').value = username;
            document.getElementById('edit_aktif').checked = aktif === 1;
            document.getElementById('edit_password').value = '';
            document.getElementById('edit_new_username').value = '';
            document.getElementById('edit_new_password').value = '';
            editModal.classList.remove('hidden');
        }

        // Open delete modal
        function openDeleteModal(id, username) {
            deleteId = id;
            document.getElementById('deleteUsername').textContent = username;
            deleteModal.classList.remove('hidden');
        }

        // Save new user
        document.getElementById('btnSimpan').addEventListener('click', async () => {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            const aktif = document.getElementById('aktif').checked ? 1 : 0;

            if (!username || !password) {
                showToast('Username dan password wajib diisi', 'error');
                return;
            }

            if (password.length < 8) {
                showToast('Password minimal 8 karakter', 'error');
                return;
            }

            const btnSimpan = document.getElementById('btnSimpan');
            btnSimpan.disabled = true;
            btnSimpan.innerHTML = '<span class="material-symbols-outlined text-sm animate-spin">progress_activity</span><span>Menyimpan...</span>';

            try {
                const formData = new FormData();
                formData.append('username', username);
                formData.append('password', password);
                formData.append('aktif', aktif);

                const response = await fetch('http://localhost/botanic/api/users/new.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    showToast(result.message);
                    addModal.classList.add('hidden');
                    document.getElementById('formTambahUser').reset();
                    // Reload data
                    window.location.reload();
                } else {
                    showToast(result.message, 'error');
                }
            } catch (error) {
                showToast('Terjadi kesalahan. Silakan coba lagi.', 'error');
            } finally {
                btnSimpan.disabled = false;
                btnSimpan.innerHTML = '<span class="material-symbols-outlined text-sm">save</span><span>Simpan</span>';
            }
        });

        // Update user
        document.getElementById('btnUpdate').addEventListener('click', async () => {
            const id_users = document.getElementById('edit_id_users').value;
            const username = document.getElementById('edit_username').value;
            const password = document.getElementById('edit_password').value;
            const newUsername = document.getElementById('edit_new_username').value.trim();
            const newPassword = document.getElementById('edit_new_password').value;
            const aktif = document.getElementById('edit_aktif').checked ? 1 : 0;

            if (!password) {
                showToast('Password wajib diisi untuk verifikasi', 'error');
                return;
            }

            // Validate new password length if provided
            if (newPassword && newPassword.length < 8) {
                showToast('Password baru minimal 8 karakter', 'error');
                return;
            }

            const btnUpdate = document.getElementById('btnUpdate');
            btnUpdate.disabled = true;
            btnUpdate.innerHTML = '<span class="material-symbols-outlined text-sm animate-spin">progress_activity</span><span>Updating...</span>';

            try {
                const formData = new FormData();
                formData.append('id_users', id_users);
                formData.append('username', username);
                formData.append('password', password);
                formData.append('aktif', aktif);
                
                if (newUsername) {
                    formData.append('new_username', newUsername);
                }
                if (newPassword) {
                    formData.append('new_password', newPassword);
                }

                const response = await fetch('http://localhost/botanic/api/users/update.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    showToast(result.message);
                    editModal.classList.add('hidden');
                    document.getElementById('formEditUser').reset();
                    // Reload data
                    window.location.reload();
                } else {
                    showToast(result.message, 'error');
                }
            } catch (error) {
                showToast('Terjadi kesalahan. Silakan coba lagi.', 'error');
            } finally {
                btnUpdate.disabled = false;
                btnUpdate.innerHTML = '<span class="material-symbols-outlined text-sm">save</span><span>Update</span>';
            }
        });

        // Delete user
        document.getElementById('btnHapus').addEventListener('click', async () => {
            const btnHapus = document.getElementById('btnHapus');
            btnHapus.disabled = true;
            btnHapus.innerHTML = '<span class="material-symbols-outlined text-sm animate-spin">progress_activity</span><span>Menghapus...</span>';

            try {
                const formData = new FormData();
                formData.append('id_users', deleteId);

                const response = await fetch('http://localhost/botanic/api/users/delete.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    showToast(result.message);
                    deleteModal.classList.add('hidden');
                    // Reload data
                    window.location.reload();
                } else {
                    showToast(result.message, 'error');
                }
            } catch (error) {
                showToast('Terjadi kesalahan. Silakan coba lagi.', 'error');
            } finally {
                btnHapus.disabled = false;
                btnHapus.innerHTML = '<span class="material-symbols-outlined text-sm">delete</span><span>Hapus</span>';
            }
        });

        // Initialize
        renderTable();
    </script>
</body>

</html>
