<?php
require_once 'auth.php';
require '../config/db.php';

$post_filter = '';
if (isset($_GET['post_id']) && is_numeric($_GET['post_id'])) {
    $post_id = (int)$_GET['post_id'];
    $post_filter = " AND posts.id = $post_id";
}

$stmt = $pdo->query("
    SELECT comments.*, users.username, posts.title as post_title
    FROM comments
    JOIN users ON comments.user_id = users.id
    JOIN posts ON comments.post_id = posts.id
    WHERE 1=1 $post_filter
    ORDER BY comments.created_at DESC
");
$comments = $stmt->fetchAll();

include '../includes/header.php';
?>

<style>
    main {
        display: block !important;
        align-items: normal !important;
        justify-content: normal !important;
    }
</style>
<div class="admin-comments-container">
    <h1 class="admin-title">Управление комментариями</h1>

    <?php if (empty($comments)): ?>
        <p class="text-muted">Комментариев пока нет.</p>
    <?php else: ?>
        <table class="admin-table admin-table-comments">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Пост</th>
                    <th>Автор</th>
                    <th>Комментарий</th>
                    <th>Дата</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($comments as $comment): ?>
                    <tr>
                        <td><?= $comment['id'] ?></td>
                        <td><a href="/post.php?id=<?= $comment['post_id'] ?>" target="_blank"><?= htmlspecialchars($comment['post_title']) ?></a></td>
                        <td><?= htmlspecialchars($comment['username']) ?></td>
                        <td><?= htmlspecialchars(mb_substr($comment['comment'], 0, 50)) ?>...</td>
                        <td><?= date('d.m.Y H:i', strtotime($comment['created_at'])) ?></td>
                        <td><a href="delete_comment.php?id=<?= $comment['id'] ?>" onclick="return confirm('Удалить комментарий?')" class="delete">Удалить</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <div class="admin-actions">
        <a href="posts.php" class="btn-admin">Назад к постам</a>
    </div>
</div>

<?php include '../includes/footer.php'; ?>