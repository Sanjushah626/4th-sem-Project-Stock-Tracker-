<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$stock_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($stock_id <= 0) {
    die("Invalid stock.");
}

// Verify the stock belongs to a portfolio owned by this user
$stmt = $pdo->prepare("
    SELECT s.*, p.user_id
    FROM stocks s
    JOIN portfolios p ON p.id = s.portfolio_id
    WHERE s.id = ?
");
$stmt->execute([$stock_id]);
$stock = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$stock || (int)$stock['user_id'] !== (int)$_SESSION['user_id']) {
    die("Invalid stock.");
}

$portfolio_id = $stock['portfolio_id'];

try {
    $pdo->beginTransaction();

    // Sum up everything this stock ever contributed to realized totals
    $stmt = $pdo->prepare("
        SELECT
            COALESCE(SUM(sale_value), 0)  AS total_sale_value,
            COALESCE(SUM(net_profit), 0)  AS total_net_profit
        FROM sold_stocks
        WHERE stock_id = ?
    ");
    $stmt->execute([$stock_id]);
    $totals = $stmt->fetch(PDO::FETCH_ASSOC);

    // Reverse that contribution out of the portfolio's running totals
    if ($totals['total_sale_value'] > 0 || $totals['total_net_profit'] != 0) {
        $stmt = $pdo->prepare("
            UPDATE portfolios
            SET total_sold_value      = total_sold_value - ?,
                total_realized_profit = total_realized_profit - ?
            WHERE id = ?
        ");
        $stmt->execute([
            $totals['total_sale_value'],
            $totals['total_net_profit'],
            $portfolio_id
        ]);
    }

    // Remove this stock's sale history
    // (also happens automatically via ON DELETE CASCADE, but explicit
    // here keeps this script correct even if the FK isn't set up yet)
    $stmt = $pdo->prepare("DELETE FROM sold_stocks WHERE stock_id = ?");
    $stmt->execute([$stock_id]);

    // Finally, remove the stock holding itself
    $stmt = $pdo->prepare("DELETE FROM stocks WHERE id = ?");
    $stmt->execute([$stock_id]);

    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    die("Error deleting stock: " . $e->getMessage());
}

header("Location: portfolio.php");
exit();