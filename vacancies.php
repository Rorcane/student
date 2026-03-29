<?php
require_once 'config.php';

function plural_form(int $n, array $forms): string {
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

$title_filter = isset($_GET['title']) ? trim((string) $_GET['title']) : '';
$company_filter = isset($_GET['company']) ? trim((string) $_GET['company']) : '';

$selectedCategories = [];
if (isset($_GET['category'])) {
    if (is_array($_GET['category'])) {
        $selectedCategories = array_map('trim', $_GET['category']);
    } else {
        $selectedCategories = [trim((string) $_GET['category'])];
    }
}
if (empty($selectedCategories) && isset($_GET['category[]']) && is_array($_GET['category[]'])) {
    $selectedCategories = array_map('trim', $_GET['category[]']);
}

$selectedCategories = array_values(array_filter($selectedCategories, static function ($value) {
    return $value !== '' && $value !== null;
}));

$limit = 15;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$categories = [];
try {
    $catStmt = $pdo->query("SELECT id, name FROM categories ORDER BY name");
    $categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $categories = [];
}

$params = [];
$sql = "
    SELECT v.*, c.name AS category_name, COUNT(a.id) AS responses
      FROM vacancies v
 LEFT JOIN categories c ON v.category_id = c.id
 LEFT JOIN applications a ON a.vacancy_id = v.id
     WHERE 1=1
";

if ($title_filter !== '') {
    $sql .= " AND v.title LIKE :title";
    $params[':title'] = '%' . $title_filter . '%';
}
if ($company_filter !== '') {
    $sql .= " AND v.company LIKE :company";
    $params[':company'] = '%' . $company_filter . '%';
}
if (!empty($selectedCategories)) {
    if (count($selectedCategories) === 1) {
        $sql .= " AND v.category_id = :category";
        $params[':category'] = $selectedCategories[0];
    } else {
        $holders = [];
        foreach ($selectedCategories as $i => $cat) {
            $ph = ':cat' . $i;
            $holders[] = $ph;
            $params[$ph] = $cat;
        }
        $sql .= ' AND v.category_id IN (' . implode(',', $holders) . ')';
    }
}

$sql .= " GROUP BY v.id ORDER BY v.created_at DESC LIMIT {$limit} OFFSET {$offset}";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$vacancies = $stmt->fetchAll(PDO::FETCH_ASSOC);

$countSql = "
    SELECT COUNT(*)
      FROM vacancies v
 LEFT JOIN categories c ON v.category_id = c.id
     WHERE 1=1
";
$countParams = [];
if ($title_filter !== '') {
    $countSql .= " AND v.title LIKE :title";
    $countParams[':title'] = '%' . $title_filter . '%';
}
if ($company_filter !== '') {
    $countSql .= " AND v.company LIKE :company";
    $countParams[':company'] = '%' . $company_filter . '%';
}
if (!empty($selectedCategories)) {
    if (count($selectedCategories) === 1) {
        $countSql .= " AND v.category_id = :category";
        $countParams[':category'] = $selectedCategories[0];
    } else {
        $holders = [];
        foreach ($selectedCategories as $i => $cat) {
            $ph = ':countcat' . $i;
            $holders[] = $ph;
            $countParams[$ph] = $cat;
        }
        $countSql .= ' AND v.category_id IN (' . implode(',', $holders) . ')';
    }
}
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($countParams);
$totalVacancies = (int) $countStmt->fetchColumn();
$totalPages = (int) ceil($totalVacancies / $limit);

$user = null;
if (isset($_COOKIE['user'])) {
    $uStmt = $pdo->prepare("SELECT role FROM users WHERE username = ?");
    $uStmt->execute([$_COOKIE['user']]);
    $user = $uStmt->fetch(PDO::FETCH_ASSOC);
}

$categoryNames = [];
foreach ($categories as $categoryRow) {
    $categoryNames[(string) $categoryRow['id']] = $categoryRow['name'];
}

$t = [
    'page_title' => $isKz ? 'TruWork - вакансиялар' : 'TruWork - вакансии',
    'home' => $isKz ? 'Басты бет' : 'Главная',
    'vacancies' => $isKz ? 'Вакансиялар' : 'Вакансии',
    'about' => $isKz ? 'Біз туралы' : 'О нас',
    'faq' => 'FAQ',
    'support' => $isKz ? 'Қолдау' : 'Поддержка',
    'contact' => $isKz ? 'Байланыс' : 'Контакты',
    'login' => $isKz ? 'Кіру' : 'Войти',
    'title' => $isKz ? 'Вакансияларды табу' : 'Найти вакансию',
    'subtitle' => $isKz ? 'Іздеу мен сүзгілерді пайдаланып, өзіңізге лайық ұсынысты табыңыз.' : 'Используйте поиск и фильтры, чтобы быстро найти подходящее предложение.',
    'title_placeholder' => $isKz ? 'Лауазым атауы' : 'Название должности',
    'company_placeholder' => $isKz ? 'Компания' : 'Компания',
    'all_categories' => $isKz ? 'Барлық санаттар' : 'Все категории',
    'filter' => $isKz ? 'Сүзу' : 'Фильтровать',
    'categories' => $isKz ? 'Санаттар' : 'Категории',
    'search_categories' => $isKz ? 'Санатты іздеу...' : 'Поиск категории...',
    'choose_categories' => $isKz ? 'Санаттарды таңдаңыз' : 'Выберите категории',
    'clear' => $isKz ? 'Тазарту' : 'Очистить',
    'cancel' => $isKz ? 'Бас тарту' : 'Отмена',
    'apply' => $isKz ? 'Қолдану' : 'Применить',
    'smart_search' => $isKz ? 'Ақылды іздеу' : 'Умный поиск',
    'found' => $isKz ? 'Табылды' : 'Найдено',
    'vacancy_forms' => $isKz ? ['вакансия', 'вакансия', 'вакансия'] : ['вакансия', 'вакансии', 'вакансий'],
    'company' => $isKz ? 'Компания' : 'Компания',
    'salary' => $isKz ? 'Жалақы' : 'Зарплата',
    'category' => $isKz ? 'Санат' : 'Категория',
    'location' => $isKz ? 'Орналасқан жері' : 'Местоположение',
    'details' => $isKz ? 'Толығырақ' : 'Подробнее',
    'delete' => $isKz ? 'Жою' : 'Удалить',
    'confirm_delete' => $isKz ? 'Осы вакансияны шынымен жойғыңыз келе ме?' : 'Вы уверены, что хотите удалить вакансию?',
    'respond' => $isKz ? 'Өтінім жіберу' : 'Откликнуться',
    'respond_question' => $isKz ? '«%s» вакансиясына өтінім жібересіз бе?' : 'Откликнуться на вакансию «%s»?',
    'description' => $isKz ? 'Сипаттама' : 'Описание',
    'empty' => $isKz ? 'Вакансиялар табылмады.' : 'Вакансии не найдены.',
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
    'alt' => $isKz ? 'vacancies.php' : 'vacancies_kk.php',
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
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/public-site.css">
</head>
<body>
  <header class="site-header">
    <div class="site-shell site-header__inner">
      <a class="brand" href="<?= htmlspecialchars($paths['index']) ?>"><img src="logo2.png" alt="TruWork"></a>
      <nav class="site-nav" aria-label="<?= $isKz ? 'Негізгі навигация' : 'Основная навигация' ?>">
        <a href="<?= htmlspecialchars($paths['index']) ?>"><?= htmlspecialchars($t['home']) ?></a>
        <a href="<?= htmlspecialchars($paths['vacancies']) ?>" class="is-active"><?= htmlspecialchars($t['vacancies']) ?></a>
        <a href="<?= htmlspecialchars($paths['about']) ?>"><?= htmlspecialchars($t['about']) ?></a>
        <a href="<?= htmlspecialchars($paths['faq']) ?>"><?= htmlspecialchars($t['faq']) ?></a>
        <a href="<?= htmlspecialchars($paths['support']) ?>"><?= htmlspecialchars($t['support']) ?></a>
        <a href="<?= htmlspecialchars($paths['contact']) ?>"><?= htmlspecialchars($t['contact']) ?></a>
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
          <a class="button-primary" href="profile.php"><?= htmlspecialchars($_COOKIE['user']) ?></a>
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

        <form method="get" class="form-grid" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>">
          <div>
            <label class="label" for="filter-title"><?= htmlspecialchars($t['title_placeholder']) ?></label>
            <input class="input" id="filter-title" type="text" name="title" value="<?= htmlspecialchars($title_filter) ?>" placeholder="<?= htmlspecialchars($t['title_placeholder']) ?>">
          </div>
          <div>
            <label class="label" for="filter-company"><?= htmlspecialchars($t['company_placeholder']) ?></label>
            <input class="input" id="filter-company" type="text" name="company" value="<?= htmlspecialchars($company_filter) ?>" placeholder="<?= htmlspecialchars($t['company_placeholder']) ?>">
          </div>
          <div>
            <label class="label" for="filter-category"><?= htmlspecialchars($t['category']) ?></label>
            <select class="input" id="filter-category" name="category">
              <option value=""><?= htmlspecialchars($t['all_categories']) ?></option>
              <?php foreach ($categories as $category): ?>
                <option value="<?= htmlspecialchars((string) $category['id']) ?>" <?= in_array((string) $category['id'], $selectedCategories, true) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($category['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div style="display:flex;align-items:flex-end;gap:12px;flex-wrap:wrap;">
            <button class="button-primary" type="submit"><?= htmlspecialchars($t['filter']) ?></button>
            <button class="button-secondary" type="button" data-bs-toggle="modal" data-bs-target="#catsModal"><?= htmlspecialchars($t['categories']) ?></button>
            <?php if ($user && ($user['role'] ?? '') === 'admin'): ?>
              <a class="button-secondary" href="smart_search.php"><?= htmlspecialchars($t['smart_search']) ?></a>
            <?php endif; ?>
          </div>
        </form>

        <div style="margin-top:22px;">
          <?php if (!empty($selectedCategories)): ?>
            <?php foreach ($selectedCategories as $selectedCategory): ?>
              <span class="button-secondary" style="padding:8px 14px;margin:0 8px 8px 0;cursor:default;">
                <?= htmlspecialchars($categoryNames[(string) $selectedCategory] ?? (string) $selectedCategory) ?>
              </span>
            <?php endforeach; ?>
          <?php else: ?>
            <span class="muted"><?= htmlspecialchars($t['all_categories']) ?></span>
          <?php endif; ?>
        </div>
      </section>

      <section class="page-card">
        <p class="section-subtitle" style="margin-bottom:0;">
          <?= htmlspecialchars($t['found']) ?> <?= $totalVacancies ?> <?= htmlspecialchars(plural_form($totalVacancies, $t['vacancy_forms'])) ?>
        </p>
      </section>

      <?php if ($vacancies): ?>
        <?php foreach ($vacancies as $vacancy): ?>
          <section class="page-card">
            <div class="grid grid-2" style="align-items:start;">
              <div>
                <h2 style="margin-bottom:14px;"><?= htmlspecialchars($vacancy['title'] ?? ($isKz ? 'Атаусыз' : 'Без названия')) ?></h2>
                <p><strong><?= htmlspecialchars($t['company']) ?>:</strong> <?= htmlspecialchars((string) ($vacancy['company'] ?? '-')) ?></p>
                <p><strong><?= htmlspecialchars($t['salary']) ?>:</strong> <?= htmlspecialchars((string) ($vacancy['salary'] ?? '-')) ?></p>
                <p><strong><?= htmlspecialchars($t['category']) ?>:</strong> <?= htmlspecialchars((string) ($vacancy['category_name'] ?? '-')) ?></p>
                <p><strong><?= htmlspecialchars($t['location']) ?>:</strong> <?= htmlspecialchars((string) ($vacancy['location'] ?? '-')) ?></p>
              </div>
              <div style="display:flex;justify-content:flex-end;align-items:flex-start;gap:12px;flex-wrap:wrap;">
                <button class="button-primary" type="button" data-bs-toggle="modal" data-bs-target="#applyModal-<?= (int) $vacancy['id'] ?>">
                  <?= htmlspecialchars($t['details']) ?>
                </button>
                <?php if ($user && ($user['role'] ?? '') === 'admin'): ?>
                  <a class="button-secondary" href="delete_vacancy.php?id=<?= (int) $vacancy['id'] ?>" onclick="return confirm('<?= htmlspecialchars($t['confirm_delete'], ENT_QUOTES) ?>');">
                    <?= htmlspecialchars($t['delete']) ?>
                  </a>
                <?php endif; ?>
              </div>
            </div>
          </section>

          <div class="modal fade" id="applyModal-<?= (int) $vacancy['id'] ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
              <form action="process_application.php" method="post" class="modal-content">
                <input type="hidden" name="vacancy_id" value="<?= (int) $vacancy['id'] ?>">
                <div class="modal-header">
                  <h5 class="modal-title"><?= htmlspecialchars($t['details']) ?></h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= htmlspecialchars($t['cancel']) ?>"></button>
                </div>
                <div class="modal-body">
                  <p><?= htmlspecialchars(sprintf($t['respond_question'], (string) ($vacancy['title'] ?? ''))) ?></p>
                  <hr>
                  <p><strong><?= htmlspecialchars($t['description']) ?>:</strong></p>
                  <p><?= nl2br(htmlspecialchars((string) ($vacancy['description'] ?? ''))) ?></p>
                  <p><strong><?= htmlspecialchars($t['company']) ?>:</strong> <?= htmlspecialchars((string) ($vacancy['company'] ?? '-')) ?></p>
                  <p><strong><?= htmlspecialchars($t['location']) ?>:</strong> <?= htmlspecialchars((string) ($vacancy['location'] ?? '-')) ?></p>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= htmlspecialchars($t['cancel']) ?></button>
                  <button type="submit" class="btn btn-primary"><?= htmlspecialchars($t['respond']) ?></button>
                </div>
              </form>
            </div>
          </div>
        <?php endforeach; ?>

        <?php if ($totalPages > 1): ?>
          <section class="page-card">
            <nav aria-label="pagination">
              <ul class="pagination justify-content-center mb-0">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                  <?php
                  $queryParams = ['page' => $i];
                  if ($title_filter !== '') {
                      $queryParams['title'] = $title_filter;
                  }
                  if ($company_filter !== '') {
                      $queryParams['company'] = $company_filter;
                  }
                  if (!empty($selectedCategories)) {
                      $queryParams['category'] = $selectedCategories;
                  }
                  ?>
                  <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                    <a class="page-link" href="?<?= htmlspecialchars(http_build_query($queryParams)) ?>"><?= $i ?></a>
                  </li>
                <?php endfor; ?>
              </ul>
            </nav>
          </section>
        <?php endif; ?>
      <?php else: ?>
        <section class="page-card">
          <p style="margin:0;"><?= htmlspecialchars($t['empty']) ?></p>
        </section>
      <?php endif; ?>
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

  <div class="modal fade" id="catsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <form id="catsForm" method="get" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>">
          <div class="modal-header">
            <h5 class="modal-title"><?= htmlspecialchars($t['choose_categories']) ?></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= htmlspecialchars($t['cancel']) ?>"></button>
          </div>
          <div class="modal-body">
            <?php if ($title_filter !== ''): ?><input type="hidden" name="title" value="<?= htmlspecialchars($title_filter) ?>"><?php endif; ?>
            <?php if ($company_filter !== ''): ?><input type="hidden" name="company" value="<?= htmlspecialchars($company_filter) ?>"><?php endif; ?>
            <input id="catSearch" class="form-control mb-3" type="search" autocomplete="off" placeholder="<?= htmlspecialchars($t['search_categories']) ?>">
            <div id="catsList" class="row" style="max-height:55vh;overflow:auto;">
              <?php foreach ($categories as $category): ?>
                <div class="col-6 col-md-4 mb-2 cat-item">
                  <div class="form-check">
                    <input class="form-check-input cat-checkbox" type="checkbox" name="category[]" value="<?= htmlspecialchars((string) $category['id']) ?>" id="cat_<?= (int) $category['id'] ?>" <?= in_array((string) $category['id'], $selectedCategories, true) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="cat_<?= (int) $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></label>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" id="clearCatsBtn" class="btn btn-outline-secondary"><?= htmlspecialchars($t['clear']) ?></button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= htmlspecialchars($t['cancel']) ?></button>
            <button type="submit" class="btn btn-primary"><?= htmlspecialchars($t['apply']) ?></button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    (function () {
      const search = document.getElementById('catSearch');
      const clearBtn = document.getElementById('clearCatsBtn');
      const checkboxes = () => Array.from(document.querySelectorAll('.cat-checkbox'));

      if (search) {
        search.addEventListener('input', function () {
          const query = this.value.trim().toLowerCase();
          document.querySelectorAll('.cat-item').forEach(function (item) {
            const label = item.textContent.toLowerCase();
            item.style.display = label.includes(query) ? '' : 'none';
          });
        });
      }

      if (clearBtn) {
        clearBtn.addEventListener('click', function () {
          checkboxes().forEach(function (checkbox) {
            checkbox.checked = false;
          });
        });
      }
    }());
  </script>
</body>
</html>
