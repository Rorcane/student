<?php
session_start();

if (!isset($_COOKIE['user'])) {
    header('Location: login.html');
    exit();
}

$uploadMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['profession']) && $_POST['profession'] !== 'Programmer') {
    if (isset($_FILES['certificate']) && $_FILES['certificate']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/uploads';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $safeName = preg_replace('/[^A-Za-z0-9._-]/', '_', basename($_FILES['certificate']['name']));
        $target = $uploadDir . '/' . time() . '_' . $safeName;

        if (move_uploaded_file($_FILES['certificate']['tmp_name'], $target)) {
            $uploadMessage = 'Файл успешно загружен.';
        } else {
            $uploadMessage = 'Не удалось сохранить файл.';
        }
    } else {
        $uploadMessage = 'Выберите файл для загрузки.';
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Проверка навыков | TruWork</title>
  <link rel="icon" type="image/png" href="/favicon-96x96.png" sizes="96x96">
  <link rel="icon" type="image/svg+xml" href="/favicon.svg">
  <link rel="shortcut icon" href="/favicon.ico">
  <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
  <link rel="manifest" href="/site.webmanifest">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/public-site.css">
</head>
<body>
  <header class="site-header">
    <div class="site-shell site-header__inner">
      <a class="brand" href="index.php"><img src="logo2.png" alt="TruWork"></a>
      <nav class="site-nav" aria-label="Основная навигация">
        <a href="index.php">Главная</a>
        <a href="vacancies.php">Вакансии</a>
        <a href="vacancy.php">Опубликовать</a>
        <a href="faq.html">FAQ</a>
        <a href="support.html">Поддержка</a>
      </nav>
      <div class="header-actions">
        <a class="button-primary" href="profile.php"><?= htmlspecialchars($_COOKIE['user']) ?></a>
      </div>
    </div>
  </header>

  <main class="page-main">
    <div class="site-shell dashboard-layout">
      <aside class="dashboard-sidebar">
        <h2 class="sidebar-title">Кабинет</h2>
        <nav class="sidebar-nav">
          <a href="profile.php">Профиль</a>
          <a href="settings.php">Настройки</a>
          <a href="security.php">Безопасность</a>
          <a class="is-active" href="IDM.php">Проверка навыков</a>
          <a href="logout.php">Выйти</a>
        </nav>
      </aside>

      <section class="dashboard-content">
        <div class="dashboard-hero">
          <div class="dashboard-hero__text">
            <h1 class="section-title">Проверка навыков</h1>
            <p class="section-subtitle">Выберите профессию. Для некоторых направлений доступен тест, для остальных можно загрузить подтверждающий документ.</p>
          </div>
        </div>

        <?php if ($uploadMessage !== ''): ?>
          <div class="alert alert-info"><?= htmlspecialchars($uploadMessage) ?></div>
        <?php endif; ?>

        <div class="grid grid-2">
          <article class="info-card">
            <h3>Выбор профессии</h3>
            <div class="form-stack">
              <div>
                <label class="label" for="profession">Профессия</label>
                <select id="profession" class="input">
                  <option value="">Выберите профессию</option>
                  <option value="Stylist">Stylist</option>
                  <option value="Designer">Designer</option>
                  <option value="Programmer">Programmer</option>
                  <option value="Translator">Translator</option>
                </select>
              </div>
            </div>

            <form id="uploadForm" method="post" enctype="multipart/form-data" class="form-stack" hidden>
              <input type="hidden" name="profession" id="profField">
              <div>
                <label class="label" for="certificate">Подтверждающий документ</label>
                <input class="input" type="file" name="certificate" id="certificate" required>
              </div>
              <button type="submit" class="button-primary">Загрузить документ</button>
            </form>

            <div id="testBlock" class="form-stack" hidden>
              <div>
                <label class="label" for="level">Уровень</label>
                <select id="level" class="input">
                  <option value="junior">Junior</option>
                  <option value="middle">Middle</option>
                  <option value="senior">Senior</option>
                </select>
              </div>
              <button id="startBtn" class="button-primary" type="button">Начать тест</button>
            </div>
          </article>

          <article class="info-card">
            <h3>Тестирование</h3>
            <div id="testArea" hidden>
              <div class="stack-actions" style="justify-content:space-between;margin-bottom:16px;">
                <div><strong>Время:</strong> <span id="timeLeft">10</span>с</div>
                <button class="button-secondary" type="button" onclick="finishTest(true)">Остановить</button>
              </div>
              <div id="questionsContainer"></div>
              <div style="margin-top:18px;background:#e5edf6;border-radius:999px;height:10px;overflow:hidden;">
                <div id="progressBar" style="height:100%;width:0;background:linear-gradient(135deg,#1d4ed8,#0f766e);"></div>
              </div>
            </div>

            <div id="results" hidden>
              <div class="stat-grid">
                <article class="stat-card">
                  <strong id="correctCount">0</strong>
                  <span>Правильных ответов</span>
                </article>
                <article class="stat-card">
                  <strong id="wrongCount">0</strong>
                  <span>Ошибок</span>
                </article>
              </div>
              <div style="margin-top:18px;">
                <button class="button-primary" type="button" onclick="location.reload()">Начать заново</button>
              </div>
            </div>

            <div id="testPlaceholder">
              <p class="muted" style="margin:0;">После выбора профессии здесь появится тест или форма загрузки документа.</p>
            </div>
          </article>
        </div>
      </section>
    </div>
  </main>

  <script>
    const questions = [
      { question: "Что такое HTML?", options: ["Язык программирования", "Язык разметки", "База данных"], correct: 1 },
      { question: "Как объявить переменную в JavaScript?", options: ["var", "let", "const", "Все варианты"], correct: 3 },
      { question: "Что делает оператор ===?", options: ["Присваивает", "Сравнивает без приведения типов", "Склеивает строки"], correct: 1 },
      { question: "Какой тег подключает JavaScript?", options: ["script", "js", "javascript"], correct: 0 },
      { question: "Что такое API?", options: ["Интерфейс взаимодействия", "База данных", "Редактор кода"], correct: 0 }
    ];

    let currentQuestion = 0;
    let correct = 0;
    let wrong = 0;
    let timer = null;
    const TIME_PER_QUESTION = 10;

    function renderQuestion() {
      const q = questions[currentQuestion];
      let html = `<h4 style="margin-top:0;">${q.question}</h4>`;
      q.options.forEach((opt, idx) => {
        html += `<label style="display:block;padding:12px 14px;border:1px solid #d8e2ee;border-radius:14px;margin-bottom:10px;cursor:pointer;"><input type="radio" name="answer" value="${idx}" style="margin-right:10px;">${opt}</label>`;
      });
      document.getElementById('questionsContainer').innerHTML = html;
    }

    function startTimer() {
      clearInterval(timer);
      let time = TIME_PER_QUESTION;
      document.getElementById('timeLeft').textContent = time;
      document.getElementById('progressBar').style.width = '0%';
      timer = setInterval(() => {
        time -= 1;
        document.getElementById('timeLeft').textContent = time;
        document.getElementById('progressBar').style.width = `${100 - (time / TIME_PER_QUESTION) * 100}%`;
        if (time <= 0) {
          wrong += 1;
          nextQuestion();
        }
      }, 1000);
    }

    function nextQuestion() {
      clearInterval(timer);
      if (currentQuestion < questions.length - 1) {
        currentQuestion += 1;
        renderQuestion();
        startTimer();
      } else {
        finishTest(false);
      }
    }

    function finishTest(userStopped) {
      clearInterval(timer);
      if (userStopped) {
        wrong += Math.max(0, questions.length - currentQuestion - 1);
      }
      document.getElementById('testArea').hidden = true;
      document.getElementById('results').hidden = false;
      document.getElementById('correctCount').textContent = correct;
      document.getElementById('wrongCount').textContent = wrong;
    }

    document.addEventListener('change', function (event) {
      if (event.target.name === 'answer') {
        if (Number(event.target.value) === questions[currentQuestion].correct) {
          correct += 1;
        } else {
          wrong += 1;
        }
        nextQuestion();
      }
    });

    document.getElementById('startBtn').addEventListener('click', function () {
      document.getElementById('testPlaceholder').hidden = true;
      document.getElementById('results').hidden = true;
      document.getElementById('testArea').hidden = false;
      currentQuestion = 0;
      correct = 0;
      wrong = 0;
      renderQuestion();
      startTimer();
    });

    document.getElementById('profession').addEventListener('change', function (event) {
      const profession = event.target.value;
      document.getElementById('profField').value = profession;
      document.getElementById('testPlaceholder').hidden = false;
      document.getElementById('results').hidden = true;
      document.getElementById('testArea').hidden = true;
      document.getElementById('uploadForm').hidden = profession === '' || profession === 'Programmer';
      document.getElementById('testBlock').hidden = profession !== 'Programmer';
    });
  </script>
</body>
</html>
