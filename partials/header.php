<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= isset($page_title) ? htmlspecialchars($page_title) : 'TruWork' ?></title>
  <link rel="stylesheet" href="/assets/css/styles.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
  <header class="header">
    <div class="container wrap">
      <a class="brand" href="/"><img src="/assets/img/logo.png" alt="logo"><span>TruWork</span></a>
      <nav class="nav" aria-label="Главное меню">
        <a href="/vacancies.php">Вакансии</a>
        <a href="/companies.php">Компании</a>
        <a href="/about.php">О проекте</a>
        <a class="cta" href="/post.php">Добавить вакансию</a>
      </nav>
      <button class="hamburger" aria-label="Меню">☰</button>
    </div>
  </header>
  <main class="container" role="main" style="padding-top:18px">
