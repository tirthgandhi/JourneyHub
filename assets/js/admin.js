/**
 * Admin Dashboard JavaScript
 * Handles user and trip management functionality
 */

// Mobile navigation toggle
document.addEventListener('DOMContentLoaded', function() {
    const mobileToggle = document.getElementById('mobile-nav-toggle');
    const navMenu = document.getElementById('admin-nav-menu');
    
    if (mobileToggle && navMenu) {
        mobileToggle.addEventListener('click', function() {
            navMenu.classList.toggle('show');
        });
    }
    
    // Initialize page-specific functionality
    if (document.getElementById('user-search')) {
        initUserManagement();
    }
    
    if (document.getElementById('trip-search')) {
        initTripManagement();
    }
});

/**
 * User Management Functions
 */
function initUserManagement() {
    const searchInput = document.getElementById('user-search');
    const searchBtn = document.getElementById('search-btn');
    const applyFiltersBtn = document.getElementById('apply-filters');
    
    // Search functionality
    let searchTimer;
    const performSearch = () => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
            searchUsers();
        }, 300);
    };
    
    searchInput.addEventListener('input', performSearch);
    searchBtn.addEventListener('click', performSearch);
    
    // Filter functionality
    if (applyFiltersBtn) {
        applyFiltersBtn.addEventListener('click', searchUsers);
    }
    
    // Enter key search
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            performSearch();
        }
    });
}

function searchUsers() {
    const loading = document.getElementById('loading');
    const tbody = document.getElementById('users-tbody');
    const searchInput = document.getElementById('user-search');
    const roleFilter = document.getElementById('filter-role');
    const statusFilter = document.getElementById('filter-status');
    const sortBy = document.getElementById('sort-by');
    const sortOrder = document.getElementById('sort-order');
    
    loading.style.display = 'block';
    
    const params = new URLSearchParams();
    
    const query = searchInput ? searchInput.value.trim() : '';
    if (query) {
        params.append('q', query);
    }
    
    if (roleFilter && roleFilter.value) {
        params.append('role', roleFilter.value);
    }
    
    if (statusFilter && statusFilter.value) {
        params.append('status', statusFilter.value);
    }
    
    if (sortBy && sortBy.value) {
        params.append('sort_by', sortBy.value);
    }
    
    if (sortOrder && sortOrder.value) {
        params.append('sort_order', sortOrder.value);
    }
    
    fetch(`/JourneyHub/api/admin/users.php?${params.toString()}`)
        .then(response => response.json())
        .then(data => {
            loading.style.display = 'none';
            
            if (data.success) {
                renderUsers(data.users);
            } else {
                showError(data.error || 'Failed to load users');
            }
        })
        .catch(error => {
            loading.style.display = 'none';
            console.error('Search users error:', error);
            showError('Failed to load users');
        });
}

function renderUsers(users) {
    const tbody = document.getElementById('users-tbody');
    
    if (!users || users.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; color: var(--color-text-muted); padding: 40px;">No users found</td></tr>';
        return;
    }
    
    tbody.innerHTML = users.map(user => {
        const joinDate = new Date(user.created_at).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
        
        const isAdmin = user.role === 'admin';
        const isActive = user.status === 'active';
        
        return `
            <tr data-user-id="${user.id}" data-role="${user.role}">
                <td>${user.id}</td>
                <td>${escapeHtml(user.name)}</td>
                <td>${escapeHtml(user.email)}</td>
                <td>
                    <span class="status-badge ${isAdmin ? 'status-public' : ''}">
                        ${user.role.charAt(0).toUpperCase() + user.role.slice(1)}
                    </span>
                </td>
                <td>
                    <span class="status-badge ${isActive ? 'status-active' : 'status-inactive'}">
                        ${user.status.charAt(0).toUpperCase() + user.status.slice(1)}
                    </span>
                </td>
                <td>${joinDate}</td>
                <td>
                    <div class="action-buttons">
                        ${isAdmin ? 
                            '<span style="color: var(--color-text-muted);">Admin</span>' :
                            isActive ? 
                                `<button class="btn-deactivate" onclick="changeUserStatus(${user.id}, 'deactivate')">Deactivate</button>` :
                                `<button class="btn-reactivate" onclick="changeUserStatus(${user.id}, 'reactivate')">Reactivate</button>`
                        }
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

function changeUserStatus(userId, action) {
    // Prevent admin from deactivating themselves or other admins
    if (userId === CURRENT_USER_ID) {
        showError('You cannot deactivate your own account');
        return;
    }
    
    const actionText = action === 'deactivate' ? 'deactivate' : 'reactivate';
    if (!confirm(`Are you sure you want to ${actionText} this user?`)) {
        return;
    }
    
    const formData = new FormData();
    formData.append('user_id', userId);
    formData.append('action', action);
    
    fetch('/JourneyHub/api/admin/users.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccess(`User ${actionText}d successfully`);
            // Refresh the user list
            searchUsers(document.getElementById('user-search').value);
        } else {
            showError(data.error || `Failed to ${actionText} user`);
        }
    })
    .catch(error => {
        console.error('Change user status error:', error);
        showError(`Failed to ${actionText} user`);
    });
}

/**
 * Trip Management Functions
 */
function initTripManagement() {
    const searchInput = document.getElementById('trip-search');
    const searchBtn = document.getElementById('search-btn');
    const applyFiltersBtn = document.getElementById('apply-trip-filters');
    
    // Search functionality
    let searchTimer;
    const performSearch = () => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
            searchTrips();
        }, 300);
    };
    
    searchInput.addEventListener('input', performSearch);
    searchBtn.addEventListener('click', performSearch);
    
    // Filter functionality
    if (applyFiltersBtn) {
        applyFiltersBtn.addEventListener('click', searchTrips);
    }
    
    // Enter key search
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            performSearch();
        }
    });
}

function searchTrips() {
    const loading = document.getElementById('loading');
    const tbody = document.getElementById('trips-tbody');
    const searchInput = document.getElementById('trip-search');
    const visibilityFilter = document.getElementById('filter-visibility');
    const sortBy = document.getElementById('sort-by');
    const sortOrder = document.getElementById('sort-order');
    
    loading.style.display = 'block';
    
    const params = new URLSearchParams();
    
    const query = searchInput ? searchInput.value.trim() : '';
    if (query) {
        params.append('q', query);
    }
    
    if (visibilityFilter && visibilityFilter.value) {
        params.append('visibility', visibilityFilter.value);
    }
    
    if (sortBy && sortBy.value) {
        params.append('sort_by', sortBy.value);
    }
    
    if (sortOrder && sortOrder.value) {
        params.append('sort_order', sortOrder.value);
    }
    
    fetch(`/JourneyHub/api/admin/trips.php?${params.toString()}`)
        .then(response => response.json())
        .then(data => {
            loading.style.display = 'none';
            
            if (data.success) {
                renderTrips(data.trips);
            } else {
                showError(data.error || 'Failed to load trips');
            }
        })
        .catch(error => {
            loading.style.display = 'none';
            console.error('Search trips error:', error);
            showError('Failed to load trips');
        });
}

function renderTrips(trips) {
    const tbody = document.getElementById('trips-tbody');
    
    if (!trips || trips.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; color: var(--color-text-muted); padding: 40px;">No trips found</td></tr>';
        return;
    }
    
    tbody.innerHTML = trips.map(trip => {
        const createdDate = new Date(trip.created_at).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
        
        const isPublic = trip.is_public === '1' || trip.is_public === 1;
        
        return `
            <tr data-trip-id="${trip.id}">
                <td>${trip.id}</td>
                <td>${escapeHtml(trip.name)}</td>
                <td>${escapeHtml(trip.owner_name)}</td>
                <td>
                    <span class="status-badge ${isPublic ? 'status-public' : 'status-private'}">
                        ${isPublic ? 'Public' : 'Private'}
                    </span>
                </td>
                <td>${createdDate}</td>
                <td>
                    <div class="action-buttons">
                        <button class="btn-remove" onclick="removeTrip(${trip.id}, '${escapeHtml(trip.name).replace(/'/g, '\\\')}')">
                            Remove
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

function removeTrip(tripId, tripName) {
    if (!confirm(`Are you sure you want to remove the trip "${tripName}"? This action cannot be undone and will remove all associated data (stops, activities, expenses).`)) {
        return;
    }
    
    const formData = new FormData();
    formData.append('trip_id', tripId);
    formData.append('action', 'remove');
    
    fetch('/JourneyHub/api/admin/trips.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccess('Trip removed successfully');
            // Refresh the trip list
            searchTrips(document.getElementById('trip-search').value);
        } else {
            showError(data.error || 'Failed to remove trip');
        }
    })
    .catch(error => {
        console.error('Remove trip error:', error);
        showError('Failed to remove trip');
    });
}

/**
 * Utility Functions
 */
function showError(message) {
    showMessage(message, 'error');
}

function showSuccess(message) {
    showMessage(message, 'success');
}

function showMessage(message, type) {
    // Remove existing messages
    const existing = document.querySelectorAll('.error-msg, .success-msg');
    existing.forEach(el => el.remove());
    
    // Create new message
    const div = document.createElement('div');
    div.className = type === 'error' ? 'error-msg' : 'success-msg';
    div.textContent = message;
    
    // Insert at top of main content
    const main = document.querySelector('.admin-main');
    main.insertBefore(div, main.firstChild);
    
    // Auto-dismiss after 4 seconds
    setTimeout(() => {
        if (div.parentElement) {
            div.remove();
        }
    }, 4000);
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text || '';
    return div.innerHTML;
}