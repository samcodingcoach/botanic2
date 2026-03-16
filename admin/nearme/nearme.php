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
$apiUrl = 'http://localhost/botanic/api/nearme/list.php';
$apiResponse = file_get_contents($apiUrl);
$apiData = json_decode($apiResponse, true);

$nearmeList = [];
$totalCount = 0;
$message = '';

if ($apiData && $apiData['success']) {
    $nearmeList = $apiData['data'];
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
    <title>Admin Panel - Manajemen Near Area</title>
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
                    <h2 class="text-lg font-semibold text-slate-800 dark:text-white">Manajemen Near Area</h2>
                </div>
                <div class="flex items-center gap-4">
                    <div class="relative hidden sm:block">
                        <span
                            class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xl leading-none">search</span>
                        <input id="searchInput"
                            class="pl-10 pr-4 py-2 bg-slate-100 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary text-sm w-64"
                            placeholder="Cari near area..." type="text" onkeyup="filterData()" />
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
                            Near Area</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Kelola area terdekat di sekitar
                            kantor cabang hotel.</p>
                    </div>
                    <button id="btnTambahNearMe"
                        class="flex items-center gap-2 px-5 py-2.5 bg-primary text-white font-bold rounded-lg hover:bg-primary/90 transition-all shadow-sm w-full sm:w-auto justify-center">
                        <span class="material-symbols-outlined">add</span>
                        <span>Tambah Near Area</span>
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
                                        Foto</th>
                                    <th
                                        class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                        Near Area</th>
                                    <th
                                        class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                        Jenis</th>
                                    <th
                                        class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                        Jarak</th>
                                    <th
                                        class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-center">
                                        GPS</th>
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
                        <span class="material-symbols-outlined text-6xl text-slate-300 dark:text-slate-600 mb-4">near_me</span>
                        <p class="text-slate-500 dark:text-slate-400">Tidak ada data near area</p>
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

    <!-- Add Near Area Modal -->
    <div class="fixed inset-0 z-50 flex items-center justify-center hidden" id="add-nearme-modal">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        <!-- Modal Content -->
        <div
            class="relative bg-white dark:bg-background-dark w-full max-w-lg mx-4 rounded-xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
            <!-- Header (Fixed) -->
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between flex-shrink-0">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Tambah Near Area Baru</h3>
                <button class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 btn-close-modal">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <!-- Scrollable Content -->
            <div class="overflow-y-auto px-6 py-4 space-y-4 scrollbar-thin scrollbar-thumb-slate-300 dark:scrollbar-thumb-slate-600">
                <form id="formTambahNearMe" class="space-y-4" enctype="multipart/form-data">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Nama
                                Area <span class="text-red-500">*</span></label>
                            <input id="nama_area" name="nama_area"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                                placeholder="Contoh: Malioboro" type="text" required />
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
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Jenis Area <span class="text-red-500">*</span></label>
                            <input id="jenis_area" name="jenis_area"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                                placeholder="Contoh: Wisata" type="text" required />
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Alamat</label>
                            <textarea id="alamat" name="alamat"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm h-20"
                                placeholder="Alamat lengkap area..."></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">GPS</label>
                            <input id="gps" name="gps"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                                placeholder="-7.7956, 110.3695" type="text" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Jarak</label>
                            <input id="jarak" name="jarak"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                                placeholder="2 km" type="text" />
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
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Foto
                                Area</label>
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
                                    <p class="text-xs text-slate-500">PNG, JPG max 1MB (512x512)</p>
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

    <!-- Edit Near Area Modal -->
    <div class="fixed inset-0 z-50 flex items-center justify-center hidden" id="edit-nearme-modal">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        <!-- Modal Content -->
        <div
            class="relative bg-white dark:bg-background-dark w-full max-w-lg mx-4 rounded-xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
            <!-- Header (Fixed) -->
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between flex-shrink-0">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Edit Near Area</h3>
                <button class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 btn-close-edit-modal">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <!-- Scrollable Content -->
            <div class="overflow-y-auto px-6 py-4 space-y-4 scrollbar-thin scrollbar-thumb-slate-300 dark:scrollbar-thumb-slate-600">
                <form id="formEditNearMe" class="space-y-4" enctype="multipart/form-data">
                    <input type="hidden" id="edit_id_area" name="id_area" />
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Nama
                                Area <span class="text-red-500">*</span></label>
                            <input id="edit_nama_area" name="nama_area"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                                placeholder="Contoh: Malioboro" type="text" required />
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
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Jenis Area <span class="text-red-500">*</span></label>
                            <input id="edit_jenis_area" name="jenis_area"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                                placeholder="Contoh: Wisata" type="text" required />
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Alamat</label>
                            <textarea id="edit_alamat" name="alamat"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm h-20"
                                placeholder="Alamat lengkap area..."></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">GPS</label>
                            <input id="edit_gps" name="gps"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                                placeholder="-7.7956, 110.3695" type="text" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Jarak</label>
                            <input id="edit_jarak" name="jarak"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                                placeholder="2 km" type="text" />
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
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Foto
                                Area</label>
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
                                    <p class="text-xs text-slate-500">PNG, JPG max 1MB (512x512)</p>
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

    <!-- Image Preview Modal -->
    <div class="fixed inset-0 z-50 flex items-center justify-center hidden" id="image-preview-modal">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-slate-900/80 backdrop-blur-sm" onclick="closeImagePreview()"></div>
        <!-- Modal Content -->
        <div class="relative bg-white dark:bg-background-dark w-full max-w-2xl mx-4 rounded-xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between flex-shrink-0">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Preview Foto</h3>
                <button onclick="closeImagePreview()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <!-- Image Container -->
            <div class="p-6 flex items-center justify-center bg-slate-100 dark:bg-slate-800">
                <img id="preview-image-full" src="" alt="Foto Preview" class="max-w-full max-h-[60vh] object-contain rounded-lg" />
            </div>
            <!-- Footer -->
            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-3 flex-shrink-0 bg-white dark:bg-background-dark">
                <button onclick="closeImagePreview()"
                    class="px-6 py-2 text-sm font-bold text-white bg-primary hover:bg-primary/90 rounded-lg shadow-sm transition-all">
                    Close
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
                const confirmed = confirm('Apakah Anda yakin ingin menyimpan data near area ini?');
                resolve(confirmed);
            });
        }

        // Modal functions
        const modal = document.getElementById('add-nearme-modal');
        const btnTambah = document.getElementById('btnTambahNearMe');
        const btnClose = document.querySelectorAll('.btn-close-modal');
        const btnSimpan = document.getElementById('btnSimpan');
        const formTambah = document.getElementById('formTambahNearMe');

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
                const requiredFields = ['nama_area', 'id_cabang', 'jenis_area'];
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
                fetch('../../api/nearme/new.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Data near area berhasil disimpan!', 'success');
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
        const editModal = document.getElementById('edit-nearme-modal');
        const btnCloseEdit = document.querySelectorAll('.btn-close-edit-modal');
        const btnUpdate = document.getElementById('btnUpdate');
        const formEdit = document.getElementById('formEditNearMe');
        const editFotoInput = document.getElementById('edit_foto');
        const editFotoPreview = document.getElementById('edit_foto_preview_container');
        const editPreviewImg = document.getElementById('edit_preview_img');
        const editFileName = document.getElementById('edit_file_name');
        const editFotoError = document.getElementById('edit_foto_error');

        function openEditModal(id, namaArea, idCabang, jenisArea, alamat, gps, jarak, aktif, foto, createdDate) {
            document.getElementById('edit_id_area').value = id;
            document.getElementById('edit_nama_area').value = namaArea;
            document.getElementById('edit_id_cabang').value = idCabang;
            document.getElementById('edit_jenis_area').value = jenisArea;
            document.getElementById('edit_alamat').value = alamat;
            document.getElementById('edit_gps').value = gps;
            document.getElementById('edit_jarak').value = jarak;
            document.getElementById('edit_aktif').value = aktif;

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
            if (e.target.closest('.btn-edit-nearme')) {
                const btn = e.target.closest('.btn-edit-nearme');
                openEditModal(
                    btn.dataset.id,
                    btn.dataset.namaArea,
                    btn.dataset.idCabang,
                    btn.dataset.jenisArea,
                    btn.dataset.alamat,
                    btn.dataset.gps,
                    btn.dataset.jarak,
                    btn.dataset.aktif,
                    btn.dataset.foto,
                    btn.dataset.createdDate
                );
            }
            // Handle edit button clicks (mobile)
            if (e.target.closest('.btn-edit-nearme-mobile')) {
                const btn = e.target.closest('.btn-edit-nearme-mobile');
                openEditModal(
                    btn.dataset.id,
                    btn.dataset.namaArea,
                    btn.dataset.idCabang,
                    btn.dataset.jenisArea,
                    btn.dataset.alamat,
                    btn.dataset.gps,
                    btn.dataset.jarak,
                    btn.dataset.aktif,
                    btn.dataset.foto,
                    btn.dataset.createdDate
                );
            }
        });

        // Handle Update Button
        if (btnUpdate) {
            btnUpdate.addEventListener('click', async function() {
                // Validate form
                const requiredFields = ['edit_nama_area', 'edit_id_cabang', 'edit_jenis_area'];
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
                const confirmed = confirm('Apakah Anda yakin ingin mengupdate data near area ini?');
                if (!confirmed) return;

                // Show loading state
                btnUpdate.disabled = true;
                btnUpdate.innerHTML = '<span class="material-symbols-outlined spinner">sync</span><span>Updating...</span>';

                // Prepare FormData
                const formData = new FormData(formEdit);

                // Send to API
                fetch('../../api/nearme/update.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Data near area berhasil diupdate!', 'success');
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
        document.querySelectorAll('#formEditNearMe input, #formEditNearMe select, #formEditNearMe textarea').forEach(input => {
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
        const allData = <?php echo json_encode($nearmeList); ?>;
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

        function filterData() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            filteredData = allData.filter(item => {
                const matchesSearch = (
                    (item.nama_area && item.nama_area.toLowerCase().includes(searchTerm)) ||
                    (item.jenis_area && item.jenis_area.toLowerCase().includes(searchTerm))
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

        function getImageUrl(foto) {
            if (!foto || foto === '') {
                return 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 40 40"%3E%3Crect fill="%23e2e8f0" width="40" height="40"/%3E%3Ctext fill="%2394a3b8" font-family="sans-serif" font-size="20" text-anchor="middle" x="20" y="25"%3E📍%3C/text%3E%3C/svg%3E';
            }
            // Check if foto already contains 'near/' prefix
            if (foto.startsWith('near/')) {
                return '../../images/' + foto;
            }
            return '../../images/near/' + foto;
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
                        <div class="w-10 h-10 rounded-lg overflow-hidden border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800 cursor-pointer hover:ring-2 hover:ring-primary transition-all" onclick="openImagePreview('${getImageUrl(item.foto)}', '${item.nama_area || 'Near Area'}')">
                            <img class="w-full h-full object-cover" src="${getImageUrl(item.foto)}" alt="${item.nama_area || 'Near Area'}" />
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="font-semibold text-slate-900 dark:text-white">${item.nama_area || '-'}</div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                            ${item.jenis_area || '-'}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">${item.jarak || '-'}</td>
                    <td class="px-6 py-4 text-center">
                        ${item.gps ? `
                        <a class="text-primary hover:text-primary/70 inline-flex items-center gap-1" href="https://www.google.com/maps?q=${item.gps}" target="_blank">
                            <span class="material-symbols-outlined text-xl">map</span>
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
                        <button class="p-1.5 text-slate-400 hover:text-primary transition-colors btn-edit-nearme"
                            data-id="${item.id_area}"
                            data-nama-area="${item.nama_area || ''}"
                            data-id-cabang="${item.id_cabang || ''}"
                            data-jenis-area="${item.jenis_area || ''}"
                            data-alamat="${item.alamat || ''}"
                            data-gps="${item.gps || ''}"
                            data-jarak="${item.jarak || ''}"
                            data-aktif="${item.aktif || '1'}"
                            data-foto="${item.foto || ''}"
                            data-created-date="${item.created_date || ''}"
                            title="Edit">
                            <span class="material-symbols-outlined text-xl">edit_square</span>
                        </button>
                        <button class="p-1.5 text-slate-400 hover:text-red-500 transition-colors btn-delete-nearme"
                            data-id="${item.id_area}"
                            data-nama="${item.nama_area || ''}"
                            title="Hapus">
                            <span class="material-symbols-outlined text-xl">delete</span>
                        </button>
                    </td>
                </tr>
            `).join('');

            // Add delete functionality
            document.querySelectorAll('.btn-delete-nearme').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.dataset.id;
                    const nama = this.dataset.nama;
                    if (confirm(`Apakah Anda yakin ingin menghapus near area "${nama}"?`)) {
                        deleteNearMe(id);
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
                        <div class="w-16 h-16 rounded-lg overflow-hidden border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800 flex-shrink-0 cursor-pointer hover:ring-2 hover:ring-primary transition-all" onclick="openImagePreview('${getImageUrl(item.foto)}', '${item.nama_area || 'Near Area'}')">
                            <img class="w-full h-full object-cover" src="${getImageUrl(item.foto)}" alt="${item.nama_area || 'Near Area'}" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="font-semibold text-slate-900 dark:text-white">${item.nama_area || '-'}</div>
                            <div class="text-xs text-slate-500">${item.jenis_area || '-'}</div>
                        </div>
                        <div class="flex gap-1">
                            <button class="p-2 text-slate-400 hover:text-primary transition-colors btn-edit-nearme-mobile"
                                data-id="${item.id_area}"
                                data-nama-area="${item.nama_area || ''}"
                                data-id-cabang="${item.id_cabang || ''}"
                                data-jenis-area="${item.jenis_area || ''}"
                                data-alamat="${item.alamat || ''}"
                                data-gps="${item.gps || ''}"
                                data-jarak="${item.jarak || ''}"
                                data-aktif="${item.aktif || '1'}"
                                data-foto="${item.foto || ''}"
                                data-created-date="${item.created_date || ''}"
                                title="Edit">
                                <span class="material-symbols-outlined text-xl">edit_square</span>
                            </button>
                            <button class="p-2 text-slate-400 hover:text-red-500 transition-colors btn-delete-nearme-mobile"
                                data-id="${item.id_area}"
                                data-nama="${item.nama_area || ''}"
                                title="Hapus">
                                <span class="material-symbols-outlined text-xl">delete</span>
                            </button>
                        </div>
                    </div>
                    <div class="text-sm text-slate-600 dark:text-slate-400 space-y-1">
                        ${item.jarak ? `
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm">straighten</span>
                            <span>${item.jarak}</span>
                        </div>` : ''}
                        ${item.gps ? `
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm">map</span>
                            <a class="text-primary hover:text-primary/70" href="https://www.google.com/maps?q=${item.gps}" target="_blank">Lihat di Peta</a>
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
            document.querySelectorAll('.btn-delete-nearme-mobile').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.dataset.id;
                    const nama = this.dataset.nama;
                    if (confirm(`Apakah Anda yakin ingin menghapus near area "${nama}"?`)) {
                        deleteNearMe(id);
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

        // Delete nearme function
        function deleteNearMe(id) {
            fetch('../../api/nearme/delete.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `id_area=${encodeURIComponent(id)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Data near area berhasil dihapus!', 'success');
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

        // Image Preview Modal Functions
        function openImagePreview(imageSrc, imageName) {
            const modal = document.getElementById('image-preview-modal');
            const previewImage = document.getElementById('preview-image-full');
            
            previewImage.src = imageSrc;
            previewImage.alt = imageName;
            modal.classList.remove('hidden');
        }

        function closeImagePreview() {
            const modal = document.getElementById('image-preview-modal');
            modal.classList.add('hidden');
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
