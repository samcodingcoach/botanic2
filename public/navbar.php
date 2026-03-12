<?php
// Get current page name
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
// Get id_cabang from URL parameter
$id_cabang_nav = isset($_GET['id_cabang']) ? (int) $_GET['id_cabang'] : 0;
?>
<!-- Bottom Navigation Bar -->
<div class="fixed bottom-0 left-0 right-0 bg-white/95 dark:bg-slate-900/95 backdrop-blur-xl border-t border-slate-200 dark:border-slate-700 px-2 py-2 flex items-center justify-around z-50">
    <!-- Room -->
    <a href="kamar.php<?php echo $id_cabang_nav > 0 ? '?id_cabang=' . $id_cabang_nav : ''; ?>"
       class="flex flex-col items-center gap-1 p-2 <?php echo $currentPage === 'kamar' ? 'text-primary' : 'text-slate-400 hover:text-primary transition-colors'; ?>">
        <span class="material-symbols-outlined text-[26px] <?php echo $currentPage === 'kamar' ? 'fill-1' : ''; ?>">bed</span>
        <span class="text-[10px] <?php echo $currentPage === 'kamar' ? 'font-semibold' : 'font-medium'; ?>">Room</span>
    </a>

    <!-- Facility -->
    <a href="fasilitas.php<?php echo $id_cabang_nav > 0 ? '?id_cabang=' . $id_cabang_nav : ''; ?>"
       class="flex flex-col items-center gap-1 p-2 <?php echo $currentPage === 'fasilitas' ? 'text-primary' : 'text-slate-400 hover:text-primary transition-colors'; ?>">
        <span class="material-symbols-outlined text-[26px] <?php echo $currentPage === 'fasilitas' ? 'fill-1' : ''; ?>">pool</span>
        <span class="text-[10px] <?php echo $currentPage === 'fasilitas' ? 'font-semibold' : 'font-medium'; ?>">Facility</span>
    </a>

    <!-- Receptionist -->
    <a href="receptionist.php<?php echo $id_cabang_nav > 0 ? '?id_cabang=' . $id_cabang_nav : ''; ?>"
       class="flex flex-col items-center gap-1 p-2 <?php echo $currentPage === 'receptionist' ? 'text-primary' : 'text-slate-400 hover:text-primary transition-colors'; ?>">
        <span class="material-symbols-outlined text-[26px] <?php echo $currentPage === 'receptionist' ? 'fill-1' : ''; ?>">concierge</span>
        <span class="text-[10px] <?php echo $currentPage === 'receptionist' ? 'font-semibold' : 'font-medium'; ?>">Receptionist</span>
    </a>

    <!-- Housekeeping -->
    <a href="housekeeping.php<?php echo $id_cabang_nav > 0 ? '?id_cabang=' . $id_cabang_nav : ''; ?>"
       class="flex flex-col items-center gap-1 p-2 <?php echo $currentPage === 'housekeeping' ? 'text-primary' : 'text-slate-400 hover:text-primary transition-colors'; ?>">
        <span class="material-symbols-outlined text-[26px] <?php echo $currentPage === 'housekeeping' ? 'fill-1' : ''; ?>">cleaning_services</span>
        <span class="text-[10px] <?php echo $currentPage === 'housekeeping' ? 'font-semibold' : 'font-medium'; ?>">Housekeeping</span>
    </a>

    <!-- More -->
    <a href="more.php<?php echo $id_cabang_nav > 0 ? '?id_cabang=' . $id_cabang_nav : ''; ?>"
       class="flex flex-col items-center gap-1 p-2 <?php echo $currentPage === 'more' ? 'text-primary' : 'text-slate-400 hover:text-primary transition-colors'; ?>">
        <span class="material-symbols-outlined text-[26px] <?php echo $currentPage === 'more' ? 'fill-1' : ''; ?>">more_horiz</span>
        <span class="text-[10px] <?php echo $currentPage === 'more' ? 'font-semibold' : 'font-medium'; ?>">More</span>
    </a>
</div>
