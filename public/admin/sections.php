<?php
require_once "includes/header.php";
require_once "../../src/config/db.php";

// Fetch Sections
$stmt = $pdo->query("SELECT * FROM sections ORDER BY sort_order ASC");
$sections = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">Manage Sections</h1>
    <button onclick="openSectionModal(null)" class="bg-bbcRed text-white px-4 py-2 rounded-lg font-bold flex items-center gap-2">
        <i data-lucide="plus" class="w-4 h-4"></i> New Section
    </button>
</div>

<div class="bg-card rounded-xl border border-border-color shadow-sm overflow-hidden">
    <?php if (empty($sections)): ?>
        <div class="p-8 text-center text-muted-text">
            <i data-lucide="layers" class="w-16 h-16 mx-auto mb-4 text-border-color"></i>
            <p class="text-lg font-bold mb-2">No Sections Found</p>
            <p class="text-sm mb-4">It looks like there are no sections yet. Sections help organize your homepage content.</p>
            <button onclick="openSectionModal(null)" class="bg-bbcRed text-white px-4 py-2 rounded-lg font-bold hover:opacity-90 transition-opacity flex items-center gap-2 w-fit mx-auto">
                <i data-lucide="plus" class="w-4 h-4"></i> Create New Section
            </button>
        </div>
    <?php else: ?>
        <table class="w-full text-left border-collapse max-md:border-0">
            <thead class="bg-muted-bg text-muted-text text-xs uppercase max-md:sr-only">
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
                <tr class="hover:bg-muted-bg transition-colors max-md:flex max-md:flex-col max-md:border-b max-md:border-border-color max-md:p-4 max-md:mb-4 max-md:bg-card max-md:rounded-xl max-md:shadow-sm">
                    <td class="p-4 font-bold text-muted-text max-md:block max-md:text-[0.8em] max-md:text-left max-md:py-2 max-md:w-full max-md:border-b-0"><?php echo $s[
                        "sort_order"
                    ]; ?></td>
                    <td class="p-4 font-mono text-sm max-md:block max-md:text-[0.8em] max-md:text-left max-md:py-2 max-md:w-full max-md:border-b-0"><?php echo htmlspecialchars(
                        $s["id"],
                    ); ?></td>
                    <td class="p-4 font-bold max-md:block max-md:text-[0.8em] max-md:text-left max-md:py-2 max-md:w-full max-md:border-b-0">
                        <div class="flex flex-col">
                            <span class="font-hind text-sm"><?php echo htmlspecialchars(
                                $s["title_bn"],
                            ); ?></span>
                            <span class="text-xs text-muted-text"><?php echo htmlspecialchars(
                                $s["title_en"],
                            ); ?></span>
                        </div>
                    </td>
                    <td class="p-4 max-md:block max-md:text-[0.8em] max-md:text-left max-md:py-2 max-md:w-full max-md:border-b-0"><span class="px-2 py-1 bg-blue-50 text-blue-700 rounded text-xs font-bold uppercase"><?php echo htmlspecialchars(
                        $s["type"],
                    ); ?></span></td>
                    <td class="p-4 text-right max-md:block max-md:text-[0.8em] max-md:text-left max-md:py-2 max-md:w-full max-md:border-b-0 max-md:mt-2 max-md:pt-4 max-md:border-t max-md:border-border-color">
                        <button onclick="deleteSection('<?php echo $s[
                            "id"
                        ]; ?>')" class="text-red-600 hover:bg-red-50 p-2 rounded"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- Section Modal -->
<div id="sectionModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm hidden items-center justify-center z-[100]">
    <div class="bg-card w-full max-w-md p-6 rounded-xl shadow-2xl">
        <h2 id="sectionModalTitle" class="text-xl font-bold mb-4">New Section</h2>
        <form onsubmit="saveSection(event)" class="space-y-4">
            <div>
                <label class="block text-sm font-bold mb-1">ID (Slug)</label>
                <input name="id" id="sectionId" required class="w-full p-2 rounded border border-border-color bg-muted-bg">
            </div>
            <div>
                <label class="block text-sm font-bold mb-1">Title (Bangla)</label>
                <input name="title_bn" id="sectionTitleBn" required class="w-full p-2 rounded border border-border-color bg-muted-bg">
            </div>
            <div>
                <label class="block text-sm font-bold mb-1">Title (English)</label>
                <input name="title_en" id="sectionTitleEn" required class="w-full p-2 rounded border border-border-color bg-muted-bg">
            </div>
            <div>
                <label class="block text-sm font-bold mb-2">Type</label>
                <select name="type" id="sectionType" class="custom-select w-full p-2.5 rounded-lg border border-border-color bg-card text-card-text text-sm">
                    <option value="hero">Hero</option>
                    <option value="grid">Grid</option>
                    <option value="list">List</option>
                    <option value="carousel">Carousel</option>
                    <option value="highlight">Highlight</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-bold mb-1">Sort Order</label>
                <input type="number" name="sort_order" id="sectionSortOrder" value="0" class="w-full p-2 rounded border border-border-color bg-muted-bg">
            </div>
            <!-- Optional fields: highlight_color, associated_category, style - can be added later -->
            <div class="flex justify-end gap-2 mt-6">
                <button type="button" onclick="closeSectionModal()" class="px-4 py-2 text-sm font-bold text-muted-text hover:text-card-text">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-bbcRed text-white rounded-lg font-bold text-sm">Save</button>
            </div>
        </form>
    </div>
</div>

<script>
function openSectionModal(data) {
    document.getElementById('sectionModal').classList.replace('hidden', 'flex');
    if (data) {
        document.getElementById('sectionModalTitle').innerText = 'Edit Section';
        document.getElementById('sectionId').value = data.id;
        document.getElementById('sectionId').readOnly = true; 
        document.getElementById('sectionTitleBn').value = data.title_bn;
        document.getElementById('sectionTitleEn').value = data.title_en;
        document.getElementById('sectionType').value = data.type;
        document.getElementById('sectionSortOrder').value = data.sort_order;
    } else {
        document.getElementById('sectionModalTitle').innerText = 'New Section';
        document.getElementById('sectionId').value = '';
        document.getElementById('sectionId').readOnly = false;
        document.getElementById('sectionTitleBn').value = '';
        document.getElementById('sectionTitleEn').value = '';
        document.getElementById('sectionType').value = 'hero';
        document.getElementById('sectionSortOrder').value = '0';
    }
}

function closeSectionModal() {
    document.getElementById('sectionModal').classList.replace('flex', 'hidden');
}

async function saveSection(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());
    
    try {
        const res = await fetch('../api/save_section.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await res.json();
        if (result.success) {
            showToastMsg('Section saved successfully!');
            location.reload();
        } else {
            showToastMsg('Error saving section: ' + result.message, 'error');
        }
    } catch (err) { 
        console.error(err);
        showToastMsg('Server error', 'error');
    }
}

async function deleteSection(id) {
    if (!confirm('Delete this section?')) return;
    try {
        const res = await fetch('../api/delete_section.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });
        const result = await res.json();
        if (result.success) {
            showToastMsg('Section deleted successfully!');
            location.reload();
        } else {
            showToastMsg('Error deleting section: ' + result.message, 'error');
        }
    } catch (err) { 
        console.error(err);
        showToastMsg('Server error', 'error');
    }
}
</script>

<?php require_once "includes/footer.php"; ?>
