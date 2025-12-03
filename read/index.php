<?php
session_start();
require_once "../get_data.php";

$user = isset($_SESSION["user_email"]) ? $_SESSION["user_email"] : null;
$isAdmin = isset($_SESSION["user_role"]) && $_SESSION["user_role"] === "admin";
$lang = isset($_GET["lang"]) && $_GET["lang"] === "en" ? "en" : "bn";
$articleId = isset($_GET["id"]) ? $_GET["id"] : null;

if (!$articleId) {
    header("Location: ../index.php");
    exit();
}

// Fetch all data to find the article
$data = get_data($lang);
$article = null;

// Search for article by ID
if (isset($data["sections"])) {
    foreach ($data["sections"] as $section) {
        if (isset($section["articles"])) {
            foreach ($section["articles"] as $a) {
                if ($a["id"] === $articleId) {
                    $article = $a;
                    $article["sectionId"] = $section["id"];
                    break 2;
                }
            }
        }
    }
}

if (!$article) {
    header("Location: ../index.php");
    exit();
}
?>
<!doctype html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo htmlspecialchars($article["title"]); ?> | BreachTimes</title>
    
    <!-- Open Graph / Social Sharing Meta Tags -->
    <meta property="og:type" content="article" />
    <meta property="og:title" content="<?php echo htmlspecialchars($article["title"]); ?>" />
    <meta property="og:description" content="<?php echo htmlspecialchars($article["summary"] ?? ""); ?>" />
    <meta property="og:image" content="<?php echo htmlspecialchars($article["image"] ?? ""); ?>" />
    <meta property="og:url" content="<?php echo "https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]; ?>" />
    <meta property="og:site_name" content="BreachTimes" />
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="<?php echo htmlspecialchars($article["title"]); ?>" />
    <meta name="twitter:description" content="<?php echo htmlspecialchars($article["summary"] ?? ""); ?>" />
    <meta name="twitter:image" content="<?php echo htmlspecialchars($article["image"] ?? ""); ?>" />
    
    <!-- Article Meta Tags -->
    <meta name="description" content="<?php echo htmlspecialchars($article["summary"] ?? ""); ?>" />
    <meta name="author" content="BreachTimes" />
    <meta name="publish_date" content="<?php echo htmlspecialchars($article["timestamp"] ?? date("Y-m-d")); ?>" />
    <meta name="category" content="<?php echo htmlspecialchars($article["category"] ?? "News"); ?>" />
    
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&display=swap');
    </style>
    
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <style type="text/tailwindcss">
        <?php include "../tailwind.config.css"; ?>
    </style>
</head>
<body class="bg-page text-page-text font-sans transition-colors duration-500 antialiased selection:bg-bbcRed selection:text-white">
    <div id="progress-bar" class="fixed top-0 left-0 h-1 bg-bbcRed z-[100] shadow-[0_0_10px_#B80000]" style="width: 0%" aria-hidden="true"></div>

    <header role="banner" class="border-b border-border-color sticky bg-white/90 dark:bg-[#121212]/90 backdrop-blur-md z-50 transition-colors duration-300 shadow-sm">
        <div class="container mx-auto px-4 lg:px-8 max-w-[1380px]">
            <div class="h-[70px] flex items-center justify-between">
                <div class="flex items-center gap-6">
                    <a href="../index.php" class="p-2.5 hover:bg-muted-bg rounded-full text-gray-700 dark:text-gray-200 transition-colors">
                        <i data-lucide="arrow-left" class="w-6 h-6"></i>
                    </a>
                    <a href="../index.php" class="block text-black dark:text-white transition-transform hover:scale-[1.02] active:scale-95 duration-300">
                        <div class="flex items-center select-none gap-2 group">
                            <span class="bg-bbcRed text-white px-2.5 py-0.5 font-bold text-xl rounded shadow-md group-hover:bg-[#d40000] transition-colors duration-300">B</span>
                            <span class="font-bold text-2xl tracking-tight leading-none text-gray-900 dark:text-white group-hover:text-gray-700 dark:group-hover:text-gray-300 transition-colors">
                                <span class="text-bbcRed">Breach</span>Times
                            </span>
                        </div>
                    </a>
                </div>
                <div class="flex items-center gap-2 md:gap-4">
                    <button onclick="toggleTheme()" class="p-2.5 rounded-full hover:bg-muted-bg text-gray-600 dark:text-yellow-400 transition-all active:scale-90">
                        <i data-lucide="sun" class="w-5 h-5"></i>
                    </button>
                    <button onclick="toggleLanguage()" class="p-2.5 rounded-full hover:bg-muted-bg text-gray-600 dark:text-green-400 transition-all active:scale-90">
                        <span class="text-sm font-bold"><?php echo $lang === "bn" ? "EN" : "BN"; ?></span>
                    </button>
                    <?php if ($user): ?>
                        <button onclick="handleLogout()" class="text-sm font-bold px-4 py-2 hover:bg-red-50 dark:hover:bg-red-900/20 text-bbcRed rounded-full transition-all flex items-center gap-2">
                            <div class="w-4 h-4 bg-bbcRed rounded-full text-white flex items-center justify-center text-[10px]"><?php echo strtoupper($user[0]); ?></div> সাইন আউট
                        </button>
                    <?php else: ?>
                        <a href="../login/" class="text-sm font-bold px-5 py-2.5 bg-bbcDark dark:bg-white text-white dark:text-black rounded-full hover:shadow-lg hover:-translate-y-0.5 transition-all">সাইন ইন</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <main role="main" class="bg-page min-h-screen font-sans animate-fade-in pb-12">
        <div class="max-w-[1280px] mx-auto px-4 py-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
                <div class="lg:col-span-8">
                    <article class="bg-card p-6 md:p-10 rounded-2xl shadow-soft border border-border-color">
                        <!-- Article Header -->
                        <div class="mb-6">
                            <span class="bg-bbcRed text-white text-xs font-bold px-3 py-1 rounded-full mb-3 inline-block"><?php echo htmlspecialchars($article["category"] ?? "News"); ?></span>
                            <h1 class="text-3xl md:text-5xl font-bold leading-tight mb-4 text-card-text"><?php echo htmlspecialchars($article["title"]); ?></h1>
                            
                            <div class="flex flex-wrap items-center gap-4 text-sm text-muted-text font-medium">
                                <span class="flex items-center gap-1.5"><i data-lucide="clock" class="w-4 h-4"></i> <?php echo htmlspecialchars($article["timestamp"] ?? "Just now"); ?></span>
                                <?php if (isset($article["readTime"])): ?>
                                    <span class="flex items-center gap-1.5"><i data-lucide="file-text" class="w-4 h-4"></i> <?php echo htmlspecialchars($article["readTime"]); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Featured Image -->
                        <div class="mb-10 relative aspect-video bg-muted-bg rounded-2xl overflow-hidden shadow-lg">
                            <img src="<?php echo htmlspecialchars($article["image"] ?? ""); ?>" onerror="this.src='https://placehold.co/1200x675/1a1a1a/FFF?text=BreachTimes'" class="w-full h-full object-cover">
                        </div>

                        <!-- Article Controls -->
                        <div class="flex items-center justify-between border-y border-border-color py-4 mb-8">
                            <div class="flex items-center gap-1 bg-muted-bg rounded-lg p-1">
                                <button onclick="setFontSize('sm')" class="w-8 h-8 flex items-center justify-center hover:bg-card rounded transition-colors text-xs font-bold text-card-text">A</button>
                                <button onclick="setFontSize('md')" class="w-8 h-8 flex items-center justify-center hover:bg-card rounded transition-colors text-sm font-bold text-card-text">A</button>
                                <button onclick="setFontSize('lg')" class="w-8 h-8 flex items-center justify-center hover:bg-card rounded transition-colors text-lg font-bold text-card-text">A</button>
                            </div>
                            <div class="flex gap-3">
                                <button aria-label="Share article" onclick="handleShare()" class="flex items-center gap-2 px-4 py-2 rounded-full bg-muted-bg hover:bg-bbcRed hover:text-white transition-all text-sm font-bold text-card-text">
                                    <i data-lucide="share-2" class="w-4 h-4"></i> শেয়ার
                                </button>
                                <button aria-label="Toggle bookmark" onclick="toggleBookmark('<?php echo $article["id"]; ?>')" class="p-2.5 rounded-full bg-muted-bg hover:bg-bbcRed hover:text-white text-black dark:text-white transition-all shadow-sm flex items-center justify-center group">
                                    <i data-lucide="bookmark" class="w-5 h-5"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Article Content -->
                        <div class="prose max-w-none font-size-md space-y-8 text-card-text transition-all duration-300">
                            <?php echo $article["content"] ?? "<p>" . htmlspecialchars($article["summary"] ?? "") . "</p>"; ?>
                        </div>
                    </article>

                    <!-- Comments Section -->
                    <div class="mt-8 bg-card p-6 md:p-10 rounded-2xl shadow-soft border border-border-color">
                        <h3 class="text-2xl font-bold mb-6 text-card-text flex items-center gap-2">
                            <i data-lucide="message-circle" class="w-6 h-6 text-bbcRed"></i> মন্তব্য
                        </h3>

                        <div class="mb-8">
                            <div class="relative">
                                <textarea id="comment-input" placeholder="আপনার মতামত জানান..." class="w-full p-4 rounded-xl border border-border-color bg-muted-bg text-card-text focus:ring-2 focus:ring-bbcRed/20 focus:border-bbcRed outline-none transition-all resize-none shadow-inner" rows="3"></textarea>
                            </div>
                            <div class="flex justify-end mt-3">
                                <button onclick="postComment('<?php echo $article["id"]; ?>')" class="bg-bbcDark dark:bg-white text-white dark:text-black px-6 py-2.5 rounded-full font-bold hover:shadow-lg hover:-translate-y-0.5 transition-all text-sm">মন্তব্য প্রকাশ করুন</button>
                            </div>
                        </div>

                        <div class="space-y-6">
                            <?php if ($article["comments"] && count($article["comments"]) > 0): ?>
                                <?php foreach ($article["comments"] as $comment): ?>
                                    <div class="bg-muted-bg p-4 rounded-xl">
                                        <div class="flex items-center gap-3 mb-2">
                                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-bbcRed to-orange-500 flex items-center justify-center font-bold text-white text-sm shadow-md"><?php echo strtoupper($comment["user"][0]); ?></div>
                                            <div>
                                                <span class="font-bold text-sm text-card-text block"><?php echo htmlspecialchars($comment["user"]); ?></span>
                                                <span class="text-xs text-muted-text"><?php echo htmlspecialchars($comment["time"]); ?></span>
                                            </div>
                                        </div>
                                        <p class="text-sm text-card-text ml-12 leading-relaxed bg-card p-3 rounded-lg rounded-tl-none border border-border-color"><?php echo htmlspecialchars($comment["text"]); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center py-8 text-muted-text">এখনও কোনো মন্তব্য নেই।</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="lg:col-span-4">
                    <!-- Quick Info Card -->
                    <div class="bg-card p-6 rounded-2xl shadow-soft border border-border-color mb-8 sticky top-24">
                        <h4 class="text-lg font-bold mb-4 text-card-text">নিবন্ধ তথ্য</h4>
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between items-center pb-3 border-b border-border-color">
                                <span class="text-muted-text">বিভাগ:</span>
                                <span class="font-bold text-card-text"><?php echo htmlspecialchars($article["category"] ?? "N/A"); ?></span>
                            </div>
                            <div class="flex justify-between items-center pb-3 border-b border-border-color">
                                <span class="text-muted-text">প্রকাশনা:</span>
                                <span class="font-bold text-card-text"><?php echo htmlspecialchars($article["timestamp"] ?? "N/A"); ?></span>
                            </div>
                            <?php if (isset($article["readTime"])): ?>
                                <div class="flex justify-between items-center">
                                    <span class="text-muted-text">পড়ার সময়:</span>
                                    <span class="font-bold text-card-text"><?php echo htmlspecialchars($article["readTime"]); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        const articleId = '<?php echo htmlspecialchars($articleId); ?>';
        const lang = '<?php echo $lang; ?>';
        let bookmarks = JSON.parse(localStorage.getItem("breachtimes-bookmarks") || "[]");
        let fontSize = "md";

        const savedTheme = localStorage.getItem("breachtimes-theme");
        const systemDark = window.matchMedia("(prefers-color-scheme: dark)").matches;
        if (savedTheme === "dark" || (!savedTheme && systemDark)) {
            document.documentElement.classList.add("dark");
        }

        lucide.createIcons();

        // Progress bar on scroll
        window.addEventListener("scroll", () => {
            const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
            const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
            const scrolled = (winScroll / height) * 100;
            document.getElementById("progress-bar").style.width = scrolled + "%";
        });

        function setFontSize(size) {
            fontSize = size;
            const proseEl = document.querySelector(".prose");
            proseEl.classList.remove("font-size-sm", "font-size-md", "font-size-lg");
            proseEl.classList.add(`font-size-${size}`);
        }

        function handleShare() {
            if (navigator.share) {
                navigator.share({
                    title: document.title,
                    url: window.location.href,
                }).catch(console.error);
            } else {
                const tempInput = document.createElement("input");
                tempInput.value = window.location.href;
                document.body.appendChild(tempInput);
                tempInput.select();
                document.execCommand("copy");
                document.body.removeChild(tempInput);
                alert("লিঙ্ক ক্লিপবোর্ডে কপি হয়েছে!");
            }
        }

        function toggleBookmark(id) {
            const index = bookmarks.indexOf(id);
            if (index > -1) {
                bookmarks.splice(index, 1);
                alert("সংরক্ষণ সরানো হয়েছে");
            } else {
                bookmarks.push(id);
                alert("সংরক্ষিত হয়েছে!");
            }
            localStorage.setItem("breachtimes-bookmarks", JSON.stringify(bookmarks));
        }

        async function postComment(articleId) {
            const input = document.getElementById("comment-input");
            const text = input.value.trim();

            if (!text) {
                alert("অনুগ্রহ করে কিছু লিখুন!");
                return;
            }

            try {
                const res = await fetch("../api/post_comment.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ articleId, user: "Anonymous", text, lang })
                });
                const result = await res.json();
                if (result.success) {
                    alert("মন্তব্য প্রকাশিত হয়েছে! পেজ রিলোড হচ্ছে...");
                    setTimeout(() => location.reload(), 1000);
                } else {
                    alert("সমস্যা হয়েছে!");
                }
            } catch (e) {
                console.error(e);
                alert("সার্ভার ত্রুটি!");
            }
        }

        function toggleTheme() {
            const isDark = document.documentElement.classList.toggle("dark");
            localStorage.setItem("breachtimes-theme", isDark ? "dark" : "light");
        }

        function toggleLanguage() {
            const newLang = lang === "bn" ? "en" : "bn";
            window.location.href = `?id=${articleId}&lang=${newLang}`;
        }

        async function handleLogout() {
            try {
                await fetch("../api/logout.php");
                window.location.reload();
            } catch (e) {
                console.error(e);
            }
        }
    </script>
</body>
</html>
