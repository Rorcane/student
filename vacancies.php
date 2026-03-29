<?php
require_once 'config.php';
/**
 * Возвращает правильную форму слова для числительных по-русски
 * @param int $n — число
 * @param array $forms — массив из трёх форм: ['человек','человека','человек']
 * @return string
 */
function plural_form(int $n, array $forms): string {
    $n = abs($n) % 100;
    $n1 = $n % 10;
    if ($n > 10 && $n < 20) return $forms[2];
    if ($n1 > 1 && $n1 < 5) return $forms[1];
    if ($n1 == 1) return $forms[0];
    return $forms[2];
}

// Получаем фильтры из GET-запроса
$title_filter    = isset($_GET['title'])    ? trim($_GET['title'])    : '';
$company_filter  = isset($_GET['company'])  ? trim($_GET['company'])  : '';

// Поддержка category (single) и category[] (multiple)
$selectedCategories = [];
if (isset($_GET['category'])) {
    if (is_array($_GET['category'])) {
        $selectedCategories = array_map('trim', $_GET['category']);
    } else {
        $selectedCategories = [ trim($_GET['category']) ];
    }
}
// Иногда JS может send category[] as 'category[]' key — обработаем на всякий случай
if (empty($selectedCategories) && isset($_GET['category[]']) && is_array($_GET['category[]'])) {
    $selectedCategories = array_map('trim', $_GET['category[]']);
}

// Нормализуем: если единственная категория — и это 'Все категории' — считаем как пустой выбор
$selectedCategories = array_filter($selectedCategories, function($c){ return $c !== '' && $c !== null; });
if (count($selectedCategories) === 1 && $selectedCategories[0] === 'Все категории') {
    $selectedCategories = [];
}

// Параметры пагинации
$limit  = 15;
$page   = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Получаем список категорий динамически:
$categories = ['Все категории'];

try {
    $catStmt = $pdo->query("SELECT id, name FROM categories ORDER BY name");
    $categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);
    // $catRows = $catStmt->fetchAll(PDO::FETCH_COLUMN);
    // if (!empty($catRows)) {
    //     foreach ($catRows as $c) $categories[] = $c;
    // } else {
    //     $vacCatStmt = $pdo->query("SELECT DISTINCT IFNULL(NULLIF(TRIM(category),''),'Другое') AS cname FROM vacancies ORDER BY cname");
    //     $vacCats = $vacCatStmt->fetchAll(PDO::FETCH_COLUMN);
    //     foreach ($vacCats as $c) {
    //         if ($c === '' || $c === null) $c = 'Другое';
    //         if (!in_array($c, $categories, true)) $categories[] = $c;
    //     }
    // }
} catch (PDOException $e) {
    try {
        $vacCatStmt = $pdo->query("SELECT DISTINCT IFNULL(NULLIF(TRIM(category),''),'Другое') AS cname FROM vacancies ORDER BY cname");
        $vacCats = $vacCatStmt->fetchAll(PDO::FETCH_COLUMN);
        foreach ($vacCats as $c) {
            if ($c === '' || $c === null) $c = 'Другое';
            if (!in_array($c, $categories, true)) $categories[] = $c;
        }
    } catch (PDOException $e2) {
        $categories = ['Все категории', 'Продажи', 'Маркетинг', 'IT', 'Логистика', 'Поддержка', 'Технологии', 'Другое'];
    }
}


// Построение основного SQL-запроса
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
    $params[':title'] = "%{$title_filter}%";
}
if ($company_filter !== '') {
    $sql .= " AND v.company LIKE :company";
    $params[':company'] = "%{$company_filter}%";
}
// Категории: поддержка множественного выбора
if (!empty($selectedCategories)) {
    // Если одна категория — простое сравнение
    if (count($selectedCategories) === 1) {
        $sql .= " AND v.category_id = :category";
        $params[':category'] = $selectedCategories[0];
    } else {
        // IN (...) — создаём плейсхолдеры
        $inPlaceholders = [];
        foreach ($selectedCategories as $i => $cat) {
            $ph = ':cat' . $i;
            $inPlaceholders[] = $ph;
            $params[$ph] = $cat;
        }
        $sql .= " AND v.category_id IN (" . implode(',', $inPlaceholders) . ")";
    }
}
// Добавляем LIMIT/OFFSET
$sql .= " GROUP BY v.id ORDER BY v.created_at DESC LIMIT {$limit} OFFSET {$offset}";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$vacancies = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Подсчёт общего количества вакансий для пагинации
$countSql = "
SELECT COUNT(*)
FROM vacancies v
LEFT JOIN categories c ON v.category_id = c.id
WHERE 1=1
";

$countParams = [];
if ($title_filter !== '') {
    $countSql .= " AND v.title LIKE :title";
    $countParams[':title'] = "%{$title_filter}%";
}
if ($company_filter !== '') {
    $countSql .= " AND v.company LIKE :company";
    $countParams[':company'] = "%{$company_filter}%";
}
if (!empty($selectedCategories)) {
    if (count($selectedCategories) === 1) {
        $countSql .= " AND v.category_id = :category";
        $countParams[':category'] = $selectedCategories[0];
    } else {
        $inPlaceholders = [];
        foreach ($selectedCategories as $i => $cat) {
            $ph = ':catc' . $i;
            $inPlaceholders[] = $ph;
            $countParams[$ph] = $cat;
        }
        $countSql .= " AND v.category_id IN (" . implode(',', $inPlaceholders) . ")";
    }
}
$countStmt = $pdo->prepare($countSql);
$countStmt->execute($countParams);
$totalVacancies = (int)$countStmt->fetchColumn();
$totalPages = (int)ceil($totalVacancies / $limit);

// Получение роли пользователя (если авторизован)
$user = null;
if (isset($_COOKIE['user'])) {
    $uStmt = $pdo->prepare("SELECT role FROM users WHERE username = ?");
    $uStmt->execute([$_COOKIE['user']]);
    $user = $uStmt->fetch(PDO::FETCH_ASSOC);
}

// Для отображения выбранных категорий (UI)
$selectedCatsForUI = $selectedCategories;

error_reporting(E_ALL);
ini_set('display_errors', 1);

?>
<!DOCTYPE html>
<html lang="ru">
<head>
	<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-QTCZG5LGVP"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-QTCZG5LGVP');
</script>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>TruWork</title>
  <link rel="icon" type="image/png" href="/favicon-96x96.png" sizes="96x96" />
  <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
  <link rel="shortcut icon" href="/favicon.ico" />
  <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png" />
  <link rel="manifest" href="/site.webmanifest" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root { --primary: #2563eb; --secondary: #3b82f6; --accent: #60a5fa; --gradient: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);}    
    body { padding-top: 35px; background: #f8f9fa; font-family: 'Inter', sans-serif; }
    .navbar { box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);}    
    .nav-link { position: relative; padding: 0.5rem 1rem !important; color: #495057 !important; }
    .nav-link.active { color: var(--primary) !important; }
    .nav-link-border { position: absolute; bottom: 0; left: 0; width: 0; height: 2px; background: var(--primary); transition: width 0.3s ease; }
    .nav-link:hover .nav-link-border, .nav-link.active .nav-link-border { width: 100%; }
  </style>
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-light bg-white fixed-top border-bottom">
    <div class="container">
      <a class="navbar-brand fw-bold d-flex align-items-center" href="index.php">
        <img src="logo2.png" alt="TruWork" width="95" class="me-2">
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navMenu">
        <ul class="navbar-nav ms-auto align-items-lg-center">
          <li class="nav-item"><a class="nav-link active" href="vacancies.php"><span class="nav-link-border"></span>Найти работу</a></li>
          <li class="nav-item"><a class="nav-link" href="vacancy.php"><span class="nav-link-border"></span>Опубликовать</a></li>
          <li class="nav-item"><a class="nav-link" href="dashboard.php"><span class="nav-link-border"></span>Панель</a></li>
          <li class="nav-item"><a class="nav-link" href="discovery.php"><span class="nav-link-border"></span>Обзор</a></li>
        </ul>
        <?php if(isset($_COOKIE['user'])): ?>
          <a href="profile.php" class="btn btn-outline-primary ms-3"><?= htmlspecialchars($_COOKIE['user']) ?></a>
        <?php else: ?>
          <a href="login.html" class="btn btn-primary ms-3">Войти</a>
        <?php endif; ?>
      </div>
    </div>
  </nav>
  <main class="container py-5">
    <h1 class="mb-4 fw-bold">Умный поиск</h1>
    <!-- Форма фильтрации -->
    <form method="get" class="row g-3 mb-4">
      <div class="col-md-3">
        <input type="text" name="title" class="form-control" placeholder="Название должности" value="<?= htmlspecialchars($title_filter) ?>">
      </div>
      <div class="col-md-3">
        <input type="text" name="company" class="form-control" placeholder="Компания" value="<?= htmlspecialchars($company_filter) ?>">
      </div>
      <div class="col-md-3">
        <!-- Оставляем селект для обратной совместимости -->
<select name="category" class="form-select">
    <option value="">Все категории</option>
    <?php foreach ($categories as $cat): ?>
        <option value="<?= $cat['id'] ?>"
            <?= in_array($cat['id'], $selectedCategories) ? 'selected' : '' ?>>
            <?= htmlspecialchars($cat['name']) ?>
        </option>
    <?php endforeach; ?>
</select>
      </div>
      <div class="col-md-3">
        <button type="submit" class="btn btn-primary w-100">Фильтровать</button>
      </div>
    </form>
	  
    <!-- Блок: кнопка "Категории" и модал (вариант B) -->
    <div class="mb-4">
      <div class="d-flex align-items-center gap-3">
        <div>
          <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#catsModal">
            Категории
          </button>
        </div>
        <div id="selectedCats" class="d-inline-block">
          <?php if (!empty($selectedCatsForUI)): ?>
          <?php 
          $catNamesById = [];
          foreach ($categories as $c) {
              $catNamesById[$c['id']] = $c['name'];
          }
          ?>
          <?php foreach ($selectedCatsForUI as $sc): ?>
            <span class="badge bg-light text-dark border me-1">
              <?= htmlspecialchars($catNamesById[$sc] ?? $sc) ?>
            </span>
          <?php endforeach; ?>
          <?php else: ?>
            <span class="text-muted small">Все категории</span>
          <?php endif; ?>
        </div>

        <?php if ($user && $user['role'] === 'admin'): ?>
        <form action="smart_search.php" method="get" style="display:inline-block; margin-left:auto;">
          <button type="submit" class="btn btn-primary">
            Умный поиск
          </button>
        </form>
        <?php endif; ?>
      </div>
    </div>

    <!-- Модал с поиском и чекбоксами -->
    <div class="modal fade" id="catsModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <form id="catsForm" method="get" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>">
            <div class="modal-header">
              <h5 class="modal-title">Выберите категории</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <div class="modal-body">
              <input id="catSearch" class="form-control mb-3" placeholder="Поиск категории..." type="search" autocomplete="off">
              <div id="catsList" class="row" style="max-height:55vh; overflow:auto;">
<?php foreach ($categories as $cat): ?>
  <div class="col-6 col-md-4 mb-2">
    <div class="form-check">
      <input class="form-check-input cat-checkbox"
             type="checkbox"
             value="<?= $cat['id'] ?>"
             id="cat_<?= $cat['id'] ?>"
             <?= in_array($cat['id'], $selectedCategories) ? 'checked' : '' ?>>
      <label class="form-check-label"
             for="cat_<?= $cat['id'] ?>">
        <?= htmlspecialchars($cat['name']) ?>
      </label>
    </div>
  </div>
<?php endforeach; ?>

              </div>
            </div>
            <div class="modal-footer">
              <button type="button" id="clearCatsBtn" class="btn btn-outline-secondary">Очистить</button>
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
              <button type="submit" id="applyCatsBtn" class="btn btn-primary">Применить</button>
            </div>
          </form>
        </div>
      </div>
    </div>
	  
    <p class="text-muted">Найдено <?= $totalVacancies ?> вакансий</p>

	
    <?php if ($vacancies): ?>
      <?php foreach ($vacancies as $vacancy): ?>
	    <?php $author = urlencode($vacancy['author']); ?>
        <div class="card mb-3 shadow-sm">
          <div class="card-body d-flex flex-column flex-sm-row align-items-start justify-content-between">
            <div>
              <h5 class="mb-1 fw-bold"><?= htmlspecialchars($vacancy['title'] ?? 'Без названия') ?></h5>
				<p><strong>Компания:</strong> <?= htmlspecialchars($vacancy['company']) ?></p>
              <p class="mb-1"><strong>Зарплата:</strong> <?= htmlspecialchars($vacancy['salary'] ?? '-') ?></p>
<p class="mb-1"><strong>Категория:</strong> <?= htmlspecialchars($vacancy['category_name'] ?? '-') ?></p>
				
              <?php if ($user && $user['role'] === 'admin'): ?>
                <a href="delete_vacancy.php?id=<?= $vacancy['id'] ?>" class="btn btn-danger btn-sm mt-2" onclick="return confirm('Вы уверены?');">Удалить</a>
              <?php endif; ?>
            </div>
            <div>
              <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#applyModal-<?= $vacancy['id'] ?>" data-bs-id="<?= $vacancy['id'] ?>" data-bs-title="<?= htmlspecialchars($vacancy['title'], ENT_QUOTES) ?>">Развернуть</button>
              <div class="modal fade" id="applyModal-<?= $vacancy['id'] ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                  <form action="process_application.php" method="POST" class="modal-content">
                    <input type="hidden" name="vacancy_id" value="<?= $vacancy['id'] ?>">
                    <div class="modal-header">
                      <h5 class="modal-title">Развернуть</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                   <div class="modal-body">
  					<p>Откликнуться на вакансию «<strong><?= htmlspecialchars($vacancy['title']) ?></strong>»?</p>
  					<hr>
  					<p><strong>Описание вакансии:</strong></p>
  					<p><?= nl2br(htmlspecialchars($vacancy['description'])) ?></p>
  					<p><strong>Компания:</strong> <?= htmlspecialchars($vacancy['company']) ?></p>
  					<p><strong>Местоположение:</strong> <?= htmlspecialchars($vacancy['location']) ?></p>
					</div>
                    <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
					<!--<a href="chat.php?with=<?= $author ?>" class="btn btn-primary">Откликнуться</a>-->
					<button type="submit" class="btn btn-primary">Откликнуться</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>

      <!-- Пагинация -->
      <?php if ($totalPages > 1): ?>
        <nav><ul class="pagination justify-content-center">
          <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
              <a class="page-link" href="?page=<?= $i ?><?= $title_filter? '&title='.urlencode($title_filter): '' ?><?= $company_filter? '&company='.urlencode($company_filter): '' ?><?php
                // передаём выбранные категории в пагинации
                if (!empty($selectedCatsForUI)) {
                    foreach ($selectedCatsForUI as $sc) {
                        echo '&' . (count($selectedCatsForUI) > 1 ? 'category[]=' . urlencode($sc) : 'category=' . urlencode($sc));
                    }
                }
              ?>"><?= $i ?></a>
            </li>
          <?php endfor; ?>
        </ul></nav>
      <?php endif; ?>
    <?php else: ?>
      <p>Вакансии не найдены.</p>
    <?php endif; ?>
  </main>
  <footer>
  <div class="container text-center">  <!-- добавили text-center -->
    <div class="row justify-content-center g-4">  <!-- центрируем колонки -->
      <div class="col-md-4">
        <img src="logo2.png" alt="TruWork" height="18">
        <p class="mt-3 text-muted">Инновационная HR-платформа нового поколения</p>
      </div>
      <div class="col-md-2">
        <h6>Компания</h6>
        <ul class="list-unstyled">
          <li><a href="about.html">О нас</a></li>
          <li><a href="https://www.instagram.com/truwork_official?igsh=MWlyaDVkaWQyZXFjYg%3D%3D&utm_source=qr" target="_blank" rel="noopener noreferrer">Блог</a></li>
        </ul>
      </div>
      <div class="col-md-2">
        <h6>Помощь</h6>
        <ul class="list-unstyled">
          <li><a href="support.html">Поддержка</a></li>
          <li><a href="faq.html">FAQ</a></li>
        </ul>
      </div>
    </div>

    <hr class="my-5">

    <div class="d-flex flex-column flex-md-row justify-content-center align-items-center text-muted"> 
      <!-- flex-column на мобильных, flex-row на десктопе -->
      <div class="mb-2 mb-md-0">© 2025 Truwork.kz. Все права защищены</div>
      <div class="ms-md-4">
        <a href="policy.html" class="me-3">Политика конфиденциальности</a>
        <a href="terms.html">Условия использования</a>
      </div>
    </div>
  </div>
</footer>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
(function(){
  const search = document.getElementById('catSearch');
  const checkboxesSelector = '.cat-checkbox';
  function getCheckboxes() { return Array.from(document.querySelectorAll(checkboxesSelector)); }
  const form = document.getElementById('catsForm');

  // Фильтрация списка по вводу
  search && search.addEventListener('input', function(){
    const q = this.value.trim().toLowerCase();
    getCheckboxes().forEach(ch => {
      const label = ch.nextElementSibling.textContent.toLowerCase();
      ch.closest('.col-6').style.display = label.includes(q) ? '' : 'none';
    });
  });

  // При сабмите: формируем URL, сохраняя остальные GET-параметры
  form && form.addEventListener('submit', function(e){
    e.preventDefault();
    const selected = getCheckboxes().filter(c => c.checked).map(c => c.value);

    // Подготовим параметры текущего URL (сохраним title/company/page и т.д.)
    const params = new URLSearchParams(window.location.search);

    // Удаляем старые категории (и category[])
    for (const key of Array.from(params.keys())) {
      if (key === 'category' || key === 'category[]' || key.startsWith('category')) {
        params.delete(key);
      }
    }

    if (selected.length === 0) {
      // ничего — оставим без category (будет означать "все")
      // либо можно явно установить 'Все категории'
    } else if (selected.length === 1) {
      params.set('category', selected[0]);
    } else {
      selected.forEach(s => params.append('category[]', s));
    }

    // Перенаправление (путь + параметры)
    const target = window.location.pathname + '?' + params.toString();
    window.location = target;
  });

  // Кнопка очистки — снимает все чекбоксы
  document.getElementById('clearCatsBtn')?.addEventListener('click', function(){
    getCheckboxes().forEach(c => c.checked = false);
  });
})();
</script>
</body>
</html>
