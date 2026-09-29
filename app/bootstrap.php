<?php
declare(strict_types=1);

date_default_timezone_set('Asia/Manila');

if (session_status() !== PHP_SESSION_ACTIVE) {
    $isHttps = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_set_cookie_params([
        'httponly' => true,
        'secure' => $isHttps,
        'samesite' => 'Lax',
        'path' => '/',
    ]);
    session_start();
}

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/UserRepository.php';
require_once __DIR__ . '/ReportRepository.php';

$databaseConfig = require dirname(__DIR__) . '/configs/config.php';

try {
    $pdo = Database::connect($databaseConfig);
} catch (PDOException $exception) {
    error_log('SmartSlope database connection failed: ' . $exception->getMessage());
    http_response_code(503);
    exit('SmartSlope is temporarily unavailable. Check the local database configuration.');
}
