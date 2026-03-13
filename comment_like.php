<?php
session_start();
require 'config/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'not_authorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['comment_id'])) {
    echo json_encode(['success' => false, 'error' => 'invalid_request']);
    exit;
}

$comment_id = (int)$_POST['comment_id'];
$user_id = $_SESSION['user_id'];


$stmt = $pdo->prepare("SELECT id FROM comments WHERE id = ?");
$stmt->execute([$comment_id]);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'error' => 'comment_not_found']);
    exit;
}


$stmt = $pdo->prepare("SELECT id FROM comment_likes WHERE user_id = ? AND comment_id = ?");
$stmt->execute([$user_id, $comment_id]);
$like = $stmt->fetch();

if ($like) {

    $stmt = $pdo->prepare("DELETE FROM comment_likes WHERE id = ?");
    $stmt->execute([$like['id']]);
    $action = 'unliked';
} else {

    $stmt = $pdo->prepare("INSERT INTO comment_likes (user_id, comment_id) VALUES (?, ?)");
    $stmt->execute([$user_id, $comment_id]);
    $action = 'liked';
}


$stmt = $pdo->prepare("SELECT COUNT(*) FROM comment_likes WHERE comment_id = ?");
$stmt->execute([$comment_id]);
$new_count = $stmt->fetchColumn();

echo json_encode([
    'success' => true,
    'action' => $action,
    'new_count' => $new_count
]);