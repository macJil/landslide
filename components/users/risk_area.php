<section class="card" aria-labelledby="risk-heading">
    <div class="card-header"><h2 class="h5 mb-0" id="risk-heading">Latest location reading</h2></div>
    <div class="card-body">
        <label class="form-label" for="risk-location-select">Study area location</label>
        <select class="form-select mb-3" id="risk-location-select">
            <option value="">Choose a location</option>
            <?php foreach ($riskLocations as $location): ?>
                <option value="<?= (int) $location['location_id'] ?>">
                    <?= e($location['location_name']) ?><?= $location['purok_zone'] ? ' — ' . e($location['purok_zone']) : '' ?>
                </option>
            <?php endforeach; ?>
        </select>
        <p id="reading-message" class="small text-muted" role="status">Choose a location to view its latest reading.</p>
        <div id="reading-panel" hidden>
            <p><strong>Prototype risk level:</strong> <span id="risk-level">Not available</span></p>
            <dl class="row mb-0">
                <dt class="col-sm-6">Rainfall, 1 hour</dt><dd class="col-sm-6" id="rainfall-1h">Not available</dd>
                <dt class="col-sm-6">Rainfall, 24 hours</dt><dd class="col-sm-6" id="rainfall-24h">Not available</dd>
                <dt class="col-sm-6">Rainfall, 72 hours</dt><dd class="col-sm-6" id="rainfall-72h">Not available</dd>
                <dt class="col-sm-6">Data source</dt><dd class="col-sm-6" id="reading-source">Not available</dd>
                <dt class="col-sm-6">Observed</dt><dd class="col-sm-6" id="reading-observed-at">Not available</dd>
            </dl>
        </div>
        <?php if (!$riskLocations): ?>
            <p class="mb-0">No active locations are available in the study area.</p>
        <?php endif; ?>
        <hr>
        <p class="small mb-0"><?= e(RiskAnalyzer::description()) ?> This prototype is not an official landslide warning.</p>
    </div>
</section>
