// Load rooms from API based on id_cabang
async function loadRooms() {
    const loading = document.getElementById('loading');
    const error = document.getElementById('error');
    const container = document.getElementById('rooms-container');
    const branchNameEl = document.getElementById('branch-name');

    if (!window.ID_CABANG) {
        error.classList.remove('hidden');
        error.classList.add('flex');
        document.getElementById('error-message').textContent = 'Invalid branch ID';
        loading.classList.add('hidden');
        return;
    }

    try {
        const response = await fetch(`../api/cabangtipekamar/list.php?id_cabang=${window.ID_CABANG}`);
        const result = await response.json();

        loading.classList.add('hidden');
        error.classList.add('hidden');

        if (result.success && result.data && result.data.length > 0) {
            // Set branch name from first result
            branchNameEl.textContent = result.data[0].nama_cabang;

            // Clear existing content (except loading and error divs)
            const existingCards = container.querySelectorAll('.room-card');
            existingCards.forEach(card => card.remove());

            // Create room cards
            result.data.forEach(room => {
                const card = document.createElement('div');
                card.className = 'room-card bg-white dark:bg-slate-900 rounded-2xl overflow-hidden border border-slate-100 dark:border-slate-700 shadow-md';
                
                // YouTube embed - full width edge-to-edge at bottom
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

                card.innerHTML = `
                    <div class="w-full aspect-[16/9] bg-center bg-no-repeat bg-cover"
                        style="background-image: url('../images/${room.gambar || 'default-room.jpg'}');">
                    </div>
                    <div class="p-5 space-y-4">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="text-xl font-bold text-slate-900 dark:text-slate-100">${room.nama_tipe || 'Room Type'}</h3>
                                <p class="text-slate-500 dark:text-slate-400 text-sm mt-1 flex items-center gap-1">
                                    <span class="material-symbols-outlined text-xs">room</span>
                                    ${room.keterangan_tipe || ''}
                                </p>
                            </div>
                            <span class="bg-primary/10 text-primary text-[10px] font-bold px-2 py-1 rounded uppercase tracking-wider flex items-center gap-1">
                                <span class="material-symbols-outlined text-xs">check_circle</span>
                                Available
                            </span>
                        </div>
                        <div class="relative">
                            <p class="description text-slate-600 dark:text-slate-400 text-sm leading-relaxed">
                                ${truncateText(room.keterangan_akomodasi || room.keterangan_tipe || 'No description available', 200)}
                            </p>
                            <button class="read-more-btn text-primary text-xs font-semibold mt-2 hover:text-primary/80" onclick="toggleReadMore(this)" data-full="${escapeHtml(room.keterangan_akomodasi || room.keterangan_tipe || 'No description available')}">
                                Read more
                            </button>
                        </div>
                        ${room.link_youtube ? `
                        <div class="flex justify-between items-center pt-2 border-t border-slate-100 dark:border-slate-800">
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Video Room Tour</p>
                            <a href="https://www.youtube.com/watch?v=${room.link_youtube}" target="_blank" class="text-xs font-semibold text-primary hover:text-primary/80 flex items-center gap-1">
                                <span class="material-symbols-outlined text-sm">open_in_new</span>
                                Watch on YouTube
                            </a>
                        </div>
                        ` : ''}
                    </div>
                    ${youtubeEmbed}
                `;

                container.appendChild(card);
            });

            // Initialize read more buttons after cards are rendered
            initReadMoreButtons();
        } else {
            branchNameEl.textContent = 'No Rooms Found';
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

// Load rooms on page load
document.addEventListener('DOMContentLoaded', loadRooms);

// Truncate text to character limit
function truncateText(text, charLimit) {
    if (text.length <= charLimit) {
        return text;
    }
    return text.substring(0, charLimit) + '...';
}

// Escape HTML for data attribute
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Toggle read more functionality
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

// Initialize read more buttons - show only if content exceeds character limit
function initReadMoreButtons() {
    const buttons = document.querySelectorAll('.read-more-btn');
    buttons.forEach(button => {
        const fullText = button.dataset.full;
        
        // Show button only if content exceeds 200 characters
        if (fullText.length > 200) {
            button.style.display = 'block';
        } else {
            button.style.display = 'none';
        }
    });
}
