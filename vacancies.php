<?php
require_once 'config.php';

function plural_form(int $n, array $forms): string
{
    $n = abs($n) % 100;
    $n1 = $n % 10;
    if ($n > 10 && $n < 20) {
        return $forms[2];
    }
    if ($n1 > 1 && $n1 < 5) {
        return $forms[1];
    }
    if ($n1 === 1) {
        return $forms[0];
    }
    return $forms[2];
}

$lang = $siteLang ?? 'ru';
$isKz = $lang === 'kk';

$titleFilter = trim((string) ($_GET['title'] ?? ''));
$companyFilter = trim((string) ($_GET['company'] ?? ''));
$categoryFilter = trim((string) ($_GET['category'] ?? ''));
$sort = trim((string) ($_GET['sort'] ?? 'newest'));

$limit = 15;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$categoriesStmt = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC");
$categories = $categoriesStmt->fetchAll(PDO::FETCH_ASSOC);

$orderBy = 'v.created_at DESC';
if ($sort === 'oldest') {
    $orderBy = 'v.created_at ASC';
} elseif ($sort === 'company') {
    $orderBy = 'v.company ASC';
} elseif ($sort === 'title') {
    $orderBy = 'v.title ASC';
}

$conditions = [];
$params = [];

if ($titleFilter !== '') {
    $conditions[] = 'v.title LIKE :title';
    $params[':title'] = '%' . $titleFilter . '%';
}
if ($companyFilter !== '') {
    $conditions[] = 'v.company LIKE :company';
    $params[':company'] = '%' . $companyFilter . '%';
}
if ($categoryFilter !== '') {
    $conditions[] = 'v.category_id = :category';
    $params[':category'] = $categoryFilter;
}

$whereSql = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

$sql = "
    SELECT v.*, c.name AS category_name
      FROM vacancies v
 LEFT JOIN categories c ON c.id = v.category_id
    {$whereSql}
  ORDER BY {$orderBy}
     LIMIT {$limit}
    OFFSET {$offset}
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$vacancies = $stmt->fetchAll(PDO::FETCH_ASSOC);

$countSql = "
    SELECT COUNT(*)
      FROM vacancies v
    {$whereSql}
";
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($params);
$totalVacancies = (int) $countStmt->fetchColumn();
$totalPages = max(1, (int) ceil($totalVacancies / $limit));

$t = [
    'page_title' => $isKz ? 'TruWork - вакансиялар' : 'TruWork - вакансии',
    'home' => $isKz ? 'Басты бет' : 'Главная',
    'vacancies' => $isKz ? 'Вакансиялар' : 'Вакансии',
    'publish' => $isKz ? 'Жариялау' : 'Опубликовать',
    'about' => $isKz ? 'Біз туралы' : 'О нас',
    'faq' => 'FAQ',
    'support' => $isKz ? 'Қолдау' : 'Поддержка',
    'login' => $isKz ? 'Кіру' : 'Войти',
    'title' => $isKz ? 'Жаңа вакансияларды табу' : 'Найти актуальные вакансии',
    'subtitle' => $isKz ? 'Іздеу, компания және санат бойынша сүзгілеп, жаңа ұсыныстарды HH стиліне жақын карточкаларда қараңыз.' : 'Фильтруйте по названию, компании и категории и просматривайте свежие вакансии в более реалистичных карточках.',
    'title_placeholder' => $isKz ? 'Лауазым атауы' : 'Название должности',
    'company_placeholder' => $isKz ? 'Компания' : 'Компания',
    'all_categories' => $isKz ? 'Барлық санаттар' : 'Все категории',
    'category' => $isKz ? 'Санат' : 'Категория',
    'sort' => $isKz ? 'Сұрыптау' : 'Сортировка',
    'sort_newest' => $isKz ? 'Алдымен жаңалары' : 'Сначала новые',
    'sort_oldest' => $isKz ? 'Алдымен ескілері' : 'Сначала старые',
    'sort_company' => $isKz ? 'Компания бойынша' : 'По компании',
    'sort_title' => $isKz ? 'Атауы бойынша' : 'По названию',
    'filter' => $isKz ? 'Қолдану' : 'Применить',
    'reset' => $isKz ? 'Тазарту' : 'Очистить',
    'found' => $isKz ? 'Табылды' : 'Найдено',
    'vacancy_forms' => $isKz ? ['вакансия', 'вакансия', 'вакансия'] : ['вакансия', 'вакансии', 'вакансий'],
    'salary' => $isKz ? 'Жалақы' : 'Зарплата',
    'location' => $isKz ? 'Орналасқан жері' : 'Местоположение',
    'details' => $isKz ? 'Толығырақ' : 'Подробнее',
    'empty' => $isKz ? 'Сұраныс бойынша вакансия табылмады.' : 'По вашему запросу вакансии не найдены.',
    'page' => $isKz ? 'Бет' : 'Страница',
    'policy' => $isKz ? 'Құпиялық саясаты' : 'Политика конфиденциальности',
    'terms' => $isKz ? 'Пайдалану шарттары' : 'Условия использования',
    'footer_note' => $isKz ? 'Жаңа әрі шынайырақ көрінетін вакансиялар таспасы.' : 'Лента вакансий в более реалистичном стиле с упором на новые предложения.',
];

$paths = [
    'index' => $isKz ? 'index_kk.php' : 'index.php',
    'vacancies' => $isKz ? 'vacancies_kk.php' : 'vacancies.php',
    'publish' => $isKz ? 'vacancy_kk.php' : 'vacancy.php',
    'about' => $isKz ? 'about_kk.html' : 'about.html',
    'faq' => $isKz ? 'faq_kk.html' : 'faq.html',
    'support' => $isKz ? 'support_kk.html' : 'support.html',
    'login' => $isKz ? 'login_kk.html' : 'login.html',
    'policy' => $isKz ? 'policy_kk.html' : 'policy.html',
    'terms' => $isKz ? 'terms_kk.html' : 'terms.html',
];

function page_link(int $page, array $params): string
{
    $params['page'] = $page;
    return '?' . http_build_query($params);
}
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
  <style>
    .vacancy-card {
      display: grid;
      gap: 18px;
    }
    .vacancy-meta {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
      margin: 12px 0;
    }
    .vacancy-chip {
      display: inline-flex;
      padding: 8px 12px;
      border-radius: 999px;
      background: #f2f7fd;
      border: 1px solid #d8e2ee;
      color: #4c5b70;
      font-weight: 600;
      font-size: 14px;
    }
    .pager {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
      align-items: center;
    }
  </style>
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
        <div class="lang-switch">
          <?php if ($isKz): ?>
            <a href="vacancies.php">RU</a>
            <span class="is-active">KZ</span>
          <?php else: ?>
            <span class="is-active">RU</span>
            <a href="vacancies_kk.php">KZ</a>
          <?php endif; ?>
        </div>
        <?php if (isset($_COOKIE['user'])): ?>
          <a class="button-primary" href="<?= $isKz ? 'profile_kk.php' : 'profile.php' ?>"><?= htmlspecialchars($_COOKIE['user']) ?></a>
        <?php else: ?>
          <a class="button-primary" href="<?= htmlspecialchars($paths['login']) ?>"><?= htmlspecialchars($t['login']) ?></a>
        <?php endif; ?>
      </div>
    </div>
  </header>

  <main class="page-main">
    <div class="site-shell grid">
      <section class="page-card">
        <h1 class="section-title"><?= htmlspecialchars($t['title']) ?></h1>
        <p class="section-subtitle"><?= htmlspecialchars($t['subtitle']) ?></p>
        <div class="stack-actions" style="margin-bottom:18px;">
          <a class="button-secondary" href="<?= $isKz ? 'smart_search_kk.php' : 'smart_search.php' ?>">
            <?= $isKz ? 'Ақылды іздеуге өту' : 'Перейти в умный поиск' ?>
          </a>
        </div>

        <form method="get" class="form-grid">
          <div>
            <label class="label" for="title"><?= htmlspecialchars($t['title']) ?></label>
            <input class="input" id="title" type="text" name="title" value="<?= htmlspecialchars($titleFilter) ?>" placeholder="<?= htmlspecialchars($t['title_placeholder']) ?>">
          </div>
          <div>
            <label class="label" for="company"><?= htmlspecialchars($t['company_placeholder']) ?></label>
            <input class="input" id="company" type="text" name="company" value="<?= htmlspecialchars($companyFilter) ?>" placeholder="<?= htmlspecialchars($t['company_placeholder']) ?>">
          </div>
          <div>
            <label class="label" for="category"><?= htmlspecialchars($t['category']) ?></label>
            <select class="input" id="category" name="category">
              <option value=""><?= htmlspecialchars($t['all_categories']) ?></option>
              <?php foreach ($categories as $category): ?>
                <option value="<?= (int) $category['id'] ?>" <?= $categoryFilter === (string) $category['id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($category['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="label" for="sort"><?= htmlspecialchars($t['sort']) ?></label>
            <select class="input" id="sort" name="sort">
              <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>><?= htmlspecialchars($t['sort_newest']) ?></option>
              <option value="oldest" <?= $sort === 'oldest' ? 'selected' : '' ?>><?= htmlspecialchars($t['sort_oldest']) ?></option>
              <option value="company" <?= $sort === 'company' ? 'selected' : '' ?>><?= htmlspecialchars($t['sort_company']) ?></option>
              <option value="title" <?= $sort === 'title' ? 'selected' : '' ?>><?= htmlspecialchars($t['sort_title']) ?></option>
            </select>
          </div>
          <div class="stack-actions">
            <button class="button-primary" type="submit"><?= htmlspecialchars($t['filter']) ?></button>
            <a class="button-secondary" href="<?= htmlspecialchars($paths['vacancies']) ?>"><?= htmlspecialchars($t['reset']) ?></a>
          </div>
        </form>
      </section>

      <section class="page-card">
        <p class="section-subtitle" style="margin-bottom:0;">
          <?= htmlspecialchars($t['found']) ?> <?= $totalVacancies ?> <?= htmlspecialchars(plural_form($totalVacancies, $t['vacancy_forms'])) ?>
        </p>
      </section>

      <?php if ($vacancies): ?>
        <?php foreach ($vacancies as $vacancy): ?>
          <section class="page-card vacancy-card">
            <div class="dashboard-hero">
              <div class="dashboard-hero__text">
                <h2 style="margin:0 0 8px;"><?= htmlspecialchars((string) ($vacancy['title'] ?? '')) ?></h2>
                <div class="muted"><?= htmlspecialchars((string) ($vacancy['company'] ?? '')) ?></div>
                <div class="vacancy-meta">
                  <span class="vacancy-chip"><?= htmlspecialchars($t['salary']) ?>: <?= htmlspecialchars((string) ($vacancy['salary'] ?? 'Не указана')) ?></span>
                  <span class="vacancy-chip"><?= htmlspecialchars($t['location']) ?>: <?= htmlspecialchars((string) ($vacancy['location'] ?? 'Не указано')) ?></span>
                  <span class="vacancy-chip"><?= htmlspecialchars($t['category']) ?>: <?= htmlspecialchars((string) ($vacancy['category_name'] ?? 'Без категории')) ?></span>
                </div>
                <p class="muted" style="margin:0;">
                  <?= htmlspecialchars(mb_strlen((string) ($vacancy['description'] ?? '')) > 260 ? mb_substr((string) $vacancy['description'], 0, 260) . '...' : (string) ($vacancy['description'] ?? '')) ?>
                </p>
              </div>
              <div class="dashboard-hero__actions">
                <a class="button-primary" href="search.php?id=<?= (int) $vacancy['id'] ?>"><?= htmlspecialchars($t['details']) ?></a>
              </div>
            </div>
          </section>
        <?php endforeach; ?>
      <?php else: ?>
        <section class="page-card">
          <p class="section-subtitle" style="margin-bottom:0;"><?= htmlspecialchars($t['empty']) ?></p>
        </section>
      <?php endif; ?>

      <section class="page-card">
        <div class="pager">
          <span class="muted"><?= htmlspecialchars($t['page']) ?> <?= $page ?> / <?= $totalPages ?></span>
          <?php if ($page > 1): ?>
            <a class="button-secondary" href="<?= htmlspecialchars(page_link($page - 1, $_GET)) ?>">←</a>
          <?php endif; ?>
          <?php if ($page < $totalPages): ?>
            <a class="button-secondary" href="<?= htmlspecialchars(page_link($page + 1, $_GET)) ?>">→</a>
          <?php endif; ?>
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
