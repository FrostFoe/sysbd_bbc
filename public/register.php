<!doctype html>
<html lang="bn">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>নিবন্ধন | BreachTimes</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&display=swap');
    </style>
    <link href="assets/css/styles.css" rel="stylesheet" />
    <link href="assets/css/custom.css" rel="stylesheet" />
    <script src="assets/js/lucide.js"></script>
</head>
<body class="bg-page text-card-text transition-colors duration-500">
    <div id="toast-container" class="fixed bottom-8 left-1/2 transform -translate-x-1/2 z-[110] pointer-events-none w-full max-w-sm flex flex-col items-center gap-2"></div>
    <div class="min-h-screen flex flex-col items-center justify-center p-4">
        <a href="index.php" class="absolute top-6 right-6 text-muted-text hover:text-card-text p-2 rounded-full hover:bg-muted-bg transition-all">
            <i data-lucide="x" class="w-8 h-8"></i>
        </a>
        <div class="bg-card p-8 md:p-12 w-full max-w-[480px] shadow-2xl rounded-2xl border border-border-color text-center relative overflow-hidden animate-fade-in-up">
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-bbcRed to-orange-600"></div>
            <h1 class="text-2xl font-bold mb-2 text-card-text">নিবন্ধন করুন</h1>
            <p class="text-sm text-muted-text mb-8">নতুন অ্যাকাউন্ট তৈরি করুন</p>
            
            <form id="registerForm" class="space-y-4 text-left">
                <div>
                    <label class="block text-xs font-bold uppercase text-muted-text mb-1">ইমেইল</label>
                    <input type="email" name="email" required class="w-full p-3 rounded-lg border border-border-color bg-muted-bg text-card-text focus:border-bbcRed outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-muted-text mb-1">পাসওয়ার্ড</label>
                    <input type="password" name="password" required class="w-full p-3 rounded-lg border border-border-color bg-muted-bg text-card-text focus:border-bbcRed outline-none">
                </div>
                <button type="submit" class="w-full bg-bbcDark dark:bg-white text-white dark:text-black font-bold py-3.5 rounded-lg hover:shadow-lg transition-all">নিবন্ধন সম্পন্ন করুন</button>
            </form>
            
            <div class="mt-6 text-sm text-card-text">
                ইতিমধ্যে অ্যাকাউন্ট আছে? <a href="login/" class="text-bbcRed font-bold hover:underline">লগইন করুন</a>
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

        function showToastMsg(msg, type = 'success') {
            const container = document.getElementById("toast-container");
            const toast = document.createElement("div");
            const icon = type === 'error' ? 'alert-circle' : 'check-circle';
            const color = type === 'error' ? 'text-red-500' : 'text-green-400 dark:text-green-600';
            
            toast.className = "toast-enter fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-black/80 dark:bg-white/90 backdrop-blur text-white dark:text-black px-6 py-3 rounded-full shadow-lg font-bold flex items-center gap-2 mb-2 text-sm w-auto";
            toast.innerHTML = `<i data-lucide="${icon}" class="w-4 h-4 ${color}"></i> ${msg}`;
            container.appendChild(toast);
            lucide.createIcons();
            setTimeout(() => toast.remove(), 3000);
        }
        
        document.getElementById('registerForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData.entries());
            
            try {
                const res = await fetch('api/register.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                const result = await res.json();
                
                if (result.success) {
                    showToastMsg('Registration successful! Redirecting...', 'success');
                    setTimeout(() => window.location.href = 'index.php', 1500);
                } else {
                    showToastMsg(result.message || 'Registration failed', 'error');
                }
            } catch (err) {
                console.error(err);
                showToastMsg('Something went wrong', 'error');
            }
        });
    </script>
</body>
</html>