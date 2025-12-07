<?php
// Ensure variables are set
$lang = isset($lang) ? $lang : "bn";
$user = isset($user) ? $user : null;
$isAdmin = isset($isAdmin) ? $isAdmin : false;
$currentCategory = isset($currentCategory) ? $currentCategory : "home";
$categories = isset($categories) ? $categories : []; // Need to populate this

require_once __DIR__ . "/translations.php";

// Fetch categories if not provided and DB connection exists
if (empty($categories) && isset($pdo)) {
    try {
        $stmt = $pdo->query("SELECT id, title_en, title_bn FROM categories ORDER BY sort_order ASC");
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        // Ignore error if table doesn't exist or DB issue
    }
}
?>

<!-- Header -->
<header class="border-b border-border-color sticky top-0 bg-white/90 dark:bg-[#121212]/90 backdrop-blur-md z-50 transition-colors duration-300 shadow-sm">
    <div class="container mx-auto px-4 lg:px-8 max-w-[1380px]">
        <div class="h-[70px] flex items-center justify-between">
            <div class="flex items-center gap-3 md:gap-6">
                <button onclick="Layout.toggleSidebar(true)" class="p-2 md:p-2.5 hover:bg-muted-bg rounded-full text-gray-700 dark:text-gray-200 transition-colors active:scale-95">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
                <a href="/" class="block text-black dark:text-white transition-transform hover:scale-[1.02] active:scale-95 duration-300">
                    <div class="flex items-center select-none gap-2 group">
                        <span class="bg-bbcRed text-white px-2.5 py-0.5 font-bold text-lg md:text-xl rounded shadow-md group-hover:bg-[var(--color-bbcRed-hover)] transition-colors duration-300">B</span>
                        <span class="font-bold text-lg md:text-2xl tracking-tight leading-none text-gray-900 dark:text-white group-hover:text-gray-700 dark:group-hover:text-gray-300 transition-colors">
                            BT
                        </span>
                    </div>
                </a>
            </div>
            <div class="flex items-center gap-2 md:gap-4">
                <button onclick="Layout.toggleLanguage()" class="p-2 md:p-2.5 rounded-full hover:bg-muted-bg text-gray-600 dark:text-green-400 transition-all active:scale-90">
                    <span class="text-sm font-bold"><?php echo $lang === "bn" ? "EN" : "BN"; ?></span>
                </button>
                <button onclick="Layout.toggleTheme()" class="p-2 md:p-2.5 rounded-full hover:bg-muted-bg text-gray-600 dark:text-yellow-400 transition-all active:scale-90 theme-toggle-btn">
                    <!-- Icon handled by JS or simple PHP check if we want -->
                    <i data-lucide="sun" class="w-5 h-5 theme-toggle-icon"></i> 
                </button>
                <button onclick="Layout.toggleSearch(true)" class="p-2 md:p-2.5 hover:bg-muted-bg rounded-full text-gray-700 dark:text-white transition-all active:scale-95">
                    <i data-lucide="search" class="w-5 h-5"></i>
                </button>
                <div id="weather-display" class="hidden lg:flex items-center gap-3 text-sm font-medium border-l border-border-color pl-5 ml-2 transition-colors">
                    <div class="animate-pulse bg-muted-bg h-4 w-16 rounded"></div>
                </div>
                <div class="hidden md:flex gap-3 items-center">
                    <?php if ($user): ?>
                        <?php if ($isAdmin): ?>
                            <a href="/admin/index.php" class="flex items-center gap-2 px-4 py-2 bg-bbcRed text-white rounded-full text-sm font-bold shadow-lg shadow-bbcRed/30 hover:bg-red-700 hover:scale-105 transition-all mr-2 active:scale-95">
                                <i data-lucide="shield" class="w-4 h-4"></i> <?php echo t("admin_panel", $lang); ?>
                            </a>
                        <?php else: ?>
                            <a href="/dashboard/" class="flex items-center gap-2 px-4 py-2 bg-bbcRed text-white rounded-full text-sm font-bold shadow-lg shadow-bbcRed/30 hover:bg-red-700 hover:scale-105 transition-all mr-2 active:scale-95">
                                <i data-lucide="layout-dashboard" class="w-4 h-4"></i> <?php echo t("dashboard", $lang); ?>
                            </a>
                        <?php endif; ?>
                        <button onclick="Layout.handleLogout()" class="text-sm font-bold px-4 py-2 hover:bg-red-50 dark:hover:bg-red-900/20 text-bbcRed rounded-full transition-all flex items-center gap-2 active:scale-95">
                            <div class="w-4 h-4 bg-bbcRed rounded-full text-white flex items-center justify-center text-[10px]"><?php echo strtoupper($user[0]); ?></div> <?php echo t("sign_out", $lang); ?>
                        </button>
                    <?php else: ?>
                        <a href="/login/" class="text-sm font-bold px-5 py-2.5 bg-bbcDark dark:bg-white text-white dark:text-black rounded-full hover:shadow-lg hover:-translate-y-0.5 transition-all active:scale-95"><?php echo t("sign_in", $lang); ?></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="relative group">
                <nav class="flex overflow-x-auto no-scrollbar gap-8 mt-2 text-gray-700 dark:text-gray-300 pb-2 mask-linear-gradient scroll-smooth">
                <a href="/?category=home" class="relative text-muted-text transition-colors duration-200 ease-out hover:text-bbcRed <?php echo $currentCategory === 'home' ? 'active text-bbcRed font-semibold opacity-100' : ''; ?> flex-shrink-0 py-2.5 px-1 text-sm font-bold whitespace-nowrap after:content-[''] after:absolute after:bottom-0 after:left-1/2 after:w-0 after:h-[2px] after:bg-bbcRed after:transition-all after:duration-300 after:ease-out after:-translate-x-1/2 [&.active]:after:w-full">
                    <?php echo t("home", $lang); ?>
                </a>
                <?php foreach ($categories as $cat): ?>
                    <?php 
                        $catTitle = $lang === 'bn' ? $cat['title_bn'] : $cat['title_en']; 
                        $isActive = $currentCategory === $cat['id'];
                    ?>
                    <a href="/?category=<?php echo $cat['id']; ?>" class="relative text-muted-text transition-colors duration-200 ease-out hover:text-bbcRed <?php echo $isActive ? 'active text-bbcRed font-semibold opacity-100' : ''; ?> flex-shrink-0 py-2.5 px-1 text-sm font-bold whitespace-nowrap after:content-[''] after:absolute after:bottom-0 after:left-1/2 after:w-0 after:h-[2px] after:bg-bbcRed after:transition-all after:duration-300 after:ease-out after:-translate-x-1/2 [&.active]:after:w-full">
                        <?php echo htmlspecialchars($catTitle); ?>
                    </a>
                <?php endforeach; ?>
                <a href="/?category=saved" class="relative text-muted-text transition-colors duration-200 ease-out hover:text-bbcRed <?php echo $currentCategory === 'saved' ? 'active text-bbcRed font-semibold opacity-100' : ''; ?> flex-shrink-0 py-2.5 px-1 text-sm font-bold whitespace-nowrap after:content-[''] after:absolute after:bottom-0 after:left-1/2 after:w-0 after:h-[2px] after:bg-bbcRed after:transition-all after:duration-300 after:ease-out after:-translate-x-1/2 [&.active]:after:w-full">
                    <?php echo t("saved", $lang); ?>
                </a>
            </nav>
        </div>
    </div>
</header>

<!-- Mobile Menu Layer -->
<div id="mobile-menu-layer">
    <div class="fixed top-0 left-0 bottom-0 z-[60] w-full sm:w-2/3 md:w-1/2 lg:w-1/4 bg-white/95 dark:bg-black/95 backdrop-blur-xl transition-all duration-300 transform -translate-x-full shadow-2xl">
        <div class="flex justify-between items-center p-6 border-b border-border-color">
            <div class="font-bold text-2xl dark:text-white tracking-tight"><?php echo t("menu", $lang); ?></div>
            <button onclick="Layout.toggleSidebar(false)" class="p-2 hover:bg-muted-bg rounded-full transition-transform hover:rotate-90 dark:text-white active:scale-95"><i data-lucide="x" class="w-8 h-8"></i></button>
        </div>
        <div class="p-6 h-full overflow-y-auto pb-20 no-scrollbar">
            <div class="mb-8 space-y-4">
                <?php if ($user): ?>
                    <div class="flex flex-col gap-3">
                        <div class="flex items-center gap-3 px-2 mb-2">
                            <div class="w-10 h-10 rounded-full bg-bbcRed text-white flex items-center justify-center font-bold text-lg"><?php echo strtoupper($user[0]); ?></div>
                            <div class="flex flex-col"><span class="font-bold text-bbcDark dark:text-white text-sm"><?php echo t("welcome", $lang); ?></span><span class="text-xs text-muted-text truncate max-w-[200px]"><?php echo htmlspecialchars($user); ?></span></div>
                        </div>
                        <?php if ($isAdmin): ?>
                            <a href="/admin/index.php" class="w-full py-3 bg-bbcRed text-white rounded-xl font-bold flex items-center justify-center gap-2 shadow-lg shadow-bbcRed/20 active:scale-95"><i data-lucide="shield" class="w-5 h-5"></i> <?php echo t("admin_panel", $lang); ?></a>
                        <?php else: ?>
                            <a href="/dashboard/" class="w-full py-3 bg-bbcRed text-white rounded-xl font-bold flex items-center justify-center gap-2 shadow-lg shadow-bbcRed/20 active:scale-95"><i data-lucide="layout-dashboard" class="w-5 h-5"></i> <?php echo t("dashboard", $lang); ?></a>
                        <?php endif; ?>
                        <button onclick="Layout.handleLogout()" class="w-full py-3 bg-muted-bg text-bbcDark dark:text-white rounded-xl font-bold flex items-center justify-center gap-2 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-600 transition-colors active:scale-95"><i data-lucide="log-out" class="w-5 h-5"></i> <?php echo t("sign_out", $lang); ?></button>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-2 gap-4">
                        <a href="/login/" class="w-full py-3 bg-bbcDark dark:bg-white text-white dark:text-black rounded-xl font-bold shadow-lg active:scale-95 text-center"><?php echo t("sign_in", $lang); ?></a>
                        <a href="/register.php" class="w-full py-3 border border-bbcDark dark:border-white text-bbcDark dark:text-white rounded-xl font-bold hover:bg-muted-bg transition-colors active:scale-95 text-center"><?php echo t("register", $lang); ?></a>
                    </div>
                <?php endif; ?>
            </div>
            <ul class="space-y-2 font-bold text-xl text-bbcDark dark:text-gray-200">
                <li class="border-b border-gray-100 dark:border-gray-800/50 pb-2 last:border-0">
                    <a href="/?category=home" class="w-full text-left py-4 flex justify-between items-center hover:text-bbcRed hover:pl-3 transition-all duration-300 group">
                        <span><?php echo t("home", $lang); ?></span>
                        <i data-lucide="chevron-right" class="w-5 h-5 text-gray-300 group-hover:text-bbcRed transition-colors"></i>
                    </a>
                </li>
                <?php foreach ($categories as $cat): ?>
                    <?php $catTitle = $lang === 'bn' ? $cat['title_bn'] : $cat['title_en']; ?>
                    <li class="border-b border-gray-100 dark:border-gray-800/50 pb-2 last:border-0">
                        <a href="/?category=<?php echo $cat['id']; ?>" class="w-full text-left py-4 flex justify-between items-center hover:text-bbcRed hover:pl-3 transition-all duration-300 group">
                            <span><?php echo htmlspecialchars($catTitle); ?></span>
                            <i data-lucide="chevron-right" class="w-5 h-5 text-gray-300 group-hover:text-bbcRed transition-colors"></i>
                        </a>
                    </li>
                <?php endforeach; ?>
                <li class="border-b border-gray-100 dark:border-gray-800/50 pb-2 last:border-0">
                    <a href="/?category=saved" class="w-full text-left py-4 flex justify-between items-center hover:text-bbcRed hover:pl-3 transition-all duration-300 group">
                        <span><?php echo t("saved", $lang); ?></span>
                        <i data-lucide="chevron-right" class="w-5 h-5 text-gray-300 group-hover:text-bbcRed transition-colors"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>

<!-- Search Overlay Layer -->
<div id="search-overlay-layer">
    <div class="fixed inset-0 z-[70] bg-white/98 dark:bg-card/98 backdrop-blur-md overflow-y-auto transition-all duration-300 no-scrollbar opacity-0 invisible">
        <div class="max-w-[1000px] mx-auto p-6 pt-12">
            <div class="flex justify-end mb-12">
                <button onclick="Layout.toggleSearch(false)" class="p-3 bg-muted-bg rounded-full hover:bg-gray-200 dark:hover:bg-gray-700 text-black dark:text-white transition-all hover:rotate-90">
                    <i data-lucide="x" class="w-8 h-8"></i>
                </button>
            </div>
            <div class="relative mb-16 group">
                <i data-lucide="search" class="absolute left-0 top-1/2 transform -translate-y-1/2 text-gray-400 w-6 h-6 md:w-10 md:h-10 group-focus-within:text-bbcRed transition-colors"></i>
                <input type="text" placeholder="<?php echo t("search_placeholder", $lang); ?>" 
                    oninput="handleSearch(this.value)"
                    class="w-full py-4 pl-10 md:pl-14 text-2xl md:text-4xl font-bold border-b-2 border-border-color focus:border-bbcRed dark:focus:border-bbcRed outline-none bg-transparent text-bbcDark dark:text-white placeholder-gray-300 dark:placeholder-gray-700 transition-colors">
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8" id="search-results-container">
                <!-- Results will be injected here via JS -->
            </div>
        </div>
    </div>
</div>
