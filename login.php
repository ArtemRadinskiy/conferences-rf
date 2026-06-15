<?php
session_start();
require_once 'db.php';
require_once 'User.php';

$database = new Database();
$db = $database->getConnection();
$user = new User($db);
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = isset($_POST['login']) ? trim($_POST['login']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if ($login === 'Admin26' && $password === 'Demo20') {
        $stmt = $db->prepare("SELECT id, fio FROM users WHERE login = 'Admin26' LIMIT 1");
        $stmt->execute();
        $adminData = $stmt->fetch(PDO::FETCH_ASSOC);

        $_SESSION['user_id'] = $adminData ? $adminData['id'] : 1;
        $_SESSION['fio'] = 'Главный Administrator';
        $_SESSION['role_id'] = 2;
        header("Location: admin.php");
        exit();
    }

    if (!empty($login) && !empty($password)) {
        $userData = $user->login($login, $password);
        if ($userData) {
            $_SESSION['user_id'] = $userData['id'];
            $_SESSION['fio'] = $userData['fio'];
            $_SESSION['role_id'] = $userData['role_id'];
            
            if ($_SESSION['role_id'] == 2) {
                header("Location: admin.php");
            } else {
                header("Location: index.php");
            }
            exit();
        } else {
            $error = "Неверный логин или пароль.";
        }
    }
}

if (isset($_GET['forced_logout'])) {
    $_SESSION = array();
    session_destroy();
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход — Конференции.РФ</title>
    <style>
        * { box-sizing: border-box; font-family: 'Segoe UI', Roboto, sans-serif; margin: 0; padding: 0; }
        body { background-color: #f0f2f5; display: flex; justify-content: center; align-items: center; min-height: 100vh; padding: 20px; }
        .card-custom { width: 100%; max-width: 420px; background: #ffffff; padding: 30px; border-radius: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        h3 { font-size: 24px; font-weight: 700; color: #212529; text-align: center; margin-bottom: 25px; }
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; margin-bottom: 6px; font-size: 13px; font-weight: 600; color: #65676b; }
        .form-input { width: 100%; padding: 12px; border: 1px solid #ced4da; border-radius: 10px; background: #f8f9fa; font-size: 15px; outline: none; transition: 0.2s; }
        .form-input:focus { border-color: #198754; background: #fff; box-shadow: 0 0 0 3px rgba(25,135,84,0.15); }
        .btn-custom { width: 100%; padding: 14px; background: #198754; color: white; border: none; border-radius: 10px; font-size: 16px; font-weight: 600; cursor: pointer; margin-top: 10px; box-shadow: 0 4px 12px rgba(25,135,84,0.2); transition: 0.2s; }
        .btn-custom:hover { background: #157347; }
        .alert { padding: 12px; border-radius: 10px; font-size: 14px; margin-bottom: 20px; font-weight: 500; background: #f8d7da; color: #842029; border: 1px solid #f5c2c7; }
        .text-center { text-align: center; margin-top: 15px; }
        .text-center a { color: #65676b; text-decoration: none; font-size: 14px; }
        .text-center a:hover { text-decoration: underline; color: #198754; }
    </style>
</head>
<body>

<div class="card-custom">
    <h3>Вход в систему</h3>
    
    <?php if(!empty($error)): ?> <div class="alert"><?=$error?></div> <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label>Логин</label>
            <input type="text" name="login" class="form-input" required>
        </div>
        <div class="form-group">
            <label>Пароль</label>
            <input type="password" name="password" class="form-input" required>
        </div>
        <button type="submit" class="btn-custom">Войти</button>
    </form>
    <div class="text-center">
        <a href="register.php">Еще не зарегистрированы? Регистрация</a>
    </div>
</div>

</body>
</html>
