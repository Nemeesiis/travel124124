<?php
require_once 'auth.php';
require '../config/db.php';

$stmt = $pdo->query("
    SELECT posts.*, users.username, COUNT(comments.id) as comments_count
    FROM posts
    JOIN users ON posts.user_id = users.id
    LEFT JOIN comments ON posts.id = comments.post_id
    GROUP BY posts.id
    ORDER BY posts.created_at DESC
");
$posts = $stmt->fetchAll();

include '../includes/header.php';
?>

<style>

    main {
        display: block !important;
        align-items: normal !important;
    }
</style>

<div class="admin-container">
    <h1 class="admin-title">Управление постами</h1>

    <?php if (empty($posts)): ?>
        <p>Пока нет постов.</p>
    <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Заголовок</th>
                    <th>Автор</th>
                    <th>Дата</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($posts as $post): ?>
                    <tr>
                        <td><?= $post['id'] ?></td>
                        <td><?= htmlspecialchars($post['title']) ?></td>
                        <td><?= htmlspecialchars($post['username']) ?></td>
                        <td><?= date('d.m.Y H:i', strtotime($post['created_at'])) ?></td>
                        <td>
                            <a href="edit_post.php?id=<?= $post['id'] ?>">Редактировать</a>
                            <a href="delete_post.php?id=<?= $post['id'] ?>" onclick="return confirm('Удалить пост?')" class="delete">Удалить</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <div class="admin-actions">
        <a href="comments.php" class="btn-admin">Комментарии</a>
        <a href="add_post.php" class="btn-admin">Новый пост</a>
    </div>
</div>

<?php include '../includes/footer.php'; ?>