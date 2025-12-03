<?php
session_start();
require_once "includes/functions.php";
$user = isset($_SESSION["user_email"]) ? $_SESSION["user_email"] : null;
$isAdmin = isset($_SESSION["user_role"]) && $_SESSION["user_role"] === "admin";
$initialCategory = isset($_GET["category"]) ? $_GET["category"] : "home";
?>
<!doctype html>
<html lang="bn">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>BreachTimes | Investigating Journalism</title>

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&display=swap');
    </style>
    
    <!-- QuillJS CSS -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet" />

    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style type="text/tailwindcss">
        <?php include "tailwind.config.css"; ?>
    </style>

    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <!-- QuillJS Script -->
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
</head>

<body
    class="bg-page text-page-text font-sans transition-colors duration-500 antialiased selection:bg-bbcRed selection:text-white">
    <div id="progress-bar" class="fixed top-0 left-0 h-1 bg-bbcRed z-[100] shadow-[0_0_10px_#B80000]" style="width: 0%">
    </div>

    <div id="app"></div>

    <div id="toast-container"
        class="fixed bottom-8 left-1/2 transform -translate-x-1/2 z-[110] pointer-events-none w-full max-w-sm flex flex-col items-center gap-2">
    </div>

    <!-- Hidden Input for Import -->
    <input type="file" id="import-input" class="hidden" accept=".json" onchange="importData(this)" />

    <script>
        const PLACEHOLDER_IMAGE = "https://placehold.co/600x400/1a1a1a/FFF?text=BreachTimes";
        let quillEditor = null;

        const translations = {
            en: {
                breaking: "Breaking",
                breaking_news: "US suspends all asylum applications after DC incident",
                ad_text: "Space available",
                advertisement: "Advertisement",
                home: "Home",
                news: "News",
                sport: "Sport",
                business: "Business",
                innovation: "Innovation",
                culture: "Culture",
                arts: "Arts",
                travel: "Travel",
                audio: "Audio",
                video: "Video",
                saved: "Saved",
                weather_in: "Weather in",
                dhaka: "Dhaka",
                weather: "Weather",
                removed: "Removed",
                saved_successfully: "Saved successfully!",
                bengali_digits: ["0", "1", "2", "3", "4", "5", "6", "7", "8", "9"],
                admin_access_denied: "Access denied! Please login as an admin.",
                link_copied: "Link copied to clipboard",
                please_write_something: "Please write something!",
                unknown_user: "Unknown User",
                comment_posted: "Comment posted! Reloading...",
                error_occurred: "An error occurred!",
                server_error: "Server error!",
                all: "All",
                no_saved_articles: "No saved articles",
                bookmark_your_favorites: "Bookmark your favorite stories.",
                no_news_in_this_category: "No news in this category",
                more_world_news: "More World News",
                business_news: "Business",
                menu: "Menu",
                search_placeholder: "What are you looking for?...",
                share: "Share",
                read_more: "Read More",
                comments: "Comments",
                post_comment: "Post Comment",
                post_comment_placeholder: "Share your thoughts...",
                no_comments_yet: "No comments yet.",
                subscribe_newsletter: "Subscribe to our newsletter for breaking news and analysis first.",
                your_email: "Your email address",
                subscribe: "Subscribe",
                subscribed_successfully: "Subscribed successfully!",
                copyright: "© 2025 BreachTimes. All rights reserved.",
                welcome: "Welcome",
                admin_panel: "Admin Panel",
                sign_out: "Sign Out",
                sign_in: "Sign In",
                register: "Register",
                image_upload_success: "Image uploaded successfully!",
                data_restored: "Data restored successfully!",
                invalid_file_format: "Invalid file format!",
                file_read_error: "Error reading file!",
                data_downloaded: "Data downloaded!",
            },
            bn: {
                breaking: "ব্রেকিং",
                breaking_news: "ডিসি ঘটনার পর সব আশ্রয় প্রার্থনার সিদ্ধান্ত স্থগিত করেছে যুক্তরাষ্ট্র",
                ad_text: "স্পেস উপলব্ধ",
                advertisement: "বিজ্ঞাপন",
                home: "হোম",
                news: "খবর",
                sport: "খেলা",
                business: "ব্যবসা",
                innovation: "উদ্ভাবন",
                culture: "সংস্কৃতি",
                arts: "শিল্পকলা",
                travel: "ভ্রমণ",
                audio: "অডিও",
                video: "ভিডিও",
                saved: "সংরক্ষিত",
                weather_in: "আবহাওয়া",
                dhaka: "ঢাকা",
                weather: "আবহাওয়া",
                removed: "সরানো হয়েছে",
                saved_successfully: "সংরক্ষিত হয়েছে!",
                bengali_digits: ["০", "১", "২", "৩", "৪", "৫", "৬", "৭", "৮", "৯"],
                admin_access_denied: "অ্যাক্সেস নেই! অ্যাডমিন হিসেবে লগইন করুন।",
                link_copied: "লিঙ্ক ক্লিপবোর্ডে কপি করা হয়েছে",
                please_write_something: "অনুগ্রহ করে কিছু লিখুন!",
                unknown_user: "অজ্ঞাত ব্যবহারকারী",
                comment_posted: "মন্তব্য প্রকাশিত হয়েছে! পেজ রিলোড হচ্ছে...",
                error_occurred: "সমস্যা হয়েছে!",
                server_error: "সার্ভার এরর!",
                all: "সব দেখুন",
                no_saved_articles: "কোনো সংরক্ষিত নিবন্ধ নেই",
                bookmark_your_favorites: "আপনার পছন্দের খবরগুলো বুকমার্ক করে রাখুন।",
                no_news_in_this_category: "এই বিভাগে কোনো খবর নেই",
                more_world_news: "আরও বিশ্ব সংবাদ",
                business_news: "ব্যবসা",
                menu: "মেনু",
                search_placeholder: "কি খুঁজতে চান?...",
                share: "শেয়ার",
                read_more: "আরও পড়ুন",
                comments: "মন্তব্যসমূহ",
                post_comment: "মন্তব্য প্রকাশ করুন",
                post_comment_placeholder: "আপনার মতামত জানান...",
                no_comments_yet: "এখনও কোনো মন্তব্য নেই।",
                subscribe_newsletter: "সবার আগে ব্রেকিং নিউজ এবং বিশ্লেষণ পেতে আপনার ইমেইল দিয়ে সাবস্ক্রাইব করুন।",
                your_email: "আপনার ইমেইল ঠিকানা",
                subscribe: "সাবস্ক্রাইব",
                subscribed_successfully: "সাবস্ক্রাইব করা হয়েছে!",
                copyright: "© ২০২৫ ব্রিচটাইমস। সর্বস্বত্ব সংরক্ষিত।",
                welcome: "স্বাগতম",
                admin_panel: "অ্যাডমিন প্যানেল",
                sign_out: "সাইন আউট",
                sign_in: "সাইন ইন",
                register: "নিবন্ধন",
                image_upload_success: "ছবি আপলোড সম্পন্ন!",
                data_restored: "ডাটা রিস্টোর সফল হয়েছে!",
                invalid_file_format: "ভুল ফরম্যাটের ফাইল!",
                file_read_error: "ফাইল রিড করতে সমস্যা হয়েছে!",
                data_downloaded: "ডাটা ডাউনলোড হয়েছে!",
            },
        };

        const CATEGORY_MAP = {
            en: {
                news: "News",
                sport: "Sport",
                business: "Business",
                innovation: "Innovation",
                culture: "Culture",
                arts: "Arts",
                travel: "Travel",
                earth: "Earth",
                audio: "Audio",
                video: "Video",
                live: "Live",
            },
            bn: {
                news: "খবর",
                sport: "খেলা",
                business: "ব্যবসা",
                innovation: "উদ্ভাবন",
                culture: "সংস্কৃতি",
                arts: "শিল্পকলা",
                travel: "ভ্রমণ",
                earth: "পৃথিবী",
                audio: "অডিও",
                video: "ভিডিও",
                live: "লাইভ",
            }
        };

        function getCategoryKey(value, lang) {
            return Object.keys(CATEGORY_MAP[lang]).find(key => CATEGORY_MAP[lang][key] === value) || value;
        }

        const state = {
            bbcData: null,
            view: "home",
            category: "<?php echo htmlspecialchars($initialCategory); ?>",
            bookmarks: [],
            user: <?php echo json_encode($user); ?>,
            isAdmin: <?php echo json_encode($isAdmin); ?>,
            fontSize: "md",
            selectedArticleId: null,
            isLoading: false,
            darkMode: false,
            language: "bn",
            isMobileMenuOpen: false,
            isSearchOpen: false,
            searchQuery: "",
            searchResults: [],
            showBreaking: false,
            showBackToTop: false,
            scrollProgress: 0,
            showSettings: false,
            showEditor: false,
            editArticle: null,
            tempSettings: {},
            authMode: "signin",
            formInputs: { email: "", age: "" },
            tempTimeline: [],
            tempAssociates: [],
            tempEvidence: [],
        };
        
        const t = (key) => translations[state.language][key] || key;

        function init() {
            loadStateFromStorage();

            const savedTheme = localStorage.getItem("breachtimes-theme");
            const systemDark = window.matchMedia("(prefers-color-scheme: dark)").matches;

            const savedLanguage = localStorage.getItem("breachtimes-language");
            if (savedLanguage) {
                state.language = savedLanguage;
            }
            
            document.documentElement.lang = state.language;

            fetchBbcData();
            fetchWeather(); // Call Weather API


            if (savedTheme === "dark" || (!savedTheme && systemDark)) {
                state.darkMode = true;
                document.documentElement.classList.add("dark");
            } else {
                state.darkMode = false;
                document.documentElement.classList.remove("dark");
            }

            window.addEventListener("scroll", () => {
                const winScroll =
                    document.body.scrollTop || document.documentElement.scrollTop;
                const height =
                    document.documentElement.scrollHeight -
                    document.documentElement.clientHeight;
                const scrolled = (winScroll / height) * 100;
                const progressBar = document.getElementById("progress-bar");
                if (progressBar) progressBar.style.width = scrolled + "%";

                const backToTopBtn = document.getElementById("back-to-top");
                if (backToTopBtn) {
                    if (winScroll > 400) {
                        backToTopBtn.classList.remove(
                            "opacity-0",
                            "translate-y-10",
                            "pointer-events-none",
                        );
                        backToTopBtn.classList.add("opacity-100", "translate-y-0");
                    } else {
                        backToTopBtn.classList.add(
                            "opacity-0",
                            "translate-y-10",
                            "pointer-events-none",
                        );
                        backToTopBtn.classList.remove("opacity-100", "translate-y-0");
                    }
                }
            });

            window.addEventListener("hashchange", handleHashChange);
            handleHashChange();

            render();
        }

        async function fetchWeather() {
            try {
                const res = await fetch(
                    "https://api.open-meteo.com/v1/forecast?latitude=23.8103&longitude=90.4125&current_weather=true",
                );
                const data = await res.json();
                const temp = data.current_weather.temperature;
                const code = data.current_weather.weathercode;
                
                let icon = "sun";
                if (code > 3) icon = "cloud";
                if (code > 45) icon = "cloud-fog";
                if (code > 50) icon = "cloud-drizzle";
                if (code > 60) icon = "cloud-rain";
                if (code > 70) icon = "snowflake";
                if (code > 95) icon = "cloud-lightning";

                const weatherContainer = document.getElementById("weather-display");
                if (weatherContainer) {
                    weatherContainer.innerHTML = `
                        <div class="flex items-center gap-2 text-gray-700 dark:text-gray-200">
                            <i data-lucide="${icon}" class="w-5 h-5 text-bbcRed"></i>
                            <div class="flex flex-col leading-none">
                                <span class="font-bold text-xs">${t('dhaka')}</span>
                                <span class="text-xs text-muted-text mt-0.5 font-bold">${translateNumber(Math.round(temp))}° C</span>
                            </div>
                        </div>
                    `;
                    lucide.createIcons();
                }
            } catch (e) {
                console.error("Weather fetch failed", e);
                const weatherContainer = document.getElementById("weather-display");
                if (weatherContainer) {
                    weatherContainer.innerHTML = `
                        <div class="flex items-center gap-2 text-gray-700 dark:text-gray-200 opacity-50">
                            <i data-lucide="cloud-off" class="w-4 h-4"></i>
                            <span class="text-xs">${t('weather')}</span>
                        </div>
                    `;
                    lucide.createIcons();
                }
            }
        }

        function handleImageUpload(input) {
            const file = input.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function (e) {
                const urlInput = document.querySelector('input[name="image"]');
                if (urlInput) {
                    urlInput.value = e.target.result;
                    showToastMsg(t('image_upload_success'));
                }
            };
            reader.readAsDataURL(file);
        }

        function exportData() {
            const data = JSON.stringify(state.bbcData, null, 2);
            const blob = new Blob([data], { type: "application/json" });
            const url = URL.createObjectURL(blob);
            const a = document.createElement("a");
            a.href = url;
            a.download = `breachtimes_backup_${new Date().toISOString().split("T")[0]}.json`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            showToastMsg(t('data_downloaded'));
        }

        function triggerImport() {
            document.getElementById("import-input").click();
        }

        function importData(input) {
            const file = input.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function (e) {
                try {
                    const data = JSON.parse(e.target.result);
                    if (data.sections && Array.isArray(data.sections)) {
                        state.bbcData = data;
                        saveStateToStorage();
                        showToastMsg(t('data_restored'));
                        render();
                    } else {
                        showToastMsg(t('invalid_file_format'));
                    }
                } catch (err) {
                    console.error(err);
                    showToastMsg(t('file_read_error'));
                }
            };
            reader.readAsText(file);
        }

        function setFontSize(size) {
            setState({ fontSize: size });
        }

        function loadStateFromStorage() {
            const savedBookmarks = localStorage.getItem("breachtimes-bookmarks");
            if (savedBookmarks) {
                try {
                    state.bookmarks = JSON.parse(savedBookmarks);
                } catch (e) {
                    console.error("Bookmarks load error", e);
                }
            }
        }

        function saveStateToStorage() {
            localStorage.setItem(
                "breachtimes-bookmarks",
                JSON.stringify(state.bookmarks),
            );
        }

        function setState(updates, shouldRender = true) {
            Object.assign(state, updates);
            if (shouldRender) render();
        }
        function handleHashChange() {
            const hash = window.location.hash.substring(1);
            const params = new URLSearchParams(hash);
            const readId = params.get("read");
            if (readId) {
                openDetail(readId, false);
            } else if (state.view !== "home") {
                navigate("home", false);
            }
        }

        async function fetchBbcData() {
            setState({ isLoading: true });
            try {
                const response = await fetch(`get_data.php?lang=${state.language}`);
                const data = await response.json();
                setState({ bbcData: data, isLoading: false });
            } catch (error) {
                console.error("Error fetching data:", error);
                setState({ isLoading: false });
            }
        }


        function updateURL(targetView, param = null) {
            if (targetView === "detail" && param) {
                window.location.hash = `read=${param}`;
            } else {
                history.pushState(
                    "",
                    document.title,
                    window.location.pathname + window.location.search,
                );
            }
        }

        function simulateLoading(callback) {
            setState({ isLoading: true });
            window.scrollTo(0, 0);
            setTimeout(() => {
                setState({ isLoading: false }, false);
                callback();
            }, 600);
        }

        function showToastMsg(msg) {
            const container = document.getElementById("toast-container");
            const toast = document.createElement("div");
            toast.className =
                "toast-enter bg-black/80 dark:bg-white/90 backdrop-blur text-white dark:text-black px-6 py-3 rounded-full shadow-lg font-bold flex items-center gap-2 mb-2 text-sm w-auto";
            toast.innerHTML = `<i data-lucide="check-circle" class="w-4 h-4 text-green-400 dark:text-green-600"></i> ${msg}`;
            container.appendChild(toast);
            lucide.createIcons();
            setTimeout(() => toast.remove(), 3000);
        }

        function toggleBookmark(id) {
            const isSaved = state.bookmarks.includes(id);
            let newBookmarks;
            if (isSaved) {
                newBookmarks = state.bookmarks.filter((b) => b !== id);
                showToastMsg(t('removed'));
            } else {
                newBookmarks = [...state.bookmarks, id];
                showToastMsg(t('saved_successfully'));
            }
            setState({ bookmarks: newBookmarks });
            saveStateToStorage();
        }

        function translateNumber(num) {
            if (state.language === 'bn') {
                const banglaDigits = t('bengali_digits');
                return num.toString().split("").map((d) => banglaDigits[parseInt(d)] || d).join("");
            }
            return num.toString();
        }

        function toggleTheme() {
            const newMode = !state.darkMode;
            setState({ darkMode: newMode }, false);
            if (newMode) {
                document.documentElement.classList.add("dark");
                localStorage.setItem("breachtimes-theme", "dark");
            } else {
                document.documentElement.classList.remove("dark");
                localStorage.setItem("breachtimes-theme", "light");
            }
            render();
        }

        function toggleLanguage() {
            const newLang = state.language === "bn" ? "en" : "bn";
            setState({ language: newLang });
            localStorage.setItem("breachtimes-language", newLang);
            document.documentElement.lang = newLang;
            fetchBbcData();
        }

        function navigate(id, pushState = true) {
            if (id === "admin") {
                if (!state.isAdmin) {
                    showToastMsg(t('admin_access_denied'));
                    return;
                }
                setState({
                    view: "admin",
                    isMobileMenuOpen: false,
                    isSearchOpen: false,
                });
            } else {
                setState({
                    category: id,
                    view: "home",
                    isMobileMenuOpen: false,
                    isSearchOpen: false,
                });
                if (pushState) updateURL("home");
            }
            window.scrollTo(0, 0);
        }

        function openDetail(id, pushState = true) {
            simulateLoading(() => {
                setState({ selectedArticleId: id, view: "detail" });
                if (pushState) updateURL("detail", id);

                const all = state.bbcData.sections.flatMap((s) => s.articles);
                const article = all.find((a) => a.id === id);
                if (article) document.title = `${article.title} | BreachTimes`;
            });
        }

        function handleShare() {
            if (navigator.share) {
                navigator
                    .share({
                        title: document.title,
                        url: window.location.href,
                    })
                    .catch(console.error);
            } else {
                const tempInput = document.createElement("input");
                tempInput.value = window.location.href;
                document.body.appendChild(tempInput);
                tempInput.select();
                document.execCommand("copy");
                document.body.removeChild(tempInput);
                showToastMsg(t('link_copied'));
            }
        }

        async function postComment(articleId) {
            const input = document.getElementById("comment-input");
            const text = input ? input.value.trim() : "";

            if (!text) {
                showToastMsg(t('please_write_something'));
                return;
            }

            const userName = state.user
                ? state.user.split("@")[0]
                : t('unknown_user');

            try {
                const res = await fetch('api/post_comment.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ articleId, user: userName, text, lang: state.language })
                });
                const result = await res.json();
                if (result.success) {
                    showToastMsg(t('comment_posted'));
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToastMsg(t('error_occurred'));
                }
            } catch (e) {
                console.error(e);
                showToastMsg(t('server_error'));
            }
        }

        function getTrophyIconHtml() {
            return `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/><path d="M4 22h16"/><path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"/><path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"/><path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"/></svg>`;
        }

        function getIconHtml(iconName) {
            if (iconName === "trophy") return getTrophyIconHtml();
            return `<i data-lucide="${iconName}" class="w-4 h-4"></i>`;
        }

        function renderArticleCard(article, type, isSectionDark) {
            const isDark = state.darkMode || isSectionDark;
            const textColor = isSectionDark ? "text-white" : "text-card-text";
            const subTextColor = isSectionDark ? "text-gray-300" : "text-gray-600";
            const metaColor = isSectionDark ? "text-gray-400" : "text-muted-text";
            const borderClass = isSectionDark
                ? "border-gray-800"
                : "border-border-color";

            const bgClass = isSectionDark ? "bg-[#1E1E1E]" : "bg-card-elevated";

            const shadowClass = "shadow-soft hover:shadow-soft-hover";
            const isBookmarked = state.bookmarks.includes(article.id);
            const bookmarkFill = isBookmarked
                ? state.darkMode
                    ? "white"
                    : "black"
                : "none";

            const bookmarkBtn = `
                <button onclick="event.stopPropagation(); toggleBookmark('${article.id}')" 
                    class="absolute top-3 right-3 bg-white/90 dark:bg-black/50 backdrop-blur p-2.5 rounded-full hover:bg-white dark:hover:bg-gray-800 text-black dark:text-white shadow-md z-10 hover:scale-110 active:scale-95 transition-all">
                    <i data-lucide="bookmark" class="w-4 h-4" fill="${bookmarkFill}"></i>
                </button>
            `;

            const readTimeBadge = article.readTime
                ? `
                <span class="text-[10px] uppercase tracking-wider opacity-80 flex items-center gap-1 font-bold">
                    <i data-lucide="clock" class="w-3 h-3"></i> ${article.readTime}
                </span>
            `
                : "";

            if (type === "reel") {
                return `
                    <div class="flex-shrink-0 w-[280px] group cursor-pointer snap-start transform transition-all duration-300 hover:-translate-y-1" onclick="openDetail('${article.id}')">
                        <div class="aspect-[9/16] overflow-hidden relative rounded-2xl shadow-lg border border-border-color">
                            <img src="${article.image}" onerror="this.src='${PLACEHOLDER_IMAGE}'" loading="lazy" alt="${article.title}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/20 to-transparent opacity-90"></div>
                            <div class="absolute bottom-5 left-5 right-5 text-white">
                                <span class="bg-bbcRed text-white text-[10px] px-2 py-0.5 rounded font-bold mb-2 inline-block">${article.category}</span>
                                <h3 class="font-bold text-lg leading-tight mb-1 group-hover:text-gray-200 transition-colors">${article.title}</h3>
                            </div>
                            <div class="absolute top-4 right-4 flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                ${bookmarkBtn}
                            </div>
                            <div class="absolute top-4 left-4 bg-black/40 backdrop-blur-md p-2 rounded-full">
                                <i data-lucide="play-circle" class="w-5 h-5 text-white"></i>
                            </div>
                        </div>
                    </div>
                `;
            }

            if (type === "audio") {
                return `
                    <div class="group cursor-pointer relative transform transition-all duration-300 hover:-translate-y-1" onclick="openDetail('${article.id}')">
                        <div class="relative aspect-square mb-3 overflow-hidden rounded-2xl shadow-md group-hover:shadow-lg border border-border-color">
                            <img src="${article.image}" onerror="this.src='${PLACEHOLDER_IMAGE}'" loading="lazy" alt="${article.title}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                            <div class="absolute inset-0 bg-black/10 group-hover:bg-black/0 transition-colors"></div>
                            <div class="absolute bottom-3 left-3 bg-white/90 backdrop-blur text-bbcDark rounded-full p-2 shadow-sm group-hover:scale-110 transition-transform">
                                <i data-lucide="headset" class="w-4 h-4 fill-current"></i>
                            </div>
                            ${bookmarkBtn}
                        </div>
                        <h3 class="text-base font-bold leading-snug group-hover:text-bbcRed transition-colors ${textColor}">${article.title}</h3>
                        <div class="flex justify-between items-center mt-2 text-xs ${metaColor} font-medium">
                            <span class="bg-muted-bg px-2 py-0.5 rounded text-gray-700 dark:text-gray-300">${article.category}</span>
                            ${readTimeBadge}
                        </div>
                    </div>
                `;
            }

            return `
                <article class="group cursor-pointer flex flex-col h-full relative ${bgClass} rounded-2xl overflow-hidden ${shadowClass} transition-all duration-300 hover:-translate-y-1 border ${borderClass}" onclick="openDetail('${article.id}')">
                    <div class="overflow-hidden aspect-video relative">
                        <img src="${article.image}" onerror="this.src='${PLACEHOLDER_IMAGE}'" loading="lazy" alt="${article.title}" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                        <div class="absolute top-0 right-0 p-3 opacity-0 group-hover:opacity-100 transition-opacity z-10">
                            ${bookmarkBtn}
                        </div>
                        ${article.isVideo ? `<div class="absolute bottom-3 left-3 bg-black/60 text-white px-2 py-1 rounded-full backdrop-blur-sm text-xs flex items-center gap-1"><i data-lucide="play" class="w-3 h-3 fill-white"></i> ${t('video')}</div>` : ""}
                        <div class="absolute bottom-0 left-0 right-0 h-1/3 bg-gradient-to-t from-black/50 to-transparent opacity-60"></div>
                    </div>
                    <div class="flex flex-col flex-grow p-5">
                        <div class="mb-2 flex items-center gap-2">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-bbcRed bg-red-50 dark:bg-red-900/20 px-2 py-0.5 rounded">${article.category}</span>
                            <span class="text-[10px] text-muted-text">• ${article.timestamp || (state.language === 'bn' ? "সদ্য" : "Just now")}</span>
                        </div>
                        <h3 class="text-lg md:text-xl font-bold mb-3 leading-tight group-hover:text-bbcRed transition-colors ${textColor}">${article.title}</h3>
                        ${type === "hero-grid" && article.summary ? `<p class="${subTextColor} text-sm leading-relaxed mb-4 line-clamp-3">${article.summary}</p>` : ""}
                        <div class="mt-auto pt-3 border-t ${borderClass} flex items-center justify-between text-xs ${metaColor}">
                            <span class="flex items-center gap-1 group-hover:translate-x-1 transition-transform">${t('read_more')} <i data-lucide="chevron-right" class="w-3 h-3"></i></span>
                            ${readTimeBadge}
                        </div>
                    </div>
                </article>
            `;
        }

        function renderSection(section) {
            const isSectionDark = state.darkMode || section.style === "dark";
            let containerClass = "mb-12";
            if (section.style === "dark") {
                containerClass = `bg-[#121212] text-white p-8 md:p-10 rounded-3xl mb-12 shadow-2xl relative overflow-hidden`;
            }
            const titleColor = isSectionDark ? "text-white" : "text-bbcDark";
            const borderColor = isSectionDark
                ? "white"
                : section.highlightColor || "#B80000";

            let content = "";

            if (section.type === "hero-grid") {
                const hero = section.articles[0];
                const subs = section.articles.slice(1);
                content = `
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        ${hero ? `<div class="col-span-1 md:col-span-2 lg:col-span-2">${renderArticleCard(hero, "hero-grid", isSectionDark)}</div>` : ""}
                        <div class="col-span-1 md:col-span-2 lg:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-6">
                            ${subs.map((a) => renderArticleCard(a, "grid", isSectionDark)).join("")}
                        </div>
                    </div>
                `;
            } else if (section.type === "list") {
                content = `
                    <div class="bg-card p-6 rounded-2xl shadow-soft border border-border-color h-full">
                        <div class="flex items-center gap-3 mb-6 pb-4 border-b border-border-color">
                            <div class="w-1.5 h-6 rounded-full" style="background-color: ${section.highlightColor}"></div>
                            <h3 class="text-xl font-bold ${titleColor}">${section.title}</h3>
                        </div>
                        <ul class="space-y-4">
                            ${section.articles
                        .map(
                            (article) => `
                                <li class="group cursor-pointer p-2 rounded-xl hover:bg-muted-bg transition-colors" onclick="openDetail('${article.id}')">
                                    <div class="flex gap-4">
                                        ${article.image ? `<div class="w-24 h-24 flex-shrink-0 aspect-square overflow-hidden rounded-lg relative"><img src="${article.image}" onerror="this.src='${PLACEHOLDER_IMAGE}'" loading="lazy" alt="${article.title}" class="w-full h-full object-cover"></div>` : ""}
                                        <div class="flex-grow">
                                            <span class="text-[10px] font-bold text-bbcRed mb-1 block">${article.category}</span>
                                            <h4 class="text-sm font-bold leading-snug group-hover:text-bbcRed transition-colors mb-2 ${titleColor} line-clamp-2">${article.title}</h4>
                                            <div class="flex justify-between items-center text-xs text-muted-text">
                                                ${article.readTime ? `<span class="flex items-center gap-1"><i data-lucide="clock" class="w-3 h-3"></i> ${article.readTime}</span>` : ""}
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            `,
                        )
                        .join("")}
                        </ul>
                    </div>
                `;
            } else {
                const gridClass =
                    section.type === "reel"
                        ? "flex overflow-x-auto no-scrollbar gap-5 pb-8 snap-x scroll-smooth px-1"
                        : section.type === "audio"
                            ? "grid grid-cols-2 md:grid-cols-4 gap-6"
                            : "grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6";
                content = `
                    <div class="${gridClass}">
                        ${section.articles.map((a) => renderArticleCard(a, section.type, isSectionDark)).join("")}
                    </div>
                `;
            }

            return `
                <section class="${containerClass} animate-fade-in relative z-10">
                    <div class="flex justify-between items-center mb-8">
                        <h2 class="text-2xl font-bold flex items-center gap-3 ${titleColor}">
                            <span class="w-2 h-8 rounded-full" style="background-color: ${borderColor}"></span>
                            ${section.title}
                        </h2>
                        ${section.type !== "hero-grid" ? `<a href="?category=${getCategoryKey(section.associatedCategory, state.language)}" class="text-sm font-bold hover:text-bbcRed transition-colors flex items-center gap-1 opacity-80 hover:opacity-100 ${titleColor}">${t('all')} <i data-lucide="chevron-right" class="w-4 h-4"></i></a>` : ""}
                    </div>
                    ${content}
                    ${section.style === "dark" ? `<div class="absolute -right-20 -bottom-20 w-64 h-64 bg-white/5 rounded-full blur-3xl pointer-events-none"></div>` : ""}
                </section>
            `;
        }

        function renderHomeView() {
            if (state.isLoading) return renderSkeleton();

            let sectionsToRender = state.bbcData?.sections || [];
            const emptyStateColor = state.darkMode ? "text-white" : "text-bbcDark";

            if (state.category === "saved") {
                const savedIds = state.bookmarks;
                const all = state.bbcData?.sections?.flatMap((s) => s.articles) || [];
                const savedArticles = all.filter((a) => savedIds.includes(a.id));

                if (savedArticles.length === 0) {
                    return `
                        <div class="flex flex-col items-center justify-center py-32 text-center animate-fade-in">
                            <div class="bg-muted-bg p-6 rounded-full mb-4">
                                <i data-lucide="bookmark" class="w-12 h-12 text-gray-400"></i>
                            </div>
                            <h3 class="text-2xl font-bold mb-2 ${emptyStateColor}">${t('no_saved_articles')}</h3>
                            <p class="text-muted-text">${t('bookmark_your_favorites')}</p>
                        </div>
                    `;
                }
                sectionsToRender = [
                    {
                        id: "saved",
                        title: t('saved'),
                        type: "grid",
                        articles: savedArticles,
                    },
                ];
            } else if (state.category !== "home") {
                const target = CATEGORY_MAP[state.language][state.category] || state.category;
                sectionsToRender = state.bbcData.sections.filter(
                    (s) => s.associatedCategory === target || s.title.includes(target),
                );
            }

            if (!sectionsToRender || sectionsToRender.length === 0) {
                return `
                    <div class="flex flex-col items-center justify-center py-32 text-center animate-fade-in">
                        <div class="bg-muted-bg p-6 rounded-full mb-4">
                            <i data-lucide="newspaper" class="w-12 h-12 text-gray-400"></i>
                        </div>
                        <h3 class="text-2xl font-bold mb-2 ${emptyStateColor}">${t('no_news_in_this_category')}</h3>
                    </div>
                `;
            }

            let extras = "";
            if (state.category === "home") {
                const titleColor = state.darkMode ? "text-white" : "text-black";
                const worldNews = state.bbcData?.sections?.find(
                    (s) => s.id === "virginia",
                );
                const businessNews = state.bbcData?.sections?.find(
                    (s) => s.id === "vermont",
                );
                const newsCollection = state.bbcData?.sections?.find(
                    (s) => s.id === "wyoming",
                );

                const renderMiniList = (articles, colorClass) => `
                    <ul class="space-y-4">
                        ${articles
                        .map(
                            (a) => `
                            <li class="group cursor-pointer p-2 rounded-xl hover:bg-muted-bg transition-colors" onclick="openDetail('${a.id}')">
                                <div class="flex gap-4">
                                    <div class="w-20 h-20 rounded-lg overflow-hidden flex-shrink-0">
                                        <img src="${a.image}" onerror="this.src='${PLACEHOLDER_IMAGE}'" loading="lazy" alt="${a.title}" class="w-full h-full object-cover">
                                    </div>
                                    <div class="flex-grow">
                                        <h4 class="text-sm font-bold leading-snug group-hover:${colorClass} transition-colors ${titleColor} line-clamp-2">${a.title}</h4>
                                    </div>
                                </div>
                            </li>
                        `,
                        )
                        .join("")}
                    </ul>
                `;

                extras = `
                    <section class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-12 animate-fade-in">
                        <div class="lg:col-span-1 h-full">${newsCollection ? renderSection({ ...newsCollection, type: "list" }) : ""}</div>
                        <div class="lg:col-span-1 h-full">
                            <div class="bg-card p-6 rounded-2xl shadow-soft border border-border-color h-full">
                                <div class="flex items-center gap-3 mb-6 pb-4 border-b border-border-color">
                                    <div class="w-1.5 h-6 rounded-full bg-blue-500"></div>
                                    <h3 class="text-xl font-bold ${titleColor}">${t('more_world_news')}</h3>
                                </div>
                                ${worldNews ? renderMiniList(worldNews.articles.slice(0, 3), "text-blue-500") : ""}
                            </div>
                        </div>
                        <div class="lg:col-span-1 h-full">
                            <div class="bg-card p-6 rounded-2xl shadow-soft border border-border-color h-full">
                                <div class="flex items-center gap-3 mb-6 pb-4 border-b border-border-color">
                                    <div class="w-1.5 h-6 rounded-full bg-green-500"></div>
                                    <h3 class="text-xl font-bold ${titleColor}">${t('business_news')}</h3>
                                </div>
                                ${businessNews ? renderMiniList(businessNews.articles.slice(2, 5), "text-green-500") : ""}
                            </div>
                        </div>
                    </section>
                `;
            }

            const mainPadding = state.showBreaking ? "pt-12" : "";

            return `
                <main class="container mx-auto px-4 lg:px-8 max-w-[1380px] py-4 min-h-[60vh] animate-fade-in ${mainPadding}">
                    ${sectionsToRender.map(renderSection).join("")}
                    ${extras}
                </main>
            `;
        }

        function renderSkeleton() {
            return `
                <div class="container mx-auto px-4 lg:px-8 max-w-[1380px] py-6 min-h-[60vh]">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-8">
                        <div class="col-span-1 md:col-span-2 lg:col-span-2">
                            <div class="skeleton w-full aspect-video mb-5 rounded-xl"></div>
                            <div class="skeleton w-3/4 h-8 mb-3 rounded"></div>
                            <div class="skeleton w-full h-4 mb-2 rounded"></div>
                            <div class="skeleton w-2/3 h-4 rounded"></div>
                        </div>
                        <div class="col-span-1 md:col-span-2 lg:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-8">
                            ${[1, 2, 3, 4].map((i) => `<div><div class="skeleton w-full aspect-video mb-3 rounded-lg"></div><div class="skeleton w-full h-5 rounded"></div></div>`).join("")}
                        </div>
                    </div>
                </div>
            `;
        }

        function render() {
            const app = document.getElementById("app");
            const {
                view,
                showBreaking,
                user,
                darkMode,
                isMobileMenuOpen,
                isSearchOpen,
                isLoading,
                showBackToTop,
                formInputs,
                authMode,
                bookmarks,
            } = state;

            const headerHtml = renderHeader();
            const footerHtml = renderFooter();
            let mainHtml = "";

            if (isLoading) mainHtml = renderSkeleton();
            else if (view === "home") mainHtml = renderHomeView();

            if (!isLoading && view === "detail") mainHtml = renderDetailView();

            const mobileMenu = renderMobileMenu();
            const searchOverlay = renderSearchOverlay();
            const backToTop = renderBackToTop();

            app.innerHTML = `
                ${mobileMenu}
                ${searchOverlay}
                ${state.view === "home" && state.showBreaking ? renderBreakingNews() : ""}
                ${headerHtml}
                ${mainHtml}
                ${footerHtml}
                ${backToTop}
            `;

            lucide.createIcons();
        }

        function renderBreakingNews() {
            return `
                <div class="fixed top-0 left-0 right-0 bg-bbcRed text-white text-xs md:text-sm font-medium py-2 px-4 flex justify-between items-center z-[100]">
                    <div class="flex items-center gap-3 max-w-[1380px] mx-auto w-full px-2">
                        <span class="uppercase animate-pulse font-bold tracking-widest text-[10px] bg-white/20 px-2 py-0.5 rounded">${t('breaking')}</span>
                        <span class="truncate opacity-95 hover:opacity-100 cursor-pointer">${t('breaking_news')}</span>
                    </div>
                    <button onclick="setState({showBreaking: false})" class="hover:bg-black/20 rounded-full p-1 transition-colors ml-2"><i data-lucide="x" class="w-4 h-4"></i></button>
                </div>
            `;
        }

        function renderHeader() {
            const { user, isAdmin, darkMode, category } = state;
            
            // Build menu items from database categories plus special items
            const categoryItems = (state.bbcData?.categories || []).map(cat => ({
                label: state.language === 'bn' ? cat.title_bn : cat.title_en,
                id: cat.id
            }));
            
            const menuItems = [
                { label: t("home"), id: "home" },
                ...categoryItems,
                { label: t("saved"), id: "saved" },
            ];
            
            const navItems = menuItems.map((item) => `
                <a href="?category=${item.id}" class="nav-link flex-shrink-0 py-2.5 px-1 text-sm font-bold whitespace-nowrap transition-all hover:text-bbcRed ${category === item.id ? "active" : ""}">
                    ${item.label}
                </a>
            `).join("");

            return `
                <header class="border-b border-border-color sticky bg-white/90 dark:bg-[#121212]/90 backdrop-blur-md z-50 transition-colors duration-300 shadow-sm">
                    <div class="container mx-auto px-4 lg:px-8 max-w-[1380px]">
                        <div class="h-[70px] flex items-center justify-between">
                            <div class="flex items-center gap-6">
                                <button onclick="setState({isMobileMenuOpen: true})" class="p-2.5 hover:bg-muted-bg rounded-full text-gray-700 dark:text-gray-200 transition-colors btn-bounce">
                                    <i data-lucide="menu" class="w-6 h-6"></i>
                                </button>
                                <a href="#" onclick="event.preventDefault(); navigate('home')" class="block text-black dark:text-white transition-transform hover:scale-[1.02] active:scale-95 duration-300">
                                    <div class="flex items-center select-none gap-2 group">
                                        <span class="bg-bbcRed text-white px-2.5 py-0.5 font-bold text-xl rounded shadow-md group-hover:bg-[#d40000] transition-colors duration-300">B</span>
                                        <span class="font-bold text-2xl tracking-tight leading-none text-gray-900 dark:text-white group-hover:text-gray-700 dark:group-hover:text-gray-300 transition-colors">
                                            <span class="text-bbcRed">Breach</span>Times
                                        </span>
                                    </div>
                                </a>
                            </div>
                            <div class="flex items-center gap-2 md:gap-4">
                                <button onclick="toggleLanguage()" class="p-2.5 rounded-full hover:bg-muted-bg text-gray-600 dark:text-green-400 transition-all active:scale-90">
                                    <span class="text-sm font-bold">${state.language === 'bn' ? 'EN' : 'BN'}</span>
                                </button>
                                <button onclick="toggleTheme()" class="p-2.5 rounded-full hover:bg-muted-bg text-gray-600 dark:text-yellow-400 transition-all active:scale-90">
                                    <i data-lucide="${darkMode ? "sun" : "moon"}" class="w-5 h-5"></i>
                                </button>
                                <button onclick="setState({isSearchOpen: true})" class="p-2.5 hover:bg-muted-bg rounded-full text-gray-700 dark:text-white transition-all btn-bounce">
                                    <i data-lucide="search" class="w-5 h-5"></i>
                                </button>
                                <div id="weather-display" class="hidden lg:flex items-center gap-3 text-sm font-medium border-l border-border-color pl-5 ml-2 transition-colors">
                                    <div class="animate-pulse bg-muted-bg h-4 w-16 rounded"></div>
                                </div>
                                <div class="hidden md:flex gap-3 items-center">
                                    ${user
                    ? `
                                        ${isAdmin ? `<a href="admin/index.php" class="flex items-center gap-2 px-4 py-2 bg-bbcRed text-white rounded-full text-sm font-bold shadow-lg shadow-bbcRed/30 hover:bg-red-700 hover:scale-105 transition-all mr-2 btn-bounce"><i data-lucide="shield" class="w-4 h-4"></i> ${t('admin_panel')}</a>` : ""}
                                        <button onclick="handleLogout()" class="text-sm font-bold px-4 py-2 hover:bg-red-50 dark:hover:bg-red-900/20 text-bbcRed rounded-full transition-all flex items-center gap-2 btn-bounce">
                                            <div class="w-4 h-4 bg-bbcRed rounded-full text-white flex items-center justify-center text-[10px]">${user.charAt(0).toUpperCase()}</div> ${t('sign_out')}
                                        </button>
                                    `
                    : `
                                        <a href="login/" class="text-sm font-bold px-5 py-2.5 bg-bbcDark dark:bg-white text-white dark:text-black rounded-full hover:shadow-lg hover:-translate-y-0.5 transition-all btn-bounce">${t('sign_in')}</a>
                                    `
                }
                                </div>
                            </div>
                        </div>
                        <div class="relative group">
                             <nav class="flex overflow-x-auto no-scrollbar gap-8 mt-2 text-gray-700 dark:text-gray-300 pb-2 mask-linear-gradient scroll-smooth">
                                ${navItems}
                            </nav>
                        </div>
                    </div>
                </header>
                ${state.view === "home"
                    ? `
                <div class="py-8 flex flex-col items-center justify-center bg-transparent transition-colors px-4">
                    <div class="w-full max-w-[320px] md:max-w-[728px] h-[90px] flex flex-col items-center justify-center text-xs bg-gray-100 dark:bg-[#1a1a1a] text-gray-400 dark:text-gray-600 rounded-xl border border-gray-200 dark:border-gray-800/50 shadow-sm transition-all hover:shadow-md overflow-hidden">
                        <span class="text-[10px] uppercase tracking-widest mb-1 opacity-50">${t('advertisement')}</span>
                        <span class="font-medium text-center px-2">${t('ad_text')}</span>
                    </div>
                </div>`
                    : ""
                }
            `;
        }

        function renderFooter() {
            return `
                <footer class="pt-16 pb-8 border-t bg-card text-card-text transition-colors border-border-color">
                    <div class="container mx-auto px-4 lg:px-8 max-w-[1380px]">
                        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-12 border-b border-border-color pb-12 gap-8">
                            <div class="flex items-center select-none gap-2">
                                <span class="bg-bbcRed text-white px-3 py-1 font-bold text-2xl rounded shadow">B</span>
                                <span class="font-bold text-3xl tracking-tighter leading-none"><span class="text-bbcRed">Breach</span>Times</span>
                            </div>
                            <div class="flex gap-6">
                                <a href="#" class="p-2 bg-muted-bg rounded-full hover:bg-bbcRed transition-colors"><i data-lucide="facebook" class="w-5 h-5"></i></a>
                                <a href="#" class="p-2 bg-muted-bg rounded-full hover:bg-bbcRed transition-colors"><i data-lucide="twitter" class="w-5 h-5"></i></a>
                                <a href="#" class="p-2 bg-muted-bg rounded-full hover:bg-bbcRed transition-colors"><i data-lucide="youtube" class="w-5 h-5"></i></a>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-12">
                            <div class="col-span-1 md:col-span-2">
                                <h3 class="font-bold text-lg mb-6 flex items-center gap-2"><i data-lucide="mail" class="w-5 h-5 text-bbcRed"></i> ${t('newsletter')}</h3>
                                <p class="text-muted-text text-sm mb-4 max-w-sm">${t('subscribe_newsletter')}</p>
                                <div class="flex flex-col sm:flex-row gap-2 max-w-md">
                                    <input type="email" placeholder="${t('your_email')}" class="p-3 bg-muted-bg text-card-text rounded-lg border border-border-color focus:outline-none focus:border-bbcRed flex-grow">
                                    <button class="bg-bbcDark text-white dark:bg-white dark:text-black font-bold px-6 py-3 rounded-lg hover:opacity-90 transition-colors" onclick="showToastMsg(t('subscribed_successfully'))">${t('subscribe')}</button>
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-col md:flex-row justify-between items-center pt-8 border-t border-border-color text-xs text-muted-text">
                            <p>${t('copyright')}</p>
                        </div>
                    </div>
                </footer>
            `;
        }

        function renderMobileMenu() {
            const {
                isMobileMenuOpen,
                user,
                isAdmin,
                darkMode,
                category,
            } = state;
            
            // Build menu items from database categories plus special items
            const categoryItems = (state.bbcData?.categories || []).map(cat => ({
                label: state.language === 'bn' ? cat.title_bn : cat.title_en,
                id: cat.id
            }));
            
            const menuItems = [
                { label: t("home"), id: "home" },
                ...categoryItems,
                { label: t("saved"), id: "saved" },
            ];
            return `
                <div class="fixed top-0 left-0 bottom-0 z-[60] w-full sm:w-2/3 md:w-1/2 lg:w-1/4 bg-white/95 dark:bg-black/95 backdrop-blur-xl transition-all duration-300 transform ${isMobileMenuOpen ? "translate-x-0" : "-translate-x-full"}">
                    <div class="flex justify-between items-center p-6 border-b border-border-color">
                        <div class="font-bold text-2xl dark:text-white tracking-tight">${t('menu')}</div>
                        <button onclick="setState({isMobileMenuOpen: false})" class="p-2 hover:bg-muted-bg rounded-full transition-transform hover:rotate-90 dark:text-white btn-bounce"><i data-lucide="x" class="w-8 h-8"></i></button>
                    </div>
                    <div class="p-6 h-full overflow-y-auto pb-20 no-scrollbar">
                        <div class="mb-8 space-y-4">
                            ${user
                    ? `
                                <div class="flex flex-col gap-3">
                                    <div class="flex items-center gap-3 px-2 mb-2">
                                        <div class="w-10 h-10 rounded-full bg-bbcRed text-white flex items-center justify-center font-bold text-lg">${user.charAt(0).toUpperCase()}</div>
                                        <div class="flex flex-col"><span class="font-bold text-bbcDark dark:text-white text-sm">${t('welcome')}</span><span class="text-xs text-muted-text truncate max-w-[200px]">${user}</span></div>
                                    </div>
                                    ${isAdmin ? `<a href="admin/index.php" class="w-full py-3 bg-bbcRed text-white rounded-xl font-bold flex items-center justify-center gap-2 shadow-lg shadow-bbcRed/20 btn-bounce"><i data-lucide="shield" class="w-5 h-5"></i> ${t('admin_panel')}</a>` : ""}
                                    <button onclick="handleLogout(); setState({isMobileMenuOpen: false})" class="w-full py-3 bg-muted-bg text-bbcDark dark:text-white rounded-xl font-bold flex items-center justify-center gap-2 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-600 transition-colors btn-bounce"><i data-lucide="log-out" class="w-5 h-5"></i> ${t('sign_out')}</button>
                                </div>
                            `
                    : `
                                <div class="grid grid-cols-2 gap-4">
                                    <a href="login/" class="w-full py-3 bg-bbcDark dark:bg-white text-white dark:text-black rounded-xl font-bold shadow-lg btn-bounce text-center">${t('sign_in')}</a>
                                    <a href="register.php" class="w-full py-3 border border-bbcDark dark:border-white text-bbcDark dark:text-white rounded-xl font-bold hover:bg-muted-bg transition-colors btn-bounce text-center">${t('register')}</a>
                                </div>
                            `
                }
                        </div>
                        <ul class="space-y-2 font-bold text-xl text-bbcDark dark:text-gray-200">
                             ${menuItems.map((item) => `
                                <li class="border-b border-gray-100 dark:border-gray-800/50 pb-2 last:border-0">
                                    <a href="?category=${item.id}" class="w-full text-left py-4 flex justify-between items-center hover:text-bbcRed hover:pl-3 transition-all duration-300 group">
                                        <span>${item.label}</span>
                                        <i data-lucide="chevron-right" class="w-5 h-5 text-gray-300 group-hover:text-bbcRed transition-colors"></i>
                                    </a>
                                </li>
                            `).join("")}
                        </ul>
                    </div>
                </div>
            `;
        }

        function renderBackToTop() {
            return `
                <button id="back-to-top" onclick="window.scrollTo({ top: 0, behavior: 'smooth' })" class="fixed bottom-8 right-8 p-3 rounded-full shadow-xl z-50 transition-all duration-300 bg-black/80 backdrop-blur text-white hover:bg-black dark:bg-white/90 dark:text-black dark:hover:bg-white hover:scale-110 opacity-0 translate-y-10 pointer-events-none">
                    <i data-lucide="chevron-up" class="w-5 h-5"></i>
                </button>
            `;
        }
        function renderSearchOverlay() {
            const { isSearchOpen, searchQuery, searchResults, darkMode } = state;
            return `
                <div class="fixed inset-0 z-[70] bg-white/98 dark:bg-[#0f0f0f]/98 backdrop-blur-md overflow-y-auto transition-all duration-300 no-scrollbar ${isSearchOpen ? "opacity-100 visible" : "opacity-0 invisible"}">
                    <div class="max-w-[1000px] mx-auto p-6 pt-12">
                        <div class="flex justify-end mb-12">
                            <button onclick="setState({isSearchOpen: false})" class="p-3 bg-muted-bg rounded-full hover:bg-gray-200 dark:hover:bg-gray-700 text-black dark:text-white transition-all hover:rotate-90">
                                <i data-lucide="x" class="w-8 h-8"></i>
                            </button>
                        </div>
                        <div class="relative mb-16 group">
                            <i data-lucide="search" class="absolute left-0 top-1/2 transform -translate-y-1/2 text-gray-400 w-10 h-10 group-focus-within:text-bbcRed transition-colors"></i>
                            <input type="text" placeholder="${t('search_placeholder')}" value="${searchQuery}" 
                                oninput="handleSearch(this.value)"
                                class="w-full py-4 pl-14 text-4xl font-bold border-b-2 border-border-color focus:border-bbcRed dark:focus:border-bbcRed outline-none bg-transparent text-bbcDark dark:text-white placeholder-gray-300 dark:placeholder-gray-700 transition-colors">
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8" id="search-results-container">
                            ${searchResults.map((a) => renderArticleCard(a, "grid", darkMode)).join("")}
                        </div>
                    </div>
                </div>
            `;
        }

        function handleSearch(query) {
            state.searchQuery = query;
            if (query.length < 2) {
                state.searchResults = [];
            } else {
                const allArticles = state.bbcData?.sections?.flatMap((s) => s.articles) || [];
                state.searchResults = allArticles.filter((a) =>
                    a.title.toLowerCase().includes(query.toLowerCase()),
                );
            }
            document.getElementById("search-results-container").innerHTML =
                state.searchResults
                    .map((a) => renderArticleCard(a, "grid", state.darkMode))
                    .join("");
            lucide.createIcons();
        }

        function renderDetailView() {
            const all = state.bbcData?.sections?.flatMap((s) => s.articles) || [];
            const article =
                all.find((a) => a.id === state.selectedArticleId) || all[0];
            if (!article) return "";

            const isBookmarked = state.bookmarks.includes(article.id);
            const bookmarkFill = isBookmarked
                ? state.darkMode
                    ? "white"
                    : "black"
                : "none";
            const titleColor = state.darkMode ? "text-white" : "text-bbcDark";
            const metaColor = state.darkMode ? "text-gray-400" : "text-gray-500";
            const proseColor = state.darkMode ? "text-gray-300" : "text-gray-800";

            return `
                <main class="bg-page min-h-screen font-sans animate-fade-in pb-12">
                    <div class="max-w-[1280px] mx-auto px-4 py-8">
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
                            <div class="lg:col-span-8">
                                <div class="bg-card p-6 md:p-10 rounded-2xl shadow-soft border border-border-color">
                                    <div class="mb-6">
                                        <span class="bg-bbcRed text-white text-xs font-bold px-3 py-1 rounded-full mb-3 inline-block">${article.category}</span>
                                        <h1 class="text-3xl md:text-5xl font-bold leading-tight mb-4 ${titleColor}">${article.title}</h1>
                                        <div class="flex flex-wrap items-center gap-4 text-sm ${metaColor} font-medium">
                                            <span class="flex items-center gap-1.5"><i data-lucide="clock" class="w-4 h-4"></i> ${article.timestamp || (state.language === 'bn' ? "সদ্য" : "Just now")}</span>
                                            <span class="flex items-center gap-1.5"><i data-lucide="file-text" class="w-4 h-4"></i> ${article.readTime || (state.language === 'bn' ? "৩ মিনিট" : "3 min")}</span>
                                        </div>
                                    </div>
                                    <div class="mb-10 relative aspect-video bg-muted-bg rounded-2xl overflow-hidden shadow-lg">
                                        <img src="${article.image}" onerror="this.src='${PLACEHOLDER_IMAGE}'" class="w-full h-full object-cover">
                                        ${article.isVideo ? `<div class="absolute inset-0 flex items-center justify-center bg-black/20 backdrop-blur-[2px]"><div class="bg-white/90 rounded-full p-5 shadow-2xl animate-pulse"><i data-lucide="play" class="w-10 h-10 fill-black text-black ml-1"></i></div></div>` : ""}
                                    </div>
                                    <div class="flex items-center justify-between border-y border-border-color py-4 mb-8">
                                        <div class="flex gap-3">
                                            <div class="flex items-center gap-1 bg-muted-bg rounded-lg p-1">
                                                <button onclick="setFontSize('sm')" class="w-8 h-8 flex items-center justify-center hover:bg-card rounded transition-colors text-xs font-bold text-card-text">A</button>
                                                <button onclick="setFontSize('md')" class="w-8 h-8 flex items-center justify-center hover:bg-card rounded transition-colors text-sm font-bold text-card-text">A</button>
                                                <button onclick="setFontSize('lg')" class="w-8 h-8 flex items-center justify-center hover:bg-card rounded transition-colors text-lg font-bold text-card-text">A</button>
                                            </div>
                                        </div>
                                        <div class="flex gap-3">
                                             <button onclick="handleShare()" class="flex items-center gap-2 px-4 py-2 rounded-full bg-muted-bg hover:bg-bbcRed hover:text-white transition-all text-sm font-bold text-card-text">
                                                <i data-lucide="share-2" class="w-4 h-4"></i> ${t('share')}
                                             </button>
                                             <button onclick="toggleBookmark('${article.id}')" class="p-2.5 rounded-full bg-muted-bg hover:bg-bbcRed hover:text-white text-black dark:text-white transition-all shadow-sm flex items-center justify-center group"><i data-lucide="bookmark" class="w-5 h-5" fill="${bookmarkFill}"></i></button>
                                        </div>
                                    </div>
                                    <div class="prose max-w-none font-size-${state.fontSize} space-y-8 ${proseColor} transition-all duration-300">
                                        ${article.content || `<p>${t('details_coming_soon')}</p>`}
                                    </div>

                                </div>

                                <div class="mt-8 bg-card p-6 md:p-10 rounded-2xl shadow-soft border border-border-color">
                                    <h3 class="text-2xl font-bold mb-6 text-card-text flex items-center gap-2"><i data-lucide="message-circle" class="w-6 h-6 text-bbcRed"></i> ${t('comments')}</h3>
                                    <div class="mb-8">
                                        <div class="relative">
                                            <textarea id="comment-input" placeholder="${t('post_comment_placeholder')}" class="w-full p-4 rounded-xl border border-border-color bg-muted-bg text-card-text focus:ring-2 focus:ring-bbcRed/20 focus:border-bbcRed outline-none transition-all resize-none shadow-inner" rows="3"></textarea>
                                        </div>
                                        <div class="flex justify-end mt-3">
                                            <button onclick="postComment('${article.id}')" class="bg-bbcDark dark:bg-white text-white dark:text-black px-6 py-2.5 rounded-full font-bold hover:shadow-lg hover:-translate-y-0.5 transition-all text-sm">${t('post_comment')}</button>
                                        </div>
                                    </div>
                                    <div class="space-y-6">
                                        ${article.comments &&
                    article.comments.length > 0
                    ? article.comments
                        .map(
                            (c) => `
                                            <div class="bg-muted-bg p-4 rounded-xl">
                                                <div class="flex items-center gap-3 mb-2">
                                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-bbcRed to-orange-500 flex items-center justify-center font-bold text-white text-sm shadow-md">${c.user.charAt(0)}</div>
                                                    <div>
                                                        <span class="font-bold text-sm text-card-text block">${c.user}</span>
                                                        <span class="text-xs text-muted-text">${c.time}</span>
                                                    </div>
                                                </div>
                                                <p class="text-sm text-card-text ml-12 leading-relaxed bg-card p-3 rounded-lg rounded-tl-none border border-border-color">${c.text}</p>
                                            </div>
                                        `,
                        )
                        .join("")
                    : `<div class="text-center py-8 text-muted-text">${t('no_comments_yet')}</div>`
                }
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </main>
            `;
        }

        async function handleLogout() {
            try {
                await fetch('api/logout.php');
                window.location.reload();
            } catch (e) {
                console.error(e);
            }
        }

        function renderBackToTop() {
            return `
                <button id="back-to-top" onclick="window.scrollTo({ top: 0, behavior: 'smooth' })" class="fixed bottom-8 right-8 p-3 rounded-full shadow-xl z-50 transition-all duration-300 bg-black/80 backdrop-blur text-white hover:bg-black dark:bg-white/90 dark:text-black dark:hover:bg-white hover:scale-110 opacity-0 translate-y-10 pointer-events-none">
                    <i data-lucide="chevron-up" class="w-5 h-5"></i>
                </button>
            `;
        }



        init();
    </script>
</body>

</html>