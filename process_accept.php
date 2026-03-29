<?php
// process_accept.php
require_once 'config.php';

if (!isset($_COOKIE['user'])) {
    header('Location: login.html');
    exit();
}

$current   = $_COOKIE['user'];
$vacancyId = isset($_POST['vacancy_id']) ? (int)$_POST['vacancy_id'] : 0;
$applicant = $_POST['applicant'] ?? '';

if ($vacancyId && $applicant) {
    $upd = $pdo->prepare("
        UPDATE applications
           SET status = 'accepted'
         WHERE vacancy_id = ? AND applicant = ?
    ");
    $upd->execute([$vacancyId, $applicant]);
}

header('Location: chat.php?with=' . urlencode($applicant) . '&vacancy=' . $vacancyId);
exit();
