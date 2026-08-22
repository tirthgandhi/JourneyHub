<?php
require_once __DIR__ . '/../includes/auth-check.php';
require_once __DIR__ . '/../config/db.php';

$userId = $_SESSION['user_id'];

// Get user data
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();

// Get travel stats
$stmt = $pdo->prepare('SELECT COUNT(*) as total FROM trips WHERE user_id = ?');
$stmt->execute([$userId]);
$totalTrips = $stmt->fetchColumn();

$stmt = $pdo->prepare('SELECT COUNT(DISTINCT ts.city_id) as total FROM trip_stops ts JOIN trips t ON t.id = ts.trip_id WHERE t.user_id = ?');
$stmt->execute([$userId]);
$totalDestinations = $stmt->fetchColumn();

$stmt = $pdo->prepare('SELECT COUNT(*) as total FROM trip_activities ta JOIN trip_stops ts ON ts.id = ta.trip_stop_id JOIN trips t ON t.id = ts.trip_id WHERE t.user_id = ?');
$stmt->execute([$userId]);
$totalActivities = $stmt->fetchColumn();

$stmt = $pdo->prepare('SELECT COUNT(*) as total FROM trips WHERE user_id = ? AND end_date < CURDATE()');
$stmt->execute([$userId]);
$completedTrips = $stmt->fetchColumn();

// Upcoming trips
$stmt = $pdo->prepare('SELECT * FROM trips WHERE user_id = ? AND start_date >= CURDATE() ORDER BY start_date LIMIT 3');
$stmt->execute([$userId]);
$upcomingTrips = $stmt->fetchAll();

$initials = strtoupper(substr($user['name'], 0, 1));
$currentPage = 'profile';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Profile — JourneyHub</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/JourneyHub/assets/css/style.css">
<link rel="stylesheet" href="/JourneyHub/assets/css/components.css">
<link rel="stylesheet" href="/JourneyHub/assets/css/navbar-compact.css">
</head>
<body>
<?php require_once __DIR__ . '/../includes/navbar.php'; ?>
<main style="max-width:1000px;margin:0 auto;padding:32px 24px;">

<!-- Profile Header -->
<div class="profile-header" style="margin-bottom:32px;">
<div class="profile-avatar">
<?php if ($user['profile_photo']): ?>
<img src="/JourneyHub/assets/images/profiles/<?= htmlspecialchars($user['profile_photo']) ?>" alt="Profile">
<?php else: ?>
<div class="profile-avatar-initials"><?= $initials ?></div>
<?php endif; ?>
</div>
<h1 class="profile-name"><?= htmlspecialchars($user['name']) ?></h1>
<div class="profile-email"><?= htmlspecialchars($user['email']) ?></div>
<div class="profile-meta">Travel Explorer • India</div>
<div style="margin-top:24px;">
<button class="btn btn-accent" onclick="toggleEditMode()">✏️ Edit Profile</button>
</div>
</div>

<!-- Stats Grid -->
<div class="stats-grid" style="margin-bottom:32px;">
<div class="stat-glass">
<span class="stat-glass-value"><?= $totalTrips ?></span>
<span class="stat-glass-label">Trips Planned</span>
</div>
<div class="stat-glass">
<span class="stat-glass-value"><?= $totalDestinations ?></span>
<span class="stat-glass-label">Destinations</span>
</div>
<div class="stat-glass">
<span class="stat-glass-value"><?= $totalActivities ?></span>
<span class="stat-glass-label">Activities</span>
</div>
<div class="stat-glass">
<span class="stat-glass-value"><?= $completedTrips ?></span>
<span class="stat-glass-label">Completed</span>
</div>
</div>

<!-- Edit Form (Hidden) -->
<div id="edit-form" class="glass-card" style="display:none;margin-bottom:32px;">
<h2 style="margin-bottom:16px;">Edit Profile</h2>
<form id="profile-form" enctype="multipart/form-data">
<div class="form-group">
<label>Name</label>
<input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
</div>
<div class="form-group">
<label>Email</label>
<input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
</div>
<div class="form-group">
<label>Profile Photo (JPG, PNG - Max 2MB)</label>
<input type="file" name="profile_photo" accept="image/jpeg,image/png">
</div>
<div style="display:flex;gap:12px;">
<button type="submit" class="btn btn-primary">Save Changes</button>
<button type="button" class="btn btn-secondary" onclick="toggleEditMode()">Cancel</button>
</div>
</form>
</div>

<!-- Upcoming Trips -->
<div class="glass-card">
<h2 style="margin-bottom:16px;">🗓️ Upcoming Trips</h2>
<?php if (empty($upcomingTrips)): ?>
<div style="text-align:center;padding:48px;color:var(--color-text-muted);">
<div style="font-size:48px;margin-bottom:16px;">✈️</div>
<p>No upcoming trips</p>
<a href="/JourneyHub/pages/create-trip.php" class="btn btn-primary" style="margin-top:16px;">Plan a Trip</a>
</div>
<?php else: ?>
<div style="display:grid;gap:16px;">
<?php foreach($upcomingTrips as $trip): ?>
<div style="padding:16px;background:rgba(249,210,186,0.2);border:1px solid rgba(249,210,186,0.4);border-radius:12px;">
<h3 style="margin-bottom:8px;"><?= htmlspecialchars($trip['name']) ?></h3>
<div style="font-size:14px;color:var(--color-text-muted);">
<?= date('M d, Y', strtotime($trip['start_date'])) ?> → <?= date('M d, Y', strtotime($trip['end_date'])) ?>
</div>
<a href="/JourneyHub/pages/itinerary.php?trip_id=<?= $trip['id'] ?>" class="btn btn-sm btn-primary" style="margin-top:12px;">View Trip</a>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>
</div>

</main>
<script>
function toggleEditMode() {
const form = document.getElementById('edit-form');
form.style.display = form.style.display === 'none' ? 'block' : 'none';
}

document.getElementById('profile-form').addEventListener('submit', async function(e) {
e.preventDefault();
const formData = new FormData(this);
const response = await fetch('/JourneyHub/api/users/update-profile.php', {method:'POST',body:formData});
const data = await response.json();
if (data.success) { location.reload(); } else { alert(data.error); }
});
</script>
</body>
</html>
