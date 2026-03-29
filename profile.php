<?php
require_once 'config.php'; // подключение к PDO

if (!isset($_COOKIE['user'])) {
    header('Location: login.html');
    exit();
}

function ensure_user_profile_columns(PDO $pdo): void {
    $columns = [];
    $stmt = $pdo->query("SHOW COLUMNS FROM users");
    foreach ($stmt as $row) {
        $columns[$row['Field']] = true;
    }

    $required = [
        'phone' => "ALTER TABLE users ADD COLUMN phone VARCHAR(255) NULL",
        'address' => "ALTER TABLE users ADD COLUMN address VARCHAR(255) NULL",
        'bio' => "ALTER TABLE users ADD COLUMN bio TEXT NULL",
        'avatar' => "ALTER TABLE users ADD COLUMN avatar VARCHAR(255) NULL",
    ];

    foreach ($required as $name => $sql) {
        if (!isset($columns[$name])) {
            $pdo->exec($sql);
        }
    }
}

if (isset($_GET['user'])) {
    $username = $_GET['user'];
} else {
    $username = $_COOKIE['user'];
}
ensure_user_profile_columns($pdo);
$isOwner = ($_COOKIE['user'] === $username);
$pdo->exec("
    CREATE TABLE IF NOT EXISTS profile_views (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(255) NOT NULL,
        viewer_username VARCHAR(255) NOT NULL,
        viewed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_profile_views_username (username),
        INDEX idx_profile_views_viewer (viewer_username),
        INDEX idx_profile_views_viewed_at (viewed_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
");
// Удаляем просмотры старше 7 дней
$deleteOldViews = $pdo->prepare("
    DELETE FROM profile_views 
    WHERE viewed_at < DATE_SUB(NOW(), INTERVAL 7 DAY)
");
$deleteOldViews->execute();
// Получаем данные пользователя из БД
$stmt = $pdo->prepare("
    SELECT username, fullname AS full_name, email, phone, address, bio, avatar
    FROM users
    WHERE username = :username
");
$stmt->execute([':username' => $username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die('Пользователь не найден');
}
if ($isOwner && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar'])) {
    $file = $_FILES['avatar'];
    if ($file['error'] === 0) {
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newName = 'avatars/' . $_COOKIE['user'] . '_' . time() . '.' . $ext;
        move_uploaded_file($file['tmp_name'], $newName);

        // Сохраняем путь в базе
        $stmt = $pdo->prepare("UPDATE users SET avatar = :avatar WHERE username = :user");
        $stmt->execute([':avatar' => $newName, ':user' => $_COOKIE['user']]);
    }
}
// Записываем просмотр (если это не сам пользователь)
// Записываем уникальный просмотр (1 пользователь = 1 раз в день)
if (isset($_COOKIE['user']) && $_COOKIE['user'] !== $username) {

    $viewer = $_COOKIE['user'];

    // Проверяем — был ли просмотр сегодня
    $check = $pdo->prepare("
        SELECT id FROM profile_views
        WHERE username = :profile
        AND viewer_username = :viewer
        AND DATE(viewed_at) = CURDATE()
    ");

    $check->execute([
        ':profile' => $username,
        ':viewer'  => $viewer
    ]);

    // Если не было — добавляем
    if (!$check->fetch()) {
        $insert = $pdo->prepare("
            INSERT INTO profile_views (username, viewer_username, viewed_at)
            VALUES (:profile, :viewer, NOW())
        ");

        $insert->execute([
            ':profile' => $username,
            ':viewer'  => $viewer
        ]);
    }
}

$stmt = $pdo->prepare("
    SELECT DATE(viewed_at) as view_date, COUNT(*) as total
    FROM profile_views
    WHERE username = :username
      AND viewed_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
    GROUP BY DATE(viewed_at)
");
$stmt->execute([':username' => $username]);
$viewsData = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Формируем массив на 7 дней
$views = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $views[$date] = 0;
}

foreach ($viewsData as $row) {
    $views[$row['view_date']] = (int)$row['total'];
}

$viewsJson = json_encode(array_values($views));

?>

<!DOCTYPE html>
<html lang="ru">
<head>
	<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-QTCZG5LGVP"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-QTCZG5LGVP');
</script>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Профиль</title>
  <link rel="icon" type="image/png" href="/favicon-96x96.png" sizes="96x96" />
  <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
  <link rel="shortcut icon" href="/favicon.ico" />
  <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png" />
  <link rel="manifest" href="/site.webmanifest" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root { --primary: #2563eb; --secondary: #3b82f6; --accent: #60a5fa; --gradient: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);}    
    body { padding-top: 35px; background: #f8f9fa; font-family: 'Inter', sans-serif; }
    .navbar { box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);}    
    .nav-link { position: relative; padding: 0.5rem 1rem !important; color: #495057 !important; }
    .nav-link.active { color: var(--primary) !important; }
    .nav-link-border { position: absolute; bottom: 0; left: 0; width: 0; height: 2px; background: var(--primary); transition: width 0.3s ease; }
    .nav-link:hover .nav-link-border, .nav-link.active .nav-link-border { width: 100%; }
  </style>
</head>

<body class="bg-light">

<!-- Навигационная панель -->
<nav class="navbar navbar-expand-lg navbar-light bg-white fixed-top border-bottom">
    <div class="container">
      <a class="navbar-brand fw-bold d-flex align-items-center" href="index.php">
        <img src="logo2.png" alt="TruWork" width="95" class="me-2">
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navMenu">
        <ul class="navbar-nav ms-auto align-items-lg-center">
          <li class="nav-item"><a class="nav-link" href="vacancies.php"><span class="nav-link-border"></span>Найти работу</a></li>
          <li class="nav-item"><a class="nav-link" href="vacancy.php"><span class="nav-link-border"></span>Опубликовать</a></li>
          <li class="nav-item"><a class="nav-link" href="dashboard.php"><span class="nav-link-border"></span>Панель</a></li>
          <li class="nav-item"><a class="nav-link" href="discovery.php"><span class="nav-link-border"></span>Обзор</a></li>
        </ul>
        <?php if(isset($_COOKIE['user'])): ?>
          <a href="profile.php" class="btn btn-outline-primary ms-3"><?= htmlspecialchars($_COOKIE['user']) ?></a>
        <?php else: ?>
          <a href="login.html" class="btn btn-primary ms-3">Войти</a>
        <?php endif; ?>
      </div>
    </div>
  </nav>

  <main class="container py-5">
<div class="container-fluid">
    <div class="row">
        <!-- Боковое меню -->
        <div class="col-md-2 bg-white vh-100 border-end p-3">

            <ul class="nav flex-column">
                <li class="nav-item mb-2">
                    <a href="profile.php" class="nav-link active">
                        <i class="fas fa-home me-2"></i>Главная
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a href="Uslugi.php" class="nav-link">
                        <i class="fas fa-handshake me-2"></i>Услуги
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a href="portfol.php" class="nav-link">
                        <i class="fas fa-briefcase me-2"></i>Портфолио
                    </a>
                </li>
				<li class="nav-item mb-2">
                    <a href="IDM.php" class="nav-link">
                        <i class="fas fa-file-alt me-2"></i>Подтвердить навыки
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a href="settings.php" class="nav-link">
                        <i class="fas fa-cog me-2"></i>Аккаунт
                    </a>
                </li>
				<li class="nav-item mb-2">
                    <a href="logout.php" class="nav-link text-danger">
                        <i class="fas fa-sign-out-alt me-2"></i>Выход
                    </a>
                </li>
            </ul>
        </div>

        <!-- Основной контент -->
        <div class="col-md-7 p-4">
            <div class="row">
                <div class="col-md-4 text-center">
                    <?php $avatarPath = !empty($user['avatar']) ? $user['avatar'] : 'avatars/default-avatar.png';?>
                    <img src="<?= htmlspecialchars($avatarPath) ?>" 
                        class="rounded" 
                        style="width:200px; height:200px; object-fit:cover;">
                </div> 

                <div class="col-md-8">
                    <p><strong>ФИО:</strong> <?= htmlspecialchars($user['full_name'] ?? '') ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($user['email'] ?? '') ?></p>
                    <p><strong>Телефон:</strong> <?= htmlspecialchars($user['phone'] ?? '') ?></p>
                    <p><strong>Адрес:</strong> <?= htmlspecialchars($user['address'] ?? '') ?></p>
                </div>
            </div>
            <!-- Блоки контента -->
            <div class="stat-card mb-4 p-4">
                <p><strong>О себе:</strong> <?= htmlspecialchars($user['bio'] ?? '') ?></p>
                <h5><i class="fas fa-chart-line me-2"></i>Активность профиля</h5>
                <div class="row g-4 mt-3">
                    <div class="col-md-6">
                        <div class="bg-light p-3 rounded">
                            <canvas id="viewsChart" height="150"></canvas>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="alert alert-info">
                            <h6><i class="fas fa-lightbulb me-2"></i>Рекомендации</h6>
                            <p class="mb-0">Добавьте больше информации в профиль</p>
                        </div>
                    </div>
                </div>
                <?php if ($isOwner): ?>
                <button class="btn btn-outline-primary mb-3" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                    Редактировать профиль
                </button>
                <?php endif; ?>
                <?php if ($isOwner): ?>
                <div class="modal fade" id="editProfileModal" tabindex="-1">
                  <div class="modal-dialog">
                    <form method="post" action="update_profile.php" class="modal-content" enctype="multipart/form-data">
                      <div class="modal-header">
                        <h5 class="modal-title">Редактировать профиль</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                      </div>
                      <div class="modal-body">
                        <div class="mb-3">
                          <label class="form-label">Полное имя</label>
                          <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($user['full_name']) ?>">
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Email</label>
                          <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>">
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Телефон</label>
                          <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone']) ?>">
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Адрес</label>
                          <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($user['address']) ?>">
                        </div>
                        <div class="mb-3">
                          <label class="form-label">О себе</label>
                          <input type="text" name="bio" class="form-control" value="<?= htmlspecialchars($user['bio']) ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Фото профиля</label>
                            <input type="file" name="avatar" class="form-control" accept="image/*">
                        </div>
                      </div>
                      <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Сохранить</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                      </div>
                    </form>
                  </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Остальные блоки с аналогичным стилем -->
        </div>

        <!-- Правая панель -->
        <div class="col-md-3 p-4 bg-light">
            <div class="sticky-top">
                <h5 class="mb-3"><i class="fas fa-bell me-2"></i>Уведомления</h5>
                <div class="list-group">
                    <div class="list-group-item activity-item">
                        <small class="text-muted">Сегодня</small>
                        <p class="mb-0 mt-2">Новое сообщение от Иван Петров</p>
                    </div>
                    <!-- Другие уведомления -->
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const viewsData = <?= $viewsJson ?>;

    const maxValue = Math.max(...viewsData);
    const dynamicMax = maxValue < 1 ? 1 : maxValue + 1; // чтобы максимум был виден

    new Chart(document.getElementById('viewsChart'), {
        type: 'line',
        data: {
            labels: ['6','5','4','3','2','Вче','Сег'],
            datasets: [{
                label: 'Просмотры',
                data: viewsData,
                borderColor: '#2A73CC',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    min: 0,
                    max: dynamicMax,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
</script>

</main>
<!-- Футер -->
<<footer>
  <div class="container text-center">  <!-- добавили text-center -->
    <div class="row justify-content-center g-4">  <!-- центрируем колонки -->
      <div class="col-md-4">
        <img src="logo2.png" alt="TruWork" height="18">
        <p class="mt-3 text-muted">Инновационная HR-платформа нового поколения</p>
      </div>
      <div class="col-md-2">
        <h6>Компания</h6>
        <ul class="list-unstyled">
          <li><a href="about.html">О нас</a></li>
          <li><a href="https://www.instagram.com/truwork_official?igsh=MWlyaDVkaWQyZXFjYg%3D%3D&utm_source=qr" target="_blank" rel="noopener noreferrer">Блог</a></li>
        </ul>
      </div>
      <div class="col-md-2">
        <h6>Помощь</h6>
        <ul class="list-unstyled">
          <li><a href="support.html">Поддержка</a></li>
          <li><a href="faq.html">FAQ</a></li>
        </ul>
      </div>
    </div>

    <hr class="my-5">

    <div class="d-flex flex-column flex-md-row justify-content-center align-items-center text-muted"> 
      <!-- flex-column на мобильных, flex-row на десктопе -->
      <div class="mb-2 mb-md-0">© 2025 Truwork.kz. Все права защищены</div>
      <div class="ms-md-4">
        <a href="policy.html" class="me-3">Политика конфиденциальности</a>
        <a href="terms.html">Условия использования</a>
      </div>
    </div>
  </div>
</footer>
</body>
</html>
