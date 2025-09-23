<?php
$script_dir = __DIR__ . '/scripts/apps';
$base_url = 'https://deploysmart.dev.mspot.se/deployment/scripts/apps/';
$output_file = __DIR__ . '/applications.available.json';

$files = scandir($script_dir);
$normal_apps = [];
$hash_apps = [];

foreach ($files as $file) {
    if (pathinfo($file, PATHINFO_EXTENSION) === 'ps1') {
        $filename = pathinfo($file, PATHINFO_FILENAME);
        $name = str_replace(['_', '#'], [' ', ''], $filename); // Replace _ with space, remove #
        $entry = [
            "Name" => $name,
            "Url" => $base_url . $file
        ];

        if (strpos($file, '-') !== false) {
            $hash_apps[] = $entry;
        } else {
            $normal_apps[] = $entry;
        }
    }
}

$app_list = array_merge($normal_apps, $hash_apps);

file_put_contents($output_file, json_encode($app_list, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
echo "✅ All global apps processed.<br>";
?>
