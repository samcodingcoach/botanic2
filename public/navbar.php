<?php
// Get current page name
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!-- Bottom Navigation Bar -->
<div class="fixed bottom-0 left-0 right-0 bg-white/95 dark:bg-slate-900/95 backdrop-blur-xl border-t border-slate-200 dark:border-slate-700 px-2 py-2 flex items-center justify-around z-50">
    <!-- Room -->
    <a href="kamar.php<?php echo isset($_GET['id_cabang']) ? '?id_cabang=' . $id_cabang : ''; ?>" 
       class="flex flex-col items-center gap-1 p-2 <?php echo $currentPage === 'kamar' ? 'text-primary' : 'text-slate-400 hover:text-primary transition-colors'; ?>">
        <span class="material-symbols-outlined text-[26px] <?php echo $currentPage === 'kamar' ? 'fill-1' : ''; ?>">bed</span>
        <span class="text-[10px] <?php echo $currentPage === 'kamar' ? 'font-semibold' : 'font-medium'; ?>">Room</span>
    </a>
    
    <!-- Facility -->
    <a href="fasilitas.php<?php echo isset($_GET['id_cabang']) ? '?id_cabang=' . $id_cabang : ''; ?>" 
       class="flex flex-col items-center gap-1 p-2 <?php echo $currentPage === 'fasilitas' ? 'text-primary' : 'text-slate-400 hover:text-primary transition-colors'; ?>">
        <span class="material-symbols-outlined text-[26px] <?php echo $currentPage === 'fasilitas' ? 'fill-1' : ''; ?>">pool</span>
        <span class="text-[10px] <?php echo $currentPage === 'fasilitas' ? 'font-semibold' : 'font-medium'; ?>">Facility</span>
    </a>
    
    <!-- Receptionist -->
    <a href="receptionist.php<?php echo isset($_GET['id_cabang']) ? '?id_cabang=' . $id_cabang : ''; ?>" 
       class="flex flex-col items-center gap-1 p-2 text-slate-400 hover:text-primary transition-colors">
        <span class="material-symbols-outlined text-[26px]">concierge</span>
        <span class="text-[10px] font-medium">Receptionist</span>
    </a>
    
    <!-- Housekeeping -->
    <a href="housekeeping.php<?php echo isset($_GET['id_cabang']) ? '?id_cabang=' . $id_cabang : ''; ?>" 
       class="flex flex-col items-center gap-1 p-2 text-slate-400 hover:text-primary transition-colors">
        <span class="material-symbols-outlined text-[26px]">cleaning_services</span>
        <span class="text-[10px] font-medium">Housekeeping</span>
    </a>
    
    <!-- More -->
    <a href="more.php<?php echo isset($_GET['id_cabang']) ? '?id_cabang=' . $id_cabang : ''; ?>" 
       class="flex flex-col items-center gap-1 p-2 text-slate-400 hover:text-primary transition-colors">
        <span class="material-symbols-outlined text-[26px]">more_horiz</span>
        <span class="text-[10px] font-medium">More</span>
    </a>
</div>
