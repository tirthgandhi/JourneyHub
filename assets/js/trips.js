/**
 * Trips JavaScript — JourneyHub
 *
 * Handles:
 * 1. Create/Edit trip form validation + fetch submission
 * 2. Delete trip with confirm dialog + DOM removal
 */

document.addEventListener('DOMContentLoaded', () => {

    // ── Trip Form (Create / Edit) ──
    const tripForm = document.getElementById('trip-form');
    if (tripForm) {
        tripForm.addEventListener('submit', handleTripFormSubmit);
    }

    // ── Delete Buttons (My Trips page) ──
    const deleteButtons = document.querySelectorAll('.btn-delete-trip');
    deleteButtons.forEach(btn => {
        btn.addEventListener('click', handleDeleteTrip);
    });

});

// ====================================================================
// FORM SUBMISSION — Create or Edit a trip
// ====================================================================
async function handleTripFormSubmit(e) {
    e.preventDefault();

    const form   = e.target;
    const mode   = form.dataset.mode;   // 'create' or 'edit'
    const tripId = form.dataset.tripId; // only set in edit mode
    const errBox = document.getElementById('form-errors');
    const submitBtn = document.getElementById('trip-submit-btn');

    // Clear previous errors
    errBox.style.display = 'none';
    errBox.innerHTML = '';

    // ── Collect values ──
    const name      = form.querySelector('#trip-name').value.trim();
    const startDate = form.querySelector('#trip-start-date').value;
    const endDate   = form.querySelector('#trip-end-date').value;
    const coverInput = form.querySelector('#trip-cover');

    // ── Client-side validation ──
    const errors = [];

    if (!name) {
        errors.push('Trip name is required.');
    } else if (name.length > 150) {
        errors.push('Trip name must be 150 characters or fewer.');
    }

    if (!startDate) {
        errors.push('Start date is required.');
    }
    if (!endDate) {
        errors.push('End date is required.');
    }

    if (startDate && endDate && new Date(endDate) < new Date(startDate)) {
        errors.push('End date must be on or after the start date.');
    }

    // Validate cover photo if selected
    if (coverInput && coverInput.files.length > 0) {
        const file = coverInput.files[0];
        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        const maxSize = 2 * 1024 * 1024; // 2 MB

        if (!allowedTypes.includes(file.type)) {
            errors.push('Cover photo must be a JPG, PNG, or GIF image.');
        }
        if (file.size > maxSize) {
            errors.push('Cover photo must be 2 MB or smaller.');
        }
    }

    if (errors.length > 0) {
        showFormErrors(errBox, errors);
        return;
    }

    // ── Build FormData ──
    const formData = new FormData(form);

    if (mode === 'edit') {
        formData.append('trip_id', tripId);
    }
    
    // Add selected destinations to FormData
    if (typeof selectedDestinations !== 'undefined' && selectedDestinations.length > 0) {
        formData.append('destinations', JSON.stringify(selectedDestinations.map(d => d.id)));
    }

    // ── Determine API endpoint ──
    const url = mode === 'edit'
        ? '/JourneyHub/api/trips/update.php'
        : '/JourneyHub/api/trips/create.php';

    // ── Submit via fetch ──
    submitBtn.disabled = true;
    submitBtn.textContent = mode === 'edit' ? 'Saving…' : 'Creating…';

    try {
        const response = await fetch(url, {
            method: 'POST',
            body: formData,
        });

        const data = await response.json();

        if (data.success) {
            // Redirect to My Trips on success
            window.location.href = '/JourneyHub/pages/my-trips.php';
        } else {
            const serverErrors = data.errors || [data.error || 'Something went wrong.'];
            showFormErrors(errBox, serverErrors);
        }
    } catch (err) {
        showFormErrors(errBox, ['Network error. Please try again.']);
        console.error('Trip form submission error:', err);
    } finally {
        submitBtn.disabled = false;
        submitBtn.textContent = mode === 'edit' ? '💾 Save Changes' : '✈️ Create Trip';
    }
}

// ====================================================================
// DELETE TRIP — Confirm + fetch + remove card from DOM
// ====================================================================
async function handleDeleteTrip(e) {
    const btn      = e.currentTarget;
    const tripId   = btn.dataset.tripId;
    const tripName = btn.dataset.tripName;

    // Confirm dialog
    if (!confirm(`Delete "${tripName}"? This cannot be undone.`)) {
        return;
    }

    btn.disabled = true;
    btn.textContent = 'Deleting…';

    try {
        const formData = new FormData();
        formData.append('trip_id', tripId);

        const response = await fetch('/JourneyHub/api/trips/delete.php', {
            method: 'POST',
            body: formData,
        });

        const data = await response.json();

        if (data.success) {
            // Remove the trip card from the DOM
            const card = document.getElementById('trip-card-' + tripId);
            if (card) {
                card.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                card.style.opacity = '0';
                card.style.transform = 'scale(0.95)';
                setTimeout(() => card.remove(), 300);
            }

            // If no trips remain, show empty state
            setTimeout(() => {
                const grid = document.getElementById('trips-grid');
                if (grid && grid.children.length === 0) {
                    location.reload();
                }
            }, 400);
        } else {
            alert(data.error || 'Failed to delete trip.');
        }
    } catch (err) {
        alert('Network error. Please try again.');
        console.error('Delete trip error:', err);
    } finally {
        btn.disabled = false;
        btn.textContent = 'Delete';
    }
}

// ====================================================================
// HELPER — Show error messages in the form error box
// ====================================================================
function showFormErrors(errBox, errors) {
    errBox.innerHTML = errors.map(e => `<p>${escapeHtml(e)}</p>`).join('');
    errBox.style.display = 'block';
    errBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

// ====================================================================
// HELPER — Escape HTML to prevent XSS in JS-rendered content
// ====================================================================
function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}
