<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get current page name
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
// Get id_cabang from URL parameter
$id_cabang_nav = isset($_GET['id_cabang']) ? (int) $_GET['id_cabang'] : 0;
?>

<!-- Bottom Navigation Container -->
<div id="navbarContainer" class="fixed bottom-0 left-0 right-0 z-50">
    <div class="bg-white/95 dark:bg-slate-900/95 backdrop-blur-xl border-t border-slate-200 dark:border-slate-700 px-2 py-2">
        <div class="flex flex-row items-center justify-around">
            <!-- Room -->
            <a href="kamar.php<?php echo $id_cabang_nav > 0 ? '?id_cabang=' . $id_cabang_nav : ''; ?>"
               class="flex flex-col items-center gap-1 p-3 min-w-[64px] <?php echo $currentPage === 'kamar' ? 'text-primary' : 'text-slate-400 hover:text-primary transition-colors'; ?>">
                <span class="material-symbols-outlined text-[26px] <?php echo $currentPage === 'kamar' ? 'fill-1' : ''; ?>">bed</span>
                <span class="nav-text text-[10px] <?php echo $currentPage === 'kamar' ? 'font-semibold' : 'font-medium'; ?> whitespace-nowrap">Room</span>
            </a>

            <!-- Facility -->
            <a href="fasilitas.php<?php echo $id_cabang_nav > 0 ? '?id_cabang=' . $id_cabang_nav : ''; ?>"
               class="flex flex-col items-center gap-1 p-3 min-w-[64px] <?php echo $currentPage === 'fasilitas' ? 'text-primary' : 'text-slate-400 hover:text-primary transition-colors'; ?>">
                <span class="material-symbols-outlined text-[26px] <?php echo $currentPage === 'fasilitas' ? 'fill-1' : ''; ?>">pool</span>
                <span class="nav-text text-[10px] <?php echo $currentPage === 'fasilitas' ? 'font-semibold' : 'font-medium'; ?> whitespace-nowrap">Facility</span>
            </a>

            <!-- Receptionist -->
            <a href="receptionist.php<?php echo $id_cabang_nav > 0 ? '?id_cabang=' . $id_cabang_nav : ''; ?>"
               class="flex flex-col items-center gap-1 p-3 min-w-[64px] <?php echo $currentPage === 'receptionist' ? 'text-primary' : 'text-slate-400 hover:text-primary transition-colors'; ?>">
                <span class="material-symbols-outlined text-[26px] <?php echo $currentPage === 'receptionist' ? 'fill-1' : ''; ?>">concierge</span>
                <span class="nav-text text-[10px] <?php echo $currentPage === 'receptionist' ? 'font-semibold' : 'font-medium'; ?> whitespace-nowrap">Receptionist</span>
            </a>

            <!-- Housekeeping -->
            <a href="housekeeping.php<?php echo $id_cabang_nav > 0 ? '?id_cabang=' . $id_cabang_nav : ''; ?>"
               class="flex flex-col items-center gap-1 p-3 min-w-[64px] <?php echo $currentPage === 'housekeeping' ? 'text-primary' : 'text-slate-400 hover:text-primary transition-colors'; ?>">
                <span class="material-symbols-outlined text-[26px] <?php echo $currentPage === 'housekeeping' ? 'fill-1' : ''; ?>">cleaning_services</span>
                <span class="nav-text text-[10px] <?php echo $currentPage === 'housekeeping' ? 'font-semibold' : 'font-medium'; ?> whitespace-nowrap">Housekeeping</span>
            </a>

            <!-- More -->
            <div class="relative">
                <button onclick="toggleMoreMenu(event)"
                    class="flex flex-col items-center gap-1 p-3 min-w-[64px] <?php echo $currentPage === 'more' ? 'text-primary' : 'text-slate-400 hover:text-primary transition-colors'; ?>">
                    <span class="material-symbols-outlined text-[26px] <?php echo $currentPage === 'more' ? 'fill-1' : ''; ?>">more_horiz</span>
                    <span class="nav-text text-[10px] <?php echo $currentPage === 'more' ? 'font-semibold' : 'font-medium'; ?> whitespace-nowrap">More</span>
                </button>

                <!-- Floating Submenu -->
                <div id="moreMenu" class="hidden absolute bottom-full right-0 mb-2 w-48 bg-white dark:bg-slate-800 rounded-xl shadow-2xl border border-slate-200 dark:border-slate-700 overflow-hidden z-50">
                    <div class="py-2">
                        <a href="nearmeplace.php<?php echo $id_cabang_nav > 0 ? '?id_cabang=' . $id_cabang_nav : ''; ?>"
                           class="flex items-center gap-3 px-4 py-3 text-slate-700 dark:text-slate-200 hover:bg-primary/10 hover:text-primary transition-colors">
                            <span class="material-symbols-outlined text-xl">near_me</span>
                            <span class="text-sm font-medium">Near Me</span>
                        </a>
                        <a href="pages.php<?php echo $id_cabang_nav > 0 ? '?id_cabang=' . $id_cabang_nav : ''; ?>"
                           class="flex items-center gap-3 px-4 py-3 text-slate-700 dark:text-slate-200 hover:bg-primary/10 hover:text-primary transition-colors">
                            <span class="material-symbols-outlined text-xl">description</span>
                            <span class="text-sm font-medium">Pages</span>
                        </a>
                        <a href="other.php<?php echo $id_cabang_nav > 0 ? '?id_cabang=' . $id_cabang_nav : ''; ?>"
                           class="flex items-center gap-3 px-4 py-3 text-slate-700 dark:text-slate-200 hover:bg-primary/10 hover:text-primary transition-colors">
                            <span class="material-symbols-outlined text-xl">apps</span>
                            <span class="text-sm font-medium">Other</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleMoreMenu(event) {
        event.stopPropagation();
        const menu = document.getElementById('moreMenu');
        const isOpen = !menu.classList.contains('hidden');
        
        // Close all other menus first
        document.querySelectorAll('#moreMenu').forEach(m => m.classList.add('hidden'));
        
        // Toggle current menu
        if (!isOpen) {
            menu.classList.remove('hidden');
        }
    }

    // Close menu when clicking outside
    document.addEventListener('click', function(event) {
        const menu = document.getElementById('moreMenu');
        const button = event.closest('.relative') || event.target.closest('.relative');
        
        if (menu && !menu.contains(event.target) && !button) {
            menu.classList.add('hidden');
        }
    });

    // Close menu on escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            document.getElementById('moreMenu').classList.add('hidden');
        }
    });
</script>
