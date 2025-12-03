<?php
header("Content-Type: application/json");
require_once __DIR__ . "/../includes/db.php";
require_once __DIR__ . "/check_auth.php";

// Check admin role
if (!isset($_SESSION["user_role"]) || $_SESSION["user_role"] !== "admin") {
    http_response_code(403);
    echo json_encode([
        "success" => false,
        "message" => "Unauthorized",
    ]);
    exit();
}

try {
    $lang = $_GET["lang"] ?? "bn";
    $lang = ($lang === "en") ? "en" : "bn";

    $stmt = $pdo->prepare("SELECT * FROM sections WHERE lang = ? ORDER BY sort_order ASC");
    $stmt->execute([$lang]);
    $sections = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "data" => $sections,
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage(),
    ]);
}
?>
