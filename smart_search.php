<?php
require_once 'config.php';
session_start();

$lang = $siteLang ?? 'ru';
$isKz = $lang === 'kk';

$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
if ($basePath === '/' || $basePath === '\\' || $basePath === '.') {
    $basePath = '';
}

$user = null;
if (isset($_COOKIE['user'])) {
    $uStmt = $pdo->prepare("SELECT id, role FROM users WHERE username = ? LIMIT 1");
    $uStmt->execute([$_COOKIE['user']]);
    $user = $uStmt->fetch(PDO::FETCH_ASSOC);
}
$myId = $user['id'] ?? null;

if (isset($_GET['clear'])) {
    unset($_SESSION['smart_search_html'], $_SESSION['smart_search_success']);
    header('Location: ' . ($isKz ? 'smart_search_kk.php' : 'smart_search.php'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_analysis_id']) && $myId) {
    $delete = $pdo->prepare("DELETE FROM analyses WHERE id = ? AND user_id = ?");
    $delete->execute([(int) $_POST['delete_analysis_id'], $myId]);
    header('Location: ' . ($isKz ? 'smart_search_kk.php' : 'smart_search.php'));
    exit;
}

$errors = [];
$success = '';
$searchHtml = '';

function extract_text_from_docx(string $path): string
{
    $zip = new ZipArchive();
    if ($zip->open($path) !== true) {
        return '';
    }

    $index = $zip->locateName('word/document.xml');
    if ($index === false) {
        $zip->close();
        return '';
    }

    $data = $zip->getFromIndex($index);
    $zip->close();
    $data = preg_replace('/<(?:[^>]+)>/u', ' ', (string) $data);
    return trim((string) preg_replace('/\s+/u', ' ', (string) $data));
}

function extract_text_from_file(string $path): string
{
    $extension = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));
    if ($extension === 'docx') {
        return extract_text_from_docx($path);
    }
    if (is_file($path)) {
        return trim((string) @file_get_contents($path));
    }
    return '';
}

function extract_keywords(string $text, int $max = 10): array
{
    $text = mb_strtolower($text, 'UTF-8');
    $clean = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $text);
    $words = preg_split('/\s+/u', (string) $clean, -1, PREG_SPLIT_NO_EMPTY);
    $stopWords = ['и', 'в', 'на', 'по', 'the', 'and', 'for', 'with', 'как', 'это'];
    $frequency = [];

    foreach ($words as $word) {
        if (mb_strlen($word, 'UTF-8') < 3 || in_array($word, $stopWords, true)) {
            continue;
        }
        $frequency[$word] = ($frequency[$word] ?? 0) + 1;
    }

    arsort($frequency);
    return array_slice(array_keys($frequency), 0, $max);
}

function search_vacancies(PDO $pdo, array $keywords): array
{
    if (!$keywords) {
        return [];
    }

    $whereParts = [];
    $params = [];
    foreach ($keywords as $index => $keyword) {
        $param = ':kw' . $index;
        $whereParts[] = "(v.title LIKE {$param} OR v.description LIKE {$param} OR v.company LIKE {$param})";
        $params[$param] = '%' . $keyword . '%';
    }

    $sql = "
        SELECT v.id, v.title, v.company, v.description, c.name AS category_name
          FROM vacancies v
     LEFT JOIN categories c ON c.id = v.category_id
         WHERE " . implode(' OR ', $whereParts) . "
      ORDER BY v.created_at DESC
         LIMIT 20
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['resume'])) {
    $file = $_FILES['resume'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = $isKz ? 'Файлды жүктеу кезінде қате пайда болды.' : 'Произошла ошибка при загрузке файла.';
    } elseif ($file['size'] > 5 * 1024 * 1024) {
        $errors[] = $isKz ? 'Файл көлемі 5 МБ-тан аспауы керек.' : 'Размер файла не должен превышать 5 МБ.';
    } else {
        $allowed = ['pdf', 'doc', 'docx', 'txt'];
        $extension = strtolower((string) pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $allowed, true)) {
            $errors[] = $isKz ? 'Тек PDF, DOC, DOCX немесе TXT файлдары қабылданады.' : 'Поддерживаются только PDF, DOC, DOCX или TXT.';
        }
    }

    if (!$errors) {
        $uploadDir = __DIR__ . '/uploads/resumes';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $safeName = preg_replace('/[^A-Za-z0-9._-]/', '_', basename($file['name']));
        $uniqueName = time() . '_' . bin2hex(random_bytes(4)) . '_' . $safeName;
        $target = $uploadDir . '/' . $uniqueName;

        if (move_uploaded_file($file['tmp_name'], $target)) {
            $success = $isKz ? 'Резюме сәтті жүктелді.' : 'Резюме успешно загружено.';
            $text = extract_text_from_file($target);
            if ($text === '') {
                $text = pathinfo($file['name'], PATHINFO_FILENAME);
            }

            $keywords = extract_keywords($text);
            $matches = search_vacancies($pdo, $keywords);

            if ($myId) {
                $insert = $pdo->prepare("
                    INSERT INTO analyses (user_id, resume_filename, resume_original, keywords, matches_json)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $insert->execute([
                    $myId,
                    $uniqueName,
                    $file['name'],
                    implode(', ', $keywords),
                    json_encode($matches, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                ]);
            }

            if ($matches) {
                ob_start();
                ?>
                <div class="page-card">
                  <h2 class="section-title" style="font-size:1.5rem;"><?= $isKz ? 'Резюме бойынша табылған вакансиялар' : 'Найденные вакансии по резюме' ?></h2>
                  <div class="grid">
                    <?php foreach ($matches as $match): ?>
                      <article class="info-card">
                        <h3><?= htmlspecialchars((string) $match['title']) ?></h3>
                        <p><strong><?= $isKz ? 'Компания' : 'Компания' ?>:</strong> <?= htmlspecialchars((string) $match['company']) ?></p>
                        <p><strong><?= $isKz ? 'Санат' : 'Категория' ?>:</strong> <?= htmlspecialchars((string) ($match['category_name'] ?? '-')) ?></p>
                        <p><?= htmlspecialchars(mb_strlen((string) $match['description']) > 220 ? mb_substr((string) $match['description'], 0, 220) . '...' : (string) $match['description']) ?></p>
                        <a class="button-primary" href="search.php?id=<?= (int) $match['id'] ?>"><?= $isKz ? 'Толығырақ' : 'Подробнее' ?></a>
                      </article>
                    <?php endforeach; ?>
                  </div>
                </div>
                <?php
                $searchHtml = (string) ob_get_clean();
            } else {
                $searchHtml = '<div class="alert alert-info">' . ($isKz ? 'Сәйкес вакансия табылмады.' : 'Подходящие вакансии не найдены.') . '</div>';
            }

            $_SESSION['smart_search_html'] = $searchHtml;
            $_SESSION['smart_search_success'] = $success;
        } else {
            $errors[] = $isKz ? 'Файлды сақтау мүмкін болмады.' : 'Не удалось сохранить файл.';
        }
    }
}

$sessionSearchHtml = $_SESSION['smart_search_html'] ?? '';
$sessionSuccess = $_SESSION['smart_search_success'] ?? '';

$myAnalyses = [];
if ($myId) {
    $historyStmt = $pdo->prepare("
        SELECT id, resume_filename, resume_original, keywords, matches_json, created_at
          FROM analyses
         WHERE user_id = ?
      ORDER BY created_at DESC
         LIMIT 30
    ");
    $historyStmt->execute([$myId]);
    $myAnalyses = $historyStmt->fetchAll(PDO::FETCH_ASSOC);
}

if (isset($_GET['analysis_id']) && $myId) {
    $analysisStmt = $pdo->prepare("
        SELECT resume_original, keywords, matches_json, created_at
          FROM analyses
         WHERE id = ? AND user_id = ?
         LIMIT 1
    ");
    $analysisStmt->execute([(int) $_GET['analysis_id'], $myId]);
    $analysis = $analysisStmt->fetch(PDO::FETCH_ASSOC);

    if ($analysis) {
        $matches = json_decode((string) $analysis['matches_json'], true) ?: [];
        ob_start();
        ?>
        <section class="page-card">
          <h2 class="section-title" style="font-size:1.5rem;"><?= $isKz ? 'Сақталған талдау' : 'Сохраненный анализ' ?></h2>
          <p class="muted"><?= htmlspecialchars((string) $analysis['created_at']) ?></p>
          <p><strong><?= $isKz ? 'Резюме' : 'Резюме' ?>:</strong> <?= htmlspecialchars((string) $analysis['resume_original']) ?></p>
          <p><strong><?= htmlspecialchars($t['keywords']) ?>:</strong> <?= htmlspecialchars((string) $analysis['keywords']) ?></p>
          <div class="grid">
            <?php foreach ($matches as $match): ?>
              <article class="info-card">
                <h3><?= htmlspecialchars((string) ($match['title'] ?? '')) ?></h3>
                <p><strong><?= $isKz ? 'Компания' : 'Компания' ?>:</strong> <?= htmlspecialchars((string) ($match['company'] ?? '')) ?></p>
                <p><strong><?= $isKz ? 'Санат' : 'Категория' ?>:</strong> <?= htmlspecialchars((string) ($match['category_name'] ?? '-')) ?></p>
                <p><?= htmlspecialchars(mb_strlen((string) ($match['description'] ?? '')) > 220 ? mb_substr((string) ($match['description'] ?? ''), 0, 220) . '...' : (string) ($match['description'] ?? '')) ?></p>
                <?php if (!empty($match['id'])): ?>
                  <a class="button-primary" href="search.php?id=<?= (int) $match['id'] ?>"><?= $isKz ? 'Толығырақ' : 'Подробнее' ?></a>
                <?php endif; ?>
              </article>
            <?php endforeach; ?>
          </div>
        </section>
        <?php
        $searchHtml = (string) ob_get_clean();
    }
}

$t = [
    'title' => $isKz ? 'Ақылды іздеу' : 'Умный поиск',
    'home' => $isKz ? 'Басты бет' : 'Главная',
    'vacancies' => $isKz ? 'Вакансиялар' : 'Вакансии',
    'publish' => $isKz ? 'Жариялау' : 'Опубликовать',
    'faq' => 'FAQ',
    'support' => $isKz ? 'Қолдау' : 'Поддержка',
    'subtitle' => $isKz ? 'Резюмені жүктеп, соған ұқсас вакансияларды бірден табыңыз.' : 'Загрузите резюме и сразу получите подборку похожих вакансий.',
    'upload_label' => $isKz ? 'Резюме (PDF, DOC, DOCX, TXT)' : 'Резюме (PDF, DOC, DOCX, TXT)',
    'choose' => $isKz ? 'Файл таңдау' : 'Выбрать файл',
    'empty_file' => $isKz ? 'Файл таңдалмаған' : 'Файл не выбран',
    'upload' => $isKz ? 'Жіберу' : 'Отправить',
    'reset' => $isKz ? 'Тазарту' : 'Очистить',
    'clear_results' => $isKz ? 'Нәтижелерді тазарту' : 'Очистить результаты',
    'history' => $isKz ? 'Жүктеу тарихы' : 'История загрузок',
    'download' => $isKz ? 'Жүктеу' : 'Скачать',
    'view' => $isKz ? 'Қарау' : 'Посмотреть',
    'delete' => $isKz ? 'Жою' : 'Удалить',
    'keywords' => $isKz ? 'Түйінді сөздер' : 'Ключевые слова',
    'date' => $isKz ? 'Күні' : 'Дата',
    'policy' => $isKz ? 'Құпиялық саясаты' : 'Политика конфиденциальности',
    'terms' => $isKz ? 'Пайдалану шарттары' : 'Условия использования',
];
?>
<!DOCTYPE html>
<html lang="<?= $isKz ? 'kk' : 'ru' ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($t['title']) ?> | TruWork</title>
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
      <a class="brand" href="<?= $isKz ? 'index_kk.php' : 'index.php' ?>"><img src="logo2.png" alt="TruWork"></a>
      <nav class="site-nav" aria-label="<?= $isKz ? 'Негізгі навигация' : 'Основная навигация' ?>">
        <a href="<?= $isKz ? 'index_kk.php' : 'index.php' ?>"><?= htmlspecialchars($t['home']) ?></a>
        <a href="<?= $isKz ? 'vacancies_kk.php' : 'vacancies.php' ?>"><?= htmlspecialchars($t['vacancies']) ?></a>
        <a href="<?= $isKz ? 'vacancy_kk.php' : 'vacancy.php' ?>"><?= htmlspecialchars($t['publish']) ?></a>
        <a href="<?= $isKz ? 'faq_kk.html' : 'faq.html' ?>"><?= htmlspecialchars($t['faq']) ?></a>
        <a href="<?= $isKz ? 'support_kk.html' : 'support.html' ?>"><?= htmlspecialchars($t['support']) ?></a>
      </nav>
      <div class="header-actions">
        <div class="lang-switch">
          <?php if ($isKz): ?>
            <a href="smart_search.php">RU</a>
            <span class="is-active">KZ</span>
          <?php else: ?>
            <span class="is-active">RU</span>
            <a href="smart_search_kk.php">KZ</a>
          <?php endif; ?>
        </div>
        <?php if (isset($_COOKIE['user'])): ?>
          <a class="button-primary" href="<?= $isKz ? 'profile_kk.php' : 'profile.php' ?>"><?= htmlspecialchars($_COOKIE['user']) ?></a>
        <?php else: ?>
          <a class="button-primary" href="<?= $isKz ? 'login_kk.html' : 'login.html' ?>">Войти</a>
        <?php endif; ?>
      </div>
    </div>
  </header>

  <main class="page-main">
    <div class="site-shell grid">
      <section class="page-card">
        <h1 class="section-title"><?= htmlspecialchars($t['title']) ?></h1>
        <p class="section-subtitle"><?= htmlspecialchars($t['subtitle']) ?></p>

        <?php if ($errors): ?>
          <div class="alert alert-danger">
            <ul style="margin:0;padding-left:18px;">
              <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <?php if ($success || $sessionSuccess): ?>
          <div class="alert alert-success"><?= htmlspecialchars($success ?: $sessionSuccess) ?></div>
        <?php endif; ?>

        <form action="<?= htmlspecialchars($isKz ? 'smart_search_kk.php' : 'smart_search.php') ?>" method="post" enctype="multipart/form-data" class="smart-upload">
          <div>
            <label class="label" for="resume"><?= htmlspecialchars($t['upload_label']) ?></label>
            <div class="input-group">
              <label class="input-group-text" for="resume" style="cursor:pointer;"><?= htmlspecialchars($t['choose']) ?></label>
              <input id="resume" type="file" name="resume" accept=".pdf,.doc,.docx,.txt" hidden>
              <input id="file-name" class="input" readonly value="<?= htmlspecialchars($t['empty_file']) ?>">
            </div>
            <div style="margin-top:12px;">
              <a class="button-secondary" href="<?= $isKz ? 'smart_search_kk.php?clear=1' : 'smart_search.php?clear=1' ?>"><?= htmlspecialchars($t['clear_results']) ?></a>
            </div>
          </div>
          <div class="stack-actions" style="justify-content:flex-end;">
            <button class="button-primary" type="submit"><?= htmlspecialchars($t['upload']) ?></button>
            <button class="button-secondary" type="reset" id="reset-btn"><?= htmlspecialchars($t['reset']) ?></button>
          </div>
        </form>
      </section>

      <?php if ($myAnalyses): ?>
        <section class="page-card">
          <h2 class="section-title" style="font-size:1.5rem;"><?= htmlspecialchars($t['history']) ?></h2>
          <div class="grid">
            <?php foreach ($myAnalyses as $analysis): ?>
              <article class="info-card">
                <div class="smart-history-item">
                  <div>
                    <strong><?= htmlspecialchars((string) $analysis['resume_original']) ?></strong>
                    <div class="muted"><?= htmlspecialchars($t['date']) ?>: <?= htmlspecialchars((string) $analysis['created_at']) ?></div>
                    <div class="muted"><?= htmlspecialchars($t['keywords']) ?>: <?= htmlspecialchars((string) $analysis['keywords']) ?></div>
                  </div>
                  <div class="smart-history-actions">
                    <a class="button-secondary" href="<?= htmlspecialchars($basePath . '/uploads/resumes/' . $analysis['resume_filename']) ?>" target="_blank"><?= htmlspecialchars($t['download']) ?></a>
                    <a class="button-primary" href="<?= $isKz ? 'smart_search_kk.php' : 'smart_search.php' ?>?analysis_id=<?= (int) $analysis['id'] ?>"><?= htmlspecialchars($t['view']) ?></a>
                    <form action="<?= htmlspecialchars($isKz ? 'smart_search_kk.php' : 'smart_search.php') ?>" method="post">
                      <input type="hidden" name="delete_analysis_id" value="<?= (int) $analysis['id'] ?>">
                      <button class="button-secondary" type="submit"><?= htmlspecialchars($t['delete']) ?></button>
                    </form>
                  </div>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        </section>
      <?php endif; ?>

      <?= $searchHtml ?: $sessionSearchHtml ?>
    </div>
  </main>

  <footer class="site-footer">
    <div class="site-shell site-footer__panel">
      <div>
        <strong>TruWork</strong>
        <div class="footer-note"><?= $isKz ? 'Резюме бойынша ақылды іздеу және талдау тарихы.' : 'Умный поиск по резюме и история анализов.' ?></div>
      </div>
      <div class="footer-links">
        <a href="<?= $isKz ? 'policy_kk.html' : 'policy.html' ?>"><?= htmlspecialchars($t['policy']) ?></a>
        <a href="<?= $isKz ? 'terms_kk.html' : 'terms.html' ?>"><?= htmlspecialchars($t['terms']) ?></a>
        <a href="<?= $isKz ? 'support_kk.html' : 'support.html' ?>"><?= htmlspecialchars($t['support']) ?></a>
      </div>
    </div>
  </footer>

  <script>
    (function () {
      var input = document.getElementById('resume');
      var fileName = document.getElementById('file-name');
      var reset = document.getElementById('reset-btn');
      input.addEventListener('change', function () {
        fileName.value = input.files && input.files.length ? input.files[0].name : <?= json_encode($t['empty_file']) ?>;
      });
      reset.addEventListener('click', function () {
        setTimeout(function () {
          fileName.value = <?= json_encode($t['empty_file']) ?>;
        }, 0);
      });
    }());
  </script>
</body>
</html>
