<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Housekeeping Service Chat</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;display=swap"
        rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@100..700,0..1&amp;display=swap"
        rel="stylesheet" />
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap"
        rel="stylesheet" />
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#135bec",
                        "background-light": "#f6f6f8",
                        "background-dark": "#101622",
                    },
                    fontFamily: {
                        "display": ["Inter"]
                    },
                    borderRadius: { "DEFAULT": "0.25rem", "lg": "0.5rem", "xl": "0.75rem", "full": "9999px" },
                },
            },
        }
    </script>
    <style>
        body {
            min-height: max(884px, 100dvh);
        }
    </style>
</head>

<body class="bg-background-light dark:bg-background-dark font-display text-slate-900 dark:text-slate-100">
    <div class="relative flex min-h-screen w-full flex-col overflow-x-hidden">
        <!-- Header -->
        <header
            class="sticky top-0 z-10 flex items-center bg-background-light/80 dark:bg-background-dark/80 backdrop-blur-md p-4 justify-between border-b border-slate-200 dark:border-slate-800">
            <div class="flex size-10 shrink-0 items-center justify-start cursor-pointer" onclick="history.back()">
                <span class="material-symbols-outlined text-slate-900 dark:text-slate-100">arrow_back_ios</span>
            </div>
            <h1
                class="text-slate-900 dark:text-slate-100 text-lg font-bold leading-tight tracking-tight flex-1 text-center">
                Housekeeping</h1>
            <div class="flex w-10 items-center justify-end">
            </div>
        </header>
        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto pb-20">
            <div class="px-4 py-4">
                <div id="staff-list" class="space-y-4">
                    <!-- Staff items will be loaded here -->
                </div>
            </div>
        </main>
        <?php include 'navbar.php'; ?>
    </div>

    <script>
        // Get id_cabang from URL parameter
        const urlParams = new URLSearchParams(window.location.search);
        const idCabang = urlParams.get('id_cabang') || '';

        // Status color mapping
        const statusColors = {
            '1': 'bg-emerald-500',    // aktif
            '0': 'bg-red-500',        // tidak aktif
            '2': 'bg-yellow-500',     // sibuk
            '3': 'bg-gray-400'        // offline
        };

        // Fetch housekeeping data
        async function fetchHousekeeping() {
            try {
                const url = idCabang 
                    ? `../api/hk/list.php?id_cabang=${idCabang}`
                    : '../api/hk/list.php';
                
                const response = await fetch(url);
                const result = await response.json();

                if (result.success && result.data) {
                    renderStaff(result.data);
                } else {
                    document.getElementById('staff-list').innerHTML = `
                        <div class="text-center py-8 text-slate-500 dark:text-slate-400">
                            <p>No staff available</p>
                        </div>
                    `;
                }
            } catch (error) {
                console.error('Error fetching housekeeping data:', error);
                document.getElementById('staff-list').innerHTML = `
                    <div class="text-center py-8 text-red-500">
                        <p>Failed to load staff data</p>
                    </div>
                `;
            }
        }

        // Render staff list
        function renderStaff(data) {
            const staffList = document.getElementById('staff-list');
            
            if (data.length === 0) {
                staffList.innerHTML = `
                    <div class="text-center py-8 text-slate-500 dark:text-slate-400">
                        <p>No staff available</p>
                    </div>
                `;
                return;
            }

            staffList.innerHTML = data.map(staff => {
                const avatarBg = staff.jenis_kelamin == '1' 
                    ? 'bg-slate-200 dark:bg-slate-700' 
                    : 'bg-pink-200 dark:bg-pink-900';
                const avatarIconColor = staff.jenis_kelamin == '1'
                    ? 'text-slate-400 dark:text-slate-500'
                    : 'text-pink-400 dark:text-pink-500';
                const statusColor = statusColors[staff.aktif] || 'bg-gray-400';

                return `
                    <div class="flex gap-4 bg-white dark:bg-slate-900 p-4 rounded-xl shadow-sm border border-slate-100 dark:border-slate-800 items-center">
                        <div class="shrink-0 relative">
                            <div class="size-14 rounded-full ${avatarBg} border-2 border-primary/20 flex items-center justify-center">
                                <span class="material-symbols-outlined ${avatarIconColor}">person</span>
                            </div>
                            <div class="absolute bottom-0 right-0 size-3 ${statusColor} border-2 border-white dark:border-slate-900 rounded-full" title="Status: ${getStatusText(staff.aktif)}"></div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-bold truncate">${escapeHtml(staff.nama_lengkap)}</p>
                            <p class="text-slate-500 dark:text-slate-400 text-xs flex items-center gap-1">
                                <span class="material-symbols-outlined text-xs">badge</span> ${escapeHtml(staff.jabatan)}
                            </p>
                        </div>
                        <div class="shrink-0">
                            <button onclick="openWhatsApp('${escapeHtml(staff.wa)}')"
                                class="flex items-center justify-center gap-2 bg-[#25D366] text-white px-4 py-2 rounded-lg text-sm font-semibold hover:opacity-90 transition-colors">
                                <span class="material-symbols-outlined text-sm">chat</span>
                                <span>Chat</span>
                            </button>
                        </div>
                    </div>
                `;
            }).join('');
        }

        // Get status text
        function getStatusText(status) {
            const statusMap = {
                '1': 'Aktif',
                '0': 'Tidak Aktif',
                '2': 'Sibuk',
                '3': 'Offline'
            };
            return statusMap[status] || 'Unknown';
        }

        // Escape HTML to prevent XSS
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Open WhatsApp chat
        function openWhatsApp(phoneNumber) {
            if (phoneNumber) {
                const cleanNumber = phoneNumber.replace(/[^\d+]/g, '');
                window.open(`https://wa.me/${cleanNumber}`, '_blank');
            }
        }

        // Initialize
        fetchHousekeeping();
    </script>
</body>

</html>
