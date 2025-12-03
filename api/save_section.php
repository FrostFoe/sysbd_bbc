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
    $lang = $lang === "en" ? "en" : "bn";

    $data = json_decode(file_get_contents("php://input"), true);

    if (empty($data["id"]) || empty($data["title"]) || empty($data["type"])) {
        throw new Exception("Missing required fields");
    }

    $id = $data["id"];
    $title = $data["title"];
    $type = $data["type"];
    $highlight_color = $data["highlight_color"] ?? null;
    $associated_category = $data["associated_category"] ?? null;
    $style = $data["style"] ?? null;
    $sort_order = $data["sort_order"] ?? 0;

    // Check if section exists for this language
    $stmt = $pdo->prepare("SELECT id FROM sections WHERE id = ? AND lang = ?");
    $stmt->execute([$id, $lang]);
    $exists = $stmt->fetch();

    if ($exists) {
        // Update
        $stmt = $pdo->prepare(
            "UPDATE sections SET title = ?, type = ?, highlight_color = ?, associated_category = ?, style = ?, sort_order = ? WHERE id = ? AND lang = ?",
        );
        $stmt->execute([
            $title,
            $type,
            $highlight_color,
            $associated_category,
            $style,
            $sort_order,
            $id,
            $lang,
        ]);
    } else {
        // Insert
        $stmt = $pdo->prepare(
            "INSERT INTO sections (id, lang, title, type, highlight_color, associated_category, style, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
        );
        $stmt->execute([
            $id,
            $lang,
            $title,
            $type,
            $highlight_color,
            $associated_category,
            $style,
            $sort_order,
        ]);
    }

    echo json_encode([
        "success" => true,
        "message" => $exists ? "Section updated" : "Section created",
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage(),
    ]);
}
?>
