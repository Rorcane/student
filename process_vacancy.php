<?php
require_once 'config.php';

if (!isset($_COOKIE['user'])) {
    header('Location: login.html');
    exit();
}

$username = $_COOKIE['user'];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title       = $_POST["title"] ?? '';
    $description = $_POST["description"] ?? '';
    $company     = $_POST["company"] ?? '';
    $location    = $_POST["location"] ?? '';
    $salary      = $_POST["salary"] ?? '';
    $categoryName = $_POST["category"] ?? '';

    // 1. Получаем author_id
    $stmtUser = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmtUser->execute([$username]);
    $user = $stmtUser->fetch();

    if (!$user) {
        die("Пользователь не найден");
    }

    $author_id = $user['id'];

    // 2. Получаем category_id
    $category_id = $_POST['category'] ?? null;

    if (!$category_id) {
        die("Категория не выбрана");
    }

    // 3. Вставка вакансии
    $stmt = $pdo->prepare("
        INSERT INTO vacancies 
        (title, description, company, location, salary, author_id, category_id, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
    ");

    $stmt->execute([
        $title,
        $description,
        $company,
        $location,
        $salary,
        $author_id,
        $category_id
    ]);

    header("Location: dashboard.php");
    exit();
}
