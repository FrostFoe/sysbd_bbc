<?php
session_start();
require_once "../../src/config/db.php";

// Verify admin access
if (!isset($_SESSION["user_role"]) || $_SESSION["user_role"] !== "admin") {
    header("Location: ../login/");
    exit();
}

$adminId = $_SESSION["user_id"] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - Admin Panel</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&display=swap');
    </style>
    <link href="../assets/css/styles.css" rel="stylesheet">
    <link href="../assets/css/custom.css" rel="stylesheet">
    <script src="../assets/js/lucide.js"></script>
    <script src="../assets/js/dropdown.js"></script>
</head>
<body class="bg-page text-card-text transition-colors duration-500 flex flex-col h-screen overflow-hidden font-sans antialiased">
    <div id="toast-container" class="fixed bottom-8 left-1/2 transform -translate-x-1/2 z-[120] pointer-events-none w-full max-w-sm flex flex-col items-center gap-2"></div>

    <!-- Header -->
    <header class="h-[70px] border-b border-border-color bg-white/90 dark:bg-[#121212]/90 backdrop-blur-md z-50 transition-colors duration-300 shadow-sm shrink-0 flex items-center px-4 lg:px-6 justify-between">
        <div class="flex items-center gap-3">
            <a href="index.php" class="p-2 -ml-2 rounded-lg hover:bg-muted-bg text-muted-text hover:text-card-text transition-colors flex-shrink-0">
                <i data-lucide="arrow-left" class="w-6 h-6"></i>
            </a>
            <button onclick="toggleMobileSidebar()" class="md:hidden p-2 -ml-2 rounded-lg hover:bg-muted-bg text-muted-text hover:text-card-text transition-colors flex-shrink-0">
                <i data-lucide="users" class="w-6 h-6"></i>
            </button>
            <div class="font-bold text-lg tracking-tight">Messages</div>
        </div>
        <div class="flex items-center gap-3">
            <select id="sort-select" onchange="loadConversations()" class="custom-select px-3 py-2 rounded-lg border border-border-color bg-card text-card-text text-sm">
                <option value="latest">Latest</option>
                <option value="unread">Unread</option>
                <option value="oldest">Oldest</option>
            </select>
            <span id="unread-badge" class="bg-bbcRed text-white px-2.5 py-0.5 rounded-full text-xs font-bold hidden"></span>
        </div>
    </header>

    <!-- Main Messenger -->
    <div class="flex-1 flex overflow-hidden relative">
        <!-- Mobile Sidebar Overlay -->
        <div id="sidebar-overlay" onclick="toggleMobileSidebar()" class="fixed inset-0 bg-black/60 z-30 hidden md:hidden backdrop-blur-sm transition-opacity duration-300 opacity-0"></div>

        <!-- Conversations List (Sidebar) -->
        <aside id="mobile-sidebar" class="w-full md:w-80 bg-card border-r border-border-color fixed inset-y-0 left-0 top-[70px] z-40 transform -translate-x-full md:translate-x-0 md:static md:transform-none md:inset-auto transition-transform duration-300 flex flex-col h-[calc(100vh-70px)] md:h-full shadow-xl md:shadow-none overflow-y-auto">
            <div class="p-4 border-b border-border-color flex-shrink-0">
                <input type="text" id="search-users" placeholder="Search users..." class="w-full px-4 py-2.5 rounded-full border border-border-color bg-muted-bg outline-none focus:border-bbcRed transition-colors text-sm">
            </div>
            
            <div id="conversations-list" class="flex-1 overflow-y-auto space-y-1 p-2">
                <div class="text-center text-muted-text text-sm py-8">
                    <i data-lucide="loader" class="w-5 h-5 inline animate-spin"></i> Loading...
                </div>
            </div>
        </aside>

        <!-- Chat Area (Full Screen) -->
        <div class="flex-1 flex flex-col overflow-hidden bg-page">
            <!-- Chat Header -->
            <div id="chat-header" class="h-[70px] border-b border-border-color bg-card flex items-center justify-between px-4 md:px-6 flex-shrink-0">
                <div class="flex items-center gap-3 flex-1 min-w-0">
                    <div id="user-avatar" class="w-10 h-10 rounded-full bg-gradient-to-br from-bbcRed to-orange-600 text-white flex items-center justify-center font-bold text-sm shadow-md flex-shrink-0">U</div>
                    <div class="flex-1 min-w-0">
                        <div id="chat-with-name" class="font-bold text-sm leading-none">Select a conversation</div>
                        <div id="chat-with-email" class="text-xs text-muted-text leading-none mt-1"></div>
                    </div>
                </div>
                <span id="online-indicator" class="w-3 h-3 bg-green-500 rounded-full flex-shrink-0 hidden"></span>
            </div>

            <!-- Messages Container -->
            <div id="messages-container" class="flex-1 overflow-y-auto p-4 md:p-6 space-y-4 flex flex-col justify-start">
                <div class="text-center text-muted-text py-12">
                    <i data-lucide="message-circle" class="w-12 h-12 mx-auto mb-3 opacity-50"></i>
                    <p class="text-base">Select a conversation to start messaging</p>
                </div>
            </div>

            <!-- Message Input Area -->
            <div class="border-t border-border-color bg-card p-4 md:p-6 flex-shrink-0">
                <div class="flex gap-3">
                    <input 
                        type="text" 
                        id="message-input" 
                        placeholder="Type a message..." 
                        class="flex-1 px-4 py-3 rounded-full border border-border-color bg-muted-bg outline-none focus:border-bbcRed transition-colors text-sm"
                        maxlength="5000"
                        disabled
                    >
                    <button 
                        onclick="sendMessage()" 
                        id="send-btn"
                        class="bg-bbcRed text-white px-5 py-3 rounded-full hover:bg-[#d40000] transition-colors font-bold shadow-md hover:shadow-lg flex-shrink-0 flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                        title="Send message (Enter)"
                        disabled
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
        const adminId = <?php echo $adminId; ?>;
        let messageInput = document.getElementById('message-input');
        let currentUserId = null;

        function toggleMobileSidebar() {
            const sidebar = document.getElementById('mobile-sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            
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

        document.addEventListener('DOMContentLoaded', () => {
            loadConversations();
            setInterval(loadConversations, 3000);
            lucide.createIcons();
        });

        async function loadConversations() {
            try {
                const sortBy = document.getElementById('sort-select').value;
                const response = await fetch(`../api/get_conversations.php?sort=${sortBy}`);
                const data = await response.json();

                if (data.success && data.conversations) {
                    displayConversations(data.conversations);
                    if (currentUserId) {
                        await loadMessages(currentUserId);
                    }
                }
            } catch (error) {
                console.error('Error loading conversations:', error);
            }
        }

        function displayConversations(conversations) {
            const list = document.getElementById('conversations-list');
            
            if (!conversations || conversations.length === 0) {
                list.innerHTML = '<div class="text-center text-muted-text text-sm py-8">No conversations yet</div>';
                return;
            }

            list.innerHTML = conversations.map(conv => `
                <div onclick="selectConversation(${conv.user_id}, '${escapeJs(conv.user_email)}', '${escapeJs(conv.user_name)}')" 
                     class="p-3 rounded-lg hover:bg-muted-bg cursor-pointer transition-colors border border-transparent hover:border-border-color ${currentUserId === conv.user_id ? 'bg-bbcRed/10 border-bbcRed' : ''}">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-bbcRed to-orange-600 text-white flex items-center justify-center font-bold text-sm shadow-md flex-shrink-0">
                            ${conv.user_name.charAt(0).toUpperCase()}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="font-bold text-sm truncate">${escapeHtml(conv.user_name)}</div>
                            <div class="text-xs text-muted-text truncate">${escapeHtml(conv.user_email)}</div>
                            ${conv.unread_count > 0 ? `<div class="text-xs text-bbcRed font-bold mt-1">${conv.unread_count} new</div>` : ''}
                        </div>
                    </div>
                </div>
            `).join('');
        }

        async function selectConversation(userId, userEmail, userName) {
            currentUserId = userId;
            
            document.getElementById('chat-with-name').textContent = userName;
            document.getElementById('chat-with-email').textContent = userEmail;
            document.getElementById('user-avatar').textContent = userName.charAt(0).toUpperCase();
            
            messageInput.disabled = false;
            document.getElementById('send-btn').disabled = false;
            
            await loadMessages(userId);
            toggleMobileSidebar(); // Close sidebar on mobile after selecting
        }

        async function loadMessages(userId) {
            try {
                const response = await fetch(`../api/get_messages.php?user_id=${userId}`);
                const data = await response.json();

                if (data.success && data.messages) {
                    displayMessages(data.messages, userId);
                }
            } catch (error) {
                console.error('Error loading messages:', error);
            }
        }

        function displayMessages(messages, userId) {
            const container = document.getElementById('messages-container');
            
            if (!messages || messages.length === 0) {
                container.innerHTML = `
                    <div class="text-center text-muted-text py-12">
                        <i data-lucide="inbox" class="w-12 h-12 mx-auto mb-3 opacity-50"></i>
                        <p class="text-base">No messages in this conversation</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = messages.map(msg => {
                const isOwn = msg.sender_id == adminId;
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

        async function sendMessage() {
            if (!currentUserId) return;

            const content = messageInput.value.trim();
            if (!content) {
                showToast('Message cannot be empty', 'error');
                return;
            }

            const button = document.getElementById('send-btn');
            button.disabled = true;

            try {
                const response = await fetch('../api/send_message.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        recipient_id: currentUserId,
                        content: content
                    })
                });

                const data = await response.json();

                if (data.success) {
                    messageInput.value = '';
                    document.getElementById('char-count').textContent = '0';
                    await loadMessages(currentUserId);
                    showToast('Message sent!');
                } else {
                    showToast(data.error || 'Failed to send message', 'error');
                }
            } catch (error) {
                showToast('Error sending message', 'error');
            } finally {
                button.disabled = false;
            }
        }

        messageInput.addEventListener('input', () => {
            document.getElementById('char-count').textContent = messageInput.value.length;
        });

        messageInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });

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

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function escapeJs(text) {
            return text.replace(/'/g, "\'" ).replace(/"/g, '\"');
        }

        function formatTime(dateStr) {
            if (!dateStr) return '';
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
    </script>
</body>
</html>