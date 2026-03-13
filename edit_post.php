<?php
require_once 'auth.php';
require '../config/db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: posts.php');
    exit;
}
$id = (int)$_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
$stmt->execute([$id]);
$post = $stmt->fetch();

if (!$post) {
    header('Location: posts.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $image = $post['image'];

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../../uploads/';
        $tmp_name = $_FILES['image']['tmp_name'];
        $name = basename($_FILES['image']['name']);
        $ext = pathinfo($name, PATHINFO_EXTENSION);
        $new_name = uniqid() . '.' . $ext;
        $destination = $upload_dir . $new_name;

        if (move_uploaded_file($tmp_name, $destination)) {
            if (!empty($image) && file_exists('../../' . $image)) {
                unlink('../../' . $image);
            }
            $image = 'uploads/' . $new_name;
        } else {
            $error = 'Ошибка загрузки изображения.';
        }
    }

    if (isset($_POST['delete_image']) && !empty($post['image'])) {
        if (file_exists('../../' . $post['image'])) {
            unlink('../../' . $post['image']);
        }
        $image = null;
    }

    if (empty($title) || empty($content)) {
        $error = 'Заполните заголовок и текст.';
    } else {
        $stmt = $pdo->prepare("UPDATE posts SET title = ?, content = ?, image = ? WHERE id = ?");
        $stmt->execute([$title, $content, $image, $id]);
        $success = 'Пост успешно обновлён.';
        $post['title'] = $title;
        $post['content'] = $content;
        $post['image'] = $image;
    }
}

include '../includes/header.php';
?>

<div class="admin-header">
    <h1>Редактировать пост</h1>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success"><?= $success ?> <a href="posts.php">Вернуться к списку</a></div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" class="admin-form">
    <div class="form-group">
        <label>Заголовок</label>
        <input type="text" name="title" value="<?= htmlspecialchars($post['title']) ?>" required>
    </div>
    <div class="form-group">
        <label>Текст</label>
        <textarea name="content" rows="10" required><?= htmlspecialchars($post['content']) ?></textarea>
    </div>

    <?php if (!empty($post['image'])): ?>
        <div class="admin-current-image">
            <p>Текущая картинка:</p>
            <img src="/<?= $post['image'] ?>" alt="Превью">
            <label>
                <input type="checkbox" name="delete_image"> Удалить картинку
            </label>
        </div>
    <?php endif; ?>

    <div class="form-group">
        <label>Заменить картинку (оставьте пустым, если не нужно)</label>
        <input type="file" name="image" accept="image/*">
    </div>

    <button type="submit">Сохранить изменения</button>
    <a href="posts.php" class="btn-cancel">Отмена</a>
</form>

<?php include '../includes/footer.php'; ?>