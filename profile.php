<?php
require_once 'config.php';

if (!isset($_COOKIE['user'])) {
    header('Location: login.html');
    exit();
}

$lang = $siteLang ?? 'ru';
$isKz = $lang === 'kk';

function ensure_profile_columns(PDO $pdo): void {
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

ensure_profile_columns($pdo);

$username = isset($_GET['user']) ? (string) $_GET['user'] : (string) $_COOKIE['user'];
$isOwner = $_COOKIE['user'] === $username;

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

$deleteOldViews = $pdo->prepare("DELETE FROM profile_views WHERE viewed_at < DATE_SUB(NOW(), INTERVAL 7 DAY)");
$deleteOldViews->execute();

$stmt = $pdo->prepare("
    SELECT username, fullname, email, phone, address, bio, avatar
      FROM users
     WHERE username = :username
     LIMIT 1
");
$stmt->execute([':username' => $username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die($isKz ? 'Пайдаланушы табылмады' : 'Пользователь не найден');
}

if ($isOwner && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar']) && $_FILES['avatar']['error'] === 0) {
    if (!is_dir('avatars')) {
        mkdir('avatars', 0755, true);
    }
    $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
    $newName = 'avatars/' . $_COOKIE['user'] . '_' . time() . '.' . $ext;
    move_uploaded_file($_FILES['avatar']['tmp_name'], $newName);
    $stmt = $pdo->prepare("UPDATE users SET avatar = :avatar WHERE username = :user");
    $stmt->execute([':avatar' => $newName, ':user' => $_COOKIE['user']]);
    $user['avatar'] = $newName;
}

if (isset($_COOKIE['user']) && $_COOKIE['user'] !== $username) {
    $viewer = $_COOKIE['user'];
    $check = $pdo->prepare("
        SELECT id
          FROM profile_views
         WHERE username = :profile
           AND viewer_username = :viewer
           AND DATE(viewed_at) = CURDATE()
    ");
    $check->execute([':profile' => $username, ':viewer' => $viewer]);

    if (!$check->fetch()) {
        $insert = $pdo->prepare("
            INSERT INTO profile_views (username, viewer_username, viewed_at)
            VALUES (:profile, :viewer, NOW())
        ");
        $insert->execute([':profile' => $username, ':viewer' => $viewer]);
    }
}

$stmt = $pdo->prepare("
    SELECT DATE(viewed_at) AS view_date, COUNT(*) AS total
      FROM profile_views
     WHERE username = :username
       AND viewed_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
     GROUP BY DATE(viewed_at)
");
$stmt->execute([':username' => $username]);
$viewsData = $stmt->fetchAll(PDO::FETCH_ASSOC);

$views = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $views[$date] = 0;
}
foreach ($viewsData as $row) {
    $views[$row['view_date']] = (int) $row['total'];
}
$viewsJson = json_encode(array_values($views));

$avatarPath = !empty($user['avatar']) ? $user['avatar'] : 'avatars/default-avatar.png';

$t = [
    'title' => $isKz ? 'Профиль | TruWork' : 'Профиль | TruWork',
    'home' => $isKz ? 'Басты бет' : 'Главная',
    'vacancies' => $isKz ? 'Вакансиялар' : 'Вакансии',
    'publish' => $isKz ? 'Жариялау' : 'Опубликовать',
    'support' => $isKz ? 'Қолдау' : 'Поддержка',
    'profile' => $isKz ? 'Профиль' : 'Профиль',
    'settings' => $isKz ? 'Баптаулар' : 'Настройки',
    'security' => $isKz ? 'Қауіпсіздік' : 'Безопасность',
    'logout' => $isKz ? 'Шығу' : 'Выйти',
    'heading' => $isKz ? 'Профиль' : 'Профиль',
    'subtitle' => $isKz ? 'Пайдаланушы туралы негізгі ақпарат пен белсенділік бір жерде.' : 'Основная информация о пользователе и активность в одном месте.',
    'fullname' => $isKz ? 'Толық аты' : 'Полное имя',
    'email' => 'Email',
    'phone' => $isKz ? 'Телефон' : 'Телефон',
    'address' => $isKz ? 'Мекенжай' : 'Адрес',
    'bio' => $isKz ? 'Өзі туралы' : 'О себе',
    'activity' => $isKz ? 'Профиль белсенділігі' : 'Активность профиля',
    'recommendation' => $isKz ? 'Профильді толықтыру ұсынылады.' : 'Рекомендуется заполнить профиль подробнее.',
    'edit' => $isKz ? 'Профильді өңдеу' : 'Редактировать профиль',
    'save' => $isKz ? 'Сақтау' : 'Сохранить',
    'cancel' => $isKz ? 'Бас тарту' : 'Отмена',
    'avatar' => $isKz ? 'Профиль суреті' : 'Фото профиля',
    'policy' => $isKz ? 'Құпиялылық саясаты' : 'Политика конфиденциальности',
    'terms' => $isKz ? 'Пайдалану шарттары' : 'Условия использования',
];

$paths = [
    'index' => $isKz ? 'index_kk.php' : 'index.php',
    'vacancies' => $isKz ? 'vacancies_kk.php' : 'vacancies.php',
    'publish' => $isKz ? 'vacancy_kk.php' : 'vacancy.php',
    'support' => $isKz ? 'support_kk.html' : 'support.html',
    'profile' => $isKz ? 'profile_kk.php' : 'profile.php',
    'settings' => $isKz ? 'settings_kk.php' : 'settings.php',
    'policy' => $isKz ? 'policy_kk.html' : 'policy.html',
    'terms' => $isKz ? 'terms_kk.html' : 'terms.html',
];
?>
<!DOCTYPE html>
<html lang="<?= $isKz ? 'kk' : 'ru' ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($t['title']) ?></title>
  <link rel="icon" type="image/png" href="/favicon-96x96.png" sizes="96x96">
  <link rel="icon" type="image/svg+xml" href="/favicon.svg">
  <link rel="shortcut icon" href="/favicon.ico">
  <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
  <link rel="manifest" href="/site.webmanifest">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/public-site.css">
</head>
<body>
  <header class="site-header">
    <div class="site-shell site-header__inner">
      <a class="brand" href="<?= htmlspecialchars($paths['index']) ?>"><img src="logo2.png" alt="TruWork"></a>
      <nav class="site-nav" aria-label="<?= $isKz ? 'Негізгі навигация' : 'Основная навигация' ?>">
        <a href="<?= htmlspecialchars($paths['index']) ?>"><?= htmlspecialchars($t['home']) ?></a>
        <a href="<?= htmlspecialchars($paths['vacancies']) ?>"><?= htmlspecialchars($t['vacancies']) ?></a>
        <a href="<?= htmlspecialchars($paths['publish']) ?>"><?= htmlspecialchars($t['publish']) ?></a>
        <a href="<?= htmlspecialchars($paths['support']) ?>"><?= htmlspecialchars($t['support']) ?></a>
        <a href="<?= htmlspecialchars($paths['profile']) ?>" class="is-active"><?= htmlspecialchars($t['profile']) ?></a>
        <a href="<?= htmlspecialchars($paths['settings']) ?>"><?= htmlspecialchars($t['settings']) ?></a>
      </nav>
      <div class="header-actions">
        <div class="lang-switch">
          <?php if ($isKz): ?>
            <a href="profile.php<?= !$isOwner ? '?user=' . urlencode($username) : '' ?>">RU</a>
            <span class="is-active">KZ</span>
          <?php else: ?>
            <span class="is-active">RU</span>
            <a href="profile_kk.php<?= !$isOwner ? '?user=' . urlencode($username) : '' ?>">KZ</a>
          <?php endif; ?>
        </div>
        <a class="button-primary" href="<?= htmlspecialchars($paths['settings']) ?>"><?= htmlspecialchars($_COOKIE['user']) ?></a>
      </div>
    </div>
  </header>

  <main class="page-main">
    <div class="site-shell grid">
      <section class="page-card">
        <h1 class="section-title"><?= htmlspecialchars($t['heading']) ?></h1>
        <p class="section-subtitle"><?= htmlspecialchars($t['subtitle']) ?></p>

        <div class="footer-links" style="margin-bottom:22px;">
          <a href="<?= htmlspecialchars($paths['profile']) ?>" class="button-primary"><?= htmlspecialchars($t['profile']) ?></a>
          <a href="<?= htmlspecialchars($paths['settings']) ?>" class="button-secondary"><?= htmlspecialchars($t['settings']) ?></a>
          <a href="security.php" class="button-secondary"><?= htmlspecialchars($t['security']) ?></a>
          <a href="logout.php" class="button-secondary"><?= htmlspecialchars($t['logout']) ?></a>
        </div>

        <div class="grid grid-2">
          <div class="info-card" style="text-align:center;">
            <img src="<?= htmlspecialchars($avatarPath) ?>" alt="avatar" style="width:180px;height:180px;border-radius:24px;object-fit:cover;margin:0 auto 18px;">
            <h3 style="margin-bottom:6px;"><?= htmlspecialchars((string) ($user['fullname'] ?? $user['username'])) ?></h3>
            <p class="muted" style="margin-bottom:0;">@<?= htmlspecialchars($user['username']) ?></p>
          </div>
          <div class="info-card">
            <p><strong><?= htmlspecialchars($t['fullname']) ?>:</strong> <?= htmlspecialchars((string) ($user['fullname'] ?? '')) ?></p>
            <p><strong><?= htmlspecialchars($t['email']) ?>:</strong> <?= htmlspecialchars((string) ($user['email'] ?? '')) ?></p>
            <p><strong><?= htmlspecialchars($t['phone']) ?>:</strong> <?= htmlspecialchars((string) ($user['phone'] ?? '')) ?></p>
            <p><strong><?= htmlspecialchars($t['address']) ?>:</strong> <?= htmlspecialchars((string) ($user['address'] ?? '')) ?></p>
            <p style="margin-bottom:0;"><strong><?= htmlspecialchars($t['bio']) ?>:</strong> <?= htmlspecialchars((string) ($user['bio'] ?? '')) ?></p>
          </div>
        </div>

        <div class="grid grid-2" style="margin-top:22px;">
          <div class="info-card">
            <h3><?= htmlspecialchars($t['activity']) ?></h3>
            <div class="bg-light p-3 rounded">
              <canvas id="viewsChart" height="150"></canvas>
            </div>
          </div>
          <div class="info-card">
            <h3><?= htmlspecialchars($t['edit']) ?></h3>
            <p><?= htmlspecialchars($t['recommendation']) ?></p>
            <?php if ($isOwner): ?>
              <button class="button-primary" type="button" data-bs-toggle="modal" data-bs-target="#editProfileModal"><?= htmlspecialchars($t['edit']) ?></button>
            <?php endif; ?>
          </div>
        </div>
      </section>
    </div>
  </main>

  <?php if ($isOwner): ?>
    <div class="modal fade" id="editProfileModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <form method="post" enctype="multipart/form-data" class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title"><?= htmlspecialchars($t['edit']) ?></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= htmlspecialchars($t['cancel']) ?>"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label"><?= htmlspecialchars($t['avatar']) ?></label>
              <input type="file" name="avatar" class="form-control" accept="image/*">
            </div>
            <p class="text-muted mb-0"><?= $isKz ? 'Қалған деректерді баптаулар бетінен өзгертіңіз.' : 'Остальные данные редактируются на странице настроек.' ?></p>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-primary"><?= htmlspecialchars($t['save']) ?></button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= htmlspecialchars($t['cancel']) ?></button>
          </div>
        </form>
      </div>
    </div>
  <?php endif; ?>

  <footer class="site-footer">
    <div class="site-shell site-footer__panel">
      <div>
        <strong>TruWork</strong>
        <div class="footer-note"><?= $isKz ? 'Профиль мен белсенділікті бірыңғай заманауи стильде көру.' : 'Просмотр профиля и активности в едином современном стиле.' ?></div>
      </div>
      <div class="footer-links">
        <a href="<?= htmlspecialchars($paths['policy']) ?>"><?= htmlspecialchars($t['policy']) ?></a>
        <a href="<?= htmlspecialchars($paths['terms']) ?>"><?= htmlspecialchars($t['terms']) ?></a>
        <a href="<?= htmlspecialchars($paths['support']) ?>"><?= htmlspecialchars($t['support']) ?></a>
      </div>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    const viewsData = <?= $viewsJson ?>;
    const maxValue = Math.max(...viewsData);
    const dynamicMax = maxValue < 1 ? 1 : maxValue + 1;

    new Chart(document.getElementById('viewsChart'), {
      type: 'line',
      data: {
        labels: ['6','5','4','3','2','1','0'],
        datasets: [{
          label: '<?= $isKz ? 'Қаралымдар' : 'Просмотры' ?>',
          data: viewsData,
          borderColor: '#1d4ed8',
          backgroundColor: 'rgba(29,78,216,0.08)',
          tension: 0.35,
          fill: true
        }]
      },
      options: {
        responsive: true,
        scales: {
          y: {
            min: 0,
            max: dynamicMax,
            ticks: { stepSize: 1 }
          }
        }
      }
    });
  </script>
</body>
</html>
