<?php
// skill_test.php — страница подтверждения навыков
session_start();
if (!isset($_COOKIE['user'])) {
    header('Location: login.html');
    exit();
}

// Обработка загрузки диплома для нетестируемых профессий
$uploadMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['profession']) && $_POST['profession'] !== 'Programmer') {
    if (isset($_FILES['certificate']) && $_FILES['certificate']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $name = basename($_FILES['certificate']['name']);
        $target = $uploadDir . time() . "_" . $name;
        if (move_uploaded_file($_FILES['certificate']['tmp_name'], $target)) {
            $uploadMessage = 'Файл успешно загружен.';
        } else {
            $uploadMessage = 'Ошибка при сохранении файла.';
        }
    } else {
        $uploadMessage = 'Файл не был загружен.';
    }
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
  <title>Подтвердить навыки</title>
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
  </nav><!-- Навигационная панель -->
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
        <div class="col-md-2 bg-white vh-100 border-end p-3">

            <ul class="nav flex-column">
                <li class="nav-item mb-2">
                    <a href="profile.php" class="nav-link">
                        <i class="fas fa-home me-2"></i>Главная
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a href="Uslugi.php" class="nav-link">
                        <i class="fas fa-handshake me-2"></i>Услуги
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a href="portfol.php" class="nav-link">
                        <i class="fas fa-briefcase me-2"></i>Портфолио
                    </a>
                </li>
        <li class="nav-item mb-2">
                    <a href="IDM.php" class="nav-link active">
                        <i class="fas fa-file-alt me-2"></i>Подтвердить навыки
                    </a>
                </li>
				<li class="nav-item mb-2">
                    <a href="settings.php" class="nav-link">
                        <i class="fas fa-cog me-2"></i>Аккаунт
                    </a>
                </li>
				<li class="nav-item mb-2">
                    <a href="logout.php" class="nav-link text-danger">
                        <i class="fas fa-sign-out-alt me-2"></i>Выход
                    </a>
                </li>
            </ul>
        </div>
    <div class="col-md-10 main-content">
      <div class="card p-4">
        <h4 class="mb-4">Подтверждение навыков</h4>
        <?php if ($uploadMessage): ?>
          <div class="alert alert-info"><?= htmlspecialchars($uploadMessage) ?></div>
        <?php endif; ?>
        <!-- Форма выбора профессии -->
        <div class="mb-4">
          <label for="profession" class="form-label">Выберите профессию:</label>
          <select id="profession" class="form-select">
            <option value="">-- Выберите --</option>
            <option>Stylist</option>
            <option>Designer</option>
            <option>Programmer</option>
            <option>Translator</option>
          </select>
        </div>
        <!-- Блок загрузки файла -->
        <form id="uploadForm" method="post" enctype="multipart/form-data" class="d-none">
          <input type="hidden" name="profession" id="profField">
          <div class="mb-3">
            <label for="certificate" class="form-label">Прикрепите документ (диплом, сертификат):</label>
            <input type="file" class="form-control" name="certificate" id="certificate" required>
          </div>
          <button type="submit" class="btn btn-primary">Загрузить документ</button>
        </form>
        <!-- Блок теста для программиста -->
        <div id="testBlock" class="d-none">
          <div class="mb-3">
            <label for="level" class="form-label">Уровень:</label>
            <select id="level" class="form-select mb-3">
              <option value="junior">Junior</option>
              <option value="middle">Middle</option>
              <option value="senior">Senior</option>
            </select>
          </div>
          <button id="startBtn" class="btn btn-success">Начать тест</button>
        </div>
        <!-- Тестирование -->
        <div id="testArea" class="d-none mt-4">
          <div class="d-flex justify-content-between mb-3">
            <div class="timer">Время: <span id="timeLeft">10</span>с</div>
            <button class="btn btn-sm btn-danger" onclick="finishTest(true)">Остановить</button>
          </div>
          <div id="questionsContainer"></div>
          <div class="progress mb-3"><div class="progress-bar" id="progressBar"></div></div>
        </div>
        <div id="results" class="d-none mt-3">
          <h5>Результаты:</h5>
          <div class="alert alert-success">Правильно: <span id="correctCount">0</span></div>
          <div class="alert alert-danger">Неправильно: <span id="wrongCount">0</span></div>
          <button class="btn btn-primary" onclick="location.reload()">Начать заново</button>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
// Полный массив вопросов для программиста
const questions = [
    { question: "Что такое HTML?", options: ["Язык программирования", "Язык разметки", "База данных"], correct: 1 },
    { question: "Как объявить переменную?", options: ["var x", "let x", "const x", "Все варианты"], correct: 3 },
    { question: "Что делает оператор '==='?", options: ["Присваивание", "Сравнение без приведения типа", "Логическое И"], correct: 1 },
    { question: "Как создать массив?", options: ["new Array()", "[]", "Оба варианта"], correct: 2 },
    { question: "Что такое CSS?", options: ["Язык стилей", "Препроцессор", "Фреймворк"], correct: 0 },
    { question: "Что такое Git?", options: ["Язык программирования", "Система контроля версий", "База данных"], correct: 1 },
    { question: "Как подключить JavaScript?", options: ["<script>", "<javascript>", "<js>"], correct: 0 },
    { question: "Что такое API?", options: ["Язык программирования", "Интерфейс взаимодействия", "Библиотека"], correct: 1 },
    { question: "Что выведет console.log(typeof null)?", options: ["null", "object", "undefined"], correct: 1 },
    { question: "Какой метод преобразует JSON?", options: ["JSON.parse()", "JSON.stringify()", "Оба варианта"], correct: 2 }
];

let currentQuestion = 0, correct = 0, wrong = 0, timer;
const TIME_PER_QUESTION = 10;

function startTest() {
    document.getElementById('testBlock').classList.add('d-none');
    document.getElementById('testArea').classList.remove('d-none');
    loadQuestion(); startTimer();
}

function loadQuestion() {
    const q = questions[currentQuestion];
    let html = `<h5>${q.question}</h5>`;
    q.options.forEach((opt, idx) => {
        html += `<div><input type="radio" name="answer" id="opt${idx}"><label class="option-label" for="opt${idx}">${opt}</label></div>`;
    });
    document.getElementById('questionsContainer').innerHTML = html;
}

function startTimer() {
    let time = TIME_PER_QUESTION;
    document.getElementById('progressBar').style.width = '0%';
    timer = setInterval(() => {
        time--;
        document.getElementById('timeLeft').textContent = time;
        document.getElementById('progressBar').style.width = `${100 - (time / TIME_PER_QUESTION * 100)}%`;
        if (time <= 0) { wrong++; nextQuestion(); }
    }, 1000);
}

function nextQuestion() {
    clearInterval(timer);
    if (currentQuestion < questions.length - 1) {
        currentQuestion++; loadQuestion(); startTimer();
    } else {
        finishTest();
    }
}

function finishTest(userStopped = false) {
    clearInterval(timer);
    if (userStopped) wrong += questions.length - currentQuestion - 1;
    document.getElementById('testArea').classList.add('d-none');
    document.getElementById('results').classList.remove('d-none');
    document.getElementById('correctCount').textContent = correct;
    document.getElementById('wrongCount').textContent = wrong;
}

document.addEventListener('change', (e) => {
    if (e.target.name === 'answer') {
        const selected = parseInt(e.target.id.replace('opt', ''));
        if (questions[currentQuestion].correct === selected) correct++;
        else wrong++;
        nextQuestion();
    }
});

// Показываем форму загрузки или тест в зависимости от выбора профессии
const profSelect = document.getElementById('profession');
profSelect.addEventListener('change', e => {
    const prof = e.target.value;
    document.getElementById('uploadForm').classList.toggle('d-none', prof === 'Programmer' || prof === '');
    document.getElementById('testBlock').classList.toggle('d-none', prof !== 'Programmer');
    document.getElementById('profField').value = prof;
});

// Старт теста по кнопке
document.getElementById('startBtn')?.addEventListener('click', startTest);
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>

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
</body>
</html>