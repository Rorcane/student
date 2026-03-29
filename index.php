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
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Truwork.kz — поиск работы и сотрудников</title>
	<meta name="description" content="Найдите работу по душе на TruWork — свежие вакансии от лучших компаний. Быстрый и удобный поиск.">
	<meta name="keywords" content="работа, вакансии, поиск работы, TruWork, карьера, трудоустройство">

	<link rel="icon" type="image/png" href="/favicon-96x96.png" sizes="96x96" />
	<link rel="icon" type="image/svg+xml" href="/favicon.svg" />
	<link rel="shortcut icon" href="/favicon.ico" />
	<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png" />
	<link rel="manifest" href="/site.webmanifest" />

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Animate.css (для анимации) -->
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"
  />

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

  <style>
    /* 1. Убираем дефолтные отступы и задаём padding под фиксированную шапку */
    html, body {
      margin: 0;
      padding: 0;
      font-family: 'Inter', sans-serif;
    }
    body {
      padding-top: 56px; /* под высоту вашего fixed-top navbar */
      background: #f4f4f4;
    }

    /* 2. Navbar */
    .navbar {
      padding: .5rem 0;
    }
    .nav-link {
      position: relative;
      padding: .5rem 1rem !important;
    }
    .nav-link-border {
      position: absolute;
      bottom: 0; left: 0;
      width: 0; height: 2px;
      background: #2563eb;
      transition: width .3s ease;
    }
    .nav-link:hover .nav-link-border,
    .nav-link.active .nav-link-border {
      width: 100%;
    }

    /* 3. Hero Section */
    .hero-section {
      background:
        linear-gradient(rgba(0,0,0,0.5),rgba(0,0,0,0.5)),
        url('img/hero-bg.jpg') center/cover no-repeat;
      height: 75vh;
      min-height: 400px;
      padding: 0;               /* убрали внутренний отступ сверху */
      display: flex;
      align-items: center;
      color: #fff;
    }
    .hero-section .container {
      padding: 2rem 1rem;       /* отступ внутри для текста */
      text-align: center;
    }
    .text-gradient {
      background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    /* 4. Features Section */
    .feature-card {
      transition: transform .3s, box-shadow .3s;
      border: 1px solid rgba(0,0,0,0.075);
    }
    .feature-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 1rem 3rem rgba(0,0,0,0.1) !important;
    }
    .icon-wrapper {
      width: 64px; height: 64px;
      border-radius: 16px;
      display: flex; align-items: center; justify-content: center;
      background: rgba(37, 99, 235, 0.1);
      margin: 0 auto 1rem;
    }
    .icon-wrapper svg {
      width: 32px; height: 32px; fill: #2563eb;
    }

    /* 5. Stats Section */
    .stats-section {
      background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
      color: #fff;
      padding: 80px 0;
    }

    /* 6. Footer */
    footer {
      background: #fff;
      padding: 60px 0 40px;
    }
    footer a {
      color: #6c757d;
      text-decoration: none;
    }
    footer a:hover {
      text-decoration: underline;
    }
  </style>
</head>

<body>

  <!-- Fixed-top Navbar -->
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
          <li class="nav-item">
            <a class="nav-link" href="vacancies.php">
              <span class="nav-link-border"></span>Найти работу
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="vacancy.php">
              <span class="nav-link-border"></span>Опубликовать
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="dashboard.php">
              <span class="nav-link-border"></span>Панель
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="discovery.php">
              <span class="nav-link-border"></span>Обзор
            </a>
          </li>
        </ul>
        <?php if(isset($_COOKIE['user'])): ?>
          <a href="profile.php" class="btn btn-outline-primary ms-3"><?= htmlspecialchars($_COOKIE['user']) ?></a>
        <?php else: ?>
          <a href="login.html" class="btn btn-primary ms-3">Войти</a>
        <?php endif; ?>
      </div>
    </div>
  </nav>

  <!-- Hero Section -->
  <header class="hero-section">
    <div class="container">
      <h1 class="display-4 fw-bold mb-3 animate__animated animate__fadeInDown">
        Твоя следующая работа<br>
        <span class="text-gradient">в одном клике</span>
      </h1>
      <p class="lead mb-4">
        Оптимизируйте подбор персонала с помощью нейросетевого анализа и блокчейн-верификации
      </p>
      <a href="vacancies.php" class="btn btn-primary btn-lg px-5 me-3">Найти сотрудников</a>
      <a href="vacancy.php" class="btn btn-outline-light btn-lg px-5">Разместить вакансию</a>
    </div>
  </header>

  <!-- Features Section -->
  <section class="features py-5 bg-light">
    <div class="container">
      <div class="row g-4">
        <div class="col-lg-3 col-md-6">
          <div class="feature-card text-center p-4 h-100">
            <div class="icon-wrapper">
              <svg aria-hidden="true"><use xlink:href="#ai-icon"/></svg>
            </div>
            <h5>AI-анализ резюме</h5>
            <p class="text-muted">Глубокий анализ по 20+ параметрам с прогнозом успешности</p>
          </div>
        </div>
        <div class="col-lg-3 col-md-6">
          <div class="feature-card text-center p-4 h-100">
            <div class="icon-wrapper">
              <svg aria-hidden="true"><use xlink:href="#shield-icon"/></svg>
            </div>
            <h5>Безопасность</h5>
            <p class="text-muted">Шифрование данных и многофакторная аутентификация</p>
          </div>
        </div>
        <div class="col-lg-3 col-md-6">
          <div class="feature-card text-center p-4 h-100">
            <div class="icon-wrapper">
              <svg aria-hidden="true"><use xlink:href="#analytics-icon"/></svg>
            </div>
            <h5>HR-аналитика</h5>
            <p class="text-muted">Автоматизация документооборота и отчёты</p>
          </div>
        </div>
        <div class="col-lg-3 col-md-6">
          <div class="feature-card text-center p-4 h-100">
            <div class="icon-wrapper">
              <svg aria-hidden="true"><use xlink:href="#payment-icon"/></svg>
            </div>
            <h5>Безопасные транзакции</h5>
            <p class="text-muted">Эскроу-счёта и криптографическая защита</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Stats Section -->
  <section class="stats-section text-center">
    <div class="container">
      <div class="row g-4">
        <div class="col-md-3">
          <div class="display-4 fw-bold">1500+</div>
          <div>Активных вакансий</div>
        </div>
        <div class="col-md-3">
          <div class="display-4 fw-bold">98%</div>
          <div>Успешных наймов</div>
        </div>
        <div class="col-md-3">
          <div class="display-4 fw-bold">24ч</div>
          <div>Среднее время подбора</div>
        </div>
        <div class="col-md-3">
          <div class="display-4 fw-bold">500+</div>
          <div>Компаний-партнеров</div>
        </div>
      </div>
    </div>
  </section>

  <!-- Footer -->
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


  <!-- SVG Icons -->
  <svg xmlns="http://www.w3.org/2000/svg" style="display:none">
    <symbol id="ai-icon" viewBox="0 0 24 24"><path d="M21 14.976c0 .558-.442 1.008-1.002 1.008H4.002C3.45 16 3 15.55 3 14.976V4.023C3 3.45 3.445 3 4 3h16c.552 0 1 .456 1 1.023v10.953zm-9.459-1.01l2.264-5.59 1.06 2.296 1.283-1.57-2.952-3.851-2.285 5.649-2.395-4.873-3.363 5.03h8.388zm6.459 4.04c0 .557-.442 1.008-1.002 1.008H4.002C3.45 19 3 18.55 3 17.976v-1.01h15.996v1.01h1.044c.555 0 1.006.456 1.006 1.023 0 .566-.45 1.023-1.006 1.023H4.002C3.45 21 3 20.544 3 19.977c0-.566.45-1.023 1.002-1.023h15.996z"/></symbol>
    <symbol id="shield-icon" viewBox="0 0 24 24"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99h7c-.45 3.61-3.07 6.64-7 7.62V12H5V6.3l7-3.11v8.8z"/></symbol>
    <symbol id="analytics-icon" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zM9 17H7v-5h2v5zm4 0h-2v-3h2v3zm0-5h-2v-2h2v2zm4 5h-2V7h2v10z"/></symbol>
    <symbol id="payment-icon" viewBox="0 0 24 24"><path d="M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z"/></symbol>
  </svg>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
