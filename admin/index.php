<?php
session_start();
require_once "../includes/functions.php";

if (!isset($_SESSION["user_role"]) || $_SESSION["user_role"] !== "admin") {
    header("Location: ../login.php");
    exit();
}

$bbcData = get_bbc_data();
?>
<!doctype html>
<html lang="bn">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>অ্যাডমিন ড্যাশবোর্ড | BreachTimes</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <style>
    @import url('https://banglawebfonts.pages.dev/css/hind-siliguri.css');
    </style> 
    <link href="https://banglawebfonts.pages.dev/css/hind-siliguri.css" rel="stylesheet">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <style type="text/tailwindcss">
        @theme {
        --color-bbcRed: #b80000;
        --color-bbcDark: #1a1a1a;
        --color-page: var(--bg-page);
        --color-page-text: var(--text-page);
        --color-card: var(--bg-card);
        --color-card-elevated: var(--bg-card-elevated);
        --color-card-text: var(--text-card);
        --color-border-color: var(--border-color);
        --color-muted-bg: var(--bg-muted);
        --color-muted-text: var(--text-muted);
      }
      @layer base {
        :root {
          --bg-page: #f6f6f6;
          --text-page: #1a1a1a;
          --bg-card: #ffffff;
          --bg-card-elevated: #ffffff;
          --text-card: #1a1a1a;
          --border-color: #e5e7eb;
          --bg-muted: #f3f4f6;
          --text-muted: #6b7280;
        }
        :root.dark {
          --bg-page: #0a0a0a;
          --text-page: #ededed;
          --bg-card: #1a1a1a;
          --bg-card-elevated: #1e1e1e;
          --text-card: #ffffff;
          --border-color: #1f2937;
          --bg-muted: #1f2937;
          --text-muted: #9ca3af;
        }
      }
      body { font-family: "Hind Siliguri", sans-serif; }
      .responsive-table td { vertical-align: middle; }

      /* Quill Editor Theme Support */
      .ql-toolbar {
        background: var(--bg-muted);
        border-color: var(--border-color);
        border-top-left-radius: 0.5rem;
        border-top-right-radius: 0.5rem;
      }
      .ql-container {
        background: var(--bg-card);
        border-color: var(--border-color);
        border-bottom-left-radius: 0.5rem;
        border-bottom-right-radius: 0.5rem;
        font-family: "Hind Siliguri", sans-serif;
      }
      .ql-editor {
        min-height: 200px;
        font-size: 1rem;
        color: var(--text-card);
      }
      .ql-toolbar .ql-stroke {
        stroke: var(--text-muted);
      }
      .ql-toolbar .ql-fill {
        fill: var(--text-muted);
      }
      .ql-editor.ql-blank::before {
        color: var(--text-muted);
      }
    </style>
</head>
<body class="bg-page text-card-text transition-colors duration-500">
    <div id="toast-container" class="fixed bottom-8 left-1/2 transform -translate-x-1/2 z-[110] pointer-events-none w-full max-w-sm flex flex-col items-center gap-2"></div>

    <main class="bg-page min-h-screen font-sans pb-12 pt-8">
        <div class="max-w-[1380px] mx-auto px-4 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
                <div class="flex items-center gap-4">
                    <a href="../index.php" class="p-2 bg-muted-bg rounded-full hover:bg-muted-bg hover:opacity-80 transition-colors"><i data-lucide="arrow-left" class="w-5 h-5"></i></a>
                    <h1 class="text-3xl font-bold text-card-text mb-0">অ্যাডমিন ড্যাশবোর্ড</h1>
                </div>
                <div class="flex gap-2">
                    <button onclick="openEditor(null)" class="bg-bbcRed text-white px-6 py-3 rounded-lg font-bold shadow-lg flex items-center gap-2 hover:opacity-90 transition-all"><i data-lucide="plus-circle" class="w-5 h-5"></i> নতুন সংবাদ</button>
                </div>
            </div>

            <div class="bg-card rounded-3xl shadow-soft border border-border-color overflow-hidden">
                <table class="w-full text-left border-collapse responsive-table">
                    <thead class="hidden md:table-header-group">
                        <tr class="bg-muted-bg text-muted-text text-xs uppercase">
                            <th class="p-6">শিরোনাম</th>
                            <th class="p-6">বিভাগ</th>
                            <th class="p-6 text-right">অ্যাকশন</th>
                        </tr>
                    </thead>
                    <tbody class="text-card-text divide-y divide-border-color">
                        <?php
                        $allArticles = [];
                        foreach ($bbcData["sections"] as $section) {
                            foreach ($section["articles"] as $article) {
                                $article["sectionId"] = $section["id"];
                                $allArticles[] = $article;
                            }
                        }
                        foreach ($allArticles as $a): ?>
                        <tr class="flex flex-col md:table-row hover:bg-muted-bg transition-colors border-b md:border-none border-border-color last:border-none">
                            <td class="p-4 md:w-1/2">
                                <div class="flex items-center gap-4">
                                    <img src="<?php echo htmlspecialchars(
                                        $a["image"] ?? "",
                                    ); ?>" onerror="this.src='https://placehold.co/600x400/1a1a1a/FFF?text=BreachTimes'" class="w-16 h-10 rounded object-cover shrink-0">
                                    <div class="font-bold text-sm line-clamp-2"><?php echo htmlspecialchars(
                                        $a["title"],
                                    ); ?></div>
                                </div>
                            </td>
                            <td class="px-4 pb-2 md:py-4 md:w-1/4 flex md:table-cell items-center justify-between">
                                <span class="md:hidden text-xs font-bold text-muted-text uppercase">বিভাগ</span>
                                <span class="bg-muted-bg px-2 py-1 rounded text-xs"><?php echo htmlspecialchars(
                                    $a["category"],
                                ); ?></span>
                            </td>
                            <td class="px-4 pb-4 md:py-4 md:w-1/4 text-right flex md:table-cell items-center justify-between md:justify-end">
                                <span class="md:hidden text-xs font-bold text-muted-text uppercase">অ্যাকশন</span>
                                <div class="flex justify-end gap-2">
                                    <button onclick='openEditor(<?php echo htmlspecialchars(
                                        json_encode($a),
                                        ENT_QUOTES,
                                        "UTF-8",
                                    ); ?>)' class="p-2 text-blue-600 hover:bg-blue-100 rounded transition-colors"><i data-lucide="edit-2" class="w-4 h-4"></i></button>
                                    <button onclick="deleteArticle('<?php echo $a[
                                        "id"
                                    ]; ?>')" class="p-2 text-red-600 hover:bg-red-100 rounded transition-colors"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach;
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Editor Modal -->
    <div id="editor-modal" class="fixed inset-0 z-[120] bg-black/80 backdrop-blur-sm hidden items-center justify-center p-4">
        <div class="bg-card w-full max-w-4xl rounded-2xl shadow-2xl max-h-[90vh] overflow-y-auto">
            <div class="sticky top-0 bg-card p-6 border-b border-border-color flex justify-between items-center z-10">
                <h2 class="text-xl font-bold text-card-text" id="modal-title">নতুন সংবাদ যোগ করুন</h2>
                <button onclick="closeEditor()" class="p-2 hover:bg-muted-bg rounded-full transition-colors text-card-text"><i data-lucide="x" class="w-6 h-6"></i></button>
            </div>
            <form id="articleForm" onsubmit="saveArticle(event)" class="p-6 space-y-6">
                <input type="hidden" name="id" id="article-id">
                <div class="grid grid-cols-1 gap-6">
                    <div class="space-y-4">
                        <h3 class="font-bold text-bbcRed uppercase text-sm tracking-wider border-b pb-2">সাধারণ তথ্য</h3>
                        <div>
                            <label class="block text-sm font-bold mb-2 text-card-text">শিরোনাম</label>
                            <input name="title" id="article-title" required class="w-full p-3 rounded-lg border border-border-color bg-muted-bg text-card-text focus:outline-none focus:border-bbcRed">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold mb-2 text-card-text">বিভাগ</label>
                                <input name="category" id="article-category" required class="w-full p-3 rounded-lg border border-border-color bg-muted-bg text-card-text focus:outline-none focus:border-bbcRed">
                            </div>
                            <div>
                                <label class="block text-sm font-bold mb-2 text-card-text">সেকশন</label>
                                <select name="sectionId" id="article-sectionId" class="w-full p-3 rounded-lg border border-border-color bg-muted-bg text-card-text focus:outline-none focus:border-bbcRed">
                                    <?php foreach (
                                        $bbcData["sections"]
                                        as $s
                                    ): ?>
                                        <option value="<?php echo $s[
                                            "id"
                                        ]; ?>"><?php echo htmlspecialchars(
    $s["title"],
); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-bold mb-2 text-card-text">ছবি (URL অথবা আপলোড)</label>
                            <div class="flex gap-2 mb-2">
                                <input type="file" onchange="handleImageUpload(this)" class="block w-full text-sm text-card-text file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-muted-bg file:text-bbcRed hover:file:bg-gray-200">
                            </div>
                            <input name="image" id="article-image" placeholder="অথবা ছবির লিংক দিন..." class="w-full p-3 rounded-lg border border-border-color bg-muted-bg text-card-text focus:outline-none focus:border-bbcRed">
                        </div>
                        <div>
                            <label class="block text-sm font-bold mb-2 text-card-text">সারাংশ</label>
                            <textarea name="summary" id="article-summary" rows="3" class="w-full p-3 rounded-lg border border-border-color bg-muted-bg text-card-text focus:outline-none focus:border-bbcRed"></textarea>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold mb-2 text-card-text">বিস্তারিত বিবরণ (Main Content)</label>
                    <div id="quill-editor" class="bg-card text-card-text rounded-lg border border-border-color h-64 overflow-y-auto"></div>
                </div>
                
                <div class="pt-4 flex justify-end gap-3 border-t border-border-color">
                    <button type="button" onclick="closeEditor()" class="px-6 py-2.5 rounded-lg font-bold text-muted-text hover:bg-muted-bg transition-colors">বাতিল</button>
                    <button type="submit" class="bg-bbcRed text-white px-8 py-2.5 rounded-lg font-bold shadow-lg hover:opacity-90 transition-all">সংরক্ষণ করুন</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const savedTheme = localStorage.getItem("breachtimes-theme");
        const systemDark = window.matchMedia("(prefers-color-scheme: dark)").matches;
        if (savedTheme === "dark" || (!savedTheme && systemDark)) {
            document.documentElement.classList.add("dark");
        } else {
            document.documentElement.classList.remove("dark");
        }

        lucide.createIcons();
        let quillEditor = null;

        function showToastMsg(msg) {
            const container = document.getElementById("toast-container");
            const toast = document.createElement("div");
            toast.className = "bg-black/80 dark:bg-white/90 backdrop-blur text-white dark:text-black px-6 py-3 rounded-full shadow-lg font-bold flex items-center gap-2 mb-2 text-sm w-auto";
            toast.innerHTML = `<i data-lucide="check-circle" class="w-4 h-4 text-green-400 dark:text-green-600"></i> ${msg}`;
            container.appendChild(toast);
            lucide.createIcons();
            setTimeout(() => toast.remove(), 3000);
        }

        function handleImageUpload(input) {
            const file = input.files[0];
            if (!file) return;
            
            if (file.size > 2 * 1024 * 1024) { // 2MB limit
                alert("দুঃখিত, ছবির সাইজ ২ মেগাবাইটের বেশি হতে পারবে না।");
                input.value = "";
                return;
            }

            const reader = new FileReader();
            reader.onload = function (e) {
                document.getElementById('article-image').value = e.target.result;
                showToastMsg("ছবি আপলোড সম্পন্ন!");
            };
            reader.readAsDataURL(file);
        }

        function openEditor(article) {
            const modal = document.getElementById('editor-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            
            document.getElementById('modal-title').innerText = article ? "আর্টিকেল এডিট করুন" : "নতুন সংবাদ যোগ করুন";
            document.getElementById('article-id').value = article ? article.id : '';
            document.getElementById('article-title').value = article ? article.title : '';
            document.getElementById('article-category').value = article ? article.category : 'খবর';
            document.getElementById('article-sectionId').value = article ? article.sectionId : '';
            document.getElementById('article-image').value = article ? article.image : '';
            document.getElementById('article-summary').value = article ? article.summary : '';
            
            if (!quillEditor) {
                quillEditor = new Quill("#quill-editor", {
                    theme: "snow",
                    placeholder: "বিস্তারিত লিখুন...",
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
        }

        function closeEditor() {
            const modal = document.getElementById('editor-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
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
                    showToastMsg("সংরক্ষিত হয়েছে! পেজ রিলোড হচ্ছে...");
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToastMsg("সমস্যা হয়েছে!");
                }
            } catch (e) {
                console.error(e);
                showToastMsg("সার্ভার এরর!");
            }
        }

        async function deleteArticle(id) {
            if (!confirm("মুছে ফেলবেন?")) return;
            try {
                const res = await fetch('../api/delete_article.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id })
                });
                const result = await res.json();
                if (result.success) {
                    showToastMsg("মুছে ফেলা হয়েছে! পেজ রিলোড হচ্ছে...");
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToastMsg("সমস্যা হয়েছে!");
                }
            } catch (e) {
                console.error(e);
                showToastMsg("সার্ভার এরর!");
            }
        }
    </script>
</body>
</html>