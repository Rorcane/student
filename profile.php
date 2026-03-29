<?php
require_once 'config.php';

if (!isset($_COOKIE['user'])) {
    header('Location: login.html');
    exit();
}

$lang = $siteLang ?? 'ru';
$isKz = $lang === 'kk';

function ensure_profile_columns(PDO $pdo): void
{
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

    foreach ($required as $column => $sql) {
        if (!isset($columns[$column])) {
            $pdo->exec($sql);
        }
    }
}

ensure_profile_columns($pdo);

$username = isset($_GET['user']) ? trim((string) $_GET['user']) : (string) $_COOKIE['user'];
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

$pdo->prepare("DELETE FROM profile_views WHERE viewed_at < DATE_SUB(NOW(), INTERVAL 7 DAY)")->execute();

$stmt = $pdo->prepare("
    SELECT username, fullname, email, phone, address, bio, avatar
      FROM users
     WHERE username = :username
     LIMIT 1
");
$stmt->execute([':username' => $username]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    http_response_code(404);
    exit($isKz ? 'Пайдаланушы табылмады' : 'Пользователь не найден');
}

if ($isOwner && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
    if (!is_dir(__DIR__ . '/avatars')) {
        mkdir(__DIR__ . '/avatars', 0755, true);
    }

    $extension = strtolower((string) pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    if (in_array($extension, $allowed, true)) {
        $avatarPath = 'avatars/' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $_COOKIE['user']) . '_' . time() . '.' . $extension;
        if (move_uploaded_file($_FILES['avatar']['tmp_name'], __DIR__ . '/' . $avatarPath)) {
            $updateAvatar = $pdo->prepare("UPDATE users SET avatar = :avatar WHERE username = :username");
            $updateAvatar->execute([
                ':avatar' => $avatarPath,
                ':username' => $_COOKIE['user'],
            ]);
            $user['avatar'] = $avatarPath;
        }
    }
}

if (isset($_COOKIE['user']) && $_COOKIE['user'] !== $username) {
    $viewer = $_COOKIE['user'];
    $checkView = $pdo->prepare("
        SELECT id
          FROM profile_views
         WHERE username = :profile
           AND viewer_username = :viewer
           AND DATE(viewed_at) = CURDATE()
         LIMIT 1
    ");
    $checkView->execute([
        ':profile' => $username,
        ':viewer' => $viewer,
    ]);

    if (!$checkView->fetch()) {
        $insertView = $pdo->prepare("
            INSERT INTO profile_views (username, viewer_username, viewed_at)
            VALUES (:profile, :viewer, NOW())
        ");
        $insertView->execute([
            ':profile' => $username,
            ':viewer' => $viewer,
        ]);
    }
}

$activityStmt = $pdo->prepare("
    SELECT DATE(viewed_at) AS view_date, COUNT(*) AS total
      FROM profile_views
     WHERE username = :username
       AND viewed_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
     GROUP BY DATE(viewed_at)
");
$activityStmt->execute([':username' => $username]);
$activityRows = $activityStmt->fetchAll(PDO::FETCH_ASSOC);

$viewsByDay = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-{$i} days"));
    $viewsByDay[$date] = 0;
}
foreach ($activityRows as $row) {
    $viewsByDay[$row['view_date']] = (int) $row['total'];
}

$totalViews = array_sum($viewsByDay);
$profileFields = [
    trim((string) ($user['fullname'] ?? '')),
    trim((string) ($user['email'] ?? '')),
    trim((string) ($user['phone'] ?? '')),
    trim((string) ($user['address'] ?? '')),
    trim((string) ($user['bio'] ?? '')),
];
$filledFields = count(array_filter($profileFields, static fn($value) => $value !== ''));
$completion = (int) round(($filledFields / count($profileFields)) * 100);
$avatarPath = !empty($user['avatar']) ? $user['avatar'] : 'avatars/default-avatar.png';
$maxViews = max(1, max($viewsByDay));

$t = [
    'title' => $isKz ? 'Профиль | TruWork' : 'Профиль | TruWork',
    'home' => $isKz ? 'Басты бет' : 'Главная',
    'vacancies' => $isKz ? 'Вакансиялар' : 'Вакансии',
    'publish' => $isKz ? 'Жариялау' : 'Опубликовать',
    'about' => $isKz ? 'Біз туралы' : 'О нас',
    'faq' => 'FAQ',
    'support' => $isKz ? 'Қолдау' : 'Поддержка',
    'profile' => $isKz ? 'Профиль' : 'Профиль',
    'settings' => $isKz ? 'Баптаулар' : 'Настройки',
    'security' => $isKz ? 'Қауіпсіздік' : 'Безопасность',
    'skills' => $isKz ? 'Дағдыларды тексеру' : 'Проверка навыков',
    'logout' => $isKz ? 'Шығу' : 'Выйти',
    'heading' => $isKz ? 'Жеке профиль' : 'Личный профиль',
    'subtitle' => $isKz
        ? 'Негізгі ақпарат, белсенділік және жеке бөлімдер бір бетте.'
        : 'Основная информация, активность и личные разделы в одном аккуратном интерфейсе.',
    'owner_badge' => $isKz ? 'Бұл сіздің профиліңіз' : 'Это ваш профиль',
    'guest_badge' => $isKz ? 'Қарап шығу режимі' : 'Просмотр профиля',
    'total_views' => $isKz ? '7 күндегі қаралымдар' : 'Просмотры за 7 дней',
    'completion' => $isKz ? 'Профиль толтырылуы' : 'Заполненность профиля',
    'account' => $isKz ? 'Аккаунт' : 'Аккаунт',
    'fullname' => $isKz ? 'Толық аты' : 'Полное имя',
    'email' => 'Email',
    'phone' => $isKz ? 'Телефон' : 'Телефон',
    'address' => $isKz ? 'Мекенжай' : 'Адрес',
    'bio' => $isKz ? 'Өзі туралы' : 'О себе',
    'empty' => $isKz ? 'Әлі толтырылмаған' : 'Пока не заполнено',
    'activity' => $isKz ? 'Соңғы 7 күндегі белсенділік' : 'Активность за последние 7 дней',
    'activity_note' => $isKz
        ? 'Әр баған профиліңізді қараған адамдар санын көрсетеді.'
        : 'Каждый столбец показывает количество просмотров профиля за день.',
    'change_avatar' => $isKz ? 'Фотоны жаңарту' : 'Обновить фото',
    'policy' => $isKz ? 'Құпиялық саясаты' : 'Политика конфиденциальности',
    'terms' => $isKz ? 'Пайдалану шарттары' : 'Условия использования',
    'footer_note' => $isKz
        ? 'Жеке кабинет енді жалпы сайт стилімен толық үйлестірілген.'
        : 'Личный кабинет теперь полностью совпадает по стилю с остальным сайтом.',
];

$paths = [
    'index' => $isKz ? 'index_kk.php' : 'index.php',
    'vacancies' => $isKz ? 'vacancies_kk.php' : 'vacancies.php',
    'publish' => $isKz ? 'vacancy_kk.php' : 'vacancy.php',
    'about' => $isKz ? 'about_kk.html' : 'about.html',
    'faq' => $isKz ? 'faq_kk.html' : 'faq.html',
    'support' => $isKz ? 'support_kk.html' : 'support.html',
    'profile' => $isKz ? 'profile_kk.php' : 'profile.php',
    'settings' => $isKz ? 'settings_kk.php' : 'settings.php',
    'security' => $isKz ? 'security_kk.php' : 'security.php',
    'policy' => $isKz ? 'policy_kk.html' : 'policy.html',
    'terms' => $isKz ? 'terms_kk.html' : 'terms.html',
    'tests' => $isKz ? 'IDM_kk.php' : 'IDM.php',
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
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/public-site.css">
  <style>
    .profile-summary {
      display: grid;
      grid-template-columns: 188px minmax(0, 1fr);
      gap: 22px;
      align-items: center;
      margin-bottom: 24px;
    }
    .profile-avatar {
      width: 188px;
      height: 188px;
      border-radius: 28px;
      object-fit: cover;
      border: 4px solid #fff;
      box-shadow: 0 16px 34px rgba(20, 33, 61, 0.14);
    }
    .profile-badge {
      display: inline-flex;
      padding: 8px 14px;
      border-radius: 999px;
      background: rgba(29, 78, 216, 0.08);
      color: #1d4ed8;
      font-weight: 700;
      margin-bottom: 12px;
    }
    .activity-bars {
      display: grid;
      grid-template-columns: repeat(7, minmax(0, 1fr));
      gap: 12px;
      align-items: end;
      min-height: 220px;
      margin-top: 18px;
    }
    .activity-bar {
      display: flex;
      flex-direction: column;
      justify-content: end;
      gap: 10px;
      min-height: 220px;
    }
    .activity-bar__fill {
      border-radius: 18px 18px 10px 10px;
      background: linear-gradient(180deg, #1d4ed8 0%, #0f766e 100%);
      min-height: 18px;
    }
    .activity-bar__meta {
      text-align: center;
      color: var(--muted);
      font-size: 13px;
    }
    @media (max-width: 768px) {
      .profile-summary {
        grid-template-columns: 1fr;
      }
      .profile-avatar {
        width: 160px;
        height: 160px;
      }
      .activity-bars {
        gap: 10px;
      }
    }
  </style>
</head>
<body>
  <header class="site-header">
    <div class="site-shell site-header__inner">
      <a class="brand" href="<?= htmlspecialchars($paths['index']) ?>"><img src="logo2.png" alt="TruWork"></a>
      <nav class="site-nav" aria-label="<?= $isKz ? 'Негізгі навигация' : 'Основная навигация' ?>">
        <a href="<?= htmlspecialchars($paths['index']) ?>"><?= htmlspecialchars($t['home']) ?></a>
        <a href="<?= htmlspecialchars($paths['vacancies']) ?>"><?= htmlspecialchars($t['vacancies']) ?></a>
        <a href="<?= htmlspecialchars($paths['publish']) ?>"><?= htmlspecialchars($t['publish']) ?></a>
        <a href="<?= htmlspecialchars($paths['about']) ?>"><?= htmlspecialchars($t['about']) ?></a>
        <a href="<?= htmlspecialchars($paths['faq']) ?>"><?= htmlspecialchars($t['faq']) ?></a>
        <a href="<?= htmlspecialchars($paths['support']) ?>"><?= htmlspecialchars($t['support']) ?></a>
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
      </div>
    </div>
  </header>

  <main class="page-main">
    <div class="site-shell dashboard-layout">
      <aside class="dashboard-sidebar">
        <h2 class="sidebar-title"><?= htmlspecialchars($t['account']) ?></h2>
        <nav class="sidebar-nav">
          <a class="is-active" href="<?= htmlspecialchars($paths['profile']) ?><?= !$isOwner ? '?user=' . urlencode($username) : '' ?>"><?= htmlspecialchars($t['profile']) ?></a>
          <?php if ($isOwner): ?>
            <a href="<?= htmlspecialchars($paths['settings']) ?>"><?= htmlspecialchars($t['settings']) ?></a>
            <a href="<?= htmlspecialchars($paths['security']) ?>"><?= htmlspecialchars($t['security']) ?></a>
            <a href="<?= htmlspecialchars($paths['tests']) ?>"><?= htmlspecialchars($t['skills']) ?></a>
            <a href="logout.php"><?= htmlspecialchars($t['logout']) ?></a>
          <?php endif; ?>
        </nav>
      </aside>

      <section class="dashboard-content">
        <div class="dashboard-hero">
          <div class="dashboard-hero__text">
            <span class="profile-badge"><?= htmlspecialchars($isOwner ? $t['owner_badge'] : $t['guest_badge']) ?></span>
            <h1 class="section-title"><?= htmlspecialchars($t['heading']) ?></h1>
            <p class="section-subtitle"><?= htmlspecialchars($t['subtitle']) ?></p>
          </div>
          <?php if ($isOwner): ?>
            <form method="post" enctype="multipart/form-data" class="stack-actions" id="avatar-form">
              <label class="button-primary" for="avatar"><?= htmlspecialchars($t['change_avatar']) ?></label>
              <input id="avatar" type="file" name="avatar" accept="image/*" hidden>
            </form>
          <?php endif; ?>
        </div>

        <div class="profile-summary">
          <img class="profile-avatar" src="<?= htmlspecialchars($avatarPath) ?>" alt="avatar">
          <div>
            <h2 style="margin:0 0 6px;"><?= htmlspecialchars((string) ($user['fullname'] ?: $user['username'])) ?></h2>
            <p class="muted" style="margin:0 0 14px;">@<?= htmlspecialchars($user['username']) ?></p>
            <div class="stat-grid">
              <article class="stat-card">
                <strong><?= $totalViews ?></strong>
                <span><?= htmlspecialchars($t['total_views']) ?></span>
              </article>
              <article class="stat-card">
                <strong><?= $completion ?>%</strong>
                <span><?= htmlspecialchars($t['completion']) ?></span>
              </article>
              <article class="stat-card">
                <strong><?= $isOwner ? 'You' : 'View' ?></strong>
                <span><?= htmlspecialchars($isOwner ? $t['owner_badge'] : $t['guest_badge']) ?></span>
              </article>
            </div>
          </div>
        </div>

        <div class="grid grid-2">
          <article class="info-card">
            <h3><?= htmlspecialchars($t['account']) ?></h3>
            <p><strong><?= htmlspecialchars($t['fullname']) ?>:</strong> <?= htmlspecialchars((string) ($user['fullname'] ?: $t['empty'])) ?></p>
            <p><strong><?= htmlspecialchars($t['email']) ?>:</strong> <?= htmlspecialchars((string) ($user['email'] ?: $t['empty'])) ?></p>
            <p><strong><?= htmlspecialchars($t['phone']) ?>:</strong> <?= htmlspecialchars((string) ($user['phone'] ?: $t['empty'])) ?></p>
            <p><strong><?= htmlspecialchars($t['address']) ?>:</strong> <?= htmlspecialchars((string) ($user['address'] ?: $t['empty'])) ?></p>
            <p style="margin-bottom:0;"><strong><?= htmlspecialchars($t['bio']) ?>:</strong> <?= htmlspecialchars((string) ($user['bio'] ?: $t['empty'])) ?></p>
          </article>

          <article class="info-card">
            <h3><?= htmlspecialchars($t['activity']) ?></h3>
            <p><?= htmlspecialchars($t['activity_note']) ?></p>
            <div class="activity-bars">
              <?php foreach ($viewsByDay as $date => $value): ?>
                <?php $height = max(18, (int) round(($value / $maxViews) * 170)); ?>
                <div class="activity-bar">
                  <div class="activity-bar__meta"><?= $value ?></div>
                  <div class="activity-bar__fill" style="height: <?= $height ?>px;"></div>
                  <div class="activity-bar__meta"><?= htmlspecialchars(date('d.m', strtotime($date))) ?></div>
                </div>
              <?php endforeach; ?>
            </div>
          </article>
        </div>
      </section>
    </div>
  </main>

  <footer class="site-footer">
    <div class="site-shell site-footer__panel">
      <div>
        <strong>TruWork</strong>
        <div class="footer-note"><?= htmlspecialchars($t['footer_note']) ?></div>
      </div>
      <div class="footer-links">
        <a href="<?= htmlspecialchars($paths['policy']) ?>"><?= htmlspecialchars($t['policy']) ?></a>
        <a href="<?= htmlspecialchars($paths['terms']) ?>"><?= htmlspecialchars($t['terms']) ?></a>
        <a href="<?= htmlspecialchars($paths['support']) ?>"><?= htmlspecialchars($t['support']) ?></a>
      </div>
    </div>
  </footer>
  <?php if ($isOwner): ?>
    <script>
      (function () {
        var input = document.getElementById('avatar');
        var form = document.getElementById('avatar-form');
        if (!input || !form) {
          return;
        }
        input.addEventListener('change', function () {
          if (input.files && input.files.length > 0) {
            form.submit();
          }
        });
      }());
    </script>
  <?php endif; ?>
</body>
</html>
