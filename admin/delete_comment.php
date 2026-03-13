<?php
require_once 'auth.php';
require '../config/db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: comments.php');
    exit;
}
$id = (int)$_GET['id'];

$stmt = $pdo->prepare("DELETE FROM comments WHERE id = ?");
$stmt->execute([$id]);

header('Location: comments.php');
exit;