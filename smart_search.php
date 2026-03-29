<?php

require_once 'config.php';
session_start();

// если есть сохранённые результаты из прошлой загрузки (fallback)
$session_search_html = $_SESSION['smart_search_html'] ?? '';
$session_success     = $_SESSION['smart_search_success'] ?? '';
// очистка сессии при clear=1 (опционально)
if (isset($_GET['clear'])) {
    unset($_SESSION['smart_search_html'], $_SESSION['smart_search_success']);
    header('Location: ' . (isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : 'smart_search.php'));
    exit;
}

/**
 * Получаем роль/пользователя из куки (как в vacancies.php)
 */
$user = null;
if (isset($_COOKIE['user'])) {
    $uStmt = $pdo->prepare("SELECT id, role FROM users WHERE username = ? LIMIT 1");
    $uStmt->execute([$_COOKIE['user']]);
    $user = $uStmt->fetch(PDO::FETCH_ASSOC);
}

// вычислим user id (если авторизован)
$myId = $user['id'] ?? null;

/**
 * Вычисляем базовый путь относительно места размещения скрипта.
 */
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
if ($basePath === '/' || $basePath === '\\' || $basePath === '.') $basePath = '';

// Обработка удаления анализа (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_analysis_id']) && $myId) {
    $delId = (int)$_POST['delete_analysis_id'];
    $del = $pdo->prepare("DELETE FROM analyses WHERE id = ? AND user_id = ?");
    $del->execute([$delId, $myId]);
    header('Location: ' . htmlspecialchars($basePath . '/smart_search.php'));
    exit();
}

// Переменные для вывода
$errors = [];
$success = '';
$search_html = ''; // сюда поместим HTML текущего/просматриваемого анализа

// --- Обработка загрузки и анализа резюме ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['resume'])) {
    // (вставлена ваша логика сохранения/валидации файла)
    $file = $_FILES['resume'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Ошибка при загрузке файла.';
    } else {
        // Проверки: размер и расширение
        $maxBytes = 5 * 1024 * 1024; // 5 MB
        if ($file['size'] > $maxBytes) {
            $errors[] = 'Файл слишком большой. Максимум 5 МБ.';
        }

        $allowed = ['pdf', 'doc', 'docx', 'txt'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed, true)) {
            $errors[] = 'Недопустимый формат файла. Разрешены: PDF, DOC, DOCX, TXT.';
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);

        if (empty($errors)) {
            $uploadDir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'resumes';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $safeName = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', basename($file['name']));
            try {
                $unique = time() . '_' . bin2hex(random_bytes(6)) . '_' . $safeName;
            } catch (Exception $e) {
                $unique = time() . '_' . uniqid() . '_' . $safeName;
            }
            $dest = $uploadDir . DIRECTORY_SEPARATOR . $unique;

            if (move_uploaded_file($file['tmp_name'], $dest)) {
                // попытка сохранить запись в таблицу resumes (если есть)
                try {
                    $iStmt = $pdo->prepare("INSERT INTO resumes (filename, original_name, uploaded_at, uploader) VALUES (?, ?, NOW(), ?)");
                    $uploader = isset($_COOKIE['user']) ? $_COOKIE['user'] : null;
                    $iStmt->execute([$unique, $file['name'], $uploader]);
                } catch (Exception $e) {
                    // игнорируем, если таблицы нет
                }

                $success = 'Резюме успешно загружено.';

                // -----------------------
                // Блок: извлечение текста и поиск вакансий
                // -----------------------
                function extract_text_from_docx($path) {
                    $text = '';
                    $zip = new ZipArchive;
                    if ($zip->open($path) === TRUE) {
                        if (($index = $zip->locateName('word/document.xml')) !== false) {
                            $data = $zip->getFromIndex($index);
                            $zip->close();
                            $data = preg_replace('/<(?:[^>]+)>/u', ' ', $data);
                            $text = preg_replace('/\s+/u', ' ', $data);
                        } else {
                            $zip->close();
                        }
                    }
                    return trim($text);
                }
                function extract_text_from_pdf($path) {
                    $tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'resume_extract_' . bin2hex(random_bytes(4)) . '.txt';
                    $cmd = 'pdftotext ' . escapeshellarg($path) . ' ' . escapeshellarg($tmp) . ' 2>/dev/null';
                    @shell_exec($cmd);
                    $text = '';
                    if (file_exists($tmp)) {
                        $text = file_get_contents($tmp);
                        @unlink($tmp);
                    }
                    return trim($text);
                }
                function extract_text_from_doc($path) {
                    $tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'resume_doc_' . bin2hex(random_bytes(4)) . '.txt';
                    $cmd = 'antiword ' . escapeshellarg($path) . ' > ' . escapeshellarg($tmp) . ' 2>/dev/null';
                    @shell_exec($cmd);
                    $text = '';
                    if (file_exists($tmp)) {
                        $text = file_get_contents($tmp);
                        @unlink($tmp);
                    }
                    return trim($text);
                }
                function extract_text_from_file($path) {
                    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                    if ($ext === 'docx') return extract_text_from_docx($path);
                    if ($ext === 'pdf') return extract_text_from_pdf($path);
                    if ($ext === 'doc') return extract_text_from_doc($path);
                    if ($ext === 'txt') return file_exists($path) ? trim(file_get_contents($path)) : '';
                    return file_exists($path) ? trim(file_get_contents($path)) : '';
                }
                function extract_keywords($text, $max=12) {
                    $text = mb_strtolower($text, 'UTF-8');
                    $clean = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $text);
                    $words = preg_split('/\s+/u', $clean, -1, PREG_SPLIT_NO_EMPTY);
                    $stop = [
                      'и','в','во','не','что','он','на','я','с','со','как','а','то','все','она','так',
                      'его','но','да','ты','к','у','же','вы','за','бы','по','только','или','для',
                      'is','the','and','a','to','of','in','on','with','as','by','from'
                    ];
                    $freq = [];
                    foreach ($words as $w) {
                        if (mb_strlen($w,'UTF-8') < 3) continue;
                        if (in_array($w, $stop, true)) continue;
                        $freq[$w] = ($freq[$w] ?? 0) + 1;
                    }
                    arsort($freq);
                    return array_slice(array_keys($freq), 0, $max);
                }
                function search_vacancies_by_keywords($pdo, $keywords) {
                    if (empty($keywords)) return [];
                    $whereParts = [];
                    $params = [];
                    $i=0;
                    foreach ($keywords as $kw) {
                        $i++;
                        $p = ":kw{$i}";
                        $whereParts[] = "(v.title LIKE {$p} OR v.description LIKE {$p} OR v.company LIKE {$p} OR v.category LIKE {$p})";
                        $params[$p] = "%{$kw}%";
                    }
                    $where = implode(' OR ', $whereParts);
                    // Если в базе нет created_at для вакансий, безопаснее ORDER BY v.id DESC (но оставим created_at, как у вас было)
                    $sql = "SELECT v.*, COUNT(a.id) AS responses
                                FROM vacancies v
                                LEFT JOIN applications a ON a.vacancy_id = v.id
                                WHERE ({$where})
                                GROUP BY v.id
                                ORDER BY v.created_at DESC
                                LIMIT 50";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($params);
                    return $stmt->fetchAll(PDO::FETCH_ASSOC);
                }

                // получить текст
                $text = extract_text_from_file($dest);
                if (trim($text) === '') $text = pathinfo($file['name'], PATHINFO_FILENAME);
                $keywords = extract_keywords($text, 12);
                if (empty($keywords)) {
                    $fnWords = preg_split('/[^A-Za-zА-Яа-я0-9]+/u', pathinfo($file['name'], PATHINFO_FILENAME));
                    $keywords = array_slice(array_filter($fnWords), 0, 6);
                }

                $matches = search_vacancies_by_keywords($pdo, $keywords);

                // --- Сохранение анализа в БД, привязанное к пользователю (если авторизован) ---
                if ($myId) {
                    try {
                        $matches_short = [];
                        foreach ($matches as $m) {
                            $matches_short[] = [
                                'id' => isset($m['id']) ? (int)$m['id'] : null,
                                'title' => $m['title'] ?? '',
                                'company' => $m['company'] ?? '',
                                'category' => $m['category'] ?? '',
                                'excerpt' => isset($m['description']) ? mb_substr($m['description'], 0, 300) : '',
                            ];
                        }
                        $insStmt = $pdo->prepare("INSERT INTO analyses (user_id, resume_filename, resume_original, keywords, matches_json) VALUES (?, ?, ?, ?, ?)");
                        $insStmt->execute([
                            $myId,
                            $unique,
                            $file['name'],
                            implode(', ', $keywords),
                            json_encode($matches_short, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)
                        ]);
                        // можно использовать $analysisId = $pdo->lastInsertId();
                    } catch (Exception $e) {
                        error_log('Не удалось сохранить анализ: ' . $e->getMessage());
                    }
                }

                // Формируем HTML для вывода результатов в интерфейсе (как раньше, с модалами)
                if (!empty($matches)) {
                    $search_html .= '<div class="mb-3"><h5>Найденные вакансии по вашему резюме:</h5>';
                    foreach ($matches as $m) {
                        $id = isset($m['id']) ? (int)$m['id'] : 0;
                        $title_raw = $m['title'] ?? 'Без названия';
                        $company_raw = $m['company'] ?? '-';
                        $category_raw = $m['category'] ?? '-';
                        $desc_raw = $m['description'] ?? '';

                        $title = htmlspecialchars($title_raw, ENT_QUOTES);
                        $company = htmlspecialchars($company_raw, ENT_QUOTES);
                        $category = htmlspecialchars($category_raw, ENT_QUOTES);
                        $desc_short = mb_strlen($desc_raw) > 180 ? htmlspecialchars(mb_substr($desc_raw, 0, 180)) . '...' : htmlspecialchars($desc_raw);
                        $modalId = 'applyModal-' . $id;

                        $search_html .= '<div class="card mb-2">';
                        $search_html .= '  <div class="card-body d-flex flex-column flex-sm-row justify-content-between align-items-start">';
                        $search_html .= '    <div>';
                        $search_html .= "      <h6 class=\"mb-1 fw-bold\">{$title}</h6>";
                        $search_html .= "      <p class=\"mb-1\"><strong>Компания:</strong> {$company}</p>";
                        $search_html .= "      <p class=\"mb-1\"><strong>Категория:</strong> {$category}</p>";
                        $search_html .= "      <p class=\"mb-0 text-muted\">{$desc_short}</p>";
                        $search_html .= '    </div>';
                        $search_html .= '    <div class="mt-3 mt-sm-0">';
                        $search_html .= "      <button type=\"button\" class=\"btn btn-outline-primary btn-sm\" data-bs-toggle=\"modal\" data-bs-target=\"#{$modalId}\" data-bs-id=\"{$id}\" data-bs-title=\"{$title}\">Открыть</button>";
                        $search_html .= '    </div>';
                        $search_html .= '  </div>';
                        $search_html .= '</div>';

                        // модальное окно
                        $search_html .= '<div class="modal fade" id="' . $modalId . '" tabindex="-1" aria-hidden="true">';
                        $search_html .= '  <div class="modal-dialog">';
                        $formAction = htmlspecialchars($basePath . '/process_application.php');
                        $search_html .= '    <form action="' . $formAction . '" method="POST" class="modal-content">';
                        $search_html .= '      <input type="hidden" name="vacancy_id" value="' . $id . '">';
                        $search_html .= '      <div class="modal-header">';
                        $search_html .= '        <h5 class="modal-title">Развернуть</h5>';
                        $search_html .= '        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>';
                        $search_html .= '      </div>';
                        $search_html .= '      <div class="modal-body">';
                        $search_html .= '        <p>Откликнуться на вакансию «<strong>' . $title . '</strong>»?</p>';
                        $search_html .= '        <hr>';
                        $search_html .= '        <p><strong>Описание вакансии:</strong></p>';
                        $search_html .= '        <p>' . nl2br(htmlspecialchars($desc_raw)) . '</p>';
                        $search_html .= '        <p><strong>Компания:</strong> ' . $company . '</p>';
                        $search_html .= '        <p><strong>Местоположение:</strong> ' . (isset($m['location']) ? htmlspecialchars($m['location'], ENT_QUOTES) : '-') . '</p>';
                        $search_html .= '      </div>';
                        $search_html .= '      <div class="modal-footer">';
                        $search_html .= '        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>';
                        $search_html .= '        <button type="submit" class="btn btn-primary">Откликнуться</button>';
                        $search_html .= '      </div>';
                        $search_html .= '    </form>';
                        $search_html .= '  </div>';
                        $search_html .= '</div>';
                    }
                    $search_html .= '</div>';
                } else {
                    $search_html .= '<div class="alert alert-info mt-3">По результатам автоматического поиска совпадений не найдено. Попробуйте загрузить резюме в другом формате или проверьте, что в базе есть вакансии.</div>';
                }

                // показать ключевые слова (полезно)
                if (!empty($keywords)) {
                    $search_html .= '<div class="mt-2"><small class="text-muted">Ключевые слова: '.htmlspecialchars(implode(', ', $keywords)).'</small></div>';
                }

                // Сохраняем в сессии как fallback (удобно)
                $_SESSION['smart_search_html'] = $search_html;
                $_SESSION['smart_search_success'] = $success;

            } else {
                $errors[] = 'Не удалось сохранить файл на сервере.';
            }
        }
    }
}

// --- Подгрузка истории анализов текущего пользователя ---
$myAnalyses = [];
if ($myId) {
    try {
        $hStmt = $pdo->prepare("SELECT id, resume_filename, resume_original, keywords, matches_json, created_at FROM analyses WHERE user_id = ? ORDER BY created_at DESC LIMIT 50");
        $hStmt->execute([$myId]);
        $myAnalyses = $hStmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        // игнорируем ошибки чтения истории, но логируем
        error_log('Ошибка получения истории анализов: ' . $e->getMessage());
    }
}

// --- Просмотр конкретного анализа (GET ?analysis_id=...) ---
if (isset($_GET['analysis_id']) && $myId) {
    $aid = (int)$_GET['analysis_id'];
    $aStmt = $pdo->prepare("SELECT * FROM analyses WHERE id = ? AND user_id = ? LIMIT 1");
    $aStmt->execute([$aid, $myId]);
    $aRow = $aStmt->fetch(PDO::FETCH_ASSOC);
    if ($aRow) {
        $search_html = '<div class="card mb-4"><div class="card-body">';
        $search_html .= '<h5>Результаты анализа от ' . htmlspecialchars($aRow['created_at']) . '</h5>';
        $search_html .= '<p><strong>Резюме:</strong> ' . htmlspecialchars($aRow['resume_original']) . '</p>';
        $search_html .= '<p><strong>Ключевые слова:</strong> ' . htmlspecialchars($aRow['keywords']) . '</p>';
        $matches_prev = json_decode($aRow['matches_json'], true) ?: [];
        if (!empty($matches_prev)) {
            foreach ($matches_prev as $m) {
                $search_html .= '<div class="card mb-2"><div class="card-body d-flex justify-content-between">';
                $search_html .= '<div><strong>' . htmlspecialchars($m['title']) . '</strong><div class="text-muted">' . htmlspecialchars($m['company']) . ' — ' . htmlspecialchars($m['category']) . '</div>';
                $search_html .= '<div class="text-muted small mt-1">' . htmlspecialchars($m['excerpt']) . '</div></div>';
                $search_html .= '<div><a class="btn btn-outline-primary btn-sm" href="' . htmlspecialchars($basePath . '/vacancy.php?id=' . (int)$m['id']) . '">Открыть</a></div>';
                $search_html .= '</div></div>';
            }
        } else {
            $search_html .= '<div class="alert alert-info">Совпадений тогда не было.</div>';
        }
        $search_html .= '</div></div>';
    } else {
        $search_html = '<div class="alert alert-warning">Анализ не найден или доступ к нему запрещён.</div>';
    }
}

// ------------------------
// HTML: вывод страницы
// ------------------------
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
  <title>TruWork — Умный поиск</title>
  <link rel="icon" type="image/png" href="/favicon-96x96.png" sizes="96x96" />
  <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
  <link rel="shortcut icon" href="/favicon.ico" />
  <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png" />
  <link rel="manifest" href="/site.webmanifest" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/public-site.css">
  <style>
    :root { --primary: #2563eb; --secondary: #3b82f6; --accent: #60a5fa; --gradient: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);}    
    body { padding-top: 0 !important; background: #f4f8fc; font-family: 'Inter', sans-serif; }
    .navbar { box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);}    
    .nav-link { position: relative; padding: 0.5rem 1rem !important; color: #495057 !important; }
    .nav-link.active { color: var(--primary) !important; }
    .nav-link-border { position: absolute; bottom: 0; left: 0; width: 0; height: 2px; background: var(--primary); transition: width 0.3s ease; }
    .nav-link:hover .nav-link-border, .nav-link.active .nav-link-border { width: 100%; }
    nav.navbar,
    footer.bg-light.border-top.py-4.mt-5 { display:none; }
    main.container.py-5 {
      max-width: 1160px;
      padding-top: 34px !important;
      padding-bottom: 72px !important;
    }
    h1.mb-4.fw-bold {
      margin-bottom: 22px !important;
      font-size: clamp(2rem, 4vw, 2.8rem);
      letter-spacing: -0.04em;
      color: #14213d;
    }
    .card {
      border: 1px solid rgba(216,226,238,0.9);
      border-radius: 24px;
      box-shadow: 0 14px 40px rgba(18,38,63,0.08);
      overflow: hidden;
    }
    .card-body { padding: 24px; }
    .alert {
      border-radius: 18px;
      border: 1px solid rgba(216,226,238,0.85);
      box-shadow: 0 10px 24px rgba(18,38,63,0.05);
    }
    .btn {
      border-radius: 14px;
      font-weight: 700;
      padding: 12px 18px;
    }
    .btn-primary {
      background: linear-gradient(135deg, #1d4ed8 0%, #163da8 100%);
      border-color: #1d4ed8;
      box-shadow: 0 10px 24px rgba(29,78,216,0.18);
    }
    .form-control,
    .input-group-text {
      min-height: 52px;
      border-radius: 14px !important;
      border-color: #d8e2ee;
    }
    .input-group > .form-control,
    .input-group > .input-group-text { margin-right: 8px; }
    .input-group > .form-control:last-child { margin-right: 0; }
    .text-muted,
    .form-text,
    .small { color: #5b6577 !important; }
    .border.rounded.p-3 {
      border-radius: 20px !important;
      border-color: #d8e2ee !important;
      background: #f8fbff;
    }
    @media (max-width: 768px) {
      main.container.py-5 { padding-top: 22px !important; }
      .card-body { padding: 18px; }
      .input-group {
        flex-direction: column;
        gap: 10px;
      }
      .input-group > .form-control,
      .input-group > .input-group-text {
        margin-right: 0;
        width: 100%;
      }
    }
  </style>
</head>
<body>
  <header class="site-header">
    <div class="site-shell site-header__inner">
      <a class="brand" href="index.php"><img src="logo2.png" alt="TruWork"></a>
      <nav class="site-nav" aria-label="Основная навигация">
        <a href="index.php">Главная</a>
        <a href="vacancies.php">Вакансии</a>
        <a href="vacancy.php">Опубликовать</a>
        <a href="support.html">Поддержка</a>
        <a href="smart_search.php" class="is-active">Умный поиск</a>
      </nav>
      <div class="header-actions">
        <?php if(isset($_COOKIE['user'])): ?>
          <a class="button-primary" href="profile.php"><?= htmlspecialchars($_COOKIE['user']) ?></a>
        <?php else: ?>
          <a class="button-primary" href="login.html">Войти</a>
        <?php endif; ?>
      </div>
    </div>
  </header>
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
          <li class="nav-item"><a class="nav-link active" href="vacancies.php"><span class="nav-link-border"></span>Найти работу</a></li>
          <li class="nav-item"><a class="nav-link" href="vacancy.php"><span class="nav-link-border"></span>Опубликовать</a></li>
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

  <main class="container py-5">
    <h1 class="mb-4 fw-bold">Умный поиск вакансий</h1>

    <!-- Сообщения об ошибках / успехе -->
    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger">
        <ul class="mb-0">
          <?php foreach ($errors as $e): ?>
            <li><?= htmlspecialchars($e) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <div class="card mb-4 shadow-sm">
      <div class="card-body">
        <p class="text-muted">Загрузите резюме в формате PDF / DOC / DOCX — мы проанализируем его и попытаемся найти подходящие вакансии.</p>

        <form action="<?= htmlspecialchars($basePath . '/smart_search.php') ?>" method="post" enctype="multipart/form-data" class="row g-3 align-items-center">
          <div class="col-md-8">
            <label for="resume" class="form-label">Резюме (PDF, DOC, DOCX)</label>
            <div class="input-group">
              <label class="input-group-text btn btn-outline-secondary mb-0" for="resume" style="cursor:pointer;">Выбрать файл</label>
              <input id="resume" name="resume" type="file" accept=".pdf,.doc,.docx,.txt" style="display:none;" />
              <input id="file-name" class="form-control" readonly value="Файл не выбран" />
            </div>
            <div class="form-text">Максимальный размер: 5 МБ.</div>
            <div class="mt-3">
            <a href="?clear=1" class="btn btn-outline-danger btn-sm">Очистить результаты</a>
            </div>

          </div>

          <div class="col-md-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary w-100 align-self-end">Отправить</button>
            <button type="reset" id="reset-btn" class="btn btn-outline-secondary w-100 align-self-end">Очистить</button>
          </div>
        </form>

        <hr>

        <p class="small text-muted mb-0">Только авторизованные пользователи могут получить расширенный анализ. Если хотите — добавьте возможность прикреплять сопроводительное письмо позже.</p>
      </div>
    </div>

    <!-- История ваших анализов (если есть) -->
    <?php if (!empty($myAnalyses)): ?>
      <div class="card mb-4">
        <div class="card-body">
          <h5 class="mb-3">История ваших загрузок / анализов</h5>
          <?php foreach ($myAnalyses as $a):
            $matches_preview = json_decode($a['matches_json'], true);
          ?>
            <div class="mb-3 border rounded p-3">
              <div class="d-flex justify-content-between align-items-start">
                <div>
                  <strong><?= htmlspecialchars($a['resume_original']) ?></strong>
                  <div class="text-muted small">Дата: <?= htmlspecialchars($a['created_at']) ?></div>
                  <div class="text-muted small">Ключевые слова: <?= htmlspecialchars($a['keywords']) ?></div>
                </div>
                <div class="text-end">
                  <a href="<?= htmlspecialchars($basePath . '/uploads/resumes/' . $a['resume_filename']) ?>" class="btn btn-sm btn-outline-secondary" target="_blank">Скачать</a>
                  <a href="<?= htmlspecialchars($basePath . '/smart_search.php?analysis_id=' . (int)$a['id']) ?>" class="btn btn-sm btn-primary">Посмотреть</a>

                  <form action="<?= htmlspecialchars($basePath . '/smart_search.php') ?>" method="post" class="d-inline-block ms-1" onsubmit="return confirm('Удалить этот анализ?');">
                    <input type="hidden" name="delete_analysis_id" value="<?= (int)$a['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-danger">Удалить</button>
                  </form>
                </div>
              </div>

              <?php if (!empty($matches_preview)): ?>
                <div class="mt-2">
                  <small class="text-muted">Превью найденных вакансий:</small>
                  <ul class="mb-0">
                    <?php foreach (array_slice($matches_preview, 0, 3) as $mp): ?>
                      <li><?= htmlspecialchars($mp['title']) ?> — <?= htmlspecialchars($mp['company']) ?></li>
                    <?php endforeach; ?>
                  </ul>
                </div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

    <!-- Здесь выводим результаты автоматического поиска (или просмотр анализов) -->
    <?= $search_html ?: $session_search_html ?>

  </main>

  <footer class="bg-light border-top py-4 mt-5">
    <div class="container text-center text-muted">&copy; <?= date('Y') ?> TruWork. Все права защищены.</div>
  </footer>

  <footer class="site-footer">
    <div class="site-shell site-footer__panel">
      <div>
        <strong>TruWork</strong>
        <div class="footer-note">Умный поиск вакансий и история анализов в едином стиле.</div>
      </div>
      <div class="footer-links">
        <a href="policy.html">Политика конфиденциальности</a>
        <a href="terms.html">Условия использования</a>
        <a href="support.html">Поддержка</a>
      </div>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    (function(){
      const fileInput = document.getElementById('resume');
      const fileName  = document.getElementById('file-name');
      const resetBtn  = document.getElementById('reset-btn');

      if(fileInput){
        fileInput.addEventListener('change', function(){
          if(!this.files || this.files.length === 0){
            fileName.value = 'Файл не выбран';
            return;
          }
          fileName.value = this.files[0].name;
        });
      }
      if(resetBtn){
        resetBtn.addEventListener('click', function(){
          fileName.value = 'Файл не выбран';
          if(fileInput){ fileInput.value = ''; }
        });
      }
    })();
  </script>
</body>
</html>
