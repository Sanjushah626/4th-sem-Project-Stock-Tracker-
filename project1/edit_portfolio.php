<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$id = $_GET['id'] ?? null;
if (!$id) {
    die("Invalid portfolio ID.");
}

// Fetch portfolio record
$stmt = $pdo->prepare("SELECT * FROM portfolios WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $_SESSION['user_id']]);
$portfolio = $stmt->fetch();

if (!$portfolio) {
    die("Portfolio not found or you don’t have permission to edit it.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['portfolio_name'];
    $type = $_POST['portfolio_type'];

    $stmt = $pdo->prepare("UPDATE portfolios 
                           SET portfolio_name = :name, portfolio_type = :type 
                           WHERE id = :id AND user_id = :user_id");
    $stmt->execute([
        ':name' => $name,
        ':type' => $type,
        ':id'   => $id,
        ':user_id' => $_SESSION['user_id']
    ]);

    header("Location: portfolio.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Portfolio — MyShareApp</title>
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
      max-width: 480px;
      margin: 0 auto;
      padding: 0 16px 60px;
    }

    .page-heading {
      margin: 32px 0 6px;
    }

    .page-heading h2 {
      color: var(--text-primary);
      font-weight: 700;
      letter-spacing: -0.01em;
      margin: 0 0 6px;
    }

    .page-heading p {
      color: var(--text-muted);
      font-size: 13.5px;
      margin: 0;
    }

    .form-card {
      background: var(--bg-surface-solid);
      border: 1px solid var(--border);
      border-radius: 14px;
      padding: 28px 30px;
      margin-top: 20px;
    }

    .form-card label {
      display: block;
      margin-top: 16px;
      font-weight: 600;
      font-size: 13px;
      color: var(--text-secondary);
    }

    .form-card label:first-of-type { margin-top: 0; }

    .form-card input,
    .form-card select {
      width: 100%;
      padding: 10px 12px;
      margin-top: 6px;
      background: var(--bg-elevated);
      border: 1px solid var(--border);
      border-radius: 8px;
      font-size: 14px;
      color: var(--text-primary);
      font-family: var(--font-ui);
    }

    .form-card input:focus,
    .form-card select:focus {
      outline: none;
      border-color: var(--accent);
      box-shadow: 0 0 0 3px var(--accent-soft);
    }

    .form-actions {
      display: flex;
      gap: 10px;
      margin-top: 22px;
    }

    .form-card button {
      flex: 1;
      padding: 12px;
      background: var(--accent);
      color: #06201c;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 700;
      font-size: 14px;
    }

    .form-card button:hover { background: var(--accent-dark); }

    .btn-cancel {
      flex: 1;
      padding: 12px;
      background: transparent;
      color: var(--text-secondary);
      border: 1px solid var(--border);
      border-radius: 8px;
      cursor: pointer;
      font-weight: 600;
      font-size: 14px;
      text-align: center;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      justify-content: center;
    }

    .btn-cancel:hover {
      border-color: var(--text-secondary);
      color: var(--text-primary);
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

  <main>
    <div class="page-heading">
      <h2>Edit Portfolio</h2>
      <p>Update the name or type of this portfolio.</p>
    </div>

    <div class="form-card">
      <form method="POST">
        <label for="portfolio_name">Portfolio Name</label>
        <input type="text" id="portfolio_name" name="portfolio_name"
               value="<?php echo htmlspecialchars($portfolio['portfolio_name']); ?>" required>

        <label for="portfolio_type">Portfolio Type</label>
        <select id="portfolio_type" name="portfolio_type" required>
          <option value="personal" <?php if ($portfolio['portfolio_type'] == 'personal') echo 'selected'; ?>>Personal</option>
          <option value="institutional" <?php if ($portfolio['portfolio_type'] == 'institutional') echo 'selected'; ?>>Institutional</option>
        </select>

        <div class="form-actions">
          <a class="btn-cancel" href="portfolio.php">Cancel</a>
          <button type="submit">Update Portfolio</button>
        </div>
      </form>
    </div>
  </main>
</body>
</html>
