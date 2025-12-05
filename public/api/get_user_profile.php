<?php
require_once "api_header.php";
session_start();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    send_response(["error" => "Method not allowed"], 405);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$userId = $data["userId"] ?? null;
$userName = $data["userName"] ?? null;

if (!$userId && !$userName) {
    send_response(["error" => "User ID or name required"], 400);
    exit;
}

// Get user info
$userStmt = $pdo->prepare("SELECT id, email FROM users WHERE id = ? OR email = ?");
$userStmt->execute([$userId, $userName]);
$user = $userStmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    send_response(["error" => "User not found"], 404);
    exit;
}

$userId = $user['id'];
$email = $user['email'];

// Extract display name from email
$displayName = explode('@', $email)[0];

// Get comment count
$commentStmt = $pdo->prepare("
    SELECT COUNT(*) as count FROM comments 
    WHERE user_id = ? OR user_name = ?
");
$commentStmt->execute([$userId, $displayName]);
$commentCount = $commentStmt->fetch(PDO::FETCH_ASSOC)['count'];

// Get total upvotes received
$voteStmt = $pdo->prepare("
    SELECT 
        SUM(CASE WHEN cv.vote_type = 'upvote' THEN 1 ELSE 0 END) as upvotes,
        SUM(CASE WHEN cv.vote_type = 'downvote' THEN 1 ELSE 0 END) as downvotes
    FROM comment_votes cv
    INNER JOIN comments c ON cv.comment_id = c.id
    WHERE c.user_id = ? OR c.user_name = ?
");
$voteStmt->execute([$userId, $displayName]);
$votes = $voteStmt->fetch(PDO::FETCH_ASSOC);
$upvotes = (int)($votes['upvotes'] ?? 0);
$downvotes = (int)($votes['downvotes'] ?? 0);
$score = $upvotes - $downvotes;

// Get helpful score percentage (simple calculation)
$helpfulPercent = $commentCount > 0 ? round(($upvotes / max($commentCount, 1)) * 100) : 0;
$helpfulPercent = min($helpfulPercent, 100); // Cap at 100%

// Get recent comments (last 5)
$recentStmt = $pdo->prepare("
    SELECT c.text, c.created_at, 
        (SELECT COUNT(*) FROM comment_votes WHERE comment_id = c.id AND vote_type = 'upvote') as up
    FROM comments c
    WHERE c.user_id = ? OR c.user_name = ?
    ORDER BY c.created_at DESC
    LIMIT 5
");
$recentStmt->execute([$userId, $displayName]);
$recentComments = $recentStmt->fetchAll(PDO::FETCH_ASSOC);

// Determine badges
$badges = [];
if ($commentCount >= 50) {
    $badges[] = "👑 Active Member";
}
if ($upvotes >= 20) {
    $badges[] = "⭐ Helpful Contributor";
}
if ($commentCount >= 100) {
    $badges[] = "🏆 Expert Commentator";
}
if ($helpfulPercent >= 80 && $commentCount >= 10) {
    $badges[] = "✅ Trusted Member";
}
if (empty($badges)) {
    $badges[] = "🌟 New Member";
}

send_response([
    "success" => true,
    "profile" => [
        "displayName" => htmlspecialchars($displayName),
        "email" => htmlspecialchars($email),
        "commentCount" => $commentCount,
        "upvotes" => $upvotes,
        "downvotes" => $downvotes,
        "score" => $score,
        "helpfulPercent" => $helpfulPercent,
        "badges" => $badges,
        "recentComments" => array_map(function($c) {
            return [
                "text" => htmlspecialchars(substr($c['text'], 0, 100)) . (strlen($c['text']) > 100 ? "..." : ""),
                "upvotes" => $c['up'],
                "time" => date('M d, Y', strtotime($c['created_at']))
            ];
        }, $recentComments)
    ]
]);
?>
