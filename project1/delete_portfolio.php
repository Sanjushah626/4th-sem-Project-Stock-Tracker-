<?php


session_start();
require 'db.php';


if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = (int) $_SESSION['user_id'];
$id = $_GET['id'] ?? $_POST['id'] ?? null;

if (!$id || !ctype_digit((string) $id)) {
    header('Location: portfolio.php');
    exit;
}
$portfolioId = (int) $id;


$stmt = $pdo->prepare('SELECT id, portfolio_name, portfolio_type FROM portfolios WHERE id = :id AND user_id = :user_id');
$stmt->execute(['id' => $portfolioId, 'user_id' => $userId]);
$portfolio = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$portfolio) {
    header('Location: portfolio.php');
    exit;
}


$countStmt = $pdo->prepare('SELECT COUNT(*) FROM stocks WHERE portfolio_id = :id');
$countStmt->execute(['id' => $portfolioId]);
$stockCount = (int) $countStmt->fetchColumn();


$deleteError = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare('DELETE FROM stocks WHERE portfolio_id = :id');
        $stmt->execute(['id' => $portfolioId]);

        $stmt = $pdo->prepare('DELETE FROM portfolios WHERE id = :id AND user_id = :user_id');
        $stmt->execute(['id' => $portfolioId, 'user_id' => $userId]);

        $pdo->commit();

        header('Location: portfolio.php?status=portfolio_deleted');
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        // Log the real error server-side instead of showing it to the user
        error_log('delete_portfolio.php error: ' . $e->getMessage());
        $deleteError = 'Something went wrong while deleting the portfolio. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MyShareApp — Confirm Delete</title>
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
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    header, nav, main, footer {
      max-width: 100%;
      overflow-x: hidden;
      box-sizing: border-box;
    }

    main.confirm-wrap {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 40px 16px;
    }

    .confirm-card {
      width: 100%;
      max-width: 460px;
      background: var(--bg-surface-solid);
      border: 1px solid var(--border);
      border-radius: 16px;
      padding: 32px 28px;
      text-align: center;
      box-shadow: 0 12px 36px rgba(0, 0, 0, 0.15);
      animation: fadeInUp 0.35s ease both;
    }

    .confirm-icon {
      width: 64px;
      height: 64px;
      margin: 0 auto 18px;
      border-radius: 50%;
      background: var(--red-soft);
      color: var(--red);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 28px;
    }

    .confirm-card h2 {
      margin: 0 0 10px;
      font-size: 19px;
      font-weight: 700;
      letter-spacing: -0.01em;
    }

    .confirm-card p {
      margin: 0 0 20px;
      color: var(--text-secondary);
      font-size: 14px;
      line-height: 1.6;
    }

    .portfolio-summary {
      background: var(--bg-elevated);
      border: 1px solid var(--border-soft);
      border-radius: 12px;
      padding: 14px 18px;
      margin-bottom: 18px;
      text-align: left;
      font-size: 13px;
    }

    .portfolio-summary .row {
      display: flex;
      justify-content: space-between;
      padding: 4px 0;
      color: var(--text-secondary);
    }

    .portfolio-summary .row strong {
      color: var(--text-primary);
      font-weight: 600;
      font-family: var(--font-ui);
    }

    .warning-note {
      display: flex;
      align-items: flex-start;
      gap: 10px;
      background: var(--amber-soft);
      border: 1px solid rgba(242, 169, 60, 0.3);
      color: var(--amber);
      border-radius: 10px;
      padding: 12px 14px;
      font-size: 12.5px;
      text-align: left;
      line-height: 1.5;
      margin-bottom: 22px;
    }

    .error-note {
      background: var(--red-soft);
      border: 1px solid rgba(240, 71, 94, 0.3);
      color: var(--red);
      border-radius: 10px;
      padding: 12px 14px;
      font-size: 13px;
      margin-bottom: 18px;
    }

    .confirm-actions {
      display: flex;
      gap: 12px;
    }

    .btn {
      flex: 1;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      padding: 12px 16px;
      border-radius: 10px;
      font-size: 14px;
      font-weight: 600;
      font-family: var(--font-ui);
      cursor: pointer;
      border: none;
      text-decoration: none;
      transition: transform 0.12s ease, opacity 0.12s ease;
    }

    .btn:hover { transform: translateY(-1px); }

    .btn-cancel {
      background: transparent;
      border: 1px solid var(--border);
      color: var(--text-secondary);
    }
    .btn-cancel:hover { border-color: #cbd5e1; color: var(--text-primary); }

    .btn-confirm-delete {
      background: var(--red);
      color: #fff;
    }
    .btn-confirm-delete:hover { background: var(--red-dark); }

    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>

<body>
  <header>
    <h1>MyShareApp</h1>
    <nav>
      <a href="hello.html">Home</a>
      <a href="market.html">Market</a>
      <a href="portfolio.php">Portfolio</a>
      <a href="news.php">News</a>
      <a href="contact.php">Contact Us</a>
    </nav>
    <div id="right-section">
      <button class="login-btn" onclick="window.location.href='logout.php'">Logout</button>
    </div>
  </header>

  <main class="confirm-wrap">
    <div class="confirm-card">
      <div class="confirm-icon">&#9888;</div>
      <h2>Delete this portfolio?</h2>
      <p>This will permanently remove the portfolio and everything in it. This action cannot be undone.</p>

      <?php if ($deleteError): ?>
        <div class="error-note"><?php echo htmlspecialchars($deleteError, ENT_QUOTES, 'UTF-8'); ?></div>
      <?php endif; ?>

      <div class="portfolio-summary">
        <div class="row"><span>Portfolio</span> <strong><?php echo htmlspecialchars($portfolio['name'] ?? ('#' . $portfolio['id']), ENT_QUOTES, 'UTF-8'); ?></strong></div>
        <div class="row"><span>Stocks inside</span> <strong><?php echo $stockCount; ?></strong></div>
      </div>

      <?php if ($stockCount > 0): ?>
        <div class="warning-note">
          &#9888; This portfolio contains <?php echo $stockCount; ?> stock<?php echo $stockCount === 1 ? '' : 's'; ?>. Deleting the portfolio will delete <?php echo $stockCount === 1 ? 'it' : 'them all'; ?> too.
        </div>
      <?php endif; ?>

      <form method="POST" action="delete_portfolio.php">
        <input type="hidden" name="id" value="<?php echo (int) $portfolio['id']; ?>">
        <div class="confirm-actions">
          <a href="portfolio.php" class="btn btn-cancel">Cancel</a>
          <button type="submit" class="btn btn-confirm-delete">Delete Portfolio</button>
        </div>
      </form>
    </div>
  </main>

  <footer class="foot" style="margin-top:0;">
    <div class="footer-content">
      <p>© 2026 MyShareApp. All rights reserved.</p>
    </div>
  </footer>
</body>
</html>