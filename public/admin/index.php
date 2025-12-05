<?php
require_once "includes/header.php";
require_once "../../src/config/db.php";

// Fetch quick stats
$stats = [
    "articles" => $pdo->query("SELECT COUNT(*) FROM articles")->fetchColumn(),
    "comments" => $pdo->query("SELECT COUNT(*) FROM comments")->fetchColumn(),
    "drafts" => $pdo
        ->query("SELECT COUNT(*) FROM articles WHERE status = 'draft'")
        ->fetchColumn(),
    "users" => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
];
?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-card p-6 rounded-xl border border-border-color shadow-sm">
        <div class="flex justify-between items-start mb-4">
            <div>
                <p class="text-muted-text text-sm font-bold uppercase tracking-wider">Total Articles</p>
                <h3 class="text-3xl font-bold text-card-text mt-1"><?php echo number_format(
                    $stats["articles"],
                ); ?></h3>
            </div>
            <div class="p-3 bg-blue-50 dark:bg-blue-900/20 text-blue-600 rounded-lg">
                <i data-lucide="file-text" class="w-6 h-6"></i>
            </div>
        </div>
        <a href="articles.php" class="text-sm text-blue-600 font-bold hover:underline">View Details &rarr;</a>
    </div>

    <div class="bg-card p-6 rounded-xl border border-border-color shadow-sm">
        <div class="flex justify-between items-start mb-4">
            <div>
                <p class="text-muted-text text-sm font-bold uppercase tracking-wider">Total Comments</p>
                <h3 class="text-3xl font-bold text-card-text mt-1"><?php echo number_format(
                    $stats["comments"],
                ); ?></h3>
            </div>
            <div class="p-3 bg-green-50 dark:bg-green-900/20 text-green-600 rounded-lg">
                <i data-lucide="message-square" class="w-6 h-6"></i>
            </div>
        </div>
        <a href="comments.php" class="text-sm text-green-600 font-bold hover:underline">Moderation &rarr;</a>
    </div>

    <div class="bg-card p-6 rounded-xl border border-border-color shadow-sm">
        <div class="flex justify-between items-start mb-4">
            <div>
                <p class="text-muted-text text-sm font-bold uppercase tracking-wider">Drafts</p>
                <h3 class="text-3xl font-bold text-card-text mt-1"><?php echo number_format(
                    $stats["drafts"],
                ); ?></h3>
            </div>
            <div class="p-3 bg-yellow-50 dark:bg-yellow-900/20 text-yellow-600 rounded-lg">
                <i data-lucide="edit-3" class="w-6 h-6"></i>
            </div>
        </div>
        <a href="articles.php?status=draft" class="text-sm text-yellow-600 font-bold hover:underline">Manage Drafts &rarr;</a>
    </div>

    <div class="bg-card p-6 rounded-xl border border-border-color shadow-sm">
        <div class="flex justify-between items-start mb-4">
            <div>
                <p class="text-muted-text text-sm font-bold uppercase tracking-wider">Users</p>
                <h3 class="text-3xl font-bold text-card-text mt-1"><?php echo number_format(
                    $stats["users"],
                ); ?></h3>
            </div>
            <div class="p-3 bg-purple-50 dark:bg-purple-900/20 text-purple-600 rounded-lg">
                <i data-lucide="users" class="w-6 h-6"></i>
            </div>
        </div>
        <span class="text-sm text-muted-text">System Users</span>
    </div>
</div>

<div class="bg-card rounded-xl border border-border-color shadow-sm p-6">
    <h3 class="text-lg font-bold mb-4">Recent Activity</h3>
    <p class="text-muted-text text-sm">Activity logs coming soon...</p>
</div>

<?php require_once "includes/footer.php"; ?>
