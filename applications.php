<?php
require_once 'config.php';

if (!isset($_COOKIE['user'])) {
    header('Location: login.html');
    exit();
}

$username = $_COOKIE['user'];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['application_id'], $_POST['action'])) {

    $appId = (int)$_POST['application_id'];
    $action = $_POST['action'];
    $allowedStatuses = ['Отклонена', 'В работе', 'Завершена', 'Принята'];


    if ($action === 'Удалить') {
        $stmtDelete = $pdo->prepare("
            DELETE a FROM applications a
            JOIN vacancies v ON a.vacancy_id = v.id
            WHERE a.id = :id AND v.author = :author
        ");
        $stmtDelete->execute([
            ':id' => $appId,
            ':author' => $username
        ]);
    } elseif (in_array($action, $allowedStatuses, true)) {
        $stmtUpdate = $pdo->prepare("
            UPDATE applications a
            JOIN vacancies v ON a.vacancy_id = v.id
            SET a.status = :status
            WHERE a.id = :id AND v.author = :author
        ");
        $stmtUpdate->execute([
            ':status' => $action,
            ':id' => $appId,
            ':author' => $username
        ]);
    }

    header("Location: applications.php");
    exit();
}



// Список статусов
$statuses = ['Новая', 'В работе', 'Завершена', 'Отклонена', 'Принята'];


$status = $_GET['status'] ?? '';

// Базовый SQL и параметры
$sql = "
     SELECT 
    a.id,
    a.vacancy_id,
    a.applicant,
    a.created_at,
    a.status,
    v.title

    FROM applications a
    JOIN vacancies v ON a.vacancy_id = v.id
    WHERE v.author = :username
";
$params = [':username' => $username];
// $params[':status'] = $status;


// Если выбран статус — добавляем фильтр
if ($status !== '') {
    $sql .= " AND a.status = :status";
    $params[':status'] = $status;
}

$sql .= " ORDER BY a.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$applications = $stmt->fetchAll();


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
  <title>Заявки</title>
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
                        <a href="dashboard.php" class="nav-link">
                            <i class="fas fa-home me-2"></i>Обзор
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a href="applications.php" class="nav-link active">
                            <i class="fas fa-file-alt me-2"></i>Заявки
                        </a>
                    </li>
                    <li class="nav-item mb-2">
                        <a href="analytics.php" class="nav-link">
                            <i class="fas fa-chart-bar me-2"></i>Аналитика
                        </a>
                    </li>
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
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class=""></i>Управление заявками</h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#filterModal">
                        <i class="fas fa-filter me-2"></i>Фильтры
                    </button>
                </div>

                <!-- Фильтры -->
                <form method="GET" class="row mb-4 filter-box p-3">
                  <div class="col-md-3">
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">Все статусы</option>
                        <?php foreach ($statuses as $s): ?>
                            <option value="<?= $s ?>" <?= ($status === $s) ? 'selected' : '' ?>>
                                <?= $s === 'Завершена' ? 'Завершённые' : $s ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                  </div>
                </form>
				<div class="card shadow-sm">
				  <div class="card-body">
					<div class="list-group">
					<?php foreach ($applications as $app): ?>
					  <?php
						// 1) Получаем ID вакансии и закодированного автора
						$vacancyId    = (int)$app['vacancy_id'];
						$applicantEnc = urlencode($app['applicant']);
					  ?>
					  <div class="list-group-item application-card mb-2">
						<div class="d-flex justify-content-between align-items-center">
						  <div>
                <h5 class="mb-1">
                  <a href="profile.php?user=<?= urlencode($app['applicant']) ?>" class="text-decoration-none">
                    <?= htmlspecialchars($app['applicant'], ENT_QUOTES) ?>
                  </a>
                </h5>
                <small class="text-muted d-block">
                  <?= htmlspecialchars($app['title'], ENT_QUOTES) ?>
                </small>
                <small class="text-muted">
                  Дата подачи: <?= date("d.m.Y H:i", strtotime($app['created_at'])) ?>
                </small>
                 <span class="badge mt-1
                  <?php
                    switch ($app['status'] ?? 'Новая') {
                      case 'Принята': echo 'bg-primary text-white'; break;
                      case 'Отклонена': echo 'bg-danger'; break;
                      case 'В работе': echo 'bg-warning text-dark'; break;
                      case 'Завершена': echo 'bg-success'; break;
                      default: echo 'bg-secondary';
                    }
                  ?>">
                    <?= htmlspecialchars($app['status'] ?? 'Новая') ?>
                  </span>
              </div>
						  <div class="d-flex align-items-center gap-2">
              <!-- Ссылка на вакансию -->
              <button type="button"
                      class="btn btn-sm btn-outline-primary"
                      data-bs-toggle="modal"
                      data-bs-target="#vacancyModal-<?= $app['vacancy_id'] ?>">
                  Перейти к вакансии
              </button>
              <?php
              // Получаем данные вакансии
              $vacStmt = $pdo->prepare("SELECT * FROM vacancies WHERE id = ?");
              $vacStmt->execute([$app['vacancy_id']]);
              $vacancy = $vacStmt->fetch(PDO::FETCH_ASSOC);
              ?>

              <div class="modal fade" id="vacancyModal-<?= $app['vacancy_id'] ?>" tabindex="-1">
                <div class="modal-dialog">
                  <div class="modal-content">

                    <div class="modal-header">
                      <h5 class="modal-title">
                        <?= htmlspecialchars($vacancy['title'] ?? 'Вакансия') ?>
                      </h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                      <p><strong>Компания:</strong> <?= htmlspecialchars($vacancy['company'] ?? '-') ?></p>
                      <p><strong>Описание:</strong></p>
                      <p><?= nl2br(htmlspecialchars($vacancy['description'] ?? '')) ?></p>
                      <p><strong>Локация:</strong> <?= htmlspecialchars($vacancy['location'] ?? '-') ?></p>
                      <p><strong>Зарплата:</strong> <?= htmlspecialchars($vacancy['salary'] ?? '-') ?></p>
                    </div>

                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Закрыть
                      </button>
                    </div>

                  </div>
                </div>
              </div>
              <!-- Ссылка на чат -->
              <a href="chat.php?with=<?= $applicantEnc ?>&vacancy=<?= $vacancyId ?>" class="btn btn-sm btn-outline-primary" title="Чат с кандидатом">
                  <i class="fas fa-comment"></i>
              </a>
              <a href="profile.php?user=<?= $applicantEnc ?>" 
                class="btn btn-sm btn-outline-dark" 
                title="Профиль">
                <i class="fas fa-user"></i>
              </a>
              <?php if (($app['status'] ?? 'Новая') === 'Отклонена'): ?>
                  <!-- Только удалить -->
                  <form method="POST" class="d-inline">
                      <input type="hidden" name="application_id" value="<?= $app['id'] ?>">
                      <input type="hidden" name="action" value="Удалить">
                      <button type="submit" class="btn btn-sm btn-outline-dark" title="Удалить" onclick="return confirm('Удалить заявку?')">
                          <i class="fas fa-trash"></i>
                      </button>
                  </form>

              <?php elseif (($app['status'] ?? 'Новая') === 'Принята'): ?>
                  <!-- Принята: никаких кнопок кроме, возможно, удаления -->
                  <form method="POST" class="d-inline">
                      <input type="hidden" name="application_id" value="<?= $app['id'] ?>">
                      <input type="hidden" name="action" value="Удалить">
                      <button type="submit" class="btn btn-sm btn-outline-dark" title="Удалить" onclick="return confirm('Удалить заявку?')">
                          <i class="fas fa-trash"></i>
                      </button>
                  </form>

              <?php else: ?>
                  <!-- Все остальные статусы: можно Отклонить и Принять -->
                  <form method="POST" class="d-inline">
                      <input type="hidden" name="application_id" value="<?= $app['id'] ?>">
                      <input type="hidden" name="action" value="Отклонена">
                      <button type="submit" class="btn btn-sm btn-outline-danger" title="Отклонить" onclick="return confirm('Отклонить заявку?')">
                          <i class="fas fa-times"></i>
                      </button>
                  </form>
                  <form method="POST" class="d-inline">
                      <input type="hidden" name="application_id" value="<?= $app['id'] ?>">
                      <input type="hidden" name="action" value="Принята">
                      <button type="submit" class="btn btn-sm btn-outline-success" title="Принять" onclick="return confirm('Принять заявку?')">
                          <i class="fas fa-check"></i>
                      </button>
                  </form>
              <?php endif; ?>
          </div>

						</div>
					  </div>
					<?php endforeach; ?>
					</div>
				  </div>
				</div>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно фильтров -->
<div class="modal fade" id="filterModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Фильтры заявок</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Форма фильтров -->
            </div>
        </div>
    </div>
</div>
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
</body>
</html>