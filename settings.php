<?php
require_once 'config.php';

if (!isset($_COOKIE['user'])) {
    header('Location: login.html');
    exit();
}

$lang = $siteLang ?? 'ru';
$isKz = $lang === 'kk';
$currentUser = (string) $_COOKIE['user'];

function ensure_settings_columns(PDO $pdo): void
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

ensure_settings_columns($pdo);

$notice = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $update = $pdo->prepare("
        UPDATE users
           SET fullname = :fullname,
               email = :email,
               phone = :phone,
               address = :address,
               bio = :bio
         WHERE username = :username
    ");

    try {
        $update->execute([
            ':fullname' => trim((string) ($_POST['full_name'] ?? '')),
            ':email' => trim((string) ($_POST['email'] ?? '')),
            ':phone' => trim((string) ($_POST['phone'] ?? '')),
            ':address' => trim((string) ($_POST['address'] ?? '')),
            ':bio' => trim((string) ($_POST['bio'] ?? '')),
            ':username' => $currentUser,
        ]);
        $notice = $isKz ? 'Параметрлер сақталды.' : 'Настройки сохранены.';
    } catch (Throwable $e) {
        $error = $isKz ? 'Параметрлерді сақтау мүмкін болмады.' : 'Не удалось сохранить настройки.';
    }
}

$userStmt = $pdo->prepare("
    SELECT username, fullname, email, phone, address, bio
      FROM users
     WHERE username = :username
     LIMIT 1
");
$userStmt->execute([':username' => $currentUser]);
$user = $userStmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    http_response_code(404);
    exit($isKz ? 'Пайдаланушы табылмады' : 'Пользователь не найден');
}

$t = [
    'title' => $isKz ? 'Баптаулар | TruWork' : 'Настройки | TruWork',
    'home' => $isKz ? 'Басты бет' : 'Главная',
    'vacancies' => $isKz ? 'Вакансиялар' : 'Вакансии',
    'publish' => $isKz ? 'Жариялау' : 'Опубликовать',
    'faq' => 'FAQ',
    'support' => $isKz ? 'Қолдау' : 'Поддержка',
    'profile' => $isKz ? 'Профиль' : 'Профиль',
    'settings' => $isKz ? 'Баптаулар' : 'Настройки',
    'security' => $isKz ? 'Қауіпсіздік' : 'Безопасность',
    'skills' => $isKz ? 'Дағдыларды тексеру' : 'Проверка навыков',
    'logout' => $isKz ? 'Шығу' : 'Выйти',
    'heading' => $isKz ? 'Аккаунт баптаулары' : 'Настройки аккаунта',
    'subtitle' => $isKz ? 'Профиль деректерін жаңартыңыз. Бұл форма өзгерістерді бірден сақтайды.' : 'Обновите данные профиля. Эта форма сразу сохраняет актуальные контактные данные.',
    'saved' => $isKz ? 'Сақталды' : 'Сохранено',
    'fullname' => $isKz ? 'Толық аты' : 'Полное имя',
    'email' => 'Email',
    'phone' => $isKz ? 'Телефон' : 'Телефон',
    'address' => $isKz ? 'Адрес' : 'Адрес',
    'bio' => $isKz ? 'Өзі туралы' : 'О себе',
    'save' => $isKz ? 'Сақтау' : 'Сохранить',
    'policy' => $isKz ? 'Құпиялық саясаты' : 'Политика конфиденциальности',
    'terms' => $isKz ? 'Пайдалану шарттары' : 'Условия использования',
    'footer_note' => $isKz ? 'Жеке баптаулар енді кабинет құрылымымен толық біріктірілген.' : 'Настройки теперь встроены в ту же структуру кабинета, что и профиль.',
];

$paths = [
    'index' => $isKz ? 'index_kk.php' : 'index.php',
    'vacancies' => $isKz ? 'vacancies_kk.php' : 'vacancies.php',
    'publish' => $isKz ? 'vacancy_kk.php' : 'vacancy.php',
    'faq' => $isKz ? 'faq_kk.html' : 'faq.html',
    'support' => $isKz ? 'support_kk.html' : 'support.html',
    'profile' => $isKz ? 'profile_kk.php' : 'profile.php',
    'settings' => $isKz ? 'settings_kk.php' : 'settings.php',
    'security' => $isKz ? 'security_kk.php' : 'security.php',
    'policy' => $isKz ? 'policy_kk.html' : 'policy.html',
    'terms' => $isKz ? 'terms_kk.html' : 'terms.html',
    'tests' => 'IDM.php',
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
        <a href="<?= htmlspecialchars($paths['faq']) ?>"><?= htmlspecialchars($t['faq']) ?></a>
        <a href="<?= htmlspecialchars($paths['support']) ?>"><?= htmlspecialchars($t['support']) ?></a>
      </nav>
      <div class="header-actions">
        <div class="lang-switch">
          <?php if ($isKz): ?>
            <a href="settings.php">RU</a>
            <span class="is-active">KZ</span>
          <?php else: ?>
            <span class="is-active">RU</span>
            <a href="settings_kk.php">KZ</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </header>

  <main class="page-main">
    <div class="site-shell dashboard-layout">
      <aside class="dashboard-sidebar">
        <h2 class="sidebar-title"><?= htmlspecialchars($t['settings']) ?></h2>
        <nav class="sidebar-nav">
          <a href="<?= htmlspecialchars($paths['profile']) ?>"><span><?= htmlspecialchars($t['profile']) ?></span><span>01</span></a>
          <a class="is-active" href="<?= htmlspecialchars($paths['settings']) ?>"><span><?= htmlspecialchars($t['settings']) ?></span><span>02</span></a>
          <a href="<?= htmlspecialchars($paths['security']) ?>"><span><?= htmlspecialchars($t['security']) ?></span><span>03</span></a>
          <a href="<?= htmlspecialchars($paths['tests']) ?>"><span><?= htmlspecialchars($t['skills']) ?></span><span>04</span></a>
          <a href="logout.php"><span><?= htmlspecialchars($t['logout']) ?></span><span>05</span></a>
        </nav>
      </aside>

      <section class="dashboard-content">
        <div class="dashboard-hero">
          <div class="dashboard-hero__text">
            <span class="profile-badge"><?= htmlspecialchars($t['saved']) ?></span>
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

        <form method="post" class="form-stack">
          <div class="form-grid">
            <div>
              <label class="label" for="full_name"><?= htmlspecialchars($t['fullname']) ?></label>
              <input class="input" id="full_name" type="text" name="full_name" value="<?= htmlspecialchars((string) ($user['fullname'] ?? '')) ?>">
            </div>
            <div>
              <label class="label" for="email"><?= htmlspecialchars($t['email']) ?></label>
              <input class="input" id="email" type="email" name="email" value="<?= htmlspecialchars((string) ($user['email'] ?? '')) ?>">
            </div>
            <div>
              <label class="label" for="phone"><?= htmlspecialchars($t['phone']) ?></label>
              <input class="input" id="phone" type="text" name="phone" value="<?= htmlspecialchars((string) ($user['phone'] ?? '')) ?>">
            </div>
            <div>
              <label class="label" for="address"><?= htmlspecialchars($t['address']) ?></label>
              <input class="input" id="address" type="text" name="address" value="<?= htmlspecialchars((string) ($user['address'] ?? '')) ?>">
            </div>
          </div>

          <div>
            <label class="label" for="bio"><?= htmlspecialchars($t['bio']) ?></label>
            <textarea class="textarea" id="bio" name="bio"><?= htmlspecialchars((string) ($user['bio'] ?? '')) ?></textarea>
          </div>

          <div class="stack-actions">
            <button class="button-primary" type="submit"><?= htmlspecialchars($t['save']) ?></button>
          </div>
        </form>
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
