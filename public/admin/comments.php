<?php
require_once "includes/header.php";
require_once "../../src/config/db.php";

// Handle basic pagination or filtering later if needed
// Simple view for now
$comments = $pdo->query("
    SELECT c.id, c.text, c.created_at, c.user_name, a.title_en, a.title_bn, a.id as article_id 
    FROM comments c
    JOIN articles a ON c.article_id = a.id
    ORDER BY c.created_at DESC
    LIMIT 50
")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">Comments Moderation</h1>
</div>

<div class="bg-card rounded-xl border border-border-color shadow-sm overflow-hidden">
    <?php if (empty($comments)): ?>
        <div class="p-8 text-center text-muted-text">
            <i data-lucide="message-circle" class="w-16 h-16 mx-auto mb-4 text-border-color"></i>
            <p class="text-lg font-bold mb-2">No Comments Found</p>
            <p class="text-sm">There are currently no comments to moderate.</p>
        </div>
    <?php else: ?>
        <table class="w-full text-left border-collapse">
            <thead class="bg-muted-bg text-muted-text text-xs uppercase">
                <tr>
                    <th class="p-4">User</th>
                    <th class="p-4">Comment</th>
                    <th class="p-4">Article</th>
                    <th class="p-4">Time</th>
                    <th class="p-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border-color">
                <?php foreach ($comments as $c): ?>
                <tr class="hover:bg-muted-bg transition-colors">
                    <td class="p-4 font-bold text-sm"><?php echo htmlspecialchars($c['user_name']); ?></td>
                    <td class="p-4 text-sm max-w-md truncate"><?php echo htmlspecialchars($c['text']); ?></td>
                    <td class="p-4 text-xs text-muted-text">
                        <?php 
                            $displayTitle = !empty($c['title_en']) ? $c['title_en'] : $c['title_bn'];
                            $langParam = !empty($c['title_en']) ? 'en' : 'bn';
                        ?>
                        <a href="../read/index.php?id=<?php echo $c['article_id']; ?>&lang=<?php echo $langParam; ?>" target="_blank" class="hover:text-bbcRed">
                            <?php echo htmlspecialchars($displayTitle); ?>
                        </a>
                    </td>
                    <td class="p-4 text-xs text-muted-text"><?php echo date('M d, H:i', strtotime($c['created_at'])); ?></td>
                    <td class="p-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <button onclick="deleteComment(<?php echo $c['id']; ?>)" class="text-red-500 hover:text-red-700 p-2 rounded hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors" title="Delete">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<script>
    async function deleteComment(id) {
        if(!confirm('Delete this comment?')) return;
        
        try {
            const res = await fetch('../api/delete_comment.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({id})
            });
            const result = await res.json();
            if(result.success) location.reload();
            else alert('Error');
        } catch(e) { console.error(e); }
    }
</script>

<?php require_once "includes/footer.php"; ?>