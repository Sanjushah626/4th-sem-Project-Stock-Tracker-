<?php


require_once 'db.php';

if (!isset($pdo) || !($pdo instanceof PDO)) {
 

    die('MyShareApp Admin: Could not find an existing $pdo PDO connection. ' .
        'Please open admin/admin-db.php and point it to your existing database connection file/variable.');
}


$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
