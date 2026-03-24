// Global variable to store all FO data
let allFOData = [];

// Load front office data from API based on id_cabang
async function loadFrontOffice() {
    const loading = document.getElementById('loading');
    const error = document.getElementById('error');
    const container = document.getElementById('fo-cards');

    console.log('loadFrontOffice called with ID_CABANG:', window.ID_CABANG);

    if (!window.ID_CABANG) {
        error.classList.remove('hidden');
        error.classList.add('flex');
        document.getElementById('error-message').textContent = 'Invalid branch ID';
        loading.classList.add('hidden');
        return;
    }

    try {
        const apiUrl = `../api/fo/list.php?id_cabang=${window.ID_CABANG}`;
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
            // Store all FO data
            allFOData = result.data;
            console.log('✅ Front Office contacts loaded:', allFOData.length, 'contacts');

            // Clear existing content
            container.innerHTML = '';

            // Create FO cards
            allFOData.forEach((fo, index) => {
                const card = createFOCard(fo, index);
                container.appendChild(card);
            });
        } else {
            container.innerHTML = `
                <div class="flex flex-col items-center justify-center py-12 mx-4">
                    <span class="material-symbols-outlined text-slate-400 text-5xl mb-4">concierge</span>
                    <p class="text-slate-500 dark:text-slate-400">No receptionist contacts available for this branch</p>
                </div>
            `;
        }
    } catch (err) {
        loading.classList.add('hidden');
        error.classList.remove('hidden');
        error.classList.add('flex');
        document.getElementById('error-message').textContent = 'Failed to load contacts. Please check your connection.';
        console.error('Error loading front office:', err);
    }
}

// Create FO card element
function createFOCard(fo, index) {
    const card = document.createElement('div');
    card.className = 'fo-card flex gap-4 bg-white dark:bg-slate-900 p-4 rounded-lg shadow-sm border border-slate-100 dark:border-slate-800 items-center';

    // Format WhatsApp number for display
    const displayPhone = fo.wa ? formatPhoneNumber(fo.wa) : 'Not available';

    card.innerHTML = `
        <div class="bg-center bg-no-repeat aspect-square bg-cover rounded-lg size-16 shrink-0"
            data-alt="${fo.nama_cabang}"
            style='background-image: url("../images/${fo.foto || 'default-branch.jpg'}");'>
        </div>
        <div class="flex flex-1 flex-col justify-center min-w-0">
            <p class="text-slate-900 dark:text-slate-100 text-base font-semibold truncate">${fo.nama_cabang}</p>
            <p class="text-slate-500 dark:text-slate-400 text-xs truncate">WhatsApp Call</p>
            <p class="text-sm font-medium mt-1" style="color: #25D366;">${displayPhone}</p>
        </div>
        <div class="shrink-0">
            <button onclick="openCallModal('${fo.wa}')"
                class="flex size-11 items-center justify-center rounded-full shadow-lg"
                style="background-color: #25D366;">
                <span class="material-symbols-outlined text-white">call</span>
            </button>
        </div>
    `;

    return card;
}

// Format phone number for display
function formatPhoneNumber(phoneNumber) {
    if (!phoneNumber) return 'Not available';

    // Remove all non-numeric characters
    let cleanNumber = phoneNumber.replace(/\D/g, '');

    // Convert leading '0' to '+62'
    if (cleanNumber.startsWith('0')) {
        cleanNumber = '62' + cleanNumber.substring(1);
    }

    // Format as +62 852 4747 1234
    const match = cleanNumber.match(/(\d{2})(\d{3})(\d{3})(\d{4})/);
    if (match) {
        return `+${match[1]} ${match[2]} ${match[3]} ${match[4]}`;
    }

    // Fallback for other formats
    return phoneNumber;
}

// Load front office on page load
document.addEventListener('DOMContentLoaded', loadFrontOffice);
