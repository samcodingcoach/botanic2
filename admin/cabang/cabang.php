<?php
// Fetch data from API
$apiUrl = 'http://localhost/botanic/api/cabang/list.php';
$apiResponse = file_get_contents($apiUrl);
$apiData = json_decode($apiResponse, true);

$cabangList = [];
$totalCount = 0;
$message = '';

if ($apiData && $apiData['success']) {
    $cabangList = $apiData['data'];
    $totalCount = $apiData['count'];
    $message = $apiData['message'];
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Admin Panel - Manajemen Cabang</title>
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
                    <h2 class="text-lg font-semibold text-slate-800 dark:text-white">Manajemen Cabang</h2>
                </div>
                <div class="flex items-center gap-4">
                    <div class="relative hidden sm:block">
                        <span
                            class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xl leading-none">search</span>
                        <input id="searchInput"
                            class="pl-10 pr-4 py-2 bg-slate-100 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary text-sm w-64"
                            placeholder="Cari cabang..." type="text" onkeyup="filterData()" />
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
                            Cabang</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Kelola informasi operasional seluruh
                            kantor
                            cabang secara terpusat.</p>
                    </div>
                    <button id="btnTambahCabang"
                        class="flex items-center gap-2 px-5 py-2.5 bg-primary text-white font-bold rounded-lg hover:bg-primary/90 transition-all shadow-sm w-full sm:w-auto justify-center">
                        <span class="material-symbols-outlined">add</span>
                        <span>Tambah Cabang</span>
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
                                        Foto</th>
                                    <th
                                        class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                        Cabang</th>
                                    <th
                                        class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                        Alamat</th>
                                    <th
                                        class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                        HP</th>
                                    <th
                                        class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-center">
                                        GPS</th>
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
                        <span class="material-symbols-outlined text-6xl text-slate-300 dark:text-slate-600 mb-4">store</span>
                        <p class="text-slate-500 dark:text-slate-400">Tidak ada data cabang</p>
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

    <!-- Add Branch Modal -->
    <div class="fixed inset-0 z-50 flex items-center justify-center hidden" id="add-branch-modal">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        <!-- Modal Content -->
        <div
            class="relative bg-white dark:bg-background-dark w-full max-w-lg mx-4 rounded-xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
            <!-- Header (Fixed) -->
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between flex-shrink-0">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Tambah Cabang Baru</h3>
                <button class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 btn-close-modal">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <!-- Scrollable Content -->
            <div class="overflow-y-auto px-6 py-4 space-y-4 scrollbar-thin scrollbar-thumb-slate-300 dark:scrollbar-thumb-slate-600">
                <form id="formTambahCabang" class="space-y-4" enctype="multipart/form-data">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Nama
                                Cabang <span class="text-red-500">*</span></label>
                            <input id="nama_cabang" name="nama_cabang"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                                placeholder="Contoh: Jakarta Selatan" type="text" required />
                        </div>
                        <div class="col-span-2">
                            <label
                                class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Alamat <span class="text-red-500">*</span></label>
                            <textarea id="alamat" name="alamat"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm h-20"
                                placeholder="Alamat lengkap kantor cabang..." required></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Kode
                                Cabang <span class="text-red-500">*</span></label>
                            <input id="kode_cabang" name="kode_cabang"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                                placeholder="JKT-005" type="text" required />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">HP /
                                Telepon <span class="text-red-500">*</span></label>
                            <input id="hp" name="hp"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                                placeholder="0812-xxxx-xxxx" type="text" required />
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">GPS
                                (Koordinat)</label>
                            <input id="gps" name="gps"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                                placeholder="-6.2088, 106.8456" type="text" />
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Foto
                                Cabang</label>
                            <div
                                class="mt-1 flex items-center gap-4 px-4 py-4 border-2 border-slate-300 dark:border-slate-700 border-dashed rounded-lg">
                                <!-- Preview Image -->
                                <div id="fotoPreview" class="hidden w-20 h-20 rounded-lg overflow-hidden border border-slate-200 dark:border-slate-600 flex-shrink-0 bg-slate-100 dark:bg-slate-800">
                                    <img id="previewImg" src="" alt="Preview" class="w-full h-full object-cover" />
                                </div>
                                <div class="space-y-1 text-center flex-1">
                                    <span class="material-symbols-outlined text-slate-400 text-3xl">image</span>
                                    <div class="flex text-sm text-slate-600 dark:text-slate-400 justify-center">
                                        <label
                                            class="relative cursor-pointer bg-white dark:bg-background-dark rounded-md font-medium text-primary hover:text-primary/80 focus-within:outline-none"
                                            for="foto">
                                            <span>Upload file</span>
                                            <input class="sr-only" id="foto" name="foto" type="file" accept="image/*" />
                                        </label>
                                    </div>
                                    <p class="text-xs text-slate-500">PNG, JPG max 1MB</p>
                                    <p id="fileName" class="text-xs text-slate-400 truncate max-w-[200px]"></p>
                                </div>
                            </div>
                            <p id="fotoError" class="text-xs text-red-500 mt-1 hidden"></p>
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

    <!-- Edit Branch Modal -->
    <div class="fixed inset-0 z-50 flex items-center justify-center hidden" id="edit-branch-modal">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        <!-- Modal Content -->
        <div
            class="relative bg-white dark:bg-background-dark w-full max-w-lg mx-4 rounded-xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
            <!-- Header (Fixed) -->
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between flex-shrink-0">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Edit Cabang</h3>
                <button class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 btn-close-edit-modal">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <!-- Scrollable Content -->
            <div class="overflow-y-auto px-6 py-4 space-y-4 scrollbar-thin scrollbar-thumb-slate-300 dark:scrollbar-thumb-slate-600">
                <form id="formEditCabang" class="space-y-4" enctype="multipart/form-data">
                    <input type="hidden" id="edit_id_cabang" name="id_cabang" />
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Nama
                                Cabang <span class="text-red-500">*</span></label>
                            <input id="edit_nama_cabang" name="nama_cabang"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                                placeholder="Contoh: Jakarta Selatan" type="text" required />
                        </div>
                        <div class="col-span-2">
                            <label
                                class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Alamat <span class="text-red-500">*</span></label>
                            <textarea id="edit_alamat" name="alamat"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm h-20"
                                placeholder="Alamat lengkap kantor cabang..." required></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Kode
                                Cabang <span class="text-red-500">*</span></label>
                            <input id="edit_kode_cabang" name="kode_cabang"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                                placeholder="JKT-005" type="text" required />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">HP /
                                Telepon <span class="text-red-500">*</span></label>
                            <input id="edit_hp" name="hp"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                                placeholder="0812-xxxx-xxxx" type="text" required />
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">GPS
                                (Koordinat)</label>
                            <input id="edit_gps" name="gps"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                                placeholder="-6.2088, 106.8456" type="text" />
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Foto
                                Cabang</label>
                            <div
                                class="mt-1 flex items-center gap-4 px-4 py-4 border-2 border-slate-300 dark:border-slate-700 border-dashed rounded-lg">
                                <!-- Preview Image -->
                                <div id="edit_foto_preview_container" class="hidden w-20 h-20 rounded-lg overflow-hidden border border-slate-200 dark:border-slate-600 flex-shrink-0 bg-slate-100 dark:bg-slate-800">
                                    <img id="edit_preview_img" src="" alt="Preview" class="w-full h-full object-cover" />
                                </div>
                                <div class="space-y-1 text-center flex-1">
                                    <span class="material-symbols-outlined text-slate-400 text-3xl">image</span>
                                    <div class="flex text-sm text-slate-600 dark:text-slate-400 justify-center">
                                        <label
                                            class="relative cursor-pointer bg-white dark:bg-background-dark rounded-md font-medium text-primary hover:text-primary/80 focus-within:outline-none"
                                            for="edit_foto">
                                            <span>Upload file</span>
                                            <input class="sr-only" id="edit_foto" name="foto" type="file" accept="image/*" />
                                        </label>
                                    </div>
                                    <p class="text-xs text-slate-500">PNG, JPG max 1MB</p>
                                    <p id="edit_file_name" class="text-xs text-slate-400 truncate max-w-[200px]"></p>
                                </div>
                            </div>
                            <p id="edit_foto_error" class="text-xs text-red-500 mt-1 hidden"></p>
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
                const confirmed = confirm('Apakah Anda yakin ingin menyimpan data cabang ini?');
                resolve(confirmed);
            });
        }

        // Modal functions
        const modal = document.getElementById('add-branch-modal');
        const btnTambah = document.getElementById('btnTambahCabang');
        const btnClose = document.querySelectorAll('.btn-close-modal');
        const btnSimpan = document.getElementById('btnSimpan');
        const formTambah = document.getElementById('formTambahCabang');

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
                const requiredFields = ['nama_cabang', 'alamat', 'kode_cabang', 'hp'];
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
                fetch('../../api/cabang/new.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Data cabang berhasil disimpan!', 'success');
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
        document.querySelectorAll('input, textarea').forEach(input => {
            input.addEventListener('input', function() {
                this.classList.remove('border-red-500');
            });
        });

        // Image Preview and Validation
        const fotoInput = document.getElementById('foto');
        const fotoPreview = document.getElementById('fotoPreview');
        const previewImg = document.getElementById('previewImg');
        const fileName = document.getElementById('fileName');
        const fotoError = document.getElementById('fotoError');

        if (fotoInput) {
            fotoInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                
                // Reset error
                fotoError.classList.add('hidden');
                fotoInput.classList.remove('border-red-500');
                
                if (!file) {
                    fotoPreview.classList.add('hidden');
                    fileName.textContent = '';
                    return;
                }

                // Validate file type
                const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                if (!validTypes.includes(file.type)) {
                    fotoError.textContent = 'Tipe file tidak valid. Gunakan JPG, PNG, GIF, atau WEBP.';
                    fotoError.classList.remove('hidden');
                    fotoInput.classList.add('border-red-500');
                    fotoInput.value = ''; // Clear input
                    fotoPreview.classList.add('hidden');
                    fileName.textContent = '';
                    return;
                }

                // Validate file size (1MB)
                if (file.size > maxSize) {
                    fotoError.textContent = 'Ukuran file terlalu besar. Maksimal 1MB.';
                    fotoError.classList.remove('hidden');
                    fotoInput.classList.add('border-red-500');
                    fotoInput.value = ''; // Clear input
                    fotoPreview.classList.add('hidden');
                    fileName.textContent = '';
                    return;
                }

                // Show preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    fotoPreview.classList.remove('hidden');
                    fileName.textContent = file.name;
                };
                reader.readAsDataURL(file);
            });
        }

        // Edit Modal Functions
        const editModal = document.getElementById('edit-branch-modal');
        const btnCloseEdit = document.querySelectorAll('.btn-close-edit-modal');
        const btnUpdate = document.getElementById('btnUpdate');
        const formEdit = document.getElementById('formEditCabang');
        const editFotoInput = document.getElementById('edit_foto');
        const editFotoPreview = document.getElementById('edit_foto_preview_container');
        const editPreviewImg = document.getElementById('edit_preview_img');
        const editFileName = document.getElementById('edit_file_name');
        const editFotoError = document.getElementById('edit_foto_error');

        function openEditModal(id, nama, kode, alamat, hp, gps, foto) {
            document.getElementById('edit_id_cabang').value = id;
            document.getElementById('edit_nama_cabang').value = nama;
            document.getElementById('edit_kode_cabang').value = kode;
            document.getElementById('edit_alamat').value = alamat;
            document.getElementById('edit_hp').value = hp;
            document.getElementById('edit_gps').value = gps;

            // Show existing foto preview
            if (foto && foto !== '') {
                editPreviewImg.src = getImageUrl(foto);
                editFotoPreview.classList.remove('hidden');
                editFileName.textContent = foto;
            } else {
                editFotoPreview.classList.add('hidden');
                editFileName.textContent = '';
            }

            editModal.classList.remove('hidden');
        }

        function closeEditModal() {
            editModal.classList.add('hidden');
            formEdit.reset();
            editFotoPreview.classList.add('hidden');
            editFileName.textContent = '';
            editFotoError.classList.add('hidden');
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
            if (e.target.closest('.btn-edit-cabang')) {
                const btn = e.target.closest('.btn-edit-cabang');
                openEditModal(
                    btn.dataset.id,
                    btn.dataset.nama,
                    btn.dataset.kode,
                    btn.dataset.alamat,
                    btn.dataset.hp,
                    btn.dataset.gps,
                    btn.dataset.foto
                );
            }
            // Handle edit button clicks (mobile)
            if (e.target.closest('.btn-edit-cabang-mobile')) {
                const btn = e.target.closest('.btn-edit-cabang-mobile');
                openEditModal(
                    btn.dataset.id,
                    btn.dataset.nama,
                    btn.dataset.kode,
                    btn.dataset.alamat,
                    btn.dataset.hp,
                    btn.dataset.gps,
                    btn.dataset.foto
                );
            }
        });

        // Handle Update Button
        if (btnUpdate) {
            btnUpdate.addEventListener('click', async function() {
                // Validate form
                const requiredFields = ['edit_nama_cabang', 'edit_alamat', 'edit_kode_cabang', 'edit_hp'];
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
                const confirmed = confirm('Apakah Anda yakin ingin mengupdate data cabang ini?');
                if (!confirmed) return;

                // Show loading state
                btnUpdate.disabled = true;
                btnUpdate.innerHTML = '<span class="material-symbols-outlined spinner">sync</span><span>Updating...</span>';

                // Prepare FormData
                const formData = new FormData(formEdit);

                // Send to API
                fetch('../../api/cabang/update.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Data cabang berhasil diupdate!', 'success');
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
        document.querySelectorAll('#formEditCabang input, #formEditCabang textarea').forEach(input => {
            input.addEventListener('input', function() {
                this.classList.remove('border-red-500');
            });
        });

        // Image Preview and Validation for Edit Form
        if (editFotoInput) {
            editFotoInput.addEventListener('change', function(e) {
                const file = e.target.files[0];

                // Reset error
                editFotoError.classList.add('hidden');
                editFotoInput.classList.remove('border-red-500');

                if (!file) {
                    editFotoPreview.classList.add('hidden');
                    editFileName.textContent = '';
                    return;
                }

                // Validate file type
                const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                if (!validTypes.includes(file.type)) {
                    editFotoError.textContent = 'Tipe file tidak valid. Gunakan JPG, PNG, GIF, atau WEBP.';
                    editFotoError.classList.remove('hidden');
                    editFotoInput.classList.add('border-red-500');
                    editFotoInput.value = ''; // Clear input
                    editFotoPreview.classList.add('hidden');
                    editFileName.textContent = '';
                    return;
                }

                // Validate file size (1MB)
                if (file.size > maxSize) {
                    editFotoError.textContent = 'Ukuran file terlalu besar. Maksimal 1MB.';
                    editFotoError.classList.remove('hidden');
                    editFotoInput.classList.add('border-red-500');
                    editFotoInput.value = ''; // Clear input
                    editFotoPreview.classList.add('hidden');
                    editFileName.textContent = '';
                    return;
                }

                // Show preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    editPreviewImg.src = e.target.result;
                    editFotoPreview.classList.remove('hidden');
                    editFileName.textContent = file.name;
                };
                reader.readAsDataURL(file);
            });
        }

        // Data from PHP
        const allData = <?php echo json_encode($cabangList); ?>;
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
            filteredData = allData.filter(item => {
                return (
                    (item.nama_cabang && item.nama_cabang.toLowerCase().includes(searchTerm)) ||
                    (item.kode_cabang && item.kode_cabang.toLowerCase().includes(searchTerm)) ||
                    (item.alamat && item.alamat.toLowerCase().includes(searchTerm))
                );
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

        function getImageUrl(foto) {
            if (!foto || foto === '') {
                return 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 40 40"%3E%3Crect fill="%23e2e8f0" width="40" height="40"/%3E%3Ctext fill="%2394a3b8" font-family="sans-serif" font-size="20" text-anchor="middle" x="20" y="25"%3E🏢%3C/text%3E%3C/svg%3E';
            }
            return '../../images/' + foto;
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
                            <img class="w-full h-full object-cover" src="${getImageUrl(item.foto)}" alt="${item.nama_cabang || 'Cabang'}" />
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="font-semibold text-slate-900 dark:text-white">${item.nama_cabang || '-'}</div>
                        <div class="text-xs text-slate-500">${item.kode_cabang || '-'}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-slate-600 dark:text-slate-400 max-w-xs truncate">${item.alamat || '-'}</div>
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">${item.hp || '-'}</td>
                    <td class="px-6 py-4 text-center">
                        ${item.gps ? `
                        <a class="text-primary hover:text-primary/70 inline-flex items-center gap-1" href="https://www.google.com/maps?q=${item.gps}" target="_blank">
                            <span class="material-symbols-outlined text-xl">map</span>
                        </a>` : '<span class="text-slate-400">-</span>'}
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-500">${formatDate(item.created_date)}</td>
                    <td class="px-6 py-4 text-right space-x-2">
                        <button class="p-1.5 text-slate-400 hover:text-primary transition-colors btn-edit-cabang" 
                            data-id="${item.id_cabang}"
                            data-nama="${item.nama_cabang || ''}"
                            data-kode="${item.kode_cabang || ''}"
                            data-alamat="${item.alamat || ''}"
                            data-hp="${item.hp || ''}"
                            data-gps="${item.gps || ''}"
                            data-foto="${item.foto || ''}"
                            title="Edit">
                            <span class="material-symbols-outlined text-xl">edit_square</span>
                        </button>
                        <button class="p-1.5 text-slate-400 hover:text-red-500 transition-colors" title="Hapus">
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
                    <div class="flex items-start gap-3">
                        <div class="w-16 h-16 rounded-lg overflow-hidden border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800 flex-shrink-0">
                            <img class="w-full h-full object-cover" src="${getImageUrl(item.foto)}" alt="${item.nama_cabang || 'Cabang'}" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="font-semibold text-slate-900 dark:text-white">${item.nama_cabang || '-'}</div>
                            <div class="text-xs text-slate-500">${item.kode_cabang || '-'}</div>
                        </div>
                        <div class="flex gap-1">
                            <button class="p-2 text-slate-400 hover:text-primary transition-colors btn-edit-cabang-mobile" 
                                data-id="${item.id_cabang}"
                                data-nama="${item.nama_cabang || ''}"
                                data-kode="${item.kode_cabang || ''}"
                                data-alamat="${item.alamat || ''}"
                                data-hp="${item.hp || ''}"
                                data-gps="${item.gps || ''}"
                                data-foto="${item.foto || ''}"
                                title="Edit">
                                <span class="material-symbols-outlined text-xl">edit_square</span>
                            </button>
                            <button class="p-2 text-slate-400 hover:text-red-500 transition-colors" title="Hapus">
                                <span class="material-symbols-outlined text-xl">delete</span>
                            </button>
                        </div>
                    </div>
                    <div class="text-sm text-slate-600 dark:text-slate-400 space-y-1">
                        ${item.alamat ? `
                        <div class="flex items-start gap-2">
                            <span class="material-symbols-outlined text-sm mt-0.5">location_on</span>
                            <span>${item.alamat}</span>
                        </div>` : ''}
                        ${item.hp ? `
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm">phone</span>
                            <span>${item.hp}</span>
                        </div>` : ''}
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm">calendar_today</span>
                            <span>${formatDate(item.created_date)}</span>
                        </div>
                    </div>
                    ${item.gps ? `
                    <a class="text-primary hover:text-primary/70 inline-flex items-center gap-1 text-sm" href="https://www.google.com/maps?q=${item.gps}" target="_blank">
                        <span class="material-symbols-outlined text-sm">map</span>
                        <span>Lihat di Peta</span>
                    </a>` : ''}
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

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            renderTable();
            renderMobileView();
            renderPagination();
        });
    </script>
</body>

</html>
