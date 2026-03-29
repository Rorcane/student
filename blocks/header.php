<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Truwork</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Подключаем Bootstrap и ваши стили -->
  <link rel="stylesheet" href="/css/style.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
  <title><?= htmlspecialchars($pageTitle, ENT_QUOTES) ?></title>
  <meta name="description" content="<?= htmlspecialchars($metaDescription, ENT_QUOTES) ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- … -->
</head>
<body>
<header class="p-3 bg-dark text-white">
  <div class="container d-flex justify-content-between align-items-center">
    <a href="index.php" class="text-white text-decoration-none"><h1>Мой сайт</h1></a>
    <div>
      <?php if(isset($_COOKIE['user'])): ?>
        <span>Здравствуйте, <?= htmlspecialchars($_COOKIE['user']) ?></span>
        <a href="logout.php" class="btn btn-outline-light ms-3">Выйти</a>
      <?php else: ?>
        <a href="login.php" class="btn btn-outline-light">Войти</a>
      <?php endif; ?>
    </div>
  </div>
</header>
