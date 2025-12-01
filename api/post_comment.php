<?php
require_once 'api_header.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_response(['error' => 'Method not allowed'], 405);
}

$data = json_decode(file_get_contents('php://input'), true);
$articleId = $data['articleId'] ?? $_POST['articleId'] ?? null;
$user = $data['user'] ?? $_POST['user'] ?? 'Anonymous';
$text = $data['text'] ?? $_POST['text'] ?? '';

if (!$articleId || !$text) {
    send_response(['error' => 'Missing fields'], 400);
}

$stmt = $pdo->prepare("INSERT INTO comments (article_id, user_name, text, time) VALUES (?, ?, ?, 'এইমাত্র')");
$stmt->execute([$articleId, $user, $text]);

send_response(['success' => true]);
?>
