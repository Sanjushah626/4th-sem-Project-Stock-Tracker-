<?php
session_start();
require 'db.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM portfolios WHERE user_id = ?");
$stmt->execute([$user_id]);
$portfolios = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>MyShareApp Market Dashboard</title>
<link rel="stylesheet" href="style.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
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
header,nav,main,footer {
max-width: 100%;
overflow-x: hidden;
box-sizing: border-box;
}
header { flex-wrap: wrap; }
main {
max-width: 1180px;
margin: 0 auto;
padding: 0 16px 40px;
}
#addPortfolioBtn {
padding: 12px 24px;
background: var(--accent);
color: #ffffff;
border: none;
border-radius: 10px;
cursor: pointer;
font-weight: 700;
font-size: 14px;
box-shadow: 0 2px 8px rgba(37,99,235,0.15);
transition: all 0.2s ease;
margin: 20px 0 4px;
display: inline-flex;
align-items: center;
gap: 6px;
}
#addPortfolioBtn:hover {
background: var(--accent-dark);
transform: translateY(-2px);
box-shadow: 0 4px 12px rgba(37,99,235,0.20);
}
.modal {
display: none;
position: fixed;
z-index: 1000;
left: 0;
top: 0;
width: 100%;
height: 100%;
background: rgba(15,23,42,0.5);

}
.modal-content {
background: var(--bg-elevated);
border: 1px solid var(--border);
margin: 8% auto;
padding: 26px 32px 32px;
width: 420px;
max-width: 90%;
border-radius: 16px;
box-shadow: 0 20px 60px rgba(0,0,0,0.06);
animation: fadeIn 0.25s ease-in-out;
}
.close {
float: right;
font-size: 22px;
font-weight: bold;
cursor: pointer;
color: var(--text-muted);
line-height: 1;
transition: color 0.15s ease;
}
.close:hover { color: var(--text-primary); }
.modal-content h2 {
text-align: center;
margin-bottom: 20px;
color: var(--text-primary);
font-size: 20px;
letter-spacing: -0.01em;
}
.modal-content form label {
display: block;
margin-top: 12px;
font-weight: 600;
font-size: 13px;
color: var(--text-secondary);
}
.modal-content form input,
.modal-content form select {
width: 100%;
padding: 10px 12px;
margin-top: 5px;
background: var(--bg-surface-solid);
border: 1px solid var(--border);
border-radius: 10px;
font-size: 14px;
color: var(--text-primary);
box-sizing: border-box;
font-family: var(--font-ui);
transition: border-color 0.15s ease,box-shadow 0.15s ease;
}
.modal-content form input:focus,
.modal-content form select:focus {
outline: none;
border-color: var(--accent);
box-shadow: 0 0 0 3px var(--accent-soft);
}
.modal-content form input:disabled {
opacity: 0.6;
cursor: not-allowed;
}
.modal-content form small.hint {
display: block;
margin-top: 4px;
color: var(--text-muted);
font-size: 12px;
line-height: 1.5;
}
.field-hidden { display: none !important; }
.modal-content form button {
margin-top: 20px;
width: 100%;
padding: 12px;
background: var(--accent);
color: #ffffff;
border: none;
border-radius: 10px;
cursor: pointer;
font-weight: 700;
font-size: 14px;
transition: background 0.15s ease;
}
.modal-content form button:hover { background: var(--accent-dark); }
#sellStockModal .modal-content form button {
background: var(--red);
color: #fff;
}
#sellStockModal .modal-content form button:hover { background: #e11d48; }
h2.page-title {
color: var(--text-primary) !important;
font-weight: 700;
letter-spacing: -0.01em;
font-size: 24px;
margin: 24px 0 20px 4px;
}

.portfolio-card {
background: var(--bg-surface-solid);
padding: 0;
margin: 20px auto;
border-radius: 18px;
box-shadow: 0 1px 3px rgba(0,0,0,0.06);
width: 100%;
cursor: pointer;
border: 1px solid var(--border);
transition: all 0.25s ease;
overflow: hidden;
}
.portfolio-card:hover {
border-color: #2d3a5c;
box-shadow: 0 10px 30px rgba(0,0,0,0.06);
transform: translateY(-2px);
}
.portfolio-card-header {
display: flex;
align-items: center;
justify-content: space-between;
padding: 20px 24px 16px;
border-bottom: 1px solid var(--border-soft);
}
.portfolio-card-header .portfolio-title-group {
display: flex;
align-items: center;
gap: 12px;
}
.portfolio-card-header h3 {
color: var(--text-primary);
margin: 0;
font-size: 18px;
font-weight: 700;
letter-spacing: -0.01em;
display: flex;
align-items: center;
gap: 10px;
}
.portfolio-card-header .ptype-badge {
font-size: 10px;
font-weight: 700;
text-transform: uppercase;
letter-spacing: 0.05em;
padding: 4px 10px;
border-radius: 999px;
background: var(--accent-soft);
color: var(--accent);
border: 1px solid rgba(37,99,235,0.2);
}
.portfolio-card-header .portfolio-actions {
display: flex;
align-items: center;
gap: 8px;
}
.portfolio-card-header .portfolio-actions a {
font-size: 12px;
font-weight: 600;
color: var(--text-muted);
text-decoration: none;
padding: 6px 12px;
border-radius: 8px;
transition: all 0.15s ease;
border: 1px solid transparent;
}
.portfolio-card-header .portfolio-actions a:hover {
color: var(--accent);
background: var(--accent-soft);
border-color: rgba(37,99,235,0.2);
}
.portfolio-card .metrics {
display: grid;
grid-template-columns: repeat(5,1fr);
gap: 10px;
padding: 16px 24px;
}
.metrics div {
background: var(--bg-elevated);
padding: 14px 16px;
border-radius: 12px;
font-size: 13px;
line-height: 1.5;
border: 1px solid var(--border-soft);
color: var(--text-primary);
transition: border-color 0.15s ease;
}
.metrics div:hover { border-color: #2d3a5c; }
.metrics div strong {
display: block;
font-size: 10px;
font-weight: 600;
text-transform: uppercase;
letter-spacing: 0.05em;
color: var(--text-muted);
margin-bottom: 6px;
}
.metrics div .metric-value {
font-family: var(--font-ui);
font-size: 15px;
font-weight: 700;
}
.metrics div .metric-value.positive {
color: var(--green);

}
.metrics div .metric-value.negative {
color: var(--red);

}
.metrics div .metric-value.neutral { color: var(--text-secondary); }
@media (max-width: 900px) {
.portfolio-card .metrics { grid-template-columns: repeat(3,1fr); }
}
@media (max-width: 600px) {
.portfolio-card .metrics { grid-template-columns: repeat(2,1fr); }
}
.portfolio-card-footer {
padding: 0 24px 20px;
}
.add-stock-btn {
padding: 10px 20px;
background: transparent;
color: var(--accent);
border: 1px solid rgba(37,99,235,0.3);
border-radius: 10px;
cursor: pointer;
font-weight: 600;
font-size: 13px;
transition: all 0.2s ease;
display: inline-flex;
align-items: center;
gap: 6px;
}
.add-stock-btn:hover {
background: var(--accent-soft);
border-color: var(--accent);
transform: translateY(-1px);
}
.stocks-list {
display: none;
border-top: 1px solid var(--border);
padding: 20px 24px;
background: var(--bg-row-alt);
}

.stocks-grid {
display: grid;
grid-template-columns: repeat(auto-fill,minmax(340px,1fr));
gap: 16px;
}
.stock-card {
background: var(--bg-elevated);
border: 1px solid var(--border);
border-radius: 16px;
padding: 20px 22px;
transition: all 0.25s ease;
position: relative;
overflow: hidden;
}
.stock-card::before {
content: '';
position: absolute;
top: 0;
left: 0;
right: 0;
height: 3px;
background: var(--accent);
opacity: 0;
transition: opacity 0.25s ease;
}
.stock-card:hover::before { opacity: 1; }
.stock-card:hover {
border-color: #2d3a5c;
box-shadow: 0 8px 24px rgba(0,0,0,0.06);
transform: translateY(-3px);
}
.stock-card.sold-out-row { opacity: 0.45; }
.stock-card.sold-out-row::before {
background: var(--text-muted);
opacity: 0.3;
}
.stock-card-header {
display: flex;
align-items: center;
justify-content: space-between;
margin-bottom: 16px;
padding-bottom: 14px;
border-bottom: 1px solid var(--border-soft);
}
.stock-symbol {
font-family: var(--font-ui);
font-weight: 800;
font-size: 20px;
letter-spacing: 0.02em;
color: var(--text-primary);
}
.stock-card-header .type-badge {
display: inline-block;
padding: 4px 12px;
border-radius: 999px;
font-size: 10px;
font-weight: 700;
text-transform: uppercase;
letter-spacing: 0.05em;
font-family: var(--font-ui);
}
.type-badge.ipo { background: var(--green-soft); color: var(--green); border: 1px solid rgba(34,197,94,0.2); }
.type-badge.secondary { background: var(--accent-soft); color: var(--accent); border: 1px solid rgba(37,99,235,0.2); }
.type-badge.bonus { background: var(--amber-soft); color: var(--amber); border: 1px solid rgba(245,158,11,0.2); }
.stock-price-row {
display: flex;
align-items: baseline;
gap: 12px;
margin-bottom: 18px;
}
.stock-ltp {
font-family: var(--font-ui);
font-size: 32px;
font-weight: 700;
color: var(--text-primary);
line-height: 1;
}
.stock-ltp.loading {
font-size: 14px;
color: var(--text-muted);
font-weight: 500;
}
.stock-pct-change {
font-family: var(--font-ui);
font-size: 14px;
font-weight: 700;
padding: 4px 10px;
border-radius: 8px;
}
.stock-pct-change.up {
background: var(--green-soft);
color: var(--green);

}
.stock-pct-change.down {
background: var(--red-soft);
color: var(--red);

}
.stock-stats {
display: grid;
grid-template-columns: repeat(2,1fr);
gap: 10px;
margin-bottom: 16px;
}
.stat-item {
background: var(--bg-surface-solid);
border: 1px solid var(--border-soft);
border-radius: 12px;
padding: 12px 14px;
transition: border-color 0.15s ease;
}
.stat-item:hover { border-color: #2d3a5c; }
.stat-item .stat-label {
display: block;
font-size: 10px;
font-weight: 600;
text-transform: uppercase;
letter-spacing: 0.05em;
color: var(--text-muted);
margin-bottom: 5px;
}
.stat-item .stat-value {
font-family: var(--font-ui);
font-size: 15px;
font-weight: 600;
color: var(--text-primary);
}
.stat-item .stat-value.positive { color: var(--green); }
.stat-item .stat-value.negative { color: var(--red); }
.stock-profit-bar {
display: flex;
align-items: center;
justify-content: space-between;
background: var(--bg-surface-solid);
border: 1px solid var(--border-soft);
border-radius: 12px;
padding: 14px 16px;
margin-bottom: 14px;
position: relative;
overflow: hidden;
}
.stock-profit-bar::after {
content: '';
position: absolute;
bottom: 0;
left: 0;
height: 3px;
border-radius: 0 0 0 12px;
transition: width 0.5s ease;
width: 100%;
}
.stock-profit-bar.positive::after {
background: var(--green);

}
.stock-profit-bar.negative::after {
background: var(--red);

}
.profit-bar-left {
display: flex;
flex-direction: column;
gap: 3px;
}
.profit-bar-label {
font-size: 10px;
font-weight: 600;
text-transform: uppercase;
letter-spacing: 0.05em;
color: var(--text-muted);
}
.profit-bar-value {
font-family: var(--font-ui);
font-size: 20px;
font-weight: 700;
}
.profit-bar-value.positive { color: var(--green); }
.profit-bar-value.negative { color: var(--red); }
.profit-bar-right { text-align: right; }
.profit-bar-percent {
font-family: var(--font-ui);
font-size: 15px;
font-weight: 700;
padding: 5px 12px;
border-radius: 10px;
}
.profit-bar-percent.positive {
background: var(--green-soft);
color: var(--green);

}
.profit-bar-percent.negative {
background: var(--red-soft);
color: var(--red);

}
.stock-sold-strip {
display: flex;
gap: 16px;
padding: 10px 14px;
background: var(--bg-row-alt);
border-radius: 10px;
margin-bottom: 16px;
font-size: 12px;
border: 1px solid var(--border-soft);
}
.sold-strip-item {
display: flex;
flex-direction: column;
gap: 3px;
}
.sold-strip-item .sold-label {
font-size: 9px;
font-weight: 600;
text-transform: uppercase;
letter-spacing: 0.05em;
color: var(--text-muted);
}
.sold-strip-item .sold-value {
font-family: var(--font-ui);
font-size: 13px;
font-weight: 600;
color: var(--text-secondary);
}
.stock-actions {
display: flex;
gap: 8px;
flex-wrap: wrap;
}
.stock-actions a {
flex: 1;
min-width: 70px;
text-align: center;
padding: 9px 14px;
border-radius: 10px;
font-size: 12px;
font-weight: 600;
text-decoration: none;
transition: all 0.2s ease;
font-family: var(--font-ui);
border: 1px solid transparent;
}
.stock-actions .btn-sell {
background: var(--red-soft);
color: var(--red);
border-color: rgba(239,68,68,0.3);
}
.stock-actions .btn-sell:hover {
background: var(--red);
color: #fff;
box-shadow: 0 2px 8px rgba(239,68,68,0.15);
transform: translateY(-1px);
}
.stock-actions .btn-edit {
background: var(--bg-surface-solid);
color: var(--text-secondary);
border-color: var(--border);
}
.stock-actions .btn-edit:hover {
background: var(--bg-row-hover);
color: var(--text-primary);
border-color: var(--accent);
box-shadow: 0 0 8px rgba(37,99,235,0.10);
}
.stock-actions .btn-delete {
background: transparent;
color: var(--text-muted);
border-color: var(--border);
}
.stock-actions .btn-delete:hover {
background: var(--red-soft);
color: var(--red);
border-color: var(--red);
}
.stocks-empty {
color: var(--text-muted);
padding: 48px 24px;
text-align: center;
font-size: 14px;
background: var(--bg-elevated);
border-radius: 16px;
border: 2px dashed #94a3b8;
}
.stock-card [title] { cursor: help; }
@keyframes fadeIn {
from { opacity: 0; transform: translateY(-20px); }
to { opacity: 1; transform: translateY(0); }
}
.foot { margin-top: 80px; }

/* ===== MARKET DEPTH BUTTON ===== */
.btn-depth {
  background: var(--accent-soft);
  color: var(--accent);
  border: 1px solid rgba(37,99,235,0.3);
}
.btn-depth:hover {
  background: var(--accent);
  color: #fff;
  box-shadow: 0 2px 8px rgba(37,99,235,0.15);
  transform: translateY(-1px);
}

/* ===== MARKET DEPTH MODAL ===== */
#depthModal .modal-content {
  width: 520px;
  max-width: 90%;
  max-height: 90vh;
  overflow-y: auto;
}
.depth-table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 20px;
  font-size: 13px;
}
.depth-table th {
  padding: 12px 8px;
  font-weight: 700;
  text-transform: uppercase;
  font-size: 10px;
  letter-spacing: 0.08em;
  text-align: center;
}
.depth-table th.buy-header {
  background: var(--green-soft);
  color: var(--green);
}
.depth-table th.sell-header {
  background: var(--red-soft);
  color: var(--red);
}
.depth-table td {
  padding: 10px 8px;
  text-align: center;
  border-bottom: 1px solid var(--border-soft);
  font-weight: 600;
}
.depth-table tr:hover td { background: var(--bg-row-hover); }
.depth-table .buy-cell { color: var(--text-primary); }
.depth-table .sell-cell { color: var(--text-primary); }
.depth-table .total-row {
  background: var(--bg-surface-solid) !important;
  font-weight: 800;
  font-size: 14px;
  border-top: 2px solid var(--border);
}
.depth-table .total-row td { padding: 14px 8px; }
.depth-table .total-buy { color: var(--text-primary); }
.depth-table .total-sell { color: var(--text-primary); }
.depth-loading {
  text-align: center;
  padding: 30px;
  color: var(--text-muted);
  font-size: 14px;
}
.depth-closed {
  text-align: center;
  padding: 30px;
  color: var(--text-muted);
  font-size: 14px;
}
.depth-actions {
  display: flex;
  gap: 12px;
  margin-top: 20px;
  padding-top: 20px;
  border-top: 1px solid var(--border);
}
.depth-actions a,
.depth-actions button {
  flex: 1;
  padding: 12px 20px;
  border-radius: 10px;
  font-size: 14px;
  font-weight: 700;
  text-decoration: none;
  text-align: center;
  transition: all 0.2s ease;
  cursor: pointer;
  border: none;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  font-family: var(--font-ui);
}
.btn-depth-buy {
  background: var(--green-soft);
  color: var(--green);
  border: 1px solid rgba(34,197,94,0.3);
}
.btn-depth-buy:hover {
  background: var(--green);
  color: #fff;
  box-shadow: 0 2px 8px rgba(34,197,94,0.2);
  transform: translateY(-1px);
}
.btn-depth-sell {
  background: var(--red-soft);
  color: var(--red);
  border: 1px solid rgba(239,68,68,0.3);
}
.btn-depth-sell:hover {
  background: var(--red);
  color: #fff;
  box-shadow: 0 2px 8px rgba(239,68,68,0.2);
  transform: translateY(-1px);
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
<div id="market-status">
<span class="dot closed"></span> Checking status...
</div>
<button class="login-btn" onclick="window.location.href='logout.php'">Logout</button>
</div>
</header>
<main>
<button id="addPortfolioBtn">+ Add Portfolio</button>
<div id="portfolioModal" class="modal" style="display:none;">
<div class="modal-content">
<span class="close" id="closePortfolio">&times;</span>
<h2>Create Portfolio</h2>
<form action="add_portfolio.php" method="POST">
<label for="portfolio_name">Portfolio Name:</label>
<input type="text" id="portfolio_name" name="portfolio_name" required>
<label for="portfolio_type">Portfolio Type:</label>
<select id="portfolio_type" name="portfolio_type" required>
<option value="personal">Personal</option>
<option value="institutional">Institutional</option>
</select>
<button type="submit">Save Portfolio</button>
</form>
</div>
</div>
<h2 class="page-title" style="margin-left:4px"><?php
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$username = $user['username'];
echo htmlspecialchars($username); ?>'s Portfolios</h2>

<?php foreach ($portfolios as $p): ?>
<?php
$stmt2 = $pdo->prepare("SELECT * FROM stocks WHERE portfolio_id = ? ORDER BY symbol ASC");
$stmt2->execute([$p['id']]);
$stocks = $stmt2->fetchAll();
$total_investment = 0;
$total_units = 0;
foreach ($stocks as $s) {
$investment = $s['quantity'] * $s['buy_price'];
$total_investment += $investment;
$total_units += $s['quantity'];
}
$total_sold_value = $p['total_sold_value'] ?? 0;
$total_realized_profit = $p['total_realized_profit'] ?? 0;
$realizedClass = '';
$realizedLabel = '';
if ($total_realized_profit > 0) {
$realizedClass = 'positive';
$realizedLabel = 'Realized Profit';
} elseif ($total_realized_profit < 0) {
$realizedClass = 'negative';
$realizedLabel = 'Realized Loss';
} else {
$realizedClass = 'neutral';
$realizedLabel = 'Realized P/L';
}
?>
<div class="portfolio-card" data-id="<?php echo $p['id']; ?>" data-type="<?php echo strtolower($p['portfolio_type']); ?>" onclick="toggleStocks(<?php echo $p['id']; ?>)">
<div class="portfolio-card-header">
<div class="portfolio-title-group">
<h3>
<?php echo htmlspecialchars($p['portfolio_name']); ?>
<span class="ptype-badge"><?php echo htmlspecialchars($p['portfolio_type']); ?></span>
</h3>
</div>
<div class="portfolio-actions">
<a href="edit_portfolio.php?id=<?php echo $p['id']; ?>" onclick="event.stopPropagation();">Edit</a>
<a href="delete_portfolio.php?id=<?php echo $p['id']; ?>" onclick="event.stopPropagation(); return confirm('Delete this portfolio?');">Delete</a>
</div>
</div>
<div class="metrics">
<div><strong>Total Investment</strong><span class="metric-value neutral">Rs. <?php echo number_format($total_investment,2); ?></span></div>
<div><strong>Profit / Loss</strong><span class="metric-value neutral">Rs. 0</span></div>
<div><strong>Current Units</strong><span class="metric-value neutral"><?php echo $total_units; ?></span></div>
<div><strong>Current Value</strong><span class="metric-value neutral">Rs. 0</span></div>
<div><strong>Receivable Amount</strong><span class="metric-value neutral">Rs. 0</span></div>
<div><strong>Total Profit/Loss %</strong><span class="metric-value neutral">0%</span></div>
<div><strong>Today's Gain/Loss</strong><span class="metric-value neutral">Rs. 0</span></div>
<div><strong>Today's Gain/Loss %</strong><span class="metric-value neutral">0%</span></div>
<div><strong>Sold Value</strong><span class="metric-value neutral">Rs. <?php echo number_format($total_sold_value,2); ?></span></div>
<div><strong><?php echo $realizedLabel; ?></strong><span class="metric-value <?php echo $realizedClass; ?>">Rs. <?php echo number_format(abs($total_realized_profit),2); ?></span></div>
</div>
<div class="portfolio-card-footer">
<button class="add-stock-btn" onclick="event.stopPropagation();">+ Add Stocks</button>
</div>
<div id="stocks-<?php echo $p['id']; ?>" class="stocks-list" style="display:none;">
<?php if ($stocks): ?>
<div class="stocks-grid">

<?php foreach ($stocks as $s):
$investment = $s['quantity'] * $s['buy_price'];
$currentValue = 0;
$profitLoss = 0;
$prevClose = $s['previousClose'] ?? 0;
$percentageChange = $s['percentageChange'] ?? 0;
$closePrice = $s['closePrice'] ?? 0;
$todaysGain = $s['quantity'] * ($closePrice - $prevClose);
$purchaseTypeLower = strtolower($s['purchase_type']);
$soldUnits = $s['sold_units'] ?? 0;
$soldValue = $s['sold_value'] ?? 0;
$isSoldOut = ((int)$s['quantity'] === 0);
?>
<div class="stock-card <?php echo $isSoldOut ? 'sold-out-row' : ''; ?>"
data-quantity="<?php echo $s['quantity']; ?>"
data-buyprice="<?php echo $s['buy_price']; ?>"
data-buydate="<?php echo $s['buy_date']; ?>"
data-purchasetype="<?php echo $purchaseTypeLower; ?>">
<div class="stock-card-header">
<span class="stock-symbol"><?php echo strtoupper($s['symbol']); ?></span>
<span class="type-badge <?php echo $purchaseTypeLower; ?>"><?php echo $s['purchase_type']; ?></span>
</div>
<div class="stock-price-row">
<span class="stock-ltp <?php echo $isSoldOut ? '' : 'loading'; ?>" data-symbol="<?php echo strtoupper($s['symbol']); ?>">
<?php echo $isSoldOut ? '-' : 'Loading...'; ?>
</span>
<span class="stock-pct-change <?php echo $percentageChange >= 0 ? 'up' : 'down'; ?>">
<?php echo number_format($percentageChange, 2); ?>%
</span>
</div>
<div class="stock-stats">
<div class="stat-item"><span class="stat-label">Quantity</span><span class="stat-value"><?php echo $s['quantity']; ?></span></div>
<div class="stat-item"><span class="stat-label">Avg Buy Price</span><span class="stat-value">Rs. <?php echo number_format($s['buy_price'], 2); ?></span></div>
<div class="stat-item"><span class="stat-label">Investment</span><span class="stat-value investment"><?php echo $isSoldOut ? '0.00' : number_format($investment, 2); ?></span></div>
<div class="stat-item"><span class="stat-label">Current Value</span><span class="stat-value current-value"><?php echo $isSoldOut ? '0.00' : '0.00'; ?></span></div>
<div class="stat-item"><span class="stat-label">Receivable</span><span class="stat-value receivable-amount"><?php echo $isSoldOut ? '0.00' : '0.00'; ?></span></div>
<div class="stat-item"><span class="stat-label">Today's Gain</span><span class="stat-value todays-gain <?php echo $todaysGain >= 0 ? 'positive' : 'negative'; ?>"><?php echo $isSoldOut ? '0.00' : number_format($todaysGain, 2); ?></span></div>
</div>
<div class="stock-profit-bar <?php echo $profitLoss >= 0 ? 'positive' : 'negative'; ?>">
<div class="profit-bar-left">
<span class="profit-bar-label">Net Profit / Loss</span>
<span class="profit-bar-value profit-loss <?php echo $profitLoss >= 0 ? 'positive' : 'negative'; ?>"><?php echo $isSoldOut ? '0.00' : '0.00'; ?></span>
</div>
<div class="profit-bar-right">
<span class="profit-bar-percent profit-percent <?php echo $profitLoss >= 0 ? 'positive' : 'negative'; ?>">0.00%</span>
</div>
</div>
<div class="stock-sold-strip">
<div class="sold-strip-item"><span class="sold-label">Sold Units</span><span class="sold-value sold-units"><?php echo $soldUnits; ?></span></div>
<div class="sold-strip-item"><span class="sold-label">Sold Value</span><span class="sold-value sold-value">Rs. <?php echo number_format($soldValue, 2); ?></span></div>
<div class="sold-strip-item"><span class="sold-label">Purchase Date</span><span class="sold-value"><?php echo $s['buy_date']; ?></span></div>
</div>
<div class="stock-actions">
<button type="button" class="btn-depth" onclick="event.stopPropagation(); openDepthModal('<?php echo strtoupper($s['symbol']); ?>');">📊 Depth</button>
<?php if (!$isSoldOut): ?>
<a href="#" class="btn-sell" onclick="openSellModal(<?php echo $s['id']; ?>, '<?php echo strtoupper($s['symbol']); ?>', <?php echo $s['quantity']; ?>); return false;">Sell</a>
<?php endif; ?>
<a href="edit_stock.php?id=<?php echo $s['id']; ?>" class="btn-edit" onclick="event.stopPropagation();">Edit</a>
<a href="delete_stock.php?id=<?php echo $s['id']; ?>" class="btn-delete" onclick="event.stopPropagation(); return confirm('Delete this stock?');">Delete</a>
</div>
</div>
<?php endforeach; ?>
</div>
<?php else: ?>
<div class="stocks-empty">No stocks added yet. Click "+ Add Stocks" to get started.</div>
<?php endif; ?>
</div>
</div>
<?php endforeach; ?>
</main>
<div  id = "addStockModal" class="modal" style="display:none;></div>
<div id="addStockModal" class="modal" style="display:none;">
<div class="modal-content">
<span class="close" id="closeStock">&times;</span>
<h2>Add Stock</h2>
<form action="add_stock.php" method="POST">
<input type="hidden" name="portfolio_id" id="portfolio_id">
<label for="symbol">Symbol:</label>
<input type="text" id="symbol" name="symbol" list="symbolList" required autocomplete="off" style="text-transform:uppercase;">
<datalist id="symbolList"></datalist>
<small class="hint">Start typing a symbol or company name and pick it from the list — it will fill in automatically. If this symbol already exists in the portfolio, the quantity will be added to it instead of creating a new entry.</small>
<label for="purchase_type">Transaction Type:</label>
<select id="purchase_type" name="purchase_type" required>
<option value="Secondary">Secondary Buy</option>
<option value="IPO">IPO</option>
<option value="Bonus">Bonus Share</option>
</select>
<small class="hint" id="typeHint">Secondary market purchase — broker commission, SEBON fee &amp; DP charge apply.</small>
<label for="quantity">Quantity:</label>
<input type="number" id="quantity" name="quantity" min="1" required>
<div id="buyPriceField">
<label for="buy_price">Buy Price (per share):</label>
<input type="number" step="0.01" id="buy_price" name="buy_price" required>
</div>
<label for="buy_date">Buy Date:</label>
<input type="date" id="buy_date" name="buy_date" max="<?php echo date('Y-m-d'); ?>">
<button type="submit">Save Stock</button>
</form>
</div>
</div>
<!-- ===== MARKET DEPTH MODAL ===== -->
<div id="depthModal" class="modal" style="display:none;">
<div class="modal-content">
<span class="close" onclick="closeDepthModal()">&times;</span>
<h2>📊 Market Depth — <span id="depthSymbol" style="color:var(--accent)">SYMBOL</span></h2>
<div id="depthLoading" class="depth-loading">Loading market depth...</div>
<div id="depthTableWrap" style="display:none;">
<table class="depth-table">
<thead>
<tr>
<th colspan="3" class="buy-header">⬆️ TOP 5 BUY</th>
<th colspan="3" class="sell-header">⬇️ TOP 5 SELL</th>
</tr>
<tr>
<th>Order</th>
<th>QTY</th>
<th>Price</th>
<th>Price</th>
<th>QTY</th>
<th>Order</th>
</tr>
</thead>
<tbody id="depthTableBody"></tbody>
<tfoot>
<tr class="total-row">
<td colspan="3" class="total-buy">📥 Total Buy Qty: <span id="totalBuyQty">0</span></td>
<td colspan="3" class="total-sell">📤 Total Sell Qty: <span id="totalSellQty">0</span></td>
</tr>
</tfoot>
</table>
<div class="depth-actions">
<a href="#" class="btn-depth-buy" id="depthBuyLink" onclick="handleDepthBuy(); return false;">🛒 Buy <span id="buySymbol">SYMBOL</span></a>
<a href="#" class="btn-depth-sell" id="depthSellLink" onclick="handleDepthSell(); return false;">💰 Sell <span id="sellSymbol">SYMBOL</span></a>
</div>
</div>
<div id="depthClosed" class="depth-closed" style="display:none;">
<div style="font-size:2rem;margin-bottom:10px;">📉</div>
<div>Market is currently closed.</div>
<div style="font-size:12px;color:var(--text-muted);margin-top:8px;">No market depth available at this time.</div>
</div>
</div>
</div>

<div id="sellStockModal" class="modal" style="display:none;">
<div class="modal-content">
<span class="close" id="closeSell">&times;</span>
<h2>Sell Stock</h2>
<form action="sell_stock.php" method="POST">
<input type="hidden" name="stock_id" id="sell_stock_id">
<label for="sell_symbol">Symbol:</label>
<input type="text" id="sell_symbol" disabled>
<label for="sell_available">Available Units:</label>
<input type="text" id="sell_available" disabled>
<label for="sell_quantity">Quantity to Sell:</label>
<input type="number" name="sell_quantity" id="sell_quantity" min="1" required>
<label for="sell_price">Sell Price (per share):</label>
<input type="number" step="0.01" name="sell_price" id="sell_price" required>
<label for="sell_date">Sell Date:</label>
<input type="date" name="sell_date" id="sell_date">
<button type="submit">Confirm Sell</button>
</form>
</div>
</div>
<footer class="foot">
<div class="footer-content">
<p>&copy; 2026 MyShareApp. All rights reserved.</p>
<p>Data provided by NEPSE API | For Educational purposes only</p>
<nav class="footer-nav">
<a href="home.html">Home</a> |
<a href="market.html">Market</a> |
<a href="portfolio.html">Portfolio</a> |
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

<script src="1.js">


</script>
</body>
</html>