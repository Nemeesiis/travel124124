<?php
date_default_timezone_set('Asia/Novosibirsk');
session_start();
require_once 'config/db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('HTTP/1.0 404 Not Found');
    echo 'Пост не найден';
    exit;
}
$post_id = (int)$_GET['id'];


$stmt = $pdo->prepare("SELECT posts.*, users.username FROM posts JOIN users ON posts.user_id = users.id WHERE posts.id = ?");
$stmt->execute([$post_id]);
$post = $stmt->fetch();

if (!$post) {
    header('HTTP/1.0 404 Not Found');
    echo 'Пост не найден';
    exit;
}


$stmt = $pdo->prepare("SELECT comments.*, users.username FROM comments JOIN users ON comments.user_id = users.id WHERE post_id = ? ORDER BY created_at DESC");
$stmt->execute([$post_id]);
$comments = $stmt->fetchAll();


$stmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE post_id = ?");
$stmt->execute([$post_id]);
$likes_count = $stmt->fetchColumn();

$user_liked = false;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT id FROM likes WHERE user_id = ? AND post_id = ?");
    $stmt->execute([$_SESSION['user_id'], $post_id]);
    $user_liked = $stmt->fetch() ? true : false;
}

include 'includes/header.php';
?>

<article class="post-full">
    <h1><?= htmlspecialchars($post['title']) ?></h1>
    <div class="post-meta">
        <span>Автор: <?= htmlspecialchars($post['username']) ?></span>
        <span>Дата: <?= date('d.m.Y H:i', strtotime($post['created_at'])) ?></span>
    </div>

    <div class="post-content">
        <?= $post['content'] ?>
    </div>

    <div class="post-likes">
        <button class="like-btn <?= $user_liked ? 'liked' : '' ?>" id="likeBtn" data-post-id="<?= $post_id ?>">
            <span class="like-icon">❤️</span>
            <span class="likes-count" id="likesCount"><?= $likes_count ?></span>
        </button>
    </div>
</article>

<div style="margin-bottom: 20px;">
    <a href="index.php" class="btn-read">← Назад к списку постов</a>
</div>

<section class="comments-section">
    <h3>Комментарии (<span id="comment-count"><?= count($comments) ?></span>)</h3>

    <div class="comments-list" id="comments-list">
        <?php foreach ($comments as $comment): 

            $stmt_likes = $pdo->prepare("SELECT COUNT(*) FROM comment_likes WHERE comment_id = ?");
            $stmt_likes->execute([$comment['id']]);
            $comment_likes_count = $stmt_likes->fetchColumn();

  
            $user_comment_liked = false;
            if (isset($_SESSION['user_id'])) {
                $stmt_user_like = $pdo->prepare("SELECT id FROM comment_likes WHERE user_id = ? AND comment_id = ?");
                $stmt_user_like->execute([$_SESSION['user_id'], $comment['id']]);
                $user_comment_liked = $stmt_user_like->fetch() ? true : false;
            }
        ?>
            <div class="comment" id="comment-<?= $comment['id'] ?>">
                <div class="comment-header">
                    <span class="comment-author"><?= htmlspecialchars($comment['username']) ?></span>
                    <span class="comment-date"><?= date('d.m.Y H:i', strtotime($comment['created_at'])) ?></span>
                </div>
                <div class="comment-text">
                    <?= nl2br(htmlspecialchars($comment['comment'])) ?>
                </div>
                <div class="comment-likes">
                    <button class="comment-like-btn <?= $user_comment_liked ? 'liked' : '' ?>" data-comment-id="<?= $comment['id'] ?>" onclick="toggleCommentLike(this)">
                        <span class="like-icon">❤️</span>
                        <span class="comment-likes-count"><?= $comment_likes_count ?></span>
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if (isset($_SESSION['user_id'])): ?>
        <div class="comment-form">
            <h4>Добавить комментарий</h4>
            <div id="comment-error" class="alert alert-danger" style="display: none;"></div>
            <form id="comment-form">
                <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                <textarea name="comment" rows="4" placeholder="Ваш комментарий..." required></textarea>
                <button type="submit">Отправить</button>
            </form>
        </div>
    <?php else: ?>
        <div class="login-message">
            Чтобы оставить комментарий, <a href="login.php">войдите</a> или <a href="register.php">зарегистрируйтесь</a>.
        </div>
    <?php endif; ?>
</section>

<script>
async function toggleCommentLike(btn) {
    const commentId = btn.dataset.commentId;
    const countSpan = btn.querySelector('.comment-likes-count');
    btn.disabled = true;
    try {
        const formData = new FormData();
        formData.append('comment_id', commentId);
        const response = await fetch('comment_like.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        if (data.success) {
            countSpan.textContent = data.new_count;
            if (data.action === 'liked') {
                btn.classList.add('liked');
            } else {
                btn.classList.remove('liked');
            }
        } else {
            alert('Ошибка: ' + data.error);
        }
    } catch (error) {
        console.error(error);
        alert('Ошибка соединения');
    } finally {
        btn.disabled = false;
    }
}
</script>

<script src="/assets/js/post.js"></script>
<script src="/assets/js/comment-likes.js"></script>

<?php include 'includes/footer.php'; ?>