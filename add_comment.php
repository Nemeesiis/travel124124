<?php
session_start();
require_once 'config/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'not_authorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['post_id']) || !isset($_POST['comment'])) {
    echo json_encode(['success' => false, 'error' => 'invalid_request']);
    exit;
}

$post_id = (int)$_POST['post_id'];
$comment = trim($_POST['comment']);

if (empty($comment)) {
    echo json_encode(['success' => false, 'error' => 'empty_comment']);
    exit;
}

$stmt = $pdo->prepare("SELECT id FROM posts WHERE id = ?");
$stmt->execute([$post_id]);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'error' => 'post_not_found']);
    exit;
}

$stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, comment) VALUES (?, ?, ?)");
$stmt->execute([$post_id, $_SESSION['user_id'], $comment]);
$comment_id = $pdo->lastInsertId();

$stmt = $pdo->prepare("SELECT comments.*, users.username FROM comments JOIN users ON comments.user_id = users.id WHERE comments.id = ?");
$stmt->execute([$comment_id]);
$newComment = $stmt->fetch();

echo json_encode([
    'success' => true,
    'comment' => [
        'id' => $newComment['id'],
        'username' => htmlspecialchars($newComment['username']),
        'comment' => nl2br(htmlspecialchars($newComment['comment'])),
        'created_at' => date('d.m.Y H:i', strtotime($newComment['created_at']))
    ]
]);