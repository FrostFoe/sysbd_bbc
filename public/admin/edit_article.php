<?php
require_once "includes/header.php";
require_once "../../src/config/db.php";

$id = isset($_GET['id']) ? $_GET['id'] : null;

$article = null;
if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
    $stmt->execute([$id]);
    $article = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Fetch Categories for dropdown
$categories = $pdo->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);

// Fetch Sections for dropdown
$sections = $pdo->query("SELECT * FROM sections")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold"><?php echo $article ? 'Edit Article (Unified)' : 'Create New Article (Unified)'; ?></h1>
        <?php if ($article): ?>
            <div class="flex gap-2">
                <a href="../read/index.php?id=<?php echo $article['id']; ?>&lang=bn" target="_blank" class="text-blue-600 hover:underline text-sm flex items-center gap-1">
                    View (BN) <i data-lucide="external-link" class="w-3 h-3"></i>
                </a>
                <a href="../read/index.php?id=<?php echo $article['id']; ?>&lang=en" target="_blank" class="text-blue-600 hover:underline text-sm flex items-center gap-1">
                    View (EN) <i data-lucide="external-link" class="w-3 h-3"></i>
                </a>
            </div>
        <?php endif; ?>
    </div>

    <form id="articleForm" onsubmit="saveArticle(event)" class="space-y-8">
        <input type="hidden" name="id" value="<?php echo $article['id'] ?? uniqid('art_'); ?>">

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-10">
                
                <!-- Bangla Section -->
                <div class="bg-card p-6 rounded-xl border border-border-color shadow-sm relative">
                    <div class="absolute top-0 right-0 bg-bbcRed text-white text-xs font-bold px-3 py-1 rounded-bl-xl rounded-tr-xl">Bangla</div>
                    <h3 class="font-bold text-lg mb-4 border-b border-border-color pb-2">Bangla Content</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold mb-2">Title (বাংলা)</label>
                            <input name="title_bn" id="title_bn" required class="w-full p-3 rounded-lg border border-border-color bg-card focus:border-bbcRed outline-none font-hind" value="<?php echo htmlspecialchars($article['title_bn'] ?? ''); ?>" placeholder="নিবন্ধের শিরোনাম লিখুন...">
                        </div>

                        <div>
                            <label class="block text-sm font-bold mb-2">Summary (বাংলা)</label>
                            <textarea name="summary_bn" id="summary_bn" rows="3" class="w-full p-3 rounded-lg border border-border-color bg-card focus:border-bbcRed outline-none font-hind" placeholder="সংক্ষিপ্ত সারসংক্ষেপ..."><?php echo htmlspecialchars($article['summary_bn'] ?? ''); ?></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-bold mb-2">Content (বাংলা)</label>
                            <div id="quill-bn" class="bg-card h-96 rounded-lg border border-border-color"></div>
                            <input type="hidden" name="content_bn" id="content-bn-input" value="<?php echo htmlspecialchars($article['content_bn'] ?? ''); ?>">
                        </div>
                    </div>
                </div>

                <!-- English Section -->
                <div class="bg-card p-6 rounded-xl border border-border-color shadow-sm relative">
                    <div class="absolute top-0 right-0 bg-blue-600 text-white text-xs font-bold px-3 py-1 rounded-bl-xl rounded-tr-xl">English</div>
                    <h3 class="font-bold text-lg mb-4 border-b border-border-color pb-2">English Content</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold mb-2">Title (English)</label>
                            <input name="title_en" id="title_en" class="w-full p-3 rounded-lg border border-border-color bg-card focus:border-blue-600 outline-none" value="<?php echo htmlspecialchars($article['title_en'] ?? ''); ?>" placeholder="Enter article title...">
                        </div>

                        <div>
                            <label class="block text-sm font-bold mb-2">Summary (English)</label>
                            <textarea name="summary_en" id="summary_en" rows="3" class="w-full p-3 rounded-lg border border-border-color bg-card focus:border-blue-600 outline-none" placeholder="Brief summary..."><?php echo htmlspecialchars($article['summary_en'] ?? ''); ?></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-bold mb-2">Content (English)</label>
                            <div id="quill-en" class="bg-card h-96 rounded-lg border border-border-color"></div>
                            <input type="hidden" name="content_en" id="content-en-input" value="<?php echo htmlspecialchars($article['content_en'] ?? ''); ?>">
                        </div>
                    </div>
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
                            <option value="draft" <?php echo ($article['status'] ?? '') === 'draft' ? 'selected' : ''; ?>>Draft</option>
                            <option value="published" <?php echo ($article['status'] ?? '') === 'published' ? 'selected' : ''; ?>>Published</option>
                            <option value="archived" <?php echo ($article['status'] ?? '') === 'archived' ? 'selected' : ''; ?>>Archived</option>
                        </select>
                    </div>

                    <button type="submit" class="w-full bg-bbcRed text-white py-3 rounded-lg font-bold hover:opacity-90 transition-opacity text-sm uppercase tracking-wide">
                        <?php echo $article ? 'Update All Versions' : 'Publish Article'; ?>
                    </button>
                </div>

                <div class="bg-card p-5 rounded-xl border border-border-color shadow-sm">
                    <h3 class="font-bold mb-4 text-sm uppercase text-muted-text">Organization</h3>
                    
                    <div class="mb-4">
                        <label class="block text-xs font-bold mb-2">Category</label>
                        <select name="category_id" class="custom-select w-full p-2.5 rounded-lg border border-border-color bg-card text-card-text text-sm">
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo ($article['category_id'] ?? '') === $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo $cat['title_bn'] . ' / ' . $cat['title_en']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-xs font-bold mb-2">Section (Homepage)</label>
                        <select name="sectionId" class="custom-select w-full p-2.5 rounded-lg border border-border-color bg-card text-card-text text-sm">
                            <option value="">None</option>
                            <?php foreach ($sections as $sec): ?>
                                <option value="<?php echo $sec['id']; ?>" <?php echo ($article['section_id'] ?? '') === $sec['id'] ? 'selected' : ''; ?>>
                                    <?php echo $sec['title_en']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="bg-card p-5 rounded-xl border border-border-color shadow-sm">
                    <h3 class="font-bold mb-4 text-sm uppercase text-muted-text">Media</h3>
                    
                    <div class="mb-4">
                        <label class="block text-xs font-bold mb-1">Featured Image URL</label>
                        <input name="image" id="image-url" class="w-full p-2 rounded border border-border-color bg-muted-bg text-sm" value="<?php echo htmlspecialchars($article['image'] ?? ''); ?>">
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-xs font-bold mb-1">Or Upload</label>
                        <input type="file" onchange="handleImageUpload(this)" class="w-full text-xs">
                    </div>

                    <div class="aspect-video bg-muted-bg rounded overflow-hidden">
                        <img id="image-preview" src="<?php echo htmlspecialchars($article['image'] ?? ''); ?>" class="w-full h-full object-cover opacity-50">
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    let quillBn, quillEn;
    const storageKey = 'article-draft-' + (new URLSearchParams(window.location.search).get('id') || 'new');

    // Initialize Quill editors
    document.addEventListener('DOMContentLoaded', () => {
        quillBn = new Quill('#quill-bn', {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, false] }],
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
                    [{ 'header': [1, 2, false] }],
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

    async function saveArticle(e) {
        e.preventDefault();
        
        // Get content from Quill editors
        const contentBn = quillBn.root.innerHTML;
        const contentEn = quillEn.root.innerHTML;
        
        document.getElementById('content-bn-input').value = contentBn;
        document.getElementById('content-en-input').value = contentEn;

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
            
            document.getElementById('restore-alert').classList.add('hidden');
            showToastMsg('Draft restored successfully!');
        } catch (e) {
            console.error('Failed to restore draft', e);
        }
    }
</script>

<?php require_once "includes/footer.php"; ?>
