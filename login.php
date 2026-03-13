<?php
session_start();
require_once 'config/db.php';

$errorMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $pass = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($pass, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_role'] = $user['role'];

        if ($user['role'] === 'admin') {
            header("Location: admin/posts.php");
        } else {
            header("Location: index.php");
        }
        exit;
    } else {
        $errorMsg = "Неверный email или пароль";
    }
}

include 'includes/header.php';
?>

<div class="auth-card">
    <div class="card-header">Авторизация</div>
    <div class="card-body">
        <?php if ($errorMsg): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($errorMsg) ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="form-group">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Пароль</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Войти</button>
        </form>
        <div class="auth-link">
            Нет аккаунта? <a href="register.php">Зарегистрироваться</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>