<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['id_users'])) {
    header('Location: ../login.php');
    exit;
}

// Fetch data from API
$apiUrl = 'http://localhost/botanic/api/hk/list.php';
$apiResponse = file_get_contents($apiUrl);
$apiData = json_decode($apiResponse, true);

$hkList = [];
$totalCount = 0;
$message = '';

if ($apiData && $apiData['success']) {
    $hkList = $apiData['data'];
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
    <title>Admin Panel - Manajemen Housekeeping</title>
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
                    <h2 class="text-lg font-semibold text-slate-800 dark:text-white">Manajemen Housekeeping</h2>
                </div>
                <div class="flex items-center gap-4">
                    <div class="relative hidden sm:block">
                        <span
                            class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xl leading-none">search</span>
                        <input id="searchInput"
                            class="pl-10 pr-4 py-2 bg-slate-100 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary text-sm w-64"
                            placeholder="Cari housekeeping..." type="text" onkeyup="filterData()" />
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
                            Housekeeping</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Kelola staff housekeeping di setiap
                            cabang hotel.</p>
                    </div>
                    <button id="btnTambahHK"
                        class="flex items-center gap-2 px-5 py-2.5 bg-primary text-white font-bold rounded-lg hover:bg-primary/90 transition-all shadow-sm w-full sm:w-auto justify-center">
                        <span class="material-symbols-outlined">add</span>
                        <span>Tambah Housekeeping</span>
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
                                        Kode HK</th>
                                    <th
                                        class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                        Jabatan</th>
                                    <th
                                        class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                        Nama Lengkap</th>
                                    <th
                                        class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-center">
                                        L/P</th>
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
                       
                        <p class="text-slate-500 dark:text-slate-400">Pilih cabang untuk menampilkan data housekeeping</p>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Add Housekeeping Modal -->
    <div class="fixed inset-0 z-50 flex items-center justify-center hidden" id="add-hk-modal">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        <!-- Modal Content -->
        <div
            class="relative bg-white dark:bg-background-dark w-full max-w-lg mx-4 rounded-xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
            <!-- Header (Fixed) -->
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between flex-shrink-0">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Tambah Housekeeping Baru</h3>
                <button class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 btn-close-modal">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <!-- Scrollable Content -->
            <div class="overflow-y-auto px-6 py-4 space-y-4 scrollbar-thin scrollbar-thumb-slate-300 dark:scrollbar-thumb-slate-600">
                <form id="formTambahHK" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Kode
                                Housekeeping <span class="text-red-500">*</span></label>
                            <input id="kode_hk" name="kode_hk"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                                placeholder="HK-001" type="text" required />
                        </div>
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
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Jabatan
                                <span class="text-red-500">*</span></label>
                            <input id="jabatan" name="jabatan"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                                placeholder="Housekeeping Staff" type="text" required />
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Nama
                                Lengkap <span class="text-red-500">*</span></label>
                            <input id="nama_lengkap" name="nama_lengkap"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                                placeholder="Nama lengkap" type="text" required />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Jenis
                                Kelamin <span class="text-red-500">*</span></label>
                            <select id="jenis_kelamin" name="jenis_kelamin"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                                required>
                                <option value="">-- Pilih --</option>
                                <option value="0">Perempuan</option>
                                <option value="1">Laki-laki</option>
                            </select>
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
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">WhatsApp
                                <span class="text-red-500">*</span></label>
                            <input id="wa" name="wa"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                                placeholder="628123456789" type="text" required />
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

    <!-- Edit Housekeeping Modal -->
    <div class="fixed inset-0 z-50 flex items-center justify-center hidden" id="edit-hk-modal">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        <!-- Modal Content -->
        <div
            class="relative bg-white dark:bg-background-dark w-full max-w-lg mx-4 rounded-xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
            <!-- Header (Fixed) -->
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between flex-shrink-0">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Edit Housekeeping</h3>
                <button class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 btn-close-edit-modal">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <!-- Scrollable Content -->
            <div class="overflow-y-auto px-6 py-4 space-y-4 scrollbar-thin scrollbar-thumb-slate-300 dark:scrollbar-thumb-slate-600">
                <form id="formEditHK" class="space-y-4">
                    <input type="hidden" id="edit_id_hk" name="id_hk" />
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Kode
                                Housekeeping <span class="text-red-500">*</span></label>
                            <input id="edit_kode_hk" name="kode_hk"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                                type="text" required />
                        </div>
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
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Jabatan
                                <span class="text-red-500">*</span></label>
                            <input id="edit_jabatan" name="jabatan"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                                type="text" required />
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Nama
                                Lengkap <span class="text-red-500">*</span></label>
                            <input id="edit_nama_lengkap" name="nama_lengkap"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                                type="text" required />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Jenis
                                Kelamin <span class="text-red-500">*</span></label>
                            <select id="edit_jenis_kelamin" name="jenis_kelamin"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                                required>
                                <option value="">-- Pilih --</option>
                                <option value="0">Perempuan</option>
                                <option value="1">Laki-laki</option>
                            </select>
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
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">WhatsApp
                                <span class="text-red-500">*</span></label>
                            <input id="edit_wa" name="wa"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                                type="text" required />
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
        // Toggle sidebar for mobile
        function toggleSidebar() {
            const sidebar = document.querySelector('aside');
            sidebar.classList.toggle('hidden');
            sidebar.classList.toggle('absolute');
            sidebar.classList.toggle('z-50');
            sidebar.classList.toggle('h-full');
        }

        // Toggle search for mobile
        function toggleSearch() {
            const searchInput = document.getElementById('searchInput');
            searchInput.classList.toggle('hidden');
            searchInput.focus();
        }

        // Filter data
        function filterData() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toLowerCase();
            const tableBody = document.getElementById('tableBody');
            const mobileView = document.getElementById('mobileView');
            
            // Filter desktop table
            const tr = tableBody.getElementsByTagName('tr');
            for (let i = 0; i < tr.length; i++) {
                const tdKode = tr[i].getElementsByTagName('td')[0];
                const tdNama = tr[i].getElementsByTagName('td')[2];
                const tdJabatan = tr[i].getElementsByTagName('td')[1];
                if (tdKode || tdNama || tdJabatan) {
                    const txtValueKode = tdKode.textContent || tdKode.innerText;
                    const txtValueNama = tdNama.textContent || tdNama.innerText;
                    const txtValueJabatan = tdJabatan.textContent || tdJabatan.innerText;
                    if (txtValueKode.toLowerCase().indexOf(filter) > -1 || 
                        txtValueNama.toLowerCase().indexOf(filter) > -1 ||
                        txtValueJabatan.toLowerCase().indexOf(filter) > -1) {
                        tr[i].style.display = '';
                    } else {
                        tr[i].style.display = 'none';
                    }
                }
            }

            // Filter mobile cards
            const cards = mobileView.querySelectorAll('.mobile-card');
            cards.forEach(card => {
                const text = card.textContent.toLowerCase();
                card.style.display = text.indexOf(filter) > -1 ? '' : 'none';
            });
        }

        // Load data into table
        let filteredData = []; // Start with empty data
        const allData = <?php echo json_encode($hkList); ?>; // Store all data for filtering

        function filterByCabang() {
            const filterCabang = document.getElementById('filterCabang').value;
            
            if (filterCabang === '') {
                filteredData = []; // Empty if no branch selected
            } else {
                filteredData = allData.filter(hk => hk.id_cabang == filterCabang);
            }
            
            loadData();
        }

        function loadData() {
            const tableBody = document.getElementById('tableBody');
            const mobileView = document.getElementById('mobileView');
            const noData = document.getElementById('noData');

            // Show no data state if filteredData is empty
            if (!filteredData || filteredData.length === 0) {
                noData.classList.remove('hidden');
                tableBody.innerHTML = '';
                mobileView.innerHTML = '';
                return;
            }

            noData.classList.add('hidden');
            tableBody.innerHTML = '';
            mobileView.innerHTML = '';

            filteredData.forEach(hk => {
                // Desktop row
                const row = document.createElement('tr');
                row.className = 'hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors';
                row.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="text-sm font-bold text-slate-900 dark:text-slate-100">${escapeHtml(hk.kode_hk)}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="text-sm text-slate-700 dark:text-slate-300">${escapeHtml(hk.jabatan)}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="text-sm font-semibold text-slate-900 dark:text-slate-100">${escapeHtml(hk.nama_lengkap)}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold ${hk.jenis_kelamin == 1 ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400' : 'bg-pink-100 text-pink-800 dark:bg-pink-900/30 dark:text-pink-400'}">
                            ${hk.jenis_kelamin == 1 ? 'L' : 'P'}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="text-sm text-slate-700 dark:text-slate-300">${escapeHtml(hk.wa)}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <button onclick="toggleAktif(${hk.id_hk}, ${hk.aktif})"
                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold cursor-pointer transition-colors ${hk.aktif == 1 ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400 hover:bg-green-200 dark:hover:bg-green-900/50' : 'bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-600'}">
                            ${hk.aktif == 1 ? 'Active' : 'Inactive'}
                        </button>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right">
                        <div class="flex items-center justify-end gap-2">
                            <button onclick='openEditModal(${JSON.stringify(hk)})'
                                class="p-2 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-900/20 text-blue-600 dark:text-blue-400 transition-colors" title="Edit">
                                <span class="material-symbols-outlined text-lg">edit</span>
                            </button>
                            <button onclick="deleteData(${hk.id_hk}, '${escapeHtml(hk.nama_lengkap)}')"
                                class="p-2 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 text-red-600 dark:text-red-400 transition-colors" title="Delete">
                                <span class="material-symbols-outlined text-lg">delete</span>
                            </button>
                        </div>
                    </td>
                `;
                tableBody.appendChild(row);

                // Mobile card
                const card = document.createElement('div');
                card.className = 'mobile-card p-4 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors';
                card.innerHTML = `
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <h4 class="font-bold text-slate-900 dark:text-slate-100">${escapeHtml(hk.nama_lengkap)}</h4>
                            <p class="text-xs text-slate-500 dark:text-slate-400">${escapeHtml(hk.kode_hk)} • ${escapeHtml(hk.jabatan)}</p>
                        </div>
                        <button onclick="toggleAktif(${hk.id_hk}, ${hk.aktif})"
                            class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold cursor-pointer transition-colors ${hk.aktif == 1 ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-slate-100 text-slate-800 dark:bg-slate-700 dark:text-slate-400'}">
                            ${hk.aktif == 1 ? 'Active' : 'Inactive'}
                        </button>
                    </div>
                    <div class="space-y-2 text-sm">
                        <div class="flex items-center gap-2 text-slate-600 dark:text-slate-400">
                            <span class="material-symbols-outlined text-base">call</span>
                            <span>${escapeHtml(hk.wa)}</span>
                        </div>
                        <div class="flex items-center gap-2 text-slate-600 dark:text-slate-400">
                            <span class="material-symbols-outlined text-base">${hk.jenis_kelamin == 1 ? 'male' : 'female'}</span>
                            <span>${hk.jenis_kelamin == 1 ? 'Laki-laki' : 'Perempuan'}</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 mt-4 pt-4 border-t border-slate-200 dark:border-slate-700">
                        <button onclick='openEditModal(${JSON.stringify(hk)})'
                            class="flex-1 py-2 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 font-semibold text-sm hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-colors flex items-center justify-center gap-1">
                            <span class="material-symbols-outlined text-base">edit</span>
                            Edit
                        </button>
                        <button onclick="deleteData(${hk.id_hk}, '${escapeHtml(hk.nama_lengkap)}')"
                            class="flex-1 py-2 rounded-lg bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 font-semibold text-sm hover:bg-red-100 dark:hover:bg-red-900/30 transition-colors flex items-center justify-center gap-1">
                            <span class="material-symbols-outlined text-base">delete</span>
                            Delete
                        </button>
                    </div>
                `;
                mobileView.appendChild(card);
            });
        }

        // Escape HTML
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Add Modal functions
        const addModal = document.getElementById('add-hk-modal');
        const editModal = document.getElementById('edit-hk-modal');

        // Auto-generate kode HK when modal opens
        async function generateKodeHK(idCabang) {
            const year = new Date().getFullYear().toString().slice(-2); // Get last 2 digits of year
            const prefix = `HK${year}-`;
            
            try {
                const response = await fetch(`http://localhost/botanic/api/hk/list.php?id_cabang=${idCabang}`);
                const result = await response.json();
                
                let maxIncrement = 0;
                if (result.success && result.data.length > 0) {
                    result.data.forEach(hk => {
                        if (hk.kode_hk && hk.kode_hk.startsWith(prefix)) {
                            const increment = parseInt(hk.kode_hk.slice(-4));
                            if (!isNaN(increment) && increment > maxIncrement) {
                                maxIncrement = increment;
                            }
                        }
                    });
                }
                
                const nextIncrement = (maxIncrement + 1).toString().padStart(4, '0');
                return prefix + nextIncrement;
            } catch (error) {
                console.error('Error generating kode HK:', error);
                return prefix + '0001';
            }
        }

        // Watch for cabang change in add modal to auto-generate kode
        document.getElementById('id_cabang').addEventListener('change', async function() {
            const kodeInput = document.getElementById('kode_hk');
            if (this.value && !kodeInput.value) {
                kodeInput.value = await generateKodeHK(this.value);
            }
        });

        document.getElementById('btnTambahHK').addEventListener('click', () => {
            addModal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        });

        document.querySelectorAll('.btn-close-modal').forEach(btn => {
            btn.addEventListener('click', () => {
                addModal.classList.add('hidden');
                document.body.style.overflow = '';
                document.getElementById('formTambahHK').reset();
                document.getElementById('kode_hk').value = ''; // Reset kode to trigger auto-generation
            });
        });

        // Edit Modal functions
        document.querySelectorAll('.btn-close-edit-modal').forEach(btn => {
            btn.addEventListener('click', () => {
                editModal.classList.add('hidden');
                document.body.style.overflow = '';
                document.getElementById('formEditHK').reset();
            });
        });

        function openEditModal(data) {
            document.getElementById('edit_id_hk').value = data.id_hk;
            document.getElementById('edit_kode_hk').value = data.kode_hk;
            document.getElementById('edit_id_cabang').value = data.id_cabang;
            document.getElementById('edit_jabatan').value = data.jabatan;
            document.getElementById('edit_nama_lengkap').value = data.nama_lengkap;
            document.getElementById('edit_jenis_kelamin').value = data.jenis_kelamin;
            document.getElementById('edit_aktif').value = data.aktif;
            document.getElementById('edit_wa').value = data.wa;
            
            editModal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        // Submit Add
        document.getElementById('btnSimpan').addEventListener('click', async () => {
            const form = document.getElementById('formTambahHK');
            const formData = new FormData(form);

            try {
                const response = await fetch('http://localhost/botanic/api/hk/new.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.success) {
                    showToast('Housekeeping berhasil ditambahkan', 'success');
                    addModal.classList.add('hidden');
                    document.body.style.overflow = '';
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showToast(result.message || 'Gagal menambahkan housekeeping', 'error');
                }
            } catch (error) {
                showToast('Terjadi kesalahan: ' + error.message, 'error');
            }
        });

        // Submit Edit
        document.getElementById('btnUpdate').addEventListener('click', async () => {
            const form = document.getElementById('formEditHK');
            const formData = new FormData(form);

            try {
                const response = await fetch('http://localhost/botanic/api/hk/update.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.success) {
                    showToast('Housekeeping berhasil diupdate', 'success');
                    editModal.classList.add('hidden');
                    document.body.style.overflow = '';
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showToast(result.message || 'Gagal mengupdate housekeeping', 'error');
                }
            } catch (error) {
                showToast('Terjadi kesalahan: ' + error.message, 'error');
            }
        });

        // Delete
        async function deleteData(id, name) {
            if (!confirm(`Apakah Anda yakin ingin menghapus housekeeping "${name}"?`)) {
                return;
            }

            try {
                const response = await fetch('http://localhost/botanic/api/hk/delete.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ id_hk: id })
                });
                const result = await response.json();

                if (result.success) {
                    showToast('Housekeeping berhasil dihapus', 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showToast(result.message || 'Gagal menghapus housekeeping', 'error');
                }
            } catch (error) {
                showToast('Terjadi kesalahan: ' + error.message, 'error');
            }
        }

        // Toggle Aktif
        async function toggleAktif(id, currentStatus) {
            const newStatus = currentStatus == 1 ? 0 : 1;

            try {
                const formData = new FormData();
                formData.append('id_hk', id);
                formData.append('aktif', newStatus);

                const response = await fetch('http://localhost/botanic/api/hk/update_aktif.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.success) {
                    showToast('Status berhasil diubah', 'success');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast(result.message || 'Gagal mengubah status', 'error');
                }
            } catch (error) {
                showToast('Terjadi kesalahan: ' + error.message, 'error');
            }
        }

        // Toast notification
        function showToast(message, type = 'success') {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg transform transition-all duration-300 translate-x-full ${
                type === 'success' 
                    ? 'bg-green-500 text-white' 
                    : 'bg-red-500 text-white'
            }`;
            toast.innerHTML = `
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined">${type === 'success' ? 'check_circle' : 'error'}</span>
                    <span class="font-semibold text-sm">${message}</span>
                </div>
            `;
            
            container.appendChild(toast);
            
            setTimeout(() => {
                toast.classList.remove('translate-x-full');
            }, 100);
            
            setTimeout(() => {
                toast.classList.add('translate-x-full');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        // Load data on page load
        loadData();
    </script>
</body>

</html>
