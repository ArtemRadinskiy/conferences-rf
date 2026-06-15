<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

require_once 'db.php';
$database = new Database();
$db = $database->getConnection();
$u_id = $_SESSION['user_id'];

$msg = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_review'])) {
    $b_id = intval($_POST['booking_id']);
    $rating = intval($_POST['rating']);
    $comment = trim($_POST['comment']);

    if ($rating >= 1 && $rating <= 5 && !empty($comment)) {
        $query = "INSERT INTO reviews (user_id, booking_id, rating, comment) VALUES (:user_id, :booking_id, :rating, :comment)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $u_id);
        $stmt->bindParam(':booking_id', $b_id);
        $stmt->bindParam(':rating', $rating);
        $stmt->bindParam(':comment', $comment);
        if ($stmt->execute()) {
            $msg = "<div class='alert alert-success py-2 small'>Спасибо за отзыв!</div>";
        }
    }
}

$query = "SELECT b.id, p.name AS premise_name, b.event_date, b.payment_method, b.status,
          (SELECT COUNT(*) FROM reviews r WHERE r.booking_id = b.id) as has_review
          FROM bookings b 
          JOIN premises p ON b.premise_id = p.id 
          WHERE b.user_id = :user_id 
          ORDER BY b.created_at DESC";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $u_id);
$stmt->execute();
$my_bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный кабинет — Конференции.РФ</title>
    <link href="https://jsdelivr.net" rel="stylesheet">
    <style>
        * { box-sizing: border-box; font-family: 'Segoe UI', Roboto, sans-serif; margin: 0; padding: 0; }
        body { background-color: #f0f2f5; color: #333; }
        .navbar { background-color: #0d6efd; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; color: white; }
        .navbar-brand { font-size: 20px; font-weight: 700; color: white; text-decoration: none; }
        .btn-action { background: #198754; color: white; padding: 8px 16px; border-radius: 8px; text-decoration: none; font-size: 14px; font-weight: 600; margin-right: 10px; }
        .btn-logout { background: rgba(255,255,255,0.2); color: white; padding: 8px 16px; border-radius: 8px; text-decoration: none; font-size: 14px; font-weight: 600; }
        .main-container { max-width: 1200px; margin: 30px auto; padding: 0 20px; display: flex; gap: 30px; flex-direction: row; }
        .left-section { flex: 1.2; min-width: 0; }
        .right-section { flex: 1.8; background: #ffffff; padding: 25px; border-radius: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); height: fit-content; }
        .slider-wrapper { width: 100%; height: 350px; position: relative; overflow: hidden; background: #ddd; border-radius: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); margin-bottom: 20px; }
        .slides { display: flex; width: 400%; height: 100%; animation: slideAnimation 12s infinite; }
        .slide { width: 25%; height: 100%; }
        .slide img { width: 100%; height: 100%; object-fit: cover; }
        @keyframes slideAnimation {
            0%, 20% { transform: translateX(0); }
            25%, 45% { transform: translateX(-25%); }
            50%, 70% { transform: translateX(-50%); }
            75%, 95% { transform: translateX(-75%); }
        }
        .badge.status-new { background-color: #fff3cd; color: #664d03; }
        .badge.status-assigned { background-color: #cff4fc; color: #055160; }
        .badge.status-completed { background-color: #d1e7dd; color: #0f5132; }
       @media (max-width: 768px) {
    .navbar { flex-direction: column; gap: 10px; text-align: center; padding: 10px; }
    .navbar div { display: flex; width: 100%; justify-content: center; gap: 5px; }
    .btn-action, .btn-logout { padding: 6px 10px; font-size: 12px; margin: 0; text-align: center; flex: 1; }
    .main-container { flex-direction: column; margin: 15px auto; gap: 20px; }
    .slider-wrapper { height: 200px; }
}
    </style>
</head>
<body>

<nav class="navbar">
    <a href="#" class="navbar-brand">Портал «Конференции.РФ»</a>
    <div>
        <a href="create_order.php" class="btn-action">Забронировать помещение</a>
        <a href="login.php?forced_logout=1" class="btn-logout">Выйти</a>
    </div>
</nav>

<div class="main-container">
    <div class="left-section">
        <div class="slider-wrapper">
            <div class="slides">
                <div class="slide"><img src="slide1.jpg" alt="1"></div>
                <div class="slide"><img src="slide2.jpg" alt="2"></div>
                <div class="slide"><img src="slide3.jpg" alt="3"></div>
                <div class="slide"><img src="slide4.jpg" alt="4"></div>
            </div>
        </div>
        <div class="p-3 bg-white shadow-sm" style="border-radius:16px;">
            <p class="small text-muted mb-0">Вы вошли в систему.</p>
        </div>
    </div>

    <div class="right-section">
        <h4 class="fw-bold mb-3">История ваших бронирований</h4>
        <?php echo $msg; ?>
        <?php if(isset($_GET['success'])): ?> <div class="alert alert-success py-2 small">Заявка создана!</div> <?php endif; ?>

        <?php if(count($my_bookings) == 0): ?>
            <p class="text-muted text-center py-4">Вы еще не создали ни одной заявки. <a href="create_order.php">Забронировать</a></p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle" style="font-size:14px;">
                    <thead class="table-dark">
                        <tr>
                            <th>№</th>
                            <th>Помещение</th>
                            <th>Дата начала</th>
                            <th>Способ оплаты</th>
                            <th>Статус</th>
                            <th>Отзыв</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($my_bookings as $b): ?>
                            <tr>
                                <td><strong><?php echo $b['id']; ?></strong></td>
                                <td><?php echo htmlspecialchars($b['premise_name']); ?></td>
                                <td><?php echo date('d.m.Y H:i', strtotime($b['event_date'])); ?></td>
                                <td><?php echo htmlspecialchars($b['payment_method']); ?></td>
                                <td>
                                    <?php 
                                        $badge = 'status-new';
                                        if ($b['status'] == 'Мероприятие назначено') $badge = 'status-assigned';
                                        if ($b['status'] == 'Мероприятие завершено') $badge = 'status-completed';
                                    ?>
                                    <span class="badge <?php echo $badge; ?> text-dark"><?php echo htmlspecialchars($b['status']); ?></span>
                                </td>
                                <td>
                                    <?php if($b['status'] == 'Мероприятие завершено'): ?>
                                        <?php if($b['has_review'] > 0): ?>
                                            <span class="text-muted small">Отзыв добавлен</span>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-outline-primary btn-sm px-2 py-0" data-bs-toggle="modal" data-bs-target="#modal<?php echo $b['id']; ?>" style="font-size:12px;">Оставить</button>
                                            
                                            <div class="modal fade" id="modal<?php echo $b['id']; ?>" tabindex="-1">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <form method="POST" action="">
                                                            <div class="modal-header"><h5>Отзыв к мероприятию №<?php echo $b['id']; ?></h5></div>
                                                            <div class="modal-body">
                                                                <input type="hidden" name="add_review" value="1">
                                                                <input type="hidden" name="booking_id" value="<?php echo $b['id']; ?>">
                                                                <div class="mb-3">
                                                                    <label class="form-label small fw-bold">Ваша оценка (1-5)</label>
                                                                    <select name="rating" class="form-select" required>
                                                                        <option value="5">5 — Отлично</option>
                                                                        <option value="4">4 — Хорошо</option>
                                                                        <option value="3">3 — Нормально</option>
                                                                        <option value="2">2 — Плохо</option>
                                                                        <option value="1">1 — Ужасно</option>
                                                                    </select>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label small fw-bold">Комментарий</label>
                                                                    <textarea name="comment" class="form-control" rows="3" required></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Закрыть</button>
                                                                                                <button type="submit" class="btn btn-primary btn-sm">Отправить</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <small class="text-muted">После завершения</small>
    <?php endif; ?>
</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://jsdelivr.net"></script>
</body>
</html>

