<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$stock_id   = isset($_POST['stock_id']) ? (int)$_POST['stock_id'] : 0;
$sell_qty   = isset($_POST['sell_quantity']) ? (int)$_POST['sell_quantity'] : 0;
$sell_price = isset($_POST['sell_price']) ? (float)$_POST['sell_price'] : 0;
$sell_date  = !empty($_POST['sell_date']) ? $_POST['sell_date'] : date('Y-m-d');

if ($stock_id <= 0 || $sell_qty <= 0 || $sell_price <= 0) {
    die("Invalid input.");
}

// Fetch the stock and verify it belongs to the logged-in user
$stmt = $pdo->prepare("
    SELECT s.*, p.portfolio_type, p.user_id
    FROM stocks s
    JOIN portfolios p ON p.id = s.portfolio_id
    WHERE s.id = ?
");
$stmt->execute([$stock_id]);
$stock = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$stock || (int)$stock['user_id'] !== (int)$_SESSION['user_id']) {
    die("Invalid stock.");
}

if ($sell_qty > (int)$stock['quantity']) {
    die("You can't sell more units than you currently hold.");
}

// ---------------------------------------------------------------
// Charge calculations — mirrors the JS logic in portfolio.php
// so the numbers shown on screen match what actually gets saved.
// ---------------------------------------------------------------
function brokerCommission(float $amount): float {
    if ($amount <= 50000)        $rate = 0.0036;
    elseif ($amount <= 500000)   $rate = 0.0033;
    elseif ($amount <= 2000000)  $rate = 0.0031;
    elseif ($amount <= 10000000) $rate = 0.0027;
    else                         $rate = 0.0024;

    $commission = $amount * $rate;
    return $commission < 10 ? 10 : $commission;
}

function sebonFee(float $amount): float {
    return $amount * 0.00015;
}

function dpCharge(): float {
    return 25.0;
}

function capitalGainTaxRate(string $buyDate, string $portfolioType): float {
    if (strtolower($portfolioType) === 'institutional') {
        return 0.10;
    }

    $buyTimestamp = strtotime($buyDate);
    if ($buyTimestamp === false) {
        return 0.10;
    }

    $holdingDays = (strtotime(date('Y-m-d')) - $buyTimestamp) / 86400;
    return $holdingDays >= 365 ? 0.075 : 0.10;
}

$investment_cost = $sell_qty * (float)$stock['buy_price'];
$sale_value      = $sell_qty * $sell_price;

$broker = brokerCommission($sale_value);
$sebon  = sebonFee($sale_value);
$dp     = dpCharge();
$sellChargesTotal = $broker + $sebon + $dp;

$net_receivable = $sale_value - $sellChargesTotal;
$gross_profit   = $net_receivable - $investment_cost;

$taxRate = capitalGainTaxRate($stock['buy_date'], $stock['portfolio_type']);
$tax     = $gross_profit > 0 ? $gross_profit * $taxRate : 0;
$net_profit = $gross_profit - $tax;

try {
    $pdo->beginTransaction();

    // 1. Log the sale with its full profit breakdown
    $stmt = $pdo->prepare("
        INSERT INTO sold_stocks
        (portfolio_id, stock_id, symbol, quantity_sold, buy_price, sell_price,
         investment_cost, sale_value, broker_charge, sebon_charge, dp_charge,
         capital_gain_tax, net_profit, sell_date)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)
    ");
    $stmt->execute([
        $stock['portfolio_id'],
        $stock_id,
        $stock['symbol'],
        $sell_qty,
        $stock['buy_price'],
        $sell_price,
        $investment_cost,
        $sale_value,
        $broker,
        $sebon,
        $dp,
        $tax,
        $net_profit,
        $sell_date
    ]);

    // 2. Reduce the live holding, accumulate sold units/value on the stock row
    $stmt = $pdo->prepare("
        UPDATE stocks
        SET quantity   = quantity - ?,
            sold_units = sold_units + ?,
            sold_value = sold_value + ?
        WHERE id = ?
    ");
    $stmt->execute([$sell_qty, $sell_qty, $sale_value, $stock_id]);

    // 3. Roll the sale into the portfolio's cumulative totals
    $stmt = $pdo->prepare("
        UPDATE portfolios
        SET total_sold_value      = total_sold_value + ?,
            total_realized_profit = total_realized_profit + ?
        WHERE id = ?
    ");
    $stmt->execute([$sale_value, $net_profit, $stock['portfolio_id']]);

    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    die("Error selling stock: " . $e->getMessage());
}

header("Location: portfolio.php");
exit();
