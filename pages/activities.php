<?php
require_once __DIR__ . '/../includes/auth-check.php';
require_once __DIR__ . '/../config/db.php';

$trip_id = isset($_GET['trip_id']) ? (int)$_GET['trip_id'] : 0;
if (!$trip_id) { header('Location: my-trips.php'); exit; }

// Verify ownership
$stmt = $pdo->prepare('SELECT * FROM trips WHERE id = ? AND user_id = ?');
$stmt->execute([$trip_id, $_SESSION['user_id']]);
$trip = $stmt->fetch();
if (!$trip) { header('Location: my-trips.php'); exit; }

// Get all destinations for this trip
$stmt = $pdo->prepare('
    SELECT ts.id, ts.start_date, ts.end_date, ts.stop_order,
           c.id as city_id, c.city_name, c.state_name, c.country_name
    FROM trip_stops ts
    JOIN cities c ON c.id = ts.city_id
    WHERE ts.trip_id = ?
    ORDER BY ts.stop_order ASC
');
$stmt->execute([$trip_id]);
$destinations = $stmt->fetchAll();

// Get all activities for each destination
$activitiesByStop = [];
$totalActivityCost = 0;

foreach ($destinations as $dest) {
    $stmt = $pdo->prepare('
        SELECT ta.id, ta.activity_date, ta.activity_time, ta.created_at,
               a.name, a.type, a.cost, a.description, a.duration
        FROM trip_activities ta
        JOIN activities a ON a.id = ta.activity_id
        WHERE ta.trip_stop_id = ?
        ORDER BY ta.activity_date, ta.activity_time
    ');
    $stmt->execute([$dest['id']]);
    $activities = $stmt->fetchAll();
    
    $activitiesByStop[$dest['id']] = $activities;
    
    foreach ($activities as $act) {
        $totalActivityCost += $act['cost'];
    }
}

$activityIcons = [
    'sightseeing' => '🏛️',
    'food' => '🍽️',
    'adventure' => '🏔️',
    'culture' => '🎭',
    'shopping' => '🛍️',
    'entertainment' => '🎪',
    'nature' => '🌳'
];

$currentPage = 'activities';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Activities — <?= htmlspecialchars($trip['name']) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/JourneyHub/assets/css/style.css">
<link rel="stylesheet" href="/JourneyHub/assets/css/components.css">
<link rel="stylesheet" href="/JourneyHub/assets/css/navbar-compact.css">
<style>
.activities-container { max-width: 1200px; margin: 0 auto; padding: 32px 24px; }
.destination-section { margin-bottom: 32px; }
.activities-grid { display: grid; gap: 16px; margin-top: 16px; }
.activity-search-modal { 
    display: none; 
    position: fixed; 
    top: 0; left: 0; right: 0; bottom: 0; 
    background: rgba(0,0,0,0.5); 
    z-index: 1000;
    align-items: center;
    justify-content: center;
}
.activity-search-modal.active { display: flex; }
.modal-content { 
    background: #fff; 
    border-radius: 18px; 
    padding: 32px; 
    max-width: 600px; 
    width: 90%; 
    max-height: 80vh;
    overflow-y: auto;
}
.activity-option {
    padding: 16px;
    border: 1px solid var(--color-border);
    border-radius: 12px;
    margin-bottom: 12px;
    cursor: pointer;
    transition: all 0.2s ease;
}
.activity-option:hover {
    background: var(--color-bg);
    border-color: var(--primary-dark);
}
</style>
</head>
<body>
<?php require_once __DIR__ . '/../includes/navbar.php'; ?>

<main class="activities-container">

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:32px;">
<div>
<h1 style="margin-bottom:8px;">🎯 <?= htmlspecialchars($trip['name']) ?> — Activities</h1>
<div style="font-size:14px;color:var(--color-text-muted);">
<?= date('M d', strtotime($trip['start_date'])) ?> - <?= date('M d, Y', strtotime($trip['end_date'])) ?>
</div>
</div>
<div style="display:flex;gap:12px;">
<a href="itinerary.php?trip_id=<?= $trip_id ?>" class="btn btn-secondary">← Itinerary</a>
<a href="budget-new.php?trip_id=<?= $trip_id ?>" class="btn btn-secondary">💰 Budget</a>
</div>
</div>

<!-- Summary Card -->
<div class="budget-summary" style="margin-bottom:32px;">
<h2 style="color:#fff;margin-bottom:16px;">Activity Summary</h2>
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:24px;">
<div>
<div style="font-size:12px;text-transform:uppercase;color:rgba(255,255,255,0.7);">Total Activities</div>
<div style="font-size:36px;font-weight:700;color:#fff;"><?= count($activitiesByStop, COUNT_RECURSIVE) - count($activitiesByStop) ?></div>
</div>
<div>
<div style="font-size:12px;text-transform:uppercase;color:rgba(255,255,255,0.7);">Destinations</div>
<div style="font-size:36px;font-weight:700;color:#fff;"><?= count($destinations) ?></div>
</div>
<div>
<div style="font-size:12px;text-transform:uppercase;color:rgba(255,255,255,0.7);">Total Cost</div>
<div style="font-size:36px;font-weight:700;color:#fff;">₹<?= number_format($totalActivityCost, 0) ?></div>
</div>
</div>
</div>

<!-- Destinations & Activities -->
<?php if (empty($destinations)): ?>
<div class="glass-card" style="text-align:center;padding:64px 32px;">
<div style="font-size:64px;margin-bottom:16px;">🗺️</div>
<h2 style="margin-bottom:8px;">No Destinations Yet</h2>
<p style="color:var(--color-text-muted);margin-bottom:24px;">Add destinations to start planning activities</p>
<a href="create-trip.php?edit=<?= $trip_id ?>" class="btn btn-primary">Add Destinations</a>
</div>
<?php else: ?>
<?php foreach ($destinations as $dest): ?>
<div class="destination-section glass-card">
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
<div>
<div style="display:flex;align-items:center;gap:12px;margin-bottom:8px;">
<div class="destination-number"><?= $dest['stop_order'] ?></div>
<h2 style="margin:0;"><?= htmlspecialchars($dest['city_name']) ?></h2>
</div>
<div style="font-size:14px;color:var(--color-text-muted);">
<?= htmlspecialchars($dest['state_name']) ?>, <?= htmlspecialchars($dest['country_name']) ?>
</div>
</div>
<button class="btn btn-primary" onclick="openActivityModal(<?= $dest['id'] ?>, <?= $dest['city_id'] ?>, '<?= htmlspecialchars($dest['city_name']) ?>')">
+ Add Activity
</button>
</div>

<!-- Activities List -->
<div class="activities-grid" id="activities-<?= $dest['id'] ?>">
<?php 
$stopActivities = $activitiesByStop[$dest['id']] ?? [];
if (empty($stopActivities)): 
?>
<div style="text-align:center;padding:32px;background:rgba(247,234,224,0.5);border-radius:12px;border:2px dashed rgba(29,69,51,0.2);">
<div style="font-size:32px;margin-bottom:8px;">🎯</div>
<p style="color:var(--color-text-muted);">No activities yet. Click "Add Activity" to start planning!</p>
</div>
<?php else: ?>
<?php foreach ($stopActivities as $act): ?>
<div class="activity-item">
<div class="activity-item-header">
<div>
<div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;">
<span style="font-size:20px;"><?= $activityIcons[$act['type']] ?? '🎯' ?></span>
<h3 class="activity-item-title"><?= htmlspecialchars($act['name']) ?></h3>
</div>
<div class="activity-item-meta">
<span>📅 <?= date('M d, Y', strtotime($act['activity_date'])) ?></span>
<?php if ($act['activity_time']): ?>
<span>🕐 <?= date('g:i A', strtotime($act['activity_time'])) ?></span>
<?php endif; ?>
<?php if ($act['duration']): ?>
<span>⏱️ <?= htmlspecialchars($act['duration']) ?></span>
<?php endif; ?>
</div>
<?php if ($act['description']): ?>
<div style="margin-top:8px;font-size:14px;color:var(--color-text-muted);">
<?= htmlspecialchars($act['description']) ?>
</div>
<?php endif; ?>
</div>
<div style="display:flex;align-items:center;gap:12px;">
<div class="activity-item-cost">₹<?= number_format($act['cost'], 0) ?></div>
<button class="btn btn-sm btn-danger" onclick="deleteActivity(<?= $act['id'] ?>, <?= $dest['id'] ?>)">✕</button>
</div>
</div>
</div>
<?php endforeach; ?>
<?php endif; ?>
</div>
</div>
<?php endforeach; ?>
<?php endif; ?>

</main>

<!-- Add Activity Modal -->
<div class="activity-search-modal" id="activity-modal">
<div class="modal-content">
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
<h2 style="margin:0;">Add Activity</h2>
<button class="btn btn-sm btn-secondary" onclick="closeActivityModal()">✕</button>
</div>

<div id="modal-city-name" style="font-size:14px;color:var(--color-text-muted);margin-bottom:24px;"></div>

<!-- Activity Selection -->
<div id="activities-list" style="margin-bottom:24px;max-height:300px;overflow-y:auto;">
<div style="text-align:center;padding:32px;color:var(--color-text-muted);">
Loading activities...
</div>
</div>

<!-- Date & Time Form -->
<form id="add-activity-form" style="display:none;">
<input type="hidden" id="selected-stop-id" name="stop_id">
<input type="hidden" id="selected-activity-id" name="activity_id">
<input type="hidden" id="selected-activity-name">

<div style="padding:16px;background:var(--color-bg);border-radius:12px;margin-bottom:16px;">
<strong id="selected-activity-display"></strong>
</div>

<div class="form-group">
<label>Date <span style="color:var(--color-danger);">*</span></label>
<input type="date" name="activity_date" id="activity-date" required>
</div>

<div class="form-group">
<label>Time (optional)</label>
<input type="time" name="activity_time">
</div>

<div style="display:flex;gap:12px;">
<button type="submit" class="btn btn-primary">Add Activity</button>
<button type="button" class="btn btn-secondary" onclick="resetForm()">← Back to List</button>
</div>
</form>
</div>
</div>

<script>
let currentStopId = null;
let currentCityId = null;

async function openActivityModal(stopId, cityId, cityName) {
    currentStopId = stopId;
    currentCityId = cityId;
    
    document.getElementById('modal-city-name').textContent = `Activities in ${cityName}`;
    document.getElementById('activity-modal').classList.add('active');
    
    // Load activities for this city
    const response = await fetch(`/JourneyHub/api/activities/search.php?city_id=${cityId}`);
    const data = await response.json();
    
    const list = document.getElementById('activities-list');
    if (data.success && data.activities.length > 0) {
        list.innerHTML = data.activities.map(act => `
            <div class="activity-option" onclick="selectActivity(${act.id}, '${escapeHtml(act.name)}', ${act.cost})">
                <div style="font-weight:600;margin-bottom:4px;">${escapeHtml(act.name)}</div>
                <div style="font-size:14px;color:var(--color-text-muted);margin-bottom:4px;">${escapeHtml(act.type)} ${act.duration ? '• ' + escapeHtml(act.duration) : ''}</div>
                <div style="font-weight:600;color:var(--primary-dark);">₹${parseFloat(act.cost).toLocaleString()}</div>
            </div>
        `).join('');
    } else {
        list.innerHTML = '<div style="text-align:center;padding:32px;color:var(--color-text-muted);">No activities available for this city</div>';
    }
}

function closeActivityModal() {
    document.getElementById('activity-modal').classList.remove('active');
    resetForm();
}

function selectActivity(activityId, name, cost) {
    document.getElementById('selected-stop-id').value = currentStopId;
    document.getElementById('selected-activity-id').value = activityId;
    document.getElementById('selected-activity-name').value = name;
    document.getElementById('selected-activity-display').textContent = `${name} - ₹${parseFloat(cost).toLocaleString()}`;
    
    document.getElementById('activities-list').style.display = 'none';
    document.getElementById('add-activity-form').style.display = 'block';
    
    // Set default date
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('activity-date').value = today;
}

function resetForm() {
    document.getElementById('activities-list').style.display = 'block';
    document.getElementById('add-activity-form').style.display = 'none';
    document.getElementById('add-activity-form').reset();
}

document.getElementById('add-activity-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    const response = await fetch('/JourneyHub/api/activities/add.php', {
        method: 'POST',
        body: formData
    });
    
    const data = await response.json();
    if (data.success) {
        location.reload();
    } else {
        alert(data.error || 'Failed to add activity');
    }
});

async function deleteActivity(activityId, stopId) {
    if (!confirm('Delete this activity?')) return;
    
    const formData = new FormData();
    formData.append('activity_id', activityId);
    
    const response = await fetch('/JourneyHub/api/activities/delete.php', {
        method: 'POST',
        body: formData
    });
    
    const data = await response.json();
    if (data.success) {
        location.reload();
    } else {
        alert(data.error || 'Failed to delete activity');
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Close modal when clicking outside
document.getElementById('activity-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeActivityModal();
    }
});
</script>
</body>
</html>
