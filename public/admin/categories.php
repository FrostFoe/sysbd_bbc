<?php
require_once "includes/header.php";
require_once "../../src/config/db.php";

$sql = "SELECT id, title_bn, title_en, color FROM categories ORDER BY id ASC";
$categories = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">Manage Categories</h1>
    <button onclick="openModal(null)" class="bg-bbcRed text-white px-4 py-2 rounded-lg font-bold flex items-center gap-2">
        <i data-lucide="plus" class="w-4 h-4"></i> New Category
    </button>
</div>

<div class="bg-card rounded-xl border border-border-color shadow-sm overflow-hidden">
    <?php if (empty($categories)): ?>
        <div class="p-8 text-center text-muted-text">
            <i data-lucide="folder" class="w-16 h-16 mx-auto mb-4 text-border-color"></i>
            <p class="text-lg font-bold mb-2">No Categories Found</p>
            <p class="text-sm mb-4">It looks like there are no categories yet.</p>
            <button onclick="openModal(null)" class="bg-bbcRed text-white px-4 py-2 rounded-lg font-bold hover:opacity-90 transition-opacity flex items-center gap-2 w-fit mx-auto">
                <i data-lucide="plus" class="w-4 h-4"></i> Create New Category
            </button>
        </div>
    <?php else: ?>
        <table class="w-full text-left border-collapse">
            <thead class="bg-muted-bg text-muted-text text-xs uppercase">
                <tr>
                    <th class="p-4">ID</th>
                    <th class="p-4">Title (BN)</th>
                    <th class="p-4">Title (EN)</th>
                    <th class="p-4">Color</th>
                    <th class="p-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border-color">
                <?php foreach ($categories as $c): ?>
                <tr class="hover:bg-muted-bg transition-colors">
                    <td class="p-4 font-mono text-sm"><?php echo htmlspecialchars(
                        $c["id"],
                    ); ?></td>
                    <td class="p-4 font-bold"><?php echo htmlspecialchars(
                        $c["title_bn"],
                    ); ?></td>
                    <td class="p-4"><?php echo htmlspecialchars(
                        $c["title_en"],
                    ); ?></td>
                    <td class="p-4">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded border border-border-color" style="background-color: <?php echo htmlspecialchars(
                                $c["color"],
                            ); ?>"></div>
                            <span class="text-xs text-muted-text"><?php echo htmlspecialchars(
                                $c["color"],
                            ); ?></span>
                        </div>
                    </td>
                    <td class="p-4 text-right">
                        <div class="flex justify-end gap-2">
                            <button onclick='openModal(<?php echo json_encode(
                                $c,
                            ); ?>)' class="p-2 text-blue-600 hover:bg-blue-50 rounded"><i data-lucide="edit-2" class="w-4 h-4"></i></button>
                            <button onclick="deleteCategory('<?php echo $c[
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

<!-- Modal -->
<div id="catModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm hidden items-center justify-center z-[100]">
    <div class="bg-card w-full max-w-md p-6 rounded-xl shadow-2xl">
        <h2 id="modalTitle" class="text-xl font-bold mb-4">New Category</h2>
        <form onsubmit="saveCategory(event)" class="space-y-4">
            <div>
                <label class="block text-sm font-bold mb-1">ID (Slug)</label>
                <input name="id" id="catId" required class="w-full p-2 rounded border border-border-color bg-muted-bg">
            </div>
            <div>
                <label class="block text-sm font-bold mb-1">Title (Bangla)</label>
                <input name="title_bn" id="catTitleBn" required class="w-full p-2 rounded border border-border-color bg-muted-bg">
            </div>
            <div>
                <label class="block text-sm font-bold mb-1">Title (English)</label>
                <input name="title_en" id="catTitleEn" required class="w-full p-2 rounded border border-border-color bg-muted-bg">
            </div>
            <div>
                <label class="block text-sm font-bold mb-1">Color</label>
                <input type="color" name="color" id="catColor" class="w-full h-10 rounded cursor-pointer">
            </div>
            <div class="flex justify-end gap-2 mt-6">
                <button type="button" onclick="closeModal()" class="px-4 py-2 text-sm font-bold text-muted-text hover:text-card-text">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-bbcRed text-white rounded-lg font-bold text-sm">Save</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(data) {
    document.getElementById('catModal').classList.replace('hidden', 'flex');
    if (data) {
        document.getElementById('modalTitle').innerText = 'Edit Category';
        document.getElementById('catId').value = data.id;
        document.getElementById('catId').readOnly = true; // Cannot change ID on edit
        document.getElementById('catTitleBn').value = data.title_bn;
        document.getElementById('catTitleEn').value = data.title_en;
        document.getElementById('catColor').value = data.color;
    } else {
        document.getElementById('modalTitle').innerText = 'New Category';
        document.getElementById('catId').value = '';
        document.getElementById('catId').readOnly = false;
        document.getElementById('catTitleBn').value = '';
        document.getElementById('catTitleEn').value = '';
        document.getElementById('catColor').value = '#b80000';
    }
}

function closeModal() {
    document.getElementById('catModal').classList.replace('flex', 'hidden');
}

async function saveCategory(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());
    
    try {
        const res = await fetch('../api/save_category.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        if ((await res.json()).success) location.reload();
    } catch (err) { console.error(err); }
}

async function deleteCategory(id) {
    if (!confirm('Delete this category?')) return;
    try {
        const res = await fetch('../api/delete_category.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });
        if ((await res.json()).success) location.reload();
    } catch (err) { console.error(err); }
}
</script>

<?php require_once "includes/footer.php"; ?>
