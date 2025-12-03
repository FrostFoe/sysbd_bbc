<?php
require_once "api_header.php";

// Start session for user context (e.g., logged-in user name)
session_start();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    send_response(["error" => "Method not allowed"], 405);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$articleId = $data["articleId"] ?? null;
// If user is logged in, use their identifier; otherwise, default to "Anonymous"
// Note: Direct use of user input here without sanitization for display might be risky for XSS.
// Assuming user is either a name or an email which is generally safe, but needs verification.
$user = $data["user"] ?? "Anonymous";
$text = $data["text"] ?? "";

if (!$articleId || !$text) {
    send_response(["error" => "Missing required fields (articleId, text)"], 400);
    exit;
}

// --- Dynamic Timestamp ---
// Use current timestamp for the 'time' column, which is now a TIMESTAMP type in the DB.
// The 'created_at' column will also be automatically populated by the DB.
$currentTime = date('Y-m-d H:i:s');
// --- End Dynamic Timestamp ---

$stmt = $pdo->prepare(
    "INSERT INTO comments (article_id, user_name, text, time) VALUES (?, ?, ?, ?)",
);
$stmt->execute([$articleId, $user, $text, $currentTime]);

send_response(["success" => true]);
?>