// Global variable to store all facilities data
let allFacilitiesData = [];

// Load facilities from API based on id_cabang
async function loadFacilities() {
    const loading = document.getElementById('loading');
    const error = document.getElementById('error');
    const container = document.getElementById('facilities-container');
    const branchNameEl = document.getElementById('branch-name');

    console.log('loadFacilities called with ID_CABANG:', window.ID_CABANG);

    if (!window.ID_CABANG) {
        error.classList.remove('hidden');
        error.classList.add('flex');
        document.getElementById('error-message').textContent = 'Invalid branch ID';
        loading.classList.add('hidden');
        return;
    }

    try {
        const apiUrl = `../api/fasilitas/list.php?id_cabang=${window.ID_CABANG}`;
        console.log('Fetching from API:', apiUrl);

        const response = await fetch(apiUrl);
        console.log('Response status:', response.status);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const result = await response.json();
        console.log('API Result:', result);

        loading.classList.add('hidden');
        error.classList.add('hidden');

        if (result.success && result.data && result.data.length > 0) {
            // Store all facilities data
            allFacilitiesData = result.data;
            console.log('✅ Facilities loaded:', allFacilitiesData.length, 'facilities');
            console.log('Sample data:', allFacilitiesData[0]);

            // Set branch name from first result
            branchNameEl.textContent = result.data[0].nama_cabang;

            // Clear existing content
            const existingContent = container.querySelectorAll('.facility-card, .no-results-message');
            existingContent.forEach(content => content.remove());

            // Create facility cards
            allFacilitiesData.forEach((facility, index) => {
                const card = createFacilityCard(facility, index);
                container.appendChild(card);
            });
        } else {
            branchNameEl.textContent = 'No Facilities Found';
            container.innerHTML = `
                <div class="flex flex-col items-center justify-center py-12 mx-4">
                    <span class="material-symbols-outlined text-slate-400 text-5xl mb-4">pool</span>
                    <p class="text-slate-500 dark:text-slate-400">No facilities available for this branch</p>
                </div>
            `;
        }
    } catch (err) {
        loading.classList.add('hidden');
        error.classList.remove('hidden');
        error.classList.add('flex');
        document.getElementById('error-message').textContent = 'Failed to load facilities. Please check your connection.';
        console.error('Error loading facilities:', err);
    }
}

// Create facility card element
function createFacilityCard(facility, index) {
    const card = document.createElement('div');
    card.className = 'facility-card bg-white dark:bg-slate-900 rounded-xl overflow-hidden shadow-sm border border-slate-100 dark:border-slate-800 mx-4';
    // Add data attributes for search
    card.dataset.namaFasilitas = (facility.nama_fasilitas || '').toLowerCase();
    card.dataset.deskripsi = (facility.deskripsi || '').toLowerCase();

    // Determine status labels
    const activeLabel = facility.aktif == 1 ? 'Active' : 'Inactive';
    const activeClass = facility.aktif == 1 ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400';
    
    // Format price with IDR and thousand separator
    let priceLabel = '';
    let priceClass = '';
    if (facility.status_free == 1) {
        priceLabel = 'Free';
        priceClass = 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400';
    } else if (facility.range_harga) {
        // Remove non-numeric characters and format
        const numericPrice = String(facility.range_harga).replace(/[^0-9]/g, '');
        const formattedPrice = parseInt(numericPrice).toLocaleString('id-ID');
        priceLabel = `Start from IDR ${formattedPrice}`;
        priceClass = 'bg-primary/10 text-primary dark:bg-primary/20';
    } else {
        priceLabel = 'Paid';
        priceClass = 'bg-primary/10 text-primary dark:bg-primary/20';
    }

    // Build images array - always include both images if they exist
    const images = [];
    if (facility.gambar1) images.push(facility.gambar1);
    if (facility.gambar2) images.push(facility.gambar2);

    // Generate slider dots
    const dotsHtml = images.map((_, i) =>
        `<span class="w-2 h-2 rounded-full ${i === 0 ? 'bg-white' : 'bg-white/50'} shadow-md"></span>`
    ).join('');

    // Generate image slides
    const slidesHtml = images.map((img, i) => `
        <div class="min-w-full h-full snap-start relative">
            <div class="w-full h-full bg-center bg-cover cursor-pointer"
                data-alt="${facility.nama_fasilitas}"
                data-image="../images/${img}"
                style='background-image: url("../images/${img}")'
                onclick="openImagePreview(this.dataset.image, this.dataset.alt)">
            </div>
        </div>
    `).join('');

    // Slider ID
    const sliderId = `slider-${index}`;

    // Process description with read more if more than 30 words
    const descriptionText = facility.deskripsi || 'No description available';
    const wordCount = descriptionText.trim().split(/\s+/).length;
    const showReadMore = wordCount > 30;
    
    // Get first 30 words for truncated version
    const truncatedDesc = showReadMore 
        ? descriptionText.trim().split(/\s+/).slice(0, 30).join(' ') + '...' 
        : descriptionText;

    card.innerHTML = `
        <!-- Image Slider with Navigation -->
        <div class="relative overflow-hidden h-52">
            <div id="${sliderId}" class="flex overflow-x-auto snap-x snap-mandatory hide-scrollbar h-full scroll-smooth">
                ${slidesHtml}
            </div>
            ${images.length > 1 ? `
            <!-- Slider Navigation Buttons -->
            <button onclick="slideImage('${sliderId}', -1)" class="absolute left-2 top-1/2 -translate-y-1/2 w-10 h-10 flex items-center justify-center rounded-full bg-slate-900/50 dark:bg-slate-900/50 backdrop-blur-sm hover:bg-slate-900/70 transition-all">
                <span class="material-symbols-outlined text-white">chevron_left</span>
            </button>
            <button onclick="slideImage('${sliderId}', 1)" class="absolute right-2 top-1/2 -translate-y-1/2 w-10 h-10 flex items-center justify-center rounded-full bg-slate-900/50 dark:bg-slate-900/50 backdrop-blur-sm hover:bg-slate-900/70 transition-all">
                <span class="material-symbols-outlined text-white">chevron_right</span>
            </button>
            ` : ''}
            <!-- Slider Dots Indicator -->
            ${images.length > 1 ? `
            <div class="absolute bottom-3 left-1/2 -translate-x-1/2 flex gap-1.5">
                ${dotsHtml}
            </div>
            ` : ''}
        </div>
        <!-- Card Content -->
        <div class="p-4">
            <h3 class="text-lg font-bold mb-3">${facility.nama_fasilitas}</h3>
            <div class="description-container mb-3">
                <p class="description-text text-sm text-slate-600 dark:text-slate-400 leading-relaxed mb-2">
                    ${showReadMore ? truncatedDesc : descriptionText}
                </p>
                ${showReadMore ? `
                <button class="read-more-btn text-primary text-xs font-semibold hover:text-primary/80 transition-colors" onclick="window.toggleReadMore(this)" data-full="${escapeHtml(descriptionText)}">
                    Read more
                </button>
                ` : ''}
            </div>
            <!-- Badges at bottom, aligned horizontally with read more -->
            <div class="flex items-center gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                <span class="px-2 py-1 rounded-full ${activeClass} text-[10px] font-bold uppercase tracking-wider">${activeLabel}</span>
                <span class="px-2 py-1 rounded-full ${priceClass} text-[10px] font-bold uppercase tracking-wider">${priceLabel}</span>
            </div>
        </div>
    `;

    return card;
}

// Toggle search - expand/collapse search input
window.toggleSearch = function() {
    const searchContainer = document.getElementById('search-container');
    const branchName = document.getElementById('branch-name');
    const searchInput = document.getElementById('search-input');
    const searchBtn = document.getElementById('search-btn');
    
    if (!searchContainer || !branchName || !searchInput) return;
    
    const isExpanded = !searchContainer.classList.contains('hidden');
    
    if (isExpanded) {
        // Close search - show branch name, hide search input
        searchContainer.classList.add('hidden');
        branchName.classList.remove('hidden');
        searchInput.value = '';
        filterFacilities('');
        searchBtn.innerHTML = '<span class="material-symbols-outlined text-2xl font-bold">search</span>';
    } else {
        // Open search - hide branch name, show search input
        branchName.classList.add('hidden');
        searchContainer.classList.remove('hidden');
        setTimeout(() => searchInput.focus(), 100);
        searchBtn.innerHTML = '<span class="material-symbols-outlined text-2xl font-bold">close</span>';
    }
};

// Clear search
window.clearSearch = function() {
    const searchInput = document.getElementById('search-input');
    if (searchInput) {
        searchInput.value = '';
        searchInput.focus();
    }
    filterFacilities('');
};

// Filter facilities based on search - searches in nama_fasilitas and deskripsi
window.filterFacilities = function(searchTerm) {
    console.log('filterFacilities called with:', searchTerm);
    console.log('allFacilitiesData count:', allFacilitiesData.length);

    const container = document.getElementById('facilities-container');
    const cards = container.querySelectorAll('.facility-card');
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
        // Remove no-results message
        const noResultsMsg = container.querySelector('.no-results-message');
        if (noResultsMsg) noResultsMsg.remove();

        // Show all cards and re-render to remove highlights
        cards.forEach(card => {
            card.classList.remove('hidden');
            card.style.display = '';
        });

        if (allFacilitiesData.length > 0) {
            // Re-render all cards
            const existingCards = container.querySelectorAll('.facility-card');
            existingCards.forEach(card => card.remove());

            allFacilitiesData.forEach((facility, index) => {
                const card = createFacilityCard(facility, index);
                container.appendChild(card);
            });
        }
        return;
    }

    const searchLower = searchTerm.toLowerCase().trim();
    let visibleCount = 0;
    const matchedFacilities = [];

    cards.forEach(card => {
        const namaFasilitas = card.dataset.namaFasilitas || '';
        const deskripsi = card.dataset.deskripsi || '';

        const matchesSearch = namaFasilitas.includes(searchLower) || deskripsi.includes(searchLower);

        if (matchesSearch) {
            card.classList.remove('hidden');
            card.style.display = '';
            visibleCount++;
            
            // Track matched facility name
            const facilityName = card.querySelector('h3')?.textContent?.trim() || 'Unknown';
            if (!matchedFacilities.includes(facilityName)) {
                matchedFacilities.push(facilityName);
            }

            // Highlight matching text
            const namaFasilitasEl = card.querySelector('h3');
            const deskripsiEl = card.querySelector('.description-text');

            if (namaFasilitasEl && namaFasilitasEl.textContent.toLowerCase().includes(searchLower)) {
                namaFasilitasEl.innerHTML = highlightText(namaFasilitasEl.textContent, searchTerm);
            }
            if (deskripsiEl && deskripsiEl.textContent.toLowerCase().includes(searchLower)) {
                deskripsiEl.innerHTML = highlightText(deskripsiEl.textContent, searchTerm);
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
        newNoResultsMsg.className = 'no-results-message flex flex-col items-center justify-center py-12 mx-4';
        newNoResultsMsg.innerHTML = `
            <span class="material-symbols-outlined text-slate-400 text-5xl mb-4">search_off</span>
            <p class="text-slate-500 dark:text-slate-400">No facilities found matching "${searchTerm}"</p>
            <p class="text-slate-400 dark:text-slate-500 text-sm mt-2">Try different keywords</p>
        `;
        container.appendChild(newNoResultsMsg);
    } else if (visibleCount > 0) {
        // Show search info with matched facility names
        const searchInfoEl = document.createElement('div');
        searchInfoEl.className = 'search-info flex items-center justify-between bg-slate-100 dark:bg-slate-800 rounded-lg px-4 py-3 mb-4 mx-4';
        searchInfoEl.innerHTML = `
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-lg">check_circle</span>
                <p class="text-sm text-slate-700 dark:text-slate-300">
                    <span class="font-bold text-primary">${visibleCount}</span> item${visibleCount > 1 ? 's' : ''} found
                </p>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400">
                ${matchedFacilities.join(', ')}
            </p>
        `;
        container.insertBefore(searchInfoEl, container.firstChild);
    }

    console.log(`Showing ${visibleCount} of ${cards.length} facilities`);
    console.log(`Matched facilities: ${matchedFacilities.join(', ')}`);
};

// Highlight text function
function highlightText(text, searchTerm) {
    if (!searchTerm) return text;
    const regex = new RegExp(`(${searchTerm.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
    return text.replace(regex, '<span class="highlight-text">$1</span>');
}

// Toggle read more / show less
window.toggleReadMore = function(button) {
    const descriptionText = button.previousElementSibling;
    const isExpanded = button.textContent === 'Show less';
    const fullText = button.dataset.full;

    if (isExpanded) {
        // Show truncated version (30 words + ...)
        const truncatedText = fullText.trim().split(/\s+/).slice(0, 30).join(' ') + '...';
        descriptionText.textContent = truncatedText;
        button.textContent = 'Read more';
    } else {
        // Show full text
        descriptionText.textContent = fullText;
        button.textContent = 'Show less';
    }
};

// Escape HTML for data attribute
window.escapeHtml = function(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
};

// Initialize search on page load
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('search-input');
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            filterFacilities(e.target.value);
        });
        
        // Close search on Escape key
        searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                toggleSearch();
            }
        });
    }
});

// Load facilities on page load
document.addEventListener('DOMContentLoaded', loadFacilities);
