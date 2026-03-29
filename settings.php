<?php
require_once 'config.php';

if (!isset($_COOKIE['user'])) {
    header('Location: login.html');
    exit();
}

$lang = $siteLang ?? 'ru';
$isKz = $lang === 'kk';
$currentUser = $_COOKIE['user'];

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

$notice = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("
        UPDATE users
           SET fullname = :fullname,
               email = :email,
               phone = :phone,
               address = :address,
               bio = :bio
         WHERE username = :username
    ");

    try {
        $stmt->execute([
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

$stmt = $pdo->prepare("
    SELECT username, fullname, email, phone, address, bio, avatar
      FROM users
     WHERE username = :username
     LIMIT 1
");
$stmt->execute([':username' => $currentUser]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die($isKz ? 'Пайдаланушы табылмады' : 'Пользователь не найден');
}

$t = [
    'title' => $isKz ? 'Баптаулар | TruWork' : 'Настройки | TruWork',
    'home' => $isKz ? 'Басты бет' : 'Главная',
    'vacancies' => $isKz ? 'Вакансиялар' : 'Вакансии',
    'publish' => $isKz ? 'Жариялау' : 'Опубликовать',
    'support' => $isKz ? 'Қолдау' : 'Поддержка',
    'profile' => $isKz ? 'Профиль' : 'Профиль',
    'settings' => $isKz ? 'Баптаулар' : 'Настройки',
    'security' => $isKz ? 'Қауіпсіздік' : 'Безопасность',
    'logout' => $isKz ? 'Шығу' : 'Выйти',
    'heading' => $isKz ? 'Аккаунт баптаулары' : 'Настройки аккаунта',
    'subtitle' => $isKz ? 'Профиль деректерін жаңартыңыз. Бұл бет енді шынымен сақтайды.' : 'Обновите данные профиля. Эта страница теперь действительно сохраняет изменения.',
    'full_name' => $isKz ? 'Толық аты' : 'Полное имя',
    'email' => 'Email',
    'phone' => $isKz ? 'Телефон' : 'Телефон',
    'address' => $isKz ? 'Мекенжай' : 'Адрес',
    'bio' => $isKz ? 'Өзі туралы' : 'О себе',
    'save' => $isKz ? 'Сақтау' : 'Сохранить',
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
        <a href="<?= htmlspecialchars($paths['profile']) ?>"><?= htmlspecialchars($t['profile']) ?></a>
        <a href="<?= htmlspecialchars($paths['settings']) ?>" class="is-active"><?= htmlspecialchars($t['settings']) ?></a>
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
        <a class="button-primary" href="<?= htmlspecialchars($paths['profile']) ?>"><?= htmlspecialchars($currentUser) ?></a>
      </div>
    </div>
  </header>

  <main class="page-main">
    <div class="site-shell grid">
      <section class="page-card">
        <h1 class="section-title"><?= htmlspecialchars($t['heading']) ?></h1>
        <p class="section-subtitle"><?= htmlspecialchars($t['subtitle']) ?></p>

        <div class="footer-links" style="margin-bottom:22px;">
          <a href="<?= htmlspecialchars($paths['profile']) ?>" class="button-secondary"><?= htmlspecialchars($t['profile']) ?></a>
          <a href="<?= htmlspecialchars($paths['settings']) ?>" class="button-primary"><?= htmlspecialchars($t['settings']) ?></a>
          <a href="security.php" class="button-secondary"><?= htmlspecialchars($t['security']) ?></a>
          <a href="logout.php" class="button-secondary"><?= htmlspecialchars($t['logout']) ?></a>
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
              <label class="label" for="full_name"><?= htmlspecialchars($t['full_name']) ?></label>
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
          <div>
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
        <div class="footer-note"><?= $isKz ? 'Параметрлерді заманауи және түсінікті түрде басқару.' : 'Современное и понятное управление настройками аккаунта.' ?></div>
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
