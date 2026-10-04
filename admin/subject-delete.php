<?php
require_once __DIR__ . '/_common.php'; require_admin();
if($_SERVER['REQUEST_METHOD']!=='POST') { header('Location:index.php'); exit; }
check_csrf(); $id=filter_input(INPUT_POST,'id',FILTER_VALIDATE_INT); if(!$id) exit('Invalid subject.');
$st=db()->prepare('SELECT pdf_path, quiz_path FROM subjects WHERE id=?');$st->execute([$id]);$row=$st->fetch();if(!$row)exit('Subject not found.');
$del=db()->prepare('DELETE FROM subjects WHERE id=?');$del->execute([$id]);
delete_stored_file($row['pdf_path']);
delete_stored_file($row['quiz_path']);
header('Location:index.php?msg='.rawurlencode('Subject deleted.'));exit;
