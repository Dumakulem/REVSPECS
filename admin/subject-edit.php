<?php
require_once __DIR__ . '/_common.php'; require_admin();
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: null;
$subject = ['id'=>null,'code'=>'','name'=>'','year'=>1,'pdf_path'=>null,'quiz_path'=>null];
if ($id) { $st=db()->prepare('SELECT * FROM subjects WHERE id=?'); $st->execute([$id]); $subject=$st->fetch() ?: exit('Subject not found.'); }
$error='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    check_csrf();
    $id = filter_input(INPUT_POST,'id',FILTER_VALIDATE_INT) ?: null;
    $code=trim($_POST['code']??''); $name=trim($_POST['name']??''); $year=(int)($_POST['year']??0);
    if (!preg_match('/^[A-Za-z0-9][A-Za-z0-9 .&()\/_-]{1,49}$/',$code)) $error='Invalid subject code.';
    elseif ($name==='') $error='Subject name is required.';
    elseif (!in_array($year,[1,2,3],true)) $error='Year must be 1, 2, or 3.';
    else {
        try {
            if ($id) { $st=db()->prepare('UPDATE subjects SET code=?, name=?, year=? WHERE id=?'); $st->execute([$code,$name,$year,$id]); }
            else { $st=db()->prepare('INSERT INTO subjects (code,name,year) VALUES (?,?,?)'); $st->execute([$code,$name,$year]); $id=(int)db()->lastInsertId(); }
            header('Location: subject-edit.php?id='.$id.'&saved=1'); exit;
        } catch (PDOException $e) { $error = $e->getCode()==='23000' ? 'That subject code already exists.' : 'Could not save the subject.'; }
    }
    $subject=compact('id','code','name','year')+['pdf_path'=>null,'quiz_path'=>null];
    if ($id) {
        $st=db()->prepare('SELECT pdf_path, quiz_path FROM subjects WHERE id=?'); $st->execute([$id]); $row=$st->fetch();
        $subject['pdf_path']=$row['pdf_path'] ?? null;
        $subject['quiz_path']=$row['quiz_path'] ?? null;
    }
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?=($subject['id']?'Edit':'Add')?> Subject — RevSpecs</title><style>:root{--ink:#1B4332;--soft:#5C8374;--line:#CDE8D5;--orange:#E67E22;--green:#27AE60;--red:#C0392B}*{box-sizing:border-box}body{margin:0;background:#f8fbf9;color:var(--ink);font-family:Arial,sans-serif}.wrap{max-width:700px;margin:auto;padding:30px 20px 60px}a{color:var(--orange);text-decoration:none;font-weight:700}header{border-bottom:2px solid var(--ink);padding-bottom:15px;margin-bottom:22px}h1{margin:0}.card{background:#fff;border:2px solid var(--line);padding:24px;border-radius:5px}.field{margin-bottom:17px}label{display:block;font-weight:700;font-size:.85rem;margin-bottom:7px}input,select{width:100%;padding:11px;border:2px solid var(--line);border-radius:4px;font-size:1rem;background:#fff;color:var(--ink)}.help{font-size:.78rem;color:var(--soft);margin-top:5px}.row{display:flex;gap:10px;justify-content:flex-end}.btn{padding:10px 15px;border:1px solid var(--line);border-radius:4px;background:#fff;color:var(--ink);font-weight:700;text-decoration:none}.primary{background:var(--orange);border-color:var(--orange);color:#fff}.primary.quiz{background:var(--green);border-color:var(--green)}.error{background:#fdecea;color:var(--red);padding:10px;margin-bottom:15px}.pdfbox{background:#f3fbf6;padding:12px;margin-bottom:18px;border-left:4px solid var(--green)}
</style></head><body><div class="wrap"><header><a href="index.php">← Back to dashboard</a><h1><?=($subject['id']?'Edit':'Add')?> subject</h1></header><div class="card"><?php if($error):?><div class="error"><?=h($error)?></div><?php endif;?><?php if(isset($_GET['saved'])):?><div class="pdfbox">Subject saved.</div><?php endif;?><form method="post"><input type="hidden" name="csrf" value="<?=h(csrf_token())?>"><input type="hidden" name="id" value="<?=h((string)$subject['id'])?>"><div class="field"><label>Subject code</label><input name="code" value="<?=h($subject['code'])?>" placeholder="CS 111" required><div class="help">Example: CS 111, MATH 111</div></div><div class="field"><label>Subject name</label><input name="name" value="<?=h($subject['name'])?>" placeholder="Introduction to Computing" required></div><div class="field"><label>Year level</label><select name="year"><option value="1" <?=$subject['year']==1?'selected':''?>>1st Year</option><option value="2" <?=$subject['year']==2?'selected':''?>>2nd Year</option><option value="3" <?=$subject['year']==3?'selected':''?>>3rd Year</option></select></div><div class="row"><a class="btn" href="index.php">Cancel</a><button class="btn primary" type="submit">Save subject</button></div></form><?php if($subject['id']):?><hr style="border:0;border-top:1px solid var(--line);margin:25px 0"><h2 style="font-size:1.1rem">Reviewer PDF</h2><?php if($subject['pdf_path']):?><div class="pdfbox"><b>Uploaded:</b> <?=h(basename($subject['pdf_path']))?><br><a href="../<?=h($subject['pdf_path'])?>" target="_blank">Open PDF</a></div><?php else:?><div class="pdfbox">No reviewer PDF uploaded yet.</div><?php endif;?><a class="btn primary" href="upload-pdf.php?id=<?=$subject['id']?>">Upload / replace PDF</a><hr style="border:0;border-top:1px solid var(--line);margin:25px 0"><h2 style="font-size:1.1rem">Quiz (JSON)</h2><?php if($subject['quiz_path']):?><div class="pdfbox"><b>Uploaded:</b> <?=h(basename($subject['quiz_path']))?><br><a href="../<?=h($subject['quiz_path'])?>" target="_blank">View quiz JSON</a></div><?php else:?><div class="pdfbox">No quiz uploaded yet. Generate one with <b>quizzes/PROMPT_TEMPLATE.md</b>, then upload it here.</div><?php endif;?><a class="btn primary quiz" href="upload-quiz.php?id=<?=$subject['id']?>">Upload / replace quiz</a><?php endif;?></div></div></body></html>
