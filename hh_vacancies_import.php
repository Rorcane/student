<?php
declare(strict_types=1);

/**
 * Production-ready import вакансий из HeadHunter API в таблицу jobs.
 *
 * Что делает файл:
 * - получает вакансии из HH API по поисковому запросу;
 * - нормализует данные;
 * - сохраняет записи в MySQL через PDO;
 * - пропускает дубликаты по external_id;
 * - выбрасывает исключения при ошибках API/БД.
 *
 * Требования к таблице jobs:
 * - id
 * - external_id
 * - title
 * - company
 * - salary
 * - city
 * - description
 * - url
 * - source
 * - created_at
 */

/**
 * Выполняет GET-запрос к API HeadHunter и возвращает массив вакансий.
 *
 * @param string $query Поисковый запрос, например "php"
 * @param int $page Номер страницы выдачи, начиная с 0
 * @return array<int, array<string, mixed>>
 *
 * @throws InvalidArgumentException
 * @throws RuntimeException
 */
function getVacanciesFromHH(string $query, int $page = 0): array
{
    $query = trim($query);

    if ($query === '') {
        throw new InvalidArgumentException('Поисковый запрос не может быть пустым.');
    }

    if ($page < 0) {
        throw new InvalidArgumentException('Параметр $page не может быть отрицательным.');
    }

    $baseUrl = 'https://api.hh.ru/vacancies';
    $params = [
        'text' => $query,
        'area' => 160,      // Казахстан
        'page' => $page,
        'per_page' => 20,
    ];

    $url = $baseUrl . '?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);

    $ch = curl_init();

    if ($ch === false) {
        throw new RuntimeException('Не удалось инициализировать cURL.');
    }

    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPGET => true,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_MAXREDIRS => 0,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'User-Agent: TruWork/1.0 (+https://truwork.local; admin@truwork.local)',
        ],
    ]);

    $responseBody = curl_exec($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($responseBody === false) {
        $curlError = curl_error($ch);
        curl_close($ch);

        throw new RuntimeException('Ошибка запроса к HeadHunter API: ' . $curlError);
    }

    curl_close($ch);

    if ($httpCode !== 200) {
        throw new RuntimeException('HeadHunter API вернул HTTP ' . $httpCode . '.');
    }

    try {
        /** @var array<string, mixed> $data */
        $data = json_decode($responseBody, true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $e) {
        throw new RuntimeException('Не удалось декодировать JSON от HeadHunter API: ' . $e->getMessage(), 0, $e);
    }

    if (!isset($data['items']) || !is_array($data['items'])) {
        throw new RuntimeException('Некорректный ответ HeadHunter API: отсутствует массив items.');
    }

    $vacancies = [];

    foreach ($data['items'] as $item) {
        if (!is_array($item) || empty($item['id'])) {
            continue;
        }

        $vacancies[] = [
            'external_id' => (string) $item['id'],
            'title' => trim((string) ($item['name'] ?? 'Без названия')),
            'company' => trim((string) ($item['employer']['name'] ?? 'Не указано')),
            'salary' => formatHhSalary($item['salary'] ?? null),
            'city' => trim((string) ($item['area']['name'] ?? 'Не указано')),
            'description' => buildHhDescription($item['snippet'] ?? null),
            'url' => trim((string) ($item['alternate_url'] ?? '')),
            'source' => 'hh',
        ];
    }

    return $vacancies;
}

/**
 * Сохраняет вакансии в MySQL через PDO.
 * Если external_id уже существует, запись пропускается.
 *
 * @param array<int, array<string, mixed>> $vacancies
 * @param PDO $pdo
 * @return int Количество новых сохранённых вакансий
 *
 * @throws RuntimeException
 */
function saveVacanciesToMySQL(array $vacancies, PDO $pdo): int
{
    if ($vacancies === []) {
        return 0;
    }

    $checkStmt = $pdo->prepare('SELECT 1 FROM jobs WHERE external_id = :external_id LIMIT 1');
    $insertStmt = $pdo->prepare(
        'INSERT INTO jobs (
            external_id,
            title,
            company,
            salary,
            city,
            description,
            url,
            source,
            created_at
        ) VALUES (
            :external_id,
            :title,
            :company,
            :salary,
            :city,
            :description,
            :url,
            :source,
            NOW()
        )'
    );

    $insertedCount = 0;

    try {
        $pdo->beginTransaction();

        foreach ($vacancies as $vacancy) {
            if (empty($vacancy['external_id'])) {
                continue;
            }

            $externalId = (string) $vacancy['external_id'];

            $checkStmt->execute([
                ':external_id' => $externalId,
            ]);

            if ($checkStmt->fetchColumn()) {
                continue;
            }

            $insertStmt->execute([
                ':external_id' => $externalId,
                ':title' => mb_substr((string) ($vacancy['title'] ?? 'Без названия'), 0, 255),
                ':company' => mb_substr((string) ($vacancy['company'] ?? 'Не указано'), 0, 255),
                ':salary' => mb_substr((string) ($vacancy['salary'] ?? 'не указано'), 0, 255),
                ':city' => mb_substr((string) ($vacancy['city'] ?? 'Не указано'), 0, 255),
                ':description' => (string) ($vacancy['description'] ?? 'Не указано'),
                ':url' => mb_substr((string) ($vacancy['url'] ?? ''), 0, 1000),
                ':source' => 'hh',
            ]);

            $insertedCount++;
        }

        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw new RuntimeException('Ошибка сохранения вакансий в MySQL: ' . $e->getMessage(), 0, $e);
    }

    return $insertedCount;
}

/**
 * Преобразует salary из HH API в строку для сохранения в БД.
 *
 * @param array<string, mixed>|null $salaryData
 * @return string
 */
function formatHhSalary(?array $salaryData): string
{
    if ($salaryData === null) {
        return 'не указано';
    }

    $from = isset($salaryData['from']) ? (string) $salaryData['from'] : null;
    $to = isset($salaryData['to']) ? (string) $salaryData['to'] : null;
    $currency = isset($salaryData['currency']) ? (string) $salaryData['currency'] : '';

    if ($from !== null && $to !== null) {
        return sprintf('от %s до %s %s', $from, $to, $currency);
    }

    if ($from !== null) {
        return sprintf('от %s %s', $from, $currency);
    }

    if ($to !== null) {
        return sprintf('до %s %s', $to, $currency);
    }

    return 'не указано';
}

/**
 * Собирает краткое описание вакансии из snippet/requirement/responsibility.
 *
 * @param array<string, mixed>|null $snippet
 * @return string
 */
function buildHhDescription(?array $snippet): string
{
    if ($snippet === null) {
        return 'Не указано';
    }

    $parts = [];

    if (!empty($snippet['requirement'])) {
        $parts[] = cleanText((string) $snippet['requirement']);
    }

    if (!empty($snippet['responsibility'])) {
        $parts[] = cleanText((string) $snippet['responsibility']);
    }

    $description = trim(implode(' | ', array_filter($parts)));

    return $description !== '' ? $description : 'Не указано';
}

/**
 * Удаляет HTML-теги и нормализует пробелы.
 *
 * @param string $text
 * @return string
 */
function cleanText(string $text): string
{
    $text = strip_tags($text);
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

    return trim($text);
}

/*
|--------------------------------------------------------------------------
| Пример использования
|--------------------------------------------------------------------------
|
| В продакшене вынесите параметры БД в конфиг/ENV.
|
*/

$host = 'MySQL-8.0';
$dbName = 'truworkk_Baza';
$dbUser = 'root';
$dbPass = '';
$dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $host, $dbName);

$pdo = new PDO(
    $dsn,
    $dbUser,
    $dbPass,
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
);

try {
    $jobs = getVacanciesFromHH('php');
    $savedCount = saveVacanciesToMySQL($jobs, $pdo);

    echo 'Импорт завершён. Добавлено вакансий: ' . $savedCount . PHP_EOL;
} catch (Throwable $e) {
    error_log('[HH Import] ' . $e->getMessage());
    http_response_code(500);
    echo 'Ошибка импорта вакансий: ' . $e->getMessage() . PHP_EOL;
}

/*
SQL для добавления уникального индекса:

ALTER TABLE jobs
ADD UNIQUE KEY uq_jobs_external_id (external_id);
*/
