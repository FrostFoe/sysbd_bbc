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
$text = htmlspecialchars($data["text"] ?? "", ENT_QUOTES, 'UTF-8');

if (!$articleId || !$text) {
    send_response(["error" => "Missing required fields (articleId, text)"], 400);
    exit;
}

// Determine User
$userId = null;
$userName = "Anonymous";

// Check if logged in (assuming standard session keys)
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $userName = $_SESSION['user_email']; // Fallback if name not available
} elseif (isset($_SESSION['user_email'])) {
    // Fetch ID if not stored in session (bad practice, but fits current structure)
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$_SESSION['user_email']]);
    $u = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($u) {
        $userId = $u['id'];
        $userName = $_SESSION['user_email'];
    }
} else {
    // Guest
    $userName = htmlspecialchars($data["user"] ?? "Anonymous", ENT_QUOTES, 'UTF-8');
}

// --- Dynamic Timestamp ---
// DB handles created_at. We don't need to manually insert time unless we want to override.
// The 'time' column is deprecated.
$stmt = $pdo->prepare(
    "INSERT INTO comments (article_id, user_id, user_name, text) VALUES (?, ?, ?, ?)",
);
$stmt->execute([$articleId, $userId, $userName, $text]);

send_response(["success" => true]);
?>