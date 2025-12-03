<?php
require_once "api_header.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    send_response(["error" => "Method not allowed"], 405);
}

$lang = $_POST["lang"] ?? "bn";
$lang = ($lang === "en") ? "en" : "bn"; // Validate language

$id = !empty($_POST["id"]) ? $_POST["id"] : uniqid();
$title = $_POST["title"] ?? "";
$category_id = $_POST["category_id"] ?? "";
$summary = $_POST["summary"] ?? "";
$content = $_POST["content"] ?? "";
$sectionId = $_POST["sectionId"] ?? "news";
$image = $_POST["image"] ?? "";

$stmt = $pdo->prepare("SELECT id FROM articles WHERE id = ? AND lang = ?");
$stmt->execute([$id, $lang]);
$exists = $stmt->fetch();

if ($exists) {
    // Update article
    $stmt = $pdo->prepare(
        "UPDATE articles SET title=?, summary=?, image=?, category_id=?, content=?, section_id=? WHERE id=? AND lang=?"
    );
    $stmt->execute([
        $title,
        $summary,
        $image,
        $category_id,
        $content,
        $sectionId,
        $id,
        $lang,
    ]);
} else {
    // Create new article for this language
    $timestamp = $lang === "bn" ? "সদ্য" : "Just now";
    $read_time = $lang === "bn" ? "৩ মিনিট" : "3 min";
    
    $stmt = $pdo->prepare(
        "INSERT INTO articles (id, lang, section_id, title, summary, image, category_id, content, timestamp, read_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->execute([
        $id,
        $lang,
        $sectionId,
        $title,
        $summary,
        $image,
        $category_id,
        $content,
        $timestamp,
        $read_time,
    ]);
}

send_response(["success" => true, "id" => $id]);
?>
