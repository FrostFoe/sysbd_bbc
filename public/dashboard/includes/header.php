<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Auth Check - Redirect to login if not authenticated
if (!isset($_SESSION["user_id"])) {
    header("Location: ../login/");
    exit();
}

// Redirect admins to admin panel
if (isset($_SESSION["user_role"]) && $_SESSION["user_role"] === "admin") {
    header("Location: ../admin/index.php");
    exit();
}

$user_email = $_SESSION["user_email"] ?? "Unknown User";
$user_name = explode("@", $user_email)[0];
$current_page = basename($_SERVER['PHP_SELF']);

// Dashboard menu items
$menu_items = [
    ['name' => 'Overview', 'link' => 'index.php', 'icon' => 'layout-dashboard'],
    ['name' => 'Messages', 'link' => 'inbox.php', 'icon' => 'mail'],
    ['name' => 'Saved Articles', 'link' => '../?category=saved', 'icon' => 'bookmark'],
];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard | BreachTimes</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&display=swap');
    </style>
    <link href="../../assets/css/styles.css" rel="stylesheet" />
    <script src="../../assets/js/lucide.js"></script>
    <script src="../../assets/js/dropdown.js"></script>
</head>
<body class="bg-page text-card-text transition-colors duration-500 flex flex-col h-screen overflow-hidden font-sans antialiased">
    <div id="toast-container" role="status" aria-live="polite" class="fixed bottom-8 left-1/2 transform -translate-x-1/2 z-[120] pointer-events-none w-full max-w-sm flex flex-col items-center gap-2"></div>

    <!-- Header -->
    <header class="h-[70px] border-b border-border-color bg-white/90 dark:bg-[#121212]/90 backdrop-blur-md z-50 transition-colors duration-300 shadow-sm shrink-0 flex items-center px-4 lg:px-8 justify-between relative min-w-0">
        <div class="flex items-center gap-3 md:gap-4 min-w-0">
            <button onclick="toggleSidebar()" class="md:hidden p-2 -ml-2 rounded-lg hover:bg-muted-bg text-muted-text hover:text-card-text transition-colors flex-shrink-0">
                <i data-lucide="menu" class="w-6 h-6"></i>
            </button>
            <a href="../index.php" class="flex items-center select-none gap-2 group min-w-0">
                <span class="bg-bbcRed text-white px-2.5 py-0.5 font-bold text-xl rounded shadow-md group-hover:bg-[#d40000] transition-colors duration-300 flex-shrink-0">B</span>
                <span class="font-bold text-xl md:text-2xl tracking-tight leading-none text-gray-900 dark:text-white group-hover:text-gray-700 dark:group-hover:text-gray-300 transition-colors whitespace-nowrap">
                    <span class="text-bbcRed">Breach</span>Times <span class="text-xs text-muted-text font-normal ml-2 uppercase tracking-widest hidden sm:inline-block">Dashboard</span>
                </span>
            </a>
        </div>

        <div class="flex items-center gap-2 md:gap-4 flex-shrink-0">
            <div class="hidden md:flex items-center gap-2 md:gap-3 pl-2 md:pl-4 border-l border-border-color flex-shrink-0">
                <div class="text-right hidden lg:block leading-tight flex-shrink-0">
                    <div class="font-bold text-sm"><?php echo htmlspecialchars($user_name); ?></div>
                    <div class="text-[10px] text-muted-text"><?php echo htmlspecialchars($user_email); ?></div>
                </div>
                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-bbcRed to-orange-600 text-white flex items-center justify-center font-bold text-sm shadow-md flex-shrink-0"><?php echo strtoupper($user_name[0]); ?></div>
            </div>
        </div>
    </header>

    <!-- Main Layout -->
    <div class="flex flex-1 overflow-hidden relative">
        <!-- Sidebar Overlay (Mobile) -->
        <div id="sidebar-overlay" onclick="toggleSidebar()" class="fixed inset-0 bg-black/60 z-30 hidden md:hidden backdrop-blur-sm transition-opacity duration-300 opacity-0"></div>

        <!-- Sidebar -->
        <aside id="sidebar" class="w-64 bg-card border-r border-border-color fixed inset-y-0 left-0 top-[70px] z-40 transform -translate-x-full transition-transform duration-300 md:translate-x-0 md:static md:inset-auto md:transform-none flex flex-col h-[calc(100vh-70px)] md:h-full shadow-xl md:shadow-none overflow-y-auto">
            <nav class="p-4 space-y-1.5">
                <?php foreach ($menu_items as $item): 
                    $isActive = $current_page === $item['link'] || strpos($_SERVER['REQUEST_URI'], $item['link']) !== false;
                    $baseClass = "flex items-center gap-3 px-4 py-3 rounded-lg font-medium transition-all duration-200 group";
                    $activeClass = "bg-bbcRed text-white shadow-md shadow-red-900/20";
                    $inactiveClass = "text-muted-text hover:bg-muted-bg hover:text-card-text";
                ?>
                    <a href="<?php echo $item['link']; ?>" class="<?php echo $baseClass . ' ' . ($isActive ? $activeClass : $inactiveClass); ?>">
                        <i data-lucide="<?php echo $item['icon']; ?>" class="w-5 h-5 <?php echo $isActive ? 'text-white' : 'text-muted-text group-hover:text-bbcRed'; ?> transition-colors"></i>
                        <?php echo $item['name']; ?>
                    </a>
                <?php endforeach; ?>
            </nav>
            
            <div class="mt-auto p-4 border-t border-border-color">
                <!-- Profile Section (Mobile) -->
                <div class="md:hidden mb-4 pb-4 border-b border-border-color">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-bbcRed to-orange-600 text-white flex items-center justify-center font-bold text-sm shadow-md"><?php echo strtoupper($user_name[0]); ?></div>
                        <div class="text-left leading-tight flex-1">
                            <div class="font-bold text-sm"><?php echo htmlspecialchars($user_name); ?></div>
                            <div class="text-[10px] text-muted-text"><?php echo htmlspecialchars($user_email); ?></div>
                        </div>
                    </div>
                </div>
                <a href="../api/logout.php" class="flex items-center gap-3 px-4 py-3 rounded-lg font-medium text-red-500 hover:bg-red-50 hover:text-red-600 transition-all duration-200">
                    <i data-lucide="log-out" class="w-5 h-5"></i>
                    Sign Out
                </a>
            </div>
        </aside>

        <!-- Content Wrapper -->
        <div class="flex-1 flex flex-col overflow-y-auto bg-page relative w-full scroll-smooth" id="main-scroll">
            <main class="flex-grow container mx-auto px-4 lg:px-8 max-w-[1200px] py-8">
