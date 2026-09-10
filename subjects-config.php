<?php
/**
 * DB-backed replacement for the old subjects-config.php.
 *
 * Existing RevSpecs files can continue doing:
 *   $subjects = include 'subjects-config.php';
 *
 * The admin panel changes the database, and the picker, reviewer and AI
 * generator automatically see those changes.
 */
require_once __DIR__ . '/config/database.php';

try {
    $rows = db()->query(
        'SELECT code, name, year, pdf_path FROM subjects ORDER BY year ASC, code ASC'
    )->fetchAll();
} catch (Throwable $e) {
    http_response_code(500);
    die('RevSpecs database is not configured. Import database.sql first.');
}

$subjects = [];
foreach ($rows as $row) {
    $subjects[$row['code']] = [
        'name' => $row['name'],
        'year' => (int)$row['year'],
        'pdf'  => $row['pdf_path'] ?? '',
    ];
}

return $subjects;
