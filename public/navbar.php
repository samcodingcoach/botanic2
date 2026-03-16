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
    <!-- Footer with User Info (Always Visible) -->
    <div class="bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-700 px-4 py-3 flex items-center justify-between">
        <!-- User Info -->
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-primary/20 flex items-center justify-center text-primary font-bold text-sm">
                <?php echo htmlspecialchars($initials); ?>
            </div>
            <div class="flex flex-col">
                <span class="text-sm font-semibold text-slate-900 dark:text-slate-100"><?php echo htmlspecialchars($displayName); ?></span>
                <span class="text-xs text-slate-500 dark:text-slate-400"><?php echo htmlspecialchars($userType); ?></span>
            </div>
        </div>
        
        <!-- Navigation Toggle & Logout -->
        <div class="flex items-center gap-2">
            <button onclick="toggleNavbar()" 
                class="flex items-center gap-1 px-3 py-2 bg-primary/10 dark:bg-primary/20 text-primary rounded-lg hover:bg-primary/20 dark:hover:bg-primary/30 transition-colors">
                <span class="material-symbols-outlined text-sm" id="toggleIcon">expand_less</span>
                <span class="text-xs font-medium hidden sm:inline" id="toggleText">Menu</span>
            </button>
            <a href="logout.php" 
                class="p-2 text-slate-400 hover:text-red-500 transition-colors" 
                title="Logout">
                <span class="material-symbols-outlined text-xl">logout</span>
            </a>
        </div>
    </div>
    
    <!-- Collapsible Navigation Menu -->
    <div id="navbarContent" class="bg-white/95 dark:bg-slate-900/95 backdrop-blur-xl border-t border-slate-200 dark:border-slate-700 px-2 py-2 transition-all duration-300 ease-in-out overflow-hidden" style="max-height: 0;">
        <div class="flex flex-row items-center justify-around overflow-x-auto scrollbar-thin scrollbar-thumb-slate-300 dark:scrollbar-thumb-slate-600">
            <!-- Room -->
            <a href="kamar.php<?php echo $id_cabang_nav > 0 ? '?id_cabang=' . $id_cabang_nav : ''; ?>"
               class="flex flex-col items-center gap-1 p-3 min-w-[64px] <?php echo $currentPage === 'kamar' ? 'text-primary' : 'text-slate-400 hover:text-primary transition-colors'; ?>">
                <span class="material-symbols-outlined text-[26px] <?php echo $currentPage === 'kamar' ? 'fill-1' : ''; ?>">bed</span>
                <span class="text-[10px] <?php echo $currentPage === 'kamar' ? 'font-semibold' : 'font-medium'; ?>">Room</span>
            </a>

            <!-- Facility -->
            <a href="fasilitas.php<?php echo $id_cabang_nav > 0 ? '?id_cabang=' . $id_cabang_nav : ''; ?>"
               class="flex flex-col items-center gap-1 p-3 min-w-[64px] <?php echo $currentPage === 'fasilitas' ? 'text-primary' : 'text-slate-400 hover:text-primary transition-colors'; ?>">
                <span class="material-symbols-outlined text-[26px] <?php echo $currentPage === 'fasilitas' ? 'fill-1' : ''; ?>">pool</span>
                <span class="text-[10px] <?php echo $currentPage === 'fasilitas' ? 'font-semibold' : 'font-medium'; ?>">Facility</span>
            </a>

            <!-- Receptionist -->
            <a href="receptionist.php<?php echo $id_cabang_nav > 0 ? '?id_cabang=' . $id_cabang_nav : ''; ?>"
               class="flex flex-col items-center gap-1 p-3 min-w-[64px] <?php echo $currentPage === 'receptionist' ? 'text-primary' : 'text-slate-400 hover:text-primary transition-colors'; ?>">
                <span class="material-symbols-outlined text-[26px] <?php echo $currentPage === 'receptionist' ? 'fill-1' : ''; ?>">concierge</span>
                <span class="text-[10px] <?php echo $currentPage === 'receptionist' ? 'font-semibold' : 'font-medium'; ?>">Receptionist</span>
            </a>

            <!-- Housekeeping -->
            <a href="housekeeping.php<?php echo $id_cabang_nav > 0 ? '?id_cabang=' . $id_cabang_nav : ''; ?>"
               class="flex flex-col items-center gap-1 p-3 min-w-[64px] <?php echo $currentPage === 'housekeeping' ? 'text-primary' : 'text-slate-400 hover:text-primary transition-colors'; ?>">
                <span class="material-symbols-outlined text-[26px] <?php echo $currentPage === 'housekeeping' ? 'fill-1' : ''; ?>">cleaning_services</span>
                <span class="text-[10px] <?php echo $currentPage === 'housekeeping' ? 'font-semibold' : 'font-medium'; ?>">Housekeeping</span>
            </a>

            <!-- More -->
            <a href="more.php<?php echo $id_cabang_nav > 0 ? '?id_cabang=' . $id_cabang_nav : ''; ?>"
               class="flex flex-col items-center gap-1 p-3 min-w-[64px] <?php echo $currentPage === 'more' ? 'text-primary' : 'text-slate-400 hover:text-primary transition-colors'; ?>">
                <span class="material-symbols-outlined text-[26px] <?php echo $currentPage === 'more' ? 'fill-1' : ''; ?>">more_horiz</span>
                <span class="text-[10px] <?php echo $currentPage === 'more' ? 'font-semibold' : 'font-medium'; ?>">More</span>
            </a>
        </div>
    </div>
</div>

<script>
    let isNavbarExpanded = false;

    function toggleNavbar() {
        console.log('toggleNavbar called, current state:', isNavbarExpanded);
        const navbarContent = document.getElementById('navbarContent');
        const toggleIcon = document.getElementById('toggleIcon');
        const toggleText = document.getElementById('toggleText');

        isNavbarExpanded = !isNavbarExpanded;
        console.log('New state:', isNavbarExpanded);

        if (isNavbarExpanded) {
            // Expand - show navigation menu
            navbarContent.style.maxHeight = '120px';
            navbarContent.classList.add('overflow-y-auto');
            document.body.classList.add('navbar-expanded');
            toggleIcon.textContent = 'expand_less';
            if (toggleText) toggleText.textContent = 'Hide';
            console.log('Navbar expanded');
        } else {
            // Collapse - hide navigation menu
            navbarContent.style.maxHeight = '0';
            navbarContent.classList.remove('overflow-y-auto');
            document.body.classList.remove('navbar-expanded');
            toggleIcon.textContent = 'expand_more';
            if (toggleText) toggleText.textContent = 'Menu';
            console.log('Navbar collapsed');
        }
    }
    
    // Debug: Log when navbar is loaded
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Navbar loaded successfully');
        console.log('navbarContent element:', document.getElementById('navbarContent'));
        console.log('toggleIcon element:', document.getElementById('toggleIcon'));
    });
</script>

<style>
    /* Add extra padding to main content when navbar is expanded */
    body.navbar-expanded main,
    body.navbar-expanded .main-content,
    body.navbar-expanded #rooms-container,
    body.navbar-expanded #facilities-container,
    body.navbar-expanded #receptionist-container,
    body.navbar-expanded #housekeeping-container {
        padding-bottom: 200px !important;
        transition: padding-bottom 0.3s ease-in-out;
    }
</style>
