<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['id_users'])) {
    header('Location: ../login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Admin Panel - Manajemen Reservasi</title>
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
                    <h2 class="text-lg font-semibold text-slate-800 dark:text-white">Manajemen Reservasi</h2>
                </div>
                <div class="flex items-center gap-4">
                    <div class="relative hidden sm:block">
                        <span
                            class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xl leading-none">search</span>
                        <input id="searchInput"
                            class="pl-10 pr-4 py-2 bg-slate-100 dark:bg-slate-800 border-none rounded-lg focus:ring-2 focus:ring-primary text-sm w-64"
                            placeholder="Cari reservasi..." type="text" onkeyup="filterData()" />
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
                            Reservasi</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Kelola informasi reservasi tamu secara terpusat.</p>
                    </div>
                    <button id="btnTambahReservasi"
                        class="flex items-center gap-2 px-5 py-2.5 bg-primary text-white font-bold rounded-lg hover:bg-primary/90 transition-all shadow-sm w-full sm:w-auto justify-center">
                        <span class="material-symbols-outlined">add</span>
                        <span>Tambah Reservasi</span>
                    </button>
                </div>

                <!-- Filters -->
                <div class="flex flex-wrap items-center gap-4">
                    <div class="relative flex-1 max-w-xs">
                        <span
                            class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">business</span>
                        <select id="filter_cabang" onchange="applyFilters()"
                            class="w-full pl-10 pr-10 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none text-sm appearance-none cursor-pointer">
                            <option value="">-- Cabang --</option>
                        </select>
                    </div>
                    <div class="relative flex-1 max-w-xs">
                        <span
                            class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">bed</span>
                        <select id="filter_tipe" onchange="applyFilters()"
                            class="w-full pl-10 pr-10 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none text-sm appearance-none cursor-pointer">
                            <option value="">-- Tipe Kamar --</option>
                        </select>
                    </div>
                    <div class="relative flex-1 max-w-xs">
                        <span
                            class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg">travel_explore</span>
                        <select id="filter_ota" onchange="applyFilters()"
                            class="w-full pl-10 pr-10 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none text-sm appearance-none cursor-pointer">
                            <option value="">All</option>
                        </select>
                    </div>
                    <button onclick="resetFilters()"
                        class="px-4 py-2.5 text-sm font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors flex items-center gap-1">
                        <span class="material-symbols-outlined text-lg">refresh</span>
                        Reset
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
                                        Kode Booking</th>
                                    <th
                                        class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                        Tamu</th>
                                    <th
                                        class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                        Cabang</th>
                                    <th
                                        class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                        Tipe Kamar</th>
                                    <th
                                        class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                        Room No</th>
                                    <th
                                        class="px-6 py-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                        Check In/Out</th>
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
                        <span class="material-symbols-outlined text-6xl text-slate-300 dark:text-slate-600 mb-4">event_busy</span>
                        <p class="text-slate-500 dark:text-slate-400">Tidak ada data reservasi</p>
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

    <!-- Add Reservation Modal -->
    <div class="fixed inset-0 z-50 flex items-center justify-center hidden" id="add-reservation-modal">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        <!-- Modal Content -->
        <div
            class="relative bg-white dark:bg-background-dark w-full max-w-2xl mx-4 rounded-xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
            <!-- Header (Fixed) -->
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between flex-shrink-0">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Tambah Reservasi Baru</h3>
                <button class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 btn-close-modal">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <!-- Scrollable Content -->
            <div class="overflow-y-auto px-6 py-4 space-y-4 scrollbar-thin scrollbar-thumb-slate-300 dark:scrollbar-thumb-slate-600">
                <form id="formTambahReservasi" class="space-y-4" enctype="multipart/form-data">
                    <!-- Guest Search Section -->
                    <div class="bg-slate-50 dark:bg-slate-800/50 p-4 rounded-lg">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Cari Tamu <span class="text-red-500">*</span></label>
                        <div class="flex gap-2">
                            <input id="guest_search"
                                class="flex-1 px-3 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                                placeholder="Cari berdasarkan No. WA atau Email..." type="text" />
                            <button type="button" onclick="searchGuest()"
                                class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 font-semibold text-sm flex items-center gap-1">
                                <span class="material-symbols-outlined text-sm">search</span>
                                Cari
                            </button>
                        </div>
                        <div id="guest_search_results" class="mt-2 hidden">
                            <!-- Search results will appear here -->
                        </div>
                    </div>

                    <!-- Selected Guest Info -->
                    <div id="selected_guest_info" class="hidden bg-green-50 dark:bg-green-900/20 p-4 rounded-lg border border-green-200 dark:border-green-800">
                        <div class="flex items-start gap-3">
                            <span class="material-symbols-outlined text-green-600 text-xl">check_circle</span>
                            <div class="flex-1">
                                <p class="text-sm font-bold text-green-800 dark:text-green-300">Tamu Terpilih</p>
                                <p id="selected_guest_name" class="text-sm text-green-700 dark:text-green-400"></p>
                                <p id="selected_guest_wa" class="text-xs text-green-600 dark:text-green-500"></p>
                                <input type="hidden" id="selected_id_guest" />
                            </div>
                            <button type="button" onclick="clearGuestSelection()" class="text-green-600 hover:text-green-800">
                                <span class="material-symbols-outlined text-sm">close</span>
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Pilih Cabang <span class="text-red-500">*</span></label>
                            <select id="id_cabang" name="id_cabang"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                                onchange="loadTipeKamar()" required>
                                <option value="">-- Pilih Cabang --</option>
                            </select>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Pilih Tipe Kamar <span class="text-red-500">*</span></label>
                            <select id="id_akomodasi" name="id_akomodasi"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                                required disabled>
                                <option value="">-- Pilih cabang terlebih dahulu --</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Kode Booking <span class="text-red-500">*</span></label>
                            <input id="kode_booking" name="kode_booking"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                                placeholder="BK-2026-001" type="text" required />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Nomor Kamar <span class="text-red-500">*</span></label>
                            <input id="nomor_kamar" name="nomor_kamar"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                                placeholder="302" type="text" required />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Tanggal Check-In <span class="text-red-500">*</span></label>
                            <input id="tanggal_in" name="tanggal_in"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                                type="date" required />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Tanggal Check-Out <span class="text-red-500">*</span></label>
                            <input id="tanggal_out" name="tanggal_out"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                                type="date" required />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">OTA / Travel Agent</label>
                            <select id="ota" name="ota"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm">
                                <option value="">-- Pilih OTA --</option>
                                <option value="Traveloka">Traveloka</option>
                                <option value="Hotels.com">Hotels.com</option>
                                <option value="Booking.com">Booking.com</option>
                                <option value="Agoda">Agoda</option>
                                <option value="Expedia">Expedia</option>
                                <option value="Direct">Direct (Walk-in)</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Status <span class="text-red-500">*</span></label>
                            <select id="status" name="status"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                                required>
                                <option value="0">Staying</option>
                                <option value="1">Completed</option>
                            </select>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Upload Receipt (PDF/JPG) <span class="text-red-500">*</span></label>
                            <div
                                class="mt-1 flex items-center gap-4 px-4 py-4 border-2 border-slate-300 dark:border-slate-700 border-dashed rounded-lg">
                                <!-- Preview -->
                                <div id="receipt_preview" class="hidden w-20 h-20 rounded-lg overflow-hidden border border-slate-200 dark:border-slate-600 flex-shrink-0 bg-slate-100 dark:bg-slate-800">
                                    <img id="receipt_preview_img" src="" alt="Preview" class="w-full h-full object-cover" />
                                </div>
                                <div class="space-y-1 text-center flex-1">
                                    <span class="material-symbols-outlined text-slate-400 text-3xl">upload_file</span>
                                    <div class="flex text-sm text-slate-600 dark:text-slate-400 justify-center">
                                        <label
                                            class="relative cursor-pointer bg-white dark:bg-background-dark rounded-md font-medium text-primary hover:text-primary/80 focus-within:outline-none"
                                            for="link_receipt">
                                            <span>Upload file</span>
                                            <input class="sr-only" id="link_receipt" name="link_receipt" type="file" accept=".pdf,.jpg,.jpeg,.png" />
                                        </label>
                                    </div>
                                    <p class="text-xs text-slate-500">PDF, JPG, PNG max 5MB</p>
                                    <p id="receipt_file_name" class="text-xs text-slate-400 truncate max-w-[200px]"></p>
                                </div>
                            </div>
                            <p id="receipt_error" class="text-xs text-red-500 mt-1 hidden"></p>
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

    <!-- Edit Reservation Modal -->
    <div class="fixed inset-0 z-50 flex items-center justify-center hidden" id="edit-reservation-modal">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        <!-- Modal Content -->
        <div
            class="relative bg-white dark:bg-background-dark w-full max-w-2xl mx-4 rounded-xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
            <!-- Header (Fixed) -->
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between flex-shrink-0">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Edit Reservasi</h3>
                <button class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 btn-close-edit-modal">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <!-- Scrollable Content -->
            <div class="overflow-y-auto px-6 py-4 space-y-4 scrollbar-thin scrollbar-thumb-slate-300 dark:scrollbar-thumb-slate-600">
                <form id="formEditReservasi" class="space-y-4" enctype="multipart/form-data">
                    <input type="hidden" id="edit_id_inap" name="id_inap" />
                    
                    <!-- Guest Info (Read-only) -->
                    <div class="bg-slate-50 dark:bg-slate-800/50 p-4 rounded-lg">
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Tamu</p>
                        <p id="edit_guest_name" class="text-sm font-semibold text-slate-900 dark:text-white"></p>
                        <input type="hidden" id="edit_id_guest" name="id_guest" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Pilih Cabang <span class="text-red-500">*</span></label>
                            <select id="edit_id_cabang" name="id_cabang"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                                onchange="editLoadTipeKamar()" required>
                                <option value="">-- Pilih Cabang --</option>
                            </select>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Pilih Tipe Kamar <span class="text-red-500">*</span></label>
                            <select id="edit_id_akomodasi" name="id_akomodasi"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                                required>
                                <option value="">-- Pilih tipe kamar --</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Kode Booking <span class="text-red-500">*</span></label>
                            <input id="edit_kode_booking" name="kode_booking"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                                type="text" required />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Nomor Kamar <span class="text-red-500">*</span></label>
                            <input id="edit_nomor_kamar" name="nomor_kamar"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                                type="text" required />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Tanggal Check-In <span class="text-red-500">*</span></label>
                            <input id="edit_tanggal_in" name="tanggal_in"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                                type="date" required />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Tanggal Check-Out <span class="text-red-500">*</span></label>
                            <input id="edit_tanggal_out" name="tanggal_out"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                                type="date" required />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">OTA / Travel Agent</label>
                            <select id="edit_ota" name="ota"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm">
                                <option value="">-- Pilih OTA --</option>
                                <option value="Traveloka">Traveloka</option>
                                <option value="Hotels.com">Hotels.com</option>
                                <option value="Booking.com">Booking.com</option>
                                <option value="Agoda">Agoda</option>
                                <option value="Expedia">Expedia</option>
                                <option value="Direct">Direct (Walk-in)</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Status <span class="text-red-500">*</span></label>
                            <select id="edit_status" name="status"
                                class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none text-sm"
                                required>
                                <option value="0">Staying</option>
                                <option value="1">Completed</option>
                            </select>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Upload Receipt (PDF/JPG)</label>
                            <div class="mb-2">
                                <p id="edit_current_receipt" class="text-xs text-slate-500"></p>
                            </div>
                            <div
                                class="mt-1 flex items-center gap-4 px-4 py-4 border-2 border-slate-300 dark:border-slate-700 border-dashed rounded-lg">
                                <div id="edit_receipt_preview" class="hidden w-20 h-20 rounded-lg overflow-hidden border border-slate-200 dark:border-slate-600 flex-shrink-0 bg-slate-100 dark:bg-slate-800">
                                    <img id="edit_receipt_preview_img" src="" alt="Preview" class="w-full h-full object-cover" />
                                </div>
                                <div class="space-y-1 text-center flex-1">
                                    <span class="material-symbols-outlined text-slate-400 text-3xl">upload_file</span>
                                    <div class="flex text-sm text-slate-600 dark:text-slate-400 justify-center">
                                        <label
                                            class="relative cursor-pointer bg-white dark:bg-background-dark rounded-md font-medium text-primary hover:text-primary/80 focus-within:outline-none"
                                            for="edit_link_receipt">
                                            <span>Upload file</span>
                                            <input class="sr-only" id="edit_link_receipt" name="link_receipt" type="file" accept=".pdf,.jpg,.jpeg,.png" />
                                        </label>
                                    </div>
                                    <p class="text-xs text-slate-500">PDF, JPG, PNG max 5MB</p>
                                    <p id="edit_receipt_file_name" class="text-xs text-slate-400 truncate max-w-[200px]"></p>
                                </div>
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

    <script>
        // Constants
        const API_BASE = '../../api';
        const maxReceiptSize = 5 * 1024 * 1024; // 5MB

        let currentData = [];
        let filteredData = [];
        let cabangList = [];
        let tipeKamarList = [];

        // Load data on page load
        document.addEventListener('DOMContentLoaded', () => {
            loadReservasi();
            loadCabang();
            loadTipeKamarForFilter();
        });

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

            setTimeout(() => toast.classList.add('show'), 10);
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        // Toggle Sidebar for mobile
        function toggleSidebar() {
            const sidebar = document.querySelector('aside');
            const overlay = document.querySelector('.sidebar-overlay');
            sidebar?.classList.toggle('active');
            overlay?.classList.toggle('active');
        }

        // Toggle Search for mobile
        function toggleSearch() {
            const searchInput = document.getElementById('searchInput');
            searchInput?.classList.toggle('hidden');
            searchInput?.focus();
        }

        // Load Cabang for dropdown
        async function loadCabang() {
            try {
                const response = await fetch(`${API_BASE}/cabang/list.php`);
                const result = await response.json();
                if (result.success && result.data) {
                    cabangList = result.data;
                    populateCabangDropdown();
                }
            } catch (err) {
                console.error('Error loading cabang:', err);
            }
        }

        // Populate Cabang dropdown
        function populateCabangDropdown() {
            const select = document.getElementById('id_cabang');
            const editSelect = document.getElementById('edit_id_cabang');
            
            const options = '<option value="">-- Pilih Cabang --</option>' +
                cabangList.map(c => `<option value="${c.id_cabang}">${c.nama_cabang}</option>`).join('');
            
            if (select) select.innerHTML = options;
            if (editSelect) editSelect.innerHTML = options;
            
            // Populate filter cabang dropdown
            populateFilterCabang();
        }

        // Populate Filter Cabang Dropdown
        function populateFilterCabang() {
            const filterSelect = document.getElementById('filter_cabang');
            if (!filterSelect) return;
            
            const uniqueCabang = [];
            const seen = new Set();
            currentData.forEach(item => {
                if (item.id_cabang && !seen.has(item.id_cabang)) {
                    seen.add(item.id_cabang);
                    uniqueCabang.push({ id: item.id_cabang, nama: item.nama_cabang });
                }
            });
            uniqueCabang.sort((a, b) => a.nama.localeCompare(b.nama));
            
            filterSelect.innerHTML = '<option value="">Semua Cabang</option>' +
                uniqueCabang.map(c => `<option value="${c.id}">${c.nama}</option>`).join('');
        }

        // Load Tipe Kamar for filter dropdown
        async function loadTipeKamarForFilter() {
            try {
                const response = await fetch(`${API_BASE}/tipe_kamar/list.php`);
                const result = await response.json();
                if (result.success && result.data) {
                    tipeKamarList = result.data;
                    populateFilterTipe();
                }
            } catch (err) {
                console.error('Error loading tipe kamar:', err);
            }
        }

        // Populate Filter Tipe Dropdown
        function populateFilterTipe() {
            const filterSelect = document.getElementById('filter_tipe');
            if (!filterSelect) return;
            
            const uniqueTipe = [];
            const seen = new Set();
            currentData.forEach(item => {
                if (item.id_akomodasi && !seen.has(item.id_akomodasi)) {
                    seen.add(item.id_akomodasi);
                    uniqueTipe.push({ id: item.id_akomodasi, nama: item.nama_tipe });
                }
            });
            uniqueTipe.sort((a, b) => a.nama.localeCompare(b.nama));
            
            filterSelect.innerHTML = '<option value="">Semua Tipe Kamar</option>' +
                uniqueTipe.map(t => `<option value="${t.id}">${t.nama}</option>`).join('');
        }

        // Populate Filter OTA Dropdown (from distinct data in table)
        function populateFilterOta() {
            const filterSelect = document.getElementById('filter_ota');
            if (!filterSelect) return;
            
            const uniqueOta = [];
            const seen = new Set();
            currentData.forEach(item => {
                if (item.ota && !seen.has(item.ota)) {
                    seen.add(item.ota);
                    uniqueOta.push(item.ota);
                }
            });
            uniqueOta.sort((a, b) => a.localeCompare(b));
            
            filterSelect.innerHTML = '<option value="">All</option>' +
                uniqueOta.map(ota => `<option value="${ota}">${ota}</option>`).join('');
        }

        // Load Tipe Kamar based on Cabang
        async function loadTipeKamar() {
            const idCabang = document.getElementById('id_cabang').value;
            const select = document.getElementById('id_akomodasi');
            
            if (!idCabang) {
                select.innerHTML = '<option value="">-- Pilih cabang terlebih dahulu --</option>';
                select.disabled = true;
                return;
            }

            select.disabled = true;
            select.innerHTML = '<option value="">Loading...</option>';

            try {
                const response = await fetch(`${API_BASE}/cabang_tipe/list.php?id_cabang=${idCabang}`);
                const result = await response.json();
                
                if (result.success && result.data && result.data.length > 0) {
                    tipeKamarList = result.data;
                    select.innerHTML = '<option value="">-- Pilih Tipe Kamar --</option>' +
                        result.data.map(t => `<option value="${t.id_akomodasi}">${t.nama_tipe}</option>`).join('');
                    select.disabled = false;
                } else {
                    select.innerHTML = '<option value="">-- Tidak ada tipe kamar --</option>';
                }
            } catch (err) {
                select.innerHTML = '<option value="">-- Error loading data --</option>';
                showToast('Gagal memuat tipe kamar', 'error');
            }
        }

        // Edit: Load Tipe Kamar based on Cabang
        async function editLoadTipeKamar() {
            const idCabang = document.getElementById('edit_id_cabang').value;
            const select = document.getElementById('edit_id_akomodasi');
            
            if (!idCabang) {
                select.innerHTML = '<option value="">-- Pilih cabang terlebih dahulu --</option>';
                return;
            }

            select.innerHTML = '<option value="">Loading...</option>';

            try {
                const response = await fetch(`${API_BASE}/cabang_tipe/list.php?id_cabang=${idCabang}`);
                const result = await response.json();
                
                if (result.success && result.data && result.data.length > 0) {
                    select.innerHTML = '<option value="">-- Pilih Tipe Kamar --</option>' +
                        result.data.map(t => `<option value="${t.id_akomodasi}">${t.nama_tipe}</option>`).join('');
                } else {
                    select.innerHTML = '<option value="">-- Tidak ada tipe kamar --</option>';
                }
            } catch (err) {
                select.innerHTML = '<option value="">-- Error loading data --</option>';
                showToast('Gagal memuat tipe kamar', 'error');
            }
        }

        // Search Guest
        async function searchGuest() {
            const searchQuery = document.getElementById('guest_search').value.trim();
            const resultsDiv = document.getElementById('guest_search_results');
            
            if (!searchQuery) {
                showToast('Masukkan no. WA atau email', 'error');
                return;
            }

            try {
                const response = await fetch(`${API_BASE}/guest/list.php`);
                const result = await response.json();
                
                if (result.success && result.data) {
                    const filtered = result.data.filter(g => 
                        (g.wa && g.wa.includes(searchQuery)) || 
                        (g.email && g.email.toLowerCase().includes(searchQuery.toLowerCase()))
                    );

                    if (filtered.length > 0) {
                        resultsDiv.innerHTML = filtered.map(g => `
                            <div onclick="selectGuest(${g.id_guest}, '${g.nama_lengkap.replace(/'/g, "\\'")}', '${g.wa || ''}')"
                                class="p-3 bg-white dark:bg-slate-900 rounded-lg border border-slate-200 dark:border-slate-700 cursor-pointer hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors mb-2">
                                <p class="font-semibold text-sm text-slate-900 dark:text-white">${g.nama_lengkap}</p>
                                <p class="text-xs text-slate-500">WA: ${g.wa || '-'} | Email: ${g.email || '-'}</p>
                            </div>
                        `).join('');
                        resultsDiv.classList.remove('hidden');
                    } else {
                        resultsDiv.innerHTML = '<p class="text-sm text-slate-500 text-center py-2">Tidak ada tamu ditemukan</p>';
                        resultsDiv.classList.remove('hidden');
                    }
                }
            } catch (err) {
                showToast('Gagal mencari tamu', 'error');
            }
        }

        // Select Guest
        function selectGuest(idGuest, nama, wa) {
            document.getElementById('selected_id_guest').value = idGuest;
            document.getElementById('selected_guest_name').textContent = nama;
            document.getElementById('selected_guest_wa').textContent = 'WA: ' + wa;
            document.getElementById('selected_guest_info').classList.remove('hidden');
            document.getElementById('guest_search_results').classList.add('hidden');
            document.getElementById('guest_search').value = '';
        }

        // Clear Guest Selection
        function clearGuestSelection() {
            document.getElementById('selected_id_guest').value = '';
            document.getElementById('selected_guest_info').classList.add('hidden');
        }

        // Load Reservasi
        async function loadReservasi() {
            const tbody = document.getElementById('tableBody');
            const mobileView = document.getElementById('mobileView');
            const noData = document.getElementById('noData');
            const paginationContainer = document.getElementById('paginationContainer');

            try {
                const response = await fetch(`${API_BASE}/inap/list.php`); // No id_guest = admin mode (all data)
                const result = await response.json();

                if (result.success && result.data) {
                    currentData = result.data;
                    filteredData = result.data;
                    
                    // Populate filter dropdowns
                    populateFilterCabang();
                    populateFilterTipe();
                    populateFilterOta();
                    
                    if (result.data.length > 0) {
                        renderTable(result.data);
                        renderMobile(result.data);
                        noData.classList.add('hidden');
                        paginationContainer.classList.remove('hidden');
                        updatePagination(result.data.length, result.data.length);
                    } else {
                        noData.classList.remove('hidden');
                        paginationContainer.classList.add('hidden');
                    }
                } else {
                    noData.classList.remove('hidden');
                    if (result.message) {
                        showToast(result.message, 'error');
                    }
                }
            } catch (err) {
                showToast('Gagal memuat data reservasi', 'error');
                console.error('Error:', err);
            }
        }

        // Apply Filters
        function applyFilters() {
            const filterCabang = document.getElementById('filter_cabang').value;
            const filterTipe = document.getElementById('filter_tipe').value;
            const filterOta = document.getElementById('filter_ota').value;

            filteredData = currentData.filter(item => {
                const matchCabang = !filterCabang || item.id_cabang == filterCabang;
                const matchTipe = !filterTipe || item.id_akomodasi == filterTipe;
                const matchOta = !filterOta || item.ota == filterOta;
                return matchCabang && matchTipe && matchOta;
            });

            renderTable(filteredData);
            renderMobile(filteredData);
            updatePagination(filteredData.length, currentData.length);

            // Show/hide no data state
            const noData = document.getElementById('noData');
            const paginationContainer = document.getElementById('paginationContainer');
            if (filteredData.length === 0) {
                noData.classList.remove('hidden');
                paginationContainer.classList.add('hidden');
            } else {
                noData.classList.add('hidden');
                paginationContainer.classList.remove('hidden');
            }
        }

        // Reset Filters
        function resetFilters() {
            document.getElementById('filter_cabang').value = '';
            document.getElementById('filter_tipe').value = '';
            document.getElementById('filter_ota').value = '';
            filteredData = currentData;
            renderTable(filteredData);
            renderMobile(filteredData);
            updatePagination(filteredData.length, filteredData.length);
        }

        // Render Desktop Table
        function renderTable(data) {
            const tbody = document.getElementById('tableBody');
            tbody.innerHTML = data.map(item => `
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                    <td class="px-6 py-4">
                        <div class="font-semibold text-slate-900 dark:text-white">${item.kode_booking || '-'}</div>
                        <div class="text-xs text-slate-500">${item.ota || 'Direct'}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="font-semibold text-slate-900 dark:text-white">${item.nama_lengkap || '-'}</div>
                        <div class="text-xs text-slate-500">${item.username || '-'}</div>
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">${item.nama_cabang || '-'}</td>
                    <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">${item.nama_tipe || '-'}</td>
                    <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-400">${item.nomor_kamar || '-'}</td>
                    <td class="px-6 py-4 text-xs text-slate-500">
                        <div>In: ${formatDate(item.tanggal_in)}</div>
                        <div>Out: ${formatDate(item.tanggal_out)}</div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="px-2 py-1 rounded-full text-xs font-bold ${item.status == 0 ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'}">
                            ${item.status == 0 ? 'STAYING' : 'COMPLETED'}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right space-x-2">
                        <button onclick="openEditModal(${item.id_inap})" class="p-1.5 text-slate-400 hover:text-primary transition-colors">
                            <span class="material-symbols-outlined text-xl">edit_square</span>
                        </button>
                        <button onclick="deleteReservasi(${item.id_inap})" class="p-1.5 text-slate-400 hover:text-red-500 transition-colors">
                            <span class="material-symbols-outlined text-xl">delete</span>
                        </button>
                    </td>
                </tr>
            `).join('');
        }

        // Render Mobile Cards
        function renderMobile(data) {
            const mobileView = document.getElementById('mobileView');
            mobileView.innerHTML = data.map(item => `
                <div class="p-4 space-y-3">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="font-semibold text-slate-900 dark:text-white">${item.kode_booking || '-'}</div>
                            <div class="text-xs text-slate-500">${item.ota || 'Direct'}</div>
                        </div>
                        <span class="px-2 py-1 rounded-full text-xs font-bold ${item.status == 0 ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400'}">
                            ${item.status == 0 ? 'STAYING' : 'COMPLETED'}
                        </span>
                    </div>
                    <div class="text-sm text-slate-600 dark:text-slate-400 space-y-1">
                        <div class="font-semibold">${item.nama_lengkap || '-'}</div>
                        <div>${item.nama_cabang || '-'} - ${item.nama_tipe || '-'}</div>
                        <div>Room: ${item.nomor_kamar || '-'}</div>
                        <div>In: ${formatDate(item.tanggal_in)} | Out: ${formatDate(item.tanggal_out)}</div>
                    </div>
                    <div class="flex gap-2">
                        <button onclick="openEditModal(${item.id_inap})" class="flex-1 p-2 bg-primary/10 text-primary rounded-lg font-semibold text-sm">
                            Edit
                        </button>
                        <button onclick="deleteReservasi(${item.id_inap})" class="flex-1 p-2 bg-red-500/10 text-red-500 rounded-lg font-semibold text-sm">
                            Delete
                        </button>
                    </div>
                </div>
            `).join('');
        }

        // Update Pagination
        function updatePagination(showing, total) {
            document.getElementById('showingText').textContent = `Showing ${showing} of ${total} results`;
        }

        // Filter Data (Search)
        function filterData() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            
            // Get current filter values
            const filterCabang = document.getElementById('filter_cabang').value;
            const filterTipe = document.getElementById('filter_tipe').value;
            const filterOta = document.getElementById('filter_ota').value;
            
            filteredData = currentData.filter(item => {
                const matchSearch = 
                    (item.kode_booking && item.kode_booking.toLowerCase().includes(searchTerm)) ||
                    (item.nama_lengkap && item.nama_lengkap.toLowerCase().includes(searchTerm)) ||
                    (item.nama_cabang && item.nama_cabang.toLowerCase().includes(searchTerm)) ||
                    (item.nomor_kamar && item.nomor_kamar.toLowerCase().includes(searchTerm));
                
                const matchCabang = !filterCabang || item.id_cabang == filterCabang;
                const matchTipe = !filterTipe || item.id_akomodasi == filterTipe;
                const matchOta = !filterOta || item.ota == filterOta;
                
                return matchSearch && matchCabang && matchTipe && matchOta;
            });
            
            renderTable(filteredData);
            renderMobile(filteredData);
            updatePagination(filteredData.length, currentData.length);
            
            // Show/hide no data state
            const noData = document.getElementById('noData');
            const paginationContainer = document.getElementById('paginationContainer');
            if (filteredData.length === 0) {
                noData.classList.remove('hidden');
                paginationContainer.classList.add('hidden');
            } else {
                noData.classList.add('hidden');
                paginationContainer.classList.remove('hidden');
            }
        }

        // Format Date
        function formatDate(dateString) {
            if (!dateString) return '-';
            const date = new Date(dateString);
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            return `${date.getDate()} ${months[date.getMonth()]} ${date.getFullYear()}`;
        }

        // Open Edit Modal
        async function openEditModal(idInap) {
            const item = currentData.find(i => i.id_inap === idInap);
            if (!item) return;

            // Populate form
            document.getElementById('edit_id_inap').value = item.id_inap;
            document.getElementById('edit_id_guest').value = item.id_guest;
            document.getElementById('edit_guest_name').textContent = item.nama_lengkap;
            document.getElementById('edit_kode_booking').value = item.kode_booking;
            document.getElementById('edit_nomor_kamar').value = item.nomor_kamar;
            document.getElementById('edit_tanggal_in').value = item.tanggal_in.split(' ')[0];
            document.getElementById('edit_tanggal_out').value = item.tanggal_out.split(' ')[0];
            document.getElementById('edit_ota').value = item.ota || '';
            document.getElementById('edit_status').value = item.status;
            document.getElementById('edit_id_cabang').value = item.id_cabang;
            
            // Load tipe kamar for this cabang
            await editLoadTipeKamar();
            document.getElementById('edit_id_akomodasi').value = item.id_akomodasi;

            // Show current receipt
            const currentReceiptDiv = document.getElementById('edit_current_receipt');
            if (item.link_receipt) {
                currentReceiptDiv.innerHTML = `Current: <a href="../../receipt/${item.link_receipt}" target="_blank" class="text-primary">${item.link_receipt}</a>`;
            } else {
                currentReceiptDiv.textContent = 'No receipt uploaded';
            }

            document.getElementById('edit-reservation-modal').classList.remove('hidden');
        }

        // Save Reservation
        document.getElementById('btnSimpan')?.addEventListener('click', async function() {
            const idGuest = document.getElementById('selected_id_guest').value;
            const idCabang = document.getElementById('id_cabang').value;
            const idAkomodasi = document.getElementById('id_akomodasi').value;
            const kodeBooking = document.getElementById('kode_booking').value;
            const nomorKamar = document.getElementById('nomor_kamar').value;
            const tanggalIn = document.getElementById('tanggal_in').value;
            const tanggalOut = document.getElementById('tanggal_out').value;
            const ota = document.getElementById('ota').value;
            const status = document.getElementById('status').value;
            const receiptFile = document.getElementById('link_receipt').files[0];

            // Validation
            if (!idGuest) {
                showToast('Pilih tamu terlebih dahulu', 'error');
                return;
            }
            if (!idCabang || !idAkomodasi) {
                showToast('Pilih cabang dan tipe kamar', 'error');
                return;
            }
            if (!kodeBooking || !nomorKamar || !tanggalIn || !tanggalOut) {
                showToast('Lengkapi semua field yang wajib diisi', 'error');
                return;
            }

            const formData = new FormData();
            formData.append('id_cabang', idCabang);
            formData.append('id_akomodasi', idAkomodasi);
            formData.append('id_guest', idGuest);
            formData.append('kode_booking', kodeBooking);
            formData.append('nomor_kamar', nomorKamar);
            formData.append('tanggal_in', tanggalIn);
            formData.append('tanggal_out', tanggalOut);
            formData.append('status', status);
            formData.append('ota', ota);
            formData.append('id_users', '<?php echo $_SESSION['id_users']; ?>');
            if (receiptFile) {
                formData.append('link_receipt', receiptFile);
            }

            const btn = this;
            btn.disabled = true;
            btn.innerHTML = '<span class="material-symbols-outlined spinner text-sm">sync</span><span>Menyimpan...</span>';

            try {
                const response = await fetch(`${API_BASE}/inap/new.php`, {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.success) {
                    showToast('Reservasi berhasil ditambahkan', 'success');
                    closeModal();
                    loadReservasi();
                } else {
                    showToast(result.message || 'Gagal menyimpan reservasi', 'error');
                }
            } catch (err) {
                showToast('Error menyimpan reservasi', 'error');
            }

            btn.disabled = false;
            btn.innerHTML = '<span class="material-symbols-outlined text-sm">save</span><span>Simpan</span>';
        });

        // Update Reservation
        document.getElementById('btnUpdate')?.addEventListener('click', async function() {
            const idInap = document.getElementById('edit_id_inap').value;
            const idGuest = document.getElementById('edit_id_guest').value;
            const idCabang = document.getElementById('edit_id_cabang').value;
            const idAkomodasi = document.getElementById('edit_id_akomodasi').value;
            const kodeBooking = document.getElementById('edit_kode_booking').value;
            const nomorKamar = document.getElementById('edit_nomor_kamar').value;
            const tanggalIn = document.getElementById('edit_tanggal_in').value;
            const tanggalOut = document.getElementById('edit_tanggal_out').value;
            const ota = document.getElementById('edit_ota').value;
            const status = document.getElementById('edit_status').value;
            const receiptFile = document.getElementById('edit_link_receipt').files[0];

            const formData = new FormData();
            formData.append('id_inap', idInap);
            formData.append('id_cabang', idCabang);
            formData.append('id_akomodasi', idAkomodasi);
            formData.append('id_guest', idGuest);
            formData.append('kode_booking', kodeBooking);
            formData.append('nomor_kamar', nomorKamar);
            formData.append('tanggal_in', tanggalIn);
            formData.append('tanggal_out', tanggalOut);
            formData.append('status', status);
            formData.append('ota', ota);
            formData.append('id_users', '<?php echo $_SESSION['id_users']; ?>');
            if (receiptFile) {
                formData.append('link_receipt', receiptFile);
            }

            const btn = this;
            btn.disabled = true;
            btn.innerHTML = '<span class="material-symbols-outlined spinner text-sm">sync</span><span>Updating...</span>';

            try {
                const response = await fetch(`${API_BASE}/inap/update.php`, {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.success) {
                    showToast('Reservasi berhasil diupdate', 'success');
                    closeEditModal();
                    loadReservasi();
                } else {
                    showToast(result.message || 'Gagal mengupdate reservasi', 'error');
                }
            } catch (err) {
                showToast('Error mengupdate reservasi', 'error');
            }

            btn.disabled = false;
            btn.innerHTML = '<span class="material-symbols-outlined text-sm">save</span><span>Update</span>';
        });

        // Delete Reservation
        async function deleteReservasi(idInap) {
            if (!confirm('Yakin ingin menghapus reservasi ini?')) return;

            try {
                const response = await fetch(`${API_BASE}/inap/delete.php`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id_inap: idInap })
                });
                const result = await response.json();

                if (result.success) {
                    showToast('Reservasi berhasil dihapus', 'success');
                    loadReservasi();
                } else {
                    showToast(result.message || 'Gagal menghapus reservasi', 'error');
                }
            } catch (err) {
                showToast('Error menghapus reservasi', 'error');
            }
        }

        // Close Modal Functions
        function closeModal() {
            document.getElementById('add-reservation-modal').classList.add('hidden');
            document.getElementById('formTambahReservasi').reset();
            clearGuestSelection();
        }

        function closeEditModal() {
            document.getElementById('edit-reservation-modal').classList.add('hidden');
            document.getElementById('formEditReservasi').reset();
        }

        // Event Listeners for modal close buttons
        document.querySelectorAll('.btn-close-modal').forEach(btn => {
            btn.addEventListener('click', closeModal);
        });

        document.querySelectorAll('.btn-close-edit-modal').forEach(btn => {
            btn.addEventListener('click', closeEditModal);
        });

        // File upload preview for receipt
        document.getElementById('link_receipt')?.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                if (file.size > maxReceiptSize) {
                    showToast('Ukuran file terlalu besar. Maksimal 5MB', 'error');
                    this.value = '';
                    return;
                }
                document.getElementById('receipt_file_name').textContent = file.name;
            }
        });

        document.getElementById('edit_link_receipt')?.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                if (file.size > maxReceiptSize) {
                    showToast('Ukuran file terlalu besar. Maksimal 5MB', 'error');
                    this.value = '';
                    return;
                }
                document.getElementById('edit_receipt_file_name').textContent = file.name;
            }
        });
    </script>
</body>

</html>
