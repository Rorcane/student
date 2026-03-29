<?php
// add_vacancy.php
if (!isset($_COOKIE['user'])) {
  header('Location: login.html');
  exit();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <title>Добавить вакансию</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  	<link rel="icon" type="image/png" href="/favicon-96x96.png" sizes="96x96" />
	<link rel="icon" type="image/svg+xml" href="/favicon.svg" />
	<link rel="shortcut icon" href="/favicon.ico" />
	<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png" />
	<link rel="manifest" href="/site.webmanifest" />ы
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <!-- Общий стиль сайта -->
  <link rel="stylesheet" href="css/style.css" />
  
  <style>
    /* Дополнительные стили для страницы добавления вакансии */
    .form-container {
      max-width: 800px;
      margin: 2rem auto;
      background: #fff;
      padding: 2rem;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }
    .form-title {
      margin-bottom: 1.5rem;
      font-weight: 600;
      text-align: center;
    }
    .form-label {
      font-weight: 500;
    }
    .btn-submit {
      width: 100%;
      padding: 0.75rem;
      font-size: 1.1rem;
    }
  </style>
</head>
<body class="bg-light">

  <!-- Шапка сайта -->
  <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
    <div class="container">
      <a class="navbar-brand fw-bold" href="vacancies.php">
        <img src="img/logo.png" alt="TruWork" width="30" height="30" class="me-2">
        TruWork
      </a>
      <div class="ms-auto">
        <?php
          if (isset($_COOKIE['user'])) {
            echo '<a href="profile.php" class="text-decoration-none">' . htmlspecialchars($_COOKIE['user']) . '</a>';
          } else {
            echo '<a href="login.html" class="btn btn-primary">Войти</a>';
          }
        ?>
      </div>
    </div>
  </nav>

  <!-- Форма добавления вакансии -->
  <div class="container">
    <div class="form-container">
      <h2 class="form-title">Добавить новую вакансию</h2>
      
      <form action="process_vacancy.php" method="POST">
        <div class="mb-3">
          <label for="jobTitle" class="form-label">Название вакансии</label>
          <input type="text" class="form-control" id="jobTitle" name="title" placeholder="Введите название вакансии" required>
        </div>
        
        <div class="mb-3">
          <label for="companyName" class="form-label">Компания</label>
          <input type="text" class="form-control" id="companyName" name="company" placeholder="Название компании" required>
        </div>
        
        <div class="mb-3 row">
          <div class="col-md-6">
            <label for="jobCategory" class="form-label">Категория</label>
            <select class="form-select" id="jobCategory" name="category" required>
              <option value="" disabled selected>Выберите категорию</option>
              <option value="IT">IT</option>
              <option value="Продажи">Продажи</option>
              <option value="Маркетинг">Маркетинг</option>
              <option value="Административная работа">Административная работа</option>
              <option value="Другое">Другое</option>
            </select>
          </div>
          <div class="col-md-6">
            <label for="salaryRange" class="form-label">Зарплата</label>
            <input type="text" class="form-control" id="salaryRange" name="salary" placeholder="Укажите диапазон зарплаты" required>
          </div>
        </div>
        
        <div class="mb-3">
          <label for="location" class="form-label">Местоположение</label>
          <input type="text" class="form-control" id="location" name="location" placeholder="Город или удалённо" required>
        </div>
        
        <div class="mb-3">
          <label for="jobDescription" class="form-label">Описание вакансии</label>
          <textarea class="form-control" id="jobDescription" name="description" rows="5" placeholder="Подробно опишите требования и обязанности" required></textarea>
