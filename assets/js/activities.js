/**
 * Activities Management — JourneyHub
 * 
 * Handles inline activity management within itinerary stops
 * All data sourced from MySQL via APIs (no static JSON/arrays)
 */

// Track which stop's search panel is currently open
let currentSearchStopId = null;

// Debounce timer for search
let searchDebounceTimer = null;

/**
 * Initialize activities for all stops on page load
 */
document.addEventListener('DOMContentLoaded', () => {
    // Load activities for each existing stop
    const stopCards = document.querySelectorAll('.stop-card');
    stopCards.forEach(card => {
        const stopId = parseInt(card.dataset.stopId);
        if (stopId) {
            initializeActivitiesForStop(stopId);
        }
    });
});

/**
 * Initialize activities section for a specific stop
 */
function initializeActivitiesForStop(stopId) {
    const stopCard = document.getElementById(`stop-${stopId}`);
    if (!stopCard) return;
    
    // Check if activities section already exists
    if (stopCard.querySelector('.stop-activities')) return;
    
    const stopContent = stopCard.querySelector('.stop-content');
    const stopActions = stopCard.querySelector('.stop-actions');
    
    // Create activities section HTML
    const activitiesSection = document.createElement('div');
    activitiesSection.className = 'stop-activities';
    activitiesSection.id = `activities-${stopId}`;
    activitiesSection.innerHTML = `
        <div class="activities-header">
            <h4>Activities</h4>
            <button class="btn btn-sm btn-primary btn-add-activity" onclick="toggleActivitySearch(${stopId})">
                + Add Activity
            </button>
        </div>
        <div class="activity-list" id="activity-list-${stopId}"></div>
        <div class="activity-search-panel hidden" id="search-panel-${stopId}">
            <div class="search-panel-header">
                <h4>Search Activities</h4>
                <button class="btn-close-search" onclick="closeActivitySearch(${stopId})">&times;</button>
            </div>
            <div class="activity-search-form">
                <input type="text" 
                       class="activity-search-input" 
                       id="search-input-${stopId}"
                       placeholder="🔍 Search activities..."
                       oninput="searchActivities(${stopId})">
                <select class="activity-type-filter" 
                        id="type-filter-${stopId}"
                        onchange="searchActivities(${stopId})">
                    <option value="">All Types</option>
                    <option value="sightseeing">Sightseeing</option>
                    <option value="food">Food</option>
                    <option value="adventure">Adventure</option>
                    <option value="culture">Culture</option>
                    <option value="shopping">Shopping</option>
                    <option value="entertainment">Entertainment</option>
                    <option value="nature">Nature</option>
                </select>
            </div>
            <div class="error-msg" id="search-error-${stopId}" style="display: none;"></div>
            <div class="activity-search-results" id="search-results-${stopId}">
                <div class="search-results-empty">
                    Search for activities in this city
                </div>
            </div>
        </div>
    `;
    
    // Insert activities section before stop actions
    stopContent.insertBefore(activitiesSection, stopActions);
    
    // Load existing activities for this stop
    loadActivitiesForStop(stopId);
}

/**
 * Load and display activities already added to a stop
 */
async function loadActivitiesForStop(stopId) {
    try {
        const response = await fetch(`/JourneyHub/api/activities/list.php?stop_id=${stopId}`);
        const data = await response.json();
        
        if (!response.ok) {
            console.error('Failed to load activities:', data.error);
            return;
        }
        
        const activityList = document.getElementById(`activity-list-${stopId}`);
        
        if (data.trip_activities && data.trip_activities.length > 0) {
            activityList.innerHTML = data.trip_activities.map(activity => 
                renderActivityItem(activity, stopId)
            ).join('');
        } else {
            activityList.innerHTML = `
                <div class="activities-empty">
                    No activities added yet. Click "+ Add Activity" to get started.
                </div>
            `;
        }
    } catch (error) {
        console.error('Error loading activities:', error);
    }
}

/**
 * Render a single activity item
 */
function renderActivityItem(activity, stopId) {
    const cost = parseFloat(activity.cost) === 0 ? 'Free' : `₹${parseFloat(activity.cost).toFixed(0)}`;
    const time = activity.activity_time ? formatTime(activity.activity_time) : 'No time set';
    const date = formatDate(activity.activity_date);
    
    return `
        <div class="activity-item" id="trip-activity-${activity.id}">
            <div class="activity-info">
                <div class="activity-name">${escapeHtml(activity.name)}</div>
                <div class="activity-meta">
                    <span>📅 ${date}</span>
                    <span>🕐 ${time}</span>
                    <span>💰 ${cost}</span>
                    <span>⏱ ${escapeHtml(activity.duration || 'N/A')}</span>
                </div>
            </div>
            <div class="activity-actions">
                <button class="btn btn-sm btn-secondary" onclick="editActivity(${activity.id}, ${stopId})">
                    Edit
                </button>
                <button class="btn btn-sm btn-danger" onclick="deleteActivity(${activity.id}, ${stopId}, '${escapeHtml(activity.name)}')">
                    Delete
                </button>
            </div>
        </div>
    `;
}

/**
 * Toggle activity search panel
 */
function toggleActivitySearch(stopId) {
    const searchPanel = document.getElementById(`search-panel-${stopId}`);
    
    // Close other open panels first
    if (currentSearchStopId && currentSearchStopId !== stopId) {
        closeActivitySearch(currentSearchStopId);
    }
    
    if (searchPanel.classList.contains('hidden')) {
        searchPanel.classList.remove('hidden');
        currentSearchStopId = stopId;
        
        // Get city_id from stop card
        const stopCard = document.getElementById(`stop-${stopId}`);
        const cityId = parseInt(stopCard.dataset.cityId);
        
        // Load all activities for this city initially
        searchActivitiesForCity(cityId, stopId);
    } else {
        closeActivitySearch(stopId);
    }
}

/**
 * Close activity search panel
 */
function closeActivitySearch(stopId) {
    const searchPanel = document.getElementById(`search-panel-${stopId}`);
    searchPanel.classList.add('hidden');
    
    // Clear search
    const searchInput = document.getElementById(`search-input-${stopId}`);
    const typeFilter = document.getElementById(`type-filter-${stopId}`);
    if (searchInput) searchInput.value = '';
    if (typeFilter) typeFilter.value = '';
    
    if (currentSearchStopId === stopId) {
        currentSearchStopId = null;
    }
}

/**
 * Search activities with debounce
 */
function searchActivities(stopId) {
    clearTimeout(searchDebounceTimer);
    
    searchDebounceTimer = setTimeout(() => {
        const stopCard = document.getElementById(`stop-${stopId}`);
        const cityId = parseInt(stopCard.dataset.cityId);
        searchActivitiesForCity(cityId, stopId);
    }, 300);
}

/**
 * Fetch and display activity search results from MySQL
 */
async function searchActivitiesForCity(cityId, stopId) {
    const searchInput = document.getElementById(`search-input-${stopId}`);
    const typeFilter = document.getElementById(`type-filter-${stopId}`);
    const resultsContainer = document.getElementById(`search-results-${stopId}`);
    const errorDiv = document.getElementById(`search-error-${stopId}`);
    
    const query = searchInput ? searchInput.value.trim() : '';
    const type = typeFilter ? typeFilter.value : '';
    
    // Build query parameters
    let url = `/JourneyHub/api/activities/search.php?city_id=${cityId}`;
    if (query) url += `&q=${encodeURIComponent(query)}`;
    if (type) url += `&type=${encodeURIComponent(type)}`;
    
    try {
        const response = await fetch(url);
        const data = await response.json();
        
        if (!response.ok) {
            showError(errorDiv, data.error || 'Failed to load activities');
            return;
        }
        
        hideError(errorDiv);
        
        if (data.activities && data.activities.length > 0) {
            // Get stop date range for date input constraints
            const stopCard = document.getElementById(`stop-${stopId}`);
            const stopDates = getStopDateRange(stopId);
            
            resultsContainer.innerHTML = data.activities.map(activity => 
                renderSearchResultItem(activity, stopId, stopDates)
            ).join('');
        } else {
            resultsContainer.innerHTML = `
                <div class="search-results-empty">
                    No activities found. Try a different search.
                </div>
            `;
        }
    } catch (error) {
        console.error('Error searching activities:', error);
        showError(errorDiv, 'Error loading activities');
    }
}

/**
 * Render a search result item
 */
function renderSearchResultItem(activity, stopId, stopDates) {
    const cost = parseFloat(activity.cost) === 0 ? 'Free' : `₹${parseFloat(activity.cost).toFixed(0)}`;
    const description = activity.description ? escapeHtml(activity.description) : '';
    
    return `
        <div class="activity-search-item">
            <div class="search-item-header">
                <div class="search-item-name">${escapeHtml(activity.name)}</div>
                <div class="search-item-meta">
                    <span>${escapeHtml(activity.type)}</span>
                    <span>💰 ${cost}</span>
                    <span>⏱ ${escapeHtml(activity.duration || 'N/A')}</span>
                </div>
            </div>
            ${description ? `<div class="search-item-description">${description}</div>` : ''}
            <div class="search-item-actions">
                <div>
                    <label>Date:</label>
                    <input type="date" 
                           id="add-date-${activity.id}"
                           min="${stopDates.start}"
                           max="${stopDates.end}"
                           value="${stopDates.start}">
                </div>
                <div>
                    <label>Time (optional):</label>
                    <input type="time" 
                           id="add-time-${activity.id}">
                </div>
                <button class="btn btn-sm btn-primary" 
                        style="align-self: end;"
                        onclick="addActivityToTrip(${activity.id}, ${stopId})">
                    + Add
                </button>
            </div>
        </div>
    `;
}

/**
 * Add activity to trip stop
 */
async function addActivityToTrip(activityId, stopId) {
    const dateInput = document.getElementById(`add-date-${activityId}`);
    const timeInput = document.getElementById(`add-time-${activityId}`);
    const errorDiv = document.getElementById(`search-error-${stopId}`);
    
    const activityDate = dateInput.value;
    const activityTime = timeInput.value || null;
    
    if (!activityDate) {
        showError(errorDiv, 'Please select a date for this activity');
        return;
    }
    
    try {
        const response = await fetch('/JourneyHub/api/activities/add.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                trip_stop_id: stopId,
                activity_id: activityId,
                activity_date: activityDate,
                activity_time: activityTime
            })
        });
        
        const data = await response.json();
        
        if (!response.ok) {
            showError(errorDiv, data.error || 'Failed to add activity');
            return;
        }
        
        hideError(errorDiv);
        
        // Reload activities list to show the new activity
        await loadActivitiesForStop(stopId);
        
        // Clear the date/time inputs
        dateInput.value = getStopDateRange(stopId).start;
        if (timeInput) timeInput.value = '';
        
    } catch (error) {
        console.error('Error adding activity:', error);
        showError(errorDiv, 'Error adding activity');
    }
}

/**
 * Edit activity date/time
 */
function editActivity(tripActivityId, stopId) {
    const activityItem = document.getElementById(`trip-activity-${tripActivityId}`);
    if (!activityItem) return;
    
    // Extract current values
    const metaSpans = activityItem.querySelectorAll('.activity-meta span');
    let currentDate = '';
    let currentTime = '';
    
    metaSpans.forEach(span => {
        const text = span.textContent;
        if (text.includes('📅')) {
            currentDate = text.replace('📅', '').trim();
        } else if (text.includes('🕐')) {
            currentTime = text.replace('🕐', '').trim();
        }
    });
    
    // Convert display formats back to input formats
    const dateValue = parseDateForInput(currentDate);
    const timeValue = currentTime !== 'No time set' ? parseTimeForInput(currentTime) : '';
    
    const stopDates = getStopDateRange(stopId);
    
    // Replace activity item with edit form
    activityItem.classList.add('editing');
    activityItem.innerHTML = `
        <div class="activity-info">
            <div class="activity-name">${activityItem.querySelector('.activity-name').textContent}</div>
            <div class="activity-edit-form">
                <div>
                    <label>Date:</label>
                    <input type="date" 
                           id="edit-date-${tripActivityId}"
                           value="${dateValue}"
                           min="${stopDates.start}"
                           max="${stopDates.end}">
                </div>
                <div>
                    <label>Time:</label>
                    <input type="time" 
                           id="edit-time-${tripActivityId}"
                           value="${timeValue}">
                </div>
                <div class="activity-edit-actions">
                    <button class="btn btn-sm btn-primary" 
                            onclick="saveActivityEdit(${tripActivityId}, ${stopId})">
                        Save
                    </button>
                    <button class="btn btn-sm btn-secondary" 
                            onclick="cancelActivityEdit(${stopId})">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    `;
}

/**
 * Save activity edit
 */
async function saveActivityEdit(tripActivityId, stopId) {
    const dateInput = document.getElementById(`edit-date-${tripActivityId}`);
    const timeInput = document.getElementById(`edit-time-${tripActivityId}`);
    
    const activityDate = dateInput.value;
    const activityTime = timeInput.value || null;
    
    if (!activityDate) {
        alert('Please select a date');
        return;
    }
    
    try {
        const response = await fetch('/JourneyHub/api/activities/update.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                trip_activity_id: tripActivityId,
                activity_date: activityDate,
                activity_time: activityTime
            })
        });
        
        const data = await response.json();
        
        if (!response.ok) {
            alert(data.error || 'Failed to update activity');
            return;
        }
        
        // Reload activities list
        await loadActivitiesForStop(stopId);
        
    } catch (error) {
        console.error('Error updating activity:', error);
        alert('Error updating activity');
    }
}

/**
 * Cancel activity edit
 */
function cancelActivityEdit(stopId) {
    loadActivitiesForStop(stopId);
}

/**
 * Delete activity from trip
 */
async function deleteActivity(tripActivityId, stopId, activityName) {
    if (!confirm(`Remove "${activityName}" from your itinerary?`)) {
        return;
    }
    
    try {
        const response = await fetch('/JourneyHub/api/activities/delete.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                trip_activity_id: tripActivityId
            })
        });
        
        const data = await response.json();
        
        if (!response.ok) {
            alert(data.error || 'Failed to delete activity');
            return;
        }
        
        // Reload activities list
        await loadActivitiesForStop(stopId);
        
    } catch (error) {
        console.error('Error deleting activity:', error);
        alert('Error deleting activity');
    }
}

/**
 * Get stop date range from the DOM
 */
function getStopDateRange(stopId) {
    const stopCard = document.getElementById(`stop-${stopId}`);
    const startInput = document.getElementById(`start-date-${stopId}`);
    const endInput = document.getElementById(`end-date-${stopId}`);
    
    return {
        start: startInput ? startInput.value : TRIP_START,
        end: endInput ? endInput.value : TRIP_END
    };
}

/**
 * Format date for display (MySQL date to readable format)
 */
function formatDate(dateStr) {
    const date = new Date(dateStr + 'T00:00:00');
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    return `${months[date.getMonth()]} ${date.getDate()}`;
}

/**
 * Format time for display (MySQL time to readable format)
 */
function formatTime(timeStr) {
    if (!timeStr) return 'No time set';
    const [hours, minutes] = timeStr.split(':');
    const h = parseInt(hours);
    const period = h >= 12 ? 'PM' : 'AM';
    const displayHour = h % 12 || 12;
    return `${displayHour}:${minutes} ${period}`;
}

/**
 * Parse display date back to input format
 */
function parseDateForInput(displayDate) {
    // Format: "Jan 10" -> need to get full date from current context
    // For simplicity, we'll extract from the trip_activities data via reload
    // This is a limitation - in production, store raw values in data attributes
    return '';
}

/**
 * Parse display time back to input format
 */
function parseTimeForInput(displayTime) {
    // Format: "10:30 AM" -> "10:30"
    const match = displayTime.match(/(\d+):(\d+)\s*(AM|PM)/i);
    if (!match) return '';
    
    let hours = parseInt(match[1]);
    const minutes = match[2];
    const period = match[3].toUpperCase();
    
    if (period === 'PM' && hours !== 12) hours += 12;
    if (period === 'AM' && hours === 12) hours = 0;
    
    return `${String(hours).padStart(2, '0')}:${minutes}`;
}

/**
 * Show error message
 */
function showError(errorDiv, message) {
    if (!errorDiv) return;
    errorDiv.textContent = message;
    errorDiv.style.display = 'block';
    
    // Auto-hide after 4 seconds
    setTimeout(() => hideError(errorDiv), 4000);
}

/**
 * Hide error message
 */
function hideError(errorDiv) {
    if (!errorDiv) return;
    errorDiv.style.display = 'none';
}

/**
 * Escape HTML to prevent XSS
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
