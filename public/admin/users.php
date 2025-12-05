<?php
require_once "includes/header.php";
require_once "../../src/config/db.php";

// Fetch all users with mute status
$users = $pdo
    ->query(
        "
    SELECT u.id, u.email, u.role, u.created_at, 
           m.id as is_muted, m.reason, m.created_at as muted_at
    FROM users u
    LEFT JOIN muted_users m ON u.id = m.user_id
    ORDER BY u.role DESC, u.created_at DESC
",
    )
    ->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">Users Management</h1>
    <div class="text-sm text-muted-text">
        Total Users: <span class="font-bold text-card-text"><?php echo count(
            $users,
        ); ?></span>
    </div>
</div>

<div class="bg-card rounded-xl border border-border-color shadow-sm overflow-hidden">
    <?php if (empty($users)): ?>
        <div class="p-8 text-center text-muted-text">
            <i data-lucide="users" class="w-16 h-16 mx-auto mb-4 text-border-color"></i>
            <p class="text-lg font-bold mb-2">No Users Found</p>
        </div>
    <?php // Can't mute yourself
        // Can't mute yourself
        else: ?>
        <table class="w-full text-left border-collapse">
            <thead class="bg-muted-bg text-muted-text text-xs uppercase">
                <tr>
                    <th class="p-4">Email</th>
                    <th class="p-4">Role</th>
                    <th class="p-4">Joined</th>
                    <th class="p-4">Status</th>
                    <th class="p-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border-color">
                <?php foreach ($users as $user):

                    $isMuted = !empty($user["is_muted"]);
                    $joinDate = date("M d, Y", strtotime($user["created_at"]));
                    ?>
                <tr class="hover:bg-muted-bg transition-colors">
                    <td class="p-4">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-bbcRed to-orange-600 flex items-center justify-center text-white text-xs font-bold">
                                <?php echo strtoupper(
                                    substr($user["email"], 0, 1),
                                ); ?>
                            </div>
                            <div>
                                <p class="font-bold text-sm"><?php echo htmlspecialchars(
                                    $user["email"],
                                ); ?></p>
                                <p class="text-xs text-muted-text">ID: <?php echo $user[
                                    "id"
                                ]; ?></p>
                            </div>
                        </div>
                    </td>
                    <td class="p-4">
                        <span class="inline-block px-3 py-1 rounded-full text-xs font-bold <?php echo $user[
                            "role"
                        ] === "admin"
                            ? "bg-red-100 dark:bg-red-900/20 text-bbcRed"
                            : "bg-blue-100 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400"; ?>">
                            <?php echo ucfirst($user["role"]); ?>
                        </span>
                    </td>
                    <td class="p-4 text-sm text-muted-text"><?php echo $joinDate; ?></td>
                    <td class="p-4">
                        <?php if ($isMuted): ?>
                            <span class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-red-100 dark:bg-red-900/20 text-red-600 dark:text-red-400 flex items-center gap-1.5 w-fit">
                                <i data-lucide="ban" class="w-3 h-3"></i> Muted
                            </span>
                            <?php if (!empty($user["reason"])): ?>
                                <p class="text-xs text-muted-text mt-1">Reason: <?php echo htmlspecialchars(
                                    $user["reason"],
                                ); ?></p>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-green-100 dark:bg-green-900/20 text-green-600 dark:text-green-400 flex items-center gap-1.5 w-fit">
                                <i data-lucide="check-circle" class="w-3 h-3"></i> Active
                            </span>
                        <?php endif; ?>
                    </td>
                    <td class="p-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <?php if ($user["id"] != $_SESSION["user_id"]): ?>
                                <?php if ($isMuted): ?>
                                    <button onclick="unmuteUser(<?php echo $user[
                                        "id"
                                    ]; ?>)" class="text-green-500 hover:text-green-700 hover:bg-green-50 dark:hover:bg-green-900/20 p-2 rounded transition-colors" title="Unmute">
                                        <i data-lucide="check" class="w-4 h-4"></i>
                                    </button>
                                <?php else: ?>
                                    <button onclick="openMuteDialog(<?php echo $user[
                                        "id"
                                    ]; ?>, '<?php echo htmlspecialchars(
    $user["email"],
); ?>')" class="text-yellow-600 hover:text-yellow-700 hover:bg-yellow-50 dark:hover:bg-yellow-900/20 p-2 rounded transition-colors" title="Mute">
                                        <i data-lucide="ban" class="w-4 h-4"></i>
                                    </button>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php
                endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- Mute User Modal -->
<div id="mute-modal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-card rounded-xl border border-border-color shadow-xl max-w-md w-full p-6 animate-zoom-in">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-full bg-yellow-100 dark:bg-yellow-900/20 flex items-center justify-center text-yellow-600 dark:text-yellow-400">
                <i data-lucide="alert-circle" class="w-5 h-5"></i>
            </div>
            <h2 class="text-lg font-bold text-card-text">Mute User</h2>
        </div>
        
        <p class="text-sm text-muted-text mb-4">
            You are about to mute <span id="mute-user-email" class="font-bold text-card-text"></span>. This user will no longer be able to post comments.
        </p>
        
        <div class="mb-4">
            <label class="block text-xs font-bold text-muted-text mb-2 uppercase tracking-wide">Reason (Optional)</label>
            <textarea id="mute-reason" placeholder="Enter reason for muting this user..." class="w-full p-3 rounded-lg border border-border-color bg-muted-bg text-card-text focus:ring-2 focus:ring-yellow-500/20 focus:border-yellow-500 outline-none transition-all resize-none text-sm" rows="3"></textarea>
        </div>
        
        <div class="flex gap-3 justify-end">
            <button onclick="closeMuteDialog()" class="px-4 py-2 rounded-lg bg-muted-bg hover:bg-border-color text-card-text font-bold transition-colors">
                Cancel
            </button>
            <button onclick="confirmMute()" class="px-4 py-2 rounded-lg bg-yellow-600 hover:bg-yellow-700 text-white font-bold transition-colors flex items-center gap-2">
                <i data-lucide="ban" class="w-4 h-4"></i> Mute User
            </button>
        </div>
    </div>
</div>

<script>
    let muteUserId = null;

    function openMuteDialog(userId, email) {
        muteUserId = userId;
        document.getElementById('mute-user-email').textContent = email;
        document.getElementById('mute-reason').value = '';
        document.getElementById('mute-modal').classList.remove('hidden');
    }

    function closeMuteDialog() {
        document.getElementById('mute-modal').classList.add('hidden');
        muteUserId = null;
    }

    async function confirmMute() {
        if (!muteUserId) return;

        const reason = document.getElementById('mute-reason').value.trim();

        try {
            const res = await fetch('../api/mute_user.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    userId: muteUserId,
                    action: 'mute',
                    reason: reason
                })
            });
            const result = await res.json();
            
            if (result.success) {
                showToastMsg('User muted successfully');
                setTimeout(() => location.reload(), 1000);
            } else {
                showToastMsg(result.error || 'Failed to mute user', 'error');
            }
        } catch (e) {
            console.error(e);
            showToastMsg('Server error', 'error');
        }
        closeMuteDialog();
    }

    async function unmuteUser(userId) {
        if (!confirm('Are you sure you want to unmute this user?')) return;

        try {
            const res = await fetch('../api/mute_user.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    userId: userId,
                    action: 'unmute'
                })
            });
            const result = await res.json();
            
            if (result.success) {
                showToastMsg('User unmuted successfully');
                setTimeout(() => location.reload(), 1000);
            } else {
                showToastMsg(result.error || 'Failed to unmute user', 'error');
            }
        } catch (e) {
            console.error(e);
            showToastMsg('Server error', 'error');
        }
    }

    // Close modal when pressing Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeMuteDialog();
    });

    // Close modal when clicking outside
    document.getElementById('mute-modal').addEventListener('click', (e) => {
        if (e.target === document.getElementById('mute-modal')) closeMuteDialog();
    });
</script>

<?php require_once "includes/footer.php"; ?>
