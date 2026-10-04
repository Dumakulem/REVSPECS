<?php
require_once __DIR__ . '/_common.php';

try { $count = (int)db()->query('SELECT COUNT(*) FROM admins')->fetchColumn(); }
catch (Throwable $e) { exit('Database not ready. Import database.sql first.'); }
if ($count > 0) exit('An admin account already exists. Delete this setup.php file.');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';
    if (!preg_match('/^[A-Za-z0-9_.-]{3,50}$/', $username)) $error = 'Username must be 3–50 characters and use letters, numbers, _, ., or -.';
    elseif (strlen($password) < 8) $error = 'Password must be at least 8 characters.';
    elseif ($password !== $confirm) $error = 'Passwords do not match.';
    else {
        $stmt = db()->prepare('INSERT INTO admins (username, password_hash) VALUES (?, ?)');
        $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT)]);
        header('Location: login.php?created=1'); exit;
    }
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>RevSpecs — Create Admin</title><style>:root{--ink:#1B4332;--soft:#5C8374;--line:#CDE8D5;--orange:#E67E22;--green:#27AE60}*{box-sizing:border-box}body{margin:0;background:#fff;color:var(--ink);font-family:Arial,sans-serif;min-height:100vh;display:grid;place-items:center}.box{width:min(430px,calc(100% - 32px));border:2px solid var(--line);border-top:6px solid var(--green);padding:30px}h1{margin:0 0 6px}p{color:var(--soft)}label{display:block;font-weight:700;font-size:.85rem;margin:14px 0 6px}input{width:100%;padding:11px;border:2px solid var(--line);border-radius:4px}button{margin-top:18px;width:100%;padding:12px;border:0;background:var(--orange);color:white;font-weight:700;border-radius:4px}.error{color:#a93226;background:#fdecea;padding:10px;margin-top:15px}</style></head><body><main class="box"><h1>Create admin</h1><p>This is a one-time setup. Delete <b>setup.php</b> after creating the account.</p><?php if($error):?><div class="error"><?=h($error)?></div><?php endif;?><form method="post"><input type="hidden" name="csrf" value="<?=h(csrf_token())?>"><label>Username</label><input name="username" required><label>Password</label><input type="password" name="password" required><label>Confirm password</label><input type="password" name="confirm" required><button>Create admin</button></form></main></body></html>