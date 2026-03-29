<?php
session_start();
require_once 'config.php';

// Если пользователь не авторизован — переадресация
if (!isset($_COOKIE['user'])) {
  header('Location: login.php');
  exit();
}

// Получаем данные пользователя по имени из куки
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$_COOKIE['user']]);
$userData = $stmt->fetch();
if (!$userData) {
  setcookie('user', '', time()-3600, '/');
  header('Location: login.php');
  exit();
}

if (isset($_POST['data']) && trim($_POST['data']) !== '') {
  $data = trim($_POST['data']);
  // Запись в таблицу user_data (предположим, что структура: id, user_id, data, created_at)
  $stmt = $pdo->prepare("INSERT INTO user_data (user_id, data, created_at) VALUES (?, ?, NOW())");
  $stmt->execute([$userData['id'], $data]);
}

header('Location: index.php');
exit();
?>
