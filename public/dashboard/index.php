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
    
    <link href="../assets/css/styles.css" rel="stylesheet" />
    <link href="../assets/css/custom.css" rel="stylesheet" />

    <script src="../assets/js/lucide.js"></script>
</head>

<body
    class="bg-page text-page-text font-sans transition-colors duration-500 antialiased selection:bg-bbcRed selection:text-white flex flex-col min-h-screen">
    <div id="progress-bar" class="fixed top-0 left-0 h-1 bg-bbcRed z-[100] shadow-[0_0_10px_var(--color-bbcRed)]" style="width: 0%">
    </div>

    <div id="toast-container"
        class="fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 z-[110] pointer-events-none w-full max-w-sm flex flex-col items-center gap-2">
    </div>

    <!-- Hidden Input for Import -->
    <input type="file" id="import-input" class="hidden" accept=".json" onchange="importData(this)" />

    <?php include "includes/header.php"; ?>

    <!-- Dashboard Main Content -->
    <main class="container mx-auto px-4 lg:px-8 max-w-[1380px] py-8 flex-1 animate-fade-in">
        <div class="bg-card p-8 rounded-2xl shadow-soft border border-border-color">
            <div class="flex items-center gap-3 mb-8">
                <span class="w-2 h-8 rounded-full" style="background-color: var(--color-bbcRed)"></span>
                <h2 class="text-2xl font-bold">Dashboard Overview</h2>
            </div>
            <div class="text-lg text-muted-text">
                <p>Welcome, <span class="font-bold"><?php echo htmlspecialchars(
                    $user_email,
                ); ?></span>! This is your personal dashboard.</p>
                <p class="mt-4">Your role: <span class="font-bold uppercase"><?php echo htmlspecialchars(
                    $user_role,
                ); ?></span></p>
                
                <!-- Placeholder for dashboard widgets or content -->
                <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-muted-bg p-6 rounded-xl shadow-sm border border-border-color">
                        <h3 class="font-bold text-xl mb-3 text-card-text">Quick Links</h3>
                        <ul class="space-y-2">
                            <li><a href="../read?id=some-article-id&lang=bn" class="text-bbcRed hover:underline">Example Article</a></li>
                            <li><a href="../saved" class="text-bbcRed hover:underline">Saved Articles</a></li>
                            <li><a href="inbox.php" class="text-bbcRed hover:underline">📬 Messages</a></li>
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

    <?php include "includes/footer.php"; ?>

    <script>
        lucide.createIcons();
    </script>
</body>

</html>

</body>

</html>