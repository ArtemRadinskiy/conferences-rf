<?php
session_start();
require_once 'db.php';
require_once 'User.php';

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

$errors = ['login' => '', 'password' => '', 'general' => ''];
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fio = trim($_POST['fio']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $login = trim($_POST['login']);
    $password = $_POST['password'];

    if (!preg_match('/^[a-zA-Z0-9]{6,}$/', $login)) {
        $errors['login'] = "Минимум 6 символов, только латиница и цифры!";
    }
    if (strlen($password) < 8) {
        $errors['password'] = "Пароль должен быть не менее 8 символов!";
    }

    if (empty($errors['login']) && empty($errors['password'])) {
        $result = $user->register($fio, $phone, $email, $login, $password);
        if ($result === true) {
            $success = "Регистрация успешна! Теперь вы можете войти.";
        } else {
            $errors['general'] = $result;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация — Конференции.РФ</title>
    <style>
        * { box-sizing: border-box; font-family: 'Segoe UI', Roboto, sans-serif; margin: 0; padding: 0; }
        body { background-color: #f0f2f5; display: flex; justify-content: center; align-items: center; min-height: 100vh; padding: 20px; }
        .card-custom { width: 100%; max-width: 500px; background: #ffffff; padding: 30px; border-radius: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        h3 { font-size: 24px; font-weight: 700; color: #212529; text-align: center; margin-bottom: 25px; }
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; margin-bottom: 6px; font-size: 13px; font-weight: 600; color: #65676b; }
        .form-input { width: 100%; padding: 12px; border: 1px solid #ced4da; border-radius: 10px; background: #f8f9fa; font-size: 15px; outline: none; transition: 0.2s; }
        .form-input:focus { border-color: #0d6efd; background: #fff; box-shadow: 0 0 0 3px rgba(13,110,253,0.15); }
        .form-input.is-invalid { border-color: #dc3545; background-color: #fff8f8; }
        .invalid-feedback { color: #dc3545; font-size: 12px; font-weight: 500; margin-top: 5px; }
        .btn-custom { width: 100%; padding: 14px; background: #0d6efd; color: white; border: none; border-radius: 10px; font-size: 16px; font-weight: 600; cursor: pointer; margin-top: 10px; box-shadow: 0 4px 12px rgba(13,110,253,0.2); transition: 0.2s; }
        .btn-custom:hover { background: #0b5ed7; }
        .alert { padding: 12px; border-radius: 10px; font-size: 14px; margin-bottom: 20px; font-weight: 500; }
        .alert-success { background: #d1e7dd; color: #0f5132; border: 1px solid #badbcc; }
        .alert-danger { background: #f8d7da; color: #842029; border: 1px solid #f5c2c7; }
        .text-center { text-align: center; margin-top: 15px; }
        .text-center a { color: #65676b; text-decoration: none; font-size: 14px; }
        .text-center a:hover { text-decoration: underline; color: #0d6efd; }
    </style>
</head>
<body>

<div class="card-custom">
    <h3>Регистрация на портале</h3>
    
    <?php if($success): ?> <div class="alert alert-success"><?=$success?> <a href="login.php" style="font-weight:700; color:#0f5132;">Войти</a></div> <?php endif; ?>
    <?php if($errors['general']): ?> <div class="alert alert-danger"><?=$errors['general']?></div> <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label>ФИО</label>
            <input type="text" name="fio" class="form-input" required>
        </div>
        <div class="form-group">
            <label>Контактный телефон</label>
            <input type="text" name="phone" class="form-input" required>
        </div>
        <div class="form-group">
            <label>E-mail</label>
            <input type="email" name="email" class="form-input" required>
        </div>
        
        <div class="form-group">
            <label>Логин (минимум 6 символов, латиница и цифры)</label>
            <input type="text" name="login" class="form-input <?=!empty($errors['login'])?'is-invalid':''?>" required>
            <?php if(!empty($errors['login'])): ?>
                <div class="invalid-feedback"><?=$errors['login']?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label>Пароль (минимум 8 символов)</label>
            <input type="password" name="password" class="form-input <?=!empty($errors['password'])?'is-invalid':''?>" required>
            <?php if(!empty($errors['password'])): ?>
                <div class="invalid-feedback"><?=$errors['password']?></div>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn-custom">Зарегистрироваться</button>
    </form>
    <div class="text-center">
        <a href="login.php">Уже зарегистрированы? Вход</a>
    </div>
</div>

</body>
</html>
