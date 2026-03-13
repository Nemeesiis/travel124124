<?php
session_start();
require_once 'config/db.php';

$errorMsg = '';
$successMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $pass = $_POST['password'];
    $passConfirm = $_POST['password_confirm'];


    if (empty($username) || empty($email) || empty($pass)) {
        $errorMsg = "Заполните все поля!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMsg = "Некорректный формат Email!";
    } elseif (strlen($pass) < 6) {
        $errorMsg = "Пароль должен содержать не менее 6 символов!";
    } elseif ($pass !== $passConfirm) {
        $errorMsg = "Пароли не совпадают!";
    } else {

        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errorMsg = "Этот email уже зарегистрирован.";
        } else {

            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                $errorMsg = "Имя пользователя уже занято.";
            }
        }


        if (empty($errorMsg)) {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, 'user')";
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute([$username, $email, $hash])) {

                $_SESSION['user_id'] = $pdo->lastInsertId();
                $_SESSION['username'] = $username;
                $_SESSION['user_role'] = 'user';
                header('Location: index.php');
                exit;
            } else {
                $errorMsg = "Ошибка при регистрации.";
            }
        }
    }
}

include 'includes/header.php';
?>

<div class="auth-card">
    <div class="card-header">Регистрация</div>
    <div class="card-body">
        <?php if ($errorMsg): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($errorMsg) ?></div>
        <?php endif; ?>
        <?php if ($successMsg): ?>
            <div class="alert alert-success"><?= $successMsg ?></div>
        <?php else: ?>
            <form method="post">
                <div class="form-group">
                    <label class="form-label">Имя</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Пароль (минимум 6 символов)</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Подтверждение пароля</label>
                    <input type="password" name="password_confirm" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">Зарегистрироваться</button>
            </form>
            <div class="auth-link">
                Уже есть аккаунт? <a href="login.php">Войти</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>