<?php
session_start();
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

    <link href="assets/css/styles.css" rel="stylesheet" />

    <script src="assets/js/lucide.js"></script>
    <!-- QuillJS Script -->
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
</head>

<body
    class="bg-page text-page-text font-sans transition-colors duration-500 antialiased selection:bg-bbcRed selection:text-white">
    <div id="progress-bar" class="fixed top-0 left-0 h-1 bg-bbcRed z-[100] shadow-[0_0_10px_var(--color-bbcRed)]" style="width: 0%">
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
                subscribe_newsletter: "Subscribe to our newsletter for the latest news and analysis first.",
                your_email: "Your email address",
                subscribe: "Subscribe",
                subscribed_successfully: "Subscribed successfully!",
                copyright: "© 2025 BreachTimes. All rights reserved.",
                welcome: "Welcome",
                admin_panel: "Admin Panel",
                dashboard: "Dashboard",
                sign_out: "Sign Out",
                sign_in: "Sign In",
                register: "Register",
                image_upload_success: "Image uploaded successfully!",
                data_restored: "Data restored successfully!",
                invalid_file_format: "Invalid file format!",
                file_read_error: "Error reading file!",
                data_downloaded: "Data downloaded!",
                // New translations for timestamp formatting
                just_now: "Just now",
                minute: "minute",
                minutes: "minutes",
                hour: "hour",
                hours: "hours",
                day: "day",
                days: "days",
            },
            bn: {
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
                subscribe_newsletter: "সবার আগে সর্বশেষ সংবাদ এবং বিশ্লেষণ পেতে আপনার ইমেইল দিয়ে সাবস্ক্রাইব করুন।",
                your_email: "আপনার ইমেইল ঠিকানা",
                subscribe: "সাবস্ক্রাইব",
                subscribed_successfully: "সাবস্ক্রাইব করা হয়েছে!",
                copyright: "© ২০২৫ ব্রিচটাইমস। সর্বস্বত্ব সংরক্ষিত।",
                welcome: "স্বাগতম",
                admin_panel: "অ্যাডমিন প্যানেল",
                dashboard: "ড্যাশবোর্ড",
                sign_out: "সাইন আউট",
                sign_in: "সাইন ইন",
                register: "নিবন্ধন",
                image_upload_success: "ছবি আপলোড সম্পন্ন!",
                data_restored: "ডাটা রিস্টোর সফল হয়েছে!",
                invalid_file_format: "ভুল ফরম্যাটের ফাইল!",
                file_read_error: "ফাইল রিড করতে সমস্যা হয়েছে!",
                data_downloaded: "ডাটা ডাউনলোড হয়েছে!",
                // New translations for timestamp formatting
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
            // This function seems to map category titles back to keys, but might need adjustment
            // based on actual data structure and usage. For now, assuming it's for display mapping.
            for (const key in CATEGORY_MAP[lang]) {
                if (CATEGORY_MAP[lang][key] === value) {
                    return key;
                }
            }
            return value; // Return original value if not found
        }

        const state = {
            bbcData: null,
            view: "home",
            category: "<?php echo htmlspecialchars($initialCategory); ?>",
            bookmarks: [],
            user: <?php echo json_encode($user); ?>,
            isAdmin: <?php echo json_encode($isAdmin); ?>,
            fontSize: "md",
            isLoading: true,
            darkMode: false,
            language: "bn",
            isMobileMenuOpen: false,
            isSearchOpen: false,
            searchQuery: "",
            searchResults: [],
            scrollProgress: 0,
        };
        
        const t = (key) => translations[state.language][key] || key;

        // --- HTML Escaping and Timestamp Formatting Functions ---

        // Function to escape HTML special characters to prevent XSS
        function escapeHtml(unsafe) {
            if (typeof unsafe !== 'string') {
                return unsafe; // Return non-strings as is
            }
            // Use DOM manipulation for reliable escaping
            const div = document.createElement('div');
            div.appendChild(document.createTextNode(unsafe));
            return div.innerHTML;
        }

        // Function to format timestamps into human-readable relative times
        function formatTimestamp(timestampString) {
            if (!timestampString) return '';

            let date;
            try {
                // Attempt to parse ISO 8601 format (e.g., from JS Date.toISOString())
                // or MySQL DATETIME/TIMESTAMP format 'YYYY-MM-DD HH:MM:SS'
                date = new Date(timestampString);
                
                // Check if date is valid after initial parsing
                if (isNaN(date.getTime())) {
                    // Try parsing MySQL format 'YYYY-MM-DD HH:MM:SS'
                    const parts = timestampString.match(/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/);
                    if (parts) {
                        // Month is 0-indexed in JS Date constructor (parts[2] is month, so part[2]-1)
                        date = new Date(parts[1], parts[2] - 1, parts[3], parts[4], parts[5], parts[6]);
                    } else {
                        // If both parsing methods fail, return the original string
                        console.warn("Could not parse timestamp format:", timestampString);
                        return timestampString;
                    }
                }
            } catch (e) {
                console.error("Error parsing timestamp:", timestampString, e);
                return timestampString; // Return original string if any error occurs
            }

            // Check validity again after potential MySQL format parsing
            if (isNaN(date.getTime())) {
                console.warn("Parsed date is invalid:", timestampString);
                return timestampString;
            }

            const now = new Date();
            const secondsPast = (now.getTime() - date.getTime()) / 1000;

            if (secondsPast < 60) {
                return t('just_now');
            } else if (secondsPast < 3600) {
                const minutes = Math.floor(secondsPast / 60);
                return `${minutes} ${minutes === 1 ? t('minute') : t('minutes')}`;
            } else if (secondsPast < 86400) {
                const hours = Math.floor(secondsPast / 3600);
                return `${hours} ${hours === 1 ? t('hour') : t('hours')}`;
            } else if (secondsPast < 2592000) { // ~30 days
                const days = Math.floor(secondsPast / 86400);
                return `${days} ${days === 1 ? t('day') : t('days')}`;
            } else {
                // For older dates, format using locale-specific options
                const options = {
                    year: 'numeric',
                    month: state.language === 'bn' ? 'long' : 'short', // Use 'long' for Bengali month names
                    day: 'numeric'
                };
                const locale = state.language === 'bn' ? 'bn-BD' : 'en-US';
                return date.toLocaleDateString(locale, options);
            }
        }

        // --- End Timestamp Formatting Functions ---


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


        async function fetchBbcData() {
            setState({ isLoading: true });
            try {
                const urlParams = new URLSearchParams(window.location.search);
                const category = urlParams.get('category');
                const lang = state.language;
                
                let apiUrl = `api/get_data.php?lang=${lang}`;
                if (category) {
                    apiUrl += `&category=${encodeURIComponent(category)}`;
                }
                // Add pagination/limit parameters if needed from state
                if (state.limit) apiUrl += `&limit=${state.limit}`;
                if (state.page) apiUrl += `&page=${state.page}`;

                const response = await fetch(apiUrl);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const data = await response.json();
                setState({ bbcData: data, isLoading: false });
            } catch (error) {
                console.error("Error fetching data:", error);
                setState({ isLoading: false });
                // Optionally show an error message to the user
                showToastMsg(t('server_error'));
            }
        }

        function showToastMsg(msg) {
            const container = document.getElementById("toast-container");
            const toast = document.createElement("div");
            toast.className =
                "toast-enter fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-black/80 dark:bg-white/90 backdrop-blur text-white dark:text-black px-6 py-3 rounded-full shadow-lg font-bold flex items-center gap-2 mb-2 text-sm w-auto";
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
            setState({ darkMode: newMode }, false); // Render call is inside the if/else
            if (newMode) {
                document.documentElement.classList.add("dark");
                localStorage.setItem("breachtimes-theme", "dark");
            } else {
                document.documentElement.classList.remove("dark");
                localStorage.setItem("breachtimes-theme", "light");
            }
            render(); // Re-render to apply theme changes to components
        }

        function toggleLanguage() {
            const newLang = state.language === "bn" ? "en" : "bn";
            setState({ language: newLang }, false); // Temporarily update state
            localStorage.setItem("breachtimes-language", newLang);
            document.documentElement.lang = newLang;
            
            // Re-fetch data to get translations and content in the new language
            fetchBbcData();
            fetchWeather(); // Weather API might also have language preferences, though not used here
            
            // Re-render immediately to update UI elements like menu and buttons
            render(); 
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
                // For category navigation, update the category state and re-render home view
                // Push state to URL for bookmarking/sharing
                if (pushState) {
                    const url = new URL(window.location.href);
                    if (id === "home") {
                        url.searchParams.delete('category');
                    } else {
                        url.searchParams.set('category', id);
                    }
                    window.history.pushState({}, '', url);
                }
                setState({
                    category: id,
                    view: "home", // Ensure we are in the home view to render sections
                    isMobileMenuOpen: false,
                    isSearchOpen: false,
                });
            }
            window.scrollTo(0, 0);
        }

        function openDetail(id, pushState = true) {
            // Navigate to dedicated article page
            // window.location.href = `read?id=${id}&lang=${state.language}`; // This causes a full page reload, losing JS state

            // Instead of full reload, navigate using history API and fetch data if necessary
            // For simplicity and given the current structure, a full reload might be intended
            // If client-side routing is desired, this would need significant changes to App structure.
            // Let's stick to full reload for now for simplicity matching original behavior.
            window.location.href = `read?id=${id}&lang=${state.language}`;
        }

        function handleShare() {
            if (navigator.share) {
                navigator
                    .share({
                        title: document.title, // Consider escaping document.title if it's user-generated
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
                ? state.user.split("@")[0] // Simple way to get username from email
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
                    // Reload page to show new comment
                    setTimeout(() => location.reload(), 1000);
                } else {
                    // Display specific error from server if available
                    const errorMsg = result.error || t('error_occurred');
                    showToastMsg(errorMsg);
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

            const bgClass = isSectionDark ? "bg-card-elevated" : "bg-card-elevated";

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

            // Format timestamp for display
            const displayTimestamp = formatTimestamp(article.published_at);
            const readTimeBadge = article.readTime
                ? `
                <span class="text-[10px] uppercase tracking-wider opacity-80 flex items-center gap-1 font-bold">
                    <i data-lucide="clock" class="w-3 h-3"></i> ${escapeHtml(article.readTime)}
                </span>
            `
                : "";

            if (type === "reel") {
                return `
                    <div class="flex-shrink-0 w-[280px] group cursor-pointer snap-start transform transition-all duration-300 hover:-translate-y-1" onclick="openDetail('${article.id}')">
                        <div class="aspect-[9/16] overflow-hidden relative rounded-2xl shadow-lg border border-border-color">
                            <img src="${article.image}" onerror="this.src='${PLACEHOLDER_IMAGE}'" loading="lazy" alt="${escapeHtml(article.title)}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/20 to-transparent opacity-90"></div>
                            <div class="absolute bottom-5 left-5 right-5 text-white">
                                <span class="bg-bbcRed text-white text-[10px] px-2 py-0.5 rounded font-bold mb-2 inline-block">${escapeHtml(article.category)}</span>
                                <h3 class="font-bold text-lg leading-tight mb-1 group-hover:text-gray-200 transition-colors">${escapeHtml(article.title)}</h3>
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
                            <img src="${article.image}" onerror="this.src='${PLACEHOLDER_IMAGE}'" loading="lazy" alt="${escapeHtml(article.title)}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                            <div class="absolute inset-0 bg-black/10 group-hover:bg-black/0 transition-colors"></div>
                            <div class="absolute bottom-3 left-3 bg-white/90 backdrop-blur text-bbcDark rounded-full p-2 shadow-sm group-hover:scale-110 transition-transform">
                                <i data-lucide="headset" class="w-4 h-4 fill-current"></i>
                            </div>
                            ${bookmarkBtn}
                        </div>
                        <h3 class="text-base font-bold leading-snug group-hover:text-bbcRed transition-colors ${textColor}">${escapeHtml(article.title)}</h3>
                        <div class="flex justify-between items-center mt-2 text-xs ${metaColor} font-medium">
                            <span class="bg-muted-bg px-2 py-0.5 rounded text-gray-700 dark:text-gray-300">${escapeHtml(article.category)}</span>
                            ${readTimeBadge}
                        </div>
                    </div>
                `;
            }

            return `
                <article class="group cursor-pointer flex flex-col h-full relative ${bgClass} rounded-2xl overflow-hidden ${shadowClass} transition-all duration-300 hover:-translate-y-1 border ${borderClass}" onclick="openDetail('${article.id}')">
                    <div class="overflow-hidden aspect-video relative">
                        <img src="${article.image}" onerror="this.src='${PLACEHOLDER_IMAGE}'" loading="lazy" alt="${escapeHtml(article.title)}" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                        <div class="absolute top-0 right-0 p-3 opacity-0 group-hover:opacity-100 transition-opacity z-10">
                            ${bookmarkBtn}
                        </div>
                        ${article.isVideo ? `<div class="absolute bottom-3 left-3 bg-black/60 text-white px-2 py-1 rounded-full backdrop-blur-sm text-xs flex items-center gap-1"><i data-lucide="play" class="w-3 h-3 fill-white"></i> ${t('video')}</div>` : ""}
                        <div class="absolute bottom-0 left-0 right-0 h-1/3 bg-gradient-to-t from-black/50 to-transparent opacity-60"></div>
                    </div>
                    <div class="flex flex-col flex-grow p-5">
                        <div class="mb-2 flex items-center gap-2">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-bbcRed bg-red-50 dark:bg-red-900/20 px-2 py-0.5 rounded">${escapeHtml(article.category)}</span>
                            <span class="text-[10px] text-muted-text">• ${displayTimestamp}</span>
                        </div>
                        <h3 class="text-lg md:text-xl font-bold mb-3 leading-tight group-hover:text-bbcRed transition-colors ${textColor}">${escapeHtml(article.title)}</h3>
                        ${type === "hero-grid" && article.summary ? `<p class="${subTextColor} text-sm leading-relaxed mb-4 line-clamp-3">${escapeHtml(article.summary)}</p>` : ""}
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
                containerClass = `bg-card-elevated text-white p-8 md:p-10 rounded-3xl mb-12 shadow-2xl relative overflow-hidden`;
            }
            const titleColor = isSectionDark ? "text-white" : "text-bbcDark";
            const borderColor = isSectionDark
                ? "white"
                : section.highlightColor || "var(--color-bbcRed)";

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
                            <h3 class="text-xl font-bold ${titleColor}">${escapeHtml(section.title)}</h3>
                        </div>
                        <ul class="space-y-4">
                            ${section.articles
                        .map(
                            (article) => `
                                <li class="group cursor-pointer p-2 rounded-xl hover:bg-muted-bg transition-colors" onclick="openDetail('${article.id}')">
                                    <div class="flex gap-4">
                                        ${article.image ? `<div class="w-24 h-24 flex-shrink-0 aspect-square overflow-hidden rounded-lg relative"><img src="${article.image}" onerror="this.src='${PLACEHOLDER_IMAGE}'" loading="lazy" alt="${escapeHtml(article.title)}" class="w-full h-full object-cover"></div>` : ""}
                                        <div class="flex-grow">
                                            <span class="text-[10px] font-bold text-bbcRed mb-1 block">${escapeHtml(article.category)}</span>
                                            <h4 class="text-sm font-bold leading-snug group-hover:text-bbcRed transition-colors mb-2 ${titleColor}">${escapeHtml(article.title)}</h4>
                                            <div class="flex justify-between items-center text-xs text-muted-text">
                                                ${article.readTime ? `<span class="flex items-center gap-1"><i data-lucide="clock" class="w-3 h-3"></i> ${escapeHtml(article.readTime)}</span>` : ""}
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
                </section>
            `;
        }

        function renderHomeView() {
            if (state.isLoading) return renderSkeleton();

            let sectionsToRender = state.bbcData?.sections || [];
            const emptyStateColor = state.darkMode ? "text-white" : "text-bbcDark";

            if (state.category === "saved") {
                const savedIds = state.bookmarks;
                const allArticles = state.bbcData?.sections?.flatMap((s) => s.articles) || [];
                const savedArticles = allArticles.filter((a) => savedIds.includes(a.id));

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
                // Render saved articles as a single section
                sectionsToRender = [
                    {
                        id: "saved-articles-section", // Unique ID for this dynamic section
                        title: t('saved'),
                        type: "grid", // Or 'list', 'grid' etc. 'grid' is a safe default
                        articles: savedArticles,
                        style: state.darkMode ? "dark" : "light", // Apply theme style
                        highlightColor: "var(--color-bbcRed)", // BBC Red
                    },
                ];
            } else if (state.category !== "home") {
                // Filter sections based on the selected category (using associatedCategory or section title)
                // Note: getCategoryKey might need to be used to map display name back to internal ID/title for filtering
                const targetCategoryTitle = CATEGORY_MAP[state.language][state.category] || state.category; // Trying to get the actual category title for comparison
                
                sectionsToRender = state.bbcData.sections.filter(
                    (s) => s.associatedCategory === targetCategoryTitle || s.title === targetCategoryTitle,
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
                const worldNewsSection = state.bbcData?.sections?.find(
                    (s) => s.id === "virginia", // Example section IDs
                );
                const businessNewsSection = state.bbcData?.sections?.find(
                    (s) => s.id === "vermont",
                );
                const newsCollectionSection = state.bbcData?.sections?.find(
                    (s) => s.id === "wyoming",
                );

                // Helper to render mini articles list for 'extras' sections
                const renderMiniList = (articles, colorClass, sectionStyle) => `
                    <ul class="space-y-4">
                        ${articles.map((a) => `
                            <li class="group cursor-pointer p-2 rounded-xl hover:bg-muted-bg transition-colors" onclick="openDetail('${a.id}')">
                                <div class="flex gap-4">
                                    ${a.image ? `<div class="w-20 h-20 flex-shrink-0 aspect-square overflow-hidden rounded-lg relative"><img src="${a.image}" onerror="this.src='${PLACEHOLDER_IMAGE}'" loading="lazy" alt="${escapeHtml(a.title)}" class="w-full h-full object-cover"></div>` : ""}
                                    <div class="flex-grow">
                                        <h4 class="text-sm font-bold leading-snug group-hover:${colorClass} transition-colors ${titleColor} line-clamp-2">${escapeHtml(a.title)}</h4>
                                    </div>
                                </div>
                            </li>
                        `).join("")}
                    </ul>
                `;

                // Ensure sections are rendered with appropriate style
                const renderStyledSection = (sectionData, type = "grid") => {
                    if (!sectionData) return "";
                    return renderSection({ ...sectionData, type: type });
                };

                extras = `
                    <section class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-12 animate-fade-in">
                        <div class="lg:col-span-1 h-full">${renderStyledSection(newsCollectionSection, "list")}</div>
                        <div class="lg:col-span-1 h-full">
                            <div class="bg-card p-6 rounded-2xl shadow-soft border border-border-color h-full">
                                <div class="flex items-center gap-3 mb-6 pb-4 border-b border-border-color">
                                    <div class="w-1.5 h-6 rounded-full bg-blue-500"></div>
                                    <h3 class="text-xl font-bold ${titleColor}">${t('more_world_news')}</h3>
                                </div>
                                ${worldNewsSection ? renderMiniList(worldNewsSection.articles.slice(0, 3), "text-blue-500", state.darkMode ? "dark" : "light") : ""}
                            </div>
                        </div>
                        <div class="lg:col-span-1 h-full">
                            <div class="bg-card p-6 rounded-2xl shadow-soft border border-border-color h-full">
                                <div class="flex items-center gap-3 mb-6 pb-4 border-b border-border-color">
                                    <div class="w-1.5 h-6 rounded-full bg-green-500"></div>
                                    <h3 class="text-xl font-bold ${titleColor}">${t('business_news')}</h3>
                                </div>
                                ${businessNewsSection ? renderMiniList(businessNewsSection.articles.slice(2, 5), "text-green-500", state.darkMode ? "dark" : "light") : ""}
                            </div>
                        </div>
                    </section>
                `;
            }

            const mainPadding = ""; // Adjust if needed, currently no extra padding applied

            return `
                <main class="container mx-auto px-4 lg:px-8 max-w-[1380px] py-4 min-h-[60vh] animate-fade-in-up ${mainPadding}">
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
                user,
                isAdmin,
                darkMode,
                isMobileMenuOpen,
                isSearchOpen,
                isLoading,
                category, // Needed for header active state
            } = state;

            const headerHtml = renderHeader();
            const footerHtml = renderFooter();
            let mainHtml = "";

            if (isLoading) mainHtml = renderSkeleton();
            else if (view === "home") mainHtml = renderHomeView();
            // Add cases for other views like 'admin' if they exist and need rendering here

            const mobileMenu = renderMobileMenu();
            const searchOverlay = renderSearchOverlay();
            const backToTop = renderBackToTop();

            app.innerHTML = `
                ${mobileMenu}
                ${searchOverlay}
            
                ${headerHtml}
                ${mainHtml}
                ${footerHtml}
                ${backToTop}
            `;

            lucide.createIcons(); // Re-initialize icons after rendering
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
                <a href="?category=${item.id}" onclick="event.preventDefault(); navigate('${item.id}')" class="nav-link flex-shrink-0 py-2.5 px-1 text-sm font-bold whitespace-nowrap transition-all hover:text-bbcRed ${category === item.id ? "active" : ""}">
                    ${escapeHtml(item.label)}
                </a>
            `).join("");

            return `
                <header class="border-b border-border-color sticky top-0 bg-white/90 dark:bg-[#121212]/90 backdrop-blur-md z-50 transition-colors duration-300 shadow-sm">
                    <div class="container mx-auto px-4 lg:px-8 max-w-[1380px]">
                        <div class="h-[70px] flex items-center justify-between">
                            <div class="flex items-center gap-3 md:gap-6">
                                <button onclick="setState({isMobileMenuOpen: true})" class="p-2 md:p-2.5 hover:bg-muted-bg rounded-full text-gray-700 dark:text-gray-200 transition-colors btn-bounce">
                                    <i data-lucide="menu" class="w-6 h-6"></i>
                                </button>
                                <a href="#" onclick="event.preventDefault(); navigate('home')" class="block text-black dark:text-white transition-transform hover:scale-[1.02] active:scale-95 duration-300">
                                    <div class="flex items-center select-none gap-2 group">
                                        <span class="bg-bbcRed text-white px-2.5 py-0.5 font-bold text-lg md:text-xl rounded shadow-md group-hover:bg-[var(--color-bbcRed-hover)] transition-colors duration-300">B</span>
                                        <span class="font-bold text-lg md:text-2xl tracking-tight leading-none text-gray-900 dark:text-white group-hover:text-gray-700 dark:group-hover:text-gray-300 transition-colors">
                                            BT
                                        </span>
                                    </div>
                                </a>
                            </div>
                            <div class="flex items-center gap-2 md:gap-4">
                                <button onclick="toggleLanguage()" class="p-2 md:p-2.5 rounded-full hover:bg-muted-bg text-gray-600 dark:text-green-400 transition-all active:scale-90">
                                    <span class="text-sm font-bold">${state.language === 'bn' ? 'EN' : 'BN'}</span>
                                </button>
                                <button onclick="toggleTheme()" class="p-2 md:p-2.5 rounded-full hover:bg-muted-bg text-gray-600 dark:text-yellow-400 transition-all active:scale-90">
                                    <i data-lucide="${darkMode ? "sun" : "moon"}" class="w-5 h-5"></i>
                                </button>
                                <button onclick="setState({isSearchOpen: true})" class="p-2 md:p-2.5 hover:bg-muted-bg rounded-full text-gray-700 dark:text-white transition-all btn-bounce">
                                    <i data-lucide="search" class="w-5 h-5"></i>
                                </button>
                                <div id="weather-display" class="hidden lg:flex items-center gap-3 text-sm font-medium border-l border-border-color pl-5 ml-2 transition-colors">
                                    <div class="animate-pulse bg-muted-bg h-4 w-16 rounded"></div>
                                </div>
                                <div class="hidden md:flex gap-3 items-center">
                                    ${user
                    ? `
                                        ${isAdmin ? `<a href="admin/index.php" class="flex items-center gap-2 px-4 py-2 bg-bbcRed text-white rounded-full text-sm font-bold shadow-lg shadow-bbcRed/30 hover:bg-red-700 hover:scale-105 transition-all mr-2 btn-bounce"><i data-lucide="shield" class="w-4 h-4"></i> ${t('admin_panel')}</a>` : `<a href="dashboard/" class="flex items-center gap-2 px-4 py-2 bg-bbcRed text-white rounded-full text-sm font-bold shadow-lg shadow-bbcRed/30 hover:bg-red-700 hover:scale-105 transition-all mr-2 btn-bounce"><i data-lucide="layout-dashboard" class="w-4 h-4"></i> ${t('dashboard')}</a>`}
                                        <button onclick="handleLogout()" class="text-sm font-bold px-4 py-2 hover:bg-red-50 dark:hover:bg-red-900/20 text-bbcRed rounded-full transition-all flex items-center gap-2 btn-bounce">
                                            <div class="w-4 h-4 bg-bbcRed rounded-full text-white flex items-center justify-center text-[10px]">${escapeHtml(user.charAt(0).toUpperCase())}</div> ${t('sign_out')}
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
                
            `;
        }

        function renderFooter() {
            return `
                <footer class="pt-16 pb-8 bg-card text-card-text transition-colors border-t border-border-color">
                    <div class="container mx-auto px-4 lg:px-8 max-w-[1380px]">
                        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-12 border-b border-border-color pb-12 gap-8">
                            <div class="flex items-center select-none gap-2">
                                <span class="bg-bbcRed text-white px-3 py-1 font-bold text-2xl rounded shadow">B</span>
                                <span class="font-bold text-3xl tracking-tighter leading-none">BT</span>
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
                <div class="fixed top-0 left-0 bottom-0 z-[60] w-full sm:w-2/3 md:w-1/2 lg:w-1/4 bg-white/95 dark:bg-black/95 backdrop-blur-xl transition-all duration-300 transform ${isMobileMenuOpen ? "translate-x-0 animate-slide-in-right" : "-translate-x-full"}">
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
                                        <div class="w-10 h-10 rounded-full bg-bbcRed text-white flex items-center justify-center font-bold text-lg">${escapeHtml(user.charAt(0).toUpperCase())}</div>
                                        <div class="flex flex-col"><span class="font-bold text-bbcDark dark:text-white text-sm">${t('welcome')}</span><span class="text-xs text-muted-text truncate max-w-[200px]">${escapeHtml(user)}</span></div>
                                    </div>
                                    ${isAdmin ? `<a href="admin/index.php" class="w-full py-3 bg-bbcRed text-white rounded-xl font-bold flex items-center justify-center gap-2 shadow-lg shadow-bbcRed/20 btn-bounce"><i data-lucide="shield" class="w-5 h-5"></i> ${t('admin_panel')}</a>` : `<a href="dashboard/" class="w-full py-3 bg-bbcRed text-white rounded-xl font-bold flex items-center justify-center gap-2 shadow-lg shadow-bbcRed/20 btn-bounce"><i data-lucide="layout-dashboard" class="w-5 h-5"></i> ${t('dashboard')}</a>`}
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
                                    <a href="?category=${item.id}" onclick="event.preventDefault(); navigate('${item.id}'); setState({isMobileMenuOpen: false})" class="w-full text-left py-4 flex justify-between items-center hover:text-bbcRed hover:pl-3 transition-all duration-300 group">
                                        <span>${escapeHtml(item.label)}</span>
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
                <div class="fixed inset-0 z-[70] bg-white/98 dark:bg-card/98 backdrop-blur-md overflow-y-auto transition-all duration-300 no-scrollbar ${isSearchOpen ? "opacity-100 visible animate-zoom-in" : "opacity-0 invisible"}">
                    <div class="max-w-[1000px] mx-auto p-6 pt-12">
                        <div class="flex justify-end mb-12">
                            <button onclick="setState({isSearchOpen: false})" class="p-3 bg-muted-bg rounded-full hover:bg-gray-200 dark:hover:bg-gray-700 text-black dark:text-white transition-all hover:rotate-90">
                                <i data-lucide="x" class="w-8 h-8"></i>
                            </button>
                        </div>
                        <div class="relative mb-16 group">
                            <i data-lucide="search" class="absolute left-0 top-1/2 transform -translate-y-1/2 text-gray-400 w-6 h-6 md:w-10 md:h-10 group-focus-within:text-bbcRed transition-colors"></i>
                            <input type="text" placeholder="${t('search_placeholder')}" value="${escapeHtml(searchQuery)}" 
                                oninput="handleSearch(this.value)"
                                class="w-full py-4 pl-10 md:pl-14 text-2xl md:text-4xl font-bold border-b-2 border-border-color focus:border-bbcRed dark:focus:border-bbcRed outline-none bg-transparent text-bbcDark dark:text-white placeholder-gray-300 dark:placeholder-gray-700 transition-colors">
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8" id="search-results-container">
                            ${searchResults.map((a) => renderArticleCard(a, "grid", darkMode)).join("")}
                        </div>
                    </div>
                </div>
            `;
        }

        async function handleSearch(query) {
            state.searchQuery = query;
            if (query.length < 2) {
                state.searchResults = [];
                renderSearchResults();
                return;
            }

            try {
                const res = await fetch(`api/search.php?q=${encodeURIComponent(query)}&lang=${state.language}`);
                if (res.ok) {
                    const data = await res.json();
                    state.searchResults = data;
                } else {
                    console.error("Search failed");
                }
            } catch (e) {
                console.error("Search error", e);
            }
            renderSearchResults();
        }

        function renderSearchResults() {
            const container = document.getElementById("search-results-container");
            if (container) {
                container.innerHTML = state.searchResults
                    .map((a) => renderArticleCard(a, "grid", state.darkMode))
                    .join("");
                lucide.createIcons();
            }
        }

        async function handleLogout() {
            try {
                const response = await fetch('api/logout.php');
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                // Clear relevant local storage items on logout
                localStorage.removeItem("breachtimes-bookmarks");
                // Reload the page to reflect logged-out state
                window.location.reload();
            } catch (e) {
                console.error("Logout failed:", e);
                showToastMsg(t('server_error')); // Show error to user
            }
        }

        // Initialize the application
        init();
    </script>
</body>

</html>