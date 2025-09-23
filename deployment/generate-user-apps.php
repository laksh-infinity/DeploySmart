<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Base config directory (relative to ./deployment/)
$baseDir = dirname(__DIR__) . '/config';
$baseUrl = 'https://deploysmart.dev.mspot.se/config'; // 🌐 Your domain prefix

$directories = glob($baseDir . '/ds_*', GLOB_ONLYDIR);

foreach ($directories as $dirPath) {
    $directoryId = basename($dirPath);
    $scriptsDir = $dirPath . '/scripts';
    $outputFile = $dirPath . '/applications.available.json';

    if (!is_dir($scriptsDir)) {
#        echo "Skipping $directoryId: no scripts folder\n";
        continue;
    }

    $scriptFiles = glob($scriptsDir . '/*.ps1');
    $apps = [];

    foreach ($scriptFiles as $scriptPath) {
        $scriptName = basename($scriptPath);
        $apps[] = [
            'Name' => basename($scriptPath, '.ps1'),
            'Url' => "$baseUrl/$directoryId/scripts/$scriptName" // ✅ Full domain path
        ];
    }

    file_put_contents($outputFile, json_encode($apps, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
#    echo "Updated $directoryId/applications.available.json with " . count($apps) . " scripts\n";
}

echo "✅ All user app directories processed.<br>";