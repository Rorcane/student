<?php
$lang = $siteLang ?? 'ru';
$isKz = $lang === 'kk';

$t = [
    'title' => $isKz ? 'TruWork - жұмыс пен мамандарды іздеу' : 'TruWork - поиск работы и сотрудников',
    'home' => $isKz ? 'Басты бет' : 'Главная',
    'vacancies' => $isKz ? 'Вакансиялар' : 'Вакансии',
    'about' => $isKz ? 'О нас' : 'О нас',
    'faq' => 'FAQ',
    'support' => $isKz ? 'Қолдау' : 'Поддержка',
    'contacts' => $isKz ? 'Байланыс' : 'Контакты',
    'login' => $isKz ? 'Кіру' : 'Войти',
    'profile' => $isKz ? 'Профиль' : 'Профиль',
    'eyebrow' => $isKz ? 'Жұмыс пен командаға арналған бір платформа' : 'Одна платформа для работы и найма',
    'hero_title' => $isKz ? 'Жұмыс табу мен қызметкер таңдауды бір жүйеге жинаңыз.' : 'Соберите поиск работы и найм сотрудников в одну систему.',
    'hero_text' => $isKz ? 'TruWork компаниялар мен мамандарға вакансия, өтінім және негізгі HR процестерін түсінікті интерфейсте басқаруға көмектеседі.' : 'TruWork помогает компаниям и специалистам управлять вакансиями, откликами и базовыми HR-процессами в одном понятном интерфейсе.',
    'hero_primary' => $isKz ? 'Вакансияларды ашу' : 'Открыть вакансии',
    'hero_secondary' => $isKz ? 'Жариялау' : 'Опубликовать вакансию',
    'features_title' => $isKz ? 'Неге TruWork' : 'Почему TruWork',
    'features_text' => $isKz ? 'Платформаның басты артықшылықтары бір бетте.' : 'Ключевые преимущества платформы на одной странице.',
    'feature_1_title' => $isKz ? 'Бірізді интерфейс' : 'Единый интерфейс',
    'feature_1_text' => $isKz ? 'Пайдаланушыға көрінетін беттер енді бірдей контейнермен, бірдей ырғақпен және бірдей визуалдық логикамен жұмыс істейді.' : 'Все пользовательские страницы теперь работают с одинаковой шириной, ритмом и визуальной логикой.',
    'feature_2_title' => $isKz ? 'Екі тіл' : 'Два языка',
    'feature_2_text' => $isKz ? 'Орыс және қазақ тіліндегі нұсқалар арасында тез ауысуға болады.' : 'Можно быстро переключаться между русской и казахской версиями страниц.',
    'feature_3_title' => $isKz ? 'Жылдам навигация' : 'Быстрая навигация',
    'feature_3_text' => $isKz ? 'Негізгі әрекеттер шапкадан қолжетімді: вакансия іздеу, көмек, байланыс және кіру.' : 'Основные действия доступны прямо из шапки: поиск вакансий, помощь, контакты и вход.',
    'feature_4_title' => $isKz ? 'Түсінікті құрылым' : 'Понятная структура',
    'feature_4_text' => $isKz ? 'Ақпараттық беттер карточкаларға бөлінген, сондықтан мәтін жеңіл оқылады.' : 'Информационные страницы разбиты на карточки, поэтому текст читается легче.',
    'metrics_title' => $isKz ? 'Платформаға не маңызды' : 'Что важно для платформы',
    'metric_1' => $isKz ? 'Бірдей бет ені' : 'Одинаковая ширина страниц',
    'metric_2' => $isKz ? 'RU / KZ ауысуы' : 'Переключение RU / KZ',
    'metric_3' => $isKz ? 'Жеңіл навигация' : 'Простая навигация',
    'metric_4' => $isKz ? 'Таза визуал' : 'Чистый визуальный стиль',
    'footer_note' => $isKz ? 'Жұмыс іздеу мен қызметкер таңдауға арналған бірыңғай HR-платформа.' : 'Единая HR-платформа для поиска работы и подбора специалистов.',
    'policy' => $isKz ? 'Құпиялылық саясаты' : 'Политика конфиденциальности',
    'terms' => $isKz ? 'Пайдалану шарттары' : 'Условия использования',
];

$paths = [
    'index' => $isKz ? 'index_kk.php' : 'index.php',
    'vacancies' => $isKz ? 'vacancies_kk.php' : 'vacancies.php',
    'about' => $isKz ? 'about_kk.html' : 'about.html',
    'faq' => $isKz ? 'faq_kk.html' : 'faq.html',
    'support' => $isKz ? 'support_kk.html' : 'support.html',
    'contact' => $isKz ? 'contact_kk.html' : 'contact.html',
    'login' => $isKz ? 'login_kk.html' : 'login.html',
    'policy' => $isKz ? 'policy_kk.html' : 'policy.html',
    'terms' => $isKz ? 'terms_kk.html' : 'terms.html',
    'alt' => $isKz ? 'index.php' : 'index_kk.php',
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
        <a href="<?= htmlspecialchars($paths['index']) ?>" class="is-active"><?= htmlspecialchars($t['home']) ?></a>
        <a href="<?= htmlspecialchars($paths['vacancies']) ?>"><?= htmlspecialchars($t['vacancies']) ?></a>
        <a href="<?= htmlspecialchars($paths['about']) ?>"><?= htmlspecialchars($t['about']) ?></a>
        <a href="<?= htmlspecialchars($paths['faq']) ?>"><?= htmlspecialchars($t['faq']) ?></a>
        <a href="<?= htmlspecialchars($paths['support']) ?>"><?= htmlspecialchars($t['support']) ?></a>
        <a href="<?= htmlspecialchars($paths['contact']) ?>"><?= htmlspecialchars($t['contacts']) ?></a>
      </nav>
      <div class="header-actions">
        <div class="lang-switch">
          <?php if ($isKz): ?>
            <a href="index.php">RU</a>
            <span class="is-active">KZ</span>
          <?php else: ?>
            <span class="is-active">RU</span>
            <a href="index_kk.php">KZ</a>
          <?php endif; ?>
        </div>
        <?php if (isset($_COOKIE['user'])): ?>
          <a class="button-primary" href="profile.php"><?= htmlspecialchars($_COOKIE['user']) ?></a>
        <?php else: ?>
          <a class="button-primary" href="<?= htmlspecialchars($paths['login']) ?>"><?= htmlspecialchars($t['login']) ?></a>
        <?php endif; ?>
      </div>
    </div>
  </header>

  <section class="hero">
    <div class="site-shell">
      <div class="hero__panel">
        <div class="hero__eyebrow"><?= htmlspecialchars($t['eyebrow']) ?></div>
        <h1><?= htmlspecialchars($t['hero_title']) ?></h1>
        <p><?= htmlspecialchars($t['hero_text']) ?></p>
        <div class="hero__actions">
          <a class="button-primary" href="<?= htmlspecialchars($paths['vacancies']) ?>"><?= htmlspecialchars($t['hero_primary']) ?></a>
          <a class="button-secondary" href="vacancy.php"><?= htmlspecialchars($t['hero_secondary']) ?></a>
        </div>
        <div class="metric-row">
          <div class="metric"><strong>1160</strong><?= htmlspecialchars($t['metric_1']) ?></div>
          <div class="metric"><strong>2</strong><?= htmlspecialchars($t['metric_2']) ?></div>
          <div class="metric"><strong>1</strong><?= htmlspecialchars($t['metric_3']) ?></div>
          <div class="metric"><strong>0</strong><?= htmlspecialchars($t['metric_4']) ?></div>
        </div>
      </div>
    </div>
  </section>

  <main class="page-main">
    <div class="site-shell grid">
      <section class="page-card">
        <h2 class="section-title"><?= htmlspecialchars($t['features_title']) ?></h2>
        <p class="section-subtitle"><?= htmlspecialchars($t['features_text']) ?></p>
        <div class="grid grid-2">
          <article class="feature-card">
            <h3><?= htmlspecialchars($t['feature_1_title']) ?></h3>
            <p><?= htmlspecialchars($t['feature_1_text']) ?></p>
          </article>
          <article class="feature-card">
            <h3><?= htmlspecialchars($t['feature_2_title']) ?></h3>
            <p><?= htmlspecialchars($t['feature_2_text']) ?></p>
          </article>
          <article class="feature-card">
            <h3><?= htmlspecialchars($t['feature_3_title']) ?></h3>
            <p><?= htmlspecialchars($t['feature_3_text']) ?></p>
          </article>
          <article class="feature-card">
            <h3><?= htmlspecialchars($t['feature_4_title']) ?></h3>
            <p><?= htmlspecialchars($t['feature_4_text']) ?></p>
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
