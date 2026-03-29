<?php
// index.php

// Подключение header (при необходимости) или начало HTML
// include_once 'blocks/header.php';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Добро пожаловать на рынок на основе ИИ</title>
    <!-- Подключение шрифта (опционально) -->
    <!-- <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,600,700&display=swap" rel="stylesheet"> -->
    
    <!-- Подключение Font Awesome через CDN -->
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css"
        integrity="sha512-3eZ6Ax+g09+5VoYc9ShO7Jf1v8NtYbBS54o+PpO2Id6Uv7qIcBLRWeB0LeqvSNc4bCyT+u6d7WWrN+p5X7pA=="
        crossorigin="anonymous"
        referrerpolicy="no-referrer"
    />

    <style>
        /* Общие стили */
        body {
            margin: 0;
            padding: 0;
            font-family: sans-serif;
            background-color: #f9f9f9;
            color: #333;
        }
        h1, h2, h3, h4 {
            margin: 0;
            padding: 0;
        }
        a {
            text-decoration: none;
            color: inherit;
        }
        /* Контейнер для контента */
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Hero-блок */
        .hero {
            text-align: center;
            padding: 60px 20px;
            background-color: #fff;
            margin-bottom: 30px;
            border-radius: 10px;
        }
        .hero h1 {
            font-size: 2em;
            margin-bottom: 10px;
        }
        .hero p {
            font-size: 1.2em;
            color: #666;
            margin-bottom: 20px;
        }
        .hero img {
            max-width: 100%;
            height: auto;
            border-radius: 10px;
        }

        /* Блок с тремя колонками */
        .features {
            display: flex;
            flex-wrap: wrap; /* Чтобы на маленьких экранах колонки шли друг под другом */
            gap: 20px;
            margin-bottom: 30px;
        }
        .feature {
            flex: 1;
            min-width: 250px; /* Минимальная ширина для колонки */
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-sizing: border-box;
            text-align: center;
        }
        .feature .icon {
            font-size: 2em;
            color: #007bff; /* Цвет иконки */
            margin-bottom: 10px;
        }
        .feature h2 {
            font-size: 1.3em;
            margin-bottom: 10px;
        }
        .feature p {
            color: #666;
        }

        /* Контактный блок */
        .contact {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }
        .contact h3 {
            font-size: 1.4em;
            margin-bottom: 10px;
        }
        .contact p {
            font-size: 1.1em;
            color: #666;
        }

        /* Footer */
        footer {
            text-align: center;
            padding: 20px;
            font-size: 0.9em;
            color: #999;
        }
    </style>
</head>
<body>

<!-- Основной контейнер -->
<div class="container">

    <!-- Hero-раздел -->
    <section class="hero">
        <h1>Добро пожаловать на рынок на основе ИИ</h1>
        <p>Используйте силу ИИ для умного подбора персонала, проверки IdM, управления HR и безопасных транзакций.</p>
        <!-- Замените src на путь к вашему изображению -->
        <img src="img/hero-example.jpg" alt="AI Sphere">
    </section>

    <!-- Блок с тремя колонками (фичи) -->
    <section class="features">
        <div class="feature">
            <!-- Иконка (пример: user-check) -->
            <div class="icon">
                <i class="fas fa-user-check"></i>
            </div>
            <h2>Умный подбор персонала</h2>
            <p>Система ИИ для быстрого и точного подбора персонала на основе анализа данных и компетенций.</p>
        </div>
        <div class="feature">
            <!-- Иконка (пример: id-card) -->
            <div class="icon">
                <i class="far fa-id-card"></i>
            </div>
            <h2>Проверка IdM</h2>
            <p>Управляйте доступом и безопасностью с помощью автоматизированной системы идентификации.</p>
        </div>
        <div class="feature">
            <!-- Иконка (пример: lock) -->
            <div class="icon">
                <i class="fas fa-lock"></i>
            </div>
            <h2>Безопасные транзакции</h2>
            <p>Применение технологий шифрования для защиты данных и предотвращения мошенничества.</p>
        </div>
    </section>

    <!-- Контактный блок -->
    <section class="contact">
        <h3>Свяжитесь с нами</h3>
        <p>Телефон: +123 567 890</p>
        <p>Email: info@aimarket.com</p>
    </section>

</div>

<!-- Футер -->
<footer>
    2025 © AI Market. Все права защищены.
</footer>

</body>
</html>

<?php
// Подключение footer (при необходимости) или окончание HTML
// include_once 'blocks/footer.php';
?>
