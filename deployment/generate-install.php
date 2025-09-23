<?php
// Define paths relative to this file
$templatePath = __DIR__ . '/../templates/install.ps1';
$configDir = __DIR__ . '/../config';

// Load the template
$template = file_get_contents($templatePath);
if ($template === false) {
#    echo "❌ Failed to read template file at $templatePath\n";
    exit(1);
}

$updated = 0;
$skipped = 0;

// Loop through each config directory
foreach (scandir($configDir) as $entry) {
    $dirPath = $configDir . '/' . $entry;
    if ($entry === '.' || $entry === '..' || !is_dir($dirPath)) {
        continue;
    }

    $directoryId = $entry;
    $configuredScript = str_replace('{DEPLOYMENT_ID}', $directoryId, $template);
    $targetPath = $dirPath . '/install.ps1';

    if (file_put_contents($targetPath, $configuredScript) !== false) {
#        echo "✅ Updated: $targetPath\n";
        $updated++;
    } else {
#        echo "⚠️ Failed to update: $targetPath\n";
        $skipped++;
    }
}
echo "✅ All install scripts updated";
# echo "❌ $skipped scripts failed\n";
# $updated

?>