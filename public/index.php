<?php
session_start();
require_once "../src/config/db.php";
require_once "../src/lib/functions.php";

$user = isset($_SESSION["user_email"]) ? $_SESSION["user_email"] : null;
$isAdmin = isset($_SESSION["user_role"]) && $_SESSION["user_role"] === "admin";
$initialCategory = isset($_GET["category"]) ? $_GET["category"] : "home";

// Variables for header.php
$lang = isset($_GET["lang"]) ? $_GET["lang"] : (isset($_SESSION["lang"]) ? $_SESSION["lang"] : "bn");
$currentCategory = $initialCategory;
?>
<!doctype html>
<html lang="<?php echo $lang; ?>">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>BreachTimes | Investigating Journalism</title>

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&display=swap');
    </style>
    
    <link href="assets/css/styles.css" rel="stylesheet" />

    <script src="assets/js/lucide.js"></script>
    <script src="assets/js/dropdown.js"></script>
    <script src="assets/js/layout.js"></script>
</head>

<body class="bg-page text-page-text font-sans transition-colors duration-500 antialiased selection:bg-bbcRed selection:text-white">
    <div id="progress-bar" class="fixed top-0 left-0 h-1 bg-bbcRed z-[100] shadow-[0_0_10px_var(--color-bbcRed)]" style="width: 0%"></div>

    <?php require_once "includes/header.php"; ?>

    <div id="app"></div>

    <div id="toast-container" class="fixed bottom-8 left-1/2 transform -translate-x-1/2 z-[110] pointer-events-none w-full max-w-sm flex flex-col items-center gap-2"></div>

    <?php require_once "includes/footer.php"; ?>

    <script>
        const PLACEHOLDER_IMAGE = "https://placehold.co/600x400/1a1a1a/FFF?text=BreachTimes";
        
        // Constants
        const translations = {
            en: {
                no_saved_articles: "No saved articles",
                bookmark_your_favorites: "Bookmark your favorite stories.",
                no_news_in_this_category: "No news in this category",
                more_world_news: "More World News",
                business_news: "Business",
                read_more: "Read More",
                video: "Video",
                all: "All",
                removed: "Removed",
                saved_successfully: "Saved successfully!",
                link_copied: "Link copied to clipboard",
                server_error: "Server error!",
                image_upload_success: "Image uploaded successfully!",
                data_restored: "Data restored successfully!",
                data_downloaded: "Data downloaded!",
                just_now: "Just now",
                minute: "minute",
                minutes: "minutes",
                hour: "hour",
                hours: "hours",
                day: "day",
                days: "days",
            },
            bn: {
                no_saved_articles: "কোনো সংরক্ষিত নিবন্ধ নেই",
                bookmark_your_favorites: "আপনার পছন্দের খবরগুলো বুকমার্ক করে রাখুন।",
                no_news_in_this_category: "এই বিভাগে কোনো খবর নেই",
                more_world_news: "আরও বিশ্ব সংবাদ",
                business_news: "ব্যবসা",
                read_more: "আরও পড়ুন",
                video: "ভিডিও",
                all: "সব দেখুন",
                removed: "সরানো হয়েছে",
                saved_successfully: "সংরক্ষিত হয়েছে!",
                link_copied: "লিঙ্ক ক্লিপবোর্ডে কপি করা হয়েছে",
                server_error: "সার্ভার এরর!",
                image_upload_success: "ছবি আপলোড সম্পন্ন!",
                data_restored: "ডাটা রিস্টোর সফল হয়েছে!",
                data_downloaded: "ডাটা ডাউনলোড হয়েছে!",
                just_now: "এইমাত্র",
                minute: "মিনিট",
                minutes: "মিনিট",
                hour: "ঘন্টা",
                hours: "ঘন্টা",
                day: "দিন",
                days: "দিন",
            },
        };

        const CATEGORY_MAP = {
            en: { news: "News", sport: "Sport", business: "Business", innovation: "Innovation", culture: "Culture", arts: "Arts", travel: "Travel", earth: "Earth", audio: "Audio", video: "Video", live: "Live" },
            bn: { news: "খবর", sport: "খেলা", business: "ব্যবসা", innovation: "উদ্ভাবন", culture: "সংস্কৃতি", arts: "শিল্পকলা", travel: "ভ্রমণ", earth: "পৃথিবী", audio: "অডিও", video: "ভিডিও", live: "লাইভ" }
        };

        // App State
        const state = {
            bbcData: null,
            category: "<?php echo htmlspecialchars($initialCategory); ?>",
            bookmarks: [],
            user: <?php echo json_encode($user); ?>,
            isAdmin: <?php echo json_encode($isAdmin); ?>,
            isLoading: true,
            darkMode: <?php echo isset($_COOKIE['breachtimes-theme']) && $_COOKIE['breachtimes-theme'] === 'dark' ? 'true' : 'false'; ?>,
            language: "<?php echo $lang; ?>",
        };
        
        const t = (key) => translations[state.language][key] || key;

        // Helpers
        function escapeHtml(unsafe) {
            if (typeof unsafe !== 'string') return unsafe;
            const div = document.createElement('div');
            div.appendChild(document.createTextNode(unsafe));
            return div.innerHTML;
        }

        function getCategoryKey(value, lang) {
            for (const key in CATEGORY_MAP[lang]) {
                if (CATEGORY_MAP[lang][key] === value) return key;
            }
            return value; 
        }

        function formatTimestamp(timestampString) {
            if (!timestampString) return '';
            let date = new Date(timestampString);
            if (isNaN(date.getTime())) {
                // Try parsing MySQL format
                const parts = timestampString.match(/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/);
                if (parts) date = new Date(parts[1], parts[2] - 1, parts[3], parts[4], parts[5], parts[6]);
                else return timestampString;
            }
            
            const now = new Date();
            const secondsPast = (now.getTime() - date.getTime()) / 1000;

            if (secondsPast < 60) return t('just_now');
            if (secondsPast < 3600) {
                const m = Math.floor(secondsPast / 60);
                return `${m} ${m === 1 ? t('minute') : t('minutes')}`;
            }
            if (secondsPast < 86400) {
                const h = Math.floor(secondsPast / 3600);
                return `${h} ${h === 1 ? t('hour') : t('hours')}`;
            }
            if (secondsPast < 2592000) { 
                const d = Math.floor(secondsPast / 86400);
                return `${d} ${d === 1 ? t('day') : t('days')}`;
            }
            
            const options = { year: 'numeric', month: state.language === 'bn' ? 'long' : 'short', day: 'numeric' };
            const locale = state.language === 'bn' ? 'bn-BD' : 'en-US';
            return date.toLocaleDateString(locale, options);
        }

        // Core Functions
        function init() {
            loadBookmarks();
            fetchBbcData();
            
            // Scroll Progress
            window.addEventListener("scroll", () => {
                const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
                const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
                const scrolled = (winScroll / height) * 100;
                const bar = document.getElementById("progress-bar");
                if (bar) bar.style.width = scrolled + "%";
                
                // Back to Top visibility (handled by CSS class toggles in JS usually, but here inline style/class)
                const btn = document.getElementById("back-to-top");
                if(btn) {
                    if (winScroll > 400) {
                        btn.classList.remove("opacity-0", "translate-y-10", "pointer-events-none");
                        btn.classList.add("opacity-100", "translate-y-0");
                    } else {
                        btn.classList.add("opacity-0", "translate-y-10", "pointer-events-none");
                        btn.classList.remove("opacity-100", "translate-y-0");
                    }
                }
            });
        }

        function loadBookmarks() {
            try {
                const saved = localStorage.getItem("breachtimes-bookmarks");
                if (saved) state.bookmarks = JSON.parse(saved);
            } catch (e) { console.error(e); }
        }

        function toggleBookmark(id) {
            const index = state.bookmarks.indexOf(id);
            if (index > -1) {
                state.bookmarks.splice(index, 1);
                showToastMsg(t('removed'));
            } else {
                state.bookmarks.push(id);
                showToastMsg(t('saved_successfully'));
            }
            localStorage.setItem("breachtimes-bookmarks", JSON.stringify(state.bookmarks));
            // Re-render if in saved view
            if (state.category === 'saved') render();
            else {
                // Update specific button if visible
                render(); // Simplest to re-render to update icons
            }
        }

        async function fetchBbcData() {
            state.isLoading = true;
            render();
            try {
                let apiUrl = `api/get_data.php?lang=${state.language}`;
                if (state.category && state.category !== 'home') {
                    apiUrl += `&category=${encodeURIComponent(state.category)}`;
                }
                const response = await fetch(apiUrl);
                if (!response.ok) throw new Error("Network response was not ok");
                state.bbcData = await response.json();
            } catch (error) {
                console.error("Fetch error:", error);
                showToastMsg(t('server_error'));
            } finally {
                state.isLoading = false;
                render();
            }
        }

        function render() {
            const app = document.getElementById("app");
            if (state.isLoading) {
                app.innerHTML = renderSkeleton();
                return;
            }
            app.innerHTML = renderHomeView();
            lucide.createIcons();
        }

        function renderHomeView() {
            let sections = state.bbcData?.sections || [];
            
            if (state.category === "saved") {
                const allArticles = state.bbcData?.sections?.flatMap(s => s.articles) || [];
                const savedArticles = allArticles.filter(a => state.bookmarks.includes(a.id));
                
                if (savedArticles.length === 0) {
                    return `
                        <div class="flex flex-col items-center justify-center py-32 text-center animate-fade-in">
                            <div class="bg-muted-bg p-6 rounded-full mb-4"><i data-lucide="bookmark" class="w-12 h-12 text-gray-400"></i></div>
                            <h3 class="text-2xl font-bold mb-2 dark:text-white text-bbcDark">${t('no_saved_articles')}</h3>
                            <p class="text-muted-text">${t('bookmark_your_favorites')}</p>
                        </div>`;
                }
                sections = [{
                    title: t('saved'),
                    type: "grid",
                    articles: savedArticles,
                    style: state.darkMode ? "dark" : "light",
                    highlightColor: "var(--color-bbcRed)"
                }];
            } else if (state.category !== 'home') {
                 // If fetching by category, the API usually returns just that section or filtered list
                 // Assuming the API structure matches, otherwise we filter locally if needed.
                 // Based on API logic, it returns relevant sections.
            }

            if (!sections.length) {
                return `
                    <div class="flex flex-col items-center justify-center py-32 text-center animate-fade-in">
                        <div class="bg-muted-bg p-6 rounded-full mb-4"><i data-lucide="newspaper" class="w-12 h-12 text-gray-400"></i></div>
                        <h3 class="text-2xl font-bold mb-2 dark:text-white text-bbcDark">${t('no_news_in_this_category')}</h3>
                    </div>`;
            }

            // Main Sections
            let html = `<main class="container mx-auto px-4 lg:px-8 max-w-[1380px] py-4 min-h-[60vh] animate-fade-in-up">
                ${sections.map(renderSection).join("")}`;

            // Extras for Home
            if (state.category === "home") {
                // Locate specific sections for the extra layout
                // This logic depends on specific IDs which might change, keeping it safe
                const world = sections.find(s => s.id === "virginia") || { articles: [] };
                const business = sections.find(s => s.id === "vermont") || { articles: [] };
                const collection = sections.find(s => s.id === "wyoming");

                html += `
                <section class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-12 animate-fade-in">
                    <div class="lg:col-span-1 h-full">${collection ? renderSection({...collection, type: 'list'}) : ''}</div>
                    <div class="lg:col-span-1 h-full">
                        <div class="bg-card p-6 rounded-2xl shadow-soft border border-border-color h-full">
                            <div class="flex items-center gap-3 mb-6 pb-4 border-b border-border-color">
                                <div class="w-1.5 h-6 rounded-full bg-blue-500"></div>
                                <h3 class="text-xl font-bold dark:text-white text-black">${t('more_world_news')}</h3>
                            </div>
                            <ul class="space-y-4">
                                ${world.articles.slice(0, 3).map(a => renderMiniArticle(a, 'text-blue-500')).join('')}
                            </ul>
                        </div>
                    </div>
                    <div class="lg:col-span-1 h-full">
                        <div class="bg-card p-6 rounded-2xl shadow-soft border border-border-color h-full">
                            <div class="flex items-center gap-3 mb-6 pb-4 border-b border-border-color">
                                <div class="w-1.5 h-6 rounded-full bg-green-500"></div>
                                <h3 class="text-xl font-bold dark:text-white text-black">${t('business_news')}</h3>
                            </div>
                            <ul class="space-y-4">
                                ${business.articles.slice(2, 5).map(a => renderMiniArticle(a, 'text-green-500')).join('')}
                            </ul>
                        </div>
                    </div>
                </section>`;
            }

            html += `</main>`;
            return html;
        }

        function renderSection(section) {
            const isDark = state.darkMode || section.style === "dark";
            const containerClass = section.style === "dark" ? 
                "bg-card-elevated text-white p-8 md:p-10 rounded-3xl mb-12 shadow-2xl relative overflow-hidden" : "mb-12";
            const titleColor = isDark ? "text-white" : "text-bbcDark";
            const borderColor = isDark ? "white" : (section.highlightColor || "var(--color-bbcRed)");

            let content = "";
            if (section.type === "hero-grid") {
                const hero = section.articles[0];
                const subs = section.articles.slice(1);
                content = `
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        ${hero ? `<div class="col-span-1 md:col-span-2 lg:col-span-2">${renderArticleCard(hero, "hero-grid", isDark)}</div>` : ""}
                        <div class="col-span-1 md:col-span-2 lg:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-6">
                            ${subs.map(a => renderArticleCard(a, "grid", isDark)).join("")}
                        </div>
                    </div>`;
            } else if (section.type === "list") {
                content = `
                    <div class="bg-card p-6 rounded-2xl shadow-soft border border-border-color h-full">
                        <div class="flex items-center gap-3 mb-6 pb-4 border-b border-border-color">
                            <div class="w-1.5 h-6 rounded-full" style="background-color: ${section.highlightColor}"></div>
                            <h3 class="text-xl font-bold ${titleColor}">${escapeHtml(section.title)}</h3>
                        </div>
                        <ul class="space-y-4">
                            ${section.articles.map(a => renderMiniArticle(a, 'text-bbcRed', isDark)).join("")}
                        </ul>
                    </div>`;
            } else {
                const gridClass = section.type === "reel" ? "flex overflow-x-auto no-scrollbar gap-5 pb-8 snap-x scroll-smooth px-1" :
                                  section.type === "audio" ? "grid grid-cols-2 md:grid-cols-4 gap-6" :
                                  "grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6";
                content = `<div class="${gridClass}">${section.articles.map(a => renderArticleCard(a, section.type, isDark)).join("")}</div>`;
            }

            return `
                <section class="${containerClass} animate-fade-in-up relative z-10">
                    <div class="flex justify-between items-center mb-8">
                        <h2 class="text-2xl font-bold flex items-center gap-3 ${titleColor}">
                            <span class="w-2 h-8 rounded-full" style="background-color: ${borderColor}"></span>
                            ${escapeHtml(section.title)}
                        </h2>
                        ${section.type !== "hero-grid" ? `<a href="?category=${getCategoryKey(section.associatedCategory, state.language)}" class="text-sm font-bold hover:text-bbcRed transition-colors flex items-center gap-1 opacity-80 hover:opacity-100 ${titleColor}">${t('all')} <i data-lucide="chevron-right" class="w-4 h-4"></i></a>` : ""}
                    </div>
                    ${content}
                    ${section.style === "dark" ? `<div class="absolute -right-20 -bottom-20 w-64 h-64 bg-white/5 rounded-full blur-3xl pointer-events-none"></div>` : ""}
                </section>`;
        }

        function renderArticleCard(article, type, isSectionDark) {
            const isDark = state.darkMode || isSectionDark;
            const textColor = isSectionDark ? "text-white" : "text-card-text";
            const subTextColor = isSectionDark ? "text-gray-300" : "text-gray-600";
            const metaColor = isSectionDark ? "text-gray-400" : "text-muted-text";
            const borderClass = isSectionDark ? "border-gray-800" : "border-border-color";
            const bgClass = "bg-card-elevated";
            
            const isBookmarked = state.bookmarks.includes(article.id);
            const bookmarkFill = isBookmarked ? (state.darkMode ? "white" : "black") : "none";
            const bookmarkBtn = `<button onclick="event.stopPropagation(); toggleBookmark('${article.id}')" class="absolute top-3 right-3 bg-white/90 dark:bg-black/50 backdrop-blur p-2.5 rounded-full hover:bg-white dark:hover:bg-gray-800 text-black dark:text-white shadow-md z-10 hover:scale-110 active:scale-95 transition-all"><i data-lucide="bookmark" class="w-4 h-4" fill="${bookmarkFill}"></i></button>`;
            const timeAgo = formatTimestamp(article.published_at);

            if (type === "reel") {
                return `
                    <div class="flex-shrink-0 w-[280px] group cursor-pointer snap-start transform transition-all duration-300 hover:-translate-y-1" onclick="openDetail('${article.id}')">
                        <div class="aspect-[9/16] overflow-hidden relative rounded-2xl shadow-lg border border-border-color">
                            <img src="${article.image}" onerror="this.src='${PLACEHOLDER_IMAGE}'" loading="lazy" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/20 to-transparent opacity-90"></div>
                            <div class="absolute bottom-5 left-5 right-5 text-white">
                                <span class="bg-bbcRed text-white text-[10px] px-2 py-0.5 rounded font-bold mb-2 inline-block">${escapeHtml(article.category)}</span>
                                <h3 class="font-bold text-lg leading-tight mb-1 group-hover:text-gray-200 transition-colors">${escapeHtml(article.title)}</h3>
                            </div>
                            <div class="absolute top-4 right-4 flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity">${bookmarkBtn}</div>
                            <div class="absolute top-4 left-4 bg-black/40 backdrop-blur-md p-2 rounded-full"><i data-lucide="play-circle" class="w-5 h-5 text-white"></i></div>
                        </div>
                    </div>`;
            }
            
            if (type === "audio") {
                return `
                    <div class="group cursor-pointer relative transform transition-all duration-300 hover:-translate-y-1" onclick="openDetail('${article.id}')">
                        <div class="relative aspect-square mb-3 overflow-hidden rounded-2xl shadow-md group-hover:shadow-lg border border-border-color">
                            <img src="${article.image}" onerror="this.src='${PLACEHOLDER_IMAGE}'" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                            <div class="absolute inset-0 bg-black/10 group-hover:bg-black/0 transition-colors"></div>
                            <div class="absolute bottom-3 left-3 bg-white/90 backdrop-blur text-bbcDark rounded-full p-2 shadow-sm group-hover:scale-110 transition-transform"><i data-lucide="headset" class="w-4 h-4 fill-current"></i></div>
                            ${bookmarkBtn}
                        </div>
                        <h3 class="text-base font-bold leading-snug group-hover:text-bbcRed transition-colors ${textColor}">${escapeHtml(article.title)}</h3>
                        <div class="flex justify-between items-center mt-2 text-xs ${metaColor} font-medium">
                            <span class="bg-muted-bg px-2 py-0.5 rounded text-gray-700 dark:text-gray-300">${escapeHtml(article.category)}</span>
                            ${article.readTime ? `<span><i data-lucide="clock" class="w-3 h-3 inline"></i> ${escapeHtml(article.readTime)}</span>` : ""}
                        </div>
                    </div>`;
            }

            return `
                <article class="group cursor-pointer flex flex-col h-full relative ${bgClass} rounded-2xl overflow-hidden shadow-soft hover:shadow-soft-hover transition-all duration-300 hover:-translate-y-1 border ${borderClass}" onclick="openDetail('${article.id}')">
                    <div class="overflow-hidden aspect-video relative">
                        <img src="${article.image}" onerror="this.src='${PLACEHOLDER_IMAGE}'" loading="lazy" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                        <div class="absolute top-0 right-0 p-3 opacity-0 group-hover:opacity-100 transition-opacity z-10">${bookmarkBtn}</div>
                        ${article.isVideo ? `<div class="absolute bottom-3 left-3 bg-black/60 text-white px-2 py-1 rounded-full backdrop-blur-sm text-xs flex items-center gap-1"><i data-lucide="play" class="w-3 h-3 fill-white"></i> ${t('video')}</div>` : ""}
                        <div class="absolute bottom-0 left-0 right-0 h-1/3 bg-gradient-to-t from-black/50 to-transparent opacity-60"></div>
                    </div>
                    <div class="flex flex-col flex-grow p-5">
                        <div class="mb-2 flex items-center gap-2">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-bbcRed bg-red-50 dark:bg-red-900/20 px-2 py-0.5 rounded">${escapeHtml(article.category)}</span>
                            <span class="text-[10px] text-muted-text">• ${timeAgo}</span>
                        </div>
                        <h3 class="text-lg md:text-xl font-bold mb-3 leading-tight group-hover:text-bbcRed transition-colors ${textColor}">${escapeHtml(article.title)}</h3>
                        ${type === "hero-grid" && article.summary ? `<p class="${subTextColor} text-sm leading-relaxed mb-4 line-clamp-3">${escapeHtml(article.summary)}</p>` : ""}
                        <div class="mt-auto pt-3 border-t ${borderClass} flex items-center justify-between text-xs ${metaColor}">
                            <span class="flex items-center gap-1 group-hover:translate-x-1 transition-transform">${t('read_more')} <i data-lucide="chevron-right" class="w-3 h-3"></i></span>
                            ${article.readTime ? `<span class="flex items-center gap-1"><i data-lucide="clock" class="w-3 h-3"></i> ${escapeHtml(article.readTime)}</span>` : ""}
                        </div>
                    </div>
                </article>`;
        }

        function renderMiniArticle(article, colorClass, isDark) {
            const titleColor = isDark ? "text-white" : "text-black";
            return `
                <li class="group cursor-pointer p-2 rounded-xl hover:bg-muted-bg transition-colors" onclick="openDetail('${article.id}')">
                    <div class="flex gap-4">
                        ${article.image ? `<div class="w-20 h-20 flex-shrink-0 aspect-square overflow-hidden rounded-lg relative"><img src="${article.image}" onerror="this.src='${PLACEHOLDER_IMAGE}'" loading="lazy" class="w-full h-full object-cover"></div>` : ""}
                        <div class="flex-grow">
                            <h4 class="text-sm font-bold leading-snug group-hover:${colorClass} transition-colors ${titleColor} line-clamp-2">${escapeHtml(article.title)}</h4>
                        </div>
                    </div>
                </li>`;
        }

        function renderSkeleton() {
            return `
                <div class="container mx-auto px-4 lg:px-8 max-w-[1380px] py-6 min-h-[60vh]">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-8">
                        <div class="col-span-1 md:col-span-2 lg:col-span-2">
                            <div class="bg-muted-bg w-full aspect-video mb-5 rounded-xl animate-pulse"></div>
                            <div class="bg-muted-bg w-3/4 h-8 mb-3 rounded animate-pulse"></div>
                            <div class="bg-muted-bg w-full h-4 mb-2 rounded animate-pulse"></div>
                            <div class="bg-muted-bg w-2/3 h-4 rounded animate-pulse"></div>
                        </div>
                        <div class="col-span-1 md:col-span-2 lg:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-8">
                            ${[1, 2, 3, 4].map(i => `<div><div class="bg-muted-bg w-full aspect-video mb-3 rounded-lg animate-pulse"></div><div class="bg-muted-bg w-full h-5 rounded animate-pulse"></div></div>`).join("")}
                        </div>
                    </div>
                </div>`;
        }

        function openDetail(id) {
            window.location.href = `read?id=${id}&lang=${state.language}`;
        }

        function showToastMsg(msg) {
            const container = document.getElementById("toast-container");
            const toast = document.createElement("div");
            toast.className = "animate-[slide-up_0.4s_cubic-bezier(0.16,1,0.3,1)_forwards] fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-black/80 dark:bg-white/90 backdrop-blur text-white dark:text-black px-6 py-3 rounded-full shadow-lg font-bold flex items-center gap-2 mb-2 text-sm w-auto";
            toast.innerHTML = `<i data-lucide="check-circle" class="w-4 h-4 text-green-400 dark:text-green-600"></i> ${msg}`;
            container.appendChild(toast);
            lucide.createIcons();
            setTimeout(() => toast.remove(), 3000);
        }
        // Expose to global for layout.js
        window.showToastMsg = showToastMsg;

        init();
    </script>
</body>
</html>