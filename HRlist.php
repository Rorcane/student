<?php
require_once 'config.php';

// 1) Проверяем авторизацию
if (!isset($_COOKIE['user'])) {
    header('Location: login.html');
    exit();
}
$currentUser = $_COOKIE['user'];

// 2) Обработка создания задания
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['applicant'], $_POST['vacancy_id'], $_POST['task_title'])
) {
    $tStmt = $pdo->prepare("
      INSERT INTO tasks (vacancy_id, applicant, title, description, created_by)
      VALUES (:vid, :app, :ttl, :desc, :cb)
    ");
    $tStmt->execute([
      ':vid'   => (int)$_POST['vacancy_id'],
      ':app'   => $_POST['applicant'],
      ':ttl'   => trim($_POST['task_title']),
      ':desc'  => trim($_POST['task_description']),
      ':cb'    => $currentUser,
    ]);
    header('Location: HRlist.php');
    exit();
}

// 3) Выбираем «accepted» кандидатов из applications
$stmt = $pdo->prepare("
    SELECT 
        a.applicant           AS username,
        u.email               AS email,
        v.id                  AS vacancy_id,
        v.title               AS vacancy_title,
        a.created_at          AS accepted_at
    FROM applications a
    JOIN vacancies  v ON a.vacancy_id = v.id
    JOIN users       u ON u.username = a.applicant
    WHERE v.author = :me
      AND a.status = 'Принята'
    ORDER BY a.created_at DESC
");
$stmt->execute([':me' => $currentUser]);
$accepted = $stmt->fetchAll(PDO::FETCH_ASSOC);

function formatDateTime(string $dt): string {
    return date('d.m.Y H:i', strtotime($dt));
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
  <title>HR</title>
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

  <!-- Navbar -->
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
      <!-- Sidebar -->
      <div class="col-md-2 sidebar bg-white vh-100 border-end">
        <div class="p-3">
          <ul class="nav flex-column">
            <li class="nav-item mb-2"><a href="dashboard.php" class="nav-link"><i class="fas fa-home me-2"></i>Обзор</a></li>
            <li class="nav-item mb-2"><a href="applications.php" class="nav-link"><i class="fas fa-file-alt me-2"></i>Заявки</a></li>
            <li class="nav-item mb-2"><a href="analytics.php" class="nav-link"><i class="fas fa-chart-bar me-2"></i>Аналитика</a></li>
            <li class="nav-item mb-2"><a href="HRlist.php" class="nav-link active"><i class="fas fa-briefcase me-2"></i>HR</a></li>
          </ul>
        </div>
      </div>

      <!-- Main content -->
      <div class="col-md-10 p-4">
        <h2 class="mb-4">Принятые кандидаты</h2>

        <?php if (empty($accepted)): ?>
          <div class="alert alert-info">Пока нет принятых кандидатов.</div>
        <?php else: ?>
          <table class="table table-striped align-middle">
            <thead>
              <tr>
                <th>Кандидат</th>
                <th>Email</th>
                <th>Вакансия</th>
                <th>Дата принятия</th>
                <th>Действие</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($accepted as $row): ?>
                <tr>
                  <td><?= htmlspecialchars($row['username'], ENT_QUOTES) ?></td>
                  <td><?= htmlspecialchars($row['email'],    ENT_QUOTES) ?></td>
                  <td>
                    <a href="vacancy.php?id=<?= (int)$row['vacancy_id'] ?>">
                      <?= htmlspecialchars($row['vacancy_title'], ENT_QUOTES) ?>
                    </a>
                  </td>
                  <td><?= formatDateTime($row['accepted_at']) ?></td>
                  <td>
                    <a href="chat.php?with=<?= urlencode($row['username']) ?>&vacancy=<?= (int)$row['vacancy_id'] ?>"
                       class="btn btn-sm btn-outline-primary">
                      <i class="fas fa-comment"></i> Чат
                    </a>
                    <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addTaskModal"
                        onclick="document.querySelector('#addTaskModal select[name=applicant]').value='<?= htmlspecialchars($row['username'], ENT_QUOTES) ?>';
                                document.getElementById('taskVacancyId').value='<?= (int)$row['vacancy_id'] ?>'">
                      <i class="fas fa-plus"></i> Задание
                    </button>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>

        <!-- Кнопка «Добавить задание» -->
        <div class="mb-3">
          <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addTaskModal">
            <i class="fas fa-plus me-2"></i>Добавить задание
          </button>
        </div>

        <!-- Модальное окно -->
        <div class="modal fade" id="addTaskModal" tabindex="-1">
          <div class="modal-dialog">
            <form method="post" class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">Новое задание</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body">
                <div class="mb-3">
                  <label class="form-label">Кандидат</label>
                  <select name="applicant" class="form-select" required>
                    <option value="" disabled selected>– выберите кандидата –</option>
                    <?php foreach($accepted as $row): ?>
                      <option value="<?= htmlspecialchars($row['username'], ENT_QUOTES) ?>"
                              data-vacancy="<?= (int)$row['vacancy_id'] ?>">
                        <?= htmlspecialchars($row['username'], ENT_QUOTES) ?> (<?= htmlspecialchars($row['vacancy_title'], ENT_QUOTES) ?>)
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <input type="hidden" name="vacancy_id" id="taskVacancyId">
                <div class="mb-3">
                  <label class="form-label">Заголовок</label>
                  <input type="text" name="task_title" class="form-control" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">Описание</label>
                  <textarea name="task_description" class="form-control" rows="3"></textarea>
                </div>
              </div>
              <div class="modal-footer">
                <button type="submit" class="btn btn-success">Создать</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
              </div>
            </form>
          </div>
        </div>

      </div>
    </div>
  </div>
  </main>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Подстановка vacancy_id при выборе кандидата
    document.querySelector('#addTaskModal select[name="applicant"]')
      .addEventListener('change', function() {
        const opt = this.selectedOptions[0];
        document.getElementById('taskVacancyId').value = opt.dataset.vacancy;
      });
  </script>
  <<footer>
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
</body>
</html>
