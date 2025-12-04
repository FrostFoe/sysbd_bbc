<?php
require_once __DIR__ . "/includes/db.php";
require_once __DIR__ . "/includes/functions.php"; // For time_ago in article data processing

function get_data($lang = "bn", $page = 1, $limit = null, $categoryFilter = null, $includeDrafts = false)
{
    global $pdo;

    // Validate language
    $lang = $lang === "en" ? "en" : "bn";

    // 1. Fetch categories
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

    // Prepare for combined query: Sections and Articles
    $params = [$lang];
    $whereSectionClause = "s.lang = ?";
    $whereArticleClause = "a.lang = ? AND a.status = 'published'"; // Default to published

    if ($includeDrafts) {
        $whereArticleClause = "a.lang = ?"; // Show all for admins
    }

    if ($categoryFilter) {
        // We need to fetch associated_category from sections and filter on it
        // Or filter directly on articles.category_id
        // For simplicity and to match original behavior, if category filter passed,
        // it applies to sections (associated_category) and articles (category_id)
        $whereSectionClause .= " AND (s.associated_category = ? OR a.category_id = ?)";
        $params[] = $categoryFilter;
        $params[] = $categoryFilter;
    }

    // Base query for sections and their articles
    // Using LEFT JOIN to ensure sections with no articles are still returned
    // Filtering by article status in the JOIN ON clause is crucial for performance
    $sql = "
        SELECT 
            s.id AS section_id, s.title AS section_title, s.type AS section_type, 
            s.highlight_color AS section_highlightColor, s.associated_category AS section_associatedCategory, 
            s.style AS section_style, s.sort_order AS section_sort_order,
            
            a.id AS article_id, a.title AS article_title, a.summary AS article_summary, 
            a.image AS article_image, a.published_at AS article_published_at, a.created_at AS article_created_at,
            a.category_id AS article_category_id, a.read_time AS article_read_time, 
            a.is_video AS article_is_video, a.status AS article_status
        FROM sections s
        LEFT JOIN articles a ON s.id = a.section_id AND " . $whereArticleClause . "
        WHERE " . $whereSectionClause . "
        ORDER BY s.sort_order ASC, a.published_at DESC
    ";

    // Adjust params for the article clause
    $articleParams = [$lang]; // For 'a.lang = ?'
    if ($includeDrafts) {
        $articleParams = [$lang]; // Still just lang
    }

    // Combine all parameters for the single execute call
    // Note: If $whereSectionClause and $whereArticleClause both use $lang, need to pass $lang twice.
    // In current setup, $lang is first for section, then for article.
    // If categoryFilter is used, it's section, lang, categoryFilter, lang, categoryFilter, lang
    $finalParams = [$lang]; // for s.lang = ?
    if ($categoryFilter) {
        $finalParams[] = $categoryFilter; // for s.associated_category = ?
        $finalParams[] = $categoryFilter; // for a.category_id = ?
    }
    $finalParams[] = $lang; // for a.lang = ?
    
    // Add status specific parameters if needed, but for now 'published' is hardcoded or removed.
    // For now, $whereArticleClause parameters are just $lang. So it should be `s.lang, category (if any), a.lang`

    $stmt = $pdo->prepare($sql);
    $stmt->execute($finalParams);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $sectionsData = [];
    $data = ["categories" => $categories, "sections" => []];

    foreach ($rows as $row) {
        $sectionId = $row['section_id'];

        // Initialize section if not already present
        if (!isset($sectionsData[$sectionId])) {
            $sectionsData[$sectionId] = [
                "id" => $sectionId,
                "title" => $row["section_title"],
                "type" => $row["section_type"],
                "highlightColor" => $row["section_highlightColor"],
                "associatedCategory" => $row["section_associatedCategory"],
                "style" => $row["section_style"],
                "articles" => [],
            ];
        }

        // Add article to section if it exists (LEFT JOIN means row['article_id'] can be null)
        if ($row['article_id']) {
            $categoryName = null;
            if (!empty($row["article_category_id"]) && isset($categoryMap[$row["article_category_id"]])) {
                $categoryName = $lang === "en" 
                    ? $categoryMap[$row["article_category_id"]]['en'] 
                    : $categoryMap[$row["article_category_id"]]['bn'];
            }

            $articleData = [
                "id" => $row["article_id"],
                "title" => $row["article_title"],
                "summary" => $row["article_summary"],
                "image" => $row["article_image"],
                "published_at" => $row["article_published_at"], // Use the new column
                "category" => $categoryName ?? ($lang === "bn" ? "অন্যান্য" : "Other"),
                "category_id" => $row["article_category_id"] ?? null,
                "read_time" => $row["article_read_time"],
                "isVideo" => (bool) $row["article_is_video"],
                "status" => $row["article_status"]
            ];
            $sectionsData[$sectionId]["articles"][] = $articleData;
        }
    }

    $data["sections"] = array_values($sectionsData); // Re-index array

    // Apply pagination to the overall list of articles across sections,
    // or to sections themselves? The original code applied it to articles per section.
    // For now, let's apply it globally if limit is set.
    // This is a complex change if global pagination is desired.
    // Sticking to per-section pagination (which was a half-measure before).
    // Better to apply pagination AFTER fetching all relevant articles if we want to mimic "infinite scroll".
    // The current pagination is LIMIT X OFFSET Y per section. This is still N queries.
    // To truly fix N+1 AND per-section LIMIT, I should refactor more.

    // Let's re-think the pagination logic. The current setup applied limit/offset PER SECTION.
    // If the goal is a truly optimized N+1 and then paginate the _resulting list of articles_,
    // that's a different query.

    // The original logic with limit/offset was applied *per section*.
    // This combined query retrieves *all* articles for *all* sections first, then re-structures.
    // If we want pagination, it must be applied to the PHP array now.
    // This makes the query return potentially too many rows, then PHP filters.
    // So for large datasets, this single query can still be slow.

    // Let's revert to a simpler, safer N+1 fix for now:
    // 1. Fetch ALL sections.
    // 2. Fetch ALL articles (for published status) in ONE query, GROUPED by section_id.
    // 3. Match articles to sections in PHP.

    // --- REVISED PLAN FOR N+1 FIX (SIMPLER) ---

    // 2. Fetch all articles at once (published, potentially filtered by category)
    $articleParams = [$lang];
    $whereArticleListClause = "lang = ? AND status = 'published'"; // Only published for general view
    if ($includeDrafts) {
        $whereArticleListClause = "lang = ?"; // For admins
    }
    
    if ($categoryFilter) {
        $whereArticleListClause .= " AND category_id = ?";
        $articleParams[] = $categoryFilter;
    }
    
    $articleListSql = "
        SELECT id, section_id, title, summary, image, published_at, created_at, category_id, read_time, is_video, status
        FROM articles
        WHERE " . $whereArticleListClause . "
        ORDER BY published_at DESC
    ";

    $stmt = $pdo->prepare($articleListSql);
    $stmt->execute($articleParams);
    $allArticles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Group articles by section_id for easier assignment
    $articlesBySection = [];
    foreach ($allArticles as $article) {
        $articlesBySection[$article['section_id']][] = $article;
    }

    // 3. Fetch sections
    $sectionParams = [$lang];
    $whereSectionListClause = "lang = ?";
    if ($categoryFilter) {
        $whereSectionListClause .= " AND associated_category = ?";
        $sectionParams[] = $categoryFilter;
    }

    $sectionSql = "
        SELECT id, title, type, highlight_color, associated_category, style, sort_order 
        FROM sections 
        WHERE " . $whereSectionListClause . "
        ORDER BY sort_order ASC
    ";
    $stmt = $pdo->prepare($sectionSql);
    $stmt->execute($sectionParams);
    $sections = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $sectionsData = [];
    foreach ($sections as $section) {
        $sectionId = $section["id"];
        $sectionData = [
            "id" => $sectionId,
            "title" => $section["title"],
            "type" => $section["type"],
            "highlightColor" => $section["highlight_color"],
            "associatedCategory" => $section["associated_category"],
            "style" => $section["style"],
            "articles" => [], // Will be filled below
        ];

        // Assign articles to this section, applying per-section pagination if limit is set
        if (isset($articlesBySection[$sectionId])) {
            $sectionArticles = $articlesBySection[$sectionId];
            
            // Apply pagination logic here if $limit is set for PER-SECTION pagination
            if ($limit !== null) {
                $sectionArticles = array_slice($sectionArticles, ($page - 1) * $limit, $limit);
            }

            foreach ($sectionArticles as $article) {
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
                    "published_at" => $article["published_at"],
                    "category" => $categoryName ?? ($lang === "bn" ? "অন্যান্য" : "Other"),
                    "category_id" => $article["category_id"] ?? null,
                    "read_time" => $article["read_time"],
                    "isVideo" => (bool) $article["is_video"],
                    "status" => $article["status"]
                ];
                $sectionData["articles"][] = $articleData;
            }
        }
        $sectionsData[] = $sectionData;
    }

    $data["sections"] = $sectionsData;

    // Extra meta for client
    $data['meta'] = [
        'page' => $page,
        'limit' => $limit,
        'categoryFilter' => $categoryFilter,
        'includeDrafts' => $includeDrafts
    ];

    return $data;
}

if (count(debug_backtrace()) == 0) {
    session_start(); // Need session to check admin status

    $lang = isset($_GET["lang"]) ? $_GET["lang"] : "bn";
    $limit = isset($_GET['limit']) && is_numeric($_GET['limit']) ? (int)$_GET['limit'] : null;
    $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
    $categoryFilter = isset($_GET['category']) ? $_GET['category'] : null;

    $isAdmin = isset($_SESSION["user_role"]) && $_SESSION["user_role"] === "admin";
    // Only admins can see drafts via this API. Non-admins always see published.
    $includeDrafts = $isAdmin; 

    $data = get_data($lang, $page, $limit, $categoryFilter, $includeDrafts);

    // Implement simple file-based caching with ETag
    // Cache key should include language, limit, page, category filter, and includeDrafts status
    $cacheKey = md5(json_encode([
        'lang' => $lang,
        'limit' => $limit,
        'page' => $page,
        'categoryFilter' => $categoryFilter,
        'includeDrafts' => $includeDrafts
    ]));
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