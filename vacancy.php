<?php
if (!isset($_COOKIE['user'])) {
    header('Location: login.html');
    exit();
}

require_once 'config.php';

$lang = $siteLang ?? 'ru';
$isKz = $lang === 'kk';

$stmt = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

$t = [
    'page_title' => $isKz ? 'Вакансия жариялау | TruWork' : 'Публикация вакансии | TruWork',
    'home' => $isKz ? 'Басты бет' : 'Главная',
    'vacancies' => $isKz ? 'Вакансиялар' : 'Вакансии',
    'about' => $isKz ? 'Біз туралы' : 'О нас',
    'faq' => 'FAQ',
    'support' => $isKz ? 'Қолдау' : 'Поддержка',
    'title' => $isKz ? 'Жаңа вакансия жариялау' : 'Опубликовать новую вакансию',
    'subtitle' => $isKz ? 'Форманы толтырып, вакансияны бірыңғай стильдегі карточка ретінде жариялаңыз.' : 'Заполните форму и опубликуйте вакансию в том же едином стиле, что и остальные страницы.',
    'job_title' => $isKz ? 'Вакансия атауы' : 'Название вакансии',
    'job_title_placeholder' => $isKz ? 'Мысалы: Frontend Developer' : 'Например: Frontend Developer',
    'company' => $isKz ? 'Компания' : 'Компания',
    'company_placeholder' => $isKz ? 'Компания атауы' : 'Название компании',
    'category' => $isKz ? 'Санат' : 'Категория',
    'category_placeholder' => $isKz ? 'Санатты таңдаңыз' : 'Выберите категорию',
    'salary' => $isKz ? 'Жалақы' : 'Зарплата',
    'salary_placeholder' => $isKz ? 'Мысалы: 350 000 - 500 000 тг' : 'Например: 350 000 - 500 000 тг',
    'location' => $isKz ? 'Орналасқан жері' : 'Местоположение',
    'location_placeholder' => $isKz ? 'Қала немесе қашықтан' : 'Город или удаленно',
    'description' => $isKz ? 'Сипаттама' : 'Описание',
    'description_placeholder' => $isKz ? 'Міндеттерді, талаптарды және жұмыс шарттарын жазыңыз' : 'Опишите обязанности, требования и условия работы',
    'submit' => $isKz ? 'Вакансияны жариялау' : 'Опубликовать вакансию',
    'policy' => $isKz ? 'Құпиялылық саясаты' : 'Политика конфиденциальности',
    'terms' => $isKz ? 'Пайдалану шарттары' : 'Условия использования',
    'login' => $isKz ? 'Кіру' : 'Войти',
];

$paths = [
    'index' => $isKz ? 'index_kk.php' : 'index.php',
    'vacancies' => $isKz ? 'vacancies_kk.php' : 'vacancies.php',
    'about' => $isKz ? 'about_kk.html' : 'about.html',
    'faq' => $isKz ? 'faq_kk.html' : 'faq.html',
    'support' => $isKz ? 'support_kk.html' : 'support.html',
    'login' => $isKz ? 'login_kk.html' : 'login.html',
    'policy' => $isKz ? 'policy_kk.html' : 'policy.html',
    'terms' => $isKz ? 'terms_kk.html' : 'terms.html',
];
?>
<!DOCTYPE html>
<html lang="<?= $isKz ? 'kk' : 'ru' ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($t['page_title']) ?></title>
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
        <a href="vacancy.php" class="is-active"><?= htmlspecialchars($t['submit']) ?></a>
        <a href="<?= htmlspecialchars($paths['about']) ?>"><?= htmlspecialchars($t['about']) ?></a>
        <a href="<?= htmlspecialchars($paths['faq']) ?>"><?= htmlspecialchars($t['faq']) ?></a>
        <a href="<?= htmlspecialchars($paths['support']) ?>"><?= htmlspecialchars($t['support']) ?></a>
      </nav>
      <div class="header-actions">
        <?php if (isset($_COOKIE['user'])): ?>
          <a class="button-primary" href="profile.php"><?= htmlspecialchars($_COOKIE['user']) ?></a>
        <?php else: ?>
          <a class="button-primary" href="<?= htmlspecialchars($paths['login']) ?>"><?= htmlspecialchars($t['login']) ?></a>
        <?php endif; ?>
      </div>
    </div>
  </header>

  <main class="page-main">
    <div class="site-shell">
      <section class="page-card">
        <h1 class="section-title"><?= htmlspecialchars($t['title']) ?></h1>
        <p class="section-subtitle"><?= htmlspecialchars($t['subtitle']) ?></p>

        <form class="form-stack" action="process_vacancy.php" method="post">
          <div class="form-grid">
            <div>
              <label class="label" for="title"><?= htmlspecialchars($t['job_title']) ?></label>
              <input class="input" id="title" type="text" name="title" placeholder="<?= htmlspecialchars($t['job_title_placeholder']) ?>" required>
            </div>
            <div>
              <label class="label" for="company"><?= htmlspecialchars($t['company']) ?></label>
              <input class="input" id="company" type="text" name="company" placeholder="<?= htmlspecialchars($t['company_placeholder']) ?>" required>
            </div>
            <div>
              <label class="label" for="category"><?= htmlspecialchars($t['category']) ?></label>
              <select class="input" id="category" name="category" required>
                <option value="" disabled selected><?= htmlspecialchars($t['category_placeholder']) ?></option>
                <?php foreach ($categories as $category): ?>
                  <option value="<?= (int) $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label class="label" for="salary"><?= htmlspecialchars($t['salary']) ?></label>
              <input class="input" id="salary" type="text" name="salary" placeholder="<?= htmlspecialchars($t['salary_placeholder']) ?>" required>
            </div>
          </div>

          <div>
            <label class="label" for="location"><?= htmlspecialchars($t['location']) ?></label>
            <input class="input" id="location" type="text" name="location" placeholder="<?= htmlspecialchars($t['location_placeholder']) ?>" required>
          </div>

          <div>
            <label class="label" for="description"><?= htmlspecialchars($t['description']) ?></label>
            <textarea class="textarea" id="description" name="description" placeholder="<?= htmlspecialchars($t['description_placeholder']) ?>" required></textarea>
          </div>

          <div>
            <button class="button-primary" type="submit"><?= htmlspecialchars($t['submit']) ?></button>
          </div>
        </form>
      </section>
    </div>
  </main>

  <footer class="site-footer">
    <div class="site-shell site-footer__panel">
      <div>
        <strong>TruWork</strong>
        <div class="footer-note"><?= $isKz ? 'Жаңа вакансияларды ұқыпты және түсінікті түрде жариялау.' : 'Аккуратная публикация новых вакансий в едином стиле.' ?></div>
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
