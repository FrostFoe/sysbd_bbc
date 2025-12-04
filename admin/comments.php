<?php
session_start();
require_once "../includes/db.php";

if (!isset($_SESSION["user_role"]) || $_SESSION["user_role"] !== "admin") {
    header("Location: ../login/");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $action = $data['action'] ?? '';
    $id = $data['id'] ?? null;
    
    if ($id) {
        if ($action === 'delete') {
            $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true]);
            exit;
        }
    }
}

$comments = $pdo->query("
    SELECT c.id, c.text, c.created_at, c.user_name, a.title as article_title, a.id as article_id, a.lang 
    FROM comments c
    JOIN articles a ON c.article_id = a.id
    ORDER BY c.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Comments Moderation | BreachTimes</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="../assets/styles.css" rel="stylesheet" />
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
</head>
<body class="bg-page text-card-text font-sans transition-colors duration-500">
    <div class="max-w-[1280px] mx-auto px-4 py-8">
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-3xl font-bold">Comments Moderation</h1>
            <a href="index.php" class="text-bbcRed font-bold hover:underline">Back to Dashboard</a>
        </div>

        <div class="bg-card rounded-xl shadow-soft border border-border-color overflow-hidden">
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
                            <a href="../read/index.php?id=<?php echo $c['article_id']; ?>&lang=<?php echo $c['lang']; ?>" target="_blank" class="hover:text-bbcRed">
                                <?php echo htmlspecialchars($c['article_title']); ?>
                            </a>
                        </td>
                        <td class="p-4 text-xs text-muted-text"><?php echo $c['created_at']; ?></td>
                        <td class="p-4 text-right">
                            <button onclick="deleteComment(<?php echo $c['id']; ?>)" class="text-red-500 hover:text-red-700 p-2"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        lucide.createIcons();
        async function deleteComment(id) {
            if(!confirm('Delete this comment?')) return;
            const res = await fetch('comments.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({action: 'delete', id})
            });
            if((await res.json()).success) location.reload();
        }
    </script>
</body>
</html>
