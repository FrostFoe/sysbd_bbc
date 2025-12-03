<?php
require_once __DIR__ . "/includes/db.php";

function get_data($lang = "bn")
{
    global $pdo;

    // Validate language
    $lang = $lang === "en" ? "en" : "bn";

    // Fetch categories
    $stmt = $pdo->query(
        "SELECT id, title_bn, title_en, color FROM categories ORDER BY id ASC",
    );
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Accept pagination & category filters
    $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
    $limit = isset($_GET['limit']) && is_numeric($_GET['limit']) ? (int) $_GET['limit'] : null;
    $categoryFilter = isset($_GET['category']) ? $_GET['category'] : null;

    // If category filter passed, convert category id to title for the given language
    $categoryTitle = null;
    if ($categoryFilter) {
        $catStmt = $pdo->prepare("SELECT title_bn, title_en FROM categories WHERE id = ? LIMIT 1");
        $catStmt->execute([$categoryFilter]);
        $catRow = $catStmt->fetch();
        if ($catRow) {
            $categoryTitle = $lang === 'en' ? $catRow['title_en'] : $catRow['title_bn'];
        }
    }

    // Fetch sections
    if ($categoryTitle) {
        $stmt = $pdo->prepare(
            "SELECT id, title, type, highlight_color, associated_category, style, sort_order FROM sections WHERE lang = ? AND (associated_category = ? OR id = ?) ORDER BY sort_order ASC",
        );
        $stmt->execute([$lang, $categoryTitle, $categoryFilter]);
    } else {
        $stmt = $pdo->prepare(
            "SELECT id, title, type, highlight_color, associated_category, style, sort_order FROM sections WHERE lang = ? ORDER BY sort_order ASC",
        );
        $stmt->execute([$lang]);
    }
    $sections = $stmt->fetchAll();

    $data = ["categories" => $categories, "sections" => []];
    $allSectionArticleIdsMap = []; // To store article IDs grouped by section ID

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

        // Count total articles in section
        $countStmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM articles WHERE section_id = ? AND lang = ?");
        $countStmt->execute([$section["id"], $lang]);
        $countRow = $countStmt->fetch();
        $totalArticles = $countRow ? (int) $countRow['cnt'] : 0;

        // Fetch articles with optional pagination
        $offset = $limit ? ($page - 1) * $limit : 0;
        $articleQuery = "SELECT id, section_id, title, summary, image, timestamp, created_at, category_id, read_time, content, is_video FROM articles WHERE section_id = ? AND lang = ? ORDER BY created_at DESC"; // Fetching timestamp and created_at
        if ($limit) {
            $articleQuery .= " LIMIT ? OFFSET ?";
            $stmt = $pdo->prepare($articleQuery);
            $stmt->execute([$section["id"], $lang, (int) $limit, (int) $offset]);
        } else {
            $stmt = $pdo->prepare($articleQuery);
            $stmt->execute([$section["id"], $lang]);
        }
        $articles = $stmt->fetchAll();

        $currentSectionArticleIds = [];
        foreach ($articles as $article) {
            $currentSectionArticleIds[] = $article['id'];
            // Fetch category name
            $categoryName = null;
            if (!empty($article["category_id"])) {
                $catStmt = $pdo->prepare(
                    "SELECT title_bn, title_en FROM categories WHERE id = ?",
                );
                $catStmt->execute([$article["category_id"]]);
                $categoryData = $catStmt->fetch();
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
                "timestamp" => $article["timestamp"], // Fetched as TIMESTAMP
                "created_at" => $article["created_at"], // Fetch created_at as well for JS formatting
                "category" =>
                    $categoryName ?? ($lang === "bn" ? "অন্যান্য" : "Other"),
                "category_id" => $article["category_id"] ?? null,
                "read_time" => $article["read_time"],
                "content" => $article["content"],
                "isVideo" => (bool) $article["is_video"],
                "comments" => [], // Initialize empty comments array
            ];
            $sectionData["articles"][] = $articleData;
        }
        $allSectionArticleIdsMap[$section["id"]] = $currentSectionArticleIds;
        $data["sections"][] = $sectionData;
    }

    // --- Optimized Comment Fetching (Avoid N+1) ---
    $allCommentsByArticleId = []; // Use array indexed by article_id to group comments
    $allArticleIdsToFetchCommentsFor = [];

    // Collect all article IDs from all sections processed
    foreach ($allSectionArticleIdsMap as $sectionId => $articleIds) {
        $allArticleIdsToFetchCommentsFor = array_merge($allArticleIdsToFetchCommentsFor, $articleIds);
    }
    $allArticleIdsToFetchCommentsFor = array_unique($allArticleIdsToFetchCommentsFor); // Ensure unique IDs

    if (!empty($allArticleIdsToFetchCommentsFor)) {
        // Fetch all comments for all relevant article IDs in one query
        $commentPlaceholders = implode(',', array_fill(0, count($allArticleIdsToFetchCommentsFor), '?'));
        $commentsStmt = $pdo->prepare(
            "SELECT article_id, user_name, text, time, created_at FROM comments WHERE article_id IN ({$commentPlaceholders}) ORDER BY created_at ASC", // Fetch comments sorted by time
        );
        
        $commentParams = $allArticleIdsToFetchCommentsFor; // Parameters for the IN clause
        $commentsStmt->execute($commentParams);
        $allComments = $commentsStmt->fetchAll();

        // Group the fetched comments by article_id
        foreach ($allComments as $comment) {
            $articleId = $comment['article_id'];
            if (!isset($allCommentsByArticleId[$articleId])) {
                $allCommentsByArticleId[$articleId] = [];
            }
            $allCommentsByArticleId[$articleId][] = [
                "user" => $comment["user_name"],
                "text" => $comment["text"],
                "time" => $comment["time"], // Fetched as TIMESTAMP
                "created_at" => $comment["created_at"], // Fetch created_at as well for JS formatting
            ];
        }
    }

    // Assign grouped comments to the respective articles in the data structure
    foreach ($data["sections"] as &$section) {
        foreach ($section["articles"] as &$article) {
            if (isset($allCommentsByArticleId[$article["id"]])) {
                $article["comments"] = $allCommentsByArticleId[$article["id"]];
            }
        }
    }
    unset($section, $article); // Unset references
    // --- End Optimized Comment Fetching ---


    // Extra meta for client
    $data['meta'] = [
        'page' => $page,
        'limit' => $limit,
        'categoryFilter' => $categoryFilter,
    ];

    return $data;
}

if (count(debug_backtrace()) == 0) {
    $lang = isset($_GET["lang"]) ? $_GET["lang"] : "bn";
    $limit = isset($_GET['limit']) && is_numeric($_GET['limit']) ? (int)$_GET['limit'] : null;
    $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
    $categoryFilter = isset($_GET['category']) ? $_GET['category'] : null; // Get category filter from GET params

    $data = get_data($lang);

    // Implement simple file-based caching with ETag
    // Cache key should include language, limit, page, and category filter
    $cacheKey = md5(json_encode(['lang'=>$lang,'limit'=>$limit,'page'=>$page,'categoryFilter'=>$categoryFilter]));
    $cacheFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'bt_cache_' . $cacheKey . '.json';
    $cacheTtl = 30; // seconds

    $etag = null;
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheTtl)) {
        $json = file_get_contents($cacheFile);
        $etag = '"' . md5($json) . '"';
        if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && trim($_SERVER['HTTP_IF_NONE_MATCH']) === $etag) {
            header('ETag: ' . $etag);
            http_response_code(304);
            exit();
        }
        header('ETag: ' . $etag);
        header('Cache-Control: public, max-age=' . $cacheTtl);
        header("Content-Type: application/json");
        echo $json;
        exit();
    }

    $json = json_encode($data);
    // write cache
    @file_put_contents($cacheFile, $json);
    $etag = '"' . md5($json) . '"';
    header('ETag: ' . $etag);
    header('Cache-Control: public, max-age=' . $cacheTtl);
    header("Content-Type: application/json");
    echo $json;
}
?>