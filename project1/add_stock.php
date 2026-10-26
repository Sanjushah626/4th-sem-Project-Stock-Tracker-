<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: portfolio.php");
    exit();
}

$portfolio_id   = (int) ($_POST['portfolio_id'] ?? 0);
$symbol         = strtoupper(trim($_POST['symbol'] ?? ''));
$purchase_type  = trim($_POST['purchase_type'] ?? 'Secondary'); // IPO | Secondary | Bonus
$quantity       = (float) ($_POST['quantity'] ?? 0);
$buy_date       = $_POST['buy_date'] ?? date('Y-m-d');


$buy_price = ($purchase_type === 'Bonus') ? 0 : (float) ($_POST['buy_price'] ?? 0);

if ($portfolio_id <= 0 || $symbol === '' || $quantity <= 0) {
    header("Location: portfolio.php?error=invalid_input");
    exit();
}


$stmt = $pdo->prepare("SELECT id FROM portfolios WHERE id = ? AND user_id = ?");
$stmt->execute([$portfolio_id, $user_id]);
if (!$stmt->fetch()) {
    header("Location: portfolio.php?error=not_your_portfolio");
    exit();
}


$pdo->beginTransaction();

try {
    $stmt = $pdo->prepare(
        "SELECT * FROM stocks WHERE portfolio_id = ? AND symbol = ? FOR UPDATE"
    );
    $stmt->execute([$portfolio_id, $symbol]);
    $existing = $stmt->fetch();

    if ($existing) {
        $existingQty   = (float) $existing['quantity'];
        $existingPrice = (float) $existing['buy_price'];

        $newQty = $existingQty + $quantity;

        $newAvgPrice = $newQty > 0
            ? (($existingQty * $existingPrice) + ($quantity * $buy_price)) / $newQty
            : 0;

       
        $existingTypes = array_map('trim', explode('+', $existing['purchase_type']));
        if (!in_array($purchase_type, $existingTypes, true)) {
            $existingTypes[] = $purchase_type;
        }
        $combinedType = implode('+', $existingTypes);

       
        $earliestDate = $existing['buy_date'];
        if (!empty($buy_date) && (empty($earliestDate) || strtotime($buy_date) < strtotime($earliestDate))) {
            $earliestDate = $buy_date;
        }

        $update = $pdo->prepare(
            "UPDATE stocks
             SET quantity = ?, buy_price = ?, purchase_type = ?, buy_date = ?
             WHERE id = ?"
        );
        $update->execute([$newQty, $newAvgPrice, $combinedType, $earliestDate, $existing['id']]);

    } else {
        $insert = $pdo->prepare(
            "INSERT INTO stocks (portfolio_id, symbol, purchase_type, quantity, buy_price, buy_date)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $insert->execute([$portfolio_id, $symbol, $purchase_type, $quantity, $buy_price, $buy_date]);
    }

    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    header("Location: portfolio.php?error=save_failed");
    exit();
}

header("Location: portfolio.php");
exit();
