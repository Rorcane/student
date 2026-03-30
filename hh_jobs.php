<?php

function hhJobsTableExists(PDO $pdo)
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = :table_name'
    );
    $stmt->execute([
        ':table_name' => 'jobs',
    ]);

    return (bool) $stmt->fetchColumn();
}

function ensureJobsTable(PDO $pdo)
{
    $sql = "CREATE TABLE IF NOT EXISTS jobs (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        external_id VARCHAR(64) NOT NULL,
        title VARCHAR(255) NOT NULL,
        company VARCHAR(255) NOT NULL,
        salary VARCHAR(255) NOT NULL DEFAULT 'не указано',
        city VARCHAR(255) NOT NULL DEFAULT 'Не указано',
        description TEXT NULL,
        url VARCHAR(1000) NOT NULL,
        source ENUM('internal', 'hh') NOT NULL DEFAULT 'hh',
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uq_jobs_external_id (external_id),
        KEY idx_jobs_created_at (created_at),
        KEY idx_jobs_source (source),
        KEY idx_jobs_title (title),
        KEY idx_jobs_company (company)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    $pdo->exec($sql);
}

function ensureHhApplicationsTable(PDO $pdo)
{
    $sql = "CREATE TABLE IF NOT EXISTS hh_applications (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        job_id INT UNSIGNED NOT NULL,
        applicant VARCHAR(255) NOT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        status VARCHAR(100) NOT NULL DEFAULT 'Новая',
        UNIQUE KEY uq_hh_applications_job_applicant (job_id, applicant),
        KEY idx_hh_applications_applicant (applicant),
        KEY idx_hh_applications_job_id (job_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    $pdo->exec($sql);
}

function hhApplicationsTableExists(PDO $pdo)
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = :table_name'
    );
    $stmt->execute([
        ':table_name' => 'hh_applications',
    ]);

    return (bool) $stmt->fetchColumn();
}

function hhApiGetJson($url)
{
    $ch = curl_init();

    if ($ch === false) {
        throw new RuntimeException('Не удалось инициализировать cURL.');
    }

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPGET, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'User-Agent: TruWork/1.0 (+https://truwork.local; support@truwork.local)',
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

    $data = json_decode($responseBody, true);
    if (!is_array($data)) {
        throw new RuntimeException('Некорректный JSON от HeadHunter API.');
    }

    return $data;
}

function getVacanciesFromHH($query, $page = 0)
{
    $query = trim((string) $query);
    $page = (int) $page;

    if ($query === '') {
        throw new InvalidArgumentException('Поисковый запрос не может быть пустым.');
    }

    if ($page < 0) {
        throw new InvalidArgumentException('Параметр page не может быть отрицательным.');
    }

    $params = [
        'text' => $query,
        'area' => 160,
        'page' => $page,
        'per_page' => 20,
        'order_by' => 'publication_time',
    ];

    $url = 'https://api.hh.ru/vacancies?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
    $data = hhApiGetJson($url);

    if (!isset($data['items']) || !is_array($data['items'])) {
        throw new RuntimeException('Некорректный ответ HeadHunter API.');
    }

    $vacancies = [];

    foreach ($data['items'] as $item) {
        if (!is_array($item) || empty($item['id'])) {
            continue;
        }

        $salaryData = isset($item['salary']) && is_array($item['salary']) ? $item['salary'] : null;
        $snippet = isset($item['snippet']) && is_array($item['snippet']) ? $item['snippet'] : null;

        $vacancies[] = [
            'external_id' => (string) $item['id'],
            'title' => trim((string) (isset($item['name']) ? $item['name'] : 'Без названия')),
            'company' => trim((string) (isset($item['employer']['name']) ? $item['employer']['name'] : 'Не указано')),
            'salary' => formatHhSalary($salaryData),
            'city' => trim((string) (isset($item['area']['name']) ? $item['area']['name'] : 'Не указано')),
            'description' => buildHhDescription($snippet),
            'url' => trim((string) (isset($item['alternate_url']) ? $item['alternate_url'] : '')),
            'source' => 'hh',
        ];
    }

    return $vacancies;
}

function getVacancyDetailsFromHH($externalId)
{
    $externalId = trim((string) $externalId);

    if ($externalId === '') {
        throw new InvalidArgumentException('Пустой external_id для детальной вакансии HH.');
    }

    $url = 'https://api.hh.ru/vacancies/' . rawurlencode($externalId);
    return hhApiGetJson($url);
}

function buildFullHhDescription($vacancyDetails, $fallbackDescription)
{
    $sections = [];

    if (is_array($vacancyDetails)) {
        if (!empty($vacancyDetails['description'])) {
            $sections[] = cleanHhText((string) $vacancyDetails['description']);
        }

        if (!empty($vacancyDetails['employment']['name'])) {
            $sections[] = 'Тип занятости: ' . cleanHhText((string) $vacancyDetails['employment']['name']);
        }

        if (!empty($vacancyDetails['schedule']['name'])) {
            $sections[] = 'График: ' . cleanHhText((string) $vacancyDetails['schedule']['name']);
        }

        if (!empty($vacancyDetails['experience']['name'])) {
            $sections[] = 'Опыт: ' . cleanHhText((string) $vacancyDetails['experience']['name']);
        }

        if (!empty($vacancyDetails['key_skills']) && is_array($vacancyDetails['key_skills'])) {
            $skills = [];
            foreach ($vacancyDetails['key_skills'] as $skill) {
                if (is_array($skill) && !empty($skill['name'])) {
                    $skills[] = cleanHhText((string) $skill['name']);
                }
            }
            if ($skills !== []) {
                $sections[] = 'Навыки: ' . implode(', ', $skills);
            }
        }
    }

    if ($fallbackDescription !== '') {
        array_unshift($sections, $fallbackDescription);
    }

    $sections = array_values(array_unique(array_filter($sections)));

    return trim(implode("\n\n", $sections));
}

function enrichHhVacancy($vacancy)
{
    if (!is_array($vacancy) || empty($vacancy['external_id'])) {
        return $vacancy;
    }

    try {
        $details = getVacancyDetailsFromHH($vacancy['external_id']);
        $vacancy['description'] = buildFullHhDescription($details, (string) (isset($vacancy['description']) ? $vacancy['description'] : ''));

        if (empty($vacancy['salary']) || $vacancy['salary'] === 'не указано') {
            $salaryData = isset($details['salary']) && is_array($details['salary']) ? $details['salary'] : null;
            $vacancy['salary'] = formatHhSalary($salaryData);
        }

        if (empty($vacancy['city']) && !empty($details['area']['name'])) {
            $vacancy['city'] = cleanHhText((string) $details['area']['name']);
        }
    } catch (Throwable $e) {
    }

    return $vacancy;
}

function saveVacanciesToMySQL($vacancies, PDO $pdo)
{
    if (!is_array($vacancies) || $vacancies === []) {
        return 0;
    }

    ensureJobsTable($pdo);

    $checkStmt = $pdo->prepare('SELECT 1 FROM jobs WHERE external_id = :external_id LIMIT 1');
    $updateStmt = $pdo->prepare(
        'UPDATE jobs
            SET title = :title,
                company = :company,
                salary = :salary,
                city = :city,
                description = :description,
                url = :url,
                source = :source
          WHERE external_id = :external_id'
    );
    $insertStmt = $pdo->prepare(
        'INSERT INTO jobs (external_id, title, company, salary, city, description, url, source, created_at)
         VALUES (:external_id, :title, :company, :salary, :city, :description, :url, :source, NOW())'
    );

    $insertedCount = 0;

    try {
        $pdo->beginTransaction();

        foreach ($vacancies as $vacancy) {
            if (!is_array($vacancy) || empty($vacancy['external_id'])) {
                continue;
            }

            $externalId = (string) $vacancy['external_id'];

            $checkStmt->execute([
                ':external_id' => $externalId,
            ]);

            $payload = [
                ':external_id' => $externalId,
                ':title' => mb_substr((string) (isset($vacancy['title']) ? $vacancy['title'] : 'Без названия'), 0, 255),
                ':company' => mb_substr((string) (isset($vacancy['company']) ? $vacancy['company'] : 'Не указано'), 0, 255),
                ':salary' => mb_substr((string) (isset($vacancy['salary']) ? $vacancy['salary'] : 'не указано'), 0, 255),
                ':city' => mb_substr((string) (isset($vacancy['city']) ? $vacancy['city'] : 'Не указано'), 0, 255),
                ':description' => (string) (isset($vacancy['description']) ? $vacancy['description'] : 'Не указано'),
                ':url' => mb_substr((string) (isset($vacancy['url']) ? $vacancy['url'] : ''), 0, 1000),
                ':source' => 'hh',
            ];

            if ($checkStmt->fetchColumn()) {
                $updateStmt->execute($payload);
                continue;
            }

            $insertStmt->execute($payload);

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

function formatHhSalary($salaryData)
{
    if (!is_array($salaryData)) {
        return 'не указано';
    }

    $from = isset($salaryData['from']) ? (string) $salaryData['from'] : null;
    $to = isset($salaryData['to']) ? (string) $salaryData['to'] : null;
    $currency = isset($salaryData['currency']) ? (string) $salaryData['currency'] : '';

    if ($from !== null && $to !== null) {
        return 'от ' . $from . ' до ' . $to . ' ' . $currency;
    }

    if ($from !== null) {
        return 'от ' . $from . ' ' . $currency;
    }

    if ($to !== null) {
        return 'до ' . $to . ' ' . $currency;
    }

    return 'не указано';
}

function buildHhDescription($snippet)
{
    if (!is_array($snippet)) {
        return 'Не указано';
    }

    $parts = [];

    if (!empty($snippet['requirement'])) {
        $parts[] = cleanHhText((string) $snippet['requirement']);
    }

    if (!empty($snippet['responsibility'])) {
        $parts[] = cleanHhText((string) $snippet['responsibility']);
    }

    $description = trim(implode(' | ', $parts));

    if ($description === '') {
        return 'Не указано';
    }

    return $description;
}

function cleanHhText($text)
{
    $text = strip_tags((string) $text);
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = preg_replace('/\s+/u', ' ', $text);

    return trim((string) $text);
}

function shouldSyncHh($cacheKey, $ttlSeconds = 900)
{
    $cacheFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'truwork_hh_sync_' . md5((string) $cacheKey) . '.cache';

    if (!is_file($cacheFile)) {
        return true;
    }

    $content = @file_get_contents($cacheFile);
    $lastSyncTime = (int) $content;

    if ($lastSyncTime <= 0) {
        return true;
    }

    return (time() - $lastSyncTime) >= (int) $ttlSeconds;
}

function markHhSync($cacheKey)
{
    $cacheFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'truwork_hh_sync_' . md5((string) $cacheKey) . '.cache';
    @file_put_contents($cacheFile, (string) time(), LOCK_EX);
}

function syncHeadHunterVacancies(PDO $pdo, $queries, $pagesPerQuery = 1)
{
    ensureJobsTable($pdo);
    ensureHhApplicationsTable($pdo);

    if (!is_array($queries)) {
        $queries = [];
    }

    $normalizedQueries = [];
    foreach ($queries as $query) {
        $query = trim((string) $query);
        if ($query === '') {
            continue;
        }
        if (!in_array($query, $normalizedQueries, true)) {
            $normalizedQueries[] = $query;
        }
    }

    $savedCount = 0;
    $pagesPerQuery = max(1, (int) $pagesPerQuery);

    foreach ($normalizedQueries as $query) {
        $cacheKey = 'hh_sync_' . mb_strtolower($query, 'UTF-8');

        if (!shouldSyncHh($cacheKey)) {
            continue;
        }

        for ($page = 0; $page < $pagesPerQuery; $page++) {
            $vacancies = getVacanciesFromHH($query, $page);
            foreach ($vacancies as $index => $vacancy) {
                $vacancies[$index] = enrichHhVacancy($vacancy);
            }
            $savedCount += saveVacanciesToMySQL($vacancies, $pdo);
        }

        markHhSync($cacheKey);
    }

    return $savedCount;
}

function hasAppliedToHhVacancy(PDO $pdo, $jobId, $applicant)
{
    ensureHhApplicationsTable($pdo);

    $stmt = $pdo->prepare('SELECT 1 FROM hh_applications WHERE job_id = :job_id AND applicant = :applicant LIMIT 1');
    $stmt->execute([
        ':job_id' => (int) $jobId,
        ':applicant' => (string) $applicant,
    ]);

    return (bool) $stmt->fetchColumn();
}
