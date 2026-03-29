<?php
// add_vacancy.php
// Если нужно, можно добавить проверку авторизации, например:
if (!isset($_COOKIE['user'])) {
header('Location: login.html');
exit();
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
  <meta charset="UTF-8">
  <title>Добавить вакансию</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/png" href="/favicon-96x96.png" sizes="96x96" />
	<link rel="icon" type="image/svg+xml" href="/favicon.svg" />
	<link rel="shortcut icon" href="/favicon.ico" />
	<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png" />
	<link rel="manifest" href="/site.webmanifest" />
  <!-- Bootstrap CSS -->
  <link 
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" 
    rel="stylesheet"
  />
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" />



  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
	<style>
        :root {
            --primary: #2563eb;    /* Обновленный синий */
            --secondary: #3b82f6;  /* Более светлый оттенок */
            --accent: #60a5fa;     /* Акцентный цвет */
            --gradient: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
        }

        * {
            font-family: 'Inter', sans-serif;
        }

        .navbar {
            padding: 0.5rem 0;
            transition: all 0.3s;
        }

        .nav-link {
            position: relative;
            padding: 0.5rem 1rem !important;
        }

        .nav-link-border {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary);
            transition: width 0.3s ease;
        }

        .nav-link:hover .nav-link-border,
        .nav-link.active .nav-link-border {
            width: 100%;
        }

        .hero-section {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 80px 0;
    margin-top: 76px; /* Это значение должно совпадать с padding-top у main */
  }

        .text-gradient {
            background: var(--gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .feature-card {
            transition: transform 0.3s, box-shadow 0.3s;
            border: 1px solid rgba(0,0,0,0.075);
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 1rem 3rem rgba(0,0,0,0.1) !important;
        }

        .icon-wrapper {
            width: 64px;
            height: 64px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(37, 99, 235, 0.1);
        }

        .icon-wrapper svg {
            width: 32px;
            height: 32px;
            fill: var(--primary);
        }

        .stats-section {
            background: var(--gradient);
            color: white;
            padding: 80px 0;
        }

        .btn-primary {
            background: var(--gradient);
            border: none;
            transition: transform 0.3s !important;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
        }

        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
            100% { transform: translateY(0px); }
        }

        .animate-float {
            animation: float 6s ease-in-out infinite;
        }
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
	<script type="application/ld+json">
{
  "@context": "https://schema.org/",
  "@type": "JobPosting",
  "title": "<?= addslashes($vacancy['title']) ?>",
  "description": "<?= addslashes(strip_tags($vacancy['description'])) ?>",
  "datePosted": "<?= date('Y-m-d', strtotime($vacancy['date_posted'])) ?>",
  "employmentType": "FULL_TIME",
  "hiringOrganization": {
    "@type": "Organization",
    "name": "<?= addslashes($vacancy['company_name']) ?>"
  },
  "jobLocation": {
    "@type": "Place",
    "address": {
      "@type": "PostalAddress",
      "addressLocality": "Алматы",
      "addressCountry": "KZ"
    }
  }
}
</script>

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
            <select class="form-select" id="jobCategory" name="category" required>
              <option value="" disabled selected>Выберите категорию</option>
              <option value="IT">IT</option>
              <option value="Продажи">Продажи</option>
              <option value="Маркетинг">Маркетинг</option>
              <option value="Логистика">Логистика</option>
              <option value="Поддержка">Поддержка</option>
              <option value="Технологии">Технологии</option>
              <option value="Административная работа">Административная работа</option>
              <option value="Другое">Другое</option>
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
</body>
</html>