/**
 * Itinerary Management JavaScript — JourneyHub
 * 
 * Handles reordering stops, editing dates, removing stops, and other itinerary interactions
 */

/**
 * Edit dates for a stop - show inline form
 */
function editDates(stopId) {
    const displayDiv = document.getElementById(`dates-display-${stopId}`);
    const editDiv = document.getElementById(`dates-edit-${stopId}`);
    
    if (displayDiv && editDiv) {
        displayDiv.style.display = 'none';
        editDiv.style.display = 'block';
        
        // Focus on start date input
        const startDateInput = document.getElementById(`start-date-${stopId}`);
        if (startDateInput) {
            startDateInput.focus();
        }
    }
}

/**
 * Cancel editing dates - hide inline form
 */
function cancelEditDates(stopId) {
    const displayDiv = document.getElementById(`dates-display-${stopId}`);
    const editDiv = document.getElementById(`dates-edit-${stopId}`);
    
    if (displayDiv && editDiv) {
        displayDiv.style.display = 'flex';
        editDiv.style.display = 'none';
    }
}

/**
 * Save updated dates for a stop
 */
function saveDates(stopId) {
    const startDateInput = document.getElementById(`start-date-${stopId}`);
    const endDateInput = document.getElementById(`end-date-${stopId}`);
    
    if (!startDateInput || !endDateInput) {
        showStopError(stopId, 'Date inputs not found');
        return;
    }
    
    const startDate = startDateInput.value;
    const endDate = endDateInput.value;
    
    // Client-side validation
    if (!startDate || !endDate) {
        showStopError(stopId, 'Both start and end dates are required');
        return;
    }
    
    if (new Date(endDate) < new Date(startDate)) {
        showStopError(stopId, 'End date must be on or after start date');
        return;
    }
    
    // Check if dates are within trip bounds
    if (new Date(startDate) < new Date(TRIP_START) || new Date(endDate) > new Date(TRIP_END)) {
        showStopError(stopId, `Dates must be between ${TRIP_START} and ${TRIP_END}`);
        return;
    }
    
    // Disable save button while processing
    const saveButton = document.querySelector(`#dates-edit-${stopId} .btn-primary`);
    if (saveButton) {
        saveButton.disabled = true;
        saveButton.textContent = 'Saving...';
    }
    
    // Make API call
    const formData = new FormData();
    formData.append('stop_id', stopId);
    formData.append('trip_id', TRIP_ID);
    formData.append('start_date', startDate);
    formData.append('end_date', endDate);
    
    fetch('../api/trips/update-stop-dates.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update display
            updateDateDisplay(stopId, startDate, endDate);
            
            // Hide edit form
            cancelEditDates(stopId);
            
        } else {
            showStopError(stopId, data.error || 'Failed to update dates');
        }
    })
    .catch(error => {
        console.error('Update dates error:', error);
        showStopError(stopId, 'Failed to update dates');
    })
    .finally(() => {
        // Re-enable save button
        if (saveButton) {
            saveButton.disabled = false;
            saveButton.textContent = 'Save';
        }
    });
}

/**
 * Update the date display in the UI
 */
function updateDateDisplay(stopId, startDate, endDate) {
    const dateRangeSpan = document.querySelector(`#dates-display-${stopId} .date-range`);
    if (dateRangeSpan) {
        const startFormatted = new Date(startDate).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        const endFormatted = new Date(endDate).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        dateRangeSpan.textContent = `${startFormatted} → ${endFormatted}`;
    }
}

/**
 * Remove a stop from the itinerary
 */
function removeStop(stopId, cityName) {
    if (!confirm(`Remove ${cityName} from your itinerary?`)) {
        return;
    }
    
    // Make API call
    const formData = new FormData();
    formData.append('stop_id', stopId);
    formData.append('trip_id', TRIP_ID);
    
    fetch('../api/trips/remove-stop.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove stop card from DOM
            const stopCard = document.getElementById(`stop-${stopId}`);
            if (stopCard) {
                // Get city ID before removing
                const cityId = parseInt(stopCard.getAttribute('data-city-id'));
                
                // Remove from DOM
                stopCard.remove();
                
                // Remove from added cities set to re-enable Add button
                addedCityIds.delete(cityId);
                
                // Update search results if visible
                updateSearchResultButton(cityId, false);
                
                // Update stop numbers
                updateStopNumbers();
                
                // Update reorder button states
                updateReorderButtonStates();
                
                // Show empty state if no stops left
                checkAndShowEmptyState();
            }
        } else {
            showStopError(stopId, data.error || 'Failed to remove stop');
        }
    })
    .catch(error => {
        console.error('Remove stop error:', error);
        showStopError(stopId, 'Failed to remove stop');
    });
}

/**
 * Move stop up in the order
 */
function moveStopUp(stopId) {
    const stopCard = document.getElementById(`stop-${stopId}`);
    const previousCard = stopCard.previousElementSibling;
    
    if (previousCard && previousCard.classList.contains('stop-card')) {
        // Swap in DOM
        stopCard.parentNode.insertBefore(stopCard, previousCard);
        
        // Update order in database
        updateStopOrder();
    }
}

/**
 * Move stop down in the order
 */
function moveStopDown(stopId) {
    const stopCard = document.getElementById(`stop-${stopId}`);
    const nextCard = stopCard.nextElementSibling;
    
    if (nextCard && nextCard.classList.contains('stop-card')) {
        // Swap in DOM
        stopCard.parentNode.insertBefore(nextCard, stopCard);
        
        // Update order in database
        updateStopOrder();
    }
}

/**
 * Update stop order in database based on current DOM order
 */
function updateStopOrder() {
    const stopCards = document.querySelectorAll('.stop-card');
    const stops = [];
    
    // Collect current order from DOM
    stopCards.forEach((card, index) => {
        const stopId = parseInt(card.getAttribute('data-stop-id'));
        stops.push({
            stop_id: stopId,
            order: index + 1
        });
    });
    
    // Send to API
    const formData = new FormData();
    formData.append('trip_id', TRIP_ID);
    formData.append('stops', JSON.stringify(stops));
    
    fetch('../api/trips/reorder-stops.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update UI elements
            updateStopNumbers();
            updateReorderButtonStates();
        } else {
            console.error('Reorder failed:', data.error);
            // Could reload page or revert DOM changes here
        }
    })
    .catch(error => {
        console.error('Reorder error:', error);
        // Could reload page or revert DOM changes here
    });
}

/**
 * Show error message for a specific stop
 */
function showStopError(stopId, message) {
    const stopCard = document.getElementById(`stop-${stopId}`);
    if (!stopCard) return;
    
    // Remove any existing error messages in this stop
    const existingError = stopCard.querySelector('.error-msg');
    if (existingError) {
        existingError.remove();
    }
    
    // Create new error message
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-msg';
    errorDiv.textContent = message;
    
    // Append to stop content
    const stopContent = stopCard.querySelector('.stop-content');
    if (stopContent) {
        stopContent.appendChild(errorDiv);
        
        // Auto-dismiss after 4 seconds
        setTimeout(() => {
            if (errorDiv.parentElement) {
                errorDiv.remove();
            }
        }, 4000);
    }
}

/**
 * Update search result button state for a city
 */
function updateSearchResultButton(cityId, isAdded) {
    const button = document.querySelector(`[data-city-id="${cityId}"]`);
    if (button) {
        if (isAdded) {
            button.textContent = 'Added ✓';
            button.classList.add('added');
            button.disabled = true;
        } else {
            button.textContent = '+ Add';
            button.classList.remove('added');
            button.disabled = false;
        }
    }
}

/**
 * Check if itinerary is empty and show empty state
 */
function checkAndShowEmptyState() {
    const itineraryStops = document.getElementById('itinerary-stops');
    const stopCards = itineraryStops.querySelectorAll('.stop-card');
    
    if (stopCards.length === 0) {
        // Show empty state
        const emptyDiv = document.createElement('div');
        emptyDiv.className = 'empty-itinerary';
        emptyDiv.id = 'empty-itinerary';
        emptyDiv.innerHTML = `
            <div class="empty-icon">📍</div>
            <p>No destinations added yet</p>
            <p class="empty-subtitle">Search and add cities to build your itinerary</p>
        `;
        
        itineraryStops.appendChild(emptyDiv);
        
        // Hide itinerary footer
        const footer = document.querySelector('.itinerary-footer');
        if (footer) {
            footer.style.display = 'none';
        }
    } else {
        // Show itinerary footer
        const footer = document.querySelector('.itinerary-footer');
        if (footer) {
            footer.style.display = 'block';
        }
    }
}

/**
 * Setup keyboard shortcuts
 */
document.addEventListener('keydown', function(e) {
    // Escape key cancels any active date editing
    if (e.key === 'Escape') {
        const editForms = document.querySelectorAll('.stop-dates-edit[style*="block"]');
        editForms.forEach(form => {
            const stopId = form.id.replace('dates-edit-', '');
            cancelEditDates(stopId);
        });
    }
});

/**
 * Initialize itinerary management
 */
document.addEventListener('DOMContentLoaded', function() {
    // Update button states on load
    updateReorderButtonStates();
    
    // Set up date input validation
    setupDateValidation();
});

/**
 * Setup date input validation
 */
function setupDateValidation() {
    // Add event listeners to date inputs for real-time validation
    document.addEventListener('change', function(e) {
        if (e.target.type === 'date' && e.target.id.includes('start-date-')) {
            const stopId = e.target.id.replace('start-date-', '');
            const endDateInput = document.getElementById(`end-date-${stopId}`);
            
            if (endDateInput && e.target.value) {
                // Update end date minimum to match start date
                endDateInput.min = e.target.value;
                
                // If end date is now before start date, update it
                if (endDateInput.value && new Date(endDateInput.value) < new Date(e.target.value)) {
                    endDateInput.value = e.target.value;
                }
            }
        }
    });
}


/**
 * Toggle Day-by-Day View
 */
function toggleDayByDayView() {
    const content = document.getElementById('day-by-day-content');
    const button = document.getElementById('toggle-day-view-btn');
    
    if (!content || !button) return;
    
    if (content.classList.contains('hidden')) {
        content.classList.remove('hidden');
        button.textContent = 'Hide Day-by-Day';
    } else {
        content.classList.add('hidden');
        button.textContent = 'Show Day-by-Day';
    }
}
