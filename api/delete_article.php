<?php
require_once 'api_header.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_response(['error' => 'Method not allowed'], 405);
}

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? $_POST['id'] ?? null;

if (!$id) {
    send_response(['error' => 'ID required'], 400);
}

$stmt = $pdo->prepare("DELETE FROM articles WHERE id = ?");
$stmt->execute([$id]);

send_response(['success' => true]);
?>
