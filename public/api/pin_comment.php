<?php
require_once "api_header.php";
session_start();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    send_response(["error" => "Method not allowed"], 405);
    exit;
}

// Only admin can pin comments
if (!isset($_SESSION["user_role"]) || $_SESSION["user_role"] !== "admin") {
    send_response(["error" => "Only admin can pin comments"], 403);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$commentId = $data["commentId"] ?? null;
$isPinned = $data["isPinned"] ?? false;

if (!$commentId) {
    send_response(["error" => "commentId required"], 400);
    exit;
}

// Verify comment exists
$stmt = $pdo->prepare("SELECT is_pinned FROM comments WHERE id = ?");
$stmt->execute([$commentId]);
$comment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$comment) {
    send_response(["error" => "Comment not found"], 404);
    exit;
}

if ($isPinned) {
    // Get next pin order
    $maxOrderStmt = $pdo->prepare("SELECT MAX(pin_order) as max_order FROM comments WHERE is_pinned = 1");
    $maxOrderStmt->execute();
    $result = $maxOrderStmt->fetch(PDO::FETCH_ASSOC);
    $nextOrder = (int)($result['max_order'] ?? 0) + 1;
    
    $updateStmt = $pdo->prepare("UPDATE comments SET is_pinned = 1, pin_order = ? WHERE id = ?");
    $updateStmt->execute([$nextOrder, $commentId]);
    $message = "Comment pinned successfully";
} else {
    // Unpin and reset order
    $updateStmt = $pdo->prepare("UPDATE comments SET is_pinned = 0, pin_order = 0 WHERE id = ?");
    $updateStmt->execute([$commentId]);
    $message = "Comment unpinned successfully";
}

send_response([
    "success" => true,
    "isPinned" => $isPinned,
    "message" => $message
]);
?>
