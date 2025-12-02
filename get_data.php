<?php
require_once __DIR__ . "/includes/db.php";

function get_data($lang = "bn")
{
    global $pdo;

    $suffix = $lang === "en" ? "_en" : "";

    $stmt = $pdo->query("SELECT * FROM sections{$suffix} ORDER BY sort_order ASC");
    $sections = $stmt->fetchAll();

    $data = ["sections" => []];

    foreach ($sections as $section) {
        $sectionData = [
            "id" => $section["id"],
            "title" => $section["title"],
            "type" => $section["type"],
            "highlightColor" => $section["highlight_color"],
            "associatedCategory" => $section["associated_category"],
            "style" => $section["style"],
            "articles" => [],
        ];

        $stmt = $pdo->prepare(
            "SELECT * FROM articles{$suffix} WHERE section_id = ? ORDER BY created_at DESC",
        );
        $stmt->execute([$section["id"]]);
        $articles = $stmt->fetchAll();

        foreach ($articles as $article) {
            $articleData = [
                "id" => $article["id"],
                "title" => $article["title"],
                "summary" => $article["summary"],
                "image" => $article["image"],
                "timestamp" => $article["timestamp"],
                "category" => $article["category"],
                "readTime" => $article["read_time"],
                "content" => $article["content"],
                "isVideo" => (bool) $article["is_video"],
                "comments" => [],
            ];

            $stmt = $pdo->prepare(
                "SELECT user_name as user, text, time FROM comments{$suffix} WHERE article_id = ? ORDER BY created_at DESC",
            );
            $stmt->execute([$article["id"]]);
            $articleData["comments"] = $stmt->fetchAll();

            $sectionData["articles"][] = $articleData;
        }

        $data["sections"][] = $sectionData;
    }

    return $data;
}

$lang = isset($_GET["lang"]) ? $_GET["lang"] : "bn";
$data = get_data($lang);

header("Content-Type: application/json");
echo json_encode($data);
?>