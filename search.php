<?php
require_once 'config.php';
require_once 'hh_jobs.php';

$lang = $siteLang ?? 'ru';
$isKz = $lang === 'kk';
$vacancyId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$source = trim((string) ($_GET['source'] ?? 'internal'));
$isHhVacancy = $source === 'hh';

if ($vacancyId <= 0) {
    http_response_code(404);
    exit($isKz ? 'Вакансия табылмады' : 'Вакансия не найдена');
}

if ($isHhVacancy) {
    if (!hhJobsTableExists($pdo)) {
        http_response_code(404);
        exit($isKz ? 'Вакансия табылмады' : 'Вакансия не найдена');
    }

    $stmt = $pdo->prepare("
        SELECT
            j.id,
            j.external_id,
            j.title,
            j.company,
            j.salary,
            j.city AS location,
            j.description,
            j.url,
            j.source,
            j.created_at,
            'HeadHunter' AS category_name,
            'TruWork Import' AS author
        FROM jobs j
        WHERE j.id = :id
        LIMIT 1
    ");
    $stmt->execute([':id' => $vacancyId]);
    $vacancy = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    $stmt = $pdo->prepare("
        SELECT v.*, c.name AS category_name
          FROM vacancies v
     LEFT JOIN categories c ON c.id = v.category_id
         WHERE v.id = :id
         LIMIT 1
    ");
    $stmt->execute([':id' => $vacancyId]);
    $vacancy = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$vacancy) {
    http_response_code(404);
    exit($isKz ? 'Вакансия табылмады' : 'Вакансия не найдена');
}

$alreadyApplied = false;
if ($isHhVacancy && isset($_COOKIE['user'])) {
    try {
        $alreadyApplied = hasAppliedToHhVacancy($pdo, $vacancyId, (string) $_COOKIE['user']);
    } catch (Throwable $e) {
        $alreadyApplied = false;
    }
}

if (!$isHhVacancy && isset($_COOKIE['user'])) {
    try {
        $checkStmt = $pdo->prepare("
            SELECT id
              FROM applications
             WHERE vacancy_id = :vacancy_id
               AND applicant = :applicant
             LIMIT 1
        ");
        $checkStmt->execute([
            ':vacancy_id' => $vacancyId,
            ':applicant' => (string) $_COOKIE['user'],
        ]);
        $alreadyApplied = (bool) $checkStmt->fetchColumn();
    } catch (Throwable $e) {
        $alreadyApplied = false;
    }
}

$t = [
    'title' => $isKz ? 'Вакансия туралы толық ақпарат' : 'Подробно о вакансии',
    'home' => $isKz ? 'Басты бет' : 'Главная',
    'vacancies' => $isKz ? 'Вакансиялар' : 'Вакансии',
    'publish' => $isKz ? 'Жариялау' : 'Опубликовать',
    'about' => $isKz ? 'Біз туралы' : 'О нас',
    'faq' => 'FAQ',
    'support' => $isKz ? 'Қолдау' : 'Поддержка',
    'login' => $isKz ? 'Кіру' : 'Войти',
    'back' => $isKz ? 'Вакансияларға оралу' : 'Вернуться к вакансиям',
    'company' => $isKz ? 'Компания' : 'Компания',
    'category' => $isKz ? 'Санат' : 'Категория',
    'salary' => $isKz ? 'Жалақы' : 'Зарплата',
    'location' => $isKz ? 'Орналасқан жері' : 'Местоположение',
    'description' => $isKz ? 'Сипаттама' : 'Описание',
    'author' => $isKz ? 'Жариялаған' : 'Опубликовал',
    'created_at' => $isKz ? 'Жарияланған күні' : 'Дата публикации',
    'apply' => $isKz ? 'Откликнуться' : 'Откликнуться',
    'applied' => $isKz ? 'Сіз бұған дейін отклик бергенсіз' : 'Вы уже откликались на эту вакансию',
    'login_notice' => $isKz ? 'Отклик беру үшін аккаунтқа кіріңіз.' : 'Чтобы откликнуться, войдите в аккаунт.',
    'hh_notice' => $isKz
        ? 'Бұл вакансия сыртқы дереккөзден импортталған және TruWork ішінде толық карточка ретінде көрсетіліп тұр.'
        : 'Эта вакансия импортирована из внешнего источника и отображается внутри TruWork как полная карточка.',
    'policy' => $isKz ? 'Құпиялық саясаты' : 'Политика конфиденциальности',
    'terms' => $isKz ? 'Пайдалану шарттары' : 'Условия использования',
    'source' => $isKz ? 'Дереккөз' : 'Источник',
    'source_internal' => $isKz ? 'Сайт' : 'Сайт',
    'source_hh' => 'HeadHunter',
    'salary_empty' => $isKz ? 'Көрсетілмеген' : 'Не указано',
    'location_empty' => $isKz ? 'Көрсетілмеген' : 'Не указано',
    'category_none' => $isKz ? 'Санат көрсетілмеген' : 'Без категории',
];

$paths = [
    'index' => $isKz ? 'index_kk.php' : 'index.php',
    'vacancies' => $isKz ? 'vacancies_kk.php' : 'vacancies.php',
    'publish' => $isKz ? 'vacancy_kk.php' : 'vacancy.php',
    'about' => $isKz ? 'about_kk.html' : 'about.html',
    'faq' => $isKz ? 'faq_kk.html' : 'faq.html',
    'support' => $isKz ? 'support_kk.html' : 'support.html',
    'login' => $isKz ? 'login_kk.html' : 'login.html',
    'profile' => $isKz ? 'profile_kk.php' : 'profile.php',
    'policy' => $isKz ? 'policy_kk.html' : 'policy.html',
    'terms' => $isKz ? 'terms_kk.html' : 'terms.html',
];
?>
<!DOCTYPE html>
<html lang="<?= $isKz ? 'kk' : 'ru' ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars((string) $vacancy['title']) ?> | TruWork</title>
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
        <a href="<?= htmlspecialchars($paths['vacancies']) ?>" class="is-active"><?= htmlspecialchars($t['vacancies']) ?></a>
        <a href="<?= htmlspecialchars($paths['publish']) ?>"><?= htmlspecialchars($t['publish']) ?></a>
        <a href="<?= htmlspecialchars($paths['about']) ?>"><?= htmlspecialchars($t['about']) ?></a>
        <a href="<?= htmlspecialchars($paths['faq']) ?>"><?= htmlspecialchars($t['faq']) ?></a>
        <a href="<?= htmlspecialchars($paths['support']) ?>"><?= htmlspecialchars($t['support']) ?></a>
      </nav>
      <div class="header-actions">
        <?php if (isset($_COOKIE['user'])): ?>
          <a class="button-primary" href="<?= htmlspecialchars($paths['profile']) ?>"><?= htmlspecialchars((string) $_COOKIE['user']) ?></a>
        <?php else: ?>
          <a class="button-primary" href="<?= htmlspecialchars($paths['login']) ?>"><?= htmlspecialchars($t['login']) ?></a>
        <?php endif; ?>
      </div>
    </div>
  </header>

  <main class="page-main">
    <div class="site-shell grid">
      <section class="page-card">
        <div class="stack-actions" style="margin-bottom:18px;">
          <a class="button-secondary" href="<?= htmlspecialchars($paths['vacancies']) ?>"><?= htmlspecialchars($t['back']) ?></a>
        </div>
        <h1 class="section-title" style="margin-bottom:8px;"><?= htmlspecialchars((string) $vacancy['title']) ?></h1>
        <p class="section-subtitle" style="margin-bottom:0;"><?= htmlspecialchars((string) ($vacancy['company'] ?? '')) ?></p>
        <?php if ($isHhVacancy): ?>
          <p class="section-subtitle" style="margin-top:14px; color:#475467;"><?= htmlspecialchars($t['hh_notice']) ?></p>
        <?php endif; ?>
      </section>

      <section class="page-card">
        <div class="stat-grid">
          <article class="stat-card">
            <strong><?= htmlspecialchars($t['salary']) ?></strong>
            <span><?= htmlspecialchars((string) ($vacancy['salary'] ?? $t['salary_empty'])) ?></span>
          </article>
          <article class="stat-card">
            <strong><?= htmlspecialchars($t['location']) ?></strong>
            <span><?= htmlspecialchars((string) ($vacancy['location'] ?? $t['location_empty'])) ?></span>
          </article>
          <article class="stat-card">
            <strong><?= htmlspecialchars($t['category']) ?></strong>
            <span><?= htmlspecialchars((string) ($vacancy['category_name'] ?? $t['category_none'])) ?></span>
          </article>
          <article class="stat-card">
            <strong><?= htmlspecialchars($t['source']) ?></strong>
            <span><?= htmlspecialchars($isHhVacancy ? $t['source_hh'] : $t['source_internal']) ?></span>
          </article>
          <article class="stat-card">
            <strong><?= htmlspecialchars($t['created_at']) ?></strong>
            <span><?= htmlspecialchars((string) ($vacancy['created_at'] ?? '')) ?></span>
          </article>
        </div>
      </section>

      <section class="page-card">
        <h2 class="section-title" style="font-size:28px;"><?= htmlspecialchars($t['description']) ?></h2>
        <div class="section-subtitle" style="white-space:pre-line;color:#334155;"><?= htmlspecialchars((string) ($vacancy['description'] ?? '')) ?></div>
      </section>

      <section class="page-card">
        <div class="grid grid-2">
          <article class="info-card">
            <h3><?= htmlspecialchars($t['company']) ?></h3>
            <p><?= htmlspecialchars((string) ($vacancy['company'] ?? '')) ?></p>
          </article>
          <article class="info-card">
            <h3><?= htmlspecialchars($t['author']) ?></h3>
            <p><?= htmlspecialchars((string) ($vacancy['author'] ?? 'TruWork')) ?></p>
          </article>
        </div>
      </section>

      <section class="page-card">
        <?php if (isset($_COOKIE['user'])): ?>
          <?php if ($alreadyApplied): ?>
            <div class="alert alert-info"><?= htmlspecialchars($t['applied']) ?></div>
          <?php else: ?>
            <form method="post" action="process_application.php" class="stack-actions">
              <input type="hidden" name="vacancy_id" value="<?= (int) $vacancy['id'] ?>">
              <input type="hidden" name="source" value="<?= htmlspecialchars($isHhVacancy ? 'hh' : 'internal') ?>">
              <button class="button-primary" type="submit"><?= htmlspecialchars($t['apply']) ?></button>
            </form>
          <?php endif; ?>
        <?php else: ?>
          <div class="alert alert-info"><?= htmlspecialchars($t['login_notice']) ?></div>
        <?php endif; ?>
      </section>
    </div>
  </main>

  <footer class="site-footer">
    <div class="site-shell site-footer__panel">
      <div>
        <strong>TruWork</strong>
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
