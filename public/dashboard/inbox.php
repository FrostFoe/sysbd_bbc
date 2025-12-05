<?php
session_start();
require_once "../../src/config/db.php";

// Check if user is logged in
if (
    !isset($_SESSION["user_id"]) ||
    ($_SESSION["user_role"] ?? null) !== "user"
) {
    header("Location: ../login/");
    exit();
}

$userId = $_SESSION["user_id"];
$userEmail = $_SESSION["user_email"] ?? "User";
$userName = explode("@", $userEmail)[0];

// Get admin user ID (usually 1)
$adminStmt = $pdo->prepare("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
$adminStmt->execute();
$adminUser = $adminStmt->fetch(PDO::FETCH_ASSOC);
$adminId = $adminUser["id"] ?? 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - BreachTimes</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&display=swap');
    </style>
    <link href="../assets/css/styles.css" rel="stylesheet">
    <link href="../assets/css/custom.css" rel="stylesheet">
    <script src="../assets/js/lucide.js"></script>
</head>
<body class="bg-page text-page-text transition-colors duration-500 antialiased flex flex-col h-screen overflow-hidden font-sans">
    <div id="toast-container" class="fixed bottom-8 left-1/2 transform -translate-x-1/2 z-[120] pointer-events-none w-full max-w-sm flex flex-col items-center gap-2"></div>

    <!-- Header -->
    <header class="h-[70px] border-b border-border-color bg-white/90 dark:bg-[#121212]/90 backdrop-blur-md z-50 transition-colors duration-300 shadow-sm flex items-center px-4 lg:px-6 justify-between flex-shrink-0">
        <div class="flex items-center gap-3">
            <button onclick="toggleMobileSidebar()" class="md:hidden p-2 -ml-2 rounded-lg hover:bg-muted-bg text-muted-text hover:text-card-text transition-colors flex-shrink-0">
                <i data-lucide="menu" class="w-6 h-6"></i>
            </button>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-bbcRed to-orange-600 text-white flex items-center justify-center font-bold text-sm shadow-md">A</div>
                <div class="flex-1 min-w-0">
                    <div class="font-bold text-sm leading-none">BreachTimes Support</div>
                    <div class="text-xs text-muted-text leading-none">Online</div>
                </div>
            </div>
        </div>
        <button class="p-2 hover:bg-muted-bg rounded-lg text-muted-text transition-colors flex-shrink-0">
            <i data-lucide="info" class="w-5 h-5"></i>
        </button>
    </header>

    <!-- Main Content - Full Page Messenger -->
    <div class="flex-1 flex overflow-hidden relative">
        <!-- Mobile Sidebar Overlay -->
        <div id="mobile-overlay" onclick="toggleMobileSidebar()" class="fixed inset-0 bg-black/60 z-30 hidden md:hidden backdrop-blur-sm transition-opacity duration-300 opacity-0"></div>

        <!-- Sidebar (Conversations List) -->
        <aside id="mobile-sidebar" class="w-full md:w-72 bg-card border-r border-border-color fixed inset-y-0 left-0 top-[70px] z-40 transform -translate-x-full md:translate-x-0 md:static md:transform-none md:inset-auto transition-transform duration-300 flex flex-col h-[calc(100vh-70px)] md:h-full shadow-xl md:shadow-none overflow-y-auto">
            <div class="p-4 border-b border-border-color flex-shrink-0">
                <h2 class="font-bold text-lg mb-3">Messages</h2>
                <input 
                    type="text" 
                    placeholder="Search conversations..." 
                    class="w-full px-4 py-2 rounded-full border border-border-color bg-muted-bg text-sm outline-none focus:border-bbcRed transition-colors"
                >
            </div>

            <!-- Conversation Item -->
            <div class="p-3 cursor-pointer hover:bg-muted-bg transition-colors border-b border-border-color" onclick="toggleMobileSidebar()">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-bbcRed to-orange-600 text-white flex items-center justify-center font-bold text-sm shadow-md flex-shrink-0">A</div>
                    <div class="flex-1 min-w-0">
                        <div class="font-bold text-sm truncate">BreachTimes Support</div>
                        <div class="text-xs text-muted-text truncate">Admin Team</div>
                        <div class="text-[11px] text-muted-text mt-1">Always here to help</div>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Chat Area (Full Screen on Mobile) -->
        <div class="flex-1 flex flex-col overflow-hidden bg-page">
            <!-- Messages Container -->
            <div id="messages-container" class="flex-1 overflow-y-auto p-4 md:p-6 space-y-4 flex flex-col justify-start">
                <div class="text-center text-muted-text py-12">
                    <i data-lucide="inbox" class="w-12 h-12 mx-auto mb-3 opacity-50"></i>
                    <p class="text-base">No messages yet.</p>
                    <p class="text-sm mt-1">Start a conversation with BreachTimes Support!</p>
                </div>
            </div>

            <!-- Message Input Area -->
            <div class="border-t border-border-color bg-card p-4 md:p-6 flex-shrink-0">
                <div class="flex gap-3">
                    <input 
                        type="text" 
                        id="message-input" 
                        placeholder="Type your message..." 
                        class="flex-1 px-4 py-3 rounded-full border border-border-color bg-muted-bg outline-none focus:border-bbcRed transition-colors text-sm"
                        maxlength="5000"
                    >
                    <button 
                        onclick="sendMessage()" 
                        class="bg-bbcRed text-white px-5 py-3 rounded-full hover:bg-[#d40000] transition-colors font-bold shadow-md hover:shadow-lg flex-shrink-0 flex items-center gap-2"
                        title="Send message (Enter)"
                    >
                        <i data-lucide="send" class="w-5 h-5"></i>
                    </button>
                </div>
                <div class="text-xs text-muted-text mt-2 px-1">
                    <span id="char-count">0</span>/5000
                </div>
            </div>
        </div>
    </div>

    <script>
        const userId = <?php echo $userId; ?>;
        const adminId = <?php echo $adminId; ?>;
        let messageInput = document.getElementById('message-input');

        function toggleMobileSidebar() {
            const sidebar = document.getElementById('mobile-sidebar');
            const overlay = document.getElementById('mobile-overlay');
            
            if (sidebar.classList.contains('-translate-x-full')) {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
                setTimeout(() => overlay.classList.remove('opacity-0'), 10);
            } else {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('opacity-0');
                setTimeout(() => overlay.classList.add('hidden'), 300);
            }
        }

        // Load conversations on page load
        document.addEventListener('DOMContentLoaded', () => {
            loadMessages();
            setInterval(loadMessages, 2000);
            lucide.createIcons();
        });

        // Load messages into chat
        async function loadMessages() {
            try {
                const response = await fetch(`../api/get_messages.php?user_id=${adminId}`);
                const data = await response.json();

                if (data.success && data.messages) {
                    displayMessages(data.messages);
                }
            } catch (error) {
                console.error('Error loading messages:', error);
            }
        }

        // Display messages
        function displayMessages(messages) {
            const container = document.getElementById('messages-container');
            
            if (!messages || messages.length === 0) {
                container.innerHTML = `
                    <div class="text-center text-muted-text py-12">
                        <i data-lucide="inbox" class="w-12 h-12 mx-auto mb-3 opacity-50"></i>
                        <p class="text-base">No messages yet.</p>
                        <p class="text-sm mt-1">Start a conversation with BreachTimes Support!</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = messages.map(msg => {
                const isOwn = msg.sender_id == userId;
                return `
                    <div class="flex ${isOwn ? 'justify-end' : 'justify-start'} animate-in fade-in slide-in-from-bottom-2">
                        <div class="max-w-xs md:max-w-sm lg:max-w-md ${isOwn ? 'bg-bbcRed text-white rounded-3xl rounded-tr-sm shadow-md' : 'bg-muted-bg text-card-text rounded-3xl rounded-tl-sm shadow-sm'} px-5 py-3">
                            <p class="text-sm break-words leading-relaxed">${escapeHtml(msg.content)}</p>
                            <p class="text-xs ${isOwn ? 'text-white/70' : 'text-muted-text'} mt-2 text-right">
                                ${formatTime(msg.created_at)}
                            </p>
                        </div>
                    </div>
                `;
            }).join('');

            container.scrollTop = container.scrollHeight;
            lucide.createIcons();
        }

        // Send message
        async function sendMessage() {
            const content = messageInput.value.trim();

            if (!content) {
                showToast('Message cannot be empty', 'error');
                return;
            }

            const button = event.target.closest('button');
            button.disabled = true;

            try {
                const response = await fetch('../api/send_message.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        recipient_id: adminId,
                        content: content
                    })
                });

                const data = await response.json();

                if (data.success) {
                    messageInput.value = '';
                    document.getElementById('char-count').textContent = '0';
                    showToast('Message sent!');
                    await loadMessages();
                } else {
                    showToast(data.error || 'Failed to send message', 'error');
                }
            } catch (error) {
                showToast('Error sending message', 'error');
            } finally {
                button.disabled = false;
            }
        }

        // Character counter
        messageInput.addEventListener('input', () => {
            document.getElementById('char-count').textContent = messageInput.value.length;
        });

        // Send on Enter key
        messageInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });

        // Show toast
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            const icon = type === 'error' ? 'alert-circle' : 'check-circle';
            const color = type === 'error' ? 'bg-red-500' : 'bg-green-500';

            toast.className = `${color} text-white px-6 py-3 rounded-lg shadow-lg flex items-center gap-2 animate-in fade-in`;
            toast.innerHTML = `
                <i data-lucide="${icon}" class="w-5 h-5"></i>
                <span>${message}</span>
            `;

            document.getElementById('toast-container').appendChild(toast);
            lucide.createIcons();

            setTimeout(() => toast.remove(), 3000);
        }

        // Format time
        function formatTime(dateStr) {
            const date = new Date(dateStr);
            const now = new Date();
            const diff = now - date;
            const hours = Math.floor(diff / 3600000);
            const minutes = Math.floor((diff % 3600000) / 60000);

            if (minutes < 1) return 'now';
            if (minutes < 60) return `${minutes}m`;
            if (hours < 24) return `${hours}h`;
            return date.toLocaleDateString();
        }

        // Escape HTML
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
</body>
</html>
