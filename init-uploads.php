<?php
/**
 * Upload Directories Initialization Script
 * Creates proper directory structure for all uploads
 */

$base_upload_dir = __DIR__ . "/public/assets/uploads";

// Directory structure to create
$directories = [
    "images/articles" => 0755,
    "images/profiles" => 0755,
    "videos" => 0755,
    "audio" => 0755,
    "media/videos" => 0755,
    "media/audio" => 0755,
    "documents" => 0755,
];

echo "Creating upload directory structure...\n";
echo "Base directory: " . $base_upload_dir . "\n\n";

foreach ($directories as $subdir => $mode) {
    $full_path = $base_upload_dir . "/" . $subdir;

    if (!is_dir($full_path)) {
        if (@mkdir($full_path, $mode, true)) {
            echo "✅ Created: " . $subdir . "\n";
        } else {
            echo "❌ Failed: " . $subdir . "\n";
        }
    } else {
        echo "✓ Exists: " . $subdir . "\n";
    }
}

// Create .gitkeep files to preserve directories
echo "\nAdding .gitkeep files...\n";
foreach ($directories as $subdir => $mode) {
    $full_path = $base_upload_dir . "/" . $subdir . "/.gitkeep";

    if (!file_exists($full_path)) {
        if (@file_put_contents($full_path, "")) {
            echo "✅ Created: " . $subdir . "/.gitkeep\n";
        }
    }
}

// Check permissions
echo "\nVerifying permissions...\n";
foreach ($directories as $subdir => $mode) {
    $full_path = $base_upload_dir . "/" . $subdir;
    $perms = fileperms($full_path);
    $octal = substr(sprintf("%o", $perms), -4);
    echo "Permissions for " . $subdir . ": " . $octal . "\n";
}

echo "\n✅ Upload directories initialized successfully!\n";
?>
