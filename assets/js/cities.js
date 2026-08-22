/**
 * City Search JavaScript — JourneyHub
 * 
 * Handles city search with debouncing, country filtering, and adding stops to itinerary
 * Follows the absolute data rule: all cities come from MySQL via API calls
 */

// DOM elements
const searchInput = document.getElementById('city-search');
const countryFilter = document.getElementById('country-filter');
const resultsList = document.getElementById('results-list');
const noResults = document.getElementById('no-results');

// Search state
let searchTimer;
const SEARCH_DEBOUNCE_MS = 300;

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    loadCountries();
    setupEventListeners();
});

/**
 * Load countries for dropdown filter
 */
function loadCountries() {
    fetch('../api/cities/get-countries.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                populateCountryDropdown(data.countries);
            } else {
                console.error('Failed to load countries:', data.error);
            }
        })
        .catch(error => {
            console.error('Error loading countries:', error);
        });
}

/**
 * Populate country dropdown with options
 */
function populateCountryDropdown(countries) {
    countryFilter.innerHTML = '<option value="">All Countries</option>';
    
    countries.forEach(country => {
        const option = document.createElement('option');
        option.value = country;
        option.textContent = country;
        countryFilter.appendChild(option);
    });
}

/**
 * Setup event listeners
 */
function setupEventListeners() {
    // Search input with debouncing
    searchInput.addEventListener('input', handleSearchInput);
    
    // Country filter change
    countryFilter.addEventListener('change', handleSearchInput);
}

/**
 * Handle search input with debouncing
 */
function handleSearchInput() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        performSearch();
    }, SEARCH_DEBOUNCE_MS);
}

/**
 * Perform the actual city search
 */
function performSearch() {
    const query = searchInput.value.trim();
    const country = countryFilter.value.trim();
    
    // Clear results if insufficient search criteria
    if (query.length < 2 && !country) {
        clearResults();
        return;
    }
    
    // Show loading state
    showLoading();
    
    // Build query parameters
    const params = new URLSearchParams();
    if (query.length >= 2) {
        params.append('q', query);
    }
    if (country) {
        params.append('country', country);
    }
    
    // Make API call
    fetch(`../api/cities/search.php?${params.toString()}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderCityResults(data.cities);
            } else {
                showError(data.error || 'Search failed');
            }
        })
        .catch(error => {
            console.error('Search error:', error);
            showError('Search failed');
        });
}

/**
 * Clear search results
 */
function clearResults() {
    resultsList.innerHTML = '';
    noResults.style.display = 'block';
    noResults.textContent = 'Type at least 2 characters to search cities';
}

/**
 * Show loading state
 */
function showLoading() {
    resultsList.innerHTML = '';
    noResults.style.display = 'block';
    noResults.textContent = 'Searching...';
}

/**
 * Show error message
 */
function showError(message) {
    resultsList.innerHTML = '';
    noResults.style.display = 'block';
    noResults.textContent = message;
}

/**
 * Render city search results
 */
function renderCityResults(cities) {
    // Hide no results message
    noResults.style.display = 'none';
    
    // Clear previous results
    resultsList.innerHTML = '';
    
    if (cities.length === 0) {
        noResults.style.display = 'block';
        noResults.textContent = 'No cities found';
        return;
    }
    
    // Render each city result
    cities.forEach(city => {
        const cityCard = createCityResultCard(city);
        resultsList.appendChild(cityCard);
    });
}

/**
 * Create a city result card element
 */
function createCityResultCard(city) {
    const card = document.createElement('div');
    card.className = 'city-result';
    
    // Check if city is already added to itinerary
    const isAdded = addedCityIds.has(city.id);
    
    // Format cost display
    const costDisplay = city.cost_index 
        ? `₹${parseFloat(city.cost_index).toLocaleString()}/day`
        : 'Cost not available';
    
    card.innerHTML = `
        <div class="city-info">
            <h4>${escapeHtml(city.city_name)}</h4>
            <div class="city-meta">
                ${escapeHtml(city.state_name)}, ${escapeHtml(city.country_name)}
            </div>
            <div class="city-cost">${costDisplay}</div>
        </div>
        <button class="add-btn ${isAdded ? 'added' : ''}" 
                data-city-id="${city.id}"
                ${isAdded ? 'disabled' : ''}
                onclick="addCityToItinerary(${city.id})">
            ${isAdded ? 'Added ✓' : '+ Add'}
        </button>
    `;
    
    return card;
}

/**
 * Add city to itinerary
 */
function addCityToItinerary(cityId) {
    // Prevent double-clicking
    const button = document.querySelector(`[data-city-id="${cityId}"]`);
    if (button.disabled) return;
    
    // Disable button and show loading
    button.disabled = true;
    button.textContent = 'Adding...';
    
    // Make API call to add stop
    const formData = new FormData();
    formData.append('trip_id', TRIP_ID);
    formData.append('city_id', cityId);
    
    fetch('../api/trips/add-stop.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Add to added cities set
            addedCityIds.add(cityId);
            
            // Update button state
            button.textContent = 'Added ✓';
            button.classList.add('added');
            
            // Add stop to itinerary UI
            appendStopToItinerary(data.stop);
            
            // Clear empty state if it was showing
            hideEmptyState();
            
        } else {
            // Show error
            showInlineError(button.parentElement, data.error);
            
            // Reset button
            button.disabled = false;
            button.textContent = '+ Add';
        }
    })
    .catch(error => {
        console.error('Add stop error:', error);
        showInlineError(button.parentElement, 'Failed to add city');
        
        // Reset button
        button.disabled = false;
        button.textContent = '+ Add';
    });
}

/**
 * Append new stop to itinerary UI
 */
function appendStopToItinerary(stop) {
    const itineraryStops = document.getElementById('itinerary-stops');
    
    // Create new stop card
    const stopCard = createStopCard(stop);
    itineraryStops.appendChild(stopCard);
    
    // Update stop numbers for all cards
    updateStopNumbers();
    
    // Update up/down button states
    updateReorderButtonStates();
}

/**
 * Create a stop card element
 */
function createStopCard(stop) {
    const card = document.createElement('div');
    card.className = 'stop-card';
    card.id = `stop-${stop.id}`;
    card.setAttribute('data-stop-id', stop.id);
    card.setAttribute('data-city-id', stop.city_id);
    
    // Format dates for display
    const startDate = new Date(stop.start_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    const endDate = new Date(stop.end_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    
    card.innerHTML = `
        <div class="stop-number">
            <span class="stop-badge">${stop.stop_order}</span>
        </div>
        
        <div class="stop-content">
            <div class="stop-header">
                <h3 class="city-name">${escapeHtml(stop.city_name)}</h3>
                <p class="city-location">
                    ${escapeHtml(stop.state_name)}, ${escapeHtml(stop.country_name)}
                </p>
            </div>
            
            <div class="stop-dates" id="dates-display-${stop.id}">
                <span class="date-icon">📅</span>
                <span class="date-range">${startDate} → ${endDate}</span>
            </div>
            
            <div class="stop-dates-edit" id="dates-edit-${stop.id}" style="display: none;">
                <div class="date-inputs">
                    <div class="date-input-group">
                        <label>Start Date:</label>
                        <input type="date" 
                               id="start-date-${stop.id}"
                               value="${stop.start_date}"
                               min="${TRIP_START}"
                               max="${TRIP_END}">
                    </div>
                    <div class="date-input-group">
                        <label>End Date:</label>
                        <input type="date" 
                               id="end-date-${stop.id}"
                               value="${stop.end_date}"
                               min="${TRIP_START}"
                               max="${TRIP_END}">
                    </div>
                </div>
                <div class="date-actions">
                    <button class="btn btn-sm btn-primary" onclick="saveDates(${stop.id})">Save</button>
                    <button class="btn btn-sm btn-secondary" onclick="cancelEditDates(${stop.id})">Cancel</button>
                </div>
            </div>
            
            <div class="stop-actions">
                <button class="btn btn-sm btn-secondary" onclick="editDates(${stop.id})">Edit Dates</button>
                <button class="btn btn-sm btn-secondary" onclick="moveStopUp(${stop.id})">↑</button>
                <button class="btn btn-sm btn-secondary" onclick="moveStopDown(${stop.id})">↓</button>
                <button class="btn btn-sm btn-danger" onclick="removeStop(${stop.id}, '${escapeHtml(stop.city_name)}')">🗑 Remove</button>
            </div>
        </div>
    `;
    
    return card;
}

/**
 * Hide empty itinerary state
 */
function hideEmptyState() {
    const emptyState = document.getElementById('empty-itinerary');
    if (emptyState) {
        emptyState.remove();
    }
}

/**
 * Show inline error message
 */
function showInlineError(parentElement, message) {
    // Remove any existing error messages
    const existingError = parentElement.querySelector('.error-msg');
    if (existingError) {
        existingError.remove();
    }
    
    // Create new error message
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-msg';
    errorDiv.textContent = message;
    
    // Append to parent
    parentElement.appendChild(errorDiv);
    
    // Auto-dismiss after 4 seconds
    setTimeout(() => {
        if (errorDiv.parentElement) {
            errorDiv.remove();
        }
    }, 4000);
}

/**
 * Update stop numbers in the UI
 */
function updateStopNumbers() {
    const stopCards = document.querySelectorAll('.stop-card');
    stopCards.forEach((card, index) => {
        const badge = card.querySelector('.stop-badge');
        if (badge) {
            badge.textContent = index + 1;
        }
    });
}

/**
 * Update up/down button states based on position
 */
function updateReorderButtonStates() {
    const stopCards = document.querySelectorAll('.stop-card');
    
    stopCards.forEach((card, index) => {
        const upButton = card.querySelector('button[onclick*="moveStopUp"]');
        const downButton = card.querySelector('button[onclick*="moveStopDown"]');
        
        if (upButton) {
            upButton.disabled = index === 0;
        }
        if (downButton) {
            downButton.disabled = index === stopCards.length - 1;
        }
    });
}

/**
 * Escape HTML to prevent XSS
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}