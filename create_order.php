<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

require_once 'db.php';
$database = new Database();
$db = $database->getConnection();

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $premise_id = intval($_POST['premise_id']);
    $event_date = !empty($_POST['event_date']) ? date('Y-m-d H:i:s', strtotime($_POST['event_date'])) : '';
    $payment_method = trim($_POST['payment_method']);

    if (!empty($premise_id) && !empty($event_date) && !empty($payment_method)) {
        /* ========================================================
           СТРОГО ПО ТЗ: СТАТУС ДЛЯ НОВОЙ ЗАЯВКИ ПО УМОЛЧАНИЮ
           Если в ТЗ изменится дефолтный статус новой заявки, поменяй 'Новая' ниже
           ======================================================== */
        $query = "INSERT INTO bookings (user_id, premise_id, event_date, payment_method, status) VALUES (:user_id, :premise_id, :event_date, :payment_method, 'Новая')";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->bindParam(':premise_id', $premise_id);
        $stmt->bindParam(':event_date', $event_date);
        $stmt->bindParam(':payment_method', $payment_method);
        
        if ($stmt->execute()) { 
            header("Location: index.php?success=1"); 
            exit(); 
        } else { $message = "Ошибка сохранения заявки."; }
    } else { $message = "Заполните все обязательные поля."; }
}

$premises = $db->query("SELECT id, name FROM premises")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- СТРОГО ПО ТЗ: НАЗВАНИЕ СИСТЕМЫ В ШАПКЕ ВКЛАДКИ -->
    <title>Оформление заявки — Конференции.РФ</title>
    <style>
        * { box-sizing: border-box; font-family: 'Segoe UI', Roboto, sans-serif; margin: 0; padding: 0; }
        body { background-color: #f0f2f5; color: #333; }
        .navbar { background-color: #212529; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; color: white; }
        .navbar-brand { font-size: 20px; font-weight: 700; color: white; text-decoration: none; }
        .btn-back { background: #0d6efd; color: white; padding: 8px 16px; border-radius: 8px; text-decoration: none; font-size: 14px; font-weight: 600; }
        
        .main-container { max-width: 500px; margin: 50px auto; padding: 0 20px; }
        .form-section { background: #ffffff; padding: 30px; border-radius: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .form-section h3 { margin-bottom: 20px; font-size: 22px; font-weight: 700; text-align: center; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-size: 13px; font-weight: 600; color: #65676b; text-transform: uppercase; }
        .form-input { width: 100%; padding: 12px; border: 1px solid #ced4da; border-radius: 10px; background: #f8f9fa; font-size: 15px; outline: none; }
        .form-input:focus { border-color: #0d6efd; background: #fff; }
        .btn-submit { width: 100%; padding: 14px; background: #198754; color: white; border: none; border-radius: 10px; font-size: 16px; font-weight: 600; cursor: pointer; }
    </style>
</head>
<body>

<nav class="navbar">
    <!-- СТРОГО ПО ТЗ: НАЗВАНИЕ СИСТЕМЫ В НАВБАРЕ -->
    <a href="index.php" class="navbar-brand">Портал «Конференции.РФ»</a>
    <a href="index.php" class="btn-back">← В кабинет</a>
</nav>

<div class="main-container">
    <div class="form-section">
        <h3>Бронирование помещения</h3>
        <?php if($message): ?> <div style="color:red; margin-bottom:15px; font-size:14px;"><?=$message?></div> <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <!-- СТРОГО ПО ТЗ: ВЫПАДАЮЩИЙ СПИСОК ПОМЕЩЕНИЙ (Аудитория, Коворкинг, Кинозал подгружаются автоматом из БД) -->
                <label>Тип помещения (Выпадающий список)</label>
                <select name="premise_id" class="form-input" required>
                    <option value="">-- Выберите помещение --</option>
                    <?php foreach($premises as $p): ?>
                        <option value="<?=$p['id']?>"><?=htmlspecialchars($p['name'])?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <!-- СТРОГО ПО ТЗ: УДОБНАЯ ДАТА НАЧАЛА В ФОРМАТЕ КАЛЕНДАРЯ -->
                <label>Время начала конференции</label>
                <input type="datetime-local" name="event_date" class="form-input" required>
            </div>
            <div class="form-group">
                <!-- СТРОГО ПО ТЗ: ПОДХОДЯЩИЙ СПОСОБ ОПЛАТЫ -->
                <label>Способ оплаты</label>
                <select name="payment_method" class="form-input" required>
                    <option value="">-- Выберите способ --</option>
                    <option value="Наличные">Наличные</option>
                    <option value="Банковская карта">Банковская карта</option>
                    <option value="Безналичный расчет">Безналичный расчет (юр. лица)</option>
                </select>
            </div>
            <button type="submit" class="btn-submit">Отправить на согласование</button>
        </form>
    </div>
</div>

</body>
</html>
