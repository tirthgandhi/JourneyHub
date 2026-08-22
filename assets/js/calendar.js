// Calendar JavaScript
let calendarData = {};
let currentMonth = new Date();
let selectedDate = null;

// DOM Elements
const calendarGrid = document.getElementById('calendar-grid');
const currentMonthEl = document.getElementById('current-month');
const prevMonthBtn = document.getElementById('prev-month');
const nextMonthBtn = document.getElementById('next-month');
const selectedDateEl = document.getElementById('selected-date');
const activitiesListEl = document.getElementById('activities-list');

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    loadCalendarData();
    setupEventListeners();
    
    // Set initial month from trip start date
    currentMonth = new Date(TRIP_START);
    renderCalendar();
});

function setupEventListeners() {
    prevMonthBtn.addEventListener('click', showPrevMonth);
    nextMonthBtn.addEventListener('click', showNextMonth);
}

function loadCalendarData() {
    fetch(`../api/calendar/list.php?trip_id=${TRIP_ID}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                calendarData = data;
                renderCalendar();
            } else {
                showError(data.error || 'Failed to load calendar data');
            }
        })
        .catch(error => {
            console.error('Load calendar error:', error);
            showError('Failed to load calendar data');
        });
}

function renderCalendar() {
    renderCalendarGrid();
    updateMonthHeader();
}

function renderCalendarGrid() {
    const year = currentMonth.getFullYear();
    const month = currentMonth.getMonth();
    
    // Get first day of month and number of days
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const startDate = new Date(firstDay);
    startDate.setDate(startDate.getDate() - firstDay.getDay()); // Start from Sunday
    
    let html = '';
    
    // Add day headers
    const dayHeaders = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    dayHeaders.forEach(day => {
        html += `<div class="calendar-day-header">${day}</div>`;
    });
    
    // Add calendar days
    const currentDate = new Date(startDate);
    for (let week = 0; week < 6; week++) {
        for (let day = 0; day < 7; day++) {
            const dateStr = currentDate.toISOString().split('T')[0];
            const isCurrentMonth = currentDate.getMonth() === month;
            const isToday = dateStr === new Date().toISOString().split('T')[0];
            const hasActivities = calendarData.days && calendarData.days[dateStr];
            const isInTripRange = dateStr >= TRIP_START && dateStr <= TRIP_END;
            
            let classes = ['calendar-day'];
            if (!isCurrentMonth) classes.push('outside-month');
            if (isToday) classes.push('today');
            if (hasActivities) classes.push('has-activities');
            if (selectedDate === dateStr) classes.push('selected');
            
            html += `<div class="${classes.join(' ')}" 
                          data-date="${dateStr}" 
                          ${isInTripRange ? 'onclick="selectDate(\'' + dateStr + '\')"' : ''}>
                        ${currentDate.getDate()}
                     </div>`;
            
            currentDate.setDate(currentDate.getDate() + 1);
        }
    }
    
    calendarGrid.innerHTML = html;
}

function updateMonthHeader() {
    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
                       'July', 'August', 'September', 'October', 'November', 'December'];
    currentMonthEl.textContent = `${monthNames[currentMonth.getMonth()]} ${currentMonth.getFullYear()}`;
    
    // Update button states based on trip date range
    const tripStart = new Date(TRIP_START);
    const tripEnd = new Date(TRIP_END);
    
    const prevMonth = new Date(currentMonth);
    prevMonth.setMonth(prevMonth.getMonth() - 1);
    
    const nextMonth = new Date(currentMonth);
    nextMonth.setMonth(nextMonth.getMonth() + 1);
    
    prevMonthBtn.disabled = prevMonth < new Date(tripStart.getFullYear(), tripStart.getMonth(), 1);
    nextMonthBtn.disabled = nextMonth > new Date(tripEnd.getFullYear(), tripEnd.getMonth() + 1, 0);
}

function showPrevMonth() {
    currentMonth.setMonth(currentMonth.getMonth() - 1);
    renderCalendar();
}

function showNextMonth() {
    currentMonth.setMonth(currentMonth.getMonth() + 1);
    renderCalendar();
}

function selectDate(dateStr) {
    // Update selected state
    document.querySelectorAll('.calendar-day.selected').forEach(el => {
        el.classList.remove('selected');
    });
    document.querySelector(`[data-date="${dateStr}"]`).classList.add('selected');
    
    selectedDate = dateStr;
    
    // Update selected date display
    const date = new Date(dateStr);
    selectedDateEl.textContent = date.toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
    
    // Show activities for this date
    showActivitiesForDate(dateStr);
}

function showActivitiesForDate(dateStr) {
    const activities = calendarData.days && calendarData.days[dateStr];
    
    if (!activities || activities.length === 0) {
        activitiesListEl.innerHTML = '<p class="no-activities">No activities scheduled for this day</p>';
        return;
    }
    
    const html = activities.map(activity => `
        <div class="activity-item">
            <div class="activity-time">${formatTime(activity.time)}</div>
            <div class="activity-name">${escapeHtml(activity.name)}</div>
            <div class="activity-details">
                <span>${escapeHtml(activity.city)}</span>
                <span>₹${formatAmount(activity.cost)}</span>
            </div>
        </div>
    `).join('');
    
    activitiesListEl.innerHTML = html;
}

function showError(message) {
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-msg';
    errorDiv.textContent = message;
    
    document.querySelector('.calendar-main').insertBefore(errorDiv, document.querySelector('.calendar-layout'));
    
    setTimeout(() => {
        if (errorDiv.parentElement) {
            errorDiv.remove();
        }
    }, 4000);
}

function formatTime(timeStr) {
    if (!timeStr) return 'All day';
    
    const time = new Date(`2000-01-01 ${timeStr}`);
    return time.toLocaleTimeString('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    });
}

function formatAmount(amount) {
    return parseFloat(amount).toLocaleString('en-IN', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2
    });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}