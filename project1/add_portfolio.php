<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id        = $_SESSION['user_id'];
    $portfolio_name = $_POST['portfolio_name'];
    $portfolio_type = $_POST['portfolio_type'];

    // Insert new portfolio into database
    $stmt = $pdo->prepare("INSERT INTO portfolios (user_id, portfolio_name, portfolio_type) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $portfolio_name, $portfolio_type]);

    // Redirect back to portfolio dashboard
    header("Location: portfolio.php");
    exit();
}
?>
