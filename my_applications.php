<?php
require_once 'config.php';
require_once 'hh_jobs.php';

if (!isset($_COOKIE['user'])) {
    header('Location: login.html');
    exit();
}

$current = (string) $_COOKIE['user'];

if (!hhApplicationsTableExists($pdo)) {
    ensureHhApplicationsTable($pdo);
}

$stmt = $pdo->prepare("
    SELECT
      a.id,
      a.vacancy_id AS item_id,
      a.created_at AS applied_at,
      CONVERT(v.title USING utf8mb4) COLLATE utf8mb4_unicode_ci AS vacancy_title,
      CONVERT(v.author USING utf8mb4) COLLATE utf8mb4_unicode_ci AS vacancy_author,
      'internal' AS source
    FROM applications a
    JOIN vacancies v ON a.vacancy_id = v.id
    WHERE a.applicant = :me

    UNION ALL

    SELECT
      ha.id,
      ha.job_id AS item_id,
      ha.created_at AS applied_at,
      CONVERT(j.title USING utf8mb4) COLLATE utf8mb4_unicode_ci AS vacancy_title,
      CONVERT(j.company USING utf8mb4) COLLATE utf8mb4_unicode_ci AS vacancy_author,
      'hh' AS source
    FROM hh_applications ha
    JOIN jobs j ON ha.job_id = j.id
    WHERE ha.applicant = :me

    ORDER BY applied_at DESC
");

$stmt->execute([':me' => $current]);
$apps = $stmt->fetchAll(PDO::FETCH_ASSOC);

function fmtDT($dt)
{
    return date('d.m.Y H:i', strtotime((string) $dt));
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Мои заявки</title>
  <link rel="icon" type="image/png" href="/favicon-96x96.png" sizes="96x96" />
  <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
  <link rel="shortcut icon" href="/favicon.ico" />
  <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png" />
  <link rel="manifest" href="/site.webmanifest" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light" style="padding-top:35px;">
  <nav class="navbar navbar-expand-lg navbar-light bg-white fixed-top border-bottom">
    <div class="container">
      <a class="navbar-brand fw-bold d-flex align-items-center" href="index.php">
        <img src="logo2.png" alt="TruWork" width="95" class="me-2">
      </a>
      <div class="collapse navbar-collapse">
        <ul class="navbar-nav ms-auto align-items-lg-center">
          <li class="nav-item"><a class="nav-link" href="vacancies.php">Найти работу</a></li>
          <li class="nav-item"><a class="nav-link" href="vacancy.php">Опубликовать</a></li>
          <li class="nav-item"><a class="nav-link" href="dashboard.php">Панель</a></li>
          <li class="nav-item"><a class="nav-link active" href="my_applications.php">Мои заявки</a></li>
        </ul>
        <a href="profile.php" class="btn btn-outline-primary ms-3"><?= htmlspecialchars($current) ?></a>
      </div>
    </div>
  </nav>

  <main class="container py-5">
    <h2 class="mb-4">Мои заявки</h2>

    <?php if (empty($apps)): ?>
      <div class="alert alert-info">Вы ещё ни на одну вакансию не откликались.</div>
    <?php else: ?>
      <div class="list-group">
        <?php foreach ($apps as $app): ?>
          <div class="list-group-item mb-2">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h5 class="mb-1"><?= htmlspecialchars((string) $app['vacancy_title']) ?></h5>
                <small class="text-muted">
                  <?= htmlspecialchars((string) $app['vacancy_author']) ?>, <?= htmlspecialchars(fmtDT($app['applied_at'])) ?>
                </small>
              </div>
              <div class="btn-group">
                <a
                  href="search.php?<?= ($app['source'] === 'hh' ? 'source=hh&' : '') ?>id=<?= (int) $app['item_id'] ?>"
                  class="btn btn-sm btn-outline-primary"
                >Подробнее</a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </main>
</body>
</html>
