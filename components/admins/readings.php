    try {
        if ($readingId && $readingId > 0) {
            $saved = $readingRepository->update((int) $readingId, $readingData);
            flash('reading_message', $saved ? 'Reading updated.' : 'The reading was not found.');
        } else {
            $readingRepository->create($readingData, (int) $_SESSION['user_id']);
            flash('reading_message', 'Reading recorded. Risk level was calculated from the rainfall values.');
        }
    } catch (PDOException $exception) {
        error_log('SmartSlope reading save failed: ' . $exception->getMessage());
        flash('reading_message', $exception->getCode() === '23000'
            ? 'A reading with this location, observation time, and source already exists.'
            : 'The reading could not be saved.');
    }
    redirect_to('readings.php');
}

$editId = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT);
$editingReading = $editId && $editId > 0 ? $readingRepository->find((int) $editId) : null;
$readingRows = $readingRepository->adminList();
$defaultObservedAt = (new DateTimeImmutable('now', new DateTimeZone('Asia/Manila')))->format('Y-m-d\\TH:i:s');
$editingObservedAt = $editingReading
    ? (new DateTimeImmutable($editingReading['observed_at'], new DateTimeZone('UTC')))
        ->setTimezone(new DateTimeZone('Asia/Manila'))->format('Y-m-d\\TH:i:s')
    : $defaultObservedAt;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage readings | SmartSlope</title>
    <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
</head>
<body>
<header class="container py-3 d-flex justify-content-between align-items-center">
    <h1 class="h3 mb-0">Manage readings</h1>
    <a class="btn btn-outline-secondary" href="admin.php">Back to admin</a>
</header>
<main class="container pb-4">
    <?php if ($message = flash('reading_message')): ?>
        <div class="alert alert-info" role="status"><?= e($message) ?></div>
    <?php endif; ?>
    <?php if ($message = flash('reading_error')): ?>
        <div class="alert alert-danger" role="alert"><?= e($message) ?></div>
    <?php endif; ?>
    <section class="card mb-4">
        <div class="card-header"><h2 class="h5 mb-0"><?= $editingReading ? 'Edit reading' : 'Record a reading' ?></h2></div>
        <div class="card-body">
            <p class="small text-muted"><?= e(RiskAnalyzer::description()) ?> Values are a prototype indicator, not an official warning.</p>
            <form method="post" action="readings.php">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <?php if ($editingReading): ?><input type="hidden" name="reading_id" value="<?= (int) $editingReading['reading_id'] ?>"><?php endif; ?>
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label" for="reading-location">Location</label><select class="form-select" id="reading-location" name="location_id" required>
                        <option value="">Choose an active location</option>
                        <?php foreach ($allLocations as $location): ?>
                            <?php $isCurrentArchivedLocation = $editingReading && (int) $editingReading['location_id'] === (int) $location['location_id'] && (int) $location['is_active'] !== 1; ?>
                            <?php if ((int) $location['is_active'] === 1 || $isCurrentArchivedLocation): ?>
                                <option value="<?= (int) $location['location_id'] ?>" <?= (string) ($editingReading['location_id'] ?? '') === (string) $location['location_id'] ? 'selected' : '' ?>>
                                    <?= e($location['location_name']) ?><?= $location['purok_zone'] ? ' — ' . e($location['purok_zone']) : '' ?><?= $isCurrentArchivedLocation ? ' (archived; keep existing association)' : '' ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select></div>
                    <div class="col-md-6"><label class="form-label" for="observed-at">Observed at (Baguio local time)</label><input class="form-control" type="datetime-local" step="1" id="observed-at" name="observed_at" value="<?= e($editingObservedAt) ?>" required></div>
                    <div class="col-md-4"><label class="form-label" for="rainfall-1h-input">Rainfall, 1 hour (mm)</label><input class="form-control" type="number" min="0" max="99999.99" step="0.01" id="rainfall-1h-input" name="rainfall_1h_mm" value="<?= e($editingReading['rainfall_1h_mm'] ?? '') ?>"></div>
                    <div class="col-md-4"><label class="form-label" for="rainfall-24h-input">Rainfall, 24 hours (mm)</label><input class="form-control" type="number" min="0" max="99999.99" step="0.01" id="rainfall-24h-input" name="rainfall_24h_mm" value="<?= e($editingReading['rainfall_24h_mm'] ?? '') ?>"></div>
                    <div class="col-md-4"><label class="form-label" for="rainfall-72h-input">Rainfall, 72 hours (mm)</label><input class="form-control" type="number" min="0" max="99999.99" step="0.01" id="rainfall-72h-input" name="rainfall_72h_mm" value="<?= e($editingReading['rainfall_72h_mm'] ?? '') ?>"></div>
                    <div class="col-md-6"><label class="form-label" for="source-name">Data source</label><input class="form-control" id="source-name" name="source_name" maxlength="150" value="<?= e($editingReading['source_name'] ?? '') ?>" required></div>
                    <div class="col-md-6"><label class="form-label" for="source-url">Source URL (optional)</label><input class="form-control" type="url" id="source-url" name="source_url" maxlength="500" value="<?= e($editingReading['source_url'] ?? '') ?>"></div>
                </div>
                <div class="mt-3 d-flex gap-2"><button class="btn btn-primary" type="submit" name="action" value="save"><?= $editingReading ? 'Save reading' : 'Record reading' ?></button>
                    <?php if ($editingReading): ?><a class="btn btn-outline-secondary" href="readings.php">Cancel</a><?php endif; ?></div>
            </form>
        </div>
    </section>
    <section class="card">
        <div class="card-header"><h2 class="h5 mb-0">Reading history</h2></div>
        <div class="table-responsive"><table class="table table-striped align-middle mb-0">
            <thead><tr><th>Observed</th><th>Location</th><th>Rainfall (1h / 24h / 72h)</th><th>Risk</th><th>Source</th><th>State</th><th>Actions</th></tr></thead>
            <tbody>
            <?php foreach ($readingRows as $reading): ?>
                <tr>
                    <td><?= e(display_local_datetime($reading['observed_at'])) ?></td>
                    <td><?= e($reading['location_name']) ?><?= $reading['purok_zone'] ? ' — ' . e($reading['purok_zone']) : '' ?></td>
                    <td><?= e($reading['rainfall_1h_mm'] ?? '—') ?> / <?= e($reading['rainfall_24h_mm'] ?? '—') ?> / <?= e($reading['rainfall_72h_mm'] ?? '—') ?> mm</td>
                    <td><?= e(ucfirst($reading['risk_level'])) ?></td>
                    <td><?= e($reading['source_name']) ?></td>
                    <td><?= (int) $reading['is_archived'] === 1 ? 'Archived' : ((int) $reading['location_is_active'] === 1 ? 'Current' : 'Location archived') ?></td>
                    <td>
                        <?php if ((int) $reading['is_archived'] !== 1): ?>
                            <div class="d-flex gap-2"><a class="btn btn-sm btn-outline-primary" href="?edit=<?= (int) $reading['reading_id'] ?>">Edit</a>
                                <form method="post" action="readings.php">
                                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="reading_id" value="<?= (int) $reading['reading_id'] ?>">
                                    <button class="btn btn-sm btn-outline-danger" type="submit" name="action" value="archive">Archive</button>
                                </form>
                            </div>
                        <?php else: ?>
                            <form method="post" action="readings.php">
                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                <input type="hidden" name="reading_id" value="<?= (int) $reading['reading_id'] ?>">
                                <button class="btn btn-sm btn-outline-success" type="submit" name="action" value="restore">Restore</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$readingRows): ?><tr><td colspan="7" class="text-center">No readings have been recorded.</td></tr><?php endif; ?>
            </tbody>
        </table></div>
    </section>
</main>
</body>
</html>
