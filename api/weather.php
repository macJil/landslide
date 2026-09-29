<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, max-age=0');

function weather_json(int $status, array $payload): void
{
    http_response_code($status);
    echo json_encode(
        $payload,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE
    );
    exit;
}

require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../app/WeatherApiClient.php';

if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'user') {
    weather_json(401, ['error' => 'authentication_required']);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    weather_json(405, ['error' => 'method_not_allowed']);
}

if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
    weather_json(403, ['error' => 'invalid_csrf_token']);
}

$locationId = filter_var($_POST['location_id'] ?? null, FILTER_VALIDATE_INT);
if (!$locationId || $locationId < 1) {
    weather_json(400, ['error' => 'invalid_location_id']);
}

$locationStatement = $pdo->prepare(
    "SELECT l.location_id, l.location_name, l.purok_zone, l.latitude, l.longitude
     FROM locations AS l
     INNER JOIN barangays AS b ON b.barangay_id = l.barangay_id
     WHERE l.location_id = :location_id
       AND l.is_active = 1 AND b.is_active = 1
       AND b.barangay_name = 'Barangay Irisan' AND b.city_name = 'Baguio City'
     LIMIT 1"
);
$locationStatement->execute(['location_id' => (int) $locationId]);
$location = $locationStatement->fetch();

if (!$location) {
    weather_json(404, ['error' => 'location_not_found']);
}
if ($location['latitude'] === null || $location['longitude'] === null) {
    weather_json(422, ['error' => 'location_coordinates_missing']);
}

try {
    $weather = (new WeatherApiClient())->fetch(
        (float) $location['latitude'],
        (float) $location['longitude']
    );

    $timezone = new DateTimeZone('Asia/Manila');
    $currentTime = new DateTimeImmutable((string) $weather['current']['time'], $timezone);
    $earliestTime = $currentTime->modify('-72 hours');
    $hourlyData = $weather['hourly'];
    $hourlyFields = [
        'temperature_2m', 'relative_humidity_2m', 'apparent_temperature',
        'precipitation', 'rain', 'showers', 'weather_code', 'cloud_cover',
        'pressure_msl', 'surface_pressure', 'wind_speed_10m',
        'wind_direction_10m', 'wind_gusts_10m', 'soil_moisture_0_to_1cm',
        'soil_moisture_1_to_3cm', 'soil_moisture_3_to_9cm',
        'soil_moisture_9_to_27cm', 'soil_moisture_27_to_81cm',
    ];

    $history = [];
    foreach ($hourlyData['time'] as $index => $timeValue) {
        if (!is_string($timeValue)) {
            continue;
        }
        $time = new DateTimeImmutable($timeValue, $timezone);
        if ($time > $currentTime || $time < $earliestTime) {
            continue;
        }

        $row = ['time' => $timeValue];
        foreach ($hourlyFields as $field) {
            $value = $hourlyData[$field][$index] ?? null;
            $row[$field] = is_numeric($value) ? (float) $value : null;
        }
        $history[] = $row;
    }

    $history = array_slice($history, -72);
    if (!$history) {
        weather_json(502, ['error' => 'weather_history_unavailable']);
    }

    $sumHours = static function (array $rows, int $hours): ?float {
        if (count($rows) < $hours) {
            return null;
        }
        $window = array_slice($rows, -$hours);
        $sum = 0.0;
        foreach ($window as $row) {
            if (!is_float($row['precipitation'])) {
                return null;
            }
            $sum += $row['precipitation'];
        }
        return round($sum, 2);
    };

    $rainfall1h = $sumHours($history, 1);
    $rainfall24h = $sumHours($history, 24);
    $rainfall72h = $sumHours($history, 72);
    $riskLevel = $rainfall1h === null && $rainfall24h === null && $rainfall72h === null
        ? null
        : RiskAnalyzer::analyze($rainfall1h, $rainfall24h, $rainfall72h);

    $lastHourlyReading = $history[count($history) - 1];
    $observedAtUtc = (new DateTimeImmutable($lastHourlyReading['time'], $timezone))
        ->setTimezone(new DateTimeZone('UTC'))
        ->format('Y-m-d H:i:s');

    // Persist the rainfall summary so the existing admin readings page can
    // review the provider-backed readings. Repeated requests update the same
    // location/hour row; archived readings remain archived.
    if ($riskLevel !== null) {
        (new ReadingRepository($pdo))->createFromApi([
            'location_id' => (int) $location['location_id'],
            'rainfall_1h_mm' => $rainfall1h,
            'rainfall_24h_mm' => $rainfall24h,
            'rainfall_72h_mm' => $rainfall72h,
            'source_name' => 'Open-Meteo',
            'source_url' => 'https://open-meteo.com/',
            'observed_at' => $observedAtUtc,
        ]);
    }

    weather_json(200, [
        'data' => [
            'location' => [
                'location_id' => (int) $location['location_id'],
                'location_name' => $location['location_name'],
                'purok_zone' => $location['purok_zone'],
            ],
            'source_name' => 'Open-Meteo',
            'source_url' => 'https://open-meteo.com/',
            'retrieved_at' => (new DateTimeImmutable('now', new DateTimeZone('UTC')))
                ->format(DateTimeInterface::ATOM),
            'current' => $weather['current'],
            'rainfall' => [
                'rainfall_1h_mm' => $rainfall1h,
                'rainfall_24h_mm' => $rainfall24h,
                'rainfall_72h_mm' => $rainfall72h,
                'risk_level' => $riskLevel,
                'observed_at' => $observedAtUtc,
            ],
            'hourly' => $history,
        ],
    ]);
} catch (Throwable $exception) {
    error_log('SmartSlope weather API request failed: ' . $exception->getMessage());
    weather_json(503, ['error' => 'weather_provider_unavailable']);
}
