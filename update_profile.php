<?php
require_once 'config.php';
if (!isset($_COOKIE['user'])) header('Location: login.html');

$columns = [];
$stmt = $pdo->query("SHOW COLUMNS FROM users");
foreach ($stmt as $row) {
    $columns[$row['Field']] = true;
}

$required = [
    'phone' => "ALTER TABLE users ADD COLUMN phone VARCHAR(255) NULL",
    'address' => "ALTER TABLE users ADD COLUMN address VARCHAR(255) NULL",
    'bio' => "ALTER TABLE users ADD COLUMN bio TEXT NULL",
    'avatar' => "ALTER TABLE users ADD COLUMN avatar VARCHAR(255) NULL",
];

foreach ($required as $name => $sql) {
    if (!isset($columns[$name])) {
        $pdo->exec($sql);
    }
}

$currentUser = $_COOKIE['user'];

$avatarPath = null;

// Обработка файла аватара
if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === 0) {
    $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
    $avatarPath = 'avatars/' . $currentUser . '_' . time() . '.' . $ext;

    // Создать папку avatars если нет
    if (!is_dir('avatars')) mkdir('avatars', 0755, true);

    move_uploaded_file($_FILES['avatar']['tmp_name'], $avatarPath);
}

// Обновление данных
$stmt = $pdo->prepare("
    UPDATE users SET fullname=:full_name, email=:email, phone=:phone, address=:address, bio=:bio
    " . ($avatarPath ? ", avatar=:avatar" : "") . "
    WHERE username=:user
");

$params = [
    ':full_name' => $_POST['full_name'] ?? '',
    ':email'     => $_POST['email'] ?? '',
    ':phone'     => $_POST['phone'] ?? '',
    ':address'   => $_POST['address'] ?? '',
    ':bio'       => $_POST['bio'] ?? '',
    ':user'      => $currentUser
];

if ($avatarPath) {
    $params[':avatar'] = $avatarPath;
}

$stmt->execute($params);

header('Location: profile.php');
exit();
