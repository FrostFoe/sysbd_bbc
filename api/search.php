<?php
require_once __DIR__ . "/../includes/db.php";
require_once __DIR__ . "/api_header.php";

$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$lang = isset($_GET['lang']) ? $_GET['lang'] : 'bn';
$lang = ($lang === 'en') ? 'en' : 'bn';

if (mb_strlen($query) < 2) {
    echo json_encode([]);
    exit;
}

// Search in title and summary
$sql = "SELECT id, title, summary, image, category_id, created_at, read_time, is_video 
        FROM articles 
        WHERE lang = ? AND (title LIKE ? OR summary LIKE ?) 
        ORDER BY created_at DESC 
        LIMIT 20";

$stmt = $pdo->prepare($sql);
$searchTerm = '%' . $query . '%';
$stmt->execute([$lang, $searchTerm, $searchTerm]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch category names for results
foreach ($results as &$article) {
    $catName = null;
    if ($article['category_id']) {
        $catStmt = $pdo->prepare("SELECT title_bn, title_en FROM categories WHERE id = ?");
        $catStmt->execute([$article['category_id']]);
        $catRow = $catStmt->fetch();
        if ($catRow) {
            $catName = ($lang === 'en') ? $catRow['title_en'] : $catRow['title_bn'];
        }
    }
    $article['category'] = $catName ?? ($lang === 'bn' ? 'অন্যান্য' : 'Other');
    
    // Add necessary fields for frontend rendering
    $article['timestamp'] = $article['created_at']; // Ensure compatibility
    $article['isVideo'] = (bool)$article['is_video'];
}

echo json_encode($results);
?>