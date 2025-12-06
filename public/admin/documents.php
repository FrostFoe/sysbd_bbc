<?php
require_once "includes/header.php";
require_once "../../src/config/db.php";

// Get article list with counts
$articlesStmt = $pdo->query("
    SELECT a.id, a.title_bn, a.title_en, COUNT(d.id) as doc_count
    FROM articles a
    LEFT JOIN documents d ON a.id = d.article_id
    GROUP BY a.id
    ORDER BY a.published_at DESC
");
$articles = $articlesStmt->fetchAll(PDO::FETCH_ASSOC);

// Get selected article
$selectedArticleId = isset($_GET["article_id"]) ? trim($_GET["article_id"]) : null;
$documents = [];
$articleTitle = "";

if ($selectedArticleId) {
    // Validate article exists
    $artStmt = $pdo->prepare(
        "SELECT title_bn, title_en FROM articles WHERE id = ?",
    );
    $artStmt->execute([$selectedArticleId]);
    $art = $artStmt->fetch();

    if ($art) {
        $articleTitle = $art["title_bn"] ?? $art["title_en"];

        $docsStmt = $pdo->prepare(
            "SELECT * FROM documents WHERE article_id = ? ORDER BY sort_order ASC",
        );
        $docsStmt->execute([$selectedArticleId]);
        $documents = $docsStmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

<!-- Header Section -->
<div class="mb-8">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl md:text-4xl font-bold text-card-text flex items-center gap-3">
                <i data-lucide="file-text" class="w-8 h-8 text-bbcRed"></i>
                Documents Manager
            </h1>
            <p class="text-muted-text mt-2">Manage downloadable files for your articles</p>
        </div>
        <button onclick="openDocumentModal(null)" class="bg-bbcRed hover:bg-[var(--color-bbcRed-hover)] text-white px-6 py-3 rounded-lg font-bold flex items-center gap-2 transition-all shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed whitespace-nowrap" id="add-doc-btn" disabled>
            <i data-lucide="plus" class="w-5 h-5"></i> <span class="hidden sm:inline">Add Document</span><span class="sm:hidden">Add</span>
        </button>
    </div>
</div>

<!-- Main Content Grid -->
<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
    <!-- Articles Sidebar -->
    <div class="lg:col-span-1">
        <div class="bg-card p-6 rounded-2xl shadow-soft border border-border-color h-fit lg:sticky lg:top-28">
            <h3 class="font-bold text-lg text-card-text mb-4 flex items-center gap-2">
                <i data-lucide="list" class="w-5 h-5 text-bbcRed"></i>
                Articles
            </h3>
            <div class="space-y-2 max-h-[70vh] overflow-y-auto pr-2">
                <?php if (empty($articles)): ?>
                    <div class="text-center py-8">
                        <i data-lucide="inbox" class="w-12 h-12 mx-auto mb-2 text-border-color opacity-50"></i>
                        <p class="text-muted-text text-sm">No articles found</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($articles as $article): ?>
                    <button onclick="selectArticle('<?php echo htmlspecialchars(
                        $article["id"],
                    ); ?>')" 
                            class="w-full text-left p-3.5 rounded-lg hover:bg-muted-bg transition-all duration-200 border-2 <?php echo $selectedArticleId ==
                            $article["id"]
                                ? "border-bbcRed bg-red-50/10 dark:bg-red-900/5"
                                : "border-border-color hover:border-bbcRed/30"; ?> group">
                        <div class="font-bold text-sm text-card-text truncate group-hover:text-bbcRed transition-colors">
                            <?php echo htmlspecialchars(
                                $article["title_bn"] ?? $article["title_en"],
                            ); ?>
                        </div>
                        <div class="flex items-center justify-between mt-1.5">
                            <span class="text-xs text-muted-text font-mono">
                                <?php echo htmlspecialchars(
                                    substr($article["id"], 0, 10),
                                ); ?>
                            </span>
                            <span class="text-xs bg-bbcRed/20 text-bbcRed px-2 py-0.5 rounded font-bold">
                                <?php echo $article["doc_count"]; ?> docs
                            </span>
                        </div>
                    </button>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Documents Content -->
    <div class="lg:col-span-3">
        <?php if (!$selectedArticleId): ?>
            <!-- Empty State -->
            <div class="bg-card p-12 md:p-16 rounded-2xl shadow-soft border border-border-color text-center">
                <div class="mb-4">
                    <i data-lucide="inbox" class="w-16 h-16 mx-auto text-border-color opacity-40"></i>
                </div>
                <h2 class="text-2xl font-bold text-card-text mb-2">Select an Article</h2>
                <p class="text-muted-text max-w-sm mx-auto">Choose an article from the left sidebar to start managing its documents and media files</p>
            </div>
        <?php else: ?>
            <div class="space-y-6">
                <!-- Article Header -->
                <div class="bg-card p-6 rounded-2xl shadow-soft border border-border-color">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div>
                            <h2 class="text-xl md:text-2xl font-bold text-card-text break-words">
                                <?php echo htmlspecialchars($articleTitle); ?>
                            </h2>
                            <p class="text-muted-text text-sm mt-1">ID: <span class="font-mono"><?php echo htmlspecialchars(
                                $selectedArticleId,
                            ); ?></span></p>
                        </div>
                        <button onclick="openDocumentModal(null)" class="bg-bbcRed hover:bg-[var(--color-bbcRed-hover)] text-white px-6 py-2.5 rounded-lg font-bold flex items-center gap-2 transition-all whitespace-nowrap shadow-md hover:shadow-lg">
                            <i data-lucide="plus" class="w-4 h-4"></i> Add Document
                        </button>
                    </div>
                </div>

                <!-- Documents List -->
                <?php if (empty($documents)): ?>
                    <div class="bg-card p-12 rounded-2xl shadow-soft border border-border-color text-center">
                        <i data-lucide="file-x" class="w-12 h-12 mx-auto mb-3 text-border-color opacity-40"></i>
                        <h3 class="text-lg font-bold text-card-text mb-2">No documents yet</h3>
                        <p class="text-muted-text mb-6">Add your first document to this article</p>
                        <button onclick="openDocumentModal(null)" class="bg-bbcRed hover:bg-[var(--color-bbcRed-hover)] text-white px-6 py-2.5 rounded-lg font-bold transition-all inline-flex items-center gap-2">
                            <i data-lucide="plus" class="w-4 h-4"></i> Add First Document
                        </button>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php foreach ($documents as $doc): ?>
                        <div class="bg-card p-4 rounded-xl border border-border-color hover:border-bbcRed/50 hover:shadow-md transition-all duration-200 group">
                            <!-- File Header -->
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex items-center gap-3 flex-grow min-w-0">
                                    <div class="w-10 h-10 rounded-lg bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center flex-shrink-0 border border-blue-200/50 font-bold text-blue-600 dark:text-blue-400 text-xs">
                                        <?php
                                        $ext = strtoupper(
                                            pathinfo(
                                                $doc["file_name"],
                                                PATHINFO_EXTENSION,
                                            ),
                                        );
                                        echo htmlspecialchars(
                                            substr($ext, 0, 3),
                                        );
                                        ?>
                                    </div>
                                    <div class="flex-grow min-w-0">
                                        <h4 class="font-bold text-sm text-card-text truncate group-hover:text-bbcRed transition-colors" title="<?php echo htmlspecialchars(
                                            $doc["display_name_bn"] ??
                                                $doc["display_name_en"],
                                        ); ?>">
                                            <?php echo htmlspecialchars(
                                                $doc["display_name_bn"] ??
                                                    $doc["display_name_en"],
                                            ); ?>
                                        </h4>
                                        <p class="text-xs text-muted-text mt-0.5"><?php echo htmlspecialchars(
                                            $doc["file_type"],
                                        ); ?></p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1 flex-shrink-0 ml-2">
                                    <button onclick="openDocumentModal(<?php echo $doc[
                                        "id"
                                    ]; ?>)" class="p-2 hover:bg-muted-bg rounded-lg transition-colors text-muted-text hover:text-bbcRed" title="Edit">
                                        <i data-lucide="edit-2" class="w-4 h-4"></i>
                                    </button>
                                    <button onclick="deleteDocument(<?php echo $doc[
                                        "id"
                                    ]; ?>)" class="p-2 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors text-muted-text hover:text-red-500" title="Delete">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- File Details -->
                            <div class="space-y-2 text-xs">
                                <div class="flex items-center justify-between">
                                    <span class="text-muted-text">Size:</span>
                                    <span class="font-semibold text-card-text"><?php echo $doc[
                                        "file_size"
                                    ]
                                        ? number_format(
                                                $doc["file_size"] / 1024,
                                                1,
                                            ) . " KB"
                                        : "N/A"; ?></span>
                                </div>
                                <?php if ($doc["download_url"]): ?>
                                    <div class="p-2 bg-green-50/50 dark:bg-green-900/10 border border-green-200/50 rounded text-green-700 dark:text-green-400 flex items-center gap-1">
                                        <i data-lucide="link" class="w-3 h-3"></i>
                                        <span class="truncate">External URL</span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Description Preview -->
                            <?php if (
                                $doc["description_bn"] ||
                                $doc["description_en"]
                            ): ?>
                                <div class="mt-3 p-2.5 bg-muted-bg/50 rounded-lg text-xs text-muted-text italic line-clamp-2">
                                    <?php echo htmlspecialchars(
                                        $doc["description_bn"] ??
                                            $doc["description_en"],
                                    ); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Document Modal -->
<div id="document-modal" class="hidden fixed inset-0 bg-black/50 z-[200] flex items-center justify-center p-4 backdrop-blur-sm" onclick="closeDocumentModal()">
    <div class="bg-card rounded-2xl shadow-2xl border border-border-color max-w-2xl w-full max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
        <!-- Modal Header -->
        <div class="sticky top-0 bg-card border-b border-border-color p-6 flex items-center justify-between">
            <div>
                <h3 id="modal-title" class="text-2xl font-bold text-card-text">Add Document</h3>
                <p class="text-sm text-muted-text mt-1">Upload a file or link for your article</p>
            </div>
            <button onclick="closeDocumentModal()" class="p-2 hover:bg-muted-bg rounded-lg transition-colors text-muted-text hover:text-card-text">
                <i data-lucide="x" class="w-6 h-6"></i>
            </button>
        </div>
        
        <!-- Modal Content -->
        <div class="p-6 md:p-8">
            <form id="document-form" class="space-y-6">
                <input type="hidden" id="doc-id" name="doc_id" value="">
                <input type="hidden" id="doc-article-id" name="article_id" value="">

                <!-- File Upload Section -->
                <div class="space-y-3">
                    <label class="block text-sm font-bold text-card-text">Upload File</label>
                    <div id="file-input-wrapper" class="border-2 border-dashed border-border-color rounded-xl p-8 text-center cursor-pointer hover:border-bbcRed hover:bg-red-50/5 dark:hover:bg-red-900/5 transition-all group" onclick="document.getElementById('file-input').click()">
                        <input type="file" id="file-input" name="file" class="hidden" accept=".pdf,.zip,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.jpg,.jpeg,.png,.mp4,.mp3" onchange="handleFileSelect()">
                        <div class="space-y-2">
                            <i data-lucide="upload-cloud" class="w-12 h-12 mx-auto text-muted-text group-hover:text-bbcRed transition-colors"></i>
                            <p class="text-sm font-bold text-card-text group-hover:text-bbcRed transition-colors">Click to upload or drag and drop</p>
                            <p class="text-xs text-muted-text">PDF, ZIP, DOC, Images, Videos (Max 100MB)</p>
                        </div>
                    </div>
                    <p id="file-name" class="text-sm text-green-600 dark:text-green-400 font-semibold"></p>
                </div>

                <!-- Divider -->
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-border-color"></div>
                    </div>
                    <div class="relative flex justify-center text-xs uppercase">
                        <span class="px-2 bg-card text-muted-text font-bold">Document Information</span>
                    </div>
                </div>

                <!-- Two-column layout for names -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-card-text mb-2">
                            <i data-lucide="globe" class="w-4 h-4 inline mr-1"></i>
                            নাম (বাংলা) *
                        </label>
                        <input type="text" id="display-name-bn" name="display_name_bn" required class="w-full px-4 py-3 rounded-lg border border-border-color bg-page text-card-text placeholder-muted-text focus:outline-none focus:ring-2 focus:ring-bbcRed transition-all" placeholder="ডকুমেন্টের নাম...">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-card-text mb-2">
                            <i data-lucide="globe" class="w-4 h-4 inline mr-1"></i>
                            Name (English) *
                        </label>
                        <input type="text" id="display-name-en" name="display_name_en" required class="w-full px-4 py-3 rounded-lg border border-border-color bg-page text-card-text placeholder-muted-text focus:outline-none focus:ring-2 focus:ring-bbcRed transition-all" placeholder="Document name...">
                    </div>
                </div>

                <!-- Two-column layout for descriptions -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-card-text mb-2">বর্ণনা (বাংলা)</label>
                        <textarea id="description-bn" name="description_bn" rows="3" class="w-full px-4 py-3 rounded-lg border border-border-color bg-page text-card-text placeholder-muted-text focus:outline-none focus:ring-2 focus:ring-bbcRed resize-none transition-all" placeholder="এই ডকুমেন্ট সম্পর্কে বিস্তারিত জানান..."></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-card-text mb-2">Description (English)</label>
                        <textarea id="description-en" name="description_en" rows="3" class="w-full px-4 py-3 rounded-lg border border-border-color bg-page text-card-text placeholder-muted-text focus:outline-none focus:ring-2 focus:ring-bbcRed resize-none transition-all" placeholder="Describe this document..."></textarea>
                    </div>
                </div>

                <!-- External URL -->
                <div>
                    <label class="block text-sm font-bold text-card-text mb-2">
                        <i data-lucide="link" class="w-4 h-4 inline mr-1"></i>
                        External Download URL (optional)
                    </label>
                    <input type="url" id="download-url" name="download_url" class="w-full px-4 py-3 rounded-lg border border-border-color bg-page text-card-text placeholder-muted-text focus:outline-none focus:ring-2 focus:ring-bbcRed transition-all" placeholder="https://example.com/download/file.pdf">
                    <p class="text-xs text-muted-text mt-2">💡 If provided, users will download from this URL instead of the uploaded file</p>
                </div>

                <!-- Sort Order -->
                <div>
                    <label class="block text-sm font-bold text-card-text mb-2">Display Order</label>
                    <input type="number" id="sort-order" name="sort_order" class="w-full px-4 py-3 rounded-lg border border-border-color bg-page text-card-text focus:outline-none focus:ring-2 focus:ring-bbcRed transition-all" min="0" value="0">
                    <p class="text-xs text-muted-text mt-2">Lower numbers appear first (0 = first position)</p>
                </div>

                <!-- Error Message -->
                <div id="form-error" class="hidden bg-red-50 dark:bg-red-900/10 border border-red-200 dark:border-red-900/30 text-red-600 dark:text-red-400 px-4 py-3 rounded-lg text-sm font-medium flex items-center gap-2">
                    <i data-lucide="alert-circle" class="w-4 h-4 flex-shrink-0"></i>
                    <span id="error-text"></span>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-3 pt-6 border-t border-border-color">
                    <button type="submit" id="save-doc-btn" class="flex-grow bg-bbcRed hover:bg-[var(--color-bbcRed-hover)] text-white px-4 py-3 rounded-lg font-bold transition-all duration-200 flex items-center justify-center gap-2 shadow-md hover:shadow-lg">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        <span id="save-doc-text">Save Document</span>
                    </button>
                    <button type="button" onclick="closeDocumentModal()" class="px-6 py-3 rounded-lg font-bold border border-border-color text-card-text hover:bg-muted-bg transition-all">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    let selectedFile = null;
    let selectedArticleId = '<?php echo htmlspecialchars(
        $selectedArticleId ?? "",
    ); ?>';

    function selectArticle(articleId) {
        window.location.href = `?article_id=${encodeURIComponent(articleId)}`;
    }

    function handleFileSelect() {
        const input = document.getElementById('file-input');
        selectedFile = input.files[0];
        if (selectedFile) {
            document.getElementById('file-name').textContent = selectedFile.name + ' (' + (selectedFile.size / 1024).toFixed(1) + ' KB)';
        }
    }

    function openDocumentModal(docId) {
        if (!selectedArticleId) {
            showToast('Please select an article first', 'error');
            return;
        }

        const modal = document.getElementById('document-modal');
        const form = document.getElementById('document-form');
        
        // Reset form
        form.reset();
        selectedFile = null;
        document.getElementById('file-name').textContent = '';
        document.getElementById('form-error').classList.add('hidden');
        document.getElementById('doc-article-id').value = selectedArticleId;

        if (docId) {
            // Edit mode
            document.getElementById('modal-title').textContent = 'Edit Document';
            document.getElementById('save-doc-text').textContent = 'Update Document';
            document.getElementById('doc-id').value = docId;
            
            // Fetch document data
            fetch(`../api/get_document.php?id=${docId}`)
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        const doc = data.document;
                        document.getElementById('display-name-bn').value = doc.display_name_bn;
                        document.getElementById('display-name-en').value = doc.display_name_en;
                        document.getElementById('description-bn').value = doc.description_bn || '';
                        document.getElementById('description-en').value = doc.description_en || '';
                        document.getElementById('download-url').value = doc.download_url || '';
                        document.getElementById('sort-order').value = doc.sort_order || 0;
                        document.getElementById('file-name').textContent = doc.file_name;
                    }
                })
                .catch(e => console.error(e));
        } else {
            // Create mode
            document.getElementById('modal-title').textContent = 'Add Document';
            document.getElementById('save-doc-text').textContent = 'Save Document';
            document.getElementById('doc-id').value = '';
        }

        modal.classList.remove('hidden');
    }

    function closeDocumentModal() {
        document.getElementById('document-modal').classList.add('hidden');
    }

    document.getElementById('document-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const formData = new FormData();
        const docId = document.getElementById('doc-id').value;
        
        if (docId) {
            formData.append('id', docId);
        }
        
        formData.append('article_id', selectedArticleId);
        formData.append('display_name_bn', document.getElementById('display-name-bn').value);
        formData.append('display_name_en', document.getElementById('display-name-en').value);
        formData.append('description_bn', document.getElementById('description-bn').value);
        formData.append('description_en', document.getElementById('description-en').value);
        formData.append('download_url', document.getElementById('download-url').value);
        formData.append('sort_order', document.getElementById('sort-order').value);
        
        if (selectedFile) {
            formData.append('file', selectedFile);
        }

        const submitBtn = document.getElementById('save-doc-btn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i data-lucide="loader" class="w-4 h-4 inline animate-spin"></i> Saving...';
        lucide.createIcons();

        try {
            const res = await fetch('../api/save_document.php', {
                method: 'POST',
                body: formData
            });
            const result = await res.json();
            
            if (result.success) {
                showToast('Document saved successfully!');
                setTimeout(() => location.reload(), 1000);
            } else {
                document.getElementById('form-error').textContent = result.error || 'Error saving document';
                document.getElementById('form-error').classList.remove('hidden');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i data-lucide="save" class="w-4 h-4"></i> Save Document';
                lucide.createIcons();
            }
        } catch (e) {
            console.error(e);
            document.getElementById('form-error').textContent = 'Server error';
            document.getElementById('form-error').classList.remove('hidden');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i data-lucide="save" class="w-4 h-4"></i> Save Document';
            lucide.createIcons();
        }
    });

    async function deleteDocument(docId) {
        if (!confirm('Delete this document?')) return;
        
        try {
            const res = await fetch('../api/delete_document.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: docId })
            });
            const result = await res.json();
            
            if (result.success) {
                showToast('Document deleted successfully!');
                setTimeout(() => location.reload(), 800);
            } else {
                showToast(result.error || 'Error deleting document', 'error');
            }
        } catch (e) {
            console.error(e);
            showToast('Server error', 'error');
        }
    }

    function showToast(msg, type = 'success') {
        const toast = document.createElement('div');
        const icon = type === 'error' ? 'alert-circle' : 'check-circle';
        const color = type === 'error' ? 'bg-red-600' : 'bg-green-600';
        
        toast.className = `fixed top-4 right-4 ${color} text-white px-6 py-3 rounded-lg shadow-lg font-bold flex items-center gap-2 mb-2 text-sm z-[210] animate-pulse-pop`;
        toast.innerHTML = `<i data-lucide="${icon}" class="w-4 h-4"></i> ${msg}`;
        document.body.appendChild(toast);
        lucide.createIcons();
        
        setTimeout(() => toast.remove(), 3000);
    }

    // Update button state based on article selection
    const addDocBtn = document.getElementById('add-doc-btn');
    if (selectedArticleId) {
        addDocBtn.disabled = false;
    }
</script>

<?php require_once "includes/footer.php"; ?>
