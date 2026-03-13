<?php
session_start();
require_once 'config/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'not_authorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['post_id'])) {
    echo json_encode(['success' => false, 'error' => 'invalid_request']);
    exit;
}

$post_id = (int)$_POST['post_id'];
$user_id = $_SESSION['user_id'];


$stmt = $pdo->prepare("SELECT id FROM posts WHERE id = ?");
$stmt->execute([$post_id]);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'error' => 'post_not_found']);
    exit;
}


$stmt = $pdo->prepare("SELECT id FROM likes WHERE user_id = ? AND post_id = ?");
$stmt->execute([$user_id, $post_id]);
$like = $stmt->fetch();

if ($like) {

    $stmt = $pdo->prepare("DELETE FROM likes WHERE id = ?");
    $stmt->execute([$like['id']]);
    $action = 'unliked';
} else {

    $stmt = $pdo->prepare("INSERT INTO likes (user_id, post_id) VALUES (?, ?)");
    $stmt->execute([$user_id, $post_id]);
    $action = 'liked';
}

$stmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE post_id = ?");
$stmt->execute([$post_id]);
$new_count = $stmt->fetchColumn();

echo json_encode([
    'success' => true,
    'action' => $action,
    'new_count' => $new_count
]);