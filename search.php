<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Вакансии на рынке ИИ</title>
		<link rel="icon" type="image/png" href="/favicon-96x96.png" sizes="96x96" />
	<link rel="icon" type="image/svg+xml" href="/favicon.svg" />
	<link rel="shortcut icon" href="/favicon.ico" />
	<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png" />
	<link rel="manifest" href="/site.webmanifest" />
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/feather-icons"></script>
</head>
<body class="bg-gray-50 text-gray-800">
  <header class="max-w-4xl mx-auto my-10 text-center px-4">
    <h1 class="text-4xl font-bold mb-2">Вакансии на основе ИИ</h1>
    <p class="text-lg text-gray-600">Сортируйте открытые позиции по дате или названию.</p>
  </header>

   <!-- Сортировка -->
  <div class="max-w-4xl mx-auto px-4 mb-6">
    <label for="sort" class="font-medium">Сортировать по:</label>
    <select id="sort" onchange="location.search='?sort='+this.value" class="border rounded p-2 ml-2">
      <option value="date_posted" <?= \$sort === 'date_posted' ? 'selected' : '' ?>>Дате публикации</option>
      <option value="title" <?= \$sort === 'title' ? 'selected' : '' ?>>Заголовку</option>
    </select>
  </div>

  <!-- Список вакансий -->
  <section class="max-w-4xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-6 px-4 mb-16">
    <?php if (empty(\$vacancies)): ?>
      <p class="text-center col-span-full text-gray-600">Вакансии не найдены.</p>
    <?php else: foreach (\$vacancies as \$vac): ?>
      <div class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition">
        <div class="mb-4">
          <i data-feather="briefcase" class="w-8 h-8 text-indigo-600"></i>
        </div>
        <h3 class="text-xl font-semibold mb-2"><?= htmlspecialchars(\$vac['title']) ?></h3>
        <p class="text-gray-600 mb-2"><?= nl2br(htmlspecialchars(\$vac['description'])) ?></p>
        <p class="text-sm text-gray-500">Дата публикации: <?= htmlspecialchars(\$vac['date_posted']) ?></p>
      </div>
    <?php endforeach; endif; ?>
  </section>

  <footer class="bg-white py-8">
    <div class="max-w-4xl mx-auto grid grid-cols-1 md:grid-cols-2 px-4 gap-6">
      <div>
        <h4 class="font-semibold mb-2">Свяжитесь с нами</h4>
        <p>Эл. почта: <a href="mailto:info@example.com" class="text-indigo-600">info@example.com</a></p>
        <p>Телефон: <a href="tel:+1234567890" class="text-indigo-600">+1 234 567 890</a></p>
      </div>
      <div>
        <h4 class="font-semibold mb-2">Быстрые ссылки</h4>
        <ul class="space-y-1">
          <li><a href="#" class="hover:text-indigo-600">Политика</a></li>
          <li><a href="#" class="hover:text-indigo-600">Условия</a></li>
          <li><a href="#" class="hover:text-indigo-600">Поддержка</a></li>
        </ul>
      </div>
    </div>
  </footer>

  <script>feather.replace();</script>
</body>
</html>