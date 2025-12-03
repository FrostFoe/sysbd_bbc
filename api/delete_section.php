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

    $data = json_decode(file_get_contents("php://input"), true);

    if (empty($data["id"])) {
        throw new Exception("Section ID required");
    }

    $id = $data["id"];

    // Delete section for this language
    $stmt = $pdo->prepare("DELETE FROM sections WHERE id = ? AND lang = ?");
    $stmt->execute([$id, $lang]);

    echo json_encode([
        "success" => true,
        "message" => "Section deleted",
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage(),
    ]);
}
?>
