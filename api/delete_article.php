<?php
require_once "api_header.php";

session_start();

// --- Authorization Check ---
if (!isset($_SESSION["user_role"]) || $_SESSION["user_role"] !== "admin") {
    send_response(["error" => "Unauthorized"], 403);
    exit; // Stop execution if unauthorized
}
// --- End Authorization Check ---

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    send_response(["error" => "Method not allowed"], 405);
}

$data = json_decode(file_get_contents("php://input"), true);
$id = $data["id"] ?? null;
$lang = $data["lang"] ?? "bn";
$lang = $lang === "en" ? "en" : "bn"; // Validate language

if (!$id) {
    send_response(["error" => "ID required"], 400);
}

$stmt = $pdo->prepare("DELETE FROM articles WHERE id = ? AND lang = ?");
$stmt->execute([$id, $lang]);

send_response(["success" => true]);
?>
