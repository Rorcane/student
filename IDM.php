<?php
require_once 'config.php';
session_start();

if (!isset($_COOKIE['user'])) {
    header('Location: login.html');
    exit();
}

$lang = $siteLang ?? 'ru';
$isKz = $lang === 'kk';
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
            $uploadMessage = $isKz ? 'Файл сәтті жүктелді.' : 'Файл успешно загружен.';
        } else {
            $uploadMessage = $isKz ? 'Файлды сақтау мүмкін болмады.' : 'Не удалось сохранить файл.';
        }
    } else {
        $uploadMessage = $isKz ? 'Жүктеу үшін файл таңдаңыз.' : 'Выберите файл для загрузки.';
    }
}

$t = [
    'title' => $isKz ? 'Дағдыларды тексеру | TruWork' : 'Проверка навыков | TruWork',
    'home' => $isKz ? 'Басты бет' : 'Главная',
    'vacancies' => $isKz ? 'Вакансиялар' : 'Вакансии',
    'publish' => $isKz ? 'Жариялау' : 'Опубликовать',
    'about' => $isKz ? 'Біз туралы' : 'О нас',
    'faq' => 'FAQ',
    'support' => $isKz ? 'Қолдау' : 'Поддержка',
    'profile' => $isKz ? 'Профиль' : 'Профиль',
    'settings' => $isKz ? 'Баптаулар' : 'Настройки',
    'security' => $isKz ? 'Қауіпсіздік' : 'Безопасность',
    'skills' => $isKz ? 'Дағдыларды тексеру' : 'Проверка навыков',
    'logout' => $isKz ? 'Шығу' : 'Выйти',
    'heading' => $isKz ? 'Дағдыларды тексеру' : 'Проверка навыков',
    'subtitle' => $isKz
        ? 'Мамандықты таңдаңыз. Кей бағыттар үшін тест бар, ал қалғандарына құжат жүктеуге болады.'
        : 'Выберите профессию. Для некоторых направлений доступен тест, для остальных можно загрузить подтверждающий документ.',
    'profession_block' => $isKz ? 'Мамандық таңдау' : 'Выбор профессии',
    'profession' => $isKz ? 'Мамандық' : 'Профессия',
    'profession_placeholder' => $isKz ? 'Мамандықты таңдаңыз' : 'Выберите профессию',
    'document' => $isKz ? 'Растайтын құжат' : 'Подтверждающий документ',
    'upload' => $isKz ? 'Құжатты жүктеу' : 'Загрузить документ',
    'level' => $isKz ? 'Деңгей' : 'Уровень',
    'start_test' => $isKz ? 'Тестті бастау' : 'Начать тест',
    'testing' => $isKz ? 'Тестілеу' : 'Тестирование',
    'time' => $isKz ? 'Уақыт' : 'Время',
    'stop' => $isKz ? 'Тоқтату' : 'Остановить',
    'correct' => $isKz ? 'Дұрыс жауаптар' : 'Правильных ответов',
    'wrong' => $isKz ? 'Қателер' : 'Ошибок',
    'restart' => $isKz ? 'Қайта бастау' : 'Начать заново',
    'placeholder' => $isKz
        ? 'Мамандықты таңдағаннан кейін мұнда тест немесе құжат жүктеу формасы пайда болады.'
        : 'После выбора профессии здесь появится тест или форма загрузки документа.',
    'policy' => $isKz ? 'Құпиялық саясаты' : 'Политика конфиденциальности',
    'terms' => $isKz ? 'Пайдалану шарттары' : 'Условия использования',
    'footer_note' => $isKz
        ? 'Дағдыларды тексеру бөлімі де енді жаңа кабинет стиліне толық сай.'
        : 'Раздел проверки навыков теперь тоже полностью приведен к новому стилю кабинета.',
];

$paths = [
    'index' => $isKz ? 'index_kk.php' : 'index.php',
    'vacancies' => $isKz ? 'vacancies_kk.php' : 'vacancies.php',
    'publish' => $isKz ? 'vacancy_kk.php' : 'vacancy.php',
    'about' => $isKz ? 'about_kk.html' : 'about.html',
    'faq' => $isKz ? 'faq_kk.html' : 'faq.html',
    'support' => $isKz ? 'support_kk.html' : 'support.html',
    'profile' => $isKz ? 'profile_kk.php' : 'profile.php',
    'settings' => $isKz ? 'settings_kk.php' : 'settings.php',
    'security' => $isKz ? 'security_kk.php' : 'security.php',
    'tests' => $isKz ? 'IDM_kk.php' : 'IDM.php',
    'policy' => $isKz ? 'policy_kk.html' : 'policy.html',
    'terms' => $isKz ? 'terms_kk.html' : 'terms.html',
];
?>
<!DOCTYPE html>
<html lang="<?= $isKz ? 'kk' : 'ru' ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($t['title']) ?></title>
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
      <a class="brand" href="<?= htmlspecialchars($paths['index']) ?>"><img src="logo2.png" alt="TruWork"></a>
      <nav class="site-nav" aria-label="<?= $isKz ? 'Негізгі навигация' : 'Основная навигация' ?>">
        <a href="<?= htmlspecialchars($paths['index']) ?>"><?= htmlspecialchars($t['home']) ?></a>
        <a href="<?= htmlspecialchars($paths['vacancies']) ?>"><?= htmlspecialchars($t['vacancies']) ?></a>
        <a href="<?= htmlspecialchars($paths['publish']) ?>"><?= htmlspecialchars($t['publish']) ?></a>
        <a href="<?= htmlspecialchars($paths['about']) ?>"><?= htmlspecialchars($t['about']) ?></a>
        <a href="<?= htmlspecialchars($paths['faq']) ?>"><?= htmlspecialchars($t['faq']) ?></a>
        <a href="<?= htmlspecialchars($paths['support']) ?>"><?= htmlspecialchars($t['support']) ?></a>
      </nav>
      <div class="header-actions">
        <div class="lang-switch">
          <?php if ($isKz): ?>
            <a href="IDM.php">RU</a>
            <span class="is-active">KZ</span>
          <?php else: ?>
            <span class="is-active">RU</span>
            <a href="IDM_kk.php">KZ</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </header>

  <main class="page-main">
    <div class="site-shell dashboard-layout">
      <aside class="dashboard-sidebar">
        <h2 class="sidebar-title"><?= htmlspecialchars($isKz ? 'Кабинет' : 'Кабинет') ?></h2>
        <nav class="sidebar-nav">
          <a href="<?= htmlspecialchars($paths['profile']) ?>"><?= htmlspecialchars($t['profile']) ?></a>
          <a href="<?= htmlspecialchars($paths['settings']) ?>"><?= htmlspecialchars($t['settings']) ?></a>
          <a href="<?= htmlspecialchars($paths['security']) ?>"><?= htmlspecialchars($t['security']) ?></a>
          <a class="is-active" href="<?= htmlspecialchars($paths['tests']) ?>"><?= htmlspecialchars($t['skills']) ?></a>
          <a href="logout.php"><?= htmlspecialchars($t['logout']) ?></a>
        </nav>
      </aside>

      <section class="dashboard-content">
        <div class="dashboard-hero">
          <div class="dashboard-hero__text">
            <h1 class="section-title"><?= htmlspecialchars($t['heading']) ?></h1>
            <p class="section-subtitle"><?= htmlspecialchars($t['subtitle']) ?></p>
          </div>
        </div>

        <?php if ($uploadMessage !== ''): ?>
          <div class="alert alert-info"><?= htmlspecialchars($uploadMessage) ?></div>
        <?php endif; ?>

        <div class="grid grid-2">
          <article class="info-card">
            <h3><?= htmlspecialchars($t['profession_block']) ?></h3>
            <div class="form-stack">
              <div>
                <label class="label" for="profession"><?= htmlspecialchars($t['profession']) ?></label>
                <select id="profession" class="input">
                  <option value=""><?= htmlspecialchars($t['profession_placeholder']) ?></option>
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
                <label class="label" for="certificate"><?= htmlspecialchars($t['document']) ?></label>
                <input class="input" type="file" name="certificate" id="certificate" required>
              </div>
              <button type="submit" class="button-primary"><?= htmlspecialchars($t['upload']) ?></button>
            </form>

            <div id="testBlock" class="form-stack" hidden>
              <div>
                <label class="label" for="level"><?= htmlspecialchars($t['level']) ?></label>
                <select id="level" class="input">
                  <option value="junior">Junior</option>
                  <option value="middle">Middle</option>
                  <option value="senior">Senior</option>
                </select>
              </div>
              <button id="startBtn" class="button-primary" type="button"><?= htmlspecialchars($t['start_test']) ?></button>
            </div>
          </article>

          <article class="info-card">
            <h3><?= htmlspecialchars($t['testing']) ?></h3>
            <div id="testArea" hidden>
              <div class="stack-actions" style="justify-content:space-between;margin-bottom:16px;">
                <div><strong><?= htmlspecialchars($t['time']) ?>:</strong> <span id="timeLeft">10</span>с</div>
                <button class="button-secondary" type="button" onclick="finishTest(true)"><?= htmlspecialchars($t['stop']) ?></button>
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
                  <span><?= htmlspecialchars($t['correct']) ?></span>
                </article>
                <article class="stat-card">
                  <strong id="wrongCount">0</strong>
                  <span><?= htmlspecialchars($t['wrong']) ?></span>
                </article>
              </div>
              <div style="margin-top:18px;">
                <button class="button-primary" type="button" onclick="location.reload()"><?= htmlspecialchars($t['restart']) ?></button>
              </div>
            </div>

            <div id="testPlaceholder">
              <p class="muted" style="margin:0;"><?= htmlspecialchars($t['placeholder']) ?></p>
            </div>
          </article>
        </div>
      </section>
    </div>
  </main>

  <footer class="site-footer">
    <div class="site-shell site-footer__panel">
      <div>
        <strong>TruWork</strong>
        <div class="footer-note"><?= htmlspecialchars($t['footer_note']) ?></div>
      </div>
      <div class="footer-links">
        <a href="<?= htmlspecialchars($paths['policy']) ?>"><?= htmlspecialchars($t['policy']) ?></a>
        <a href="<?= htmlspecialchars($paths['terms']) ?>"><?= htmlspecialchars($t['terms']) ?></a>
        <a href="<?= htmlspecialchars($paths['support']) ?>"><?= htmlspecialchars($t['support']) ?></a>
      </div>
    </div>
  </footer>

  <script>
    const questions = [
      {
        question: <?= json_encode($isKz ? 'HTML деген не?' : 'Что такое HTML?') ?>,
        options: <?= json_encode($isKz ? ['Бағдарламалау тілі', 'Белгілеу тілі', 'Дерекқор'] : ['Язык программирования', 'Язык разметки', 'База данных']) ?>,
        correct: 1
      },
      {
        question: <?= json_encode($isKz ? 'JavaScript тілінде айнымалыны қалай жариялайды?' : 'Как объявить переменную в JavaScript?') ?>,
        options: <?= json_encode($isKz ? ['var', 'let', 'const', 'Барлығы дұрыс'] : ['var', 'let', 'const', 'Все варианты']) ?>,
        correct: 3
      },
      {
        question: <?= json_encode($isKz ? '=== операторы не істейді?' : 'Что делает оператор ===?') ?>,
        options: <?= json_encode($isKz ? ['Меншіктейді', 'Түрлендірмей салыстырады', 'Жолдарды біріктіреді'] : ['Присваивает', 'Сравнивает без приведения типов', 'Склеивает строки']) ?>,
        correct: 1
      },
      {
        question: <?= json_encode($isKz ? 'JavaScript қай тег арқылы қосылады?' : 'Какой тег подключает JavaScript?') ?>,
        options: ['script', 'js', 'javascript'],
        correct: 0
      },
      {
        question: <?= json_encode($isKz ? 'API деген не?' : 'Что такое API?') ?>,
        options: <?= json_encode($isKz ? ['Өзара әрекеттесу интерфейсі', 'Дерекқор', 'Код редакторы'] : ['Интерфейс взаимодействия', 'База данных', 'Редактор кода']) ?>,
        correct: 0
      }
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
