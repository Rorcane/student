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
  <title>Обзор</title>
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
          <li class="nav-item"><a class="nav-link" href="dashboard.php"><span class="nav-link-border"></span>Панель</a></li>
          <li class="nav-item"><a class="nav-link active" href="discovery.php"><span class="nav-link-border"></span>Обзор</a></li>
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
    <div class="col-md-2 d-none d-md-block sidebar">
      <div class="p-3">
      <ul class="nav flex-column">
        <li class="nav-item mb-2">
          <a class="nav-link active" href="discovery.php">
            <i class="fas fa-compass me-2"></i>Обзор
          </a>
        </li>
        <li class="nav-item mb-2">
          <a class="nav-link" href="services.php">
            <i class="fas fa-magic me-2"></i>Сервисы ИИ
          </a>
        </li>
        <li class="nav-item mb-2">
          <a class="nav-link" href="portfolio.php">
            <i class="fas fa-briefcase me-2"></i>Портфолио
          </a>
        </li>
		<li class="nav-item mb-2">
  			<a href="my_applications.php" class="nav-link">
    			<i class="fas fa-paper-plane me-2"></i>Мои заявки
  			</a>
		</li>
        <li class="nav-item mb-2">
          <a class="nav-link" href="connections.php">
            <i class="fas fa-network-wired me-2"></i>Связи
          </a>
        </li>
       
      </ul>
      </div>
    </div>

    <!-- Основной контент -->
    <div class="col-md-8 p-4">
      <!-- Блок ИИ-рекомендаций -->
      <div class="card mb-4 vacancy-card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="card-title">Персональные рекомендации <span class="ai-badge">AI Powered</span></h4>
            <a href="#" class="btn btn-link">Настроить фильтры</a>
          </div>
          
          <!-- Пример вакансии -->
          <div class="card mb-3">
            <div class="card-body">
              <div class="d-flex align-items-start">
                <img src="company-logo.png" alt="Company" width="80" class="me-3">
                <div class="flex-grow-1">
                  <h5>Senior Python Developer</h5>
                  <div class="d-flex gap-2 mb-2">
                    <span class="badge bg-primary">Удалённая работа</span>
                    <span class="badge bg-success">От 200 000 ₽</span>
                    <span class="badge bg-info">Опыт 5+ лет</span>
                  </div>
                  <p class="text-muted">Совпадение по навыкам: 94%</p>
                  <div class="progress mb-3">
                    <div class="progress-bar" role="progressbar" style="width: 94%"></div>
                  </div>
                </div>
                <button class="btn btn-outline-primary">Откликнуться</button>
              </div>
            </div>
          </div>

          <!-- Ещё вакансии... -->
        </div>
      </div>

      <!-- Блок аналитики -->
      <div class="card mb-4">
        <div class="card-body">
          <h4 class="card-title mb-4">Ваша активность <span class="ai-badge">Smart Analysis</span></h4>
          <div class="row">
            <div class="col-md-6">
              <canvas id="activityChart"></canvas>
            </div>
            <div class="col-md-6">
              <div class="alert alert-info">
                <h5><i class="fas fa-lightbulb me-2"></i>Совет от ИИ</h5>
                <p>Добавьте больше навыков в профиль, чтобы увеличить количество предложений на 40%</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Правая панель -->
    <div class="col-md-2 p-3 bg-light">
      <div class="sticky-back">
        <h5 class="mb-3"><i class="fas fa-bolt me-2"></i>Быстрые действия</h5>
        <div class="list-group">
          <a href="vacancy.php" class="list-group-item list-group-item-action">
            <i class="fas fa-plus-circle me-2"></i>Создать вакансию
          </a>
          <a href="#" class="list-group-item list-group-item-action">
            <i class="fas fa-users me-2"></i>Мои кандидаты
          </a>
          <a href="#" class="list-group-item list-group-item-action">
            <i class="fas fa-chart-pie me-2"></i>Статистика
          </a>
        </div>

        <h5 class="mt-4 mb-3"><i class="fas fa-bell me-2"></i>Уведомления</h5>
        <div class="activity-feed">
          <!-- Уведомления... -->
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
  // Инициализация графика
  const ctx = document.getElementById('activityChart').getContext('2d');
  new Chart(ctx, {
    type: 'line',
    data: {
      labels: ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'],
      datasets: [{
        label: 'Активность',
        data: [12, 19, 3, 5, 2, 3, 15],
        borderColor: '#2A73CC',
        tension: 0.4
      }]
    }
  });
</script>
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
</body>
</html>