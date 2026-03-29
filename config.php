<?php
// Включаем отображение ошибок для отладки
ini_set('display_errors', 1);
error_reporting(E_ALL);

//$host = 'localhost';//
$host = 'MySQL-8.0';
$db   = 'truworkk_Baza';
$user = 'root';
$pass = '';
$dsn  = "mysql:host=$host;dbname=$db;charset=utf8";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения к базе: " . $e->getMessage());
}
define('OPENAI_API_KEY', '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');
