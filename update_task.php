<?php
require_once 'config.php';
session_start();

if (!isset($_POST['task_id'], $_POST['status']) || !isset($_COOKIE['user'])) {
    http_response_code(400);
    die(json_encode(['success' => false]));
}

try {
    $stmt = $pdo->prepare("
        UPDATE tasks 
        SET status = ?
        WHERE id = ? AND created_by = ?
    ");
    $success = $stmt->execute([
        $_POST['status'],
        (int)$_POST['task_id'],
        $_COOKIE['user']
    ]);
    
    echo json_encode(['success' => $success]);
} catch(PDOException $e) {
    error_log("Update task error: ".$e->getMessage());
    echo json_encode(['success' => false]);
}