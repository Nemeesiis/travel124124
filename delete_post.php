<?php
require_once 'auth.php';
require '../config/db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: posts.php');
    exit;
}
$id = (int)$_GET['id'];

$stmt = $pdo->prepare("SELECT image FROM posts WHERE id = ?");
$stmt->execute([$id]);
$post = $stmt->fetch();

if ($post && !empty($post['image'])) {

    $file_path = '../../' . $post['image'];
    if (file_exists($file_path)) {
        unlink($file_path);
    }
}

$stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
$stmt->execute([$id]);

header('Location: posts.php');
exit;