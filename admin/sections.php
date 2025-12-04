<?php
require_once "includes/header.php";
require_once "../config/db.php";

// Fetch Sections
$stmt = $pdo->query("SELECT * FROM sections ORDER BY sort_order ASC");
$sections = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">Manage Sections</h1>
    <!-- Add Create Button later if needed -->
</div>

<div class="bg-card rounded-xl border border-border-color shadow-sm overflow-hidden">
    <table class="w-full text-left border-collapse">
        <thead class="bg-muted-bg text-muted-text text-xs uppercase">
            <tr>
                <th class="p-4">Order</th>
                <th class="p-4">ID</th>
                <th class="p-4">Title (BN / EN)</th>
                <th class="p-4">Type</th>
                <th class="p-4 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-border-color">
            <?php foreach ($sections as $s): ?>
            <tr class="hover:bg-muted-bg transition-colors">
                <td class="p-4 font-bold text-muted-text"><?php echo $s['sort_order']; ?></td>
                <td class="p-4 font-mono text-sm"><?php echo htmlspecialchars($s['id']); ?></td>
                <td class="p-4 font-bold">
                    <div class="flex flex-col">
                        <span class="font-hind text-sm"><?php echo htmlspecialchars($s['title_bn']); ?></span>
                        <span class="text-xs text-muted-text"><?php echo htmlspecialchars($s['title_en']); ?></span>
                    </div>
                </td>
                <td class="p-4"><span class="px-2 py-1 bg-blue-50 text-blue-700 rounded text-xs font-bold uppercase"><?php echo htmlspecialchars($s['type']); ?></span></td>
                <td class="p-4 text-right">
                    <button onclick="deleteSection('<?php echo $s['id']; ?>')" class="text-red-600 hover:bg-red-50 p-2 rounded"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
async function deleteSection(id) {
    if (!confirm('Delete this section?')) return;
    try {
        const res = await fetch('../api/delete_section.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });
        if ((await res.json()).success) location.reload();
    } catch (err) { console.error(err); }
}
</script>

<?php require_once "includes/footer.php"; ?>
