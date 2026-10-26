<?php

$pageTitle = 'News Management';
require_once 'header.php';
require_once 'sidebar.php';



$newsList = $pdo->query('SELECT id, title, description, image, created_at FROM news ORDER BY created_at DESC')
                 ->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="page-header">
    <div>
        <h1>News Management</h1>
        <p>Create, edit, and remove news articles shown to users.</p>
    </div>
    <a href="add_news.php" class="btn btn-primary">
        <i class="fa-solid fa-plus"></i> Add News
    </a>
</div>

<?php if (isset($_GET['status']) && $_GET['status'] === 'created'): ?>
    <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> News article created successfully.</div>
<?php elseif (isset($_GET['status']) && $_GET['status'] === 'updated'): ?>
    <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> News article updated successfully.</div>
<?php elseif (isset($_GET['status']) && $_GET['status'] === 'deleted'): ?>
    <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> News article deleted successfully.</div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h2>All News Articles</h2>
        <div class="search-box">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" class="search-input" data-search-target="newsTable" placeholder="Search news...">
        </div>
    </div>

    <div class="table-responsive">
        <table class="data-table" id="newsTable">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($newsList)): ?>
                    <tr><td colspan="5" class="no-data">No news articles yet. Click "Add News" to create one.</td></tr>
                <?php else: ?>
                    <?php foreach ($newsList as $news): ?>
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
                            <td><?php echo htmlspecialchars(mb_strimwidth($news['description'], 0, 70, '...'), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo date('M d, Y', strtotime($news['created_at'])); ?></td>
                            <td class="action-buttons">
                                <a href="edit_news.php?id=<?php echo (int) $news['id']; ?>"
                                   class="btn btn-outline btn-sm btn-icon" title="Edit">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                                <a href="delete_news.php?id=<?php echo (int) $news['id']; ?>"
                                   class="btn btn-danger btn-sm btn-icon js-delete-btn"
                                   data-message="Delete news article '<?php echo htmlspecialchars($news['title'], ENT_QUOTES, 'UTF-8'); ?>'?"
                                   title="Delete">
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
<script>
    async function checkMarketStatus() {
    try {
      const response = await fetch("http://localhost:8000/IsNepseOpen");
      const data = await response.json();

      const statusDiv = document.getElementById("market-status");

      if (data.isOpen === "OPEN") {
        statusDiv.innerHTML = '<span class="dot open"></span> Market is Open';
      } else {
        statusDiv.innerHTML = '<span class="dot closed"></span> Market is Closed';
      }
    } catch (error) {
      console.error("Error fetching market status:", error);
      document.getElementById("market-status").innerHTML =
        '<span class="dot closed"></span> Error fetching status';
    }
  }

  checkMarketStatus();
</script>
<?php require_once 'footer.php'; ?>
