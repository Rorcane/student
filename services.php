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
  <title>Сервисы ИИ</title>
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
      <ul class="nav flex-column">
        <li class="nav-item mb-2">
          <a class="nav-link" href="discovery.php">
            <i class="fas fa-compass me-2"></i>Обзор
          </a>
        </li>
        <li class="nav-item mb-2">
          <a class="nav-link active" href="services.php">
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

    <!-- Основной контент -->
    <div class="col-md-10 p-4">
      <h2 class="mb-4"><i class=""></i>AI-Сервисы</h2>
      
      <div class="row g-4">
        <!-- Карточка сервиса 1 -->
        <div class="col-md-4">
          <div class="card ai-service-card">
            <div class="card-body">
              <h5 class="card-title">Резюме-ассистент</h5>
              <p class="text-muted">Автоматическая оптимизация резюме под вакансии</p>
              <ul class="list-unstyled">
                <li><i class="fas fa-check text-success me-2"></i>Анализ ключевых слов</li>
                <li><i class="fas fa-check text-success me-2"></i>Советы по оформлению</li>
                <li><i class="fas fa-check text-success me-2"></i>Рейтинг совпадений</li>
              </ul>
              <button class="btn btn-primary w-100">Активировать</button>
            </div>
          </div>
        </div>

        <!-- Карточка сервиса 2 -->
        <div class="col-md-4">
          <div class="card ai-service-card">
            <div class="card-body">
              <h5 class="card-title">Предиктор зарплат</h5>
              <p class="text-muted">Прогнозирование рыночной стоимости специалиста</p>
              <div class="progress mb-3">
                <div class="progress-bar" style="width: 85%">Точность 85%</div>
              </div>
              <button class="btn btn-outline-primary w-100">Подробнее</button>
            </div>
          </div>
        </div>

        <!-- Добавьте другие сервисы... -->
      </div>
    </div>
  </div>
</div>
</main>
<!-- Футер -->
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
  
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>