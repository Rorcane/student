<?php
require_once 'config.php';
// dashboard.php
if (!isset($_COOKIE['user'])) {
  header('Location: login.html');
  exit();
}
// Имя текущего пользователя из куки
$username = $_COOKIE['user'];

// Удаление вакансии
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_vacancy_id'])) {

    $id = (int)$_POST['delete_vacancy_id'];
    $author = $_COOKIE['user'];

    $stmt = $pdo->prepare("DELETE FROM vacancies WHERE id = :id AND author = :author");
    $stmt->execute([
        ':id' => $id,
        ':author' => $author
    ]);

    header("Location: dashboard.php");
    exit();
}

// Обновление вакансии
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['vacancy_id']) && !isset($_POST['delete_vacancy_id'])) {

    $id = (int)$_POST['vacancy_id'];
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $company = trim($_POST['company']);
    $location = trim($_POST['location']);
    $author = $_COOKIE['user'];

    $stmt = $pdo->prepare("
        UPDATE vacancies
        SET title = :title,
            description = :description,
            company = :company,
            location = :location
        WHERE id = :id AND author = :author
    ");

    $stmt->execute([
        ':title' => $title,
        ':description' => $description,
        ':company' => $company,
        ':location' => $location,
        ':id' => $id,
        ':author' => $author
    ]);

    header("Location: dashboard.php");
    exit();
}




// Получаем все вакансии, где author = username
$vStmt = $pdo->prepare("
SELECT id, title, description, company, location, created_at
FROM vacancies
WHERE author = :author
ORDER BY created_at DESC

");
$vStmt->execute([':author' => $username]);
$vacancies = $vStmt->fetchAll(PDO::FETCH_ASSOC);

// Считаем количество активных вакансий
$activeCount = count($vacancies);

// Вспомогательная функция форматирования даты (если есть created_at)
function formatDateTime(string $dt): string {
    $ts = strtotime($dt);
    return date('d.m.Y H:i', $ts);
}

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

$appStmt = $pdo->prepare("
  SELECT COUNT(a.id)
  FROM applications a
  JOIN vacancies v ON v.id = a.vacancy_id
  WHERE v.author = :author
");
$appStmt->execute([':author' => $username]);
$newApplications = $appStmt->fetchColumn();


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
  <title>Панель управления TruWork</title>
  <link rel="icon" type="image/png" href="/favicon-96x96.png" sizes="96x96" />
  <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
  <link rel="shortcut icon" href="/favicon.ico" />
  <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png" />
  <link rel="manifest" href="/site.webmanifest" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

<body class="bg-light">

<!-- Навигационная панель -->
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
          <li class="nav-item"><a class="nav-link" href="vacancies.php"><span class="nav-link-border"></span>Найти работу</a></li>
          <li class="nav-item"><a class="nav-link" href="vacancy.php"><span class="nav-link-border"></span>Опубликовать</a></li>
          <li class="nav-item"><a class="nav-link active" href="dashboard.php"><span class="nav-link-border"></span>Панель</a></li>
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
<div class="container-fluid">
  <div class="row">
      <!-- Боковое меню -->
      <div class="col-md-2 sidebar bg-white vh-100 border-end">
          <div class="p-3">
              
              <ul class="nav flex-column">
                  <li class="nav-item mb-2">
                      <a href="dashboard.php" class="nav-link active">
                          <i class="fas fa-home me-2"></i>Обзор
                      </a>
                  </li>
                  <li class="nav-item mb-2">
                      <a href="applications.php" class="nav-link">
                          <i class="fas fa-file-alt me-2"></i>Заявки
                      </a>
                  </li>
                  <li class="nav-item mb-2">
                      <a href="analytics.php" class="nav-link">
                          <i class="fas fa-chart-bar me-2"></i>Аналитика
                      </a>
                  <li class="nav-item mb-2">
                      <a href="HRlist.php" class="nav-link">
                          <i class="fas fa-briefcase me-2"></i>HR
                      </a>
                  </li>
              </ul>
          </div>
      </div>

        <!-- Основной контент -->
  <div class="col-md-10 p-4">
    <h2 class="mb-4">Панель управления</h2>

    <!-- Статистика -->
    <div class="row g-4 mb-4">
      <div class="col-md-3">
        <div class="card dashboard-card">
          <div class="card-body text-center">
            <h5 class="card-title">Активные вакансии</h5>
            <div class="d-flex justify-content-center align-items-center">
              <span class="stat-badge"><?= $activeCount ?></span>
              <i class="fas fa-briefcase fs-2 text-primary ms-2"></i>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card dashboard-card">
          <div class="card-body text-center">
            <h5 class="card-title">Новые заявки</h5>
            <div class="d-flex justify-content-center align-items-center">
              <span class="stat-badge"><?= $newApplications ?? '—' ?></span>
              <i class="fas fa-file-alt fs-2 text-success ms-2"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Последние вакансии -->
    <div class="card dashboard-card">
      <div class="card-body">
        <h5 class="card-title mb-4">Последние вакансии</h5>
        <div class="list-group">
          <?php if (empty($vacancies)): ?>
            <div class="list-group-item text-muted">У вас пока нет опубликованных вакансий.</div>
          <?php else: ?>
            <?php foreach ($vacancies as $vac): ?>
              <a href="#" class="list-group-item">
                <div class="d-flex justify-content-between">
                  <span><?= htmlspecialchars($vac['title'], ENT_QUOTES) ?></span>
                  <small class="text-muted">
                    <?= isset($vac['created_at']) ? formatDateTime($vac['created_at']) : '' ?>
          
          
          <div>
            <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#applyModal-<?= $vac['id'] ?>" data-bs-id="<?= $vac['id'] ?>" data-bs-title="<?= htmlspecialchars($vac['title'], ENT_QUOTES) ?>">Развернуть</button>
            <div class="modal fade" id="applyModal-<?= $vac['id'] ?>" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog">
                <form method="POST" class="modal-content">
                  <input type="hidden" name="vacancy_id" value="<?= $vac['id'] ?>">
                  <div class="modal-header">
                    <h5 class="modal-title">Развернуть</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                  </div>
                  <div class="modal-body">

                    <div class="mb-3">
                      <label class="form-label">Название</label>
                      <input type="text" name="title" class="form-control"
                            value="<?= htmlspecialchars($vac['title']) ?>">
                    </div>

                    <div class="mb-3">
                      <label class="form-label">Описание</label>
                      <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($vac['description']) ?></textarea>
                    </div>

                    <div class="mb-3">
                      <label class="form-label">Компания</label>
                      <input type="text" name="company" class="form-control"
                            value="<?= htmlspecialchars($vac['company']) ?>">
                    </div>

                    <div class="mb-3">
                      <label class="form-label">Местоположение</label>
                      <input type="text" name="location" class="form-control"
                            value="<?= htmlspecialchars($vac['location']) ?>">
                    </div>

                  </div>
                  <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Сохранить</button>
                    <button type="submit"
                            name="delete_vacancy_id"
                            value="<?= $vac['id'] ?>"
                            class="btn btn-danger"
                            onclick="return confirm('Удалить эту вакансию?')">
                        Удалить
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
          
          
                  </small>
                </div>
              </a>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>

  </div>
  </div>
</div>
</main>
<!-- Футер -->
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
</body>
</html>