<?php
require_once 'config.php';
session_start();

// Кто откликается — из куки или сессии:
$applicant = $_COOKIE['user'] ?? ($_SESSION['username'] ?? 'Аноним');

// ID вакансии из формы
$vacancyId = intval($_POST['vacancy_id'] ?? 0);
if ($vacancyId > 0) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO applications (vacancy_id, applicant)
            VALUES (:vacancy_id, :applicant)
        ");
        $stmt->execute([
            ':vacancy_id' => $vacancyId,
            ':applicant'  => $applicant
        ]);
    } catch (PDOException $e) {
        // Можно логировать или показывать ошибку
    }
}

header('Location: vacancies.php');
exit();
