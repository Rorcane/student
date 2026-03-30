<?php
require_once 'config.php';
require_once 'hh_jobs.php';

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

function page_link(int $page, array $params): string
{
    $params['page'] = $page;
    return '?' . http_build_query($params);
}

function vacancy_detail_link(array $vacancy): string
{
    if (($vacancy['source'] ?? 'internal') === 'hh') {
        return 'search.php?source=hh&id=' . (int) ($vacancy['id'] ?? 0);
    }

    return 'search.php?id=' . (int) ($vacancy['id'] ?? 0);
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

$categoriesStmt = $pdo->query('SELECT id, name FROM categories ORDER BY name ASC');
$categories = $categoriesStmt->fetchAll(PDO::FETCH_ASSOC);

$hhSyncError = null;
$hhQueries = $titleFilter !== ''
    ? [$titleFilter]
    : ['php', 'backend developer', 'frontend developer'];

try {
    syncHeadHunterVacancies($pdo, $hhQueries, 1);
} catch (Throwable $e) {
    $hhSyncError = $e->getMessage();
    error_log('[HH Sync] ' . $e->getMessage());
}

$hasJobsTable = hhJobsTableExists($pdo);

$sortMap = [
    'newest' => 'created_at DESC',
    'oldest' => 'created_at ASC',
    'company' => 'company ASC',
    'title' => 'title ASC',
];
$unionOrderBy = $sortMap[$sort] ?? $sortMap['newest'];

$internalConditions = [];
$internalParams = [];
$hhConditions = [];
$hhParams = [];

if ($titleFilter !== '') {
    $internalConditions[] = 'v.title LIKE :title';
    $internalParams[':title'] = '%' . $titleFilter . '%';

    if ($hasJobsTable) {
        $hhConditions[] = 'j.title LIKE :title';
        $hhParams[':title'] = '%' . $titleFilter . '%';
    }
}

if ($companyFilter !== '') {
    $internalConditions[] = 'v.company LIKE :company';
    $internalParams[':company'] = '%' . $companyFilter . '%';

    if ($hasJobsTable) {
        $hhConditions[] = 'j.company LIKE :company';
        $hhParams[':company'] = '%' . $companyFilter . '%';
    }
}

if ($categoryFilter !== '') {
    $internalConditions[] = 'v.category_id = :category';
    $internalParams[':category'] = $categoryFilter;

    if ($hasJobsTable) {
        $hhConditions[] = '1 = 0';
    }
}

$internalWhereSql = $internalConditions ? 'WHERE ' . implode(' AND ', $internalConditions) : '';
$hhWhereSql = $hhConditions ? 'WHERE ' . implode(' AND ', $hhConditions) : '';
$hhSelectSql = '';
$hhCountSql = '';

if ($hasJobsTable) {
    $hhSelectSql = "
            UNION ALL

            SELECT
                j.id,
                CONVERT(j.external_id USING utf8mb4) COLLATE utf8mb4_unicode_ci AS external_id,
                CONVERT(j.title USING utf8mb4) COLLATE utf8mb4_unicode_ci AS title,
                CONVERT(j.company USING utf8mb4) COLLATE utf8mb4_unicode_ci AS company,
                CONVERT(j.salary USING utf8mb4) COLLATE utf8mb4_unicode_ci AS salary,
                CONVERT(j.city USING utf8mb4) COLLATE utf8mb4_unicode_ci AS city,
                CONVERT(j.description USING utf8mb4) COLLATE utf8mb4_unicode_ci AS description,
                CONVERT(j.url USING utf8mb4) COLLATE utf8mb4_unicode_ci AS url,
                CONVERT(j.source USING utf8mb4) COLLATE utf8mb4_unicode_ci AS source,
                j.created_at,
                CAST('HeadHunter' AS CHAR CHARACTER SET utf8mb4) COLLATE utf8mb4_unicode_ci AS category_name
            FROM jobs j
            {$hhWhereSql}
    ";

    $hhCountSql = "
            UNION ALL

            SELECT j.id
            FROM jobs j
            {$hhWhereSql}
    ";
}

$sql = "
    SELECT *
      FROM (
            SELECT
                v.id,
                NULL AS external_id,
                CONVERT(v.title USING utf8mb4) COLLATE utf8mb4_unicode_ci AS title,
                CONVERT(v.company USING utf8mb4) COLLATE utf8mb4_unicode_ci AS company,
                CONVERT(v.salary USING utf8mb4) COLLATE utf8mb4_unicode_ci AS salary,
                CONVERT(v.location USING utf8mb4) COLLATE utf8mb4_unicode_ci AS city,
                CONVERT(v.description USING utf8mb4) COLLATE utf8mb4_unicode_ci AS description,
                NULL AS url,
                CAST('internal' AS CHAR CHARACTER SET utf8mb4) COLLATE utf8mb4_unicode_ci AS source,
                v.created_at,
                CONVERT(COALESCE(c.name, '') USING utf8mb4) COLLATE utf8mb4_unicode_ci AS category_name
            FROM vacancies v
            LEFT JOIN categories c ON c.id = v.category_id
            {$internalWhereSql}
            {$hhSelectSql}
      ) AS combined_vacancies
  ORDER BY {$unionOrderBy}
     LIMIT :limit
    OFFSET :offset
";

$stmt = $pdo->prepare($sql);
foreach ($internalParams as $key => $value) {
    $stmt->bindValue($key, $value, PDO::PARAM_STR);
}
foreach ($hhParams as $key => $value) {
    if (!array_key_exists($key, $internalParams)) {
        $stmt->bindValue($key, $value, PDO::PARAM_STR);
    }
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$vacancies = $stmt->fetchAll(PDO::FETCH_ASSOC);

$countSql = "
    SELECT COUNT(*)
      FROM (
            SELECT v.id
            FROM vacancies v
            {$internalWhereSql}
            {$hhCountSql}
      ) AS combined_count
";

$countStmt = $pdo->prepare($countSql);
foreach ($internalParams as $key => $value) {
    $countStmt->bindValue($key, $value, PDO::PARAM_STR);
}
foreach ($hhParams as $key => $value) {
    if (!array_key_exists($key, $internalParams)) {
        $countStmt->bindValue($key, $value, PDO::PARAM_STR);
    }
}
$countStmt->execute();
$totalVacancies = (int) $countStmt->fetchColumn();
$totalPages = max(1, (int) ceil($totalVacancies / $limit));

$t = [
    'page_title' => $isKz ? 'TruWork - Вакансиялар' : 'TruWork - Вакансии',
    'home' => $isKz ? 'Басты бет' : 'Главная',
    'vacancies' => $isKz ? 'Вакансиялар' : 'Вакансии',
    'publish' => $isKz ? 'Жариялау' : 'Опубликовать',
    'about' => $isKz ? 'Біз туралы' : 'О нас',
    'faq' => 'FAQ',
    'support' => $isKz ? 'Қолдау' : 'Поддержка',
    'login' => $isKz ? 'Кіру' : 'Войти',
    'title' => $isKz ? 'Өзекті вакансияларды табу' : 'Найти актуальные вакансии',
    'subtitle' => $isKz
        ? 'Сайттағы және HeadHunter-тен жүктелген вакансияларды бір таспада қарап, сүзгілер арқылы тез табыңыз.'
        : 'Просматривайте вакансии сайта и HeadHunter в одной общей ленте с единым стилем и удобными фильтрами.',
    'hh_sync_warning' => $isKz
        ? 'HeadHunter вакансияларын жаңарту уақытша қолжетімсіз. Сайттағы вакансиялар көрсетілді.'
        : 'Временное обновление вакансий HeadHunter недоступно. Показаны вакансии сайта.',
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
    'details_hh' => $isKz ? 'Толығырақ' : 'Подробнее',
    'empty' => $isKz ? 'Сұраныс бойынша вакансия табылмады.' : 'По вашему запросу вакансии не найдены.',
    'page' => $isKz ? 'Бет' : 'Страница',
    'policy' => $isKz ? 'Құпиялық саясаты' : 'Политика конфиденциальности',
    'terms' => $isKz ? 'Пайдалану шарттары' : 'Условия использования',
    'footer_note' => $isKz
        ? 'Сайт және HeadHunter көздерінен жиналған вакансиялар таспасы.'
        : 'Единая лента вакансий из сайта и HeadHunter в общем визуальном стиле.',
    'source' => $isKz ? 'Дереккөз' : 'Источник',
    'source_internal' => $isKz ? 'Сайт' : 'Сайт',
    'source_hh' => 'HeadHunter',
    'category_none' => $isKz ? 'Санат көрсетілмеген' : 'Без категории',
    'salary_empty' => $isKz ? 'Көрсетілмеген' : 'Не указано',
    'location_empty' => $isKz ? 'Көрсетілмеген' : 'Не указано',
    'smart_search' => $isKz ? 'Ақылды іздеуге өту' : 'Перейти в умный поиск',
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
        <?php if ($hhSyncError !== null): ?>
          <p class="section-subtitle" style="margin-top:12px; color:#b42318;"><?= htmlspecialchars($t['hh_sync_warning']) ?></p>
        <?php endif; ?>
        <div class="stack-actions" style="margin-bottom:18px;">
          <a class="button-secondary" href="<?= $isKz ? 'smart_search_kk.php' : 'smart_search.php' ?>">
            <?= htmlspecialchars($t['smart_search']) ?>
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
                  <span class="vacancy-chip"><?= htmlspecialchars($t['salary']) ?>: <?= htmlspecialchars((string) ($vacancy['salary'] ?? $t['salary_empty'])) ?></span>
                  <span class="vacancy-chip"><?= htmlspecialchars($t['location']) ?>: <?= htmlspecialchars((string) ($vacancy['city'] ?? $t['location_empty'])) ?></span>
                  <span class="vacancy-chip"><?= htmlspecialchars($t['category']) ?>: <?= htmlspecialchars((string) ($vacancy['category_name'] ?? $t['category_none'])) ?></span>
                  <span class="vacancy-chip"><?= htmlspecialchars($t['source']) ?>: <?= htmlspecialchars(($vacancy['source'] ?? 'internal') === 'hh' ? $t['source_hh'] : $t['source_internal']) ?></span>
                </div>
                <p class="muted" style="margin:0;">
                  <?= htmlspecialchars(mb_strlen((string) ($vacancy['description'] ?? '')) > 260 ? mb_substr((string) $vacancy['description'], 0, 260) . '...' : (string) ($vacancy['description'] ?? '')) ?>
                </p>
              </div>
              <div class="dashboard-hero__actions">
                <a
                  class="button-primary"
                  href="<?= htmlspecialchars(vacancy_detail_link($vacancy)) ?>"
                ><?= htmlspecialchars(($vacancy['source'] ?? 'internal') === 'hh' ? $t['details_hh'] : $t['details']) ?></a>
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
