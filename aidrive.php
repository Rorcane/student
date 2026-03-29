<?php
$page = isset($_GET['page']) ? $_GET['page'] : 'panel';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8" />
  <title>TruWork</title>
		<link rel="icon" type="image/png" href="/favicon-96x96.png" sizes="96x96" />
	<link rel="icon" type="image/svg+xml" href="/favicon.svg" />
	<link rel="shortcut icon" href="/favicon.ico" />
	<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png" />
	<link rel="manifest" href="/site.webmanifest" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    .sidebar {
      min-height: 100vh;
      background-color: #f8f9fa;
      padding: 1rem;
      border-right: 1px solid #dee2e6;
    }
    
    .nav-link.active {
      background: #2A73CC !important;
      color: white !important;
    }
    
    .logo-main {
      width: 100px;
      padding: 1rem 0;
    }
  </style>
</head>
<body>

<div class="container-fluid">
  <div class="row">
    <!-- Левая панель -->
    <div class="col-12 col-md-2 sidebar">
      <a href="vacancies.php">
        <img src="logo2.png" alt="TruWork" class="logo-main">
      </a>
      
      <ul class="nav flex-column mb-5 mt-3">
        <li class="nav-item mb-2">
          <a class="nav-link <?= $page == 'panel' ? 'active' : '' ?>" 
             href="aidrive.php?page=panel">
            <i class="fas fa-home me-2"></i>Панель
          </a>
        </li>
        <li class="nav-item mb-2">
          <a class="nav-link <?= $page == 'tasks' ? 'active' : '' ?>" 
             href="aidrive.php?page=tasks">
            <i class="fas fa-tasks me-2"></i>Мои задачи
          </a>
        </li>
        <li class="nav-item mb-2">
          <a class="nav-link <?= $page == 'analytics' ? 'active' : '' ?>" 
             href="aidrive.php?page=analytics">
            <i class="fas fa-chart-line me-2"></i>Аналитика
          </a>
        </li>
      </ul>

      <ul class="nav flex-column mt-auto">
        <li class="nav-item mb-2">
          <a class="nav-link <?= $page == 'settings' ? 'active' : '' ?>" 
             href="aidrive.php?page=settings">
            <i class="fas fa-cog me-2"></i>Настройки
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-danger" href="logout.php">
            <i class="fas fa-sign-out-alt me-2"></i>Выйти
          </a>
        </li>
      </ul>
    </div>

    <!-- Основной контент -->
    <div class="col-md-10 content-area p-4">
      <!-- Верхняя панель -->
      <div class="d-flex align-items-center justify-content-between mb-4">
        <div class="d-flex gap-3 align-items-center">
          <div class="input-group" style="width: 300px;">
            <input type="text" class="form-control" placeholder="Поиск задач...">
            <button class="btn btn-outline-secondary">
              <i class="fas fa-filter"></i>
            </button>
          </div>
          <button class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Новая задача
          </button>
        </div>
        
        <?php if(isset($_COOKIE['user'])): ?>
          <div class="dropdown">
            <a href="#" class="btn btn-link text-dark" data-bs-toggle="dropdown">
              <i class="fas fa-user-circle fs-4"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="profile.php">Профиль</a></li>
              <li><a class="dropdown-item" href="settings.php">Настройки</a></li>
            </ul>
          </div>
        <?php endif; ?>
      </div>

      <!-- Контент страниц -->
      <?php
      switch($page) {
        case 'tasks':
          include 'sections/tasks.php';
          break;
        case 'analytics':
          include 'sections/analytics.php';
          break;
        case 'settings':
          include 'sections/settings.php';
          break;
        default:
          include 'sections/dashboard.php';
      }
      ?>
    </div>
  </div>
</div>

<!-- Футер -->
<footer class="bg-light py-4">
  <div class="container">
    <div class="row">
      <div class="col-md-4">
        <img src="logo.png" alt="TruWork" width="120">
        <p class="text-muted mt-2">AI-powered HR решения</p>
      </div>
      <div class="col-md-8">
        <!-- ... остальное содержимое футера ... -->
      </div>
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>