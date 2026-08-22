/**
 * Trip Sharing — JourneyHub
 * 
 * Handles share panel interactions on itinerary page
 * All data sourced from MySQL via APIs
 */

// Track current sharing state
let currentShareUrl = null;
let isShared = false;

/**
 * Initialize sharing functionality
 */
function initSharing(tripId) {
    // Check if trip is already shared by fetching trip data
    checkSharingStatus(tripId);
}

/**
 * Check if trip is currently shared
 */
async function checkSharingStatus(tripId) {
    // This would typically be done by fetching trip data
    // For now, we'll load the state when the panel is opened
}

/**
 * Toggle share panel visibility
 */
function toggleSharePanel() {
    const panel = document.getElementById('share-panel');
    if (!panel) return;
    
    if (panel.classList.contains('hidden')) {
        panel.classList.remove('hidden');
        // Load current sharing status when panel opens
        loadSharingStatus();
    } else {
        panel.classList.add('hidden');
    }
}

/**
 * Load current sharing status from database
 */
async function loadSharingStatus() {
    const shareUrlContainer = document.getElementById('share-url-container');
    const generateBtn = document.getElementById('generate-share-btn');
    const shareActions = document.getElementById('share-actions');
    const errorDiv = document.getElementById('share-error');
    
    // For simplicity, we'll determine state based on what's rendered
    // In a more complete implementation, we'd fetch trip data here
}

/**
 * Generate share link
 */
async function generateShareLink() {
    const generateBtn = document.getElementById('generate-share-btn');
    const shareUrlContainer = document.getElementById('share-url-container');
    const shareActions = document.getElementById('share-actions');
    const errorDiv = document.getElementById('share-error');
    
    // Disable button during request
    generateBtn.disabled = true;
    generateBtn.textContent = 'Generating...';
    
    hideError(errorDiv);
    
    try {
        const response = await fetch('/JourneyHub/api/trips/share.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                trip_id: TRIP_ID
            })
        });
        
        const data = await response.json();
        
        if (!response.ok) {
            showError(errorDiv, data.error || 'Failed to generate share link');
            generateBtn.disabled = false;
            generateBtn.textContent = 'Generate Share Link';
            return;
        }
        
        // Store share URL
        currentShareUrl = data.share_url;
        isShared = true;
        
        // Update UI to show share URL and actions
        shareUrlContainer.innerHTML = `
            <input type="text" 
                   class="share-url-input" 
                   id="share-url-input"
                   value="${escapeHtml(data.share_url)}" 
                   readonly>
        `;
        
        shareActions.innerHTML = `
            <button class="btn btn-primary" onclick="copyShareLink()">
                📋 Copy Link
            </button>
            <button class="btn btn-danger" onclick="disableSharing()">
                🔒 Disable Sharing
            </button>
            <span class="share-success-msg" id="copy-success" style="display: none;">
                ✓ Link copied!
            </span>
        `;
        
        generateBtn.style.display = 'none';
        
    } catch (error) {
        console.error('Error generating share link:', error);
        showError(errorDiv, 'Error generating share link');
        generateBtn.disabled = false;
        generateBtn.textContent = 'Generate Share Link';
    }
}

/**
 * Copy share link to clipboard
 */
async function copyShareLink() {
    const shareUrlInput = document.getElementById('share-url-input');
    const copySuccess = document.getElementById('copy-success');
    const errorDiv = document.getElementById('share-error');
    
    if (!shareUrlInput) return;
    
    try {
        await navigator.clipboard.writeText(shareUrlInput.value);
        
        // Show success message
        if (copySuccess) {
            copySuccess.style.display = 'inline-flex';
            setTimeout(() => {
                copySuccess.style.display = 'none';
            }, 3000);
        }
        
    } catch (error) {
        console.error('Failed to copy link:', error);
        
        // Fallback: select the text
        shareUrlInput.select();
        shareUrlInput.setSelectionRange(0, 99999); // For mobile
        
        try {
            document.execCommand('copy');
            if (copySuccess) {
                copySuccess.style.display = 'inline-flex';
                setTimeout(() => {
                    copySuccess.style.display = 'none';
                }, 3000);
            }
        } catch (err) {
            showError(errorDiv, 'Could not copy link. Please copy manually.');
        }
    }
}

/**
 * Disable sharing
 */
async function disableSharing() {
    if (!confirm('Disable sharing for this trip? The current link will stop working.')) {
        return;
    }
    
    const shareUrlContainer = document.getElementById('share-url-container');
    const shareActions = document.getElementById('share-actions');
    const generateBtn = document.getElementById('generate-share-btn');
    const errorDiv = document.getElementById('share-error');
    
    hideError(errorDiv);
    
    try {
        const response = await fetch('/JourneyHub/api/trips/unshare.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                trip_id: TRIP_ID
            })
        });
        
        const data = await response.json();
        
        if (!response.ok) {
            showError(errorDiv, data.error || 'Failed to disable sharing');
            return;
        }
        
        // Reset UI to "not shared" state
        isShared = false;
        currentShareUrl = null;
        
        shareUrlContainer.innerHTML = '';
        shareActions.innerHTML = `
            <button class="btn btn-primary" id="generate-share-btn" onclick="generateShareLink()">
                🔗 Generate Share Link
            </button>
        `;
        
    } catch (error) {
        console.error('Error disabling sharing:', error);
        showError(errorDiv, 'Error disabling sharing');
    }
}

/**
 * Show error message
 */
function showError(errorDiv, message) {
    if (!errorDiv) return;
    errorDiv.textContent = message;
    errorDiv.style.display = 'block';
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

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    // Share functionality is initialized when panel is opened
    // TRIP_ID variable should be defined in itinerary.php
});
