<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/bootstrap.php';
require_user();
$reportLocations = (new ReportRepository($pdo))->activeLocations();
$riskLocations = (new LocationRepository($pdo))->activeForStudyArea();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartSlope Resident</title>
    <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
    <script src="../../assets/js/bootstrap.bundle.js" defer></script>
</head>
<body>
<header class="nav" style="background-color: aliceblue; display:flex; justify-content:space-between; align-items:center; padding:10px 20px;">
    <h1>Barangay Irisan (Baguio City) — SmartSlope</h1>
    <form action="../../configs/logout.php" method="post">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <button class="btn btn-outline-danger" type="submit">Log out</button>
    </form>
</header>

<main class="container my-4">
    <h2>Welcome, <?= e($_SESSION['full_name'] ?? 'Resident') ?></h2>
    <p>Signed in as <?= e($_SESSION['username'] ?? '') ?></p>

    <?php if ($message = flash('report_success')): ?>
        <div class="alert alert-success" role="status"><?= e($message) ?></div>
    <?php elseif ($message = flash('report_error')): ?>
        <div class="alert alert-danger" role="alert"><?= e($message) ?></div>
    <?php endif; ?>

    <div class="row g-3">
        <div class="col-lg-6"><?php include __DIR__ . '/risk_area.php'; ?></div>
        <div class="col-lg-6"><?php include __DIR__ . '/report.php'; ?></div>
    </div>
</main>
<?php include __DIR__ . '/../footer.html'; ?>
<script src="../../assets/js/vendor/jquery.min.js"></script>
<script src="../../assets/js/app.js"></script>
</body>
</html>
