<?php

$pageTitle = 'Users';
require_once 'header.php';
require_once 'sidebar.php';


$deleteMessage = '';
if (isset($_GET['delete']) && ctype_digit($_GET['delete'])) {
    $deleteId = (int) $_GET['delete'];

    if ($deleteId === (int) $_SESSION['user_id']) {
        $deleteMessage = '<div class="alert alert-danger"><i class="fa-solid fa-circle-exclamation"></i> You cannot delete your own account.</div>';
    } else {
        $stmt = $pdo->prepare('DELETE FROM users WHERE id = :id');
        $stmt->execute(['id' => $deleteId]);
        $deleteMessage = '<div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> User deleted successfully.</div>';
    }
}


$usersStmt = $pdo->query('
    SELECT u.id, u.username, u.email, u.created_at,
           (SELECT COUNT(*) FROM portfolios p WHERE p.user_id = u.id) AS portfolio_count
    FROM users u
    ORDER BY u.created_at DESC
');
$users = $usersStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="page-header">
    <div>
        <h1>Users</h1>
        <p>Manage all registered MyShareApp users.</p>
    </div>
    <div class="search-box">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" class="search-input" data-search-target="usersTable" placeholder="Search users...">
    </div>
</div>

<?php echo $deleteMessage; ?>

<div class="card">
    <div class="table-responsive">
        <table class="data-table" id="usersTable">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Email</th>
                    <th>Portfolios</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr><td colspan="5" class="no-data">No users found.</td></tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td>
                                <div class="table-avatar">
                                    <div class="avatar-circle">
                                        <?php echo strtoupper(substr(htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'), 0, 1)); ?>
                                    </div>
                                    <div><?php echo htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'); ?></div>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><span class="badge badge-user"><?php echo (int) $user['portfolio_count']; ?></span></td>
                            <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                            <td class="action-buttons">
                                <a href="users.php?delete=<?php echo (int) $user['id']; ?>"
                                   class="btn btn-danger btn-sm btn-icon js-delete-btn"
                                   data-message="Delete user '<?php echo htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'); ?>'? This cannot be undone."
                                   title="Delete user">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'footer.php'; ?>
