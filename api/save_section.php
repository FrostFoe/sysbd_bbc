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
    $data = json_decode(file_get_contents("php://input"), true);

    if (empty($data["id"]) || empty($data["type"])) {
        throw new Exception("Missing required fields: id and type");
    }

    $id = $data["id"];
    $title_bn = $data["title_bn"] ?? null;
    $title_en = $data["title_en"] ?? null;
    $type = $data["type"];
    $highlight_color = $data["highlight_color"] ?? null;
    $associated_category = $data["associated_category"] ?? null;
    $style = $data["style"] ?? null;
    $sort_order = $data["sort_order"] ?? 0;
    $lang = $data["lang"] ?? "bn";
    $lang = $lang === "en" ? "en" : "bn";

    // Get the title for the current language
    $title = $lang === "en" ? $title_en : $title_bn;
    if (empty($title)) {
        throw new Exception("Title is required for the selected language");
    }

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
        $message = "সেকশন আপডেট হয়েছে";
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
        $message = "সেকশন তৈরি হয়েছে";
    }

    echo json_encode([
        "success" => true,
        "message" => $message,
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage(),
    ]);
}
?>
