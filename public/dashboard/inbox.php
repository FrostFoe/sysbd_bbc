<?php
session_start();
require_once "../../src/config/db.php";

// Check if user is logged in
if (!isset($_SESSION["user_id"]) || ($_SESSION["user_role"] ?? null) !== "user") {
    header("Location: ../login/");
    exit();
}

$userId = $_SESSION["user_id"];
$userEmail = $_SESSION["user_email"] ?? "User";

// Get admin user ID (usually 1)
$adminStmt = $pdo->prepare("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
$adminStmt->execute();
$adminUser = $adminStmt->fetch(PDO::FETCH_ASSOC);
$adminId = $adminUser['id'] ?? 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inbox - BreachTimes</title>
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

    <?php include 'includes/header.php'; ?>

    <!-- Main Chat Interface (Facebook Messenger Style) -->
    <main class="flex-1 container mx-auto px-4 lg:px-8 max-w-7xl py-6 overflow-y-auto">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 h-full">
            <!-- Sidebar -->
            <aside class="lg:col-span-1 bg-card rounded-lg border border-border-color shadow-sm flex flex-col overflow-hidden">
                <div class="p-4 border-b border-border-color">
                    <h2 class="font-bold text-lg mb-3">Messages</h2>
                    <input 
                        type="text" 
                        placeholder="Search..." 
                        class="w-full px-4 py-2 rounded-full border border-border-color bg-muted-bg text-sm outline-none focus:border-bbcRed transition-colors"
                    >
                </div>

                <!-- Chat Item -->
                <div class="p-2 flex-1">
                    <div class="p-3 rounded-lg bg-muted-bg/50 hover:bg-muted-bg cursor-pointer transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-bbcRed text-white rounded-full flex items-center justify-center font-bold">A</div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-bold text-sm truncate">BreachTimes Support</h3>
                                <p class="text-xs text-muted-text truncate">Admin</p>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- Chat Area -->
            <div class="lg:col-span-3 bg-card rounded-lg border border-border-color shadow-sm flex flex-col overflow-hidden">
                <!-- Chat Header -->
                <div class="p-4 border-b border-border-color flex items-center justify-between bg-muted-bg/30">
                    <div>
                        <h2 class="font-bold text-lg">BreachTimes Support</h2>
                        <p class="text-xs text-muted-text">Online</p>
                    </div>
                    <button class="p-2 hover:bg-muted-bg rounded-lg transition-colors">
                        <i data-lucide="info" class="w-5 h-5"></i>
                    </button>
                </div>

                <!-- Messages -->
                <div id="messages-container" class="flex-1 overflow-y-auto p-4 space-y-4">
                    <div class="text-center text-muted-text py-8">
                        <i data-lucide="inbox" class="w-8 h-8 mx-auto mb-2 opacity-50"></i>
                        <p>No messages yet. Start a conversation with the admin!</p>
                    </div>
                </div>

                <!-- Message Input -->
                <div class="p-4 border-t border-border-color bg-muted-bg/20">
                    <div class="flex gap-3">
                        <input 
                            type="text" 
                            id="message-input" 
                            placeholder="Aa" 
                            class="flex-1 px-4 py-2.5 rounded-full border border-border-color bg-card outline-none focus:border-bbcRed transition-colors"
                            maxlength="5000"
                        >
                        <button 
                            onclick="sendMessage()" 
                            class="bg-bbcRed text-white p-2.5 rounded-full hover:bg-[#d40000] transition-colors font-bold"
                            title="Send message (Enter)"
                        >
                            <i data-lucide="send" class="w-5 h-5"></i>
                        </button>
                    </div>
                    <div class="text-xs text-muted-text mt-2">
                        <span id="char-count">0</span>/5000
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        const userId = <?php echo $userId; ?>;
        const adminId = <?php echo $adminId; ?>;
        let messageInput = document.getElementById('message-input');

        // Toggle theme
        function toggleTheme() {
            const isDark = document.documentElement.classList.toggle('dark');
            localStorage.setItem('breachtimes-theme', isDark ? 'dark' : 'light');
        }

        // Initialize theme
        if (localStorage.getItem('breachtimes-theme') === 'dark' || 
            (!localStorage.getItem('breachtimes-theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
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
                    <div class="text-center text-muted-text py-8">
                        <i data-lucide="inbox" class="w-8 h-8 mx-auto mb-2 opacity-50"></i>
                        <p>No messages yet. Start a conversation!</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = messages.map(msg => {
                const isOwn = msg.sender_id == userId;
                return `
                    <div class="flex ${isOwn ? 'justify-end' : 'justify-start'}">
                        <div class="max-w-xs lg:max-w-md ${isOwn ? 'bg-bbcRed text-white rounded-3xl rounded-tr-sm' : 'bg-muted-bg rounded-3xl rounded-tl-sm'} px-4 py-2.5">
                            <p class="text-sm break-words">${escapeHtml(msg.content)}</p>
                            <p class="text-xs ${isOwn ? 'text-white/70' : 'text-muted-text'} mt-1">
                                ${formatTime(msg.created_at)}
                            </p>
                        </div>
                    </div>
                `;
            }).join('');

            container.scrollTop = container.scrollHeight;
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

            toast.className = `${color} text-white px-6 py-3 rounded-lg shadow-lg flex items-center gap-2 animate-in`;
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

    <?php include 'includes/footer.php'; ?>
