<?php
require_once "includes/header.php";
require_once "../../src/config/db.php";

$status = isset($_GET["status"]) ? $_GET["status"] : "all";
$search = isset($_GET["search"]) ? trim($_GET["search"]) : "";
$catFilter = isset($_GET["cat"]) ? $_GET["cat"] : "";

// Fetch Categories for Filter
$categories = $pdo
    ->query("SELECT * FROM categories")
    ->fetchAll(PDO::FETCH_ASSOC);

// Build Query
$sql = "SELECT a.id, a.title_bn, a.title_en, a.status, a.image, a.created_at, a.published_at, c.title_en as cat_en, c.title_bn as cat_bn 
        FROM articles a 
        LEFT JOIN categories c ON a.category_id = c.id 
        WHERE 1=1";
$params = [];

if ($status !== "all") {
    $sql .= " AND a.status = ?";
    $params[] = $status;
}

if (!empty($search)) {
    $sql .= " AND (a.title_bn LIKE ? OR a.title_en LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($catFilter)) {
    $sql .= " AND a.category_id = ?";
    $params[] = $catFilter;
}

$sql .= " ORDER BY a.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
    <h1 class="text-2xl font-bold">Manage Articles</h1>
    
    <div class="flex flex-col sm:flex-row gap-2 w-full md:w-auto">
        <!-- Filter Form -->
        <form method="GET" class="flex gap-2 w-full md:w-auto">
            <?php if ($status !== "all"): ?>
                <input type="hidden" name="status" value="<?php echo htmlspecialchars(
                    $status,
                ); ?>">
            <?php endif; ?>
            
            <input type="text" name="search" placeholder="Search articles..." value="<?php echo htmlspecialchars(
                $search,
            ); ?>" class="p-2 rounded border border-border-color bg-card text-sm w-full md:w-48 focus:border-bbcRed outline-none">
            
            <select name="cat" class="custom-select p-2.5 rounded-lg border border-border-color bg-card text-sm w-32 md:w-40 text-card-text" onchange="this.form.submit()">
                <option value="">All Categories</option>
                <?php foreach ($categories as $c): ?>
                    <option value="<?php echo $c[
                        "id"
                    ]; ?>" <?php echo $catFilter === $c["id"]
    ? "selected"
    : ""; ?>>
                        <?php echo $c["title_en"]; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <button type="submit" class="bg-muted-bg p-2 rounded hover:bg-bbcRed hover:text-white transition-colors">
                <i data-lucide="search" class="w-4 h-4"></i>
            </button>
        </form>

        <a href="edit_article.php" class="bg-bbcRed text-white px-4 py-2 rounded-lg font-bold hover:opacity-90 transition-opacity flex items-center gap-2 whitespace-nowrap justify-center">
            <i data-lucide="plus" class="w-4 h-4"></i> <span class="hidden sm:inline">New</span>
        </a>
    </div>
</div>

<div class="bg-card rounded-xl border border-border-color shadow-sm overflow-hidden">
    <?php if (empty($articles)): ?>
        <div class="p-8 text-center text-muted-text">
            <i data-lucide="file-text" class="w-16 h-16 mx-auto mb-4 text-border-color"></i>
            <p class="text-lg font-bold mb-2">No Articles Found</p>
            <p class="text-sm mb-4">It looks like there are no articles matching your criteria.</p>
            <a href="edit_article.php" class="bg-bbcRed text-white px-4 py-2 rounded-lg font-bold hover:opacity-90 transition-opacity flex items-center gap-2 w-fit mx-auto">
                <i data-lucide="plus" class="w-4 h-4"></i> Create New Article
            </a>
        </div>
    <?php else: ?>
        <table class="w-full text-left border-collapse">
            <thead class="bg-muted-bg text-muted-text text-xs uppercase">
                <tr>
                    <th class="p-4">Article</th>
                    <th class="p-4">Status</th>
                    <th class="p-4">Category</th>
                    <th class="p-4">Date</th>
                    <th class="p-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border-color">
                <?php foreach ($articles as $a): ?>
                <tr class="hover:bg-muted-bg transition-colors">
                    <td class="p-4">
                        <div class="flex items-center gap-4">
                            <img src="<?php echo htmlspecialchars(
                                $a["image"] ?? "",
                            ); ?>" onerror="this.src='https://placehold.co/100x100?text=Img'" class="w-12 h-12 rounded object-cover bg-gray-200">
                            <div class="max-w-md">
                                <?php if (!empty($a["title_bn"])): ?>
                                    <a href="edit_article.php?id=<?php echo $a[
                                        "id"
                                    ]; ?>" class="font-bold text-sm block hover:text-bbcRed line-clamp-1 font-hind mb-0.5">
                                        <?php echo htmlspecialchars(
                                            $a["title_bn"],
                                        ); ?>
                                    </a>
                                <?php endif; ?>
                                <?php if (!empty($a["title_en"])): ?>
                                    <span class="text-xs text-muted-text block line-clamp-1">
                                        <?php echo htmlspecialchars(
                                            $a["title_en"],
                                        ); ?>
                                    </span>
                                <?php endif; ?>
                                <?php if (
                                    empty($a["title_bn"]) &&
                                    empty($a["title_en"])
                                ): ?>
                                    <span class="text-xs italic text-muted-text">(No Title)</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td class="p-4">
                        <?php
                        $statusColors = [
                            "published" =>
                                "bg-green-100 text-green-700 border-green-200",
                            "draft" =>
                                "bg-yellow-100 text-yellow-700 border-yellow-200",
                            "archived" =>
                                "bg-gray-100 text-gray-700 border-gray-200",
                        ];
                        $colorClass =
                            $statusColors[$a["status"]] ??
                            $statusColors["draft"];
                        ?>
                        <span class="px-2 py-1 rounded-full text-xs font-bold border <?php echo $colorClass; ?>">
                            <?php echo ucfirst($a["status"]); ?>
                        </span>
                    </td>
                    <td class="p-4 text-sm">
                        <div class="flex flex-col">
                            <span class="font-hind"><?php echo $a["cat_bn"] ??
                                "-"; ?></span>
                            <span class="text-xs text-muted-text"><?php echo $a[
                                "cat_en"
                            ] ?? "-"; ?></span>
                        </div>
                    </td>
                    <td class="p-4 text-xs text-muted-text">
                        <div class="flex flex-col">
                            <span>Pub: <?php echo date(
                                "M d, Y",
                                strtotime($a["published_at"]),
                            ); ?></span>
                            <span class="opacity-70">Cr: <?php echo date(
                                "M d",
                                strtotime($a["created_at"]),
                            ); ?></span>
                        </div>
                    </td>
                    <td class="p-4 text-right">
                        <div class="flex justify-end gap-2">
                            <a href="edit_article.php?id=<?php echo $a[
                                "id"
                            ]; ?>" class="p-2 text-blue-600 hover:bg-blue-50 rounded"><i data-lucide="edit-2" class="w-4 h-4"></i></a>
                            <button onclick="deleteArticle('<?php echo $a[
                                "id"
                            ]; ?>')" class="p-2 text-red-600 hover:bg-red-50 rounded"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<script>
async function deleteArticle(id) {
    if (!confirm('Are you sure you want to delete this article? This will delete both language versions.')) return;
    
    try {
        const res = await fetch('../api/delete_article.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });
        const result = await res.json();
        if (result.success) {
            showToastMsg('Article deleted successfully');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToastMsg('Error deleting article', 'error');
        }
    } catch (e) {
        console.error(e);
        showToastMsg('Server error', 'error');
    }
}
</script>

<?php require_once "includes/footer.php"; ?>
