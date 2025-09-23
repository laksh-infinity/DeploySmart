<?php
require '../assets/auth.php'; // ensures session is started and user is authenticated

// Check if user is logged in and is an admin
$stmt = $pdo->prepare("SELECT is_admin FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id'] ?? 0]);
$user = $stmt->fetch();

if (!$user || $user['is_admin'] != 1) {
    echo '<div class="content"><h2>Access Denied</h2><p>You do not have permission to delete scripts.</p></div>';
    exit;
}

header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

$data = json_decode(file_get_contents('php://input'), true);
$name = $data['name'] ?? '';
$directoryId = $data['directoryId'] ?? '';

if (!$name || !$directoryId) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing parameters']);
    exit;
}

$scriptPath = __DIR__ . "/../config/$directoryId/scripts/$name.ps1";
$jsonPath = __DIR__ . "/../config/$directoryId/applications.available.json";

if (!file_exists($scriptPath)) {
    http_response_code(404);
    echo json_encode(['error' => 'Script not found']);
    exit;
}

// Delete the script
unlink($scriptPath);

// Regenerate applications.available.json
$scriptFiles = glob(dirname($scriptPath) . '/*.ps1');
$apps = [];
$baseUrl = DEPLOYSMART_BASE_URL . '/config';

foreach ($scriptFiles as $path) {
    $scriptName = basename($path, '.ps1');
    $apps[] = [
        'Name' => $scriptName,
        'Url' => "$baseUrl/$directoryId/scripts/" . basename($path)
    ];
}

file_put_contents($jsonPath, json_encode($apps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo json_encode(['success' => true]);