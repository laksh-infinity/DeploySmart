<?php
require __DIR__ . '/../db.php'; // Make sure DEPLOYSMART_BASE_URL is available

$templatePath = __DIR__ . '/../templates/install.ps1';
$configDir = __DIR__ . '/../config';

$template = file_get_contents($templatePath);
if ($template === false) {
    exit(1);
}

$updated = 0;
$skipped = 0;

foreach (scandir($configDir) as $entry) {
    $dirPath = $configDir . '/' . $entry;
    if ($entry === '.' || $entry === '..' || !is_dir($dirPath)) {
        continue;
    }

    $directoryId = $entry;

    // Replace both placeholders
    $configuredScript = str_replace(
        ['{DEPLOYMENT_ID}', 'Toothbrush'],
        [$directoryId, DEPLOYSMART_BASE_URL],
        $template
    );

    $targetPath = $dirPath . '/install.ps1';

    if (file_put_contents($targetPath, $configuredScript) !== false) {
        $updated++;
    } else {
        $skipped++;
    }
}

echo "✅ All install scripts updated";
