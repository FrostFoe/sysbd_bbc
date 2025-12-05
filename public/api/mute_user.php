<?php
require_once "api_header.php";
session_start();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    send_response(["error" => "Method not allowed"], 405);
    exit();
}

// Only admin can mute/unmute users
if (!isset($_SESSION["user_role"]) || $_SESSION["user_role"] !== "admin") {
    send_response(["error" => "Only admin can mute users"], 403);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);
$userId = $data["userId"] ?? null;
$action = $data["action"] ?? null; // 'mute' or 'unmute'
$reason = htmlspecialchars($data["reason"] ?? "", ENT_QUOTES, "UTF-8");

if (!$userId || !in_array($action, ["mute", "unmute"])) {
    send_response(
        ["error" => "Missing or invalid fields (userId, action)"],
        400,
    );
    exit();
}

// Verify user exists
$userStmt = $pdo->prepare("SELECT id, email FROM users WHERE id = ?");
$userStmt->execute([$userId]);
$user = $userStmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    send_response(["error" => "User not found"], 404);
    exit();
}

// Prevent admin from muting themselves
if ($userId == $_SESSION["user_id"]) {
    send_response(["error" => "You cannot mute yourself"], 400);
    exit();
}

$adminId = $_SESSION["user_id"];

if ($action === "mute") {
    // Check if already muted
    $checkStmt = $pdo->prepare("SELECT id FROM muted_users WHERE user_id = ?");
    $checkStmt->execute([$userId]);

    if ($checkStmt->fetch()) {
        send_response(["error" => "User is already muted"], 400);
        exit();
    }

    // Mute user
    $insertStmt = $pdo->prepare(
        "INSERT INTO muted_users (user_id, muted_by_admin_id, reason) VALUES (?, ?, ?)",
    );
    $insertStmt->execute([$userId, $adminId, $reason]);
    $message = "User muted successfully";
} else {
    // Unmute user
    $deleteStmt = $pdo->prepare("DELETE FROM muted_users WHERE user_id = ?");
    $deleteStmt->execute([$userId]);
    $message = "User unmuted successfully";
}

send_response([
    "success" => true,
    "userId" => $userId,
    "action" => $action,
    "userEmail" => $user["email"],
    "message" => $message,
]);
?>
