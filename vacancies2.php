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
$category_filter = isset($_GET['category']) ? trim($_GET['category']) : '';

// Параметры пагинации
$limit  = 15;
$page   = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$categories = ['Все категории', 'Продажи', 'Маркетинг', 'IT', 'Логистика', 'Поддержка', 'Технологии', 'Другое'];

// Построение основного SQL-запроса
$params = [];
$sql = "
    SELECT v.*, COUNT(a.id) AS responses
      FROM vacancies v
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
if ($category_filter !== '' && $category_filter !== 'Все категории') {
    $sql .= " AND v.category = :category";
    $params[':category'] = $category_filter;
}
// Добавляем LIMIT/OFFSET
$sql .= " GROUP BY v.id ORDER BY v.created_at DESC LIMIT {$limit} OFFSET {$offset}";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$vacancies = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Подсчёт общего количества вакансий для пагинации
$countSql = "SELECT COUNT(*) FROM vacancies v WHERE 1=1";
$countParams = [];
if ($title_filter !== '') {
    $countSql .= " AND v.title LIKE :title";
    $countParams[':title'] = "%{$title_filter}%";
}
if ($company_filter !== '') {
    $countSql .= " AND v.company LIKE :company";
    $countParams[':company'] = "%{$company_filter}%";
}
if ($category_filter !== '' && $category_filter !== 'Все категории') {
    $countSql .= " AND v.category = :category";
    $countParams[':category'] = $category_filter;
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
        <select name="category" class="form-select">
          <?php foreach ($categories as $cat): ?>
            <option value="<?= $cat ?>" <?= $category_filter === $cat ? 'selected' : '' ?>><?= $cat ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <button type="submit" class="btn btn-primary w-100">Фильтровать</button>
      </div>
    </form>

    <div class="mb-4 d-flex flex-wrap gap-2">
      <?php foreach ($categories as $cat): ?>
        <a href="?category=<?= urlencode($cat) ?>" class="btn btn-outline-secondary btn-sm <?= $category_filter === $cat ? 'active' : '' ?>"><?= $cat ?></a>
      <?php endforeach; ?>
    </div>

    <p class="text-muted">Найдено <?= $totalVacancies ?> вакансий</p>

    <?php if ($vacancies): ?>
      <?php foreach ($vacancies as $vacancy): ?>
        <div class="card mb-3 shadow-sm">
          <div class="card-body d-flex flex-column flex-sm-row align-items-start justify-content-between">
            <div>
              <h5 class="mb-1 fw-bold"><?= htmlspecialchars($vacancy['title'] ?? 'Без названия') ?></h5>
				<p><strong>Компания:</strong> <?= htmlspecialchars($vacancy['company']) ?></p>
              <p class="mb-1"><strong>Зарплата:</strong> <?= htmlspecialchars($vacancy['salary'] ?? '-') ?></p>
<p class="mb-1"><strong>Категория:</strong> <?= htmlspecialchars($vacancy['category'] ?? '-') ?></p>

              <!--<p><strong>Откликнулись:</strong> <?= (int)$vacancy['responses'] ?> <?= plural_form((int)$vacancy['responses'], ['человек','человека','человек']) ?></p>-->
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
                    <!--<p><strong>Опубликовал:</strong> <?= htmlspecialchars($vacancy['author'] ?? 'Неизвестно') ?></p>-->
  					<p><strong>Компания:</strong> <?= htmlspecialchars($vacancy['company']) ?></p>
  					<p><strong>Местоположение:</strong> <?= htmlspecialchars($vacancy['location']) ?></p>
					</div>
                    <div class="modal-footer">
                      <button type="submit" class="btn btn-primary">Да</button>
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
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
              <a class="page-link" href="?page=<?= $i ?><?= $title_filter? '&title='.urlencode($title_filter): '' ?><?= $company_filter? '&company='.urlencode($company_filter): '' ?><?= ($category_filter && $category_filter!=='Все категории')? '&category='.urlencode($category_filter): '' ?>"><?= $i ?></a>
            </li>
          <?php endfor; ?>
        </ul></nav>
      <?php endif; ?>
    <?php else: ?>
      <p>Вакансии не найдены.</p>
    <?php endif; ?>
  </main>
  <footer class="bg-light border-top py-4 mt-5">
    <div class="container text-center text-muted">&copy; 2025 TruWork. Все права защищены.</div>
  </footer>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
