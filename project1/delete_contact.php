<?php

require_once 'header.php';

if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    header('Location: contacts.php');
    exit;
}
$contactId = (int) $_GET['id'];

$stmt = $pdo->prepare('DELETE FROM contacts WHERE id = :id');
$stmt->execute(['id' => $contactId]);

header('Location: contacts.php?status=deleted');
exit;
