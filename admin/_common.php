<?php
// Hardened session cookie flags — set before session_start() so they apply
// to every page that includes this file (which is now all of them).
session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
]);
session_start();
require_once __DIR__ . '/../config/database.php';

function require_admin(): void {
    if (empty($_SESSION['admin_id'])) {
        header('Location: login.php');
        exit;
    }
}

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}

function check_csrf(): void {
    $token = $_POST['csrf'] ?? '';
    if (!$token || !hash_equals($_SESSION['csrf'] ?? '', $token)) {
        http_response_code(403);
        exit('Invalid request token. Refresh the page and try again.');
    }
}

// Lightweight brute-force throttle: 5 failed attempts locks the form for
// the rest of a 15-minute window. Session-based, so it's per-browser, not
// a substitute for real rate limiting at the network level — but it stops
// naive password-guessing scripts for free.
function login_is_throttled(): bool {
    $data = $_SESSION['login_throttle'] ?? null;
    if (!$data || time() - $data['first'] > 900) return false;
    return $data['count'] >= 5;
}

function login_register_failure(): void {
    $data = $_SESSION['login_throttle'] ?? null;
    if (!$data || time() - $data['first'] > 900) $data = ['count' => 0, 'first' => time()];
    $data['count']++;
    $_SESSION['login_throttle'] = $data;
}

function login_reset_throttle(): void {
    unset($_SESSION['login_throttle']);
}

function h(?string $value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// Renamed from delete_pdf() — this deletes any stored upload (PDF or quiz
// JSON) that lives under the project root, given its DB-stored relative path.
function delete_stored_file(?string $relativePath): void {
    if (!$relativePath) return;
    $root = realpath(__DIR__ . '/..');
    if (!$root) return;
    $full = realpath(__DIR__ . '/../' . ltrim($relativePath, '/'));
    if ($full && str_starts_with($full, $root . DIRECTORY_SEPARATOR) && is_file($full)) {
        @unlink($full);
    }
}

/**
 * Validates a decoded quiz JSON payload against the RevSpecs quiz schema
 * (see quizzes/PROMPT_TEMPLATE.md). Returns an error message describing the
 * first problem found, or null if the payload is valid.
 */
function validate_quiz_json($data): ?string {
    if (!is_array($data)) {
        return 'File is not valid JSON, or its top level is not an object.';
    }
    if (!isset($data['items']) || !is_array($data['items']) || !$data['items']) {
        return 'JSON must contain a non-empty "items" array.';
    }

    $allowedTypes = ['multiple_choice', 'fill_blank', 'true_false', 'identification'];

    foreach ($data['items'] as $i => $item) {
        $n = $i + 1;
        if (!is_array($item)) {
            return "Item #$n is not a valid object.";
        }
        if (empty($item['question']) || !is_string($item['question'])) {
            return "Item #$n is missing a \"question\" string.";
        }
        if (empty($item['type']) || !in_array($item['type'], $allowedTypes, true)) {
            return "Item #$n has an invalid \"type\" (must be one of: " . implode(', ', $allowedTypes) . ").";
        }
        if (empty($item['explanation']) || !is_string($item['explanation'])) {
            return "Item #$n is missing an \"explanation\" string.";
        }

        switch ($item['type']) {
            case 'multiple_choice':
                if (!isset($item['options']) || !is_array($item['options']) || count($item['options']) < 2) {
                    return "Item #$n (multiple_choice) needs an \"options\" array with at least 2 choices.";
                }
                if (!isset($item['correctIndex']) || !is_int($item['correctIndex'])
                    || $item['correctIndex'] < 0 || $item['correctIndex'] >= count($item['options'])) {
                    return "Item #$n (multiple_choice) has an invalid \"correctIndex\".";
                }
                break;
            case 'true_false':
                if (!array_key_exists('answer', $item) || !is_bool($item['answer'])) {
                    return "Item #$n (true_false) needs a boolean \"answer\" (true or false, not a string).";
                }
                break;
            case 'fill_blank':
            case 'identification':
                if (empty($item['answer']) || !is_string($item['answer'])) {
                    return "Item #$n ({$item['type']}) is missing an \"answer\" string.";
                }
                break;
        }
    }

    return null;
}