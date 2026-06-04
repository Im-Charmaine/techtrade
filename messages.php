<?php

// Users can chat about listings without sharing personal contact info

require_once 'includes/db.php';
require_once 'includes/auth.php';
require_login();

$user_id = $_SESSION['user_id'];

if (!isset($conn) || !($conn instanceof mysqli)) {
    die('Database connection not established.');
}
// Handle sending a message
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['message'])) {
    $receiver_id = intval($_POST['receiver_id']);
    $listing_id = intval($_POST['listing_id']);
    $message = clean($_POST['message']);

    if ($message != '') {
        $sql = "INSERT INTO messages (sender_id, receiver_id, listing_id, message)
                VALUES ($user_id, $receiver_id, $listing_id, '$message')";
        mysqli_query($conn, $sql);
    }

    header("Location: messages.php?to=$receiver_id&listing=$listing_id");
    exit();
}

// Get conversation partner from URL
$chat_with = isset($_GET['to']) ? intval($_GET['to']) : 0;
$listing_id = isset($_GET['listing']) ? intval($_GET['listing']) : 0;

// Fetch all conversations for this user
$conv_sql = "SELECT DISTINCT 
                    CASE WHEN sender_id = $user_id THEN receiver_id ELSE sender_id END as other_id,
                    u.full_name as other_name,
                    MAX(m.created_at) as last_message_time,
                    (SELECT message FROM messages 
                     WHERE (sender_id = $user_id AND receiver_id = other_id) 
                        OR (sender_id = other_id AND receiver_id = $user_id)
                     ORDER BY created_at DESC LIMIT 1) as last_message,
                    (SELECT COUNT(*) FROM messages WHERE receiver_id = $user_id AND sender_id = other_id AND is_read = 0) as unread
             FROM messages m
             JOIN users u ON u.user_id = CASE WHEN m.sender_id = $user_id THEN m.receiver_id ELSE m.sender_id END
             WHERE m.sender_id = $user_id OR m.receiver_id = $user_id
             GROUP BY other_id, u.full_name
             ORDER BY last_message_time DESC";
$conv_result = mysqli_query($conn, $conv_sql);

// Fetch messages for active conversation
$messages = [];
$other_user = null;
if ($chat_with > 0) {
    $msg_sql = "SELECT m.*, u.full_name as sender_name
                FROM messages m
                JOIN users u ON m.sender_id = u.user_id
                WHERE (m.sender_id = $user_id AND m.receiver_id = $chat_with)
                   OR (m.sender_id = $chat_with AND m.receiver_id = $user_id)
                ORDER BY m.created_at ASC";
    $msg_result = mysqli_query($conn, $msg_sql);
    while ($row = mysqli_fetch_assoc($msg_result)) {
        $messages[] = $row;
    }

    // Mark messages as read
    mysqli_query($conn, "UPDATE messages SET is_read = 1 WHERE sender_id = $chat_with AND receiver_id = $user_id");

    // Get other user info
    $other_sql = "SELECT * FROM users WHERE user_id = $chat_with";
    $other_result = mysqli_query($conn, $other_sql);
    $other_user = mysqli_fetch_assoc($other_result);
}

include 'includes/header.php';
?>

<section class="dashboard container">
    <h2 class="section-title">Messages</h2>

    <div class="messages-layout">
        <!-- Conversations List -->
        <div class="conversations-list">
            <?php if (mysqli_num_rows($conv_result) > 0): ?>
                <?php while ($conv = mysqli_fetch_assoc($conv_result)): ?>
                <a href="/techtrade/messages.php?to=<?php echo $conv['other_id']; ?>" 
                   class="conversation-item <?php echo $chat_with == $conv['other_id'] ? 'active' : ''; ?>">
                    <h4>
                        <?php echo $conv['other_name']; ?>
                        <?php if ($conv['unread'] > 0): ?>
                            <span style="background: var(--accent); color: white; padding: 2px 8px; border-radius: 10px; font-size: 11px; margin-left: 8px;"><?php echo $conv['unread']; ?></span>
                        <?php endif; ?>
                    </h4>
                    <p><?php echo substr($conv['last_message'], 0, 40); ?><?php echo strlen($conv['last_message']) > 40 ? '...' : ''; ?></p>
                </a>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="padding: 32px; text-align: center; color: var(--text-light);">
                    <i class="ti ti-message-circle" style="font-size: 32px; margin-bottom: 8px; display: block;"></i>
                    <p>No conversations yet</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Chat Area -->
        <div class="chat-area">
            <?php if ($chat_with > 0 && $other_user): ?>
                <div style="border-bottom: 1px solid var(--border); padding-bottom: 12px; margin-bottom: 16px;">
                    <h4><?php echo $other_user['full_name']; ?></h4>
                    <p style="font-size: 13px; color: var(--text-light);"><i class="ti ti-map-pin"></i> <?php echo $other_user['location']; ?></p>
                </div>

                <div class="chat-messages">
                    <?php foreach ($messages as $msg): ?>
                    <div class="chat-bubble <?php echo $msg['sender_id'] == $user_id ? 'sent' : 'received'; ?>">
                        <?php echo $msg['message']; ?>
                        <div style="font-size: 11px; opacity: 0.7; margin-top: 4px;">
                            <?php echo date('d M H:i', strtotime($msg['created_at'])); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <form method="POST" class="chat-input">
                    <input type="hidden" name="receiver_id" value="<?php echo $chat_with; ?>">
                    <input type="hidden" name="listing_id" value="<?php echo $listing_id; ?>">
                    <input type="text" name="message" placeholder="Type your message..." required>
                    <button type="submit" class="btn-primary" style="padding: 12px 16px;">
                        <i class="ti ti-send"></i>
                    </button>
                </form>
            <?php else: ?>
                <div style="display: flex; align-items: center; justify-content: center; height: 100%; color: var(--text-light);">
                    <div style="text-align: center;">
                        <i class="ti ti-message-circle" style="font-size: 48px; margin-bottom: 16px; display: block;"></i>
                        <p>Select a conversation to start chatting</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>