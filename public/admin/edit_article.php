<?php
require_once "includes/header.php";
require_once "../../src/config/db.php";

$id = isset($_GET["id"]) ? $_GET["id"] : null;

$article = null;
if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
    $stmt->execute([$id]);
    $article = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Fetch Categories for dropdown
$categories = $pdo
    ->query("SELECT * FROM categories")
    ->fetchAll(PDO::FETCH_ASSOC);

// Fetch Sections for dropdown
$sections = $pdo->query("SELECT * FROM sections")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold"><?php echo $article
            ? "Edit Article (Unified)"
            : "Create New Article (Unified)"; ?></h1>
        <?php if ($article): ?>
            <div class="flex gap-2">
                <a href="../read/index.php?id=<?php echo $article[
                    "id"
                ]; ?>&lang=bn" target="_blank" class="text-blue-600 hover:underline text-sm flex items-center gap-1">
                    View (BN) <i data-lucide="external-link" class="w-3 h-3"></i>
                </a>
                <a href="../read/index.php?id=<?php echo $article[
                    "id"
                ]; ?>&lang=en" target="_blank" class="text-blue-600 hover:underline text-sm flex items-center gap-1">
                    View (EN) <i data-lucide="external-link" class="w-3 h-3"></i>
                </a>
            </div>
        <?php endif; ?>
    </div>

    <form id="articleForm" onsubmit="saveArticle(event)" class="space-y-8">
        <input type="hidden" name="id" value="<?php echo $article["id"] ??
            uniqid("art_"); ?>">

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-8 bg-card p-6 rounded-xl border border-border-color shadow-sm">
                
                <h3 class="font-bold text-lg mb-4 border-b border-border-color pb-2">Content (Unified)</h3>
                
                <!-- Title fields -->
                <div>
                    <label class="block text-sm font-bold mb-2">Title (বাংলা)</label>
                    <input name="title_bn" id="title_bn" required class="w-full p-3 rounded-lg border border-border-color bg-card focus:border-bbcRed outline-none font-hind" value="<?php echo htmlspecialchars(
                        $article["title_bn"] ?? "",
                    ); ?>" placeholder="নিবন্ধের শিরোনাম লিখুন...">
                </div>

                <div>
                    <label class="block text-sm font-bold mb-2">Title (English)</label>
                    <input name="title_en" id="title_en" class="w-full p-3 rounded-lg border border-border-color bg-card focus:border-bbcRed outline-none" value="<?php echo htmlspecialchars(
                        $article["title_en"] ?? "",
                    ); ?>" placeholder="Enter article title...">
                </div>

                <!-- Summary fields -->
                <div>
                    <label class="block text-sm font-bold mb-2">Summary (বাংলা)</label>
                    <textarea name="summary_bn" id="summary_bn" rows="3" class="w-full p-3 rounded-lg border border-border-color bg-card focus:border-bbcRed outline-none font-hind" placeholder="সংক্ষিপ্ত সারসংক্ষেপ..."><?php echo htmlspecialchars(
                        $article["summary_bn"] ?? "",
                    ); ?></textarea>
                </div>

                <div>
                    <label class="block text-sm font-bold mb-2">Summary (English)</label>
                    <textarea name="summary_en" id="summary_en" rows="3" class="w-full p-3 rounded-lg border border-border-color bg-card focus:border-bbcRed outline-none" placeholder="Brief summary..."><?php echo htmlspecialchars(
                        $article["summary_en"] ?? "",
                    ); ?></textarea>
                </div>

                <!-- Content fields -->
                <div>
                    <label class="block text-sm font-bold mb-2">Content (বাংলা)</label>
                    <div id="quill-bn" class="bg-card h-96 rounded-lg border border-border-color"></div>
                    <input type="hidden" name="content_bn" id="content-bn-input" value="<?php echo htmlspecialchars(
                        $article["content_bn"] ?? "",
                    ); ?>">
                </div>

                <div>
                    <label class="block text-sm font-bold mb-2">Content (English)</label>
                    <div id="quill-en" class="bg-card h-96 rounded-lg border border-border-color"></div>
                    <input type="hidden" name="content_en" id="content-en-input" value="<?php echo htmlspecialchars(
                        $article["content_en"] ?? "",
                    ); ?>">
                </div>

                <!-- TOC fields -->
                <div class="border-t border-border-color pt-4 mt-4">
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-sm font-bold">Table of Contents (Bangla)</label>
                        <button type="button" onclick="generateTOC('bn')" class="text-xs bg-bbcRed text-white px-2 py-1 rounded hover:bg-[var(--color-bbcRed-hover)]">
                            Generate from Content
                        </button>
                    </div>
                    <div id="toc-bn-editor" class="space-y-2 bg-muted-bg p-3 rounded-lg min-h-[100px]">
                        <p class="text-xs text-muted-text text-center py-4">No TOC generated yet.</p>
                    </div>
                    <input type="hidden" name="toc_bn" id="toc-bn-input" value="<?php echo htmlspecialchars(
                        $article["toc_bn"] ?? "[]",
                    ); ?>">
                </div>

                <div class="border-t border-border-color pt-4 mt-4">
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-sm font-bold">Table of Contents (English)</label>
                        <button type="button" onclick="generateTOC('en')" class="text-xs bg-bbcRed text-white px-2 py-1 rounded hover:bg-[var(--color-bbcRed-hover)]">
                            Generate from Content
                        </button>
                    </div>
                    <div id="toc-en-editor" class="space-y-2 bg-muted-bg p-3 rounded-lg min-h-[100px]">
                        <p class="text-xs text-muted-text text-center py-4">No TOC generated yet.</p>
                    </div>
                    <input type="hidden" name="toc_en" id="toc-en-input" value="<?php echo htmlspecialchars(
                        $article["toc_en"] ?? "[]",
                    ); ?>">
                </div>
            </div>

            <!-- Sidebar Settings -->
            <div class="space-y-6">
                <!-- Restore Autosave Alert -->
                <div id="restore-alert" class="hidden bg-blue-50 border border-blue-200 rounded-xl p-4">
                    <div class="flex items-start gap-3">
                        <i data-lucide="save" class="w-5 h-5 text-blue-600 mt-0.5"></i>
                        <div>
                            <h4 class="text-sm font-bold text-blue-800">Unsaved Draft Found</h4>
                            <p class="text-xs text-blue-600 mt-1">A newer version of this article was found in your browser.</p>
                            <button type="button" onclick="restoreDraft()" class="mt-2 text-xs bg-blue-600 text-white px-3 py-1.5 rounded hover:bg-blue-700 transition-colors font-bold">Restore Draft</button>
                        </div>
                    </div>
                </div>

                <div class="bg-card p-5 rounded-xl border border-border-color shadow-sm">
                    <h3 class="font-bold mb-4 text-sm uppercase text-muted-text">Publishing</h3>
                    
                    <div class="mb-4">
                        <label class="block text-xs font-bold mb-2">Status</label>
                        <select name="status" class="custom-select w-full p-2.5 rounded-lg border border-border-color bg-card text-card-text text-sm">
                            <option value="draft" <?php echo ($article[
                                "status"
                            ] ??
                                "") ===
                            "draft"
                                ? "selected"
                                : ""; ?>>Draft</option>
                            <option value="published" <?php echo ($article[
                                "status"
                            ] ??
                                "") ===
                            "published"
                                ? "selected"
                                : ""; ?>>Published</option>
                            <option value="archived" <?php echo ($article[
                                "status"
                            ] ??
                                "") ===
                            "archived"
                                ? "selected"
                                : ""; ?>>Archived</option>
                        </select>
                    </div>

                    <button type="submit" class="w-full bg-bbcRed text-white py-3 rounded-lg font-bold hover:opacity-90 transition-opacity text-sm uppercase tracking-wide">
                        <?php echo $article
                            ? "Update All Versions"
                            : "Publish Article"; ?>
                    </button>
                </div>

                <div class="bg-card p-5 rounded-xl border border-border-color shadow-sm">
                    <h3 class="font-bold mb-4 text-sm uppercase text-muted-text">Organization</h3>
                    
                    <div class="mb-4">
                        <label class="block text-xs font-bold mb-2">Category</label>
                        <select name="category_id" class="custom-select w-full p-2.5 rounded-lg border border-border-color bg-card text-card-text text-sm">
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat[
                                    "id"
                                ]; ?>" <?php echo ($article["category_id"] ??
    "") ===
$cat["id"]
    ? "selected"
    : ""; ?>>
                                    <?php echo $cat["title_bn"] .
                                        " / " .
                                        $cat["title_en"]; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-xs font-bold mb-2">Section (Homepage)</label>
                        <select name="sectionId" class="custom-select w-full p-2.5 rounded-lg border border-border-color bg-card text-card-text text-sm">
                            <option value="">None</option>
                            <?php foreach ($sections as $sec): ?>
                                <option value="<?php echo $sec[
                                    "id"
                                ]; ?>" <?php echo ($article["section_id"] ??
    "") ===
$sec["id"]
    ? "selected"
    : ""; ?>>
                                    <?php echo $sec["title_en"]; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="bg-card p-5 rounded-xl border border-border-color shadow-sm">
                    <h3 class="font-bold mb-4 text-sm uppercase text-muted-text">Media</h3>
                    
                    <div class="mb-4">
                        <label class="block text-xs font-bold mb-1">Featured Image URL</label>
                        <input name="image" id="image-url" class="w-full p-2 rounded border border-border-color bg-muted-bg text-sm" value="<?php echo htmlspecialchars(
                            $article["image"] ?? "",
                        ); ?>">
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-xs font-bold mb-1">Or Upload</label>
                        <input type="file" onchange="handleImageUpload(this)" class="w-full text-xs">
                    </div>

                    <div class="aspect-video bg-muted-bg rounded overflow-hidden">
                        <img id="image-preview" src="<?php echo htmlspecialchars(
                            $article["image"] ?? "",
                        ); ?>" class="w-full h-full object-cover opacity-50">
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    let quillBn, quillEn;
    const storageKey = 'article-draft-' + (new URLSearchParams(window.location.search).get('id') || 'new');
    let tocBnData = [];
    let tocEnData = [];

    // Initialize Quill editors
    document.addEventListener('DOMContentLoaded', () => {
        quillBn = new Quill('#quill-bn', {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, false] }], // Added h3
                    ['bold', 'italic', 'underline', 'strike'],
                    ['blockquote', 'code-block'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    ['link', 'image'],
                    ['clean']
                ]
            },
            placeholder: 'বাংলায় লিখুন...'
        });

        quillEn = new Quill('#quill-en', {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, false] }], // Added h3
                    ['bold', 'italic', 'underline', 'strike'],
                    ['blockquote', 'code-block'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    ['link', 'image'],
                    ['clean']
                ]
            },
            placeholder: 'Write in English...'
        });

        // Load existing content if editing
        const contentBnInput = document.getElementById('content-bn-input');
        const contentEnInput = document.getElementById('content-en-input');
        
        if (contentBnInput.value) {
            quillBn.root.innerHTML = contentBnInput.value;
        }
        if (contentEnInput.value) {
            quillEn.root.innerHTML = contentEnInput.value;
        }

        // Initialize TOC data
        try {
            tocBnData = JSON.parse(document.getElementById('toc-bn-input').value || '[]');
            tocEnData = JSON.parse(document.getElementById('toc-en-input').value || '[]');
        } catch (e) {
            console.error("Error parsing TOC data", e);
        }
        renderTOC('bn', tocBnData);
        renderTOC('en', tocEnData);
        
        // Check for autosave
        const saved = localStorage.getItem(storageKey);
        if (saved) {
            document.getElementById('restore-alert').classList.remove('hidden');
        }
        
        // Autosave
        const form = document.getElementById('articleForm');
        if (form) {
            form.addEventListener('change', () => autosaveArticle());
            
            // Auto-save Quill editors
            quillBn.on('text-change', () => autosaveArticle());
            quillEn.on('text-change', () => autosaveArticle());
        }
    });

    function handleImageUpload(input) {
        const file = input.files[0];
        if (!file) return;
        
        if (file.size > 2 * 1024 * 1024) {
            alert("File too large (max 2MB)");
            return;
        }

        const reader = new FileReader();
        reader.onload = function (e) {
            document.getElementById('image-url').value = e.target.result;
            document.getElementById('image-preview').src = e.target.result;
            document.getElementById('image-preview').classList.remove('opacity-50');
        };
        reader.readAsDataURL(file);
    }

    function generateTOC(lang) {
        const quill = lang === 'bn' ? quillBn : quillEn;
        const root = quill.root;
        const headers = root.querySelectorAll('h2, h3');
        const toc = [];

        headers.forEach((header, index) => {
            let text = header.textContent.trim();
            if (!text) return;

            // Generate ID if missing
            if (!header.id) {
                // Simple ID generation: lang-h-index-random
                const id = `${lang}-h-${index}-${Math.floor(Math.random() * 1000)}`;
                header.id = id;
            }

            toc.push({
                id: header.id,
                text: text,
                level: parseInt(header.tagName.substring(1))
            });
        });

        if (lang === 'bn') {
            tocBnData = toc;
        } else {
            tocEnData = toc;
        }

        renderTOC(lang, toc);
        
        // Notify user
        showToastMsg(`Generated ${toc.length} TOC items for ${lang.toUpperCase()}`);
    }

    function renderTOC(lang, items) {
        const container = document.getElementById(`toc-${lang}-editor`);
        const input = document.getElementById(`toc-${lang}-input`);
        
        if (!items || items.length === 0) {
            container.innerHTML = '<p class="text-xs text-muted-text text-center py-4">No TOC generated yet.</p>';
            input.value = '[]';
            return;
        }

        container.innerHTML = '';
        items.forEach((item, index) => {
            const row = document.createElement('div');
            row.className = 'flex items-center gap-2 mb-2 bg-card p-2 rounded border border-border-color';
            row.style.marginLeft = (item.level - 2) * 20 + 'px'; // Indent h3

            // Level Indicator
            const badge = document.createElement('span');
            badge.className = 'text-xs font-mono bg-muted-bg px-1 rounded text-muted-text';
            badge.innerText = `H${item.level}`;

            // Text Input
            const textInput = document.createElement('input');
            textInput.type = 'text';
            textInput.value = item.text;
            textInput.className = 'flex-1 text-sm bg-transparent border-none focus:outline-none';
            textInput.onchange = (e) => {
                items[index].text = e.target.value;
                updateTOCInput(lang);
            };

            // ID (Hidden/Visible on hover? Keep simple for now)
            
            // Delete Button
            const delBtn = document.createElement('button');
            delBtn.type = 'button';
            delBtn.innerHTML = '<i data-lucide="trash-2" class="w-4 h-4 text-red-500"></i>';
            delBtn.onclick = () => {
                items.splice(index, 1);
                if (lang === 'bn') tocBnData = items; else tocEnData = items;
                renderTOC(lang, items);
                // Note: We don't remove ID from content, just from TOC
            };

            row.appendChild(badge);
            row.appendChild(textInput);
            row.appendChild(delBtn);
            container.appendChild(row);
        });
        
        // Re-initialize icons
        if (window.lucide) lucide.createIcons();

        updateTOCInput(lang);
    }

    function updateTOCInput(lang) {
        const data = lang === 'bn' ? tocBnData : tocEnData;
        document.getElementById(`toc-${lang}-input`).value = JSON.stringify(data);
    }

    async function saveArticle(e) {
        e.preventDefault();
        
        // Get content from Quill editors
        const contentBn = quillBn.root.innerHTML;
        const contentEn = quillEn.root.innerHTML;
        
        document.getElementById('content-bn-input').value = contentBn;
        document.getElementById('content-en-input').value = contentEn;
        
        // TOC inputs are already updated by renderTOC/onchange, but let's ensure
        document.getElementById('toc-bn-input').value = JSON.stringify(tocBnData);
        document.getElementById('toc-en-input').value = JSON.stringify(tocEnData);

        const formData = new FormData(e.target);

        try {
            const res = await fetch('../api/save_article.php', {
                method: 'POST',
                body: formData
            });
            const result = await res.json();

            if (result.success) {
                // Clear autosave on success
                localStorage.removeItem(storageKey);
                
                showToastMsg('Article saved successfully!');
                if (!window.location.search.includes('id=')) {
                    setTimeout(() => {
                        window.location.href = `edit_article.php?id=${result.id}`;
                    }, 1000);
                }
            } else {
                showToastMsg('Error saving article', 'error');
            }
        } catch (err) {
            console.error(err);
            showToastMsg('Server error', 'error');
        }
    }

    function autosaveArticle() {
        const formData = {
            title_bn: document.getElementById('title_bn')?.value,
            summary_bn: document.getElementById('summary_bn')?.value,
            content_bn: quillBn.root.innerHTML,
            title_en: document.getElementById('title_en')?.value,
            summary_en: document.getElementById('summary_en')?.value,
            content_en: quillEn.root.innerHTML,
            toc_bn: tocBnData,
            toc_en: tocEnData
        };
        
        localStorage.setItem(storageKey, JSON.stringify(formData));
    }

    function restoreDraft() {
        const saved = localStorage.getItem(storageKey);
        if (!saved) return;
        
        try {
            const data = JSON.parse(saved);
            document.getElementById('title_bn').value = data.title_bn || '';
            document.getElementById('summary_bn').value = data.summary_bn || '';
            document.getElementById('title_en').value = data.title_en || '';
            document.getElementById('summary_en').value = data.summary_en || '';
            
            if (data.content_bn) {
                quillBn.root.innerHTML = data.content_bn;
            }
            if (data.content_en) {
                quillEn.root.innerHTML = data.content_en;
            }
            
            if (data.toc_bn) {
                tocBnData = data.toc_bn;
                renderTOC('bn', tocBnData);
            }
            if (data.toc_en) {
                tocEnData = data.toc_en;
                renderTOC('en', tocEnData);
            }
            
            document.getElementById('restore-alert').classList.add('hidden');
            showToastMsg('Draft restored successfully!');
        } catch (e) {
            console.error('Failed to restore draft', e);
        }
    }
</script>

<?php require_once "includes/footer.php"; ?>
