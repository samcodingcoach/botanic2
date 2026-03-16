<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get current page name
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
// Get id_cabang from URL parameter
$id_cabang_nav = isset($_GET['id_cabang']) ? (int) $_GET['id_cabang'] : 0;

// Get user info from session
$displayName = '';
$userType = '';
if (isset($_SESSION['username'])) {
    $displayName = $_SESSION['username'];
    $userType = 'User';
} elseif (isset($_SESSION['nama_lengkap'])) {
    $displayName = $_SESSION['nama_lengkap'];
    $userType = 'Guest';
}
$initials = strtoupper(substr($displayName, 0, 2));
?>

<!-- Bottom Navigation Container -->
<div id="navbarContainer" class="fixed bottom-0 left-0 right-0 z-50">
    <!-- Collapsible Navigation Menu -->
    <div id="navbarContent" class="bg-white/95 dark:bg-slate-900/95 backdrop-blur-xl border-t border-slate-200 dark:border-slate-700 px-2 py-2 transition-all duration-300 ease-in-out">
        <div class="flex flex-row items-center justify-around">
            <!-- Room -->
            <a href="kamar.php<?php echo $id_cabang_nav > 0 ? '?id_cabang=' . $id_cabang_nav : ''; ?>"
               class="nav-item flex flex-col items-center gap-1 p-3 min-w-[64px] <?php echo $currentPage === 'kamar' ? 'text-primary' : 'text-slate-400 hover:text-primary transition-colors'; ?>">
                <span class="material-symbols-outlined text-[26px] <?php echo $currentPage === 'kamar' ? 'fill-1' : ''; ?>">bed</span>
                <span class="nav-text text-[10px] <?php echo $currentPage === 'kamar' ? 'font-semibold' : 'font-medium'; ?> whitespace-nowrap">Room</span>
            </a>

            <!-- Facility -->
            <a href="fasilitas.php<?php echo $id_cabang_nav > 0 ? '?id_cabang=' . $id_cabang_nav : ''; ?>"
               class="nav-item flex flex-col items-center gap-1 p-3 min-w-[64px] <?php echo $currentPage === 'fasilitas' ? 'text-primary' : 'text-slate-400 hover:text-primary transition-colors'; ?>">
                <span class="material-symbols-outlined text-[26px] <?php echo $currentPage === 'fasilitas' ? 'fill-1' : ''; ?>">pool</span>
                <span class="nav-text text-[10px] <?php echo $currentPage === 'fasilitas' ? 'font-semibold' : 'font-medium'; ?> whitespace-nowrap">Facility</span>
            </a>

            <!-- Receptionist -->
            <a href="receptionist.php<?php echo $id_cabang_nav > 0 ? '?id_cabang=' . $id_cabang_nav : ''; ?>"
               class="nav-item flex flex-col items-center gap-1 p-3 min-w-[64px] <?php echo $currentPage === 'receptionist' ? 'text-primary' : 'text-slate-400 hover:text-primary transition-colors'; ?>">
                <span class="material-symbols-outlined text-[26px] <?php echo $currentPage === 'receptionist' ? 'fill-1' : ''; ?>">concierge</span>
                <span class="nav-text text-[10px] <?php echo $currentPage === 'receptionist' ? 'font-semibold' : 'font-medium'; ?> whitespace-nowrap">Receptionist</span>
            </a>

            <!-- Housekeeping -->
            <a href="housekeeping.php<?php echo $id_cabang_nav > 0 ? '?id_cabang=' . $id_cabang_nav : ''; ?>"
               class="nav-item flex flex-col items-center gap-1 p-3 min-w-[64px] <?php echo $currentPage === 'housekeeping' ? 'text-primary' : 'text-slate-400 hover:text-primary transition-colors'; ?>">
                <span class="material-symbols-outlined text-[26px] <?php echo $currentPage === 'housekeeping' ? 'fill-1' : ''; ?>">cleaning_services</span>
                <span class="nav-text text-[10px] <?php echo $currentPage === 'housekeeping' ? 'font-semibold' : 'font-medium'; ?> whitespace-nowrap">Housekeeping</span>
            </a>

            <!-- More -->
            <a href="more.php<?php echo $id_cabang_nav > 0 ? '?id_cabang=' . $id_cabang_nav : ''; ?>"
               class="nav-item flex flex-col items-center gap-1 p-3 min-w-[64px] <?php echo $currentPage === 'more' ? 'text-primary' : 'text-slate-400 hover:text-primary transition-colors'; ?>">
                <span class="material-symbols-outlined text-[26px] <?php echo $currentPage === 'more' ? 'fill-1' : ''; ?>">more_horiz</span>
                <span class="nav-text text-[10px] <?php echo $currentPage === 'more' ? 'font-semibold' : 'font-medium'; ?> whitespace-nowrap">More</span>
            </a>
        </div>
    </div>

    <!-- Footer with User Info and Toggle (Always Visible) -->
    <div class="bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-700 px-4 py-3 flex items-center justify-between">
        <!-- User Info -->
        <div class="flex items-center gap-3 overflow-hidden">
            <div class="w-10 h-10 rounded-full bg-primary/20 flex items-center justify-center text-primary font-bold text-sm shrink-0">
                <?php echo htmlspecialchars($initials); ?>
            </div>
            <div class="flex flex-col user-info-text">
                <span class="text-sm font-semibold text-slate-900 dark:text-slate-100 truncate"><?php echo htmlspecialchars($displayName); ?></span>
                <span class="text-xs text-slate-500 dark:text-slate-400 truncate"><?php echo htmlspecialchars($userType); ?></span>
            </div>
        </div>

        <!-- Toggle & Logout -->
        <div class="flex items-center gap-2">
            <button onclick="toggleNavbar()"
                class="flex items-center justify-center w-10 h-10 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors shadow-lg"
                title="Toggle Navigation">
                <span class="material-symbols-outlined text-xl" id="toggleIcon">menu</span>
            </button>
            <a href="logout.php"
                class="flex items-center justify-center w-10 h-10 bg-red-500/10 dark:bg-red-500/20 text-red-500 rounded-lg hover:bg-red-500/20 dark:hover:bg-red-500/30 transition-colors"
                title="Logout">
                <span class="material-symbols-outlined text-xl">logout</span>
            </a>
        </div>
    </div>
</div>

<script>
    let isNavbarExpanded = true;

    function toggleNavbar() {
        const navbarContent = document.getElementById('navbarContent');
        const navItems = document.querySelectorAll('.nav-item');
        const navTexts = document.querySelectorAll('.nav-text');
        const userInfoText = document.querySelector('.user-info-text');
        const toggleIcon = document.getElementById('toggleIcon');

        isNavbarExpanded = !isNavbarExpanded;

        if (isNavbarExpanded) {
            // Expand - show text labels
            navbarContent.classList.remove('navbar-collapsed');
            navItems.forEach(item => {
                item.classList.remove('navbar-item-collapsed');
            });
            navTexts.forEach(text => {
                text.classList.remove('hidden');
            });
            if (userInfoText) userInfoText.classList.remove('hidden');
            toggleIcon.textContent = 'menu_open';
            localStorage.setItem('navbarExpanded', 'true');
        } else {
            // Collapse - hide text labels, show icons only
            navbarContent.classList.add('navbar-collapsed');
            navItems.forEach(item => {
                item.classList.add('navbar-item-collapsed');
            });
            navTexts.forEach(text => {
                text.classList.add('hidden');
            });
            if (userInfoText) userInfoText.classList.add('hidden');
            toggleIcon.textContent = 'menu';
            localStorage.setItem('navbarExpanded', 'false');
        }
    }

    // Initialize navbar state from localStorage
    document.addEventListener('DOMContentLoaded', function() {
        const savedState = localStorage.getItem('navbarExpanded');
        const navbarContent = document.getElementById('navbarContent');
        const navItems = document.querySelectorAll('.nav-item');
        const navTexts = document.querySelectorAll('.nav-text');
        const userInfoText = document.querySelector('.user-info-text');
        const toggleIcon = document.getElementById('toggleIcon');

        if (savedState === 'false') {
            isNavbarExpanded = false;
            navbarContent.classList.add('navbar-collapsed');
            navItems.forEach(item => {
                item.classList.add('navbar-item-collapsed');
            });
            navTexts.forEach(text => {
                text.classList.add('hidden');
            });
            if (userInfoText) userInfoText.classList.add('hidden');
            toggleIcon.textContent = 'menu';
        }
    });
</script>

<style>
    /* Collapsed state styles */
    .navbar-collapsed {
        padding-left: 0.5rem !important;
        padding-right: 0.5rem !important;
    }

    .navbar-item-collapsed {
        min-width: 48px !important;
        padding-left: 0.5rem !important;
        padding-right: 0.5rem !important;
    }

    /* Smooth transitions */
    .nav-item,
    .nav-text {
        transition: all 0.3s ease-in-out;
    }
</style>
