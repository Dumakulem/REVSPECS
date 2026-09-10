<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!empty($_SESSION['admin_id'])) {
    header('Location: index.php'); exit;
}

$error = '';
try {
    $adminCount = (int)db()->query('SELECT COUNT(*) FROM admins')->fetchColumn();
} catch (Throwable $e) {
    $adminCount = 0;
    $error = 'Database not ready. Import database.sql first.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $stmt = db()->prepare('SELECT id, username, password_hash FROM admins WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        header('Location: index.php'); exit;
    }
    $error = 'Invalid username or password.';
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>RevSpecs Admin — Login</title>
<style>
:root{--ink:#1B4332;--soft:#5C8374;--line:#CDE8D5;--orange:#E67E22;--green:#27AE60}*{box-sizing:border-box}body{margin:0;background:#fff;color:var(--ink);font-family:Arial,sans-serif;min-height:100vh;display:grid;place-items:center}.box{width:min(420px,calc(100% - 32px));border:2px solid var(--line);border-top:6px solid var(--green);padding:30px;background:#fff;box-shadow:0 10px 28px #1b433215}h1{margin:0 0 6px;font-size:1.8rem}p{color:var(--soft);margin:0 0 24px}.field{margin-bottom:15px}label{display:block;font-weight:700;font-size:.85rem;margin-bottom:6px}input{width:100%;padding:11px;border:2px solid var(--line);border-radius:4px;font-size:1rem}button{width:100%;padding:12px;border:0;background:var(--orange);color:#fff;font-weight:700;border-radius:4px;cursor:pointer}.error{background:#fdecea;color:#a93226;padding:10px;margin-bottom:16px;border-radius:4px}.setup{margin-top:15px;font-size:.85rem}.setup a{color:var(--orange);font-weight:700}
</style></head><body><main class="box"><h1>RevSpecs Admin</h1><p>Manage subjects and reviewer PDFs.</p><?php if($error):?><div class="error"><?=h($error)?></div><?php endif;?><form method="post"><div class="field"><label>Username</label><input name="username" required autocomplete="username"></div><div class="field"><label>Password</label><input type="password" name="password" required autocomplete="current-password"></div><button>Log in</button></form><?php if($adminCount===0):?><div class="setup">No admin exists yet. <a href="setup.php">Create the first admin</a>.</div><?php endif;?></main></body></html>
