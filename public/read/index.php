<?php
session_start();
require_once "../../src/config/db.php";
require_once "../../src/lib/security.php";
require_once "../../src/lib/functions.php";

$user = isset($_SESSION["user_email"]) ? $_SESSION["user_email"] : null;
$isAdmin = isset($_SESSION["user_role"]) && $_SESSION["user_role"] === "admin";
$lang = isset($_GET["lang"]) && $_GET["lang"] === "en" ? "en" : "bn";
$articleId = isset($_GET["id"]) ? $_GET["id"] : null;

if (!$articleId) {
    header("Location: ../index.php");
    exit();
}

// Fetch article directly from DB (Unified Schema)
$stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
$stmt->execute([$articleId]);
$articleRaw = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$articleRaw) {
    header("Location: ../index.php");
    exit();
}

// Map localized fields to generic keys
$article = $articleRaw;
$article['title'] = $lang === 'en' ? $articleRaw['title_en'] : $articleRaw['title_bn'];
$article['summary'] = $lang === 'en' ? $articleRaw['summary_en'] : $articleRaw['summary_bn'];
$article['content'] = $lang === 'en' ? $articleRaw['content_en'] : $articleRaw['content_bn'];
$article['readTime'] = $lang === 'en' ? ($articleRaw['read_time_en'] ?? '') : ($articleRaw['read_time_bn'] ?? '');

// Fallback for title if empty (optional, but good UX)
if (empty($article['title'])) {
    $article['title'] = $lang === 'en' ? $articleRaw['title_bn'] : $articleRaw['title_en'];
    $article['content'] = "<em>Content not available in this language.</em><br>" . ($lang === 'en' ? $articleRaw['content_bn'] : $articleRaw['content_en']);
}

// Check status
$status = $article['status'] ?? 'published'; 
if ($status !== 'published' && !$isAdmin) {
    header("Location: ../index.php");
    exit();
}

// Sanitize Content
$article['content'] = sanitize_html($article['content']);

// Fetch Category Name
$categoryName = "News";
if ($article['category_id']) {
    $catStmt = $pdo->prepare("SELECT title_bn, title_en FROM categories WHERE id = ?");
    $catStmt->execute([$article['category_id']]);
    $cat = $catStmt->fetch(PDO::FETCH_ASSOC);
    if ($cat) {
        $categoryName = $lang === 'en' ? $cat['title_en'] : $cat['title_bn'];
    }
}
$article['category'] = $categoryName;

// Fetch Comments (with votes and replies)
// Get pinned comments first, then regular comments
$commentStmt = $pdo->prepare("
    SELECT c.id, c.text, c.created_at, c.user_name, c.user_id, c.is_pinned, c.pin_order, u.email 
    FROM comments c 
    LEFT JOIN users u ON c.user_id = u.id 
    WHERE c.article_id = ? AND c.parent_comment_id IS NULL
    ORDER BY c.is_pinned DESC, c.pin_order ASC, c.created_at DESC
");
$commentStmt->execute([$articleId]);
$rawComments = $commentStmt->fetchAll(PDO::FETCH_ASSOC);

$processedComments = [];
foreach ($rawComments as $c) {
    // Determine display name
    $displayName = $c['user_name'];
    if (!empty($c['email'])) {
        $parts = explode('@', $c['email']);
        $displayName = $parts[0]; 
    }
    
    // Get votes for this comment
    $voteStmt = $pdo->prepare(
        "SELECT 
            SUM(CASE WHEN vote_type = 'upvote' THEN 1 ELSE 0 END) as upvotes,
            SUM(CASE WHEN vote_type = 'downvote' THEN 1 ELSE 0 END) as downvotes
        FROM comment_votes WHERE comment_id = ?"
    );
    $voteStmt->execute([$c['id']]);
    $votes = $voteStmt->fetch(PDO::FETCH_ASSOC);
    $upvotes = (int)($votes['upvotes'] ?? 0);
    $downvotes = (int)($votes['downvotes'] ?? 0);
    
    // Get replies for this comment
    $replyStmt = $pdo->prepare("
        SELECT c.id, c.text, c.created_at, c.user_name, c.user_id, u.email 
        FROM comments c 
        LEFT JOIN users u ON c.user_id = u.id 
        WHERE c.parent_comment_id = ? 
        ORDER BY c.created_at ASC
    ");
    $replyStmt->execute([$c['id']]);
    $rawReplies = $replyStmt->fetchAll(PDO::FETCH_ASSOC);
    
    $replies = [];
    foreach ($rawReplies as $r) {
        $replyDisplayName = $r['user_name'];
        if (!empty($r['email'])) {
            $parts = explode('@', $r['email']);
            $replyDisplayName = $parts[0];
        }
        
        $replies[] = [
            'id' => $r['id'],
            'user' => $replyDisplayName,
            'text' => htmlspecialchars($r['text']),
            'time' => time_ago($r['created_at'], $lang),
            'isAdmin' => !empty($r['email']) && strpos($r['email'], 'admin') !== false
        ];
    }
    
    $processedComments[] = [
        'id' => $c['id'],
        'user' => $displayName,
        'text' => htmlspecialchars($c['text']),
        'time' => time_ago($c['created_at'], $lang),
        'upvotes' => $upvotes,
        'downvotes' => $downvotes,
        'isPinned' => (bool)$c['is_pinned'],
        'replies' => $replies,
        'userId' => $c['user_id']
    ];
}
$article['comments'] = $processedComments;

// Decode leaked documents if any
$leakedDocuments = [];
if (!empty($article['leaked_documents'])) {
    $leakedDocuments = json_decode($article['leaked_documents'], true);
}
?>
<!doctype html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo htmlspecialchars($article["title"]); ?> | BreachTimes</title>
    
    <!-- Open Graph / Social Sharing Meta Tags -->
    <meta property="og:type" content="article" />
    <meta property="og:title" content="<?php echo htmlspecialchars($article["title"]); ?>" />
    <meta property="og:description" content="<?php echo htmlspecialchars($article["summary"] ?? ""); ?>" />
    <meta property="og:image" content="<?php echo htmlspecialchars($article["image"] ?? ""); ?>" />
    <meta property="og:url" content="<?php echo "https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]; ?>" />
    <meta property="og:site_name" content="BreachTimes" />
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="<?php echo htmlspecialchars($article["title"]); ?>" />
    <meta name="twitter:description" content="<?php echo htmlspecialchars($article["summary"] ?? ""); ?>" />
    <meta name="twitter:image" content="<?php echo htmlspecialchars($article["image"] ?? ""); ?>" />
    
    <!-- Article Meta Tags -->
    <meta name="description" content="<?php echo htmlspecialchars($article["summary"] ?? ""); ?>" />
    <meta name="author" content="BreachTimes" />
    <meta name="publish_date" content="<?php echo htmlspecialchars($article["published_at"] ?? date("Y-m-d")); ?>" />
    <meta name="category" content="<?php echo htmlspecialchars($article["category"] ?? "News"); ?>" />
    
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&display=swap');
    </style>
    
    <link href="../assets/css/styles.css" rel="stylesheet" />
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet" />
    <script src="../assets/js/lucide.js"></script>
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
</head>
<body class="bg-page text-page-text font-sans transition-colors duration-500 antialiased selection:bg-bbcRed selection:text-white">
    <div id="toast-container" class="fixed bottom-8 left-1/2 transform -translate-x-1/2 z-[110] pointer-events-none w-full max-w-sm flex flex-col items-center gap-2"></div>
    <div id="progress-bar" class="fixed top-0 left-0 h-1 bg-bbcRed z-[100] shadow-[0_0_10px_#B80000]" style="width: 0%" aria-hidden="true"></div>

    <header role="banner" class="border-b border-border-color sticky bg-white/90 dark:bg-[#121212]/90 backdrop-blur-md z-50 transition-colors duration-300 shadow-sm">
        <div class="container mx-auto px-4 lg:px-8 max-w-[1380px]">
            <div class="h-[70px] flex items-center justify-between">
                <div class="flex items-center gap-6">
                    <a href="../index.php" class="p-2.5 hover:bg-muted-bg rounded-full text-gray-700 dark:text-gray-200 transition-colors">
                        <i data-lucide="arrow-left" class="w-6 h-6"></i>
                    </a>
                    <a href="../index.php" class="block text-black dark:text-white transition-transform hover:scale-[1.02] active:scale-95 duration-300">
                        <div class="flex items-center select-none gap-2 group">
                            <span class="bg-bbcRed text-white px-2.5 py-0.5 font-bold text-xl rounded shadow-md group-hover:bg-[#d40000] transition-colors duration-300">B</span>
                            <span class="font-bold text-2xl tracking-tight leading-none text-gray-900 dark:text-white group-hover:text-gray-700 dark:group-hover:text-gray-300 transition-colors">
                                <span class="text-bbcRed">Breach</span>Times
                            </span>
                        </div>
                    </a>
                </div>
                <div class="flex items-center gap-2 md:gap-4">
                    <button onclick="toggleTheme()" class="p-2.5 rounded-full hover:bg-muted-bg text-gray-600 dark:text-yellow-400 transition-all active:scale-90">
                        <i data-lucide="sun" class="w-5 h-5"></i>
                    </button>
                    <button onclick="toggleLanguage()" class="p-2.5 rounded-full hover:bg-muted-bg text-gray-600 dark:text-green-400 transition-all active:scale-90">
                        <span class="text-sm font-bold"><?php echo $lang === "bn" ? "EN" : "BN"; ?></span>
                    </button>
                    <?php if ($user): ?>
                        <button onclick="handleLogout()" class="text-sm font-bold px-4 py-2 hover:bg-red-50 dark:hover:bg-red-900/20 text-bbcRed rounded-full transition-all flex items-center gap-2">
                            <div class="w-4 h-4 bg-bbcRed rounded-full text-white flex items-center justify-center text-[10px]"><?php echo strtoupper($user[0]); ?></div> সাইন আউট
                        </button>
                    <?php else: ?>
                        <a href="../login/" class="text-sm font-bold px-5 py-2.5 bg-bbcDark dark:bg-white text-white dark:text-black rounded-full hover:shadow-lg hover:-translate-y-0.5 transition-all">সাইন ইন</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <main role="main" class="bg-page min-h-screen font-sans animate-fade-in-up pb-12">
        <div class="max-w-[1280px] mx-auto px-4 py-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
                <div class="lg:col-span-8">
                    <article class="bg-card p-6 md:p-10 rounded-2xl shadow-soft border border-border-color">
                        <!-- Article Header -->
                        <div class="mb-6">
                            <span class="bg-bbcRed text-white text-xs font-bold px-3 py-1 rounded-full mb-3 inline-block"><?php echo htmlspecialchars($article["category"] ?? "News"); ?></span>
                            <h1 class="text-3xl md:text-5xl font-bold leading-tight mb-4 text-card-text"><?php echo htmlspecialchars($article["title"]); ?></h1>
                            
                            <div class="flex flex-wrap items-center gap-4 text-sm text-muted-text font-medium">
                                <span class="flex items-center gap-1.5"><i data-lucide="clock" class="w-4 h-4"></i> <?php echo time_ago($article["published_at"] ?? "now", $lang); ?></span>
                                <?php if (isset($article["readTime"])): ?>
                                    <span class="flex items-center gap-1.5"><i data-lucide="file-text" class="w-4 h-4"></i> <?php echo htmlspecialchars($article["readTime"]); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Featured Image -->
                        <div class="mb-10 relative aspect-video bg-muted-bg rounded-2xl overflow-hidden shadow-lg">
                            <img src="<?php echo htmlspecialchars($article["image"] ?? ""); ?>" onerror="this.src='https://placehold.co/1200x675/1a1a1a/FFF?text=BreachTimes'" class="w-full h-full object-cover">
                        </div>

                        <!-- Article Controls -->
                        <div class="flex items-center justify-between border-y border-border-color py-4 mb-8">
                            <div class="flex items-center gap-1 bg-muted-bg rounded-lg p-1">
                                <button onclick="setFontSize('sm')" class="w-8 h-8 flex items-center justify-center hover:bg-card rounded transition-colors text-xs font-bold text-card-text">A</button>
                                <button onclick="setFontSize('md')" class="w-8 h-8 flex items-center justify-center hover:bg-card rounded transition-colors text-sm font-bold text-card-text">A</button>
                                <button onclick="setFontSize('lg')" class="w-8 h-8 flex items-center justify-center hover:bg-card rounded transition-colors text-lg font-bold text-card-text">A</button>
                            </div>
                            <div class="flex gap-3">
                                <button aria-label="Share article" onclick="handleShare()" class="flex items-center gap-2 px-4 py-2 rounded-full bg-muted-bg hover:bg-bbcRed hover:text-white transition-all text-sm font-bold text-card-text">
                                    <i data-lucide="share-2" class="w-4 h-4"></i> শেয়ার
                                </button>
                                <button aria-label="Toggle bookmark" onclick="toggleBookmark('<?php echo $article["id"]; ?>')" class="p-2.5 rounded-full bg-muted-bg hover:bg-bbcRed hover:text-white text-black dark:text-white transition-all shadow-sm flex items-center justify-center group">
                                    <i data-lucide="bookmark" class="w-5 h-5"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Article Content -->
                        <div class="prose max-w-none font-size-md space-y-8 text-card-text transition-all duration-300">
                            <?php echo $article["content"]; // Sanitized above ?>
                        </div>
                    </article>

                </div>

                <!-- Sidebar -->
                <div class="lg:col-span-4 relative">
                    <div class="w-full lg:fixed lg:top-28 lg:right-4 lg:w-[calc(33.33vw-4rem)] lg:max-w-[400px] lg:max-h-[calc(100vh-8rem)] lg:overflow-y-auto no-scrollbar space-y-6 z-40 lg:pr-2">
                        
                        <!-- Table of Contents -->
                        <div class="bg-card p-6 rounded-2xl shadow-soft border border-border-color">
                            <h4 class="text-lg font-bold mb-4 text-card-text border-b border-border-color pb-2">
                                <?php echo $lang === 'bn' ? 'সূচিপত্র' : 'Table of Contents'; ?>
                            </h4>
                            <nav id="toc-container" class="text-sm space-y-2 text-muted-text">
                                <!-- JS will populate this -->
                            </nav>
                        </div>

                        <!-- Leaked Documents -->
                        <?php if (!empty($leakedDocuments)): ?>
                        <div class="bg-card p-6 rounded-2xl shadow-soft border border-border-color">
                            <h4 class="text-lg font-bold mb-4 text-card-text border-b border-border-color pb-2 flex items-center gap-2">
                                <i data-lucide="file-warning" class="w-5 h-5 text-bbcRed"></i>
                                <?php echo $lang === 'bn' ? 'ফাঁস হওয়া নথি' : 'Leaked Documents'; ?>
                            </h4>
                            <ul class="space-y-3">
                                <?php foreach ($leakedDocuments as $doc): ?>
                                <li class="group flex items-center gap-3 p-2 rounded-lg hover:bg-muted-bg transition-colors cursor-pointer">
                                    <div class="w-10 h-10 rounded-lg bg-red-50 dark:bg-red-900/20 flex items-center justify-center flex-shrink-0 text-bbcRed font-bold text-xs border border-bbcRed/20">
                                        <?php echo htmlspecialchars($doc['type'] ?? 'DOC'); ?>
                                    </div>
                                    <div class="flex-grow min-w-0">
                                        <p class="text-sm font-bold text-card-text truncate group-hover:text-bbcRed transition-colors">
                                            <?php echo htmlspecialchars($doc['title']); ?>
                                        </p>
                                        <span class="text-[10px] text-muted-text uppercase tracking-wider">Download</span>
                                    </div>
                                    <i data-lucide="download" class="w-4 h-4 text-muted-text group-hover:text-bbcRed transition-colors"></i>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endif; ?>

                        <!-- Quick Info Card -->
                        <div class="bg-card p-6 rounded-2xl shadow-soft border border-border-color">
                            <h4 class="text-lg font-bold mb-4 text-card-text">
                                <?php echo $lang === 'bn' ? 'নিবন্ধ তথ্য' : 'Article Info'; ?>
                            </h4>
                            <div class="space-y-3 text-sm">
                                <div class="flex justify-between items-center pb-3 border-b border-border-color">
                                    <span class="text-muted-text"><?php echo $lang === 'bn' ? 'বিভাগ:' : 'Category:'; ?></span>
                                    <span class="font-bold text-card-text"><?php echo htmlspecialchars($article["category"] ?? "N/A"); ?></span>
                                </div>
                                <div class="flex justify-between items-center pb-3 border-b border-border-color">
                                    <span class="text-muted-text"><?php echo $lang === 'bn' ? 'প্রকাশনা:' : 'Published:'; ?></span>
                                    <span class="font-bold text-card-text"><?php echo time_ago($article["published_at"] ?? "now", $lang); ?></span>
                                </div>
                                <?php if (isset($article["readTime"])): ?>
                                    <div class="flex justify-between items-center">
                                        <span class="text-muted-text"><?php echo $lang === 'bn' ? 'পড়ার সময়:' : 'Read Time:'; ?></span>
                                        <span class="font-bold text-card-text"><?php echo htmlspecialchars($article["readTime"]); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Comments Section -->
                <div class="lg:col-span-8 bg-card p-6 md:p-10 rounded-2xl shadow-soft border border-border-color">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-2xl font-bold text-card-text flex items-center gap-2">
                            <i data-lucide="message-circle" class="w-6 h-6 text-bbcRed"></i> মন্তব্য
                        </h3>
                        <div class="flex items-center gap-2">
                            <label class="text-xs font-bold text-muted-text">সাজান:</label>
                            <select id="sort-comments" onchange="sortComments()" class="px-3 py-1.5 rounded-lg bg-muted-bg border border-border-color text-card-text text-xs font-bold hover:bg-border-color transition-colors">
                                <option value="newest">সর্বশেষ</option>
                                <option value="oldest">সবচেয়ে পুরানো</option>
                                <option value="helpful">সবচেয়ে সহায়ক</option>
                                <option value="discussed">সবচেয়ে আলোচিত</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-8">
                        <!-- Quill Editor -->
                        <div id="quill-editor" class="bg-card rounded-xl border border-border-color overflow-hidden" style="height: 300px;"></div>
                        
                        <!-- Character Counter -->
                        <div class="flex justify-between items-center mt-3 gap-4">
                            <div id="char-counter" class="text-xs text-muted-text">0/5000</div>
                            <div id="error-message" class="text-xs text-red-500 font-bold hidden"></div>
                            <button onclick="postComment('<?php echo $article["id"]; ?>')" class="bg-bbcDark dark:bg-white text-white dark:text-black px-6 py-2.5 rounded-full font-bold hover:shadow-lg hover:-translate-y-0.5 transition-all text-sm" id="post-btn">মন্তব্য প্রকাশ করুন</button>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <?php if (count($article['comments']) > 0): ?>
                            <?php foreach ($article['comments'] as $comment): ?>
                                <div class="bg-muted-bg p-4 rounded-xl border border-border-color" id="comment-<?php echo $comment['id']; ?>">
                                    <!-- Pinned Badge -->
                                    <?php if ($comment['isPinned']): ?>
                                        <div class="flex items-center gap-2 mb-3 text-xs font-bold text-bbcRed bg-red-50 dark:bg-red-900/20 px-3 py-1.5 rounded-lg w-fit">
                                            <i data-lucide="pin" class="w-3 h-3"></i> <?php echo $lang === 'bn' ? 'অ্যাডমিন মতামত' : 'Admin Comment'; ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <!-- Main Comment -->
                                    <div class="flex items-start gap-3 mb-2">
                                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-bbcRed to-orange-500 flex items-center justify-center font-bold text-white text-sm shadow-md flex-shrink-0"><?php echo strtoupper(substr($comment["user"], 0, 1)); ?></div>
                                        <div class="flex-grow min-w-0">
                                            <span onclick="showUserProfile('<?php echo htmlspecialchars($comment['user']); ?>')" class="font-bold text-sm text-card-text block hover:text-bbcRed cursor-pointer transition-colors"><?php echo htmlspecialchars($comment["user"]); ?></span>
                                            <span class="text-xs text-muted-text"><?php echo $comment["time"]; ?></span>
                                        </div>
                                        <!-- Admin Pin Button -->
                                        <?php if ($isAdmin): ?>
                                            <button onclick="togglePin(<?php echo $comment['id']; ?>, <?php echo $comment['isPinned'] ? 'true' : 'false'; ?>)" class="p-2 rounded hover:bg-yellow-100 dark:hover:bg-yellow-900/20 transition-colors text-yellow-600 dark:text-yellow-400" title="<?php echo $comment['isPinned'] ? 'Unpin' : 'Pin'; ?>">
                                                <i data-lucide="pin" class="w-4 h-4"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Comment Text -->
                                    <p class="text-sm text-card-text ml-12 leading-relaxed bg-card p-3 rounded-lg rounded-tl-none border border-border-color mb-3"><?php echo $comment["text"]; ?></p>
                                    
                                    <!-- Vote Section & Reply Button -->
                                    <div class="ml-12 flex items-center gap-3 text-xs">
                                        <div class="flex items-center gap-1 bg-card px-2 py-1 rounded-lg border border-border-color">
                                            <button onclick="voteComment(<?php echo $comment['id']; ?>, 'upvote')" class="p-1 hover:text-green-500 transition-colors text-muted-text vote-btn-up" data-comment-id="<?php echo $comment['id']; ?>" title="Upvote">
                                                <i data-lucide="thumbs-up" class="w-3 h-3"></i>
                                            </button>
                                            <span id="vote-count-<?php echo $comment['id']; ?>" class="text-xs font-bold text-muted-text min-w-[20px] text-center"><?php echo $comment['upvotes'] - $comment['downvotes']; ?></span>
                                            <button onclick="voteComment(<?php echo $comment['id']; ?>, 'downvote')" class="p-1 hover:text-red-500 transition-colors text-muted-text vote-btn-down" data-comment-id="<?php echo $comment['id']; ?>" title="Downvote">
                                                <i data-lucide="thumbs-down" class="w-3 h-3"></i>
                                            </button>
                                        </div>
                                        <?php if ($isAdmin): ?>
                                            <button onclick="toggleReplyForm(<?php echo $comment['id']; ?>)" class="px-3 py-1 hover:bg-blue-100 dark:hover:bg-blue-900/20 text-blue-600 dark:text-blue-400 rounded transition-colors font-bold">
                                                <?php echo $lang === 'bn' ? 'উত্তর' : 'Reply'; ?>
                                            </button>
                                        <?php endif; ?>
                                        <?php if ($isAdmin): ?>
                                            <button onclick="deleteComment(<?php echo $comment['id']; ?>)" class="text-red-500 hover:text-red-700 p-1 rounded hover:bg-red-50 dark:hover:bg-red-900/20">
                                                <i data-lucide="trash-2" class="w-3 h-3"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Replies Section -->
                                    <?php if (!empty($comment['replies'])): ?>
                                        <div class="ml-12 mt-4 space-y-3 border-l-2 border-border-color pl-4">
                                            <?php foreach ($comment['replies'] as $reply): ?>
                                                <div class="bg-card p-3 rounded-lg">
                                                    <div class="flex items-start gap-2 mb-1">
                                                        <div class="w-7 h-7 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center font-bold text-white text-xs shadow-md flex-shrink-0"><?php echo strtoupper(substr($reply["user"], 0, 1)); ?></div>
                                                        <div class="flex-grow min-w-0">
                                                            <span class="font-bold text-xs text-card-text block"><?php echo htmlspecialchars($reply["user"]); ?></span>
                                                            <span class="text-xs text-muted-text"><?php echo $reply["time"]; ?></span>
                                                        </div>
                                                    </div>
                                                    <p class="text-xs text-card-text ml-9 leading-relaxed"><?php echo $reply["text"]; ?></p>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <!-- Reply Form (Hidden by default) -->
                                    <?php if ($isAdmin): ?>
                                        <div id="reply-form-<?php echo $comment['id']; ?>" class="ml-12 mt-4 hidden">
                                            <textarea id="reply-input-<?php echo $comment['id']; ?>" placeholder="<?php echo $lang === 'bn' ? 'আপনার উত্তর লিখুন...' : 'Write your reply...'; ?>" class="w-full p-3 rounded-lg border border-border-color bg-card text-card-text focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all resize-none text-sm" rows="2"></textarea>
                                            <div class="flex justify-end gap-2 mt-2">
                                                <button onclick="toggleReplyForm(<?php echo $comment['id']; ?>)" class="px-3 py-1.5 rounded-lg bg-muted-bg hover:bg-border-color transition-colors text-sm font-bold text-card-text">
                                                    <?php echo $lang === 'bn' ? 'বাতিল' : 'Cancel'; ?>
                                                </button>
                                                <button onclick="postReply(<?php echo $comment['id']; ?>)" class="px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white transition-colors text-sm font-bold">
                                                    <?php echo $lang === 'bn' ? 'উত্তর পাঠান' : 'Send Reply'; ?>
                                                </button>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-8 text-muted-text"><?php echo $lang === 'bn' ? 'এখনও কোনো মন্তব্য নেই।' : 'No comments yet.'; ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- User Profile Modal -->
    <div id="profile-modal" class="hidden fixed inset-0 bg-black/50 dark:bg-black/70 z-[200] flex items-center justify-center p-4 backdrop-blur-sm" onclick="closeProfileModal()">
        <div class="bg-card rounded-2xl shadow-2xl border border-border-color max-w-md w-full max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
            <div class="sticky top-0 bg-card border-b border-border-color p-4 flex items-center justify-between">
                <h3 class="text-xl font-bold text-card-text">👤 ব্যবহারকারী প্রোফাইল</h3>
                <button onclick="closeProfileModal()" class="p-2 hover:bg-muted-bg rounded-lg transition-colors">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            
            <div id="profile-content" class="p-6 space-y-4">
                <!-- Loading state -->
                <div class="flex items-center justify-center py-8">
                    <i data-lucide="loader" class="w-6 h-6 animate-spin text-bbcRed"></i>
                </div>
            </div>
        </div>
    </div>

    <script>
        const articleId = '<?php echo htmlspecialchars($articleId); ?>';
        const lang = '<?php echo $lang; ?>';
        let bookmarks = JSON.parse(localStorage.getItem("breachtimes-bookmarks") || "[]");
        let fontSize = "md";
        let userVotes = JSON.parse(localStorage.getItem(`votes-${articleId}`) || "{}");
        let commentSort = localStorage.getItem(`sort-${articleId}`) || "newest";

        const savedTheme = localStorage.getItem("breachtimes-theme");
        const systemDark = window.matchMedia("(prefers-color-scheme: dark)").matches;
        if (savedTheme === "dark" || (!savedTheme && systemDark)) {
            document.documentElement.classList.add("dark");
        }

        lucide.createIcons();

        // Progress bar on scroll
        window.addEventListener("scroll", () => {
            const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
            const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
            const scrolled = (winScroll / height) * 100;
            document.getElementById("progress-bar").style.width = scrolled + "%";
        });

        // Character counter
        document.addEventListener('DOMContentLoaded', () => {
            // Initialize Quill editor for comments
            window.quillEditor = new Quill('#quill-editor', {
                theme: 'snow',
                modules: {
                    toolbar: [
                        [{ 'header': [1, 2, false] }],
                        ['bold', 'italic', 'underline', 'strike'],
                        ['blockquote', 'code-block'],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        ['link'],
                        ['clean']
                    ]
                },
                placeholder: 'আপনার মতামত জানান...'
            });

            // Character counter
            window.quillEditor.on('text-change', () => {
                const text = window.quillEditor.getText().trim();
                const count = text.length;
                const charCountEl = document.getElementById('char-counter');
                charCountEl.textContent = count + '/5000';
                
                // Show warning at 80%
                if (count >= 4000) {
                    charCountEl.classList.add('text-orange-500');
                    charCountEl.classList.remove('text-red-500');
                }
                // Show danger at 95%
                else if (count >= 4750) {
                    charCountEl.classList.add('text-red-500');
                    charCountEl.classList.remove('text-orange-500');
                } else {
                    charCountEl.classList.remove('text-orange-500', 'text-red-500');
                }
            });

            const charInput = document.getElementById('comment-input');
            if (charInput) {
                charInput.addEventListener('input', () => {
                    const count = charInput.value.length;
                    const charCountEl = document.getElementById('char-count');
                    charCountEl.textContent = count;
                    
                    // Show warning at 80%
                    if (count >= 4000) {
                        charCountEl.classList.add('near-limit');
                        charCountEl.classList.remove('over-limit');
                    }
                    // Show danger at 95%
                    else if (count >= 4750) {
                        charCountEl.classList.add('over-limit');
                        charCountEl.classList.remove('near-limit');
                    } else {
                        charCountEl.classList.remove('near-limit', 'over-limit');
                    }
                });
            }
            // Restore sort preference
            const select = document.getElementById('sort-comments');
            if (select) select.value = commentSort;
            highlightUserVotes();
        });

        function setFontSize(size) {
            fontSize = size;
            const proseEl = document.querySelector(".prose");
            proseEl.classList.remove("font-size-sm", "font-size-md", "font-size-lg");
            proseEl.classList.add(`font-size-${size}`);
        }

        function showToastMsg(msg, type = 'success') {
            const container = document.getElementById("toast-container");
            const toast = document.createElement("div");
            const icon = type === 'error' ? 'alert-circle' : 'check-circle';
            const color = type === 'error' ? 'text-red-500' : 'text-green-400 dark:text-green-600';
            
            toast.className = "toast-enter fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-black/80 dark:bg-white/90 backdrop-blur text-white dark:text-black px-6 py-3 rounded-full shadow-lg font-bold flex items-center gap-2 mb-2 text-sm w-auto";
            toast.innerHTML = `<i data-lucide="${icon}" class="w-4 h-4 ${color}"></i> ${msg}`;
            container.appendChild(toast);
            lucide.createIcons();
            setTimeout(() => toast.remove(), 3000);
        }

        function handleShare() {
            if (navigator.share) {
                navigator.share({
                    title: document.title,
                    url: window.location.href,
                }).catch(console.error);
            } else {
                const tempInput = document.createElement("input");
                tempInput.value = window.location.href;
                document.body.appendChild(tempInput);
                tempInput.select();
                document.execCommand("copy");
                document.body.removeChild(tempInput);
                showToastMsg("লিঙ্ক ক্লিপবোর্ডে কপি হয়েছে!");
            }
        }

        function toggleBookmark(id) {
            const index = bookmarks.indexOf(id);
            if (index > -1) {
                bookmarks.splice(index, 1);
                showToastMsg("সংরক্ষণ সরানো হয়েছে");
            } else {
                bookmarks.push(id);
                showToastMsg("সংরক্ষিত হয়েছে!");
            }
            localStorage.setItem("breachtimes-bookmarks", JSON.stringify(bookmarks));
        }

        async function postComment(articleId) {
            const text = window.quillEditor?.getText?.()?.trim() || '';
            const trimmedText = text.trim();
            const errorMsg = document.getElementById("error-message");
            const postBtn = document.getElementById("post-btn");

            errorMsg.classList.add('hidden');

            if (!trimmedText) {
                errorMsg.textContent = "অনুগ্রহ করে কিছু লিখুন! (ন্যূনতম ৩ অক্ষর)";
                errorMsg.classList.remove('hidden');
                return;
            }

            if (trimmedText.length < 3) {
                errorMsg.textContent = "আপনার মন্তব্য খুব ছোট! (ন্যূনতম ৩ অক্ষর প্রয়োজন)";
                errorMsg.classList.remove('hidden');
                return;
            }

            postBtn.disabled = true;
            postBtn.innerHTML = '<i data-lucide="loader" class="w-4 h-4 inline-block animate-spin"></i> পাঠাচ্ছে...';
            lucide.createIcons();

            try {
                const res = await fetch("../api/post_comment.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ articleId, user: "Anonymous", text: window.quillEditor.root.innerHTML, lang })
                });
                const result = await res.json();
                if (result.success) {
                    showToastMsg("মন্তব্য প্রকাশিত হয়েছে! পেজ রিলোড হচ্ছে...");
                    setTimeout(() => location.reload(), 1000);
                } else {
                    errorMsg.textContent = result.error || "সমস্যা হয়েছে!";
                    errorMsg.classList.remove('hidden');
                    postBtn.disabled = false;
                    postBtn.textContent = "মন্তব্য প্রকাশ করুন";
                }
            } catch (e) {
                console.error(e);
                errorMsg.textContent = "সার্ভার ত্রুটি!";
                errorMsg.classList.remove('hidden');
                postBtn.disabled = false;
                postBtn.textContent = "মন্তব্য প্রকাশ করুন";
            }
        }

        function toggleTheme() {
            const isDark = document.documentElement.classList.toggle("dark");
            const theme = isDark ? "dark" : "light";
            localStorage.setItem("breachtimes-theme", theme);
            
            // Save to backend if logged in
            fetch("../api/save_theme.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ theme })
            }).catch(console.error);
            
            // Show visual feedback
            showToastMsg(isDark ? "🌙 গাঢ় মোড সক্রিয় হয়েছে" : "☀️ হালকা মোড সক্রিয় হয়েছে");
        }

        function toggleLanguage() {
            const newLang = lang === "bn" ? "en" : "bn";
            window.location.href = `?id=${articleId}&lang=${newLang}`;
        }

        // Generate Table of Contents
        const tocInitialize = () => {
            const prose = document.querySelector('.prose');
            const tocContainer = document.getElementById('toc-container');
            
            if (prose && tocContainer) {
                const headers = prose.querySelectorAll('h2, h3');
                if (headers.length === 0) {
                    tocContainer.innerHTML = '<p class="italic opacity-50"><?php echo $lang === "bn" ? "কোনো সূচিপত্র নেই" : "No table of contents available"; ?></p>';
                    return;
                }

                const ul = document.createElement('ul');
                ul.className = 'space-y-2 border-l border-border-color pl-4';

                headers.forEach((header, index) => {
                    if (!header.id) {
                        header.id = `section-${index}`;
                    }

                    const li = document.createElement('li');
                    const link = document.createElement('a');
                    link.href = `#${header.id}`;
                    link.textContent = header.textContent;
                    link.className = `block hover:text-bbcRed transition-colors ${header.tagName === 'H3' ? 'pl-4 text-xs' : 'font-bold'}`;
                    
                    link.addEventListener('click', (e) => {
                        e.preventDefault();
                        header.scrollIntoView({ behavior: 'smooth' });
                    });

                    li.appendChild(link);
                    ul.appendChild(li);
                });
                
                tocContainer.appendChild(ul);
            }
        };
        document.addEventListener('DOMContentLoaded', tocInitialize);

        async function handleLogout() {
            try {
                await fetch("../api/logout.php");
                window.location.reload();
            } catch (e) {
                console.error(e);
            }
        }

        // Comment Features
        function toggleReplyForm(commentId) {
            const form = document.getElementById(`reply-form-${commentId}`);
            form.classList.toggle('hidden');
            if (!form.classList.contains('hidden')) {
                document.getElementById(`reply-input-${commentId}`).focus();
            }
        }

        async function postReply(parentCommentId) {
            const textarea = document.getElementById(`reply-input-${parentCommentId}`);
            const text = textarea.value.trim();
            const form = document.getElementById(`reply-form-${parentCommentId}`);
            const buttons = form.querySelectorAll('button');

            if (!text) {
                showToastMsg("<?php echo $lang === 'bn' ? 'অনুগ্রহ করে কিছু লিখুন!' : 'Please write something!'; ?>", 'error');
                return;
            }

            // Disable buttons and show loading
            buttons.forEach(btn => btn.disabled = true);
            const sendBtn = Array.from(buttons).find(b => b.textContent.includes('পাঠান'));
            if (sendBtn) sendBtn.innerHTML = '<i data-lucide="loader" class="w-4 h-4 inline animate-spin"></i> পাঠাচ্ছে...';
            lucide.createIcons();

            try {
                const res = await fetch("../api/post_reply.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ parentCommentId, text })
                });
                const result = await res.json();
                if (result.success) {
                    showToastMsg("<?php echo $lang === 'bn' ? 'উত্তর পাঠানো হয়েছে! পেজ রিলোড হচ্ছে...' : 'Reply posted! Reloading...'; ?>");
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToastMsg(result.error || "<?php echo $lang === 'bn' ? 'সমস্যা হয়েছে!' : 'Error occurred!'; ?>", 'error');
                    buttons.forEach(btn => btn.disabled = false);
                    if (sendBtn) sendBtn.textContent = "<?php echo $lang === 'bn' ? 'উত্তর পাঠান' : 'Send Reply'; ?>";
                }
            } catch (e) {
                console.error(e);
                showToastMsg("<?php echo $lang === 'bn' ? 'সার্ভার ত্রুটি!' : 'Server error!'; ?>", 'error');
                buttons.forEach(btn => btn.disabled = false);
                if (sendBtn) sendBtn.textContent = "<?php echo $lang === 'bn' ? 'উত্তর পাঠান' : 'Send Reply'; ?>";
            }
        }

        async function voteComment(commentId, voteType) {
            const upBtn = document.querySelector(`.vote-btn-up[data-comment-id="${commentId}"]`);
            const downBtn = document.querySelector(`.vote-btn-down[data-comment-id="${commentId}"]`);
            const countEl = document.getElementById(`vote-count-${commentId}`);
            
            // Add loading state
            upBtn.classList.add('opacity-50');
            downBtn.classList.add('opacity-50');
            
            try {
                const res = await fetch("../api/vote_comment.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ commentId, voteType })
                });
                const result = await res.json();
                if (result.success) {
                    document.getElementById(`vote-count-${commentId}`).textContent = result.score;
                    
                    // Animate count change
                    countEl.classList.add('animate-pulse-pop');
                    setTimeout(() => countEl.classList.remove('animate-pulse-pop'), 400);
                    
                    // Store user vote
                    userVotes[commentId] = voteType;
                    localStorage.setItem(`votes-${articleId}`, JSON.stringify(userVotes));
                    
                    // Highlight votes with animation
                    highlightUserVotes();
                    
                    showToastMsg("<?php echo $lang === 'bn' ? 'ভোট রেকর্ড হয়েছে!' : 'Vote recorded!'; ?>");
                } else {
                    showToastMsg(result.error || "<?php echo $lang === 'bn' ? 'সমস্যা হয়েছে!' : 'Error occurred!'; ?>", 'error');
                }
            } catch (e) {
                console.error(e);
                showToastMsg("<?php echo $lang === 'bn' ? 'সার্ভার ত্রুটি!' : 'Server error!'; ?>", 'error');
            } finally {
                // Remove loading state
                upBtn.classList.remove('opacity-50');
                downBtn.classList.remove('opacity-50');
            }
        }

        function highlightUserVotes() {
            Object.keys(userVotes).forEach(commentId => {
                const voteType = userVotes[commentId];
                const upBtn = document.querySelector(`.vote-btn-up[data-comment-id="${commentId}"]`);
                const downBtn = document.querySelector(`.vote-btn-down[data-comment-id="${commentId}"]`);
                
                if (upBtn && downBtn) {
                    upBtn.classList.remove('text-green-500', 'font-bold');
                    downBtn.classList.remove('text-red-500', 'font-bold');
                    
                    if (voteType === 'upvote') {
                        upBtn.classList.add('text-green-500', 'font-bold');
                    } else if (voteType === 'downvote') {
                        downBtn.classList.add('text-red-500', 'font-bold');
                    }
                }
            });
        }

        async function togglePin(commentId, isPinned) {
            try {
                const res = await fetch("../api/pin_comment.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ commentId, isPinned: !isPinned })
                });
                const result = await res.json();
                if (result.success) {
                    showToastMsg(result.message);
                    setTimeout(() => location.reload(), 800);
                } else {
                    showToastMsg(result.error || "<?php echo $lang === 'bn' ? 'সমস্যা হয়েছে!' : 'Error occurred!'; ?>", 'error');
                }
            } catch (e) {
                console.error(e);
                showToastMsg("<?php echo $lang === 'bn' ? 'সার্ভার ত্রুটি!' : 'Server error!'; ?>", 'error');
            }
        }

        async function deleteComment(id) {
            if (!confirm("<?php echo $lang === 'bn' ? 'এই মন্তব্য মুছে দিতে চান?' : 'Delete this comment?'; ?>")) return;
            
            try {
                const res = await fetch('../api/delete_comment.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({id})
                });
                const result = await res.json();
                if (result.success) {
                    showToastMsg("<?php echo $lang === 'bn' ? 'মন্তব্য মুছে দেওয়া হয়েছে!' : 'Comment deleted!'; ?>");
                    setTimeout(() => location.reload(), 800);
                } else alert("<?php echo $lang === 'bn' ? 'সমস্যা হয়েছে!' : 'Error!'; ?>");
            } catch(e) { console.error(e); }
        }

        // Comment Sorting
        function sortComments() {
            const select = document.getElementById('sort-comments');
            commentSort = select.value;
            localStorage.setItem(`sort-${articleId}`, commentSort);
            
            const comments = Array.from(document.querySelectorAll('[id^="comment-"]'));
            const container = document.querySelector('.space-y-6');
            
            comments.sort((a, b) => {
                const aId = parseInt(a.id.split('-')[1]);
                const bId = parseInt(b.id.split('-')[1]);
                
                switch(commentSort) {
                    case 'newest':
                        return bId - aId;
                    case 'oldest':
                        return aId - bId;
                    case 'helpful': {
                        const aScore = parseInt(a.querySelector('[id^="vote-count-"]')?.textContent || 0);
                        const bScore = parseInt(b.querySelector('[id^="vote-count-"]')?.textContent || 0);
                        return bScore - aScore;
                    }
                    case 'discussed': {
                        const aReplies = a.querySelectorAll('.ml-12 .ml-9').length;
                        const bReplies = b.querySelectorAll('.ml-12 .ml-9').length;
                        return bReplies - aReplies;
                    }
                    default:
                        return 0;
                }
            });
            
            comments.forEach(comment => container.appendChild(comment));
            showToastMsg("মন্তব্য সাজানো হয়েছে!");
        }

        // User Profile Modal
        async function showUserProfile(userName) {
            const modal = document.getElementById('profile-modal');
            const content = document.getElementById('profile-content');
            
            modal.classList.remove('hidden');
            content.innerHTML = '<div class="flex items-center justify-center py-8"><i data-lucide="loader" class="w-6 h-6 animate-spin text-bbcRed"></i></div>';
            lucide.createIcons();

            try {
                const res = await fetch("../api/get_user_profile.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ userName })
                });
                const result = await res.json();

                if (result.success) {
                    const profile = result.profile;
                    const starRating = '⭐'.repeat(Math.min(Math.ceil(profile.helpfulPercent / 20), 5));
                    
                    content.innerHTML = `
                        <div class="space-y-4">
                            <!-- User Header -->
                            <div class="flex items-center gap-4 pb-4 border-b border-border-color">
                                <div class="w-16 h-16 rounded-full bg-gradient-to-br from-bbcRed to-orange-500 flex items-center justify-center font-bold text-white text-2xl shadow-lg">
                                    ${profile.displayName[0].toUpperCase()}
                                </div>
                                <div>
                                    <h4 class="text-lg font-bold text-card-text">${profile.displayName}</h4>
                                    <p class="text-xs text-muted-text">${profile.email}</p>
                                </div>
                            </div>

                            <!-- Stats -->
                            <div class="grid grid-cols-2 gap-3">
                                <div class="bg-muted-bg p-3 rounded-lg border border-border-color">
                                    <div class="text-2xl font-bold text-bbcRed">${profile.commentCount}</div>
                                    <div class="text-xs text-muted-text">মন্তব্য</div>
                                </div>
                                <div class="bg-muted-bg p-3 rounded-lg border border-border-color">
                                    <div class="text-2xl font-bold text-green-500">${profile.upvotes}</div>
                                    <div class="text-xs text-muted-text">ঊর্ধ্বমূলক ভোট</div>
                                </div>
                                <div class="bg-muted-bg p-3 rounded-lg border border-border-color">
                                    <div class="text-2xl font-bold text-blue-500">${profile.score}</div>
                                    <div class="text-xs text-muted-text">মোট স্কোর</div>
                                </div>
                                <div class="bg-muted-bg p-3 rounded-lg border border-border-color">
                                    <div class="text-lg font-bold">${profile.helpfulPercent}%</div>
                                    <div class="text-xs text-muted-text">সহায়ক রেটিং</div>
                                </div>
                            </div>

                            <!-- Helpful Rating -->
                            <div class="bg-muted-bg p-3 rounded-lg border border-border-color">
                                <div class="text-sm font-bold text-card-text mb-2">সহায়কতা রেটিং</div>
                                <div class="flex items-center gap-2">
                                    <div class="text-lg">${starRating}</div>
                                    <div class="text-sm text-muted-text">${profile.helpfulPercent}% সহায়ক</div>
                                </div>
                            </div>

                            <!-- Badges -->
                            <div>
                                <div class="text-sm font-bold text-card-text mb-2">🏆 ব্যাজ</div>
                                <div class="flex flex-wrap gap-2">
                                    ${profile.badges.map(badge => `
                                        <span class="bg-bbcRed/20 text-bbcRed text-xs font-bold px-3 py-1 rounded-full border border-bbcRed/30">
                                            ${badge}
                                        </span>
                                    `).join('')}
                                </div>
                            </div>

                            <!-- Recent Comments -->
                            ${profile.recentComments.length > 0 ? `
                                <div>
                                    <div class="text-sm font-bold text-card-text mb-2">সাম্প্রতিক মন্তব্য</div>
                                    <div class="space-y-2">
                                        ${profile.recentComments.map(comment => `
                                            <div class="bg-muted-bg p-2 rounded-lg text-xs text-card-text border border-border-color">
                                                <p class="mb-1">"${comment.text}"</p>
                                                <div class="flex items-center justify-between text-muted-text text-xs">
                                                    <span>${comment.time}</span>
                                                    <span>👍 ${comment.upvotes}</span>
                                                </div>
                                            </div>
                                        `).join('')}
                                    </div>
                                </div>
                            ` : ''}
                        </div>
                    `;
                } else {
                    content.innerHTML = `
                        <div class="text-center py-8">
                            <p class="text-muted-text">ব্যবহারকারী তথ্য পাওয়া যায়নি</p>
                        </div>
                    `;
                }
                lucide.createIcons();
            } catch (error) {
                console.error(error);
                content.innerHTML = `
                    <div class="text-center py-8">
                        <p class="text-red-500">সার্ভার ত্রুটি</p>
                    </div>
                `;
            }
        }

        function closeProfileModal() {
            document.getElementById('profile-modal').classList.add('hidden');
        }

        // Close modal on escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeProfileModal();
            }
        });
    </script>
</body>
</html>