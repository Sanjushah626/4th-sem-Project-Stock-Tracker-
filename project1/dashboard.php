<?php

$pageTitle = 'Dashboard';
require_once 'admin-db.php';
require_once 'header.php';
require_once 'sidebar.php';




$totalUsers = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();


$totalPortfolios = (int) $pdo->query('SELECT COUNT(*) FROM portfolios')->fetchColumn();


try {
    $totalNews = (int) $pdo->query('SELECT COUNT(*) FROM news')->fetchColumn();
} catch (PDOException $e) {
    $totalNews = 0;
}


try {
    $totalContacts = (int) $pdo->query('SELECT COUNT(*) FROM contacts')->fetchColumn();
} catch (PDOException $e) {
    $totalContacts = 0;
}


$recentUsersStmt = $pdo->query('
    SELECT u.id, u.username, u.email, u.created_at,
           (SELECT COUNT(*) FROM portfolios p WHERE p.user_id = u.id) AS portfolio_count
    FROM users u
    ORDER BY u.created_at DESC
    LIMIT 5
');
$recentUsers = $recentUsersStmt->fetchAll(PDO::FETCH_ASSOC);

$latestContacts = [];
try {
    $latestContacts = $pdo->query('
        SELECT id, name, email, subject, created_at
        FROM contacts
        ORDER BY created_at DESC
        LIMIT 5
    ')->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $latestContacts = [];
}

$latestNews = [];
try {
    $latestNews = $pdo->query('
        SELECT id, title, image, created_at
        FROM news
        ORDER BY created_at DESC
        LIMIT 5
    ')->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $latestNews = [];
}
?>

<div class="page-header">
    <div>
        <h1>Dashboard</h1>
        <p>Welcome back, <?php echo $adminUsername; ?>. Here's what's happening today.</p>
    </div>
</div>


<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fa-solid fa-users"></i></div>
        <div class="stat-info">
            <h3><?php echo number_format($totalUsers); ?></h3>
            <p>Total Users</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon green"><i class="fa-solid fa-briefcase"></i></div>
        <div class="stat-info">
            <h3><?php echo number_format($totalPortfolios); ?></h3>
            <p>Total Portfolios</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon orange"><i class="fa-solid fa-newspaper"></i></div>
        <div class="stat-info">
            <h3><?php echo number_format($totalNews); ?></h3>
            <p>Total Published News</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon purple"><i class="fa-solid fa-envelope"></i></div>
        <div class="stat-info">
            <h3><?php echo number_format($totalContacts); ?></h3>
            <p>Contact Messages</p>
        </div>
    </div>
</div>

<div class="dashboard-columns">

    <!-- Recent Users -->
    <div class="card">
        <div class="card-header">
            <h2>Recent Users</h2>
            <a href="users.php" class="btn btn-outline btn-sm">View All</a>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Portfolios</th>
                        <th>Joined</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentUsers)): ?>
                        <tr><td colspan="3" class="no-data">No users found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($recentUsers as $user): ?>
                            <tr>
                                <td>
                                    <div class="table-avatar">
                                        <div class="avatar-circle">
                                            <?php echo strtoupper(substr(htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'), 0, 1)); ?>
                                        </div>
                                        <div>
                                            <div><?php echo htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'); ?></div>
                                            <small style="color:var(--color-text-muted);"><?php echo htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo (int) $user['portfolio_count']; ?></td>
                                <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

  
    <div class="card">
        <div class="card-header">
            <h2>Latest Contact Messages</h2>
            <a href="contacts.php" class="btn btn-outline btn-sm">View All</a>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Subject</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($latestContacts)): ?>
                        <tr><td colspan="3" class="no-data">No messages yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($latestContacts as $contact): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($contact['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo htmlspecialchars($contact['subject'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo date('M d, Y', strtotime($contact['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

   
    <div class="card">
        <div class="card-header">
            <h2>Latest News</h2>
            <a href="newss.php" class="btn btn-outline btn-sm">View All</a>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Title</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($latestNews)): ?>
                        <tr><td colspan="3" class="no-data">No news published yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($latestNews as $news): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($news['image'])): ?>
                                        <img src="uploads/news/<?php echo htmlspecialchars($news['image'], ENT_QUOTES, 'UTF-8'); ?>" class="news-thumb" alt="News image">
                                    <?php else: ?>
                                        <div class="news-thumb" style="background:var(--color-bg);display:flex;align-items:center;justify-content:center;">
                                            <i class="fa-solid fa-image" style="color:var(--color-text-muted);"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($news['title'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td><?php echo date('M d, Y', strtotime($news['created_at'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php require_once 'footer.php'; ?>
