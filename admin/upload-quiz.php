<?php
require_once __DIR__ . '/_common.php'; require_admin();
$id=filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT) ?: filter_input(INPUT_POST,'id',FILTER_VALIDATE_INT);
if(!$id) exit('Missing subject.');
$st=db()->prepare('SELECT * FROM subjects WHERE id=?');$st->execute([$id]);$subject=$st->fetch();if(!$subject)exit('Subject not found.');
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    check_csrf();
    if(!isset($_FILES['quiz']) || $_FILES['quiz']['error']!==UPLOAD_ERR_OK) $error='Please select a JSON file.';
    else {
        $file=$_FILES['quiz'];
        if($file['size'] > 2*1024*1024) $error='Quiz JSON must be 2 MB or smaller.';
        elseif($file['size'] < 1) $error='The uploaded file is empty.';
        else {
            $raw = file_get_contents($file['tmp_name']);
            $data = json_decode($raw, true);
            $validationError = validate_quiz_json($data);
            if ($validationError) $error = $validationError;
            else {
                $dir=__DIR__.'/../uploads/quizzes'; if(!is_dir($dir))mkdir($dir,0755,true);
                $safe=preg_replace('/[^A-Za-z0-9_-]+/','-', $subject['code']);
                $filename=$safe.'_'.bin2hex(random_bytes(8)).'.json';
                $dest=$dir.'/'.$filename;
                // Re-encode rather than storing the raw upload verbatim, so formatting
                // is consistent and any stray BOM/whitespace from the source is stripped.
                $encoded = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                if(file_put_contents($dest, $encoded) === false) $error='Could not save the quiz file.';
                else {
                    $relative='uploads/quizzes/'.$filename;
                    $old=$subject['quiz_path'];
                    $up=db()->prepare('UPDATE subjects SET quiz_path=? WHERE id=?');$up->execute([$relative,$id]);
                    delete_stored_file($old);
                    header('Location: subject-edit.php?id='.$id.'&saved=1');exit;
                }
            }
        }
    }
}
$itemCount = null;
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Upload Quiz — RevSpecs</title><style>:root{--ink:#1B4332;--soft:#5C8374;--line:#CDE8D5;--orange:#E67E22;--green:#27AE60;--red:#C0392B}*{box-sizing:border-box}body{margin:0;background:#f8fbf9;color:var(--ink);font-family:Arial,sans-serif}.wrap{max-width:650px;margin:auto;padding:30px 20px}.card{background:#fff;border:2px solid var(--line);border-top:6px solid var(--green);padding:25px;border-radius:5px}a{color:var(--orange);font-weight:700;text-decoration:none}h1{margin:8px 0 4px}.muted{color:var(--soft)}label{display:block;font-weight:700;margin:20px 0 7px}input[type=file]{width:100%;padding:12px;border:2px dashed var(--line);background:#f8fbf9;border-radius:5px}.help{color:var(--soft);font-size:.8rem;margin-top:7px}.help a{color:var(--soft);text-decoration:underline}.btn{display:inline-block;padding:11px 15px;border:1px solid var(--line);border-radius:4px;background:#fff;color:var(--ink);font-weight:700;margin-top:18px}.primary{background:var(--green);color:#fff;border-color:var(--green)}.error{background:#fdecea;color:var(--red);padding:11px;margin-top:18px;white-space:pre-wrap}</style></head><body><div class="wrap"><div class="card"><a href="subject-edit.php?id=<?=$id?>">← Back to subject</a><h1>Upload quiz JSON</h1><div class="muted"><b><?=h($subject['code'])?></b> — <?=h($subject['name'])?></div><?php if($error):?><div class="error"><?=h($error)?></div><?php endif;?><form method="post" enctype="multipart/form-data"><input type="hidden" name="csrf" value="<?=h(csrf_token())?>"><input type="hidden" name="id" value="<?=$id?>"><label>Quiz JSON file</label><input type="file" name="quiz" accept="application/json,.json" required><div class="help">Generate this using <b>quizzes/PROMPT_TEMPLATE.md</b> against the reviewer PDF in an AI chat, then upload the JSON it returns here. Maximum 2 MB. Every item is checked against the required schema before it's saved — you'll see exactly which item is wrong if something doesn't match.</div><button class="btn primary" type="submit">Upload quiz</button></form></div></div></body></html>
