<?php
// auth.php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username']);
  $password = trim($_POST['password']);
  
  // Ищем пользователя по имени
  $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
  $stmt->execute([$username]);
  $user = $stmt->fetch();
  
  
  if ($user) {
    // Проверяем пароль
    if (password_verify($password, $user['password'])) {
      // Авторизация успешна
      // Ставим куки на 1 день
      setcookie('user', $username, time() + 86400, '/');
      header('Location: vacancies.php'); // на главную
      exit();
    } else {
      echo "<h2>Неверный пароль. <a href='login.html'>Повторить</a></h2>";
    }
  } else {
    echo "<h2>Пользователь не найден. <a href='login.html'>Повторить</a></h2>";
  }
  
} else {
  echo "Неверный метод запроса.";
}
