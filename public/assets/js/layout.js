const Layout = {
    toggleSidebar: function(show) {
        const el = document.querySelector('#mobile-menu-layer > div');
        if (!el) return;
        
        if (show) {
            el.classList.remove('-translate-x-full');
            el.classList.add('animate-slide-in-left');
        } else {
            el.classList.add('-translate-x-full');
            el.classList.remove('animate-slide-in-left');
        }
    },

    toggleSearch: function(show) {
        const el = document.querySelector('#search-overlay-layer > div');
        if (!el) return;

        if (show) {
            el.classList.remove('opacity-0', 'invisible');
            el.classList.add('opacity-100', 'visible', 'animate-zoom-in');
            setTimeout(() => {
                const input = el.querySelector('input');
                if(input) input.focus();
            }, 100);
        } else {
            el.classList.add('opacity-0', 'invisible');
            el.classList.remove('opacity-100', 'visible', 'animate-zoom-in');
        }
    },

    toggleTheme: function() {
        const isDark = document.documentElement.classList.toggle("dark");
        const theme = isDark ? "dark" : "light";
        localStorage.setItem("breachtimes-theme", theme);
        
        // Dispatch event for other components
        window.dispatchEvent(new CustomEvent('themeChanged', { detail: { theme } }));
        
        // Change Icon
        const icon = document.querySelector('.theme-toggle-icon');
        if(icon) {
            // Re-render icon logic if needed or just toggle lucide attribute
            // Lucide icons are static svgs after render, so we might need to re-render or swap HTML
            // Simpler: Reload or use generic icon handling. 
            // Ideally, we swap the icon element or class.
            location.reload(); // Simple fix for icon update for now, or we can improve later
        }
    },

    toggleLanguage: function() {
        // Get current URL params
        const urlParams = new URLSearchParams(window.location.search);
        const currentLang = document.documentElement.lang;
        const newLang = currentLang === 'bn' ? 'en' : 'bn';
        
        // Update param
        urlParams.set('lang', newLang);
        
        // Reload with new param
        window.location.search = urlParams.toString();
    },

    handleLogout: async function() {
        try {
            await fetch('api/logout.php'); // Relative path might vary
            localStorage.removeItem("breachtimes-bookmarks");
            window.location.reload();
        } catch (e) {
            // Try absolute path if relative fails
            try {
                await fetch('/api/logout.php');
                localStorage.removeItem("breachtimes-bookmarks");
                window.location.reload();
            } catch(e2) {
                console.error("Logout failed");
            }
        }
    },

    fetchWeather: async function(lang) {
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

            const container = document.getElementById("weather-display");
            const dhakaLabel = lang === 'bn' ? 'ঢাকা' : 'Dhaka';
            const banglaDigits = ["০", "১", "২", "৩", "৪", "৫", "৬", "৭", "৮", "৯"];
            
            let tempDisplay = Math.round(temp);
            if (lang === 'bn') {
                tempDisplay = tempDisplay.toString().split("").map(d => banglaDigits[parseInt(d)] || d).join("");
            }

            if (container) {
                container.innerHTML = `
                    <div class="flex items-center gap-2 text-gray-700 dark:text-gray-200">
                        <i data-lucide="${icon}" class="w-5 h-5 text-bbcRed"></i>
                        <div class="flex flex-col leading-none">
                            <span class="font-bold text-xs">${dhakaLabel}</span>
                            <span class="text-xs text-muted-text mt-0.5 font-bold">${tempDisplay}° C</span>
                        </div>
                    </div>
                `;
                if(window.lucide) window.lucide.createIcons();
            }
        } catch (e) {
            console.error("Weather fetch failed", e);
        }
    },
    
    copyToClipboard: function(text, successMsg) {
        navigator.clipboard.writeText(text).then(() => {
            if(window.showToastMsg) window.showToastMsg(successMsg);
            else alert(successMsg);
        });
    }
};

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    // Check Theme
    const savedTheme = localStorage.getItem("breachtimes-theme");
    const systemDark = window.matchMedia("(prefers-color-scheme: dark)").matches;
    if (savedTheme === "dark" || (!savedTheme && systemDark)) {
        document.documentElement.classList.add("dark");
    } else {
        document.documentElement.classList.remove("dark");
    }

    // Initialize Weather
    const lang = document.documentElement.lang || 'bn';
    Layout.fetchWeather(lang);

    // Lucide
    if(window.lucide) window.lucide.createIcons();
});
