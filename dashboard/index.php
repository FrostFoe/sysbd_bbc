<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: ../login/index.php");
    exit();
}

// Get user info from session
$user_email = $_SESSION["user_email"] ?? "Unknown User";
$user_role = $_SESSION["user_role"] ?? "user";

// Redirect admin to admin panel immediately if they somehow land here
if ($user_role === "admin") {
    header("Location: ../admin/index.php");
    exit();
}

// --- Dashboard Content ---
$dashboard_content = "
    <main class='container mx-auto px-4 lg:px-8 max-w-[1380px] py-8 min-h-[60vh] animate-fade-in'>
        <div class='bg-card p-8 rounded-2xl shadow-soft border border-border-color'>
            <h2 class='text-3xl font-bold mb-6 flex items-center gap-3 text-card-text'>
                <span class='w-2 h-8 rounded-full' style='background-color: #B80000'></span>
                Dashboard
            </h2>
            <div class='text-lg text-muted-text'>
                <p>Welcome, <span class='font-bold'>" . htmlspecialchars($user_email) . "</span>! This is your personal dashboard.</p>
                <p class='mt-4'>Your role: <span class='font-bold uppercase'>" . htmlspecialchars($user_role) . "</span></p>
                
                <!-- Placeholder for dashboard widgets or content -->
                <div class='mt-8 grid grid-cols-1 md:grid-cols-2 gap-6'>
                    <div class='bg-muted-bg p-6 rounded-xl shadow-sm border border-border-color'>
                        <h3 class='font-bold text-xl mb-3 text-card-text'>Quick Links</h3>
                        <ul class='space-y-2'>
                            <li><a href='../read?id=some-article-id&lang=bn' class='text-bbcRed hover:underline'>Example Article</a></li>
                            <li><a href='../saved' class='text-bbcRed hover:underline'>Saved Articles</a></li>
                            <li><a href='inbox.php' class='text-bbcRed hover:underline'>📬 Messages</a></li>
                        </ul>
                    </div>
                    <div class='bg-muted-bg p-6 rounded-xl shadow-sm border border-border-color'>
                        <h3 class='font-bold text-xl mb-3 text-card-text'>Your Activity</h3>
                        <p class='text-muted-text'>Recent activity or stats can be shown here.</p>
                    </div>
                </div>
            </div>
        </div>
    </main>
";
// --- End Dashboard Content ---
?>
<!doctype html>
<html lang="bn">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard | BreachTimes</title>

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&display=swap');
    </style>
    
    <link href="../public/assets/styles.css" rel="stylesheet" />

    <script src="../public/assets/js/lucide.js"></script>
</head>

<body
    class="bg-page text-page-text font-sans transition-colors duration-500 antialiased selection:bg-bbcRed selection:text-white">
    <div id="progress-bar" class="fixed top-0 left-0 h-1 bg-bbcRed z-[100] shadow-[0_0_10px_#B80000]" style="width: 0%">
    </div>

    <div id="app"></div>

    <div id="toast-container"
        class="fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 z-[110] pointer-events-none w-full max-w-sm flex flex-col items-center gap-2">
    </div>

    <!-- Hidden Input for Import -->
    <input type="file" id="import-input" class="hidden" accept=".json" onchange="importData(this)" />

    <script>
        const PLACEHOLDER_IMAGE = "https://placehold.co/600x400/1a1a1a/FFF?text=BreachTimes";
        
        // State management for dashboard - simplified for now
        const state = {
            language: "bn", // Default language, can be loaded from localStorage
            darkMode: false,
            user_email: <?php echo json_encode($user_email); ?>,
            user_role: <?php echo json_encode($user_role); ?>,
            isLoading: false,
            // Add other state variables as needed for dashboard features
        };

        // Dummy translations for essential functions
        const translations = {
            en: {
                welcome: "Welcome",
                sign_out: "Sign Out",
                admin_panel: "Admin Panel",
                dashboard: "Dashboard",
                // Add more if needed
            },
            bn: {
                welcome: "স্বাগতম",
                sign_out: "সাইন আউট",
                admin_panel: "অ্যাডমিন প্যানেল",
                dashboard: "ড্যাশবোর্ড",
                // Add more if needed
            },
        };

        const t = (key) => translations[state.language][key] || key;

        // --- HTML Escaping Function (from index.php) ---
        function escapeHtml(unsafe) {
            if (typeof unsafe !== 'string') {
                return unsafe; // Return non-strings as is
            }
            const div = document.createElement('div');
            div.appendChild(document.createTextNode(unsafe));
            return div.innerHTML;
        }

        // --- Timestamp Formatting Function (simplified for dashboard) ---
        function formatTimestamp(timestampString) {
             if (!timestampString) return '';
             // Basic display for now, actual date formatting might be complex
             try {
                 const date = new Date(timestampString);
                 if (isNaN(date.getTime())) {
                     // Fallback for non-standard formats if needed
                     return timestampString.split(' ')[0]; // Just show YYYY-MM-DD
                 }
                 const options = { year: 'numeric', month: state.language === 'bn' ? 'long' : 'short', day: 'numeric' };
                 const locale = state.language === 'bn' ? 'bn-BD' : 'en-US';
                 return date.toLocaleDateString(locale, options);
             } catch(e) {
                console.error("Error formatting timestamp:", timestampString, e);
                return timestampString; // Return original string if error
             }
        }

        function showToastMsg(msg, type = 'success') {
            const container = document.getElementById("toast-container");
            const toast = document.createElement("div");
            const icon = type === 'error' ? 'alert-circle' : 'check-circle';
            const color = type === 'error' ? 'text-red-500' : 'text-green-400 dark:text-green-600';
            
            toast.className = "toast-enter bg-black/80 dark:bg-white/90 backdrop-blur text-white dark:text-black px-6 py-3 rounded-full shadow-lg font-bold flex items-center gap-2 mb-2 text-sm w-auto";
            toast.innerHTML = `<i data-lucide="${icon}" class="w-4 h-4 ${color}"></i> ${msg}`;
            container.appendChild(toast);
            lucide.createIcons();
            setTimeout(() => toast.remove(), 3000);
        }

        function setState(updates, shouldRender = true) {
            Object.assign(state, updates);
            if (shouldRender) render();
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
            render(); // Re-render to apply theme changes
        }

        function toggleLanguage() {
            const newLang = state.language === "bn" ? "en" : "bn";
            setState({ language: newLang }, false);
            localStorage.setItem("breachtimes-language", newLang);
            document.documentElement.lang = newLang;
            render(); // Re-render to apply language changes
        }

        function handleLogout() {
            fetch('../api/logout.php')
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = '../login/index.php'; // Redirect to login after logout
                    } else {
                        showToastMsg('Logout failed.', 'error');
                    }
                })
                .catch(err => {
                    console.error('Logout fetch error:', err);
                    showToastMsg('An error occurred during logout.', 'error');
                });
        }
        
        // --- Main Render Function ---
        function render() {
            const { user_email, user_role, darkMode, language, isLoading } = state;
            const isAdmin = user_role === 'admin';

            const headerHtml = `
                <header class="border-b border-border-color sticky top-0 bg-white/90 dark:bg-[#121212]/90 backdrop-blur-md z-50 transition-colors duration-300 shadow-sm">
                    <div class="container mx-auto px-4 lg:px-8 max-w-[1380px]">
                        <div class="h-[70px] flex items-center justify-between">
                            <div class="flex items-center gap-6">
                                <a href="/dashboard" class="block text-black dark:text-white transition-transform hover:scale-[1.02] active:scale-95 duration-300">
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
                                    <span class="text-sm font-bold">${language === 'bn' ? 'EN' : 'BN'}</span>
                                </button>
                                <button onclick="toggleTheme()" class="p-2.5 rounded-full hover:bg-muted-bg text-gray-600 dark:text-yellow-400 transition-all active:scale-90">
                                    <i data-lucide="${darkMode ? "sun" : "moon"}" class="w-5 h-5"></i>
                                </button>
                                <div class="hidden md:flex gap-3 items-center">
                                    ${isAdmin ? `<a href="../admin/index.php" class="flex items-center gap-2 px-4 py-2 bg-bbcRed text-white rounded-full text-sm font-bold shadow-lg shadow-bbcRed/30 hover:bg-red-700 hover:scale-105 transition-all mr-2 btn-bounce"><i data-lucide="shield" class="w-4 h-4"></i> ${t('admin_panel')}</a>` : ""}
                                    <button onclick="handleLogout()" class="text-sm font-bold px-4 py-2 hover:bg-red-50 dark:hover:bg-red-900/20 text-bbcRed rounded-full transition-all flex items-center gap-2 btn-bounce">
                                        <div class="w-4 h-4 bg-bbcRed rounded-full text-white flex items-center justify-center text-[10px]">${escapeHtml(user_email.charAt(0).toUpperCase())}</div> ${t('sign_out')}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>
            `;

            const footerHtml = `
                <footer class="pt-16 pb-8 bg-card text-card-text transition-colors border-t border-border-color">
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
                        <div class="flex flex-col md:flex-row justify-between items-center pt-8 border-t border-border-color text-xs text-muted-text">
                            <p>© 2025 BreachTimes. All rights reserved.</p>
                        </div>
                    </div>
                </footer>
            `;

            const dashboardMainContent = `
                <main class="container mx-auto px-4 lg:px-8 max-w-[1380px] py-8 min-h-[60vh] animate-fade-in-up">
                    <div class="bg-card p-8 rounded-2xl shadow-soft border border-border-color">
                        <h2 class="text-3xl font-bold mb-6 flex items-center gap-3 text-card-text">
                            <span class="w-2 h-8 rounded-full" style="background-color: #B80000"></span>
                            ${t('dashboard')}
                        </h2>
                        <div class="text-lg text-muted-text">
                            <p>Welcome, ${escapeHtml(user_email)}! This is your personal dashboard.</p>
                            <p class="mt-4">Your role: <span class="font-bold uppercase">${user_role}</span></p>
                            
                            <!-- Placeholder for dashboard widgets or content -->
                            <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="bg-muted-bg p-6 rounded-xl shadow-sm border border-border-color">
                                    <h3 class="font-bold text-xl mb-3 text-card-text">Quick Links</h3>
                                    <ul>
                                        <li><a href="../read?id=some-article-id&lang=bn" class="text-bbcRed hover:underline">Example Article</a></li>
                                        <li><a href="../saved" class="text-bbcRed hover:underline">Saved Articles</a></li>
                                        <!-- Add more links as needed -->
                                    </ul>
                                </div>
                                <div class="bg-muted-bg p-6 rounded-xl shadow-sm border border-border-color">
                                    <h3 class="font-bold text-xl mb-3 text-card-text">Your Activity</h3>
                                    <p class="text-muted-text">Recent activity or stats can be shown here.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </main>
            `;

            document.getElementById("app").innerHTML = `
                ${headerHtml}
                ${dashboardMainContent}
                ${footerHtml}
            `;
            lucide.createIcons();
        }

        // Initialize the application
        function init() {
            const savedLanguage = localStorage.getItem("breachtimes-language");
            if (savedLanguage) {
                state.language = savedLanguage;
            }
            document.documentElement.lang = state.language;

            const savedTheme = localStorage.getItem("breachtimes-theme");
            const systemDark = window.matchMedia("(prefers-color-scheme: dark)").matches;

            if (savedTheme === "dark" || (!savedTheme && systemDark)) {
                state.darkMode = true;
                document.documentElement.classList.add("dark");
            } else {
                state.darkMode = false;
                document.documentElement.classList.remove("dark");
            }
            render();
        }

        init();
    </script>
</body>

</html>