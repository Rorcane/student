<?php
require_once 'config.php';

// Проверка авторизации
if (!isset($_COOKIE['user'])) {
    header("Location: login.html");
    exit();
}

$username = $_COOKIE['user'];

// Проверка роли пользователя
$stmt = $pdo->prepare("SELECT role FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

if (!$user || $user['role'] !== 'admin') {
    echo "Доступ запрещён. Только администратор может удалять вакансии.";
    exit();
}

// Получение ID вакансии
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "Некорректный ID вакансии.";
    exit();
}

$vacancy_id = (int)$_GET['id'];

// Удаление вакансии
$stmt = $pdo->prepare("DELETE FROM vacancies WHERE id = ?");
if ($stmt->execute([$vacancy_id])) {
    header("Location: vacancies.php");
    exit();
} else {
    echo "Ошибка при удалении вакансии.";
}
