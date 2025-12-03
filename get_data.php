<?php
require_once __DIR__ . "/includes/db.php";

function get_data($lang = "bn")
{
    global $pdo;

    // Validate language
    $lang = ($lang === "en") ? "en" : "bn";

    $stmt = $pdo->prepare(
        "SELECT * FROM sections WHERE lang = ? ORDER BY sort_order ASC"
    );
    $stmt->execute([$lang]);
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
            "SELECT * FROM articles WHERE section_id = ? AND lang = ? ORDER BY created_at DESC"
        );
        $stmt->execute([$section["id"], $lang]);
        $articles = $stmt->fetchAll();

        foreach ($articles as $article) {
            // Fetch category name
            $categoryName = null;
            if (!empty($article["category_id"])) {
                $stmt = $pdo->prepare(
                    "SELECT title_bn, title_en FROM categories WHERE id = ?"
                );
                $stmt->execute([$article["category_id"]]);
                $categoryData = $stmt->fetch();
                $categoryName = $categoryData
                    ? ($lang === "en"
                        ? $categoryData["title_en"]
                        : $categoryData["title_bn"])
                    : null;
            }

            $articleData = [
                "id" => $article["id"],
                "title" => $article["title"],
                "summary" => $article["summary"],
                "image" => $article["image"],
                "timestamp" => $article["timestamp"],
                "category" =>
                    $categoryName ?? ($lang === "bn" ? "অন্যান্য" : "Other"),
                "category_id" => $article["category_id"] ?? null,
                "readTime" => $article["read_time"],
                "content" => $article["content"],
                "isVideo" => (bool) $article["is_video"],
                "comments" => [],
            ];

            $stmt = $pdo->prepare(
                "SELECT user_name as user, text, time FROM comments WHERE article_id = ? ORDER BY created_at DESC"
            );
            $stmt->execute([$article["id"]]);
            $articleData["comments"] = $stmt->fetchAll();

            $sectionData["articles"][] = $articleData;
        }

        $data["sections"][] = $sectionData;
    }

    return $data;
}

if (count(debug_backtrace()) == 0) {
    $lang = isset($_GET["lang"]) ? $_GET["lang"] : "bn";
    $data = get_data($lang);

    header("Content-Type: application/json");
    echo json_encode($data);
}
?>
