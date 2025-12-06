<?php
require_once "../../src/config/db.php";

$file_path = isset($_GET["file"]) ? $_GET["file"] : null;

if (!$file_path) {
    http_response_code(400);
    echo "File path required";
    exit();
}

// Sanitize path to prevent directory traversal
$file_path = str_replace("..", "", $file_path);
$file_path = str_replace("//", "/", $file_path);
$file_path = ltrim($file_path, "/");

// Build full path from assets/uploads
$base_dir = __DIR__ . "/../assets/uploads/";
$full_path = realpath($base_dir . $file_path);

// Verify path is within uploads directory
if (!$full_path || strpos($full_path, realpath($base_dir)) !== 0) {
    http_response_code(403);
    echo "Access denied";
    exit();
}

if (!file_exists($full_path) || !is_file($full_path)) {
    http_response_code(404);
    echo "File not found";
    exit();
}

// Get file info
$file_name = basename($full_path);
$file_size = filesize($full_path);

// Determine MIME type
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$file_type = finfo_file($finfo, $full_path);
finfo_close($finfo);
$file_type = $file_type ?: "application/octet-stream";

// Send file
header("Content-Type: " . $file_type);
header(
    'Content-Disposition: attachment; filename="' .
        addslashes($file_name) .
        '"',
);
header("Content-Length: " . $file_size);
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
header("Accept-Ranges: bytes");

readfile($full_path);
exit();
?>
