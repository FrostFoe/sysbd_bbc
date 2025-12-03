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
    
    // Create Category Map for O(1) Lookup
    $categoryMap = [];
    foreach ($categories as $cat) {
        $categoryMap[$cat['id']] = [
            'bn' => $cat['title_bn'],
            'en' => $cat['title_en']
        ];
    }

    // Accept pagination & category filters
    $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
    $limit = isset($_GET['limit']) && is_numeric($_GET['limit']) ? (int) $_GET['limit'] : null;
    $categoryFilter = isset($_GET['category']) ? $_GET['category'] : null;

    // If category filter passed, convert category id to title for the given language
    $categoryTitle = null;
    if ($categoryFilter && isset($categoryMap[$categoryFilter])) {
        $categoryTitle = $lang === 'en' ? $categoryMap[$categoryFilter]['en'] : $categoryMap[$categoryFilter]['bn'];
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

        // Fetch articles with optional pagination
        // Optimization: Removed 'content' from SELECT to reduce payload size
        $offset = $limit ? ($page - 1) * $limit : 0;
        $articleQuery = "SELECT id, section_id, title, summary, image, timestamp, created_at, category_id, read_time, is_video FROM articles WHERE section_id = ? AND lang = ? ORDER BY created_at DESC"; 
        if ($limit) {
            $articleQuery .= " LIMIT ? OFFSET ?";
            $stmt = $pdo->prepare($articleQuery);
            $stmt->execute([$section["id"], $lang, (int) $limit, (int) $offset]);
        } else {
            $stmt = $pdo->prepare($articleQuery);
            $stmt->execute([$section["id"], $lang]);
        }
        $articles = $stmt->fetchAll();

        foreach ($articles as $article) {
            // Optimization: Use Map Lookup instead of SQL Query
            $categoryName = null;
            if (!empty($article["category_id"]) && isset($categoryMap[$article["category_id"]])) {
                $categoryName = $lang === "en" 
                    ? $categoryMap[$article["category_id"]]['en'] 
                    : $categoryMap[$article["category_id"]]['bn'];
            }

            $articleData = [
                "id" => $article["id"],
                "title" => $article["title"],
                "summary" => $article["summary"],
                "image" => $article["image"],
                "timestamp" => $article["timestamp"], 
                "created_at" => $article["created_at"],
                "category" => $categoryName ?? ($lang === "bn" ? "অন্যান্য" : "Other"),
                "category_id" => $article["category_id"] ?? null,
                "read_time" => $article["read_time"],
                // "content" => removed for performance
                "isVideo" => (bool) $article["is_video"],
                // "comments" => removed for performance
            ];
            $sectionData["articles"][] = $articleData;
        }
        $data["sections"][] = $sectionData;
    }

    // Optimization: Removed Comment Fetching Logic entirely from here.
    // Comments should be fetched lazily or on the specific article page.


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