<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Determine base path for sidebar links
$basePath = '/botanic/admin/';

// Get user data from session
$username = $_SESSION['username'] ?? 'User';
$initials = strtoupper(substr($username, 0, 2));
?>
<!-- Mobile Sidebar Overlay -->
<div id="sidebarOverlay" class="sidebar-overlay fixed inset-0 bg-black/50 z-40 md:hidden"
    onclick="toggleSidebar()"></div>

<!-- Sidebar -->
<aside id="sidebar"
    class="fixed md:static inset-y-0 left-0 z-50 w-64 bg-white dark:bg-background-dark border-r border-slate-200 dark:border-slate-800 flex flex-col transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out">
    <div class="p-6 flex items-center gap-3">
        <div class="bg-primary p-1.5 rounded-lg">
            <span class="material-symbols-outlined text-white">account_balance</span>
        </div>
        <h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">Botanic</h1>
    </div>
    <nav class="flex-1 px-4 space-y-1 mt-4">
        <a class="nav-item flex items-center gap-3 px-4 py-3 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-lg transition-colors"
            href="<?php echo $basePath; ?>index.php" data-page="dashboard">
            <span class="material-symbols-outlined">dashboard</span>
            <span class="text-sm font-medium">Dashboard</span>
        </a>
        <a class="nav-item flex items-center gap-3 px-4 py-3 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-lg transition-colors"
            href="<?php echo $basePath; ?>users/users.php" data-page="users">
            <span class="material-symbols-outlined">people</span>
            <span class="text-sm font-medium">Users</span>
        </a>
        <a class="nav-item flex items-center gap-3 px-4 py-3 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-lg transition-colors"
            href="<?php echo $basePath; ?>cabang/cabang.php" data-page="cabang">
            <span class="material-symbols-outlined">storefront</span>
            <span class="text-sm font-medium">Cabang</span>
        </a>
        <a class="nav-item flex items-center gap-3 px-4 py-3 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-lg transition-colors"
            href="<?php echo $basePath; ?>tipekamar/tipekamar.php" data-page="tipekamar">
            <span class="material-symbols-outlined">bed</span>
            <span class="text-sm font-medium">Tipe Kamar</span>
        </a>
        <a class="nav-item flex items-center gap-3 px-4 py-3 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-lg transition-colors"
            href="<?php echo $basePath; ?>cabangtipekamar/cabangtipekamar.php" data-page="cabangtipekamar">
            <span class="material-symbols-outlined">hotel</span>
            <span class="text-sm font-medium">Akomodasi</span>
        </a>
        <a class="nav-item flex items-center gap-3 px-4 py-3 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-lg transition-colors"
            href="<?php echo $basePath; ?>fasilitas/fasilitas.php" data-page="fasilitas">
            <span class="material-symbols-outlined">pool</span>
            <span class="text-sm font-medium">Fasilitas</span>
        </a>
        <a class="nav-item flex items-center gap-3 px-4 py-3 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-lg transition-colors"
            href="#" data-page="settings">
            <span class="material-symbols-outlined">settings</span>
            <span class="text-sm font-medium">Settings</span>
        </a>
    </nav>
    <div class="p-4 mt-auto border-t border-slate-200 dark:border-slate-800">
        <form id="logoutForm" action="<?php echo $basePath; ?>logout.php" method="POST">
            <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 cursor-pointer"
                onclick="document.getElementById('logoutForm').submit();">
                <div
                    class="w-10 h-10 rounded-full bg-primary/20 flex items-center justify-center text-primary font-bold">
                    <?php echo htmlspecialchars($initials); ?></div>
                <div class="flex-1 overflow-hidden">
                    <p class="text-sm font-semibold truncate"><?php echo htmlspecialchars($username); ?></p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 truncate">Administrator</p>
                </div>
                <button type="submit" class="text-slate-400 hover:text-red-500 transition-colors" title="Logout">
                    <span class="material-symbols-outlined text-sm">logout</span>
                </button>
            </div>
        </form>
    </div>
</aside>

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const isClosed = sidebar.classList.contains('-translate-x-full');

        if (isClosed) {
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.add('active');
        } else {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.remove('active');
        }
    }

    // Highlight active sidebar item based on current page
    document.addEventListener('DOMContentLoaded', function() {
        const currentPage = window.location.pathname.split('/').pop() || 'index.php';
        const navItems = document.querySelectorAll('.nav-item');

        navItems.forEach(item => {
            const href = item.getAttribute('href');
            if (href && href !== '#') {
                const hrefPage = href.split('/').pop();
                // Exact match for active state
                if (currentPage === hrefPage) {
                    item.classList.add('bg-primary', 'text-white');
                    item.classList.remove('text-slate-600', 'dark:text-slate-400', 'hover:bg-slate-50', 'dark:hover:bg-slate-800');
                }
            }
        });
    });
</script>
