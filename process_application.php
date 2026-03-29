<?php
// process_application.php
require_once 'config.php';

if (!isset($_COOKIE['user'])) {
    header('Location: login.html');
    exit();
}

$applicant  = $_COOKIE['user'];
$vacancy_id = isset($_POST['vacancy_id']) ? (int)$_POST['vacancy_id'] : 0;

if ($vacancy_id > 0) {
    // 1) Вставляем отклик
    $ins = $pdo->prepare("
        INSERT INTO applications (vacancy_id, applicant, created_at)
        VALUES (:vid, :app, NOW())
    ");
    $ins->execute([
        ':vid' => $vacancy_id,
        ':app' => $applicant,
    ]);

    // 2) Узнаём автора вакансии
    $uStmt = $pdo->prepare("SELECT author FROM vacancies WHERE id = ?");
    $uStmt->execute([$vacancy_id]);
    $author = $uStmt->fetchColumn();

    if ($author) {
        // 3) Редирект в чат с передачей vacancy_id
        $url = 'chat.php?with=' . urlencode($author)
             . '&vacancy=' . $vacancy_id;
        header('Location: ' . $url);
        exit();
    }
}

// Если что-то пошло не так — возвращаем на страницу с вакансиями
header('Location: vacancies.php');
exit();
