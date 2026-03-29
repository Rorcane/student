<?php

if (!isset($_COOKIE['user'])) {
  header('Location: login.html');
  exit();
}

?>

<!DOCTYPE html>

<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>HR TruWork</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
		<link rel="icon" type="image/png" href="/favicon-96x96.png" sizes="96x96" />
	<link rel="icon" type="image/svg+xml" href="/favicon.svg" />
	<link rel="shortcut icon" href="/favicon.ico" />
	<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png" />
	<link rel="manifest" href="/site.webmanifest" />
    <style>
        .dashboard-card {
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }

    .dashboard-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 12px rgba(0,0,0,0.1);
    }
    
    .stat-badge {
        background: linear-gradient(45deg, #2A73CC, #3AA0FF);
        color: white;
        padding: 8px 15px;
        border-radius: 20px;
    }
	:root {
  --primary-color: #2A73CC;
  --secondary-color: #F8F9FA;
}

.sidebar {
  min-height: 100vh;
  background-color: var(--secondary-color);
  padding: 1rem;
  border-right: 1px solid #dee2e6;
}

.nav-link {
  color: #333;
  border-radius: 8px;
  transition: all 0.3s ease;
}

.nav-link:hover {
  background-color: #e2e2e2;
  transform: translateX(5px);
}

.nav-link.active {
  background-color: var(--primary-color) !important;
  color: white !important;
}

.ai-badge {
  background: linear-gradient(45deg, #2A73CC, #3AA0FF);
  color: white;
  padding: 3px 8px;
  border-radius: 4px;
  font-size: 0.8em;
}

.vacancy-card {
  transition: transform 0.3s ease;
  border: none;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.vacancy-card:hover {
  transform: translateY(-5px);
}

.activity-feed {
  max-height: 500px;
  overflow-y: auto;
}
</style>
	
</head>
<body class="bg-light">

<!-- Навигационная панель -->

<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="vacancies.php">
            <img src="logo2.png" alt="TruWork" width="95" class="me-2">
        </a>

    <div class="d-flex align-items-center gap-3">
        <div class="dropdown">
            <a class="btn btn-link text-dark dropdown-toggle" 
               href="#" 
               data-bs-toggle="dropdown">
                <i class="fas fa-user-circle fs-4"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow">
                <li><a class="dropdown-item" href="profile.php">
                    <i class="fas fa-user me-2"></i>Профиль
                </a></li>
                <li><a class="dropdown-item" href="settings.php">
                    <i class="fas fa-cog me-2"></i>Настройки
                </a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="logout.php">
                    <i class="fas fa-sign-out-alt me-2"></i>Выйти
                </a></li>
            </ul>
        </div>
    </div>
</div>
```

</nav>

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
                    <a href="vacancy.php" class="nav-link">
                        <i class="fas fa-briefcase me-2"></i>Вакансии
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
                    <a href="HRlist.php" class="nav-link active">
                        <i class="fas fa-briefcase me-2"></i>HR
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a href="settings.php" class="nav-link">
                        <i class="fas fa-cog me-2"></i>Настройки
                    </a>
                </li>
            </ul>
        </div>
    </div>

    
  <!-- Offcanvas (для мобильных устройств) -->
  <div 
    class="offcanvas offcanvas-start" 
    tabindex="-1" 
    id="offcanvasSidebar" 
    aria-labelledby="offcanvasSidebarLabel"
  >
    <div class="offcanvas-header">
      <h5 class="offcanvas-title" id="offcanvasSidebarLabel">Меню</h5>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
      <ul class="nav flex-column">
        <li class="nav-item mb-2">
          <a class="nav-link active" href="index.php">Главная</a>
        </li>
        <li class="nav-item mb-2">
          <a class="nav-link" href="#">Сервисы</a>
        </li>
        <li class="nav-item mb-2">
          <a class="nav-link" href="#">Портфолио</a>
        </li>
        <li class="nav-item mb-2">
          <a class="nav-link" href="#">Связи</a>
        </li>
        <li class="nav-item mb-2">
          <a class="nav-link" href="#">Аккаунт</a>
        </li>
        <li class="nav-item mt-4">
          <a class="nav-link text-danger" href="login.html">Выйти</a>
        </li>
      </ul>
    </div>
  </div>
  
  <!-- Центральная часть -->
  <div class="col-md-7 content-area">
    <div class="container-fluid">
      
      <!-- Smart Recruitment -->
      <div class="bg-light p-3 mb-3 rounded">
        <h4>Умный найм (Smart Recruitment)</h4>
        <div class="d-flex flex-wrap gap-2 mt-3">
          <button class="btn btn-outline-secondary">Оценка ID</button>
          <button class="btn btn-primary">Проверить</button>
          <button class="btn btn-outline-secondary">Клиентская база HR</button>
          <button class="btn btn-outline-secondary">Обновить</button>
        </div>
        <p class="mt-3">Управляйте своей командой эффективно</p>
		<table class="table table-striped">
		  <thead>
			<tr>
			  <th>ID</th><th>Логин</th><th>Email</th><th>Роль</th><th>Дата регистрации</th>
			</tr>
		  </thead>
		  <tbody>
			<?php foreach ($hrs as $h): ?>
			  <tr>
				<td><?= $h['id'] ?></td>
				<td><?= htmlspecialchars($h['username'], ENT_QUOTES) ?></td>
				<td><?= htmlspecialchars($h['email'],    ENT_QUOTES) ?></td>
				<td><?= htmlspecialchars($h['role'],     ENT_QUOTES) ?></td>
				<td><?= formatDateTime($h['created_at']) ?></td>
			  </tr>
			<?php endforeach; ?>
		  </tbody>
		</table>

        <div class="d-flex gap-3">
          <div>
            <img src="https://via.placeholder.com/50" class="rounded-circle" alt="Профи" />
            <p class="small text-center">Профи</p>
          </div>
          <div>
            <img src="https://via.placeholder.com/50" class="rounded-circle" alt="Профи" />
            <p class="small text-center">Профи</p>
          </div>
          <div>
            <img src="https://via.placeholder.com/50" class="rounded-circle" alt="Профи" />
            <p class="small text-center">Профи</p>
          </div>
        </div>
        <button class="btn btn-outline-primary mt-2">Уведомить</button>
      </div>
      
      <!-- Profile View (график) -->
      <div class="bg-light p-3 mb-3 rounded">
        <div class="d-flex justify-content-between align-items-center">
          <h5>Просмотры профиля</h5>
          <input 
            type="text" 
            class="form-control form-control-sm" 
            placeholder="Поиск по дате" 
            style="width: 150px;"
          />
        </div>
        <!-- «График» (заглушка) -->
        <div class="mt-3" style="height: 200px; background: #e1ecf4;">
          <p class="text-center pt-5">График просмотров (макет)</p>
        </div>
      </div>
      
      <!-- Product ideas -->
      <div class="bg-light p-3 mb-3 rounded">
        <h5>Идеи продуктов</h5>
        <p class="text-muted">Ищете новые концепции продуктов?</p>
        <div class="row g-2">
          <div class="col">
            <button class="btn btn-outline-secondary w-100">
              Экспертные предложения <br />
              <small>Примерное время:</small>
            </button>
          </div>
          <div class="col">
            <button class="btn btn-outline-secondary w-100">
              Экспертные советы <br />
              <small>Примерное время:</small>
            </button>
          </div>
          <div class="col">
            <button class="btn btn-outline-secondary w-100">
              Полезные подсказки <br />
              <small>Примерное время:</small>
            </button>
          </div>
          <div class="col">
            <button class="btn btn-outline-secondary w-100">
              Внутренние инсайты <br />
              <small>Примерное время:</small>
            </button>
          </div>
        </div>
      </div>
      
      <!-- Expand your clientele -->
      <div class="bg-light p-3 rounded">
        <h5>Расширьте базу клиентов</h5>
        <p>Узнайте простые стратегии для привлечения большего числа покупателей.</p>
        <div class="d-flex gap-2 flex-wrap">
          <button class="btn btn-outline-primary">Подключиться к сети</button>
          <button class="btn btn-outline-primary">Подключиться к сети</button>
          <button class="btn btn-outline-primary">Подключиться к сети</button>
        </div>
      </div>
      
    </div>
  </div>
  
  <!-- Правая колонка -->
  <div class="col-md-3 right-column">
    <!-- Лента активности -->
    <div class="mb-4">
      <h5 class="fw-bold">Лента активности</h5>
      <div class="mt-3">
        <p><strong>Иван Петров</strong> по проекту «X»<br />
        <small class="text-muted">2 ч назад — Отличное сотрудничество</small></p>
      </div>
      <div class="mt-3">
        <p><strong>Анна Смирнова</strong> по проекту «Y»<br />
        <small class="text-muted">1 день назад — Впечатляющая работа, браво</small></p>
      </div>
      <div class="mt-3">
        <p><strong>Алиса Иванова</strong> по проекту «Z»<br />
        <small class="text-muted">5 ч назад — Очень рекомендую</small></p>
      </div>
      <button class="btn btn-sm btn-outline-primary mt-2">Подробнее</button>
    </div>
    
    <!-- Топовые предложения -->
    <div class="mb-4 bg-white p-3 rounded">
      <h6>Топовые предложения</h6>
      <ul class="list-unstyled mt-2">
        <li class="d-flex justify-content-between align-items-center">
          <span>Товар – 3600</span>
          <span>199,99₸</span>
          <a href="#" class="btn btn-sm btn-outline-secondary">Активировать</a>
        </li>
        <li class="d-flex justify-content-between align-items-center mt-2">
          <span>Товар – 3600</span>
          <span>199,99₸</span>
          <a href="#" class="btn btn-sm btn-outline-secondary">Активировать</a>
        </li>
        <li class="d-flex justify-content-between align-items-center mt-2">
          <span>Товар – 3600</span>
          <span>199,99₸</span>
          <a href="#" class="btn btn-sm btn-outline-secondary">Активировать</a>
        </li>
      </ul>
      <button class="btn btn-sm btn-primary w-100 mt-3">Все продукты</button>
    </div>
    
    <!-- Запросы на возврат -->
    <div class="bg-white p-3 rounded">
      <h6>Запросы на возврат</h6>
      <p class="text-muted" style="font-size: 0.9rem;">
        Требуется действие по 52 запросам на возврат, в том числе 8 новых.
      </p>
      <button class="btn btn-outline-danger w-100">Просмотр запросов</button>
    </div>
  </div>
</div>

  </div>

  <!-- Bootstrap JS -->

  <script 
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"
  ></script>

</body>
</html>
