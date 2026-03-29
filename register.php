<?php
// register.php
session_start();
require_once 'config.php'; // ваш файл с PDO-подключением

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.html');
    exit;
}

$username = trim($_POST['username']   ?? '');
$email    = trim($_POST['email']      ?? '');
$password = trim($_POST['password']   ?? '');

if ($username === '' || $email === '' || $password === '') {
    // Можно сохранить сообщение об ошибке в сессии и показать его на форме
    $_SESSION['reg_error'] = 'Все поля обязательны.';
    header('Location: register.html');
    exit;
}

// 1) Проверяем, нет ли уже такого login или email
$stmt = $pdo->prepare("SELECT id FROM users WHERE username = :u OR email = :e");
$stmt->execute([':u' => $username, ':e' => $email]);
if ($stmt->fetch()) {
    $_SESSION['reg_error'] = 'Пользователь с таким именем или почтой уже существует.';
    header('Location: register.html');
    exit;
}

// 2) Вставляем нового пользователя
$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $pdo->prepare("
    INSERT INTO users (username, email, password)
    VALUES (:u, :e, :p)
");
$stmt->execute([':u' => $username, ':e' => $email, ':p' => $hash]);

// 3) Сразу «логиним» пользователя (сессионный способ более надёжный, чем cookie)
$_SESSION['user'] = $username;

// 4) Редирект на index.php (после header – никаких echo!)
header('Location: vacancies.php');
exit;
