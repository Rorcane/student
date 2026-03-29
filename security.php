<?php
require_once 'config.php';

if (!isset($_COOKIE['user'])) {
    header('Location: login.html');
    exit();
}

$lang = $siteLang ?? 'ru';
$isKz = $lang === 'kk';
$currentUser = (string) $_COOKIE['user'];
$notice = '';
$error = '';

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

$userStmt = $pdo->prepare("SELECT username, password, email, created_at FROM users WHERE username = :username LIMIT 1");
$userStmt->execute([':username' => $currentUser]);
$user = $userStmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    http_response_code(404);
    exit($isKz ? 'Пайдаланушы табылмады' : 'Пользователь не найден');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = (string) ($_POST['current_password'] ?? '');
    $newPassword = (string) ($_POST['new_password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

    if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
        $error = $isKz ? 'Барлық пароль өрістерін толтырыңыз.' : 'Заполните все поля пароля.';
    } elseif ($newPassword !== $confirmPassword) {
        $error = $isKz ? 'Жаңа парольдер сәйкес келмейді.' : 'Новые пароли не совпадают.';
    } elseif (mb_strlen($newPassword) < 6) {
        $error = $isKz ? 'Жаңа пароль кемінде 6 таңбадан тұруы керек.' : 'Новый пароль должен содержать минимум 6 символов.';
    } else {
        $storedPassword = (string) $user['password'];
        $validCurrent = password_get_info($storedPassword)['algo'] !== 0
            ? password_verify($currentPassword, $storedPassword)
            : hash_equals($storedPassword, $currentPassword);

        if (!$validCurrent) {
            $error = $isKz ? 'Ағымдағы пароль қате.' : 'Текущий пароль указан неверно.';
        } else {
            $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $updatePassword = $pdo->prepare("UPDATE users SET password = :password WHERE username = :username");
            $updatePassword->execute([
                ':password' => $newHash,
                ':username' => $currentUser,
            ]);
            $notice = $isKz ? 'Пароль сәтті жаңартылды.' : 'Пароль успешно обновлен.';
        }
    }
}

$activityStmt = $pdo->prepare("
    SELECT viewed_at, viewer_username
      FROM profile_views
     WHERE username = :username
     ORDER BY viewed_at DESC
     LIMIT 5
");

try {
    $activityStmt->execute([':username' => $currentUser]);
    $recentViews = $activityStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $recentViews = [];
}

$t = [
    'title' => $isKz ? 'Қауіпсіздік | TruWork' : 'Безопасность | TruWork',
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
    'heading' => $isKz ? 'Қауіпсіздік баптаулары' : 'Настройки безопасности',
    'subtitle' => $isKz
        ? 'Парольді өзгертіп, соңғы белсенділікті тексеріңіз.'
        : 'Измените пароль и проверьте недавнюю активность аккаунта.',
    'current_password' => $isKz ? 'Ағымдағы пароль' : 'Текущий пароль',
    'new_password' => $isKz ? 'Жаңа пароль' : 'Новый пароль',
    'confirm_password' => $isKz ? 'Парольді қайталау' : 'Повторите пароль',
    'save' => $isKz ? 'Парольді жаңарту' : 'Обновить пароль',
    'recent_activity' => $isKz ? 'Соңғы белсенділік' : 'Последняя активность',
    'recent_activity_note' => $isKz
        ? 'Профильге соңғы кірген пайдаланушылар тізімі.'
        : 'Список последних пользователей, которые открывали профиль.',
    'empty_activity' => $isKz ? 'Әзірге белсенділік тіркелмеген.' : 'Пока активность не зафиксирована.',
    'email' => 'Email',
    'since' => $isKz ? 'Тіркелген күні' : 'Дата регистрации',
    'policy' => $isKz ? 'Құпиялық саясаты' : 'Политика конфиденциальности',
    'terms' => $isKz ? 'Пайдалану шарттары' : 'Условия использования',
    'footer_note' => $isKz
        ? 'Қауіпсіздік бөлімі енді кабинет стилімен бірдей.'
        : 'Раздел безопасности теперь полностью оформлен в стиле кабинета.',
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
            <a href="security.php">RU</a>
            <span class="is-active">KZ</span>
          <?php else: ?>
            <span class="is-active">RU</span>
            <a href="security_kk.php">KZ</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </header>

  <main class="page-main">
    <div class="site-shell dashboard-layout">
      <aside class="dashboard-sidebar">
        <h2 class="sidebar-title"><?= htmlspecialchars($t['security']) ?></h2>
        <nav class="sidebar-nav">
          <a href="<?= htmlspecialchars($paths['profile']) ?>"><?= htmlspecialchars($t['profile']) ?></a>
          <a href="<?= htmlspecialchars($paths['settings']) ?>"><?= htmlspecialchars($t['settings']) ?></a>
          <a class="is-active" href="<?= htmlspecialchars($paths['security']) ?>"><?= htmlspecialchars($t['security']) ?></a>
          <a href="<?= htmlspecialchars($paths['tests']) ?>"><?= htmlspecialchars($t['skills']) ?></a>
          <a href="logout.php"><?= htmlspecialchars($t['logout']) ?></a>
        </nav>
      </aside>

      <section class="dashboard-content">
        <div class="dashboard-hero">
          <div class="dashboard-hero__text">
            <h1 class="section-title"><?= htmlspecialchars($t['heading']) ?></h1>
            <p class="section-subtitle"><?= htmlspecialchars($t['subtitle']) ?></p>
          </div>
        </div>

        <?php if ($notice): ?>
          <div class="alert alert-success"><?= htmlspecialchars($notice) ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
          <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="grid grid-2">
          <article class="info-card">
            <h3><?= htmlspecialchars($t['security']) ?></h3>
            <form method="post" class="form-stack">
              <div>
                <label class="label" for="current_password"><?= htmlspecialchars($t['current_password']) ?></label>
                <input class="input" id="current_password" type="password" name="current_password" required>
              </div>
              <div>
                <label class="label" for="new_password"><?= htmlspecialchars($t['new_password']) ?></label>
                <input class="input" id="new_password" type="password" name="new_password" required>
              </div>
              <div>
                <label class="label" for="confirm_password"><?= htmlspecialchars($t['confirm_password']) ?></label>
                <input class="input" id="confirm_password" type="password" name="confirm_password" required>
              </div>
              <div class="stack-actions">
                <button class="button-primary" type="submit"><?= htmlspecialchars($t['save']) ?></button>
              </div>
            </form>
          </article>

          <article class="info-card">
            <h3><?= htmlspecialchars($t['recent_activity']) ?></h3>
            <p><?= htmlspecialchars($t['recent_activity_note']) ?></p>
            <p><strong><?= htmlspecialchars($t['email']) ?>:</strong> <?= htmlspecialchars((string) ($user['email'] ?? '')) ?></p>
            <p><strong><?= htmlspecialchars($t['since']) ?>:</strong> <?= htmlspecialchars((string) ($user['created_at'] ?? '')) ?></p>
            <?php if ($recentViews): ?>
              <div class="form-stack">
                <?php foreach ($recentViews as $item): ?>
                  <div class="stat-card">
                    <strong><?= htmlspecialchars((string) $item['viewer_username']) ?></strong>
                    <span><?= htmlspecialchars((string) $item['viewed_at']) ?></span>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php else: ?>
              <div class="stat-card">
                <span><?= htmlspecialchars($t['empty_activity']) ?></span>
              </div>
            <?php endif; ?>
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
</body>
</html>
