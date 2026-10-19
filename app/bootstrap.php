<?php
/**
 * Loaded first by every page: config, session, database and helper functions.
 */
declare(strict_types=1);

define('ROOT', dirname(__DIR__));
define('PUBLIC_DIR', ROOT . '/public');

$configFile = ROOT . '/config/config.php';
if (!is_file($configFile)) {
    http_response_code(500);
    exit('ยังไม่มีไฟล์ config/config.php — คัดลอกจาก config/config.sample.php แล้วแก้ค่าฐานข้อมูล');
}
$CONFIG = require $configFile;

date_default_timezone_set($CONFIG['timezone'] ?? 'Asia/Bangkok');

if (!empty($CONFIG['debug'])) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
}

require __DIR__ . '/db.php';
require __DIR__ . '/session_db.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name('GOSESSID');
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ]);
    session_set_save_handler(new DbSessionHandler(), true);
    session_start();
}

require __DIR__ . '/helpers.php';
require __DIR__ . '/auth.php';
require __DIR__ . '/data.php';
