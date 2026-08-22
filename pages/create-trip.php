<?php
/**
 * Create / Edit Trip — JourneyHub
 *
 * Shows a form to create a new trip or edit an existing one.
 * Edit mode is activated by ?edit=TRIP_ID in the URL.
 */

require_once __DIR__ . '/../includes/auth-check.php';
require_once __DIR__ . '/../config/db.php';

$userId   = $_SESSION['user_id'];
$editMode = false;
$trip     = null;

// --- Edit mode: load existing trip data ---
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $editId = (int) $_GET['edit'];
    $stmt = $pdo->prepare('SELECT * FROM trips WHERE id = ? AND user_id = ?');
    $stmt->execute([$editId, $userId]);
    $trip = $stmt->fetch();

    if ($trip) {
        $editMode = true;
    }
}

$currentPage = 'create-trip';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $editMode ? 'Edit Trip' : 'Create Trip'; ?> — JourneyHub</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/JourneyHub/assets/css/style.css">`n    <link rel="stylesheet" href="/JourneyHub/assets/css/navbar.css">
    <link rel="stylesheet" href="/JourneyHub/assets/css/dashboard.css">
</head>
<body>
    <?php require_once __DIR__ . '/../includes/navbar.php'; ?>

    <main class="dashboard-main">
        <section class="form-section" id="trip-form-section">
            <h1><?php echo $editMode ? 'Edit Trip' : 'Plan a New Trip'; ?></h1>

            <!-- Inline error container (populated by JS) -->
            <div class="form-errors" id="form-errors" style="display: none;"></div>

            <form id="trip-form"
                  class="trip-form"
                  enctype="multipart/form-data"
                  data-mode="<?php echo $editMode ? 'edit' : 'create'; ?>"
                  data-trip-id="<?php echo $editMode ? $trip['id'] : ''; ?>">

                <div class="form-group">
                    <label for="trip-name">Trip Name <span class="required">*</span></label>
                    <input type="text" id="trip-name" name="name" maxlength="150"
                           value="<?php echo $editMode ? htmlspecialchars($trip['name']) : ''; ?>"
                           placeholder="e.g. Summer in Bali" required>
                </div>

                <div class="form-group">
                    <label for="trip-description">Description</label>
                    <textarea id="trip-description" name="description" rows="4"
                              placeholder="What's this trip about?"><?php echo $editMode ? htmlspecialchars($trip['description'] ?? '') : ''; ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="trip-start-date">Start Date <span class="required">*</span></label>
                        <input type="date" id="trip-start-date" name="start_date"
                               value="<?php echo $editMode ? htmlspecialchars($trip['start_date']) : ''; ?>"
                               required>
                    </div>
                    <div class="form-group">
                        <label for="trip-end-date">End Date <span class="required">*</span></label>
                        <input type="date" id="trip-end-date" name="end_date"
                               value="<?php echo $editMode ? htmlspecialchars($trip['end_date']) : ''; ?>"
                               required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="trip-cover">Cover Photo <span class="optional">(optional)</span></label>
                    <input type="file" id="trip-cover" name="cover_image"
                           accept="image/jpeg,image/png,image/gif">
                    <?php if ($editMode && $trip['cover_image']): ?>
                        <p class="current-cover">
                            Current: <img src="/JourneyHub/assets/images/covers/<?php echo htmlspecialchars($trip['cover_image']); ?>"
                                          alt="Current cover" class="cover-preview">
                        </p>
                    <?php endif; ?>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary" id="trip-submit-btn">
                        <?php echo $editMode ? '💾 Save Changes' : '✈️ Create Trip'; ?>
                    </button>
                    <a href="/JourneyHub/pages/my-trips.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </section>
    </main>

    <script src="/JourneyHub/assets/js/trips.js"></script>
</body>
</html>
