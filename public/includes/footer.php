<?php
require_once __DIR__ . "/translations.php";
$lang = isset($lang) ? $lang : "bn";
?>
<footer class="pt-16 pb-8 bg-card text-card-text transition-colors border-t border-border-color mt-auto">
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
                <h3 class="font-bold text-lg mb-6 flex items-center gap-2"><i data-lucide="mail" class="w-5 h-5 text-bbcRed"></i> <?php echo t("newsletter", $lang); ?></h3>
                <p class="text-muted-text text-sm mb-4 max-w-sm"><?php echo t("subscribe_newsletter", $lang); ?></p>
                <div class="flex flex-col sm:flex-row gap-2 max-w-md">
                    <input type="email" placeholder="<?php echo t("your_email", $lang); ?>" class="p-3 bg-muted-bg text-card-text rounded-lg border border-border-color focus:outline-none focus:border-bbcRed flex-grow">
                    <button class="bg-bbcDark text-white dark:bg-white dark:text-black font-bold px-6 py-3 rounded-lg hover:opacity-90 transition-colors" onclick="Layout.copyToClipboard('subscribed', '<?php echo t("subscribed_successfully", $lang); ?>')"><?php echo t("subscribe", $lang); ?></button>
                </div>
            </div>
            <div class="col-span-1 md:col-span-2 space-y-4">
                <h3 class="font-bold text-lg flex items-center gap-2"><i data-lucide="heart" class="w-5 h-5 text-bbcRed"></i> <?php echo t("support_work", $lang); ?></h3>
                <p class="text-muted-text text-sm max-w-sm"><?php echo t("support_text", $lang); ?></p>
                
                <div class="space-y-3 mt-4">
                    <div class="bg-muted-bg p-3 rounded-lg border border-border-color">
                        <div class="flex items-center gap-2 mb-1">
                            <i data-lucide="coins" class="w-4 h-4 text-green-500"></i>
                            <span class="font-bold text-xs text-card-text">USDT (TRC-20)</span>
                        </div>
                        <div class="flex items-center justify-between gap-2 bg-card p-2 rounded border border-border-color">
                            <code class="text-[10px] text-muted-text break-all">TNztLXjP7zYWotPRpzdtPCNVu8JB3DG5jV</code>
                            <button onclick="Layout.copyToClipboard('TNztLXjP7zYWotPRpzdtPCNVu8JB3DG5jV', '<?php echo t("link_copied", $lang); ?>')" class="text-bbcRed hover:text-red-700 p-1">
                                <i data-lucide="copy" class="w-3 h-3"></i>
                            </button>
                        </div>
                    </div>

                    <div class="bg-muted-bg p-3 rounded-lg border border-border-color">
                        <div class="flex items-center gap-2 mb-1">
                            <i data-lucide="bitcoin" class="w-4 h-4 text-orange-500"></i>
                            <span class="font-bold text-xs text-card-text">Bitcoin (BTC)</span>
                        </div>
                        <div class="flex items-center justify-between gap-2 bg-card p-2 rounded border border-border-color">
                            <code class="text-[10px] text-muted-text break-all">18kgAYsUMVF51MNUeMt6vr1WhfgHtzcWai</code>
                            <button onclick="Layout.copyToClipboard('18kgAYsUMVF51MNUeMt6vr1WhfgHtzcWai', '<?php echo t("link_copied", $lang); ?>')" class="text-bbcRed hover:text-red-700 p-1">
                                <i data-lucide="copy" class="w-3 h-3"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex flex-col md:flex-row justify-between items-center pt-8 border-t border-border-color text-xs text-muted-text">
            <p><?php echo t("copyright", $lang); ?></p>
        </div>
    </div>
</footer>

<!-- Back to Top Button -->
<button id="back-to-top" onclick="window.scrollTo({ top: 0, behavior: 'smooth' })" class="fixed bottom-8 right-8 p-3 rounded-full shadow-xl z-50 transition-all duration-300 bg-black/80 backdrop-blur text-white hover:bg-black dark:bg-white/90 dark:text-black dark:hover:bg-white hover:scale-110 opacity-0 translate-y-10 pointer-events-none">
    <i data-lucide="chevron-up" class="w-5 h-5"></i>
</button>
