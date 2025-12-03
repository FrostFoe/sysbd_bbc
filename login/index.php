<!doctype html>
<html lang="bn">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>লগইন | BreachTimes</title>
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
<body class="bg-page text-card-text transition-colors duration-500">
    <div class="min-h-screen flex flex-col items-center justify-center p-4">
        <a href="../index.php" class="absolute top-6 right-6 text-muted-text hover:text-card-text p-2 rounded-full hover:bg-muted-bg transition-all">
            <i data-lucide="x" class="w-8 h-8"></i>
        </a>
        <div class="bg-card p-8 md:p-12 w-full max-w-[480px] shadow-2xl rounded-2xl border border-border-color text-center relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-bbcRed to-orange-600"></div>
            <h1 class="text-2xl font-bold mb-2 text-card-text">স্বাগতম!</h1>
            <p class="text-sm text-muted-text mb-8">আপনার অ্যাকাউন্টে লগইন করুন</p>
            
            <form id="loginForm" class="space-y-4 text-left">
                <div>
                    <label class="block text-xs font-bold uppercase text-muted-text mb-1">ইমেইল</label>
                    <input type="email" name="email" required class="w-full p-3 rounded-lg border border-border-color bg-muted-bg text-card-text focus:border-bbcRed outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-muted-text mb-1">পাসওয়ার্ড</label>
                    <input type="password" name="password" required class="w-full p-3 rounded-lg border border-border-color bg-muted-bg text-card-text focus:border-bbcRed outline-none">
                </div>
                <button type="submit" class="w-full bg-bbcDark dark:bg-white text-white dark:text-black font-bold py-3.5 rounded-lg hover:shadow-lg transition-all">সাইন ইন</button>
            </form>
            
            <div class="mt-6 text-sm text-card-text">
                অ্যাকাউন্ট নেই? <a href="../register.php" class="text-bbcRed font-bold hover:underline">নিবন্ধন করুন</a>
            </div>
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
        
        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData.entries());
            
            try {
                const res = await fetch('../api/login.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                const result = await res.json();
                
                if (result.success) {
                    // --- Redirection Logic ---
                    // Use absolute paths to ensure correct redirection
                    if (result.user && result.user.role) {
                        if (result.user.role === 'admin') {
                            window.location.href = '/admin/index.php'; // Redirect admin to admin panel
                        } else {
                            window.location.href = '/dashboard/index.php'; // Redirect normal user to dashboard
                        }
                    } else {
                        // Fallback if role is missing or undefined, redirect to dashboard
                        console.warn("User role not found in API response, redirecting to dashboard.");
                        window.location.href = '/dashboard/index.php';
                    }
                    // --- End Redirection Logic ---
                } else {
                    alert(result.message || 'Login failed');
                }
            } catch (err) {
                console.error('Login fetch error:', err);
                alert('An error occurred during login. Please try again.');
            }
        });
    </script>
</body>
</html>