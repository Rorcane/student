<?php
// add_vacancy.php
// Если нужно, можно добавить проверку авторизации, например:
if (!isset($_COOKIE['user'])) {
header('Location: login.html');
exit();
}
require_once 'config.php';

$stmt = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC");
$categories = $stmt->fetchAll();

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
  <title>Добавить вакансию</title>
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
  <style>
		.form-container {
      max-width: 800px;
      margin: 2rem auto;
      background: #fff;
      padding: 2rem;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }
    .form-title {
      margin-bottom: 1.5rem;
      font-weight: 600;
      text-align: center;
    }
    .form-label {
      font-weight: 500;
    }
    .btn-submit {
      width: 100%;
      padding: 0.75rem;
      font-size: 1.1rem;
    }
		main {
    padding-top: 76px; /* Высота навбара + отступ */
    min-height: calc(100vh - 120px); /* Чтобы футер не прилипал вверх */
  }

  .pagination .page-item.active .page-link {
    background: var(--gradient);
    border-color: var(--primary);
  }

  .vacancy-card {
    transition: transform 0.2s;
    border: 1px solid rgba(0,0,0,0.08);
  }

  .vacancy-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
  }

  .category-badge {
    transition: all 0.2s;
    border: 1px solid var(--primary);
  }

  .category-badge.active {
    background: var(--gradient);
    color: white !important;
    border-color: transparent;
  }


  /* Обновим существующий стиль для .hero-section */
  
	</style>
</head>
<body style="background: #f4f4f4;">

  <!-- Шапка сайта (при необходимости можно вынести в общий блок) -->
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
          <li class="nav-item"><a class="nav-link active" href="vacancy.php"><span class="nav-link-border"></span>Опубликовать</a></li>
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

  <!-- Основной контент: Форма добавления вакансии -->
  <div class="container pt-5" style="padding-top: 100px">
    <div class="form-container">
      <h2 class="form-title">Добавить новую вакансию</h2>
      
      <!-- Форма. Можно отправлять данные на этот же файл или на отдельный обработчик (например, process_vacancy.php) -->
      <form action="process_vacancy.php" method="POST">
        <div class="mb-3">
          <label for="jobTitle" class="form-label">Название вакансии</label>
          <input type="text" class="form-control" id="title" name="title" placeholder="Введите название вакансии" required>
        </div>
        
        <div class="mb-3">
          <label for="companyName" class="form-label">Компания</label>
          <input type="text" class="form-control" id="companyName" name="company" placeholder="Название компании" required>
        </div>
        
        <div class="mb-3 row">
          <div class="col-md-6">
            <label for="jobCategory" class="form-label">Категория</label>
            <select class="form-select" name="category" required>
              <option value="" disabled selected>Выберите категорию</option>
              <?php foreach ($categories as $cat): ?>
                  <option value="<?= $cat['id'] ?>">
                      <?= htmlspecialchars($cat['name']) ?>
                  </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-6">
            <label for="salaryRange" class="form-label">Зарплата</label>
            <input type="text" class="form-control" id="salaryRange" name="salary" placeholder="Укажите диапазон зарплаты" required>
          </div>
        </div>
        
        <div class="mb-3">
          <label for="location" class="form-label">Местоположение</label>
          <input type="text" class="form-control" id="location" name="location" placeholder="Город или удалённо" required>
        </div>
        
        <div class="mb-3">
          <label for="jobDescription" class="form-label">Описание вакансии</label>
          <textarea class="form-control" id="jobDescription" name="description" rows="5" placeholder="Подробно опишите требования и обязанности" required></textarea>
        </div>
        
        <button type="submit" class="btn btn-primary btn-submit">
          Опубликовать вакансию
        </button>
      </form>
    </div>
  </div>

  <!-- Подключение Bootstrap JS -->
  <script 
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"
  ></script>
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