<?php
include 'config.php'; // Подключаем файл с параметрами подключения

// Выполним простой запрос для проверки (например, выборка из таблицы users)
$sql = "SELECT * FROM users";
$result = $conn->query($sql);

if ($result) {
    echo "Соединение с базой данных успешно!";
} else {
    echo "Ошибка выполнения запроса: " . $conn->error;
}

$conn->close();
?>
