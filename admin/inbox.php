<?php
require_once "includes/header.php";
require_once "../config/db.php";

// Verify admin access
if (!isset($_SESSION["user_role"]) || $_SESSION["user_role"] !== "admin") {
    header("Location: ../login/");
    exit();
}

$adminId = $_SESSION["user_id"] ?? null;
?>

<div class="max-w-7xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Messages Inbox</h1>
        <div class="flex items-center gap-3">
            <select id="sort-select" onchange="loadConversations()" class="px-4 py-2 rounded-lg border border-border-color bg-card">
                <option value="latest">Latest Messages</option>
                <option value="unread">Unread First</option>
                <option value="oldest">Oldest Messages</option>
            </select>
            <span id="unread-badge" class="bg-bbcRed text-white px-3 py-1 rounded-full text-sm font-bold hidden"></span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 h-[calc(100vh-250px)]">
        <!-- Conversations List -->
        <div class="lg:col-span-1 bg-card rounded-xl border border-border-color shadow-sm flex flex-col overflow-hidden">
            <div class="p-4 border-b border-border-color">
                <input type="text" id="search-users" placeholder="Search users..." class="w-full px-4 py-2 rounded-lg border border-border-color bg-muted-bg outline-none focus:border-bbcRed transition-colors">
            </div>
            
            <div id="conversations-list" class="flex-1 overflow-y-auto space-y-2 p-2">
                <div class="text-center text-muted-text text-sm py-8">
                    <i data-lucide="loader" class="w-5 h-5 inline animate-spin"></i> Loading conversations...
                </div>
            </div>
        </div>

        <!-- Message Thread -->
        <div class="lg:col-span-3 bg-card rounded-xl border border-border-color shadow-sm flex flex-col overflow-hidden">
            <div id="chat-header" class="p-4 border-b border-border-color bg-muted-bg/30 flex items-center justify-between">
                <div>
                    <h2 id="chat-with-name" class="font-bold text-lg">Select a conversation</h2>
                    <p id="chat-with-email" class="text-xs text-muted-text"></p>
                </div>
                <div class="flex items-center gap-2">
                    <span id="online-indicator" class="w-3 h-3 bg-green-500 rounded-full"></span>
                </div>
            </div>

            <!-- Messages -->
            <div id="messages-container" class="flex-1 overflow-y-auto p-4 space-y-4">
                <div class="text-center text-muted-text py-12">
                    <i data-lucide="inbox" class="w-8 h-8 mx-auto mb-2 opacity-50"></i>
                    <p>Select a conversation to start messaging</p>
                </div>
            </div>

            <!-- Message Input -->
            <div class="p-4 border-t border-border-color bg-muted-bg/20">
                <div class="flex gap-3">
                    <input 
                        type="text" 
                        id="message-input" 
                        placeholder="Type a message..." 
                        class="flex-1 px-4 py-2.5 rounded-lg border border-border-color bg-card outline-none focus:border-bbcRed transition-colors"
                        maxlength="5000"
                        disabled
                    >
                    <button 
                        onclick="sendMessage()" 
                        class="bg-bbcRed text-white px-6 py-2.5 rounded-lg hover:bg-[#d40000] transition-colors font-bold disabled:opacity-50"
                        disabled
                        id="send-btn"
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
</div>

<script>
    let selectedUserId = null;
    const adminId = <?php echo $adminId; ?>;
    const messageInput = document.getElementById('message-input');
    const sendBtn = document.getElementById('send-btn');

    // Load conversations on page load
    document.addEventListener('DOMContentLoaded', () => {
        loadConversations();
        setInterval(loadConversations, 3000);
    });

    // Load all conversations
    async function loadConversations() {
        try {
            const sort = document.getElementById('sort-select').value || 'latest';
            const response = await fetch(`../api/get_conversations.php?sort=${sort}`);
            const data = await response.json();

            if (data.success && data.conversations) {
                displayConversations(data.conversations);
            }
        } catch (error) {
            console.error('Error loading conversations:', error);
        }
    }

    // Display conversations list
    function displayConversations(conversations) {
        const container = document.getElementById('conversations-list');
        
        if (!conversations || conversations.length === 0) {
            container.innerHTML = '<div class="text-center text-muted-text text-sm py-8"><i data-lucide="inbox" class="w-5 h-5 inline mb-2"></i><p>No conversations yet</p></div>';
            return;
        }

        container.innerHTML = conversations.map(conv => {
            const preview = (conv.last_message || 'No messages').substring(0, 50) + (conv.last_message && conv.last_message.length > 50 ? '...' : '');
            const isSelected = selectedUserId === conv.user_id;
            const unreadClass = conv.unread_count > 0 ? 'bg-muted-bg font-bold' : '';

            return `
                <div 
                    class="p-3 rounded-lg cursor-pointer transition-colors hover:bg-muted-bg ${unreadClass}"
                    onclick="selectConversation(${conv.user_id}, '${escapeJs(conv.email)}')"
                >
                    <div class="flex items-start gap-2">
                        <div class="w-10 h-10 bg-bbcRed text-white rounded-full flex items-center justify-center font-bold flex-shrink-0">
                            ${conv.email[0].toUpperCase()}
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-bold text-sm truncate">${escapeHtml(conv.email)}</h3>
                            <p class="text-xs text-muted-text truncate">${escapeHtml(preview)}</p>
                            <p class="text-xs text-muted-text mt-1">${formatTime(conv.last_message_time)}</p>
                        </div>
                        ${conv.unread_count > 0 ? `<span class="bg-bbcRed text-white text-xs rounded-full w-6 h-6 flex items-center justify-center flex-shrink-0">${conv.unread_count}</span>` : ''}
                    </div>
                </div>
            `;
        }).join('');

        lucide.createIcons();
    }

    // Select conversation
    async function selectConversation(userId, email) {
        selectedUserId = userId;
        document.getElementById('chat-with-name').textContent = email;
        document.getElementById('chat-with-email').textContent = `Joined ${new Date().toLocaleDateString()}`;
        messageInput.disabled = false;
        sendBtn.disabled = false;

        await loadMessages();
        setInterval(() => loadMessages(), 2000);
    }

    // Load messages for selected user
    async function loadMessages() {
        if (!selectedUserId) return;

        try {
            const response = await fetch(`../api/get_messages.php?user_id=${selectedUserId}`);
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
            container.innerHTML = '<div class="text-center text-muted-text py-8"><i data-lucide="inbox" class="w-8 h-8 mx-auto mb-2 opacity-50"></i><p>No messages in this conversation</p></div>';
            return;
        }

        container.innerHTML = messages.map(msg => {
            const isAdmin = msg.sender_id == adminId;
            return `
                <div class="flex ${isAdmin ? 'justify-end' : 'justify-start'}">
                    <div class="max-w-xs lg:max-w-md ${isAdmin ? 'bg-bbcRed text-white rounded-3xl rounded-tr-sm' : 'bg-muted-bg rounded-3xl rounded-tl-sm'} px-4 py-2.5">
                        <p class="text-sm break-words">${escapeHtml(msg.content)}</p>
                        <p class="text-xs ${isAdmin ? 'text-white/70' : 'text-muted-text'} mt-1">
                            ${formatTime(msg.created_at)}
                            ${msg.is_read ? ' ✓✓' : ' ✓'}
                        </p>
                    </div>
                </div>
            `;
        }).join('');

        container.scrollTop = container.scrollHeight;
    }

    // Send message
    async function sendMessage() {
        if (!selectedUserId) return;

        const content = messageInput.value.trim();
        if (!content) return;

        sendBtn.disabled = true;

        try {
            const response = await fetch('../api/send_message.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    recipient_id: selectedUserId,
                    content: content
                })
            });

            const data = await response.json();

            if (data.success) {
                messageInput.value = '';
                document.getElementById('char-count').textContent = '0';
                await loadMessages();
            }
        } catch (error) {
            console.error('Error sending message:', error);
        } finally {
            sendBtn.disabled = false;
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

    // Helper functions
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function escapeJs(text) {
        return text.replace(/'/g, "\\'").replace(/"/g, '\\"');
    }

    function formatTime(dateStr) {
        if (!dateStr) return '';
        const date = new Date(dateStr);
        const now = new Date();
        const diff = now - date;
        const hours = Math.floor(diff / 3600000);
        const minutes = Math.floor((diff % 3600000) / 60000);
        const days = Math.floor(diff / 86400000);

        if (minutes < 1) return 'now';
        if (minutes < 60) return `${minutes}m ago`;
        if (hours < 24) return `${hours}h ago`;
        if (days < 7) return `${days}d ago`;
        return date.toLocaleDateString();
    }
</script>

<?php require_once "includes/footer.php"; ?>
