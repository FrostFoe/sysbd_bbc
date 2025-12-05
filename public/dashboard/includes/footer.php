            </main>
            
            <footer class="border-t border-border-color bg-card py-6 mt-auto shrink-0">
                <div class="container mx-auto px-4 text-center text-muted-text text-xs">
                    &copy; <?php echo date(
                        "Y",
                    ); ?> BreachTimes Dashboard. All rights reserved.
                </div>
            </footer>
        </div> <!-- End Content Wrapper -->
    </div> <!-- End Main Layout -->

    <script>
        lucide.createIcons();

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            
            if (sidebar.classList.contains('-translate-x-full')) {
                // Open
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
                // small delay to allow display:block to apply before opacity transition
                setTimeout(() => overlay.classList.remove('opacity-0'), 10);
            } else {
                // Close
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('opacity-0');
                setTimeout(() => overlay.classList.add('hidden'), 300);
            }
        }

        function showToastMsg(msg, type = 'success') {
            const container = document.getElementById("toast-container");
            const toast = document.createElement("div");
            const icon = type === 'error' ? 'alert-circle' : 'check-circle';
            const color = type === 'error' ? 'text-red-500' : 'text-green-400 dark:text-green-600';
            
            toast.className = "toast-enter fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-black/80 dark:bg-white/90 backdrop-blur text-white dark:text-black px-6 py-3 rounded-full shadow-lg font-bold flex items-center gap-2 mb-2 text-sm w-auto z-[130]";
            toast.innerHTML = `<i data-lucide="${icon}" class="w-4 h-4 ${color}"></i> ${msg}`;
            container.appendChild(toast);
            lucide.createIcons();
            setTimeout(() => toast.remove(), 3000);
        }
    </script>
</body>
</html>
