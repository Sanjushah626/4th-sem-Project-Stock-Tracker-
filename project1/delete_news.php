<?php

require_once 'header.php';

if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    header('Location: news.php');
    exit;
}
$newsId = (int) $_GET['id'];


$stmt = $pdo->prepare('SELECT image FROM news WHERE id = :id');
$stmt->execute(['id' => $newsId]);
$news = $stmt->fetch(PDO::FETCH_ASSOC);

if ($news) {
   
    $deleteStmt = $pdo->prepare('DELETE FROM news WHERE id = :id');
    $deleteStmt->execute(['id' => $newsId]);

    
    if (!empty($news['image'])) {
        $imagePath = __DIR__ . '/uploads/news/' . $news['image'];
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }
}

header('Location: news.php?status=deleted');
exit;
