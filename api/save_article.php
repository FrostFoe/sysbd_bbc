<?php
require_once "api_header.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    send_response(["error" => "Method not allowed"], 405);
}

$id = !empty($_POST["id"]) ? $_POST["id"] : uniqid(); // Use existing ID or generate new
$title = $_POST["title"] ?? "";
$category = $_POST["category"] ?? "খবর";
$summary = $_POST["summary"] ?? "";
$content = $_POST["content"] ?? "";
$sectionId = $_POST["sectionId"] ?? "news";
$image = $_POST["image"] ?? "";

$stmt = $pdo->prepare("SELECT id FROM articles WHERE id = ?");
$stmt->execute([$id]);
$exists = $stmt->fetch();

if ($exists) {
    $stmt = $pdo->prepare(
        "UPDATE articles SET title=?, summary=?, image=?, category=?, content=?, section_id=? WHERE id=?",
    );
    $stmt->execute([
        $title,
        $summary,
        $image,
        $category,
        $content,
        $sectionId,
        $id,
    ]);
} else {
    $stmt = $pdo->prepare(
        "INSERT INTO articles (id, section_id, title, summary, image, category, content, timestamp, read_time) VALUES (?, ?, ?, ?, ?, ?, ?, 'সদ্য', '৩ মিনিট')",
    );
    $stmt->execute([
        $id,
        $sectionId,
        $title,
        $summary,
        $image,
        $category,
        $content,
    ]);
}

send_response(["success" => true, "id" => $id]);
?>
