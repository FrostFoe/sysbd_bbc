<?php
require_once "api_header.php";

// Helper function to calculate read time (simple example)
function calculate_read_time_from_text($text, $lang = 'en') {
    $word_count = str_word_count(strip_tags($text));
    $words_per_minute = 200; // Average reading speed
    $minutes = ceil($word_count / $words_per_minute);
    $minutes = max(1, $minutes); // Ensure at least 1 minute

    if ($lang === 'bn') {
        // Bengali translation for minutes
        $bengali_digits = ["০", "১", "২", "৩", "৪", "৫", "৬", "৭", "৮", "৯"];
        $minute_str = " মিনিট"; // Using plural form for consistency
        // Convert minutes to Bengali digits
        $tens = floor($minutes / 10);
        $ones = $minutes % 10;
        return ($tens > 0 ? $bengali_digits[$tens] : '') . $bengali_digits[$ones] . $minute_str;
    } else {
        return $minutes . " min";
    }
}

// Start session for authentication checks
session_start();

// --- Authorization Check ---
if (!isset($_SESSION["user_role"]) || $_SESSION["user_role"] !== "admin") {
    send_response(["error" => "Unauthorized"], 403);
    exit; // Stop execution if unauthorized
}
// --- End Authorization Check ---

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    send_response(["error" => "Method not allowed"], 405);
    exit;
}

$lang = $_POST["lang"] ?? "bn";
$lang = $lang === "en" ? "en" : "bn"; // Validate language

$id = !empty($_POST["id"]) ? $_POST["id"] : uniqid();
$title = $_POST["title"] ?? "";
$category_id = $_POST["category_id"] ?? "";
$summary = $_POST["summary"] ?? "";
$content = $_POST["content"] ?? "";
$sectionId = $_POST["sectionId"] ?? "news";
$image = $_POST["image"] ?? "";

// Dynamically calculate read_time
$read_time = calculate_read_time_from_text($content, $lang);

$stmt = $pdo->prepare("SELECT id FROM articles WHERE id = ? AND lang = ?");
$stmt->execute([$id, $lang]);
$exists = $stmt->fetch();

if ($exists) {
    // Update article
    // 'timestamp' column is handled by ON UPDATE CURRENT_TIMESTAMP in the DB
    $stmt = $pdo->prepare(
        "UPDATE articles SET title=?, summary=?, image=?, category_id=?, content=?, section_id=?, read_time=? WHERE id=? AND lang=?",
    );
    $stmt->execute([
        $title,
        $summary,
        $image,
        $category_id,
        $content,
        $sectionId,
        $read_time, // Dynamically calculated read time
        $id,
        $lang,
    ]);
} else {
    // Create new article for this language
    // 'timestamp' column will be set by DB default CURRENT_TIMESTAMP
    $stmt = $pdo->prepare(
        "INSERT INTO articles (id, lang, section_id, title, summary, image, category_id, content, read_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
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
        $read_time, // Dynamically calculated read time
    ]);
}

send_response(["success" => true, "id" => $id]);
?>