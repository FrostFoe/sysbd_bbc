<?php
require_once __DIR__ . "/db.php";

function get_bbc_data()
{
    global $pdo;

    $stmt = $pdo->query("SELECT * FROM sections ORDER BY sort_order ASC");
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
            "SELECT * FROM articles WHERE section_id = ? ORDER BY created_at DESC",
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
                "SELECT user_name as user, text, time FROM comments WHERE article_id = ? ORDER BY created_at DESC",
            );
            $stmt->execute([$article["id"]]);
            $articleData["comments"] = $stmt->fetchAll();

            $stmt = $pdo->prepare(
                "SELECT * FROM culprit_profiles WHERE article_id = ?",
            );
            $stmt->execute([$article["id"]]);
            $profile = $stmt->fetch();

            if ($profile) {
                $profileData = [
                    "name" => $profile["name"],
                    "crime" => $profile["crime"],
                    "status" => $profile["status"],
                    "description" => $profile["description"],
                    "image" => $profile["image"],
                    "timeline" => [],
                    "associates" => [],
                    "evidence" => [], // Not in DB schema yet but in JS, keeping empty for now
                ];

                $stmt = $pdo->prepare(
                    "SELECT year, event FROM culprit_timeline WHERE profile_id = ?",
                );
                $stmt->execute([$profile["id"]]);
                $profileData["timeline"] = $stmt->fetchAll();

                $stmt = $pdo->prepare(
                    "SELECT name, role FROM culprit_associates WHERE profile_id = ?",
                );
                $stmt->execute([$profile["id"]]);
                $profileData["associates"] = $stmt->fetchAll();

                $articleData["culpritProfile"] = $profileData;
            }

            $sectionData["articles"][] = $articleData;
        }

        $data["sections"][] = $sectionData;
    }

    return $data;
}
?>
