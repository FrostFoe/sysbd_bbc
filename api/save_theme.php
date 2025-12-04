<?php
require_once "api_header.php";
session_start();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    send_response(["error" => "Method not allowed"], 405);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$theme = $data["theme"] ?? null; // 'light' or 'dark'

if (!in_array($theme, ['light', 'dark'])) {
    send_response(["error" => "Invalid theme value"], 400);
    exit;
}

// Save to localStorage (handled by frontend)
// Also save to user preferences if logged in
if (isset($_SESSION['user_id'])) {
    // You can add a user_preferences table or column to users table
    // For now, we just save to localStorage
}

send_response([
    "success" => true,
    "message" => "Theme preference saved",
    "theme" => $theme
]);
?>
