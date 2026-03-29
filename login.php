<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username']);
  $password = trim($_POST['password']); // для демонстрации; в реальности используйте password_hash и password_verify

  // Проверяем наличие пользователя в БД
  $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
  $stmt->execute([$username]);
  $userData = $stmt->fetch();
  if ($userData && $password === $userData['password']) { // в реальности: password_verify($password, $userData['password'])
    // Устанавливаем куки на 1 день (86400 секунд)
    setcookie('user', $username, time() + 86400, '/');
    header('Location: index.php');
    exit();
  } else {
    $error = "Неверное имя пользователя или пароль";
  }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
	<link rel="icon" type="image/png" href="/favicon-96x96.png" sizes="96x96" />
	<link rel="icon" type="image/svg+xml" href="/favicon.svg" />
	<link rel="shortcut icon" href="/favicon.ico" />
	<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png" />
	<link rel="manifest" href="/site.webmanifest" />
  <meta charset="UTF-8">
  <title>Вход</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
  <h1>Вход</h1>
  <?php if(isset($error)): ?>
    <div class="alert alert-danger"><?= $error ?></div>
  <?php endif; ?>
  <form method="POST">
    <div class="mb-3">
      <label for="username" class="form-label">Имя пользователя</label>
      <input type="text" name="username" id="username" class="form-control" required>
    </div>
    <div class="mb-3">
      <label for="password" class="form-label">Пароль</label>
      <input type="password" name="password" id="password" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-primary">Войти</button>
  </form>
</div>
</body>
</html>
