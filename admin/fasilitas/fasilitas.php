<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['id_users'])) {
    header('Location: ../login.php');
    exit;
}

// Fetch data from API
$apiUrl = 'http://localhost/botanic/api/fasilitas/list.php';
$apiResponse = file_get_contents($apiUrl);
$apiData = json_decode($apiResponse, true);

$fasilitasList = [];
$totalCount = 0;
$message = '';

if ($apiData && $apiData['success']) {
    $fasilitasList = $apiData['data'];
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
    <title>Admin Panel - Manajemen Fasilitas</title>
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
                    <h2 class="text-lg font-semibold text-slate-800 dark:text-white">Manajemen Fasilitas</h2>
                </div>
                <div class="flex items-center gap-4">
                    <div class="relative hidden sm:block">
                        <span
                            class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xl leading-none">search</span>
                        <input id="searchInput"
                            class="pl-10 pr-4 py-2 bg-slate-100 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary text-sm w-64"
                            placeholder="Cari fasilitas..." type="text" onkeyup="filterData()" />
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
                            Fasilitas</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Kelola informasi fasilitas di setiap
                            cabang hotel.</p>
                    </div>
                    <button id="btnTambahFasilitas"
                        class="flex items-center gap-2 px-5 py-2.5 bg-primary text-white font-bold rounded-lg hover:bg-primary/90 transition-all shadow-sm w-full sm:w-auto justify-center">
                        <span class="material-symbols-outlined">add</span>
                        <span>Tambah Fasilitas</span>
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
                                        Nama Fasilitas</th>
                                    <th
                                        class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                        Deskripsi</th>
                                    <th
                                        class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-center">
                                        Status</th>
                                    <th
                                        class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                        Harga</th>
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
                        <span class="material-symbols-outlined text-6xl text-slate-300 dark:text-slate-600 mb-4">pool</span>
                        <p class="text-slate-500 dark:text-slate-400">Tidak ada data fasilitas</p>
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

    <!-- Add Fasilitas Modal -->
    <div class="fixed inset-0 z-50 flex items-center justify-center hidden" id="add-fasilitas-modal">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        <!-- Modal Content -->
        <div
            class="relative bg-white dark:bg-background-dark w-full max-w-lg mx-4 rounded-xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
            <!-- Header (Fixed) -->
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between flex-shrink-0">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Tambah Fasilitas Baru</h3>
                <button class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 btn-close-modal">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <!-- Scrollable Content -->
            <div class="overflow-y-auto px-6 py-4 space-y-4 scrollbar-thin scrollbar-thumb-slate-300 dark:scrollbar-thumb-slate-600">
                <form id="formTambahFasilitas" class="space-y-4" enctype="multipart/form-data">
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
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Nama
                                Fasilitas <span class="text-red-500">*</span></label>
                            <input id="nama_fasilitas" name="nama_fasilitas"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                                placeholder="Contoh: Kolam Renang" type="text" required />
                        </div>
                        <div class="col-span-2">
                            <label
                                class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Deskripsi</label>
                            <textarea id="deskripsi" name="deskripsi"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm h-20"
                                placeholder="Deskripsi fasilitas..."></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Status
                                Aktif</label>
                            <select id="aktif" name="aktif"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm">
                                <option value="1">Aktif</option>
                                <option value="0">Tidak Aktif</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Status
                                Free</label>
                            <select id="status_free" name="status_free"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm">
                                <option value="0">Berbayar</option>
                                <option value="1">Gratis</option>
                            </select>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Range
                                Harga</label>
                            <input id="range_harga" name="range_harga"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                                placeholder="50000" type="number" step="0.01" />
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Foto
                                1</label>
                            <div
                                class="mt-1 flex items-center gap-4 px-4 py-4 border-2 border-slate-300 dark:border-slate-700 border-dashed rounded-lg">
                                <!-- Preview Image -->
                                <div id="gambar1Preview" class="hidden w-20 h-20 rounded-lg overflow-hidden border border-slate-200 dark:border-slate-600 flex-shrink-0 bg-slate-100 dark:bg-slate-800">
                                    <img id="previewImg1" src="" alt="Preview" class="w-full h-full object-cover" />
                                </div>
                                <div class="space-y-1 text-center flex-1">
                                    <span class="material-symbols-outlined text-slate-400 text-3xl">image</span>
                                    <div class="flex text-sm text-slate-600 dark:text-slate-400 justify-center">
                                        <label
                                            class="relative cursor-pointer bg-white dark:bg-background-dark rounded-md font-medium text-primary hover:text-primary/80 focus-within:outline-none"
                                            for="gambar1">
                                            <span>Upload file</span>
                                            <input class="sr-only" id="gambar1" name="gambar1" type="file" accept="image/*" />
                                        </label>
                                    </div>
                                    <p class="text-xs text-slate-500">PNG, JPG max 1MB</p>
                                    <p id="fileName1" class="text-xs text-slate-400 truncate max-w-[200px]"></p>
                                </div>
                            </div>
                            <p id="gambar1Error" class="text-xs text-red-500 mt-1 hidden"></p>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Foto
                                2</label>
                            <div
                                class="mt-1 flex items-center gap-4 px-4 py-4 border-2 border-slate-300 dark:border-slate-700 border-dashed rounded-lg">
                                <!-- Preview Image -->
                                <div id="gambar2Preview" class="hidden w-20 h-20 rounded-lg overflow-hidden border border-slate-200 dark:border-slate-600 flex-shrink-0 bg-slate-100 dark:bg-slate-800">
                                    <img id="previewImg2" src="" alt="Preview" class="w-full h-full object-cover" />
                                </div>
                                <div class="space-y-1 text-center flex-1">
                                    <span class="material-symbols-outlined text-slate-400 text-3xl">image</span>
                                    <div class="flex text-sm text-slate-600 dark:text-slate-400 justify-center">
                                        <label
                                            class="relative cursor-pointer bg-white dark:bg-background-dark rounded-md font-medium text-primary hover:text-primary/80 focus-within:outline-none"
                                            for="gambar2">
                                            <span>Upload file</span>
                                            <input class="sr-only" id="gambar2" name="gambar2" type="file" accept="image/*" />
                                        </label>
                                    </div>
                                    <p class="text-xs text-slate-500">PNG, JPG max 1MB</p>
                                    <p id="fileName2" class="text-xs text-slate-400 truncate max-w-[200px]"></p>
                                </div>
                            </div>
                            <p id="gambar2Error" class="text-xs text-red-500 mt-1 hidden"></p>
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

    <!-- Edit Fasilitas Modal -->
    <div class="fixed inset-0 z-50 flex items-center justify-center hidden" id="edit-fasilitas-modal">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        <!-- Modal Content -->
        <div
            class="relative bg-white dark:bg-background-dark w-full max-w-lg mx-4 rounded-xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
            <!-- Header (Fixed) -->
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between flex-shrink-0">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Edit Fasilitas</h3>
                <button class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 btn-close-edit-modal">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <!-- Scrollable Content -->
            <div class="overflow-y-auto px-6 py-4 space-y-4 scrollbar-thin scrollbar-thumb-slate-300 dark:scrollbar-thumb-slate-600">
                <form id="formEditFasilitas" class="space-y-4" enctype="multipart/form-data">
                    <input type="hidden" id="edit_id_fasilitas" name="id_fasilitas" />
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
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Nama
                                Fasilitas <span class="text-red-500">*</span></label>
                            <input id="edit_nama_fasilitas" name="nama_fasilitas"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                                placeholder="Contoh: Kolam Renang" type="text" required />
                        </div>
                        <div class="col-span-2">
                            <label
                                class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Deskripsi</label>
                            <textarea id="edit_deskripsi" name="deskripsi"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm h-20"
                                placeholder="Deskripsi fasilitas..."></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Status
                                Aktif</label>
                            <select id="edit_aktif" name="aktif"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm">
                                <option value="1">Aktif</option>
                                <option value="0">Tidak Aktif</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Status
                                Free</label>
                            <select id="edit_status_free" name="status_free"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm">
                                <option value="0">Berbayar</option>
                                <option value="1">Gratis</option>
                            </select>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Range
                                Harga</label>
                            <input id="edit_range_harga" name="range_harga"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                                placeholder="50000" type="number" step="0.01" />
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Foto
                                1</label>
                            <div
                                class="mt-1 flex items-center gap-4 px-4 py-4 border-2 border-slate-300 dark:border-slate-700 border-dashed rounded-lg">
                                <!-- Preview Image -->
                                <div id="edit_gambar1_preview_container" class="hidden w-20 h-20 rounded-lg overflow-hidden border border-slate-200 dark:border-slate-600 flex-shrink-0 bg-slate-100 dark:bg-slate-800">
                                    <img id="edit_preview_img1" src="" alt="Preview" class="w-full h-full object-cover" />
                                </div>
                                <div class="space-y-1 text-center flex-1">
                                    <span class="material-symbols-outlined text-slate-400 text-3xl">image</span>
                                    <div class="flex text-sm text-slate-600 dark:text-slate-400 justify-center">
                                        <label
                                            class="relative cursor-pointer bg-white dark:bg-background-dark rounded-md font-medium text-primary hover:text-primary/80 focus-within:outline-none"
                                            for="edit_gambar1">
                                            <span>Upload file</span>
                                            <input class="sr-only" id="edit_gambar1" name="gambar1" type="file" accept="image/*" />
                                        </label>
                                    </div>
                                    <p class="text-xs text-slate-500">PNG, JPG max 1MB</p>
                                    <p id="edit_file_name1" class="text-xs text-slate-400 truncate max-w-[200px]"></p>
                                </div>
                            </div>
                            <p id="edit_gambar1_error" class="text-xs text-red-500 mt-1 hidden"></p>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Foto
                                2</label>
                            <div
                                class="mt-1 flex items-center gap-4 px-4 py-4 border-2 border-slate-300 dark:border-slate-700 border-dashed rounded-lg">
                                <!-- Preview Image -->
                                <div id="edit_gambar2_preview_container" class="hidden w-20 h-20 rounded-lg overflow-hidden border border-slate-200 dark:border-slate-600 flex-shrink-0 bg-slate-100 dark:bg-slate-800">
                                    <img id="edit_preview_img2" src="" alt="Preview" class="w-full h-full object-cover" />
                                </div>
                                <div class="space-y-1 text-center flex-1">
                                    <span class="material-symbols-outlined text-slate-400 text-3xl">image</span>
                                    <div class="flex text-sm text-slate-600 dark:text-slate-400 justify-center">
                                        <label
                                            class="relative cursor-pointer bg-white dark:bg-background-dark rounded-md font-medium text-primary hover:text-primary/80 focus-within:outline-none"
                                            for="edit_gambar2">
                                            <span>Upload file</span>
                                            <input class="sr-only" id="edit_gambar2" name="gambar2" type="file" accept="image/*" />
                                        </label>
                                    </div>
                                    <p class="text-xs text-slate-500">PNG, JPG max 1MB</p>
                                    <p id="edit_file_name2" class="text-xs text-slate-400 truncate max-w-[200px]"></p>
                                </div>
                            </div>
                            <p id="edit_gambar2_error" class="text-xs text-red-500 mt-1 hidden"></p>
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

        function confirmSave() {
            return new Promise((resolve) => {
                const confirmed = confirm('Apakah Anda yakin ingin menyimpan data fasilitas ini?');
                resolve(confirmed);
            });
        }

        // Modal functions
        const modal = document.getElementById('add-fasilitas-modal');
        const btnTambah = document.getElementById('btnTambahFasilitas');
        const btnClose = document.querySelectorAll('.btn-close-modal');
        const btnSimpan = document.getElementById('btnSimpan');
        const formTambah = document.getElementById('formTambahFasilitas');

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
                const requiredFields = ['id_cabang', 'nama_fasilitas'];
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
                fetch('../../api/fasilitas/new.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Data fasilitas berhasil disimpan!', 'success');
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
        document.querySelectorAll('input, textarea, select').forEach(input => {
            input.addEventListener('input', function() {
                this.classList.remove('border-red-500');
            });
        });

        // Image Preview and Validation for gambar1
        const gambar1Input = document.getElementById('gambar1');
        const gambar1Preview = document.getElementById('gambar1Preview');
        const previewImg1 = document.getElementById('previewImg1');
        const fileName1 = document.getElementById('fileName1');
        const gambar1Error = document.getElementById('gambar1Error');

        if (gambar1Input) {
            gambar1Input.addEventListener('change', function(e) {
                const file = e.target.files[0];
                handleImagePreview(file, gambar1Preview, previewImg1, fileName1, gambar1Error);
            });
        }

        // Image Preview and Validation for gambar2
        const gambar2Input = document.getElementById('gambar2');
        const gambar2Preview = document.getElementById('gambar2Preview');
        const previewImg2 = document.getElementById('previewImg2');
        const fileName2 = document.getElementById('fileName2');
        const gambar2Error = document.getElementById('gambar2Error');

        if (gambar2Input) {
            gambar2Input.addEventListener('change', function(e) {
                const file = e.target.files[0];
                handleImagePreview(file, gambar2Preview, previewImg2, fileName2, gambar2Error);
            });
        }

        // Common image preview handler
        function handleImagePreview(file, previewContainer, previewImg, fileName, errorElement) {
            // Reset error
            errorElement.classList.add('hidden');

            if (!file) {
                previewContainer.classList.add('hidden');
                fileName.textContent = '';
                return;
            }

            // Validate file type
            const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            if (!validTypes.includes(file.type)) {
                errorElement.textContent = 'Tipe file tidak valid. Gunakan JPG, PNG, GIF, atau WEBP.';
                errorElement.classList.remove('hidden');
                previewContainer.classList.add('hidden');
                fileName.textContent = '';
                return;
            }

            // Validate file size (1MB)
            if (file.size > maxSize) {
                errorElement.textContent = 'Ukuran file terlalu besar. Maksimal 1MB.';
                errorElement.classList.remove('hidden');
                previewContainer.classList.add('hidden');
                fileName.textContent = '';
                return;
            }

            // Show preview
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                previewContainer.classList.remove('hidden');
                fileName.textContent = file.name;
            };
            reader.readAsDataURL(file);
        }

        // Edit Modal Functions
        const editModal = document.getElementById('edit-fasilitas-modal');
        const btnCloseEdit = document.querySelectorAll('.btn-close-edit-modal');
        const btnUpdate = document.getElementById('btnUpdate');
        const formEdit = document.getElementById('formEditFasilitas');

        function openEditModal(idFasilitas, idCabang, namaFasilitas, deskripsi, aktif, statusFree, rangeHarga, gambar1, gambar2) {
            document.getElementById('edit_id_fasilitas').value = idFasilitas;
            document.getElementById('edit_id_cabang').value = idCabang;
            document.getElementById('edit_nama_fasilitas').value = namaFasilitas;
            document.getElementById('edit_deskripsi').value = deskripsi;
            document.getElementById('edit_aktif').value = aktif;
            document.getElementById('edit_status_free').value = statusFree;
            document.getElementById('edit_range_harga').value = rangeHarga;

            // Show existing gambar1 preview
            const editGambar1Preview = document.getElementById('edit_gambar1_preview_container');
            const editPreviewImg1 = document.getElementById('edit_preview_img1');
            const editFileName1 = document.getElementById('edit_file_name1');
            if (gambar1 && gambar1 !== '') {
                editPreviewImg1.src = getImageUrl(gambar1);
                editGambar1Preview.classList.remove('hidden');
                editFileName1.textContent = gambar1;
            } else {
                editGambar1Preview.classList.add('hidden');
                editFileName1.textContent = '';
            }

            // Show existing gambar2 preview
            const editGambar2Preview = document.getElementById('edit_gambar2_preview_container');
            const editPreviewImg2 = document.getElementById('edit_preview_img2');
            const editFileName2 = document.getElementById('edit_file_name2');
            if (gambar2 && gambar2 !== '') {
                editPreviewImg2.src = getImageUrl(gambar2);
                editGambar2Preview.classList.remove('hidden');
                editFileName2.textContent = gambar2;
            } else {
                editGambar2Preview.classList.add('hidden');
                editFileName2.textContent = '';
            }

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
            if (e.target.closest('.btn-edit-fasilitas')) {
                const btn = e.target.closest('.btn-edit-fasilitas');
                openEditModal(
                    btn.dataset.idfasilitas,
                    btn.dataset.idcabang,
                    btn.dataset.namafasilitas,
                    btn.dataset.deskripsi,
                    btn.dataset.aktif,
                    btn.dataset.statusfree,
                    btn.dataset.rangeharga,
                    btn.dataset.gambar1,
                    btn.dataset.gambar2
                );
            }
            // Handle edit button clicks (mobile)
            if (e.target.closest('.btn-edit-fasilitas-mobile')) {
                const btn = e.target.closest('.btn-edit-fasilitas-mobile');
                openEditModal(
                    btn.dataset.idfasilitas,
                    btn.dataset.idcabang,
                    btn.dataset.namafasilitas,
                    btn.dataset.deskripsi,
                    btn.dataset.aktif,
                    btn.dataset.statusfree,
                    btn.dataset.rangeharga,
                    btn.dataset.gambar1,
                    btn.dataset.gambar2
                );
            }
        });

        // Handle Update Button
        if (btnUpdate) {
            btnUpdate.addEventListener('click', async function() {
                // Validate form
                const requiredFields = ['edit_id_cabang', 'edit_nama_fasilitas'];
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
                const confirmed = confirm('Apakah Anda yakin ingin mengupdate data fasilitas ini?');
                if (!confirmed) return;

                // Show loading state
                btnUpdate.disabled = true;
                btnUpdate.innerHTML = '<span class="material-symbols-outlined spinner">sync</span><span>Updating...</span>';

                // Prepare FormData
                const formData = new FormData(formEdit);

                // Send to API
                fetch('../../api/fasilitas/update.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('Data fasilitas berhasil diupdate!', 'success');
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

        // Image Preview and Validation for Edit Form - gambar1
        const editGambar1Input = document.getElementById('edit_gambar1');
        const editGambar1Preview = document.getElementById('edit_gambar1_preview_container');
        const editPreviewImg1 = document.getElementById('edit_preview_img1');
        const editFileName1 = document.getElementById('edit_file_name1');
        const editGambar1Error = document.getElementById('edit_gambar1_error');

        if (editGambar1Input) {
            editGambar1Input.addEventListener('change', function(e) {
                const file = e.target.files[0];
                handleImagePreview(file, editGambar1Preview, editPreviewImg1, editFileName1, editGambar1Error);
            });
        }

        // Image Preview and Validation for Edit Form - gambar2
        const editGambar2Input = document.getElementById('edit_gambar2');
        const editGambar2Preview = document.getElementById('edit_gambar2_preview_container');
        const editPreviewImg2 = document.getElementById('edit_preview_img2');
        const editFileName2 = document.getElementById('edit_file_name2');
        const editGambar2Error = document.getElementById('edit_gambar2_error');

        if (editGambar2Input) {
            editGambar2Input.addEventListener('change', function(e) {
                const file = e.target.files[0];
                handleImagePreview(file, editGambar2Preview, editPreviewImg2, editFileName2, editGambar2Error);
            });
        }

        // Data from PHP
        const allData = <?php echo json_encode($fasilitasList); ?>;
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
                    (item.nama_fasilitas && item.nama_fasilitas.toLowerCase().includes(searchTerm)) ||
                    (item.deskripsi && item.deskripsi.toLowerCase().includes(searchTerm))
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

        function formatRupiah(angka) {
            if (!angka && angka !== 0) return '-';
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka);
        }

        function getImageUrl(gambar) {
            if (!gambar || gambar === '') {
                return 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 40 40"%3E%3Crect fill="%23e2e8f0" width="40" height="40"/%3E%3Ctext fill="%2394a3b8" font-family="sans-serif" font-size="20" text-anchor="middle" x="20" y="25"%3E🏊%3C/text%3E%3C/svg%3E';
            }
            return '../../images/' + gambar;
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
                            <img class="w-full h-full object-cover" src="${getImageUrl(item.gambar1)}" alt="${item.nama_fasilitas || 'Fasilitas'}" />
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="font-semibold text-slate-900 dark:text-white">${item.nama_cabang || '-'}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="font-semibold text-slate-900 dark:text-white">${item.nama_fasilitas || '-'}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-slate-600 dark:text-slate-400 max-w-xs truncate">${item.deskripsi || '-'}</div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        ${item.aktif == 1 ? `
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                            <span class="material-symbols-outlined text-xs">check_circle</span>
                            Aktif
                        </span>` : `
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                            <span class="material-symbols-outlined text-xs">cancel</span>
                            Tidak Aktif
                        </span>`}
                        <div class="mt-1">
                            ${item.status_free == 1 ? `
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                Gratis
                            </span>` : `
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200">
                                Berbayar
                            </span>`}
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">${formatRupiah(item.range_harga)}</td>
                    <td class="px-6 py-4 text-right space-x-2">
                        <button class="p-1.5 text-slate-400 hover:text-primary transition-colors btn-edit-fasilitas"
                            data-idfasilitas="${item.id_fasilitas}"
                            data-idcabang="${item.id_cabang}"
                            data-namafasilitas="${item.nama_fasilitas || ''}"
                            data-deskripsi="${item.deskripsi || ''}"
                            data-aktif="${item.aktif}"
                            data-statusfree="${item.status_free}"
                            data-rangeharga="${item.range_harga || ''}"
                            data-gambar1="${item.gambar1 || ''}"
                            data-gambar2="${item.gambar2 || ''}"
                            title="Edit">
                            <span class="material-symbols-outlined text-xl">edit_square</span>
                        </button>
                        <button class="p-1.5 text-slate-400 hover:text-red-500 transition-colors btn-delete-fasilitas"
                            data-idfasilitas="${item.id_fasilitas}"
                            data-namafasilitas="${item.nama_fasilitas || ''}"
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
                    <div class="flex items-start gap-3">
                        <div class="w-16 h-16 rounded-lg overflow-hidden border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800 flex-shrink-0">
                            <img class="w-full h-full object-cover" src="${getImageUrl(item.gambar1)}" alt="${item.nama_fasilitas || 'Fasilitas'}" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="font-semibold text-slate-900 dark:text-white">${item.nama_fasilitas || '-'}</div>
                            <div class="text-xs text-slate-500">${item.nama_cabang || '-'}</div>
                        </div>
                        <div class="flex gap-1">
                            <button class="p-2 text-slate-400 hover:text-primary transition-colors btn-edit-fasilitas-mobile"
                                data-idfasilitas="${item.id_fasilitas}"
                                data-idcabang="${item.id_cabang}"
                                data-namafasilitas="${item.nama_fasilitas || ''}"
                                data-deskripsi="${item.deskripsi || ''}"
                                data-aktif="${item.aktif}"
                                data-statusfree="${item.status_free}"
                                data-rangeharga="${item.range_harga || ''}"
                                data-gambar1="${item.gambar1 || ''}"
                                data-gambar2="${item.gambar2 || ''}"
                                title="Edit">
                                <span class="material-symbols-outlined text-xl">edit_square</span>
                            </button>
                            <button class="p-2 text-slate-400 hover:text-red-500 transition-colors btn-delete-fasilitas-mobile"
                                data-idfasilitas="${item.id_fasilitas}"
                                data-namafasilitas="${item.nama_fasilitas || ''}"
                                title="Hapus">
                                <span class="material-symbols-outlined text-xl">delete</span>
                            </button>
                        </div>
                    </div>
                    ${item.deskripsi ? `
                    <div class="text-sm text-slate-600 dark:text-slate-400">
                        <div class="flex items-start gap-2">
                            <span class="material-symbols-outlined text-sm mt-0.5">description</span>
                            <span>${item.deskripsi}</span>
                        </div>
                    </div>` : ''}
                    <div class="flex items-center gap-2">
                        ${item.aktif == 1 ? `
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                            <span class="material-symbols-outlined text-xs">check_circle</span>
                            Aktif
                        </span>` : `
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                            <span class="material-symbols-outlined text-xs">cancel</span>
                            Tidak Aktif
                        </span>`}
                        ${item.status_free == 1 ? `
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                            Gratis
                        </span>` : `
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200">
                            Berbayar
                        </span>`}
                    </div>
                    <div class="text-sm text-slate-600 dark:text-slate-400">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm">payments</span>
                            <span>${formatRupiah(item.range_harga)}</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 text-xs text-slate-500">
                        <span class="material-symbols-outlined text-sm">calendar_today</span>
                        <span>${formatDate(item.created_at)}</span>
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

        // Handle Delete Button
        document.addEventListener('click', function(e) {
            const deleteBtn = e.target.closest('.btn-delete-fasilitas, .btn-delete-fasilitas-mobile');
            if (deleteBtn) {
                const id = deleteBtn.dataset.idfasilitas;
                const namaFasilitas = deleteBtn.dataset.namafasilitas;

                if (confirm(`Apakah Anda yakin ingin menghapus fasilitas "${namaFasilitas}"?`)) {
                    fetch('../../api/fasilitas/delete.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ id_fasilitas: parseInt(id) })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showToast('Data fasilitas berhasil dihapus!', 'success');
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
