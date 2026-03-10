<!-- Bottom Navigation Bar -->
<div class="fixed bottom-0 left-0 right-0 bg-white/95 dark:bg-slate-900/95 backdrop-blur-xl border-t border-slate-200 dark:border-slate-700 px-2 py-2 flex items-center justify-around z-50">
    <a href="kamar.php<?php echo isset($_GET['id_cabang']) ? '?id_cabang=' . $id_cabang : ''; ?>" class="flex flex-col items-center gap-1 p-2 text-primary">
        <span class="material-symbols-outlined text-[26px] fill-1">bed</span>
        <span class="text-[10px] font-semibold">Room</span>
    </a>
    <a href="fasilitas.php<?php echo isset($_GET['id_cabang']) ? '?id_cabang=' . $id_cabang : ''; ?>" class="flex flex-col items-center gap-1 p-2 text-slate-400 hover:text-primary transition-colors">
        <span class="material-symbols-outlined text-[26px]">pool</span>
        <span class="text-[10px] font-medium">Facility</span>
    </a>
    <a href="receptionist.php<?php echo isset($_GET['id_cabang']) ? '?id_cabang=' . $id_cabang : ''; ?>" class="flex flex-col items-center gap-1 p-2 text-slate-400 hover:text-primary transition-colors">
        <span class="material-symbols-outlined text-[26px]">concierge</span>
        <span class="text-[10px] font-medium">Receptionist</span>
    </a>
    <a href="housekeeping.php<?php echo isset($_GET['id_cabang']) ? '?id_cabang=' . $id_cabang : ''; ?>" class="flex flex-col items-center gap-1 p-2 text-slate-400 hover:text-primary transition-colors">
        <span class="material-symbols-outlined text-[26px]">cleaning_services</span>
        <span class="text-[10px] font-medium">Housekeeping</span>
    </a>
    <a href="more.php<?php echo isset($_GET['id_cabang']) ? '?id_cabang=' . $id_cabang : ''; ?>" class="flex flex-col items-center gap-1 p-2 text-slate-400 hover:text-primary transition-colors">
        <span class="material-symbols-outlined text-[26px]">more_horiz</span>
        <span class="text-[10px] font-medium">More</span>
    </a>
</div>
