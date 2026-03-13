<?php
require_once 'auth.php';
require '../config/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $image = '';

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/';
        $tmp_name = $_FILES['image']['tmp_name'];
        $name = basename($_FILES['image']['name']);
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array($ext, $allowed)) {
            $new_name = uniqid() . '.' . $ext;
            $destination = $upload_dir . $new_name;
            if (move_uploaded_file($tmp_name, $destination)) {
                $image = 'uploads/' . $new_name;
            } else {
                $error = 'Не удалось сохранить файл. Проверьте права на папку uploads.';
            }
        } else {
            $error = 'Недопустимый формат файла. Разрешены: jpg, jpeg, png, gif, webp.';
        }
    }

    if (empty($title) || empty($content)) {
        $error = 'Заполните заголовок и текст.';
    } elseif (empty($error)) {
        $stmt = $pdo->prepare("INSERT INTO posts (title, content, image, user_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([$title, $content, $image, $_SESSION['user_id']]);
        $success = 'Пост успешно добавлен.';
    }
}

include '../includes/header.php';
?>

<style>
    main {
        display: block !important;
        align-items: normal !important;
    }
</style>

<div class="admin-form-container">
    <h1 class="admin-title">Добавить новый пост</h1>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success">
            <?= $success ?> <a href="posts.php" class="alert-link">Вернуться к списку</a>
        </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="admin-form">
        <div class="form-group">
            <label class="form-label">Заголовок</label>
            <input type="text" name="title" class="form-control" required>
        </div>
        <div class="form-group">
            <label class="form-label">Текст</label>
            <textarea name="content" class="form-control" rows="10" required></textarea>
        </div>
        <div class="form-group">
            <label class="form-label">Картинка (необязательно)</label>
            <input type="file" name="image" class="form-control" accept="image/*">
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Сохранить</button>
            <a href="posts.php" class="btn-cancel">Отмена</a>
        </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>