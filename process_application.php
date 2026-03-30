<?php
require_once 'config.php';
require_once 'hh_jobs.php';

if (!isset($_COOKIE['user'])) {
    header('Location: login.html');
    exit();
}

$applicant = (string) $_COOKIE['user'];
$vacancyId = isset($_POST['vacancy_id']) ? (int) $_POST['vacancy_id'] : 0;
$source = isset($_POST['source']) ? trim((string) $_POST['source']) : 'internal';

if ($vacancyId > 0) {
    if ($source === 'hh') {
        ensureHhApplicationsTable($pdo);

        $stmt = $pdo->prepare(
            'INSERT IGNORE INTO hh_applications (job_id, applicant, created_at)
             VALUES (:job_id, :applicant, NOW())'
        );
        $stmt->execute([
            ':job_id' => $vacancyId,
            ':applicant' => $applicant,
        ]);

        header('Location: my_applications.php');
        exit();
    }

    $ins = $pdo->prepare(
        'INSERT INTO applications (vacancy_id, applicant, created_at)
         VALUES (:vid, :app, NOW())'
    );
    $ins->execute([
        ':vid' => $vacancyId,
        ':app' => $applicant,
    ]);

    $uStmt = $pdo->prepare('SELECT author FROM vacancies WHERE id = ?');
    $uStmt->execute([$vacancyId]);
    $author = $uStmt->fetchColumn();

    if ($author) {
        $url = 'chat.php?with=' . urlencode((string) $author) . '&vacancy=' . $vacancyId;
        header('Location: ' . $url);
        exit();
    }
}

header('Location: vacancies.php');
exit();
