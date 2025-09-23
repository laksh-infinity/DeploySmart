<?php
header('Content-Type: text/html; charset=utf-8');

# Configuration values
define('DEPLOYSMART_BASE_URL', 'https://your.subdomain.domain.com');
define('DEPLOYSMART_TEMP_DIR', sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'deploysmart');

if (!is_dir(DEPLOYSMART_TEMP_DIR)) {
    mkdir(DEPLOYSMART_TEMP_DIR, 0700, true);
}

define('SMTP_HOST', 'SMTP.YOURDOMAIN.COM');
define('SMTP_PORT', 587);
define('SMTP_USER', 'SMTP-USER');
define('SMTP_PASS', 'SMTP-PASS');
define('SMTP_FROM', 'no-reply@yourdomain.com');

define('DB_HOST', 'localhost');
define('DB_NAME', 'Your-DB');
define('DB_USER', 'Your-DB-User');
define('DB_PASS', 'Your-DB-Pass');
define('DB_CHARSET', 'utf8mb4');

# PDO setup
$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

$options = [
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_swedish_ci",
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    throw new PDOException($e->getMessage(), (int)$e->getCode());
}

# Error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>