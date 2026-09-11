<?php
// RevSpecs database connection.
// XAMPP defaults: host=127.0.0.1, user=root, password is usually empty.

define('DB_HOST', 'sql309.infinityfree.com');
define('DB_NAME', 'if0_42875884_revspecs');
define('DB_USER', 'if0_42875884');
define('DB_PASS', 'WpnbyHLNU5');

function db(): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) return $pdo;

    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    return $pdo;
}
