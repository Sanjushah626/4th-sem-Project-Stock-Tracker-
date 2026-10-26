<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$id = $_GET['id'] ?? null;
if (!$id) {
    die("Invalid stock ID.");
}


$stmt = $pdo->prepare("
    SELECT s.* FROM stocks s
    INNER JOIN portfolios p ON p.id = s.portfolio_id
    WHERE s.id = ? AND p.user_id = ?
");
$stmt->execute([$id, $_SESSION['user_id']]);
$stock = $stmt->fetch();

if (!$stock) {
    die("Stock not found or you don't have permission to edit it.");
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $symbol        = strtoupper(trim($_POST['symbol']));
    $purchase_type = $_POST['purchase_type'];
    $quantity      = $_POST['quantity'];
    $buy_date      = $_POST['buy_date'];

    
    $buy_price = ($purchase_type === 'Bonus') ? 0 : $_POST['buy_price'];

    $stmt = $pdo->prepare("
        UPDATE stocks s
        INNER JOIN portfolios p ON p.id = s.portfolio_id
        SET s.symbol = :symbol, s.purchase_type = :purchase_type, s.quantity = :quantity,
            s.buy_price = :buy_price, s.buy_date = :buy_date
        WHERE s.id = :id AND p.user_id = :user_id
    ");

    $stmt->execute([
        ':symbol'        => $symbol,
        ':purchase_type' => $purchase_type,
        ':quantity'      => $quantity,
        ':buy_price'     => $buy_price,
        ':buy_date'      => $buy_date,
        ':id'            => $id,
        ':user_id'       => $_SESSION['user_id']
    ]);

    header("Location: portfolio.php");
    exit;
}

$purchaseTypeLower = strtolower($stock['purchase_type']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Stock — MyShareApp</title>
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

    .form-card small.hint {
      display: block;
      margin-top: 4px;
      color: var(--text-muted);
      font-size: 12px;
    }

    .field-hidden { display: none !important; }

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
      <h2>Edit Stock</h2>
      <p>Update the details for <?php echo htmlspecialchars(strtoupper($stock['symbol'])); ?>.</p>
    </div>

    <div class="form-card">
      <form method="POST">
        <label for="symbol">Symbol</label>
        <input type="text" id="symbol" name="symbol" style="text-transform:uppercase;"
               value="<?php echo htmlspecialchars($stock['symbol']); ?>" required>

        <label for="purchase_type">Transaction Type</label>
        <select id="purchase_type" name="purchase_type" required>
          <option value="Secondary" <?php if ($purchaseTypeLower == 'secondary') echo 'selected'; ?>>Secondary Buy</option>
          <option value="IPO" <?php if ($purchaseTypeLower == 'ipo') echo 'selected'; ?>>IPO</option>
          <option value="Bonus" <?php if ($purchaseTypeLower == 'bonus') echo 'selected'; ?>>Bonus Share</option>
        </select>
        <small class="hint" id="typeHint">Secondary market purchase — broker commission, SEBON fee &amp; DP charge apply.</small>

        <label for="quantity">Quantity</label>
        <input type="number" id="quantity" name="quantity" min="1"
               value="<?php echo htmlspecialchars($stock['quantity']); ?>" required>

        <div id="buyPriceField">
          <label for="buy_price">Buy Price (per share)</label>
          <input type="number" step="0.01" id="buy_price" name="buy_price"
                 value="<?php echo htmlspecialchars($stock['buy_price']); ?>">
        </div>

        <label for="buy_date">Buy Date</label>
        <input type="date" id="buy_date" name="buy_date"
               value="<?php echo htmlspecialchars($stock['buy_date']); ?>">

        <div class="form-actions">
          <a class="btn-cancel" href="portfolio.php">Cancel</a>
          <button type="submit">Update Stock</button>
        </div>
      </form>
    </div>
  </main>

  <script>
    
    const purchaseTypeSelect = document.getElementById("purchase_type");
    const buyPriceField = document.getElementById("buyPriceField");
    const buyPriceInput = document.getElementById("buy_price");
    const typeHint = document.getElementById("typeHint");

    function applyTransactionTypeRules() {
        const type = purchaseTypeSelect.value;

        if (type === "Bonus") {
            buyPriceField.classList.add("field-hidden");
            buyPriceInput.required = false;
            typeHint.textContent =
                "Bonus shares are received at zero cost and carry no buying charges.";
        } else if (type === "IPO") {
            buyPriceField.classList.remove("field-hidden");
            buyPriceInput.required = true;
            typeHint.textContent =
                "IPO allotment — no broker commission, SEBON fee or DP charge on buying.";
        } else {
            buyPriceField.classList.remove("field-hidden");
            buyPriceInput.required = true;
            typeHint.textContent =
                "Secondary market purchase — broker commission, SEBON fee & DP charge apply.";
        }
    }

    purchaseTypeSelect.addEventListener("change", applyTransactionTypeRules);
    applyTransactionTypeRules();
  </script>
</body>
</html>