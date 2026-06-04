<?php

// Admin can view, verify sellers, and ban users

require_once 'includes/db.php';
require_once 'includes/auth.php';
require_admin();

if (!isset($conn) || !($conn instanceof mysqli)) {
    die('Database connection not established.');
}

// Handle seller verification
if (isset($_GET['verify'])) {
    $user_id = intval($_GET['verify']);
    mysqli_query($conn, "UPDATE users SET is_verified = 1 WHERE user_id = $user_id");
    log_admin_action($conn, "Verified seller", "user", $user_id);
    header("Location: admin_users.php");
    exit();
}

// Handle user deletion
if (isset($_GET['delete'])) {
    $user_id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM users WHERE user_id = $user_id");
    log_admin_action($conn, "Deleted user", "user", $user_id);
    header("Location: admin_users.php");
    exit();
}

// Fetch all users
$users_sql = "SELECT * FROM users ORDER BY created_at DESC";
$users_result = mysqli_query($conn, $users_sql);

include 'includes/header.php';
?>

<div class="admin-layout">
    <aside class="admin-sidebar">
        <div class="sidebar-brand"><i class="ti ti-shield"></i> Admin Panel</div>
        <a href="/techtrade/admin_dashboard.php"><i class="ti ti-layout-dashboard"></i> Overview</a>
        <a href="/techtrade/admin_users.php" class="active"><i class="ti ti-users"></i> Users</a>
        <a href="/techtrade/admin_listings.php"><i class="ti ti-shopping-bag"></i> Listings</a>
        <a href="/techtrade/admin_transactions.php"><i class="ti ti-transfer"></i> Transactions</a>
        <a href="/techtrade/index.php"><i class="ti ti-arrow-left"></i> Back to Site</a>
    </aside>

    <main class="admin-main">
        <div class="dashboard-header">
            <h1>Manage Users</h1>
            <p>View and manage all registered users</p>
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Location</th>
                    <th>Verified</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = mysqli_fetch_assoc($users_result)): ?>
                <tr>
                    <td><strong><?php echo $user['full_name']; ?></strong></td>
                    <td><?php echo $user['email']; ?></td>
                    <td><span class="badge badge-<?php echo $user['role']; ?>"><?php echo ucfirst($user['role']); ?></span></td>
                    <td><?php echo $user['location']; ?></td>
                    <td>
                        <?php if ($user['role'] == 'seller'): ?>
                            <?php if ($user['is_verified']): ?>
                                <span class="badge badge-verified"><i class="ti ti-check"></i> Yes</span>
                            <?php else: ?>
                                <span class="badge badge-pending">Pending</span>
                            <?php endif; ?>
                        <?php else: ?>
                            <span style="color: var(--text-light);">-</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo date('d M Y', strtotime($user['created_at'])); ?></td>
                    <td>
                        <?php if ($user['role'] == 'seller' && !$user['is_verified']): ?>
                            <a href="admin_users.php?verify=<?php echo $user['user_id']; ?>" class="btn-small btn-verify">Verify</a>
                        <?php endif; ?>
                        <?php if ($user['role'] != 'admin'): ?>
                            <a href="admin_users.php?delete=<?php echo $user['user_id']; ?>" 
                               class="btn-small btn-delete"
                               onclick="return confirmDelete('Are you sure you want to delete this user?')">Delete</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </main>
</div>

<?php include 'includes/footer.php'; ?>