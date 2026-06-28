<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_login();

$user_id = $_SESSION['user_id'];

// Fallback clean function if not in db.php
if (!function_exists('clean')) {
    function clean($data) {
        global $conn;
        return htmlspecialchars(mysqli_real_escape_string($conn, trim($data)), ENT_QUOTES, 'UTF-8');
    }
}

// Handle sending a message
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['message_text'])) {
    $receiver_id = intval($_POST['receiver_id']);
    $listing_id = intval($_POST['listing_id']);
    $message_text = clean($_POST['message_text']);
    
    if (!empty($message_text) && $receiver_id > 0) {
        $stmt = mysqli_prepare($conn, 
            "INSERT INTO messages (sender_id, receiver_id, listing_id, message, is_read, created_at) 
             VALUES (?, ?, ?, ?, 0, NOW())"
        );
        mysqli_stmt_bind_param($stmt, "iiis", $user_id, $receiver_id, $listing_id, $message_text);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    
    // Redirect back to same conversation
    header("Location: messages.php?to=$receiver_id&listing=$listing_id");
    exit;
}

// Get conversation partner and listing from URL
$to_user_id = isset($_GET['to']) ? intval($_GET['to']) : 0;
$listing_id = isset($_GET['listing']) ? intval($_GET['listing']) : 0;

// Mark messages as read when viewing
if ($to_user_id > 0) {
    $stmt = mysqli_prepare($conn, 
        "UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ? AND is_read = 0"
    );
    mysqli_stmt_bind_param($stmt, "ii", $to_user_id, $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

include 'includes/header.php';
?>

<div class="container" style="padding: 32px 20px; max-width: 1000px; margin: 0 auto;">
    <h1 style="margin-bottom: 24px;"><i class="ti ti-messages"></i> Messages</h1>

    <?php if ($to_user_id > 0): ?>
        <!-- VIEWING A SPECIFIC CONVERSATION -->
        
        <?php
        // Get other user's info
        $other_user_sql = "SELECT user_id, full_name, email, role FROM users WHERE user_id = $to_user_id";
        $other_user_result = mysqli_query($conn, $other_user_sql);
        $other_user = mysqli_fetch_assoc($other_user_result);
        
        // Get listing info if provided
        $listing_info = null;
        if ($listing_id > 0) {
            $listing_sql = "SELECT title, listing_id, image_url FROM listings WHERE listing_id = $listing_id";
            $listing_result = mysqli_query($conn, $listing_sql);
            $listing_info = mysqli_fetch_assoc($listing_result);
        }
        ?>

        <!-- Conversation Header -->
        <div style="background: var(--bg-card); border: 1px solid rgba(100,100,200,0.2); border-radius: 12px; padding: 16px 20px; margin-bottom: 20px; display: flex; align-items: center; gap: 16px;">
            <a href="messages.php" style="color: var(--text-light); text-decoration: none; font-size: 20px;">
                <i class="ti ti-arrow-left"></i>
            </a>
            <div style="width: 40px; height: 40px; background: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700;">
                <?php echo strtoupper(substr($other_user['full_name'], 0, 1)); ?>
            </div>
            <div>
                <h3 style="font-size: 16px; margin: 0;"><?php echo htmlspecialchars($other_user['full_name']); ?></h3>
                <p style="font-size: 13px; color: var(--text-light); margin: 0;">
                    <?php echo ucfirst($other_user['role']); ?> • <?php echo htmlspecialchars($other_user['email']); ?>
                </p>
            </div>
            <?php if ($listing_info): ?>
                <div style="margin-left: auto; display: flex; align-items: center; gap: 12px; background: var(--surface); padding: 8px 16px; border-radius: 8px;">
                    <?php if ($listing_info['image_url']): ?>
                        <img src="uploads/<?php echo htmlspecialchars($listing_info['image_url']); ?>" style="width: 40px; height: 40px; object-fit: cover; border-radius: 6px;">
                    <?php endif; ?>
                    <div>
                        <p style="font-size: 12px; color: var(--text-light); margin: 0;">About listing</p>
                        <a href="listing.php?id=<?php echo $listing_id; ?>" style="font-size: 13px; color: var(--primary); text-decoration: none;">
                            <?php echo htmlspecialchars($listing_info['title']); ?>
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Messages Thread -->
        <div style="background: var(--bg-card); border: 1px solid rgba(100,100,200,0.2); border-radius: 12px; padding: 20px; margin-bottom: 20px; max-height: 500px; overflow-y: auto;">
            <?php
            // Get all messages between these two users (about this listing or general)
            $thread_sql = "SELECT m.*, u.full_name as sender_name 
                           FROM messages m
                           JOIN users u ON m.sender_id = u.user_id
                           WHERE ((m.sender_id = $user_id AND m.receiver_id = $to_user_id) 
                              OR (m.sender_id = $to_user_id AND m.receiver_id = $user_id))
                           " . ($listing_id > 0 ? "AND m.listing_id = $listing_id" : "") . "
                           ORDER BY m.created_at ASC";
            $thread_result = mysqli_query($conn, $thread_sql);
            
            if (mysqli_num_rows($thread_result) == 0): ?>
                <div style="text-align: center; padding: 40px; color: var(--text-light);">
                    <i class="ti ti-message-circle" style="font-size: 48px; margin-bottom: 12px; display: block;"></i>
                    <p>No messages yet. Start the conversation below.</p>
                </div>
            <?php else: ?>
                <?php while ($msg = mysqli_fetch_assoc($thread_result)): 
                    $is_me = ($msg['sender_id'] == $user_id);
                ?>
                    <div style="display: flex; justify-content: <?php echo $is_me ? 'flex-end' : 'flex-start'; ?>; margin-bottom: 16px;">
                        <div style="max-width: 70%; padding: 12px 16px; border-radius: 16px; 
                                    background: <?php echo $is_me ? 'var(--primary)' : 'var(--surface)'; ?>; 
                                    color: <?php echo $is_me ? 'white' : 'var(--text)'; ?>;
                                    border-bottom-right-radius: <?php echo $is_me ? '4px' : '16px'; ?>;
                                    border-bottom-left-radius: <?php echo $is_me ? '16px' : '4px'; ?>;">
                            <p style="margin: 0; font-size: 14px; line-height: 1.5;"><?php echo nl2br(htmlspecialchars($msg['message'])); ?></p>
                            <p style="margin: 8px 0 0 0; font-size: 11px; opacity: 0.7; text-align: right;">
                                <?php echo date('M j, g:i A', strtotime($msg['created_at'])); ?>
                                <?php if ($is_me): ?>
                                    <i class="ti ti-check" style="margin-left: 4px;"></i>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>

        <!-- Reply Form -->
        <form method="POST" style="display: flex; gap: 12px; align-items: flex-end;">
            <input type="hidden" name="receiver_id" value="<?php echo $to_user_id; ?>">
            <input type="hidden" name="listing_id" value="<?php echo $listing_id; ?>">
            <textarea name="message_text" placeholder="Type your message..." required
                style="flex: 1; padding: 14px 16px; border: 1px solid rgba(100,100,200,0.3); border-radius: 12px; 
                       background: var(--bg-dark); color: var(--text); font-size: 14px; resize: none; height: 60px;"></textarea>
            <button type="submit" class="btn-primary" style="padding: 14px 24px; height: 60px;">
                <i class="ti ti-send"></i> Send
            </button>
        </form>

    <?php else: ?>
        <!-- CONVERSATIONS LIST VIEW -->
        
        <?php
        // Get all conversations for this user
        $conversations_sql = "
            SELECT 
                m.*,
                u.user_id as other_user_id,
                u.full_name as other_user_name,
                u.role as other_user_role,
                l.title as listing_title,
                l.image_url as listing_image,
                l.listing_id,
                (SELECT COUNT(*) FROM messages 
                 WHERE sender_id = u.user_id AND receiver_id = $user_id AND is_read = 0) as unread_count
            FROM messages m
            JOIN users u ON (
                (m.sender_id = $user_id AND m.receiver_id = u.user_id) OR
                (m.receiver_id = $user_id AND m.sender_id = u.user_id)
            )
            LEFT JOIN listings l ON m.listing_id = l.listing_id
            WHERE m.message_id IN (
                SELECT MAX(message_id) 
                FROM messages 
                WHERE sender_id = $user_id OR receiver_id = $user_id
                GROUP BY LEAST(sender_id, receiver_id), GREATEST(sender_id, receiver_id)
            )
            ORDER BY m.created_at DESC";
        
        $conversations_result = mysqli_query($conn, $conversations_sql);
        ?>

        <?php if (mysqli_num_rows($conversations_result) == 0): ?>
            <div style="text-align: center; padding: 80px 20px;">
                <div style="width: 120px; height: 120px; background: var(--surface); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px;">
                    <i class="ti ti-message-circle-off" style="font-size: 56px; color: var(--text-light);"></i>
                </div>
                <h2 style="margin-bottom: 8px; font-size: 24px;">No messages yet</h2>
                <p style="color: var(--text-light); margin-bottom: 32px; font-size: 16px;">
                    When buyers message you about your listings, or you message sellers, conversations will appear here.
                </p>
                <a href="listings.php" class="btn-primary" style="padding: 14px 32px; font-size: 16px;">
                    <i class="ti ti-search"></i> Browse Listings
                </a>
            </div>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <?php while ($conv = mysqli_fetch_assoc($conversations_result)): 
                    $is_me_sender = ($conv['sender_id'] == $user_id);
                    $other_id = $is_me_sender ? $conv['receiver_id'] : $conv['sender_id'];
                    $listing_param = $conv['listing_id'] ? '&listing=' . $conv['listing_id'] : '';
                ?>
                    <a href="messages.php?to=<?php echo $other_id . $listing_param; ?>" 
                       style="text-decoration: none; color: inherit;">
                        <div style="background: var(--bg-card); border: 1px solid rgba(100,100,200,0.2); border-radius: 12px; padding: 16px 20px; display: flex; align-items: center; gap: 16px; transition: all 0.2s;"
                             onmouseover="this.style.borderColor='var(--primary)'" 
                             onmouseout="this.style.borderColor='rgba(100,100,200,0.2)'">
                            
                            <!-- Avatar -->
                            <div style="width: 48px; height: 48px; background: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; flex-shrink: 0;">
                                <?php echo strtoupper(substr($conv['other_user_name'], 0, 1)); ?>
                            </div>
                            
                            <!-- Info -->
                            <div style="flex: 1; min-width: 0;">
                                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                                    <h3 style="font-size: 16px; margin: 0;"><?php echo htmlspecialchars($conv['other_user_name']); ?></h3>
                                    <?php if ($conv['unread_count'] > 0): ?>
                                        <span style="background: var(--danger); color: white; font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 10px;">
                                            <?php echo $conv['unread_count']; ?> new
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <p style="font-size: 13px; color: var(--text-light); margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    <?php if ($conv['listing_title']): ?>
                                        <i class="ti ti-device-mobile" style="margin-right: 4px;"></i>
                                        <?php echo htmlspecialchars($conv['listing_title']); ?> • 
                                    <?php endif; ?>
                                    <?php echo htmlspecialchars(substr($conv['message'], 0, 60)) . (strlen($conv['message']) > 60 ? '...' : ''); ?>
                                </p>
                            </div>
                            
                            <!-- Time -->
                            <div style="text-align: right; flex-shrink: 0;">
                                <p style="font-size: 12px; color: var(--text-light); margin: 0;">
                                    <?php 
                                    $msg_time = strtotime($conv['created_at']);
                                    $now = time();
                                    $diff = $now - $msg_time;
                                    if ($diff < 86400) {
                                        echo date('g:i A', $msg_time);
                                    } else {
                                        echo date('M j', $msg_time);
                                    }
                                    ?>
                                </p>
                            </div>
                        </div>
                    </a>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>