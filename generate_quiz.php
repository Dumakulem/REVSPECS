<?php
/**
 * generate_quiz.php
 *
 * Serves a PRE-BUILT quiz for a subject. Quizzes are uploaded once, offline,
 * through the admin panel (admin/upload-quiz.php) — see quizzes/PROMPT_TEMPLATE.md
 * for how to generate the JSON in the first place. This endpoint just looks
 * up the subject's quiz_path in the `subjects` table and streams the file —
 * no API key, no per-request cost, no external calls.
 *
 * Usage: generate_quiz.php?subject=CS+211
 */

require_once __DIR__ . '/config/database.php';

header('Content-Type: application/json');

$subject = $_GET['subject'] ?? '';

// Looking the code up in the DB doubles as the whitelist check — an
// unknown code simply won't match a row, same protection subjects-config.php
// used to give, minus the extra file to keep in sync.
$stmt = db()->prepare('SELECT quiz_path FROM subjects WHERE code = ? LIMIT 1');
$stmt->execute([$subject]);
$row = $stmt->fetch();

if (!$row) {
    http_response_code(404);
    echo json_encode(['error' => 'Unknown subject']);
    exit;
}

$quizPath = $row['quiz_path'];

if (!$quizPath) {
    http_response_code(404);
    echo json_encode(['error' => 'No quiz has been generated yet for this subject']);
    exit;
}

$fullPath = __DIR__ . '/' . ltrim($quizPath, '/');

if (!is_readable($fullPath)) {
    http_response_code(500);
    echo json_encode(['error' => 'Quiz file is missing on the server']);
    exit;
}

$json = file_get_contents($fullPath);

// Sanity check — don't forward a corrupted file as if it were a valid quiz.
if (json_decode($json) === null) {
    http_response_code(500);
    echo json_encode(['error' => 'Stored quiz file is not valid JSON']);
    exit;
}

echo $json;