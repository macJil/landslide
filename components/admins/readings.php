<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/bootstrap.php';
require_admin();
$readingRepository = new ReadingRepository($pdo);
$locationRepository = new LocationRepository($pdo);
$activeLocations = $locationRepository->activeForStudyArea();
$allLocations = $locationRepository->adminList();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        flash('reading_message', 'Your session expired. Reload the page and try again.');
        redirect_to('readings.php');
    }

    $action = post_string('action') ?: 'save';
    $readingId = filter_var($_POST['reading_id'] ?? null, FILTER_VALIDATE_INT);
    $invalidReadingId = array_key_exists('reading_id', $_POST)
        && (!$readingId || $readingId < 1);
    if (in_array($action, ['archive', 'restore'], true)) {
        if (!$readingId || !$readingRepository->setArchived((int) $readingId, $action === 'archive')) {
            flash('reading_message', 'The reading was not found.');
        } else {
            flash('reading_message', $action === 'archive'
                ? 'Reading archived. Its original data remains in history.'
                : 'Reading restored to current results.');
        }
        redirect_to('readings.php');
    }

    $locationId = filter_var($_POST['location_id'] ?? null, FILTER_VALIDATE_INT);
    $existing = $readingId && $readingId > 0 ? $readingRepository->find((int) $readingId) : null;
    $activeLocationIds = array_map(static fn(array $location): int => (int) $location['location_id'], $activeLocations);
    $existingLocationMayRemain = $existing && (int) $existing['location_id'] === (int) $locationId;
    $locationAllowed = $locationId && $locationId > 0
        && (in_array((int) $locationId, $activeLocationIds, true) || $existingLocationMayRemain);

    $rainfallFields = ['rainfall_1h_mm', 'rainfall_24h_mm', 'rainfall_72h_mm'];
    $rainfall = [];
    $rainfallValid = true;
    $hasRainfall = false;
    foreach ($rainfallFields as $field) {
        $raw = trim(post_string($field));
        if ($raw === '') {
            $rainfall[$field] = null;
        } elseif (preg_match('/\A\d{1,5}(?:\.\d{1,2})?\z/', $raw) === 1 && (float) $raw <= 99999.99) {
            $rainfall[$field] = (float) $raw;
            $hasRainfall = true;
        } else {
            $rainfallValid = false;
            $rainfall[$field] = null;
        }
    }

    $sourceName = trim(post_string('source_name'));
    $sourceUrl = trim(post_string('source_url'));
    $observedInput = trim(post_string('observed_at'));
    $sourceNameLength = preg_match_all('/./us', $sourceName);
    $validSourceUrl = $sourceUrl === '' || (
        filter_var($sourceUrl, FILTER_VALIDATE_URL) !== false
        && in_array(strtolower((string) parse_url($sourceUrl, PHP_URL_SCHEME)), ['http', 'https'], true)
        && strlen($sourceUrl) <= 500
    );
    $observedLocal = DateTimeImmutable::createFromFormat(
        '!Y-m-d\\TH:i:s',
        $observedInput,
        new DateTimeZone('Asia/Manila')
    );
    $dateErrors = DateTimeImmutable::getLastErrors();
    $observedValid = $observedLocal !== false
        && ($dateErrors === false || ($dateErrors['warning_count'] === 0 && $dateErrors['error_count'] === 0))
        && $observedLocal->format('Y-m-d\\TH:i:s') === $observedInput;

    if ($invalidReadingId || (!$existing && $readingId)) {
        flash('reading_message', 'The reading was not found.');
        redirect_to('readings.php');
    }
    if ($existing && (int) $existing['is_archived'] === 1) {
        flash('reading_message', 'Restore an archived reading before editing it.');
        redirect_to('readings.php');
    }
    if (!$locationAllowed || !$rainfallValid || !$hasRainfall
        || $sourceNameLength === false || $sourceNameLength < 1 || $sourceNameLength > 150
        || !$validSourceUrl || !$observedValid) {
        flash('reading_message', 'Choose an active location, enter at least one valid rainfall value, source name, and valid observation time.');
        redirect_to('readings.php');
    }

    $readingData = $rainfall + [
        'location_id' => (int) $locationId,
        'source_name' => $sourceName,
        'source_url' => $sourceUrl === '' ? null : $sourceUrl,
        'observed_at' => $observedLocal->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s'),
    ];

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
