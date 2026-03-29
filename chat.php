<?php
// chat.php (версия для схемы: id, sender_id, receiver_id, content, created_at, vacancy_id)
require_once 'config.php';
session_start();

// Авторизация
if (!isset($_COOKIE['user'])) {
    header('Location: login.html');
    exit();
}
$currentUsername = $_COOKIE['user'];
$peerUsername    = $_GET['with']    ?? '';
$vacancyId       = isset($_GET['vacancy']) ? (int)$_GET['vacancy'] : 0;

// Валидация
if ($peerUsername === '' || $vacancyId <= 0) {
    die('Неверные параметры чата.');
}

// Проверяем что вакансия существует
$stmtVac = $pdo->prepare("SELECT id, author, title FROM vacancies WHERE id = ?");
$stmtVac->execute([$vacancyId]);
$vac = $stmtVac->fetch(PDO::FETCH_ASSOC);
if (!$vac) {
    die('Вакансия не найдена.');
}
$vacTitle = $vac['title'];
// Предполагаем, что в vacancies.author хранится username
$isAuthor = ($vac['author'] === $currentUsername);

// Получим id текущего пользователя и собеседника из таблицы users
$uidStmt = $pdo->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
$uidStmt->execute([$currentUsername]);
$row = $uidStmt->fetch(PDO::FETCH_ASSOC);
if (!$row) die('Текущий пользователь не найден в базе (users).');
$myId = (int)$row['id'];

$uidStmt->execute([$peerUsername]);
$row2 = $uidStmt->fetch(PDO::FETCH_ASSOC);
if (!$row2) die('Собеседник не найден в базе (users).');
$peerId = (int)$row2['id'];

// Получаем сообщения для этой вакансии между этими двумя пользователями
try {
    $stmt = $pdo->prepare("
        SELECT sender_id, receiver_id, content, created_at
          FROM messages
         WHERE vacancy_id = :vacancy
           AND (
                 (sender_id   = :me   AND receiver_id = :peer)
              OR (sender_id   = :peer AND receiver_id = :me)
           )
      ORDER BY created_at ASC, id ASC
    ");
    $stmt->execute([
        ':vacancy' => $vacancyId,
        ':me'      => $myId,
        ':peer'    => $peerId,
    ]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die('Ошибка при получении сообщений: ' . htmlspecialchars($e->getMessage()));
}

// Отправка нового сообщения
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['text']) && trim($_POST['text']) !== '') {
    $text = trim($_POST['text']);
    try {
        $ins = $pdo->prepare("
            INSERT INTO messages (sender_id, receiver_id, vacancy_id, content, created_at)
            VALUES (:me, :peer, :vacancy, :content, NOW())
        ");
        $ins->execute([
            ':me'      => $myId,
            ':peer'    => $peerId,
            ':vacancy' => $vacancyId,
            ':content' => $text,
        ]);
    } catch (Exception $e) {
        die('Ошибка при отправке сообщения: ' . htmlspecialchars($e->getMessage()));
    }

    // Редирект, чтобы убрать повторную отправку формы при F5
    header('Location: chat.php?with=' . urlencode($peerUsername) . '&vacancy=' . $vacancyId);
    exit();
}

function fmtDT($dt) {
    if (!$dt) return '';
    return date('d.m.Y H:i', strtotime($dt));
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Чат: <?= htmlspecialchars($vacTitle, ENT_QUOTES) ?></title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background:#f8f9fa; padding-top:4rem; }
    .chat-window { max-width:700px;margin:auto;background:#fff;border:1px solid #ddd;border-radius:.5rem;overflow:hidden; }
    .messages { max-height:60vh;overflow-y:auto;padding:1rem;background:#f1f1f1; }
    .msg { display:flex;margin-bottom:.5rem; }
    .msg.me { justify-content:flex-end; }
    .bubble { max-width:75%;padding:.5rem 1rem;border-radius:1rem;position:relative; }
    .msg.me .bubble { background:#d1e7dd;border-bottom-right-radius:.2rem; }
    .msg.peer .bubble { background:#fff;border-bottom-left-radius:.2rem; }
    .bubble small { display:block;font-size:.7rem;color:#666;margin-top:.25rem; }
    .chat-header { background:#2563eb;color:#fff;padding:.75rem 1rem; }
  </style>
</head>
<body>
<nav class="navbar navbar-dark bg-primary fixed-top">
  <div class="container-fluid justify-content-center">
    <span class="navbar-brand h5 mb-0">
      <?= htmlspecialchars($vacTitle,ENT_QUOTES) ?> — чат с <?= htmlspecialchars($peerUsername,ENT_QUOTES) ?>
    </span>
  </div>
</nav>

<div class="chat-window mt-4">
  <div class="chat-header">
    Вакансия: <?= htmlspecialchars($vacTitle,ENT_QUOTES) ?>
    <?php if($isAuthor): ?><span class="badge bg-success ms-2">Ваши действия</span><?php endif; ?>
  </div>

  <!-- Кнопка «Принять заявку» если вы автор вакансии -->
  <?php if($isAuthor): ?>
    <form action="process_accept.php" method="POST" class="px-3 py-2 bg-white">
      <input type="hidden" name="vacancy_id" value="<?= $vacancyId ?>">
      <input type="hidden" name="applicant"  value="<?= htmlspecialchars($peerUsername,ENT_QUOTES) ?>">
      <button type="submit"
              class="btn btn-success mb-3"
              onclick="return confirm('Принять заявку от <?= htmlspecialchars($peerUsername,ENT_QUOTES) ?>?')">
        Принять заявку
      </button>
    </form>
  <?php endif; ?>

  <!-- История сообщений -->
  <div class="messages">
    <?php if (empty($messages)): ?>
      <p class="text-center text-muted">Переписка пуста.</p>
    <?php else: ?>
      <?php foreach ($messages as $m): ?>
        <?php $me = ((int)$m['sender_id'] === $myId); ?>
        <div class="msg <?= $me ? 'me' : 'peer' ?>">
          <div class="bubble">
            <?= nl2br(htmlspecialchars($m['content'],ENT_QUOTES)) ?>
            <?php if (!empty($m['created_at'])): ?>
              <small><?= fmtDT($m['created_at']) ?></small>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <!-- Форма отправки -->
  <form method="post" class="p-3 border-top bg-white">
    <input type="hidden" name="vacancy_id" value="<?= $vacancyId ?>">
    <div class="input-group">
      <textarea name="text" required class="form-control" rows="1" placeholder="Сообщение…"></textarea>
      <button class="btn btn-primary" type="submit">Отправить</button>
      <a href="applications.php" class="btn btn-link">← Назад</a>
    </div>
  </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
