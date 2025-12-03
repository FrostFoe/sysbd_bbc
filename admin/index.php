<?php
session_start();
require_once "../get_data.php";

if (!isset($_SESSION["user_role"]) || $_SESSION["user_role"] !== "admin") {
    header("Location: ../login/");
    exit();
}

// Fetch data using the existing get_data.php API
$lang = isset($_GET["lang"]) && $_GET["lang"] === "en" ? "en" : "bn";
$data = get_data($lang);
?>
<!doctype html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Dashboard | BreachTimes</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&display=swap');
    </style>
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet" />
    <link href="../assets/styles.css" rel="stylesheet" />
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
</head>
<body class="bg-page text-card-text transition-colors duration-500">
    <div id="toast-container" role="status" aria-live="polite" class="fixed bottom-8 left-1/2 transform -translate-x-1/2 z-[110] pointer-events-none w-full max-w-sm flex flex-col items-center gap-2"></div>

    <main role="main" class="bg-page min-h-screen font-sans pb-12 pt-8 animate-fade-in-up">
        <div class="max-w-[1380px] mx-auto px-4 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
                <div class="flex items-center gap-4">
                    <a href="../index.php" class="p-2 bg-muted-bg rounded-full hover:bg-muted-bg hover:opacity-80 transition-colors"><i data-lucide="arrow-left" class="w-5 h-5"></i></a>
                    <h1 class="text-3xl font-bold text-card-text mb-0" data-translate="admin_dashboard">অ্যাডমিন ড্যাশবোর্ড</h1>
                </div>
                <div class="flex gap-2">
                    <button onclick="openEditor(null)" class="bg-bbcRed text-white px-6 py-3 rounded-lg font-bold shadow-lg flex items-center gap-2 hover:opacity-90 transition-all" id="add-article-btn"><i data-lucide="plus-circle" class="w-5 h-5"></i> <span data-translate="new_article">নতুন সংবাদ</span></button>
                    <button onclick="openSectionEditor(null)" class="bg-bbcRed text-white px-6 py-3 rounded-lg font-bold shadow-lg flex items-center gap-2 hover:opacity-90 transition-all hidden" id="add-section-btn"><i data-lucide="plus-circle" class="w-5 h-5"></i> নতুন সেকশন</button>
                    <button onclick="openCategoryEditor(null)" class="bg-bbcRed text-white px-6 py-3 rounded-lg font-bold shadow-lg flex items-center gap-2 hover:opacity-90 transition-all hidden" id="add-category-btn"><i data-lucide="plus-circle" class="w-5 h-5"></i> নতুন বিভাগ</button>
                </div>
            </div>

            <div class="mb-4 border-b border-border-color">
                <nav class="flex -mb-px gap-6 flex-wrap" aria-label="Tabs">
                    <button onclick="switchTab('articles')" id="tab-articles" class="articles-tab border-bbcRed text-bbcRed whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm cursor-pointer">সংবাদ (Articles)</button>
                    <button onclick="switchTab('sections')" id="tab-sections" class="sections-tab border-transparent text-muted-text hover:text-card-text hover:border-border-color whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm cursor-pointer">সেকশন (Sections)</button>
                    <button onclick="switchTab('categories')" id="tab-categories" class="categories-tab border-transparent text-muted-text hover:text-card-text hover:border-border-color whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm cursor-pointer">বিভাগ (Categories)</button>
                    <a href="?lang=bn" class="ml-auto <?php echo $lang === "bn"
                        ? "border-bbcRed text-bbcRed"
                        : "border-transparent text-muted-text hover:text-card-text hover:border-border-color"; ?> whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">বাংলা</a>
                    <a href="?lang=en" class="<?php echo $lang === "en"
                        ? "border-bbcRed text-bbcRed"
                        : "border-transparent text-muted-text hover:text-card-text hover:border-border-color"; ?> whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">English</a>
                </nav>
            </div>

            <div id="articles-section" class="rounded-3xl shadow-soft overflow-hidden bg-card border border-border-color">
                <table class="w-full text-left border-collapse responsive-table">
                    <thead class="hidden md:table-header-group">
                        <tr class="text-xs uppercase bg-muted text-muted-text">
                            <th class="p-6" data-translate="title">শিরোনাম</th>
                            <th class="p-6" data-translate="category">বিভাগ</th>
                            <th class="p-6 text-right" data-translate="actions">অ্যাকশন</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-color text-card-text">
                        <?php
                        $allArticles = [];
                        if (isset($data["sections"])) {
                            foreach ($data["sections"] as $section) {
                                if (isset($section["articles"])) {
                                    foreach ($section["articles"] as $article) {
                                        $article["sectionId"] = $section["id"];
                                        $allArticles[] = $article;
                                    }
                                }
                            }
                        }
                        foreach ($allArticles as $a): ?>
                        <tr class="flex flex-col md:table-row transition-colors border-b md:border-none last:border-none hover:bg-muted-bg border-border-color">
                            <td class="p-4 md:w-1/2">
                                <div class="flex items-center gap-4">
                                    <img src="<?php echo htmlspecialchars(
                                        $a["image"] ?? "",
                                    ); ?>" onerror="this.src='https://placehold.co/600x400/1a1a1a/FFF?text=BreachTimes'" class="w-16 h-10 rounded object-cover shrink-0">
                                    <div class="font-bold text-sm line-clamp-2 text-card-text"><?php echo htmlspecialchars(
                                        $a["title"],
                                    ); ?></div>
                                </div>
                            </td>
                            <td class="px-4 pb-2 md:py-4 md:w-1/4 flex md:table-cell items-center justify-between">
                                <span class="md:hidden text-xs font-bold uppercase text-muted-text" data-translate="category">বিভাগ</span>
                                <span class="px-2 py-1 rounded text-xs bg-muted text-muted-text"><?php echo htmlspecialchars(
                                    $a["category"] ?? "N/A",
                                ); ?></span>
                            </td>
                            <td class="px-4 pb-4 md:py-4 md:w-1/4 text-right flex md:table-cell items-center justify-between md:justify-end">
                                <span class="md:hidden text-xs font-bold uppercase text-muted-text" data-translate="actions">অ্যাকশন</span>
                                <div class="flex justify-end gap-2">
                                    <button onclick='openEditor(<?php echo htmlspecialchars(
                                        json_encode($a),
                                        ENT_QUOTES,
                                        "UTF-8",
                                    ); ?>)' class="p-2 rounded transition-colors text-blue-600 hover:bg-blue-50"><i data-lucide="edit-2" class="w-4 h-4"></i></button>
                                    <button onclick="deleteArticle('<?php echo $a[
                                        "id"
                                    ]; ?>')" class="p-2 rounded transition-colors text-red-600 hover:bg-red-50"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach;
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- Categories Section -->
            <div id="categories-section" class="hidden rounded-3xl shadow-soft overflow-hidden bg-card border border-border-color">
                <table class="w-full text-left border-collapse responsive-table">
                    <thead class="hidden md:table-header-group">
                        <tr class="text-xs uppercase bg-muted text-muted-text">
                            <th class="p-6">ID</th>
                            <th class="p-6">বাংলা নাম</th>
                            <th class="p-6">English Name</th>
                            <th class="p-6">Color</th>
                            <th class="p-6 text-right">অ্যাকশন</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-color text-card-text" id="categories-table-body">
                    </tbody>
                </table>
            </div>

            <!-- Sections Section -->
            <div id="sections-section" class="hidden rounded-3xl shadow-soft overflow-hidden bg-card border border-border-color">
                <table class="w-full text-left border-collapse responsive-table">
                    <thead class="hidden md:table-header-group">
                        <tr class="text-xs uppercase bg-muted text-muted-text">
                            <th class="p-6">ID</th>
                            <th class="p-6">শিরোনাম</th>
                            <th class="p-6">ধরন</th>
                            <th class="p-6">বিভাগ</th>
                            <th class="p-6 text-right">অ্যাকশন</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-color text-card-text" id="sections-table-body">
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Add/Edit Category Modal -->
    <div id="category-modal" role="dialog" aria-modal="true" aria-labelledby="category-modal-title" class="fixed inset-0 z-[120] bg-black/80 backdrop-blur-sm hidden items-center justify-center p-4 animate-zoom-in">
        <div class="bg-card w-full max-w-2xl rounded-2xl shadow-2xl">
            <div class="sticky top-0 bg-card p-6 border-b border-border-color flex justify-between items-center z-10">
                <h2 class="text-xl font-bold text-card-text" id="category-modal-title">নতুন বিভাগ যোগ করুন</h2>
                <button onclick="closeCategoryEditor()" class="p-2 hover:bg-muted-bg rounded-full transition-colors text-card-text"><i data-lucide="x" class="w-6 h-6"></i></button>
            </div>
            <form id="categoryForm" onsubmit="saveCategory(event)" class="p-6 space-y-6">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-bold mb-2 text-card-text">Category ID</label>
                        <input name="id" id="category-id" required class="w-full p-3 rounded-lg border border-border-color bg-muted-bg text-card-text focus:outline-none focus:border-bbcRed" placeholder="e.g., news, sport">
                    </div>
                    <div>
                        <label class="block text-sm font-bold mb-2 text-card-text">বাংলা নাম</label>
                        <input name="title_bn" id="category-title-bn" required class="w-full p-3 rounded-lg border border-border-color bg-muted-bg text-card-text focus:outline-none focus:border-bbcRed">
                    </div>
                    <div>
                        <label class="block text-sm font-bold mb-2 text-card-text">English Name</label>
                        <input name="title_en" id="category-title-en" required class="w-full p-3 rounded-lg border border-border-color bg-muted-bg text-card-text focus:outline-none focus:border-bbcRed">
                    </div>
                    <div>
                        <label class="block text-sm font-bold mb-2 text-card-text">Color</label>
                        <input type="color" name="color" id="category-color" class="w-full p-3 rounded-lg border border-border-color bg-muted-bg text-card-text focus:outline-none focus:border-bbcRed" value="#b80000">
                    </div>
                </div>
                
                <div class="pt-4 flex justify-end gap-3 border-t border-border-color">
                    <button type="button" onclick="closeCategoryEditor()" class="px-6 py-2.5 rounded-lg font-bold text-muted-text hover:bg-muted-bg transition-colors">বাতিল</button>
                    <button type="submit" class="bg-bbcRed text-white px-8 py-2.5 rounded-lg font-bold shadow-lg hover:opacity-90 transition-all">সংরক্ষণ করুন</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add/Edit Section Modal -->
    <div id="section-modal" role="dialog" aria-modal="true" aria-labelledby="section-modal-title" class="fixed inset-0 z-[120] bg-black/80 backdrop-blur-sm hidden items-center justify-center p-4 animate-zoom-in">
        <div class="bg-card w-full max-w-2xl rounded-2xl shadow-2xl">
            <div class="sticky top-0 bg-card p-6 border-b border-border-color flex justify-between items-center z-10">
                <h2 class="text-xl font-bold text-card-text" id="section-modal-title">নতুন সেকশন যোগ করুন</h2>
                <button onclick="closeSectionEditor()" class="p-2 hover:bg-muted-bg rounded-full transition-colors text-card-text"><i data-lucide="x" class="w-6 h-6"></i></button>
            </div>
            <form id="sectionForm" onsubmit="saveSection(event)" class="p-6 space-y-6">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-bold mb-2 text-card-text">Section ID</label>
                        <input name="id" id="section-id" required class="w-full p-3 rounded-lg border border-border-color bg-muted-bg text-card-text focus:outline-none focus:border-bbcRed" placeholder="e.g., vermont, indiana">
                    </div>
                    <div>
                        <label class="block text-sm font-bold mb-2 text-card-text">বাংলা শিরোনাম</label>
                        <input name="title_bn" id="section-title-bn" required class="w-full p-3 rounded-lg border border-border-color bg-muted-bg text-card-text focus:outline-none focus:border-bbcRed">
                    </div>
                    <div>
                        <label class="block text-sm font-bold mb-2 text-card-text">English Title</label>
                        <input name="title_en" id="section-title-en" required class="w-full p-3 rounded-lg border border-border-color bg-muted-bg text-card-text focus:outline-none focus:border-bbcRed">
                    </div>
                    <div>
                        <label class="block text-sm font-bold mb-2 text-card-text">ধরন</label>
                        <select name="type" id="section-type" required class="w-full p-3 rounded-lg border border-border-color bg-muted-bg text-card-text focus:outline-none focus:border-bbcRed">
                            <option value="">ধরন নির্বাচন করুন</option>
                            <option value="hero-grid">Hero Grid</option>
                            <option value="grid">Grid</option>
                            <option value="list">List</option>
                            <option value="reel">Reel</option>
                            <option value="audio">Audio</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold mb-2 text-card-text">Highlight Color</label>
                        <input type="color" name="highlight_color" id="section-highlight-color" class="w-full p-3 rounded-lg border border-border-color bg-muted-bg text-card-text focus:outline-none focus:border-bbcRed" value="#b80000">
                    </div>
                    <div>
                        <label class="block text-sm font-bold mb-2 text-card-text">বিভাগ (Associated Category)</label>
                        <input name="associated_category" id="section-associated-category" class="w-full p-3 rounded-lg border border-border-color bg-muted-bg text-card-text focus:outline-none focus:border-bbcRed" placeholder="যেমন: News, Sport">
                    </div>
                </div>
                
                <div class="pt-4 flex justify-end gap-3 border-t border-border-color">
                    <button type="button" onclick="closeSectionEditor()" class="px-6 py-2.5 rounded-lg font-bold text-muted-text hover:bg-muted-bg transition-colors">বাতিল</button>
                    <button type="submit" class="bg-bbcRed text-white px-8 py-2.5 rounded-lg font-bold shadow-lg hover:opacity-90 transition-all">সংরক্ষণ করুন</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Editor Modal -->
    <div id="editor-modal" role="dialog" aria-modal="true" aria-labelledby="modal-title" class="fixed inset-0 z-[120] bg-black/80 backdrop-blur-sm hidden items-center justify-center p-4 animate-zoom-in">
        <div class="bg-card w-[95%] max-w-[95%] rounded-2xl shadow-2xl max-h-[90vh] overflow-y-auto no-scrollbar">
            <div class="sticky top-0 bg-card p-6 border-b border-border-color flex justify-between items-center z-10">
                <h2 class="text-xl font-bold text-card-text" id="modal-title" data-translate="add_new_article">নতুন সংবাদ যোগ করুন</h2>
                <button onclick="closeEditor()" class="p-2 hover:bg-muted-bg rounded-full transition-colors text-card-text"><i data-lucide="x" class="w-6 h-6"></i></button>
            </div>
            <form id="articleForm" onsubmit="saveArticle(event)" class="p-6 space-y-6">
                <input type="hidden" name="id" id="article-id">
                <input type="hidden" name="lang" id="article-lang" value="<?php echo $lang; ?>">
                <div class="grid grid-cols-1 gap-6">
                    <div class="space-y-4">
                        <h3 class="font-bold text-bbcRed uppercase text-sm tracking-wider border-b border-border-color pb-2" data-translate="general_info">সাধারণ তথ্য</h3>
                        <div>
                            <label class="block text-sm font-bold mb-2 text-card-text" data-translate="title">শিরোনাম</label>
                            <input name="title" id="article-title" required class="w-full p-3 rounded-lg border border-border-color bg-muted-bg text-card-text focus:outline-none focus:border-bbcRed">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold mb-2 text-card-text" data-translate="category">বিভাগ</label>
                                <select name="category_id" id="article-category" required class="w-full p-3 rounded-lg border border-border-color bg-muted-bg text-card-text focus:outline-none focus:border-bbcRed">
                                    <option value="">বিভাগ নির্বাচন করুন</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-bold mb-2 text-card-text" data-translate="section">সেকশন</label>
                                <select name="sectionId" id="article-sectionId" class="w-full p-3 rounded-lg border border-border-color bg-muted-bg text-card-text focus:outline-none focus:border-bbcRed">
                                    <?php if (isset($data["sections"])) {
                                        foreach ($data["sections"] as $s): ?>
                                            <option value="<?php echo $s[
                                                "id"
                                            ]; ?>"><?php echo htmlspecialchars(
    $s["title"],
); ?></option>
                                        <?php endforeach;
                                    } ?>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-bold mb-2 text-card-text" data-translate="image_url_upload">ছবি (URL অথবা আপলোড)</label>
                            <div class="flex gap-2 mb-2">
                                <input type="file" onchange="handleImageUpload(this)" class="block w-full text-sm text-card-text file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-muted-bg file:text-bbcRed hover:file:bg-border-color">
                            </div>
                            <input name="image" id="article-image" placeholder="অথবা ছবির লিংক দিন..." class="w-full p-3 rounded-lg border border-border-color bg-muted-bg text-card-text focus:outline-none focus:border-bbcRed">
                        </div>
                        <div>
                            <label class="block text-sm font-bold mb-2 text-card-text" data-translate="summary">সারাংশ</label>
                            <textarea name="summary" id="article-summary" rows="3" class="w-full p-3 rounded-lg border border-border-color bg-muted-bg text-card-text focus:outline-none focus:border-bbcRed"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-bold mb-2 text-card-text">ফাঁস হওয়া নথি (JSON)</label>
                            <textarea name="leaked_documents" id="article-leaked-documents" rows="3" class="w-full p-3 rounded-lg border border-border-color bg-muted-bg text-card-text focus:outline-none focus:border-bbcRed font-mono text-xs" placeholder='[{"title": "Doc 1", "type": "PDF"}, ...]'></textarea>
                            <p class="text-xs text-muted-text mt-1">Format: [{"title": "Doc Name", "type": "PDF/DOCX"}]</p>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold mb-2 text-card-text" data-translate="main_content">বিস্তারিত বিবরণ (Main Content)</label>
                    <div id="quill-editor" class="bg-card text-card-text rounded-lg border border-border-color h-64 overflow-y-auto no-scrollbar"></div>
                </div>
                
                <div class="pt-4 flex justify-end gap-3 border-t border-border-color">
                    <button type="button" onclick="closeEditor()" class="px-6 py-2.5 rounded-lg font-bold text-muted-text hover:bg-muted-bg transition-colors" data-translate="cancel">বাতিল</button>
                    <button type="submit" class="bg-bbcRed text-white px-8 py-2.5 rounded-lg font-bold shadow-lg hover:opacity-90 transition-all" data-translate="save">সংরক্ষণ করুন</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const translations = {
            en: {
                admin_dashboard: "Admin Dashboard",
                new_article: "New Article",
                title: "Title",
                category: "Category",
                actions: "Actions",
                add_new_article: "Add New Article",
                edit_article: "Edit Article",
                general_info: "General Information",
                section: "Section",
                image_url_upload: "Image (URL or Upload)",
                summary: "Summary",
                main_content: "Main Content",
                cancel: "Cancel",
                save: "Save",
                delete_confirm: "Are you sure you want to delete this?",
                saved_success: "Saved successfully! Reloading...",
                deleted_success: "Deleted successfully! Reloading...",
                error_occurred: "An error occurred!",
                server_error: "Server error!",
                image_size_error: "Sorry, image size cannot exceed 2MB.",
                image_upload_success: "Image uploaded successfully!",
                write_details: "Write details...",
            },
            bn: {
                admin_dashboard: "অ্যাডমিন ড্যাশবোর্ড",
                new_article: "নতুন সংবাদ",
                title: "শিরোনাম",
                category: "বিভাগ",
                actions: "অ্যাকশন",
                add_new_article: "নতুন সংবাদ যোগ করুন",
                edit_article: "আর্টিকেল এডিট করুন",
                general_info: "সাধারণ তথ্য",
                section: "সেকশন",
                image_url_upload: "ছবি (URL অথবা আপলোড)",
                summary: "সারাংশ",
                main_content: "বিস্তারিত বিবরণ (Main Content)",
                cancel: "বাতিল",
                save: "সংরক্ষণ করুন",
                delete_confirm: "মুছে ফেলবেন?",
                saved_success: "সংরক্ষিত হয়েছে! পেজ রিলোড হচ্ছে...",
                deleted_success: "মুছে ফেলা হয়েছে! পেজ রিলোড হচ্ছে...",
                error_occurred: "সমস্যা হয়েছে!",
                server_error: "সার্ভার এরর!",
                image_size_error: "দুঃখিত, ছবির সাইজ ২ মেগাবাইটের বেশি হতে পারবে না।",
                image_upload_success: "ছবি আপলোড সম্পন্ন!",
                write_details: "বিস্তারিত লিখুন...",
            }
        };

        const currentLang = '<?php echo $lang; ?>';
        const t = translations[currentLang];

        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('[data-translate]').forEach(el => {
                const key = el.getAttribute('data-translate');
                if (t[key]) {
                    el.innerText = t[key];
                }
            });
        });

        const savedTheme = localStorage.getItem("breachtimes-theme");
        const systemDark = window.matchMedia("(prefers-color-scheme: dark)").matches;
        if (savedTheme === "dark" || (!savedTheme && systemDark)) {
            document.documentElement.classList.add("dark");
        } else {
            document.documentElement.classList.remove("dark");
        }

        lucide.createIcons();
        let quillEditor = null;
        let categoriesList = [];

        function showToastMsg(msg) {
            const container = document.getElementById("toast-container");
            const toast = document.createElement("div");
            toast.className = "toast-enter fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-bbcDark/80 dark:bg-muted-bg backdrop-blur text-page-text dark:text-card-text px-6 py-3 rounded-full shadow-lg font-bold flex items-center gap-2 mb-2 text-sm w-auto";
            toast.innerHTML = `<i data-lucide="check-circle" class="w-4 h-4 text-green-400 dark:text-green-600"></i> ${msg}`;
            container.appendChild(toast);
            lucide.createIcons();
            setTimeout(() => toast.remove(), 3000);
        }

        // Load categories on page load
        async function loadCategories() {
            try {
                const res = await fetch('../api/get_categories.php');
                const result = await res.json();
                if (result.success) {
                    categoriesList = result.data;
                    populateCategoryDropdown();
                    renderCategoriesTable();
                }
            } catch (e) {
                console.error('Failed to load categories:', e);
            }
        }

        function populateCategoryDropdown() {
            const select = document.getElementById('article-category');
            const currentValue = select.value;
            select.innerHTML = '<option value="">বিভাগ নির্বাচন করুন</option>';
            categoriesList.forEach(cat => {
                const option = document.createElement('option');
                option.value = cat.id;
                option.textContent = currentLang === 'bn' ? cat.title_bn : cat.title_en;
                select.appendChild(option);
            });
            if (currentValue) select.value = currentValue;
        }

        function renderCategoriesTable() {
            const tbody = document.getElementById('categories-table-body');
            tbody.innerHTML = categoriesList.map(cat => `
                <tr class="flex flex-col md:table-row transition-colors border-b md:border-none last:border-none hover:bg-muted-bg border-border-color">
                    <td class="p-4 md:w-1/5 font-bold text-sm text-card-text">${cat.id}</td>
                    <td class="px-4 pb-2 md:py-4 md:w-1/5 text-card-text">${cat.title_bn}</td>
                    <td class="px-4 pb-2 md:py-4 md:w-1/5 text-card-text">${cat.title_en}</td>
                    <td class="px-4 pb-2 md:py-4 md:w-1/5">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg border-2 border-border-color" style="background-color: ${cat.color};"></div>
                            <span class="text-xs text-card-text">${cat.color}</span>
                        </div>
                    </td>
                    <td class="px-4 pb-4 md:py-4 md:w-1/5 text-right flex md:table-cell items-center justify-between md:justify-end">
                        <div class="flex justify-end gap-2">
                            <button onclick='openCategoryEditor(${JSON.stringify(cat)})' class="p-2 rounded transition-colors text-blue-600 hover:bg-blue-50"><i data-lucide="edit-2" class="w-4 h-4"></i></button>
                            <button onclick="deleteCategory('${cat.id}')" class="p-2 rounded transition-colors text-red-600 hover:bg-red-50"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                        </div>
                    </td>
                </tr>
            `).join('');
            lucide.createIcons();
        }

        function switchTab(tab) {
            const articlesSection = document.getElementById('articles-section');
            const sectionsSection = document.getElementById('sections-section');
            const categoriesSection = document.getElementById('categories-section');
            const tabArticles = document.getElementById('tab-articles');
            const tabSections = document.getElementById('tab-sections');
            const tabCategories = document.getElementById('tab-categories');
            const addArticleBtn = document.getElementById('add-article-btn');
            const addSectionBtn = document.getElementById('add-section-btn');
            const addCategoryBtn = document.getElementById('add-category-btn');

            // Reset all
            articlesSection.classList.add('hidden');
            sectionsSection.classList.add('hidden');
            categoriesSection.classList.add('hidden');
            tabArticles.classList.add('border-transparent', 'text-muted-text');
            tabArticles.classList.remove('border-bbcRed', 'text-bbcRed');
            tabSections.classList.add('border-transparent', 'text-muted-text');
            tabSections.classList.remove('border-bbcRed', 'text-bbcRed');
            tabCategories.classList.add('border-transparent', 'text-muted-text');
            tabCategories.classList.remove('border-bbcRed', 'text-bbcRed');
            addArticleBtn.classList.add('hidden');
            addSectionBtn.classList.add('hidden');
            addCategoryBtn.classList.add('hidden');

            // Show selected tab
            if (tab === 'articles') {
                articlesSection.classList.remove('hidden');
                tabArticles.classList.remove('border-transparent', 'text-muted-text');
                tabArticles.classList.add('border-bbcRed', 'text-bbcRed');
                addArticleBtn.classList.remove('hidden');
            } else if (tab === 'sections') {
                sectionsSection.classList.remove('hidden');
                tabSections.classList.remove('border-transparent', 'text-muted-text');
                tabSections.classList.add('border-bbcRed', 'text-bbcRed');
                addSectionBtn.classList.remove('hidden');
            } else if (tab === 'categories') {
                categoriesSection.classList.remove('hidden');
                tabCategories.classList.remove('border-transparent', 'text-muted-text');
                tabCategories.classList.add('border-bbcRed', 'text-bbcRed');
                addCategoryBtn.classList.remove('hidden');
            }
        }

        function handleImageUpload(input) {
            const file = input.files[0];
            if (!file) return;
            
            if (file.size > 2 * 1024 * 1024) { // 2MB limit
                alert(t.image_size_error);
                input.value = "";
                return;
            }

            const reader = new FileReader();
            reader.onload = function (e) {
                document.getElementById('article-image').value = e.target.result;
                showToastMsg(t.image_upload_success);
            };
            reader.readAsDataURL(file);
        }

        function openEditor(article) {
            const modal = document.getElementById('editor-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            
            document.getElementById('modal-title').innerText = article ? t.edit_article : t.add_new_article;
            document.getElementById('article-id').value = article ? article.id : '';
            document.getElementById('article-title').value = article ? article.title : '';
            // Update to use category_id if article has it, otherwise use category as fallback
            const categoryId = article && article.category_id ? article.category_id : (article ? article.category : '');
            document.getElementById('article-category').value = categoryId;
            document.getElementById('article-sectionId').value = article ? article.sectionId : '';
            document.getElementById('article-image').value = article ? article.image : '';
            document.getElementById('article-summary').value = article ? article.summary : '';
            document.getElementById('article-leaked-documents').value = article && article.leaked_documents ? article.leaked_documents : '';
            document.getElementById('article-lang').value = currentLang;
            
            if (!quillEditor) {
                quillEditor = new Quill("#quill-editor", {
                    theme: "snow",
                    placeholder: t.write_details,
                    modules: {
                        toolbar: [
                            ["bold", "italic", "underline", "strike"],
                            [{ header: [1, 2, 3, false] }],
                            [{ list: "ordered" }, { list: "bullet" }],
                            ["link", "image", "video"],
                            ["clean"],
                        ],
                    },
                });
            }
            
            if (article && article.content) {
                quillEditor.clipboard.dangerouslyPasteHTML(0, article.content);
            } else {
                quillEditor.setText('');
            }
            // Focus the title input for accessibility
            const titleInput = document.getElementById('article-title');
            if (titleInput) titleInput.focus();
        }

        function closeEditor() {
            const modal = document.getElementById('editor-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        // Section management functions
        let sectionsData = [];

        async function loadSections() {
            try {
                const res = await fetch('../api/get_sections.php?lang=' + currentLang);
                const result = await res.json();
                if (result.success) {
                    sectionsData = result.data;
                    renderSectionsTable();
                }
            } catch (e) {
                console.error('Failed to load sections:', e);
            }
        }

        function renderSectionsTable() {
            const tbody = document.getElementById('sections-table-body');
            tbody.innerHTML = sectionsData.map(sec => `
                <tr class="flex flex-col md:table-row transition-colors border-b md:border-none last:border-none hover:bg-muted-bg border-border-color">
                    <td class="p-4 md:w-1/6 font-bold text-sm text-card-text">${sec.id}</td>
                    <td class="px-4 pb-2 md:py-4 md:w-1/4 text-card-text">${sec.title}</td>
                    <td class="px-4 pb-2 md:py-4 md:w-1/4 text-xs bg-muted rounded px-2 py-1 w-fit text-muted-text">${sec.type}</td>
                    <td class="px-4 pb-2 md:py-4 md:w-1/4 text-card-text text-sm">${sec.associated_category || '-'}</td>
                    <td class="px-4 pb-4 md:py-4 md:w-1/6 text-right flex md:table-cell items-center justify-between md:justify-end">
                        <div class="flex justify-end gap-2">
                            <button onclick='openSectionEditor(${JSON.stringify(sec)})' class="p-2 rounded transition-colors text-blue-600 hover:bg-blue-50"><i data-lucide="edit-2" class="w-4 h-4"></i></button>
                            <button onclick="deleteSection('${sec.id}')" class="p-2 rounded transition-colors text-red-600 hover:bg-red-50"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                        </div>
                    </td>
                </tr>
            `).join('');
            lucide.createIcons();
        }

        function openSectionEditor(section) {
            const modal = document.getElementById('section-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            
            document.getElementById('section-modal-title').innerText = section ? 'সেকশন এডিট করুন' : 'নতুন সেকশন যোগ করুন';
            document.getElementById('section-id').value = section ? section.id : '';
            document.getElementById('section-id').disabled = section ? true : false;
            document.getElementById('section-title-bn').value = section ? section.title_bn || section.title : '';
            document.getElementById('section-title-en').value = section ? section.title_en || section.title : '';
            document.getElementById('section-type').value = section ? section.type : '';
            document.getElementById('section-highlight-color').value = section ? section.highlight_color : '#b80000';
            document.getElementById('section-associated-category').value = section ? section.associated_category : '';
            const idEl = document.getElementById('section-id');
            if (idEl) idEl.focus();
        }

        function closeSectionEditor() {
            const modal = document.getElementById('section-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.getElementById('sectionForm').reset();
        }

        async function saveSection(event) {
            event.preventDefault();
            const id = document.getElementById('section-id').value;
            const title_bn = document.getElementById('section-title-bn').value;
            const title_en = document.getElementById('section-title-en').value;
            const type = document.getElementById('section-type').value;
            const highlight_color = document.getElementById('section-highlight-color').value;
            const associated_category = document.getElementById('section-associated-category').value;
            const lang = currentLang;

            try {
                const res = await fetch('../api/save_section.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id, title_bn, title_en, type, highlight_color, associated_category, lang })
                });
                const result = await res.json();
                if (result.success) {
                    showToastMsg(result.message || 'সেকশন সংরক্ষিত হয়েছে');
                    closeSectionEditor();
                    loadSections();
                } else {
                    showToastMsg(result.message || 'ত্রুটি হয়েছে');
                }
            } catch (e) {
                console.error(e);
                showToastMsg('সার্ভার ত্রুটি');
            }
        }

        async function deleteSection(id) {
            if (!confirm('এই সেকশন মুছে ফেলবেন?')) return;
            
            try {
                const res = await fetch('../api/delete_section.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id, lang: currentLang })
                });
                const result = await res.json();
                if (result.success) {
                    showToastMsg(result.message || 'সেকশন মুছে ফেলা হয়েছে');
                    loadSections();
                } else {
                    showToastMsg(result.message || 'ত্রুটি হয়েছে');
                }
            } catch (e) {
                console.error(e);
                showToastMsg('সার্ভার ত্রুটি');
            }
        }

        async function saveArticle(event) {
            event.preventDefault();
            const formData = new FormData(event.target);
            if (quillEditor) {
                formData.append('content', quillEditor.root.innerHTML);
            }

            try {
                const res = await fetch('../api/save_article.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await res.json();
                if (result.success) {
                    showToastMsg(t.saved_success);
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToastMsg(t.error_occurred);
                }
            } catch (e) {
                console.error(e);
                showToastMsg(t.server_error);
            }
        }

        async function deleteArticle(id) {
            if (!confirm(t.delete_confirm)) return;
            const payload = {
                id: id,
                lang: currentLang
            };
            try {
                const res = await fetch('../api/delete_article.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const result = await res.json();
                if (result.success) {
                    showToastMsg(t.deleted_success);
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToastMsg(t.error_occurred);
                }
            } catch (e) {
                console.error(e);
                showToastMsg(t.server_error);
            }
        }

        // Category management functions
        function openCategoryEditor(category) {
            const modal = document.getElementById('category-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            
            document.getElementById('category-modal-title').innerText = category ? 'বিভাগ এডিট করুন' : 'নতুন বিভাগ যোগ করুন';
            document.getElementById('category-id').value = category ? category.id : '';
            document.getElementById('category-id').disabled = category ? true : false;
            document.getElementById('category-title-bn').value = category ? category.title_bn : '';
            document.getElementById('category-title-en').value = category ? category.title_en : '';
            document.getElementById('category-color').value = category ? category.color : '#b80000';
            const idEl = document.getElementById('category-id');
            if (idEl) idEl.focus();
        }

        function closeCategoryEditor() {
            const modal = document.getElementById('category-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.getElementById('categoryForm').reset();
        }

        async function saveCategory(event) {
            event.preventDefault();
            const id = document.getElementById('category-id').value;
            const title_bn = document.getElementById('category-title-bn').value;
            const title_en = document.getElementById('category-title-en').value;
            const color = document.getElementById('category-color').value;

            try {
                const res = await fetch('../api/save_category.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id, title_bn, title_en, color })
                });
                const result = await res.json();
                if (result.success) {
                    showToastMsg(result.message);
                    closeCategoryEditor();
                    loadCategories();
                } else {
                    showToastMsg(result.message || 'ত্রুটি হয়েছে');
                }
            } catch (e) {
                console.error(e);
                showToastMsg('সার্ভার ত্রুটি');
            }
        }

        async function deleteCategory(id) {
            if (!confirm('এই বিভাগ মুছে ফেলবেন? এটি সম্পর্কিত আর্টিকেলগুলিকে প্রভাবিত করতে পারে।')) return;
            
            try {
                const res = await fetch('../api/delete_category.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id })
                });
                const result = await res.json();
                if (result.success) {
                    showToastMsg(result.message);
                    loadCategories();
                } else {
                    showToastMsg(result.message || 'ত্রুটি হয়েছে');
                }
            } catch (e) {
                console.error(e);
                showToastMsg('সার্ভার ত্রুটি');
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', () => {
            loadCategories();
            loadSections();
        });
    </script>
</body>
</html>