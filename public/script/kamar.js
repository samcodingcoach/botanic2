// Global variable to store all rooms data
let allRoomsData = [];

// Load rooms from API based on id_cabang
async function loadRooms() {
    const loading = document.getElementById('loading');
    const error = document.getElementById('error');
    const container = document.getElementById('rooms-container');
    const branchNameEl = document.getElementById('branch-name');

    console.log('loadRooms called with ID_CABANG:', window.ID_CABANG);

    if (!window.ID_CABANG) {
        error.classList.remove('hidden');
        error.classList.add('flex');
        document.getElementById('error-message').textContent = 'Invalid branch ID';
        loading.classList.add('hidden');
        return;
    }

    try {
        const apiUrl = `../api/cabangtipekamar/list.php?id_cabang=${window.ID_CABANG}`;
        console.log('Fetching from API:', apiUrl);

        const response = await fetch(apiUrl);
        console.log('Response status:', response.status);

        const result = await response.json();
        console.log('API Result:', result);

        loading.classList.add('hidden');
        error.classList.add('hidden');

        if (result.success && result.data && result.data.length > 0) {
            // Store all rooms data for searching
            allRoomsData = result.data;
            console.log('✅ Rooms loaded:', allRoomsData.length, 'rooms');
            console.log('Sample data:', allRoomsData[0]);

            // Set branch name from first result with "Room" prefix
            branchNameEl.textContent = 'Room ' + result.data[0].nama_cabang;

            // Clear existing content
            const existingCards = container.querySelectorAll('.room-card, .no-results-message');
            existingCards.forEach(card => card.remove());

            // Create room cards
            allRoomsData.forEach(room => {
                const card = createRoomCard(room);
                container.appendChild(card);
            });

            // Initialize read more buttons
            initReadMoreButtons();
        } else {
            branchNameEl.textContent = 'Room - No Rooms Found';
            container.innerHTML = `
                <div class="flex flex-col items-center justify-center py-12">
                    <span class="material-symbols-outlined text-slate-400 text-5xl mb-4">meeting_room</span>
                    <p class="text-slate-500 dark:text-slate-400">No rooms available for this branch</p>
                </div>
            `;
        }
    } catch (err) {
        loading.classList.add('hidden');
        error.classList.remove('hidden');
        error.classList.add('flex');
        document.getElementById('error-message').textContent = 'Failed to load rooms. Please check your connection.';
        console.error('Error loading rooms:', err);
    }
}

// Create room card element
function createRoomCard(room) {
    const card = document.createElement('div');
    card.className = 'room-card bg-white dark:bg-slate-900 rounded-2xl overflow-hidden border border-slate-100 dark:border-slate-700 shadow-md';
    card.dataset.namaTipe = (room.nama_tipe || '').toLowerCase();
    card.dataset.keteranganTipe = (room.keterangan_tipe || '').toLowerCase();
    card.dataset.keteranganAkomodasi = (room.keterangan_akomodasi || '').toLowerCase();

    // YouTube embed
    let youtubeEmbed = '';
    if (room.link_youtube) {
        youtubeEmbed = `
            <div class="w-full">
                <div class="relative w-full overflow-hidden">
                    <div class="relative pb-[56.25%] h-0">
                        <iframe class="absolute top-0 left-0 w-full h-full"
                            src="https://www.youtube.com/embed/${room.link_youtube}?si=T2o-F83IovRysU4v"
                            title="YouTube video player"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            referrerpolicy="strict-origin-when-cross-origin"
                            allowfullscreen>
                        </iframe>
                    </div>
                </div>
            </div>
        `;
    }

    const descriptionText = room.keterangan_akomodasi || room.keterangan_tipe || 'No description available';
    const truncatedText = truncateText(descriptionText, 200);

    card.innerHTML = `
        <div class="w-full aspect-[16/9] bg-center bg-no-repeat bg-cover"
            style="background-image: url('../images/${room.gambar || 'default-room.jpg'}');">
        </div>
        <div class="p-5 space-y-4">
            <div class="flex justify-between items-start">
                <div>
                    <h3 class="text-xl font-bold text-slate-900 dark:text-slate-100 nama-tipe">${room.nama_tipe || 'Room Type'}</h3>
                    <p class="text-slate-500 dark:text-slate-400 text-sm mt-1 flex items-center gap-1 keterangan-tipe">
                        <span class="material-symbols-outlined text-xs">room</span>
                        ${room.keterangan_tipe || ''}
                    </p>
                </div>
                <span class="bg-primary/10 text-primary text-[10px] font-bold px-2 py-1 rounded uppercase tracking-wider flex items-center gap-1">
                    <span class="material-symbols-outlined text-xs">check_circle</span>
                    Available
                </span>
            </div>
            ${room.keterangan_akomodasi ? `
            <div class="text-slate-600 dark:text-slate-300 text-sm keterangan-akomodasi">
                <p class="description leading-relaxed">${truncatedText}</p>
                ${descriptionText.length > 200 ? `
                <button class="read-more-btn text-primary text-xs font-semibold mt-2 hover:text-primary/80" onclick="toggleReadMore(this)" data-full="${escapeHtml(descriptionText)}">
                    Read more
                </button>
                ` : ''}
            </div>
            ` : ''}
            ${room.link_youtube ? `
            <div class="flex justify-between items-center pt-2 border-t border-slate-100 dark:border-slate-800">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Video Room Tour</p>
                <a href="https://www.youtube.com/watch?v=${room.link_youtube}" target="_blank" class="text-xs font-semibold hover:opacity-80 flex items-center gap-1" style="color: #FF0000;">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="#FF0000" xmlns="http://www.w3.org/2000/svg">
                        <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                    </svg>
                    Watch on YouTube
                </a>
            </div>
            ` : ''}
        </div>
        ${youtubeEmbed}
    `;

    return card;
}

// Load rooms on page load
document.addEventListener('DOMContentLoaded', loadRooms);

// Truncate text
function truncateText(text, charLimit) {
    if (text.length <= charLimit) return text;
    return text.substring(0, charLimit) + '...';
}

// Escape HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Toggle read more
function toggleReadMore(button) {
    const description = button.previousElementSibling;
    const isExpanded = button.textContent === 'Show less';
    const fullText = button.dataset.full;

    if (isExpanded) {
        description.textContent = truncateText(fullText, 200);
        button.textContent = 'Read more';
    } else {
        description.textContent = fullText;
        button.textContent = 'Show less';
    }
}

// Initialize read more buttons
function initReadMoreButtons() {
    const buttons = document.querySelectorAll('.read-more-btn');
    buttons.forEach(button => {
        const fullText = button.dataset.full;
        if (fullText.length > 200) {
            button.style.display = 'block';
        } else {
            button.style.display = 'none';
        }
    });
}

// Highlight text
function highlightText(text, searchTerm) {
    if (!searchTerm) return text;
    const regex = new RegExp(`(${searchTerm})`, 'gi');
    return text.replace(regex, '<span class="highlight-text">$1</span>');
}

// Search functions - inline expandable search
function toggleSearch() {
    const searchContainer = document.getElementById('search-container');
    const branchName = document.getElementById('branch-name');
    const searchInput = document.getElementById('search-input');
    const searchBtn = document.getElementById('search-btn');
    
    if (!searchContainer || !branchName || !searchInput) return;
    
    const isExpanded = !searchContainer.classList.contains('hidden');
    
    if (isExpanded) {
        // Close search - show branch name, hide search
        searchContainer.classList.add('hidden');
        branchName.classList.remove('hidden');
        searchInput.value = '';
        filterRooms('');
        searchBtn.innerHTML = '<span class="material-symbols-outlined text-2xl font-bold">search</span>';
    } else {
        // Open search - hide branch name, show search input
        branchName.classList.add('hidden');
        searchContainer.classList.remove('hidden');
        setTimeout(() => searchInput.focus(), 100);
        searchBtn.innerHTML = '<span class="material-symbols-outlined text-2xl font-bold">close</span>';
    }
}

function clearSearch() {
    const searchInput = document.getElementById('search-input');
    if (searchInput) {
        searchInput.value = '';
        searchInput.focus();
    }
    filterRooms('');
}

// Filter rooms based on search - filters cards in main container
// Search only in: nama_tipe and keterangan_akomodasi (exact phrase, bukan per kata)
window.filterRooms = function(searchTerm) {
    console.log('filterRooms called with:', searchTerm);
    console.log('allRoomsData count:', allRoomsData.length);

    const container = document.getElementById('rooms-container');
    const cards = container.querySelectorAll('.room-card');
    const clearBtn = document.getElementById('clear-search');

    // Show/hide clear button
    if (clearBtn) {
        if (searchTerm && searchTerm.trim()) {
            clearBtn.classList.remove('hidden');
        } else {
            clearBtn.classList.add('hidden');
        }
    }

    // If no search term, show all cards
    if (!searchTerm || !searchTerm.trim()) {
        // Remove no-results message and search info
        const noResultsMsg = container.querySelector('.no-results-message');
        const searchInfo = container.querySelector('.search-info');
        if (noResultsMsg) noResultsMsg.remove();
        if (searchInfo) searchInfo.remove();

        // Show all cards and re-render to remove highlights
        cards.forEach(card => {
            card.classList.remove('hidden');
            card.style.display = '';
        });

        if (allRoomsData.length > 0) {
            // Re-render all cards
            const existingCards = container.querySelectorAll('.room-card');
            existingCards.forEach(card => card.remove());

            allRoomsData.forEach(room => {
                const card = createRoomCard(room);
                container.appendChild(card);
            });

            initReadMoreButtons();
        }
        return;
    }

    const searchLower = searchTerm.toLowerCase().trim();
    let visibleCount = 0;
    const matchedRoomTypes = []; // Track matched room type names

    cards.forEach(card => {
        const namaTipe = card.dataset.namaTipe || '';
        // Don't search in keterangan_tipe, only nama_tipe and keterangan_akomodasi
        const keteranganAkomodasi = card.dataset.keteranganAkomodasi || '';

        // Exact phrase match - cari kalimat lengkap
        const matchesNamaTipe = namaTipe.includes(searchLower);
        const matchesKeteranganAkomodasi = keteranganAkomodasi.includes(searchLower);
        
        const matchesSearch = matchesNamaTipe || matchesKeteranganAkomodasi;

        if (matchesSearch) {
            card.classList.remove('hidden');
            card.style.display = '';
            visibleCount++;
            
            // Track matched room type (without duplicates)
            const roomTypeName = card.querySelector('.nama-tipe')?.textContent?.trim() || 'Unknown';
            if (!matchedRoomTypes.includes(roomTypeName)) {
                matchedRoomTypes.push(roomTypeName);
            }

            // Highlight exact phrase match
            const namaTipeEl = card.querySelector('.nama-tipe');
            const keteranganAkomodasiEl = card.querySelector('.keterangan-akomodasi');

            if (namaTipeEl && matchesNamaTipe) {
                const regex = new RegExp(`(${searchLower.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
                namaTipeEl.innerHTML = namaTipeEl.textContent.replace(regex, '<span class="highlight-text">$1</span>');
            }
            
            if (keteranganAkomodasiEl && matchesKeteranganAkomodasi) {
                const regex = new RegExp(`(${searchLower.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
                keteranganAkomodasiEl.innerHTML = keteranganAkomodasiEl.textContent.replace(regex, '<span class="highlight-text">$1</span>');
            }
        } else {
            card.classList.add('hidden');
            card.style.display = 'none';
        }
    });

    // Remove existing search info and no-results message
    const existingSearchInfo = container.querySelector('.search-info');
    const noResultsMsg = container.querySelector('.no-results-message');
    if (existingSearchInfo) existingSearchInfo.remove();
    if (noResultsMsg) noResultsMsg.remove();

    // Show search info or no results message
    if (visibleCount === 0) {
        const newNoResultsMsg = document.createElement('div');
        newNoResultsMsg.className = 'no-results-message flex flex-col items-center justify-center py-12';
        newNoResultsMsg.innerHTML = `
            <span class="material-symbols-outlined text-slate-400 text-5xl mb-4">search_off</span>
            <p class="text-slate-500 dark:text-slate-400">No rooms found matching "${searchTerm}"</p>
            <p class="text-slate-400 dark:text-slate-500 text-sm mt-2">Try different keywords</p>
        `;
        container.appendChild(newNoResultsMsg);
    } else if (visibleCount > 0) {
        // Show search info with matched room types
        const searchInfoEl = document.createElement('div');
        searchInfoEl.className = 'search-info flex items-center justify-between bg-slate-100 dark:bg-slate-800 rounded-lg px-4 py-3 mb-4';
        searchInfoEl.innerHTML = `
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-lg">check_circle</span>
                <p class="text-sm text-slate-700 dark:text-slate-300">
                    <span class="font-bold text-primary">${visibleCount}</span> item${visibleCount > 1 ? 's' : ''} found
                </p>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400">
                ${matchedRoomTypes.join(', ')}
            </p>
        `;
        container.insertBefore(searchInfoEl, container.firstChild);
    }

    console.log(`Showing ${visibleCount} of ${cards.length} rooms`);
    console.log(`Matched room types: ${matchedRoomTypes.join(', ')}`);
};

// Initialize search - input event listener and Escape key
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('search-input');
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            filterRooms(e.target.value);
        });
        
        // Close search on Escape key
        searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                toggleSearch();
            }
        });
    }
});
