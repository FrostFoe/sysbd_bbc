<?php
require_once "api_header.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    send_response(["error" => "Method not allowed"], 405);
}

$data = json_decode(file_get_contents("php://input"), true);
$articleId = $data["articleId"] ?? null;
$user = $data["user"] ?? "Anonymous";
$text = $data["text"] ?? "";

if (!$articleId || !$text) {
    send_response(["error" => "Missing fields"], 400);
}

$time = "Just now";

$stmt = $pdo->prepare(
    "INSERT INTO comments (article_id, user_name, text, time) VALUES (?, ?, ?, ?)"
);
$stmt->execute([$articleId, $user, $text, $time]);

send_response(["success" => true]);
?>
