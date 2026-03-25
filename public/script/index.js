// Toggle account dropdown
function toggleDropdown() {
    const menu = document.getElementById('dropdown-menu');
    menu.classList.toggle('show');
}

// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
    const dropdown = document.querySelector('.account-dropdown');
    const menu = document.getElementById('dropdown-menu');
    if (dropdown && !dropdown.contains(e.target)) {
        menu.classList.remove('show');
    }
});

// Profile Modal Functions
const profileModal = document.getElementById('profile-modal');
let currentGuestData = null;

// Open profile modal and load data
async function openProfileModal() {
    toggleDropdown(); // Close dropdown
    profileModal.classList.add('active');
    document.body.style.overflow = 'hidden';

    // Show loading state
    const submitBtn = document.getElementById('btn-update-profile');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="material-symbols-outlined spinner text-sm">sync</span><span>Loading...</span>';

    try {
        const response = await fetch('../api/guest/detail.php');
        const result = await response.json();

        if (result.success) {
            currentGuestData = result.data;
            // Populate form
            document.getElementById('profile-nama').value = result.data.nama_lengkap || '';
            document.getElementById('profile-email').value = result.data.email || '';
            document.getElementById('profile-wa').value = result.data.wa || '';
            document.getElementById('profile-kota').value = result.data.kota || '';
            document.getElementById('profile-points').textContent = (result.data.total_point || 0).toLocaleString();
            document.getElementById('profile-password').value = '';

            // Reset button
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<span class="material-symbols-outlined text-sm">save</span><span>Update Profile</span>';
        } else {
            showToast('Failed to load profile data', 'error');
            closeProfileModal();
        }
    } catch (err) {
        showToast('Error loading profile', 'error');
        closeProfileModal();
    }
}

// Close profile modal
function closeProfileModal() {
    profileModal.classList.remove('active');
    document.body.style.overflow = '';
    currentGuestData = null;
}

// Close modal when clicking outside
profileModal.addEventListener('click', function(e) {
    if (e.target === profileModal) {
        closeProfileModal();
    }
});

// Close modal on ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && profileModal.classList.contains('active')) {
        closeProfileModal();
    }
});

// Toggle password visibility
document.getElementById('toggle-profile-password').addEventListener('click', function() {
    const passwordInput = document.getElementById('profile-password');
    const icon = this.querySelector('span');

    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.textContent = 'visibility_off';
    } else {
        passwordInput.type = 'password';
        icon.textContent = 'visibility';
    }
});

// Update profile handler
document.getElementById('btn-update-profile').addEventListener('click', async function() {
    const nama = document.getElementById('profile-nama').value.trim();
    const email = document.getElementById('profile-email').value.trim();
    const wa = document.getElementById('profile-wa').value.trim();
    const kota = document.getElementById('profile-kota').value.trim();
    const password = document.getElementById('profile-password').value.trim();

    // Validation
    if (!nama || !wa) {
        showToast('Name and WhatsApp are required', 'error');
        return;
    }

    if (!password) {
        showToast('Password is required for verification', 'error');
        return;
    }

    if (!currentGuestData) {
        showToast('Profile data not loaded', 'error');
        return;
    }

    const submitBtn = this;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="material-symbols-outlined spinner text-sm">sync</span><span>Updating...</span>';

    const formData = new FormData();
    formData.append('id_guest', currentGuestData.id_guest);
    formData.append('nama_lengkap', nama);
    formData.append('email', email);
    formData.append('wa', wa);
    formData.append('kota', kota);
    formData.append('password', password);

    try {
        const response = await fetch('../api/guest/update.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();

        if (result.success) {
            showToast('Profile updated successfully!', 'success');
            closeProfileModal();
        } else {
            showToast(result.message || 'Failed to update profile', 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<span class="material-symbols-outlined text-sm">save</span><span>Update Profile</span>';
        }
    } catch (err) {
        showToast('Error updating profile', 'error');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<span class="material-symbols-outlined text-sm">save</span><span>Update Profile</span>';
    }
});

// Toast notification function
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 z-[60] flex items-center gap-3 px-5 py-4 rounded-lg shadow-lg transform transition-all duration-300 translate-x-full ${
        type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
    }`;

    const icon = type === 'success' ? 'check_circle' : 'error';
    toast.innerHTML = `
        <span class="material-symbols-outlined">${icon}</span>
        <span class="font-medium text-sm">${message}</span>
    `;

    document.body.appendChild(toast);

    setTimeout(() => toast.classList.remove('translate-x-full'), 10);
    setTimeout(() => {
        toast.classList.add('translate-x-full');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Add click handler for Account menu item
document.querySelector('#dropdown-menu a[href="#"]').addEventListener('click', function(e) {
    e.preventDefault();
    openProfileModal();
});

// Logout confirmation modal
const logoutModal = document.getElementById('logout-modal');
const logoutBtn = document.getElementById('logout-btn');

// Open logout modal
function openLogoutModal() {
    toggleDropdown(); // Close dropdown
    logoutModal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

// Close logout modal
function closeLogoutModal() {
    logoutModal.classList.remove('active');
    document.body.style.overflow = '';
}

// Close modal when clicking outside
logoutModal.addEventListener('click', function(e) {
    if (e.target === logoutModal) {
        closeLogoutModal();
    }
});

// Close modal on ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        if (logoutModal.classList.contains('active')) {
            closeLogoutModal();
        }
        if (profileModal.classList.contains('active')) {
            closeProfileModal();
        }
        if (linkModal.classList.contains('active')) {
            closeLinkModal();
        }
    }
});

// Handle logout button click
if (logoutBtn) {
    logoutBtn.addEventListener('click', function(e) {
        e.preventDefault();
        openLogoutModal();
    });
}

// Make functions globally accessible
window.closeLogoutModal = closeLogoutModal;

// Link Confirmation Modal
const linkModal = document.getElementById('link-modal');
let pendingLinkUrl = '';

// Open link modal
function openLinkModal(url) {
    pendingLinkUrl = url;
    linkModal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

// Close link modal
function closeLinkModal() {
    linkModal.classList.remove('active');
    document.body.style.overflow = '';
    pendingLinkUrl = '';
}

// Close modal when clicking outside
if (linkModal) {
    linkModal.addEventListener('click', function(e) {
        if (e.target === linkModal) {
            closeLinkModal();
        }
    });
}

// Handle confirm link button
document.getElementById('confirm-link-btn').addEventListener('click', function(e) {
    e.preventDefault();
    if (pendingLinkUrl) {
        window.open(pendingLinkUrl, '_blank');
        closeLinkModal();
    }
});

// Make functions globally accessible
window.openLinkModal = openLinkModal;
window.closeLinkModal = closeLinkModal;

// Load branches from API
async function loadBranches() {
    const loading = document.getElementById('loading');
    const error = document.getElementById('error');
    const container = document.getElementById('branches-container');

    try {
        const response = await fetch('../api/cabang/list.php');
        const result = await response.json();

        loading.classList.add('hidden');
        error.classList.add('hidden');

        if (result.success && result.data && result.data.length > 0) {
            container.innerHTML = result.data.map(branch => `
                <div class="bg-white dark:bg-slate-900 rounded-xl overflow-hidden shadow-sm border border-slate-200 dark:border-slate-800 branch-card" data-name="${branch.nama_cabang.toLowerCase()}">
                    <div class="relative h-48 w-full bg-slate-200 dark:bg-slate-800 bg-center bg-cover cursor-pointer"
                        style="background-image: url('../images/${branch.foto || 'default-branch.jpg'}');"
                        onclick="window.location.href='kamar.php?id_cabang=${branch.id_cabang}'">
                        <div class="absolute bottom-3 left-3 px-2 py-1 bg-primary text-white text-xs font-bold rounded">
                            ${branch.kode_cabang || 'N/A'}
                        </div>
                    </div>
                    <div class="p-4">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <h3 class="text-lg font-bold leading-tight">${branch.nama_cabang}</h3>
                                <p class="text-slate-500 text-sm flex items-center gap-1">
                                    <span class="material-symbols-outlined text-xs">location_on</span>
                                    ${branch.alamat || 'No address available'}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 mt-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                            <a class="flex-1 flex items-center justify-center gap-2 py-2.5 bg-slate-100 dark:bg-slate-800 rounded-lg text-slate-700 dark:text-slate-200 text-sm font-semibold"
                                href="${branch.gps ? 'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(branch.gps) : '#'}"
                                target="_blank">
                                <span class="material-symbols-outlined text-primary text-lg">map</span>
                                View Map
                            </a>
                            <a class="flex-1 flex items-center justify-center gap-2 py-2.5 bg-green-500/10 dark:bg-green-500/20 rounded-lg text-green-600 dark:text-green-400 text-sm font-semibold"
                                href="${branch.hp ? 'https://wa.me/' + branch.hp.replace(/[^0-9]/g, '') : '#'}"
                                target="_blank">
                                <span class="material-symbols-outlined text-lg">chat</span>
                                WhatsApp
                            </a>
                        </div>
                    </div>
                </div>
            `).join('');

            // Load stays after branches are loaded
            loadStays();
            // Load halaman after stays are loaded
            loadHalaman();
        } else {
            container.innerHTML = `
                <div class="flex flex-col items-center justify-center py-12">
                    <span class="material-symbols-outlined text-slate-400 text-5xl mb-4">folder_open</span>
                    <p class="text-slate-500 dark:text-slate-400">No branches found</p>
                </div>
            `;
        }
    } catch (err) {
        loading.classList.add('hidden');
        error.classList.remove('hidden');
        error.classList.add('flex');
        document.getElementById('error-message').textContent = 'Failed to load branches. Please check your connection.';
        container.innerHTML = '';
    }
}

// Load halaman from API
async function loadHalaman() {
    const section = document.getElementById('follow-us-section');
    const container = document.getElementById('halaman-container');
    const countLabel = document.getElementById('halaman-count');

    try {
        const response = await fetch('../api/halaman/list.php?aktif=1');
        const result = await response.json();

        if (result.success && result.data && result.data.length > 0) {
            section.classList.remove('hidden');
            const count = result.data.length;
            countLabel.textContent = count + ' Page' + (count !== 1 ? 's' : '');
            container.innerHTML = result.data.map(item => {
                const escapedLink = item.link.replace(/'/g, "\\'");
                return `
                <a class="shrink-0 block transition-transform active:scale-95" href="#" onclick="openLinkModal('${escapedLink}'); return false;">
                    <img alt="${item.nama_halaman}"
                        class="rounded-[10px] w-[270px] h-[90px] object-cover shadow-sm border border-slate-200 dark:border-slate-800"
                        src="../images/${item.logo || 'default-logo.jpg'}" />
                </a>
            `}).join('');
        } else {
            section.classList.add('hidden');
        }
    } catch (err) {
        console.error('Error loading halaman:', err);
        section.classList.add('hidden');
    }
}

// Search functionality
document.getElementById('search-input').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const cards = document.querySelectorAll('.branch-card');

    cards.forEach(card => {
        const name = card.dataset.name;
        if (name.includes(searchTerm)) {
            card.classList.remove('hidden');
        } else {
            card.classList.add('hidden');
        }
    });
});

// Filter buttons
document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.filter-btn').forEach(b => {
            b.classList.remove('bg-primary', 'text-white');
            b.classList.add('bg-slate-200', 'dark:bg-slate-800');
        });
        this.classList.remove('bg-slate-200', 'dark:bg-slate-800');
        this.classList.add('bg-primary', 'text-white');

        if (this.dataset.filter === 'near') {
            // For now, just show all - can be enhanced with geolocation
            alert('Near Me feature requires location access');
        }
    });
});

// Load branches on page load
document.addEventListener('DOMContentLoaded', loadBranches);

// Load your stays from API
async function loadStays() {
    const loading = document.getElementById('stay-loading');
    const error = document.getElementById('stay-error');
    const container = document.getElementById('stay-container');
    const empty = document.getElementById('stay-empty');
    const section = document.getElementById('your-stay-section');

    // Get id_guest from session (passed from PHP)
    const idGuest = window.sessionGuestId || null;

    if (!idGuest) {
        loading.classList.add('hidden');
        container.classList.add('hidden');
        empty.classList.remove('hidden');
        empty.classList.add('flex');
        return;
    }

    try {
        const response = await fetch(`../api/inap/list.php?id_guest=${idGuest}`);
        const result = await response.json();

        loading.classList.add('hidden');
        error.classList.add('hidden');

        if (result.success && result.data && result.data.length > 0) {
            // Show section and take only top 3
            const stays = result.data.slice(0, 3);
            const totalStays = result.data.length;

            // Update stay count label
            const countLabel = document.getElementById('stay-count');
            countLabel.textContent = totalStays + ' Stay' + (totalStays !== 1 ? 's' : '');

            container.innerHTML = stays.map(stay => {
                const statusClass = stay.status === 0 ? 'staying' : 'completed';
                const statusIcon = stay.status === 0 ? 'home_work' : 'hotel';
                const hasReceipt = stay.link_receipt && stay.link_receipt !== null;

                return `
                <div class="stay-card">
                    <div class="flex flex-col justify-between flex-grow w-full">
                        <div>
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-[0.15em] mb-1">
                                        ${escapeHtml(stay.nama_cabang)}
                                    </p>
                                    <h3 class="font-display font-bold text-lg text-slate-900 dark:text-slate-100 leading-tight">
                                        ${escapeHtml(stay.nama_tipe)}
                                    </h3>
                                    <p class="text-xs font-semibold text-primary/70 uppercase tracking-widest mt-0.5">
                                        Room ${escapeHtml(stay.nomor_kamar)}
                                    </p>
                                </div>
                                ${stay.ota ? `<span class="bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded text-[10px] font-bold text-slate-600 dark:text-slate-300 uppercase tracking-tighter">${escapeHtml(stay.ota)}</span>` : ''}
                            </div>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-3 flex items-center gap-1">
                                <span class="material-symbols-outlined text-xs" style="font-variation-settings: 'FILL' 1;">calendar_today</span>
                                ${formatDate(stay.tanggal_in)} - ${formatDate(stay.tanggal_out)}
                            </p>
                        </div>
                        <div class="flex justify-between items-end mt-4">
                            <div class="flex items-center gap-1.5 stay-status ${statusClass}">
                                <span class="material-symbols-outlined text-sm">${statusIcon}</span>
                                <span>${statusClass}</span>
                            </div>
                            ${hasReceipt 
                                ? `<a href="../receipt/${stay.link_receipt}" target="_blank" class="stay-receipt-btn has-receipt">
                                    <span class="material-symbols-outlined text-base">download</span>
                                    Receipt
                                   </a>`
                                : `<button class="stay-receipt-btn no-receipt" onclick="showToast('Receipt not available', 'error')">
                                    <span class="material-symbols-outlined text-base">download</span>
                                    Receipt
                                   </button>`
                            }
                        </div>
                    </div>
                </div>
            `}).join('');

            container.classList.remove('hidden');
            empty.classList.add('hidden');
        } else {
            loading.classList.add('hidden');
            container.classList.add('hidden');
            empty.classList.remove('hidden');
            empty.classList.add('flex');
        }
    } catch (err) {
        loading.classList.add('hidden');
        error.classList.remove('hidden');
        error.classList.add('flex');
        document.getElementById('stay-error-message').textContent = 'Failed to load stays. Please check your connection.';
        container.classList.add('hidden');
    }
}

// Helper function to escape HTML
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Helper function to format date
function formatDate(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    return `${date.getDate()} ${months[date.getMonth()]} ${date.getFullYear()}`;
}
