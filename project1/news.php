<?php


session_start();
require 'db.php';

$logged_in = isset($_SESSION['user_id']);


$news = [];
$db_error = false;

try {
    $stmt = $pdo->prepare("SELECT * FROM news ORDER BY created_at DESC LIMIT 50");
    $stmt->execute();
    $news = $stmt->fetchAll();
} catch (Exception $e) {
    $db_error = true;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>MyShareApp — Market News</title>
  <link rel="stylesheet" href="style.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    :root {
  --bg-app: #f8fafc;
  --bg-surface: #ffffff;
  --bg-surface-solid: #ffffff;
  --bg-elevated: #f1f5f9;
  --bg-row-alt: #f8fafc;
  --bg-row-hover: #e2e8f0;
  --border: #e2e8f0;
  --border-soft: #f1f5f9;
  --text-primary: #1e293b;
  --text-secondary: #64748b;
  --text-muted: #94a3b8;
  --accent: #2563eb;
  --accent-dark: #1d4ed8;
  --accent-soft: rgba(37, 99, 235, 0.12);
  --green: #22c55e;
  --green-soft: rgba(34, 197, 94, 0.12);
  --red: #ef4444;
  --red-soft: rgba(239, 68, 68, 0.12);
  --amber: #f59e0b;
  --amber-soft: rgba(245, 158, 11, 0.14);
  --font-ui: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
  --font-num: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
  --primary: #2563eb;
  --primary-dark: #1d4ed8;
}

    * { box-sizing: border-box; }

    html { max-width: 100%; overflow-x: hidden; }

    body {
      background-color: var(--bg-app);
      color: var(--text-primary);
      font-family: var(--font-ui);
      max-width: 100vw;
      overflow-x: hidden;
    }

    header, nav, main, footer {
      max-width: 100%;
      overflow-x: hidden;
      box-sizing: border-box;
    }

    header { flex-wrap: wrap; }

    main {
      max-width: 900px;
      margin: 0 auto;
      padding: 0 16px 40px;
    }

    .page-heading {
      display: flex;
      align-items: baseline;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 10px;
      margin: 28px 0 8px;
    }

    .page-heading h2 {
      color: var(--text-primary);
      font-weight: 700;
      letter-spacing: -0.01em;
      margin: 0;
    }

    .page-heading .subtitle {
      color: var(--text-muted);
      font-size: 13px;
    }

    .news-list {
      display: flex;
      flex-direction: column;
      gap: 14px;
      margin-top: 18px;
    }

    .news-card {
      display: flex;
      gap: 18px;
      background: var(--bg-surface-solid);
      border: 1px solid var(--border);
      border-radius: 14px;
      padding: 18px 20px;
      transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    .news-card:hover {
      border-color: #cbd5e1;
      box-shadow: 0 8px 28px rgba(0, 0, 0, 0.06);
    }

    .news-card .thumb {
      flex: 0 0 auto;
      width: 140px;
      height: 100px;
      border-radius: 10px;
      overflow: hidden;
      background: var(--bg-elevated);
      border: 1px solid var(--border-soft);
    }

    .news-card .thumb img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
    }

    .news-card .body {
      flex: 1 1 auto;
      min-width: 0;
    }

    .news-card .date {
      font-size: 12px;
      color: var(--text-muted);
      font-family: var(--font-ui);
      display: block;
      margin-bottom: 6px;
    }

    .news-card h3 {
      margin: 0 0 8px;
      font-size: 17px;
      font-weight: 700;
      color: var(--text-primary);
      letter-spacing: -0.01em;
    }

    .news-card p {
      margin: 0;
      color: var(--text-secondary);
      font-size: 14px;
      line-height: 1.6;
    }

    @media (max-width: 560px) {
      .news-card { flex-direction: column; }
      .news-card .thumb { width: 100%; height: 160px; }
    }

    .empty-state {
      background: var(--bg-elevated);
      border: 1px dashed var(--border);
      border-radius: 14px;
      padding: 40px 24px;
      text-align: center;
      color: var(--text-muted);
      margin-top: 20px;
    }

    .empty-state strong {
      display: block;
      color: var(--text-secondary);
      font-size: 15px;
      margin-bottom: 6px;
    }
  </style>
</head>

<body>
  <header>
    <h1>MyShareApp</h1>
    <nav>
      <a href="hello.html">Home</a>
      <a href="market.html">Market</a>
      <?php if ($logged_in): ?><a href="portfolio.php">Portfolio</a><?php endif; ?>
      <a href="news.php">News</a>
      <a href="contact.php">Contact Us</a>
    </nav>
    <div id="right-section">
       <div id="right-section">
      <div id="market-status" aria-live="polite">
        <span class="dot closed"></span> Checking status...
      </div>
      <?php if ($logged_in): ?>
        <button class="login-btn" onclick="window.location.href='logout.php'">Logout</button>
      <?php else: ?>
        <button class="login-btn" onclick="window.location.href='login.php'">Login</button>
      <?php endif; ?>
    </div>
  </header>

  <main>
    <div class="page-heading">
      <h2>Market News</h2>
      <span class="subtitle">Latest updates from NEPSE &amp; the market</span>
    </div>

    <?php if ($db_error): ?>
      <div class="empty-state">
        <strong>News isn't set up yet</strong>
        The <code>news</code> table hasn't been created in the database, so there's nothing to show here yet.
      </div>
    <?php elseif (empty($news)): ?>
      <div class="empty-state">
        <strong>No news yet</strong>
        Check back soon — new market updates will show up here.
      </div>
    <?php else: ?>
      <div class="news-list">
        <?php foreach ($news as $n): ?>
          <div class="news-card">
            <?php if (!empty($n['image'])): ?>
              <div class="thumb">
              <img src="uploads/news/<?php echo htmlspecialchars($n['image']); ?>" alt="<?php echo htmlspecialchars($n['title']); ?>">
              </div>
            <?php endif; ?>
            <div class="body">
              <span class="date"><?php echo date('d M Y, g:i A', strtotime($n['created_at'])); ?></span>
              <h3><?php echo htmlspecialchars($n['title']); ?></h3>
              <?php if (!empty($n['description'])): ?>
                <p><?php echo nl2br(htmlspecialchars($n['description'])); ?></p>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </main>

  <footer class="foot" style="margin-top:80px;">
    <div class="footer-content">
      <p>© 2026 MyShareApp. All rights reserved.</p>
      <p>Data provided by NEPSE API | For Educational purposes only</p>
      <nav class="footer-nav">
        <a href="hello.html">Home</a> |
        <a href="market.html">Market</a> |
        <a href="news.php">News</a> |
        <a href="contact.php">Contact Us</a>
      </nav>
      <div class="social-icons">
        <a href="#">Facebook</a>
        <a href="#">Twitter</a>
        <a href="#">LinkedIn</a>
      </div>
    </div>
  </footer>
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
    setInterval(checkMarketStatus, 60000);
  </script>
</body>
</html>
