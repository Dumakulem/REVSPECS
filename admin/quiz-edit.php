<?php
require_once __DIR__ . '/_common.php';
require_admin();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id) exit('Missing subject.');

$st = db()->prepare('SELECT * FROM subjects WHERE id=?');
$st->execute([$id]);
$subject = $st->fetch();
if (!$subject) exit('Subject not found.');

$quiz = [
    'title' => $subject['name'] . ' Quiz',
    'description' => '',
    'status' => 'draft',
    'items' => [],
];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $status = $_POST['status'] ?? 'draft';
    $questions = $_POST['question'] ?? [];
    $types = $_POST['type'] ?? [];
    $explanations = $_POST['explanation'] ?? [];
    $answers = $_POST['answer'] ?? [];
    $correctIndexes = $_POST['correctIndex'] ?? [];
    $options = $_POST['options'] ?? [];
    $items = [];

    if ($title === '' || strlen($title) > 160) {
        $error = 'Quiz title is required and must be 160 characters or fewer.';
    } elseif (strlen($description) > 1000) {
        $error = 'Quiz description must be 1,000 characters or fewer.';
    } elseif (!in_array($status, ['draft', 'published'], true)) {
        $error = 'Choose either draft or published.';
    } elseif (!is_array($questions) || !$questions) {
        $error = 'Add at least one question.';
    } elseif (count($questions) > 100) {
        $error = 'A quiz may contain at most 100 questions.';
    } else {
        foreach ($questions as $index => $question) {
            $type = $types[$index] ?? '';
            $item = [
                'question' => trim((string)$question),
                'type' => $type,
                'explanation' => trim((string)($explanations[$index] ?? '')),
            ];
            if ($type === 'multiple_choice') {
                $itemOptions = $options[$index] ?? [];
                if (!is_array($itemOptions)) $itemOptions = [];
                $item['options'] = array_values(array_map('trim', $itemOptions));
                $item['correctIndex'] = filter_var($correctIndexes[$index] ?? null, FILTER_VALIDATE_INT);
            } elseif ($type === 'true_false') {
                $item['answer'] = ($answers[$index] ?? '') === 'true';
            } elseif ($type === 'fill_blank' || $type === 'identification') {
                $item['answer'] = trim((string)($answers[$index] ?? ''));
            }
            $items[] = $item;
        }
        $quiz = ['title' => $title, 'description' => $description, 'status' => $status, 'items' => $items];
        $error = validate_quiz_json($quiz) ?? '';
    }

    if (!$error && count($items) > 0) {
        $dir = __DIR__ . '/../uploads/quizzes';
        if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
            $error = 'Could not create the quiz storage directory.';
        } else {
            $safe = preg_replace('/[^A-Za-z0-9_-]+/', '-', $subject['code']);
            $filename = $safe . '_' . bin2hex(random_bytes(8)) . '.json';
            $dest = $dir . '/' . $filename;
            $encoded = json_encode($quiz, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            if ($encoded === false || file_put_contents($dest, $encoded, LOCK_EX) === false) {
                $error = 'Could not save the quiz file.';
                if (is_file($dest)) @unlink($dest);
            } else {
                $relative = 'uploads/quizzes/' . $filename;
                $up = db()->prepare('UPDATE subjects SET quiz_path=?, updated_at=CURRENT_TIMESTAMP WHERE id=?');
                $up->execute([$relative, $id]);
                delete_stored_file($subject['quiz_path']);
                header('Location: subject-edit.php?id=' . $id . '&saved=1');
                exit;
            }
        }
    }
} elseif ($subject['quiz_path']) {
    $path = __DIR__ . '/../' . ltrim($subject['quiz_path'], '/');
    if (is_readable($path)) {
        $stored = json_decode(file_get_contents($path), true);
        if (is_array($stored) && !empty($stored['items'])) {
            $quiz = array_merge($quiz, $stored);
        }
    }
}

$items = $quiz['items'];
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= $subject['quiz_path'] ? 'Edit' : 'Create' ?> Quiz — RevSpecs</title>
<style>
:root{--ink:#1B4332;--soft:#5C8374;--line:#CDE8D5;--orange:#E67E22;--green:#27AE60;--red:#C0392B}
*{box-sizing:border-box}body{margin:0;background:#f8fbf9;color:var(--ink);font-family:Arial,sans-serif}
.wrap{max-width:900px;margin:auto;padding:30px 20px 60px}a{color:var(--orange);text-decoration:none;font-weight:700}
header{border-bottom:2px solid var(--ink);padding-bottom:15px;margin-bottom:22px}h1{margin:7px 0 4px}
.card,.question{background:#fff;border:2px solid var(--line);border-radius:5px;padding:20px;margin-bottom:16px}
.field{margin-bottom:16px}label{display:block;font-weight:700;font-size:.85rem;margin-bottom:7px}
input,textarea,select{width:100%;padding:10px;border:2px solid var(--line);border-radius:4px;font:inherit;color:var(--ink);background:#fff}
textarea{min-height:80px;resize:vertical}.grid{display:grid;grid-template-columns:2fr 1fr;gap:12px}.question-head{display:flex;justify-content:space-between;gap:12px;align-items:center;margin-bottom:12px}.question-head h2{font-size:1rem;margin:0}
.option-row{display:flex;gap:8px;margin:7px 0}.option-row input{flex:1}.remove-option,.remove-question{border:1px solid #efb5ae;background:#fff;color:var(--red);border-radius:4px;font-weight:700;padding:8px 10px}
.actions{display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap}.btn{display:inline-block;padding:10px 15px;border:1px solid var(--line);border-radius:4px;background:#fff;color:var(--ink);font-weight:700;cursor:pointer}.primary{background:var(--orange);border-color:var(--orange);color:#fff}.add{background:#f3fbf6;color:var(--green)}
.error{background:#fdecea;color:var(--red);padding:11px;margin-bottom:16px;white-space:pre-wrap}.muted,.help{color:var(--soft);font-size:.83rem}.empty{padding:18px;background:#f3fbf6;border:1px dashed var(--line);margin-bottom:16px}
@media(max-width:650px){.grid{grid-template-columns:1fr}.question-head{align-items:flex-start;flex-direction:column}}
</style>
</head>
<body><div class="wrap">
<header><a href="subject-edit.php?id=<?=$id?>">← Back to subject</a><h1><?= $subject['quiz_path'] ? 'Edit' : 'Create' ?> quiz</h1><div class="muted"><b><?=h($subject['code'])?></b> — <?=h($subject['name'])?></div></header>
<form method="post" id="quizForm"><input type="hidden" name="csrf" value="<?=h(csrf_token())?>"><input type="hidden" name="id" value="<?=$id?>">
<?php if($error):?><div class="error"><?=h($error)?></div><?php endif;?>
<section class="card"><div class="grid"><div class="field"><label for="title">Quiz title</label><input id="title" name="title" maxlength="160" value="<?=h($quiz['title'])?>" required></div><div class="field"><label for="status">Status</label><select id="status" name="status"><option value="draft" <?=$quiz['status']==='draft'?'selected':''?>>Draft</option><option value="published" <?=$quiz['status']==='published'?'selected':''?>>Published</option></select></div></div><div class="field"><label for="description">Description <span class="muted">(optional)</span></label><textarea id="description" name="description" maxlength="1000"><?=h($quiz['description'])?></textarea></div></section>
<div id="questions"></div><div class="actions"><button class="btn add" type="button" id="addQuestion">+ Add question</button><div><a class="btn" href="subject-edit.php?id=<?=$id?>">Cancel</a> <button class="btn primary" type="submit">Save quiz</button></div></div>
</form></div>
<template id="questionTemplate"><section class="question"><div class="question-head"><h2>Question <span class="question-number"></span></h2><button type="button" class="remove-question">Remove</button></div><div class="field"><label>Question</label><textarea name="question[]" required></textarea></div><div class="grid"><div class="field"><label>Question type</label><select name="type[]" class="type-select"><option value="multiple_choice">Multiple choice</option><option value="true_false">True or false</option><option value="fill_blank">Fill in the blank</option><option value="identification">Identification</option></select></div><div class="field answer-field"></div></div><div class="field"><label>Explanation</label><textarea name="explanation[]" required></textarea></div><div class="options-field"><label>Options</label><div class="options"></div><button type="button" class="btn add add-option">+ Add option</button></div></section></template>
<script>
(function () {
  const questions = document.getElementById('questions');
  const template = document.getElementById('questionTemplate');
  const existing = <?=json_encode(array_values($items), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)?>;
  function refreshNumbers() { questions.querySelectorAll('.question').forEach((card, i) => card.querySelector('.question-number').textContent = i + 1); }
  function optionRow(card, value) {
    const row = document.createElement('div'); row.className = 'option-row';
    row.innerHTML = '<input required><button type="button" class="remove-option">Remove</button>';
    row.querySelector('input').value = value || '';
    row.querySelector('.remove-option').addEventListener('click', () => { row.remove(); });
    card.querySelector('.options').appendChild(row);
  }
  function updateCard(card, item) {
    const type = card.querySelector('.type-select').value;
    const index = Array.from(questions.children).indexOf(card);
    const answer = card.querySelector('.answer-field');
    const options = card.querySelector('.options-field');
    answer.innerHTML = '';
    options.hidden = type !== 'multiple_choice';
    if (type === 'multiple_choice') {
      const label = document.createElement('label'); label.textContent = 'Correct option';
      const select = document.createElement('select'); select.name = 'correctIndex[' + index + ']'; select.required = true;
      (item && item.options || ['', '']).forEach((value, index) => { const opt = document.createElement('option'); opt.value = index; opt.textContent = 'Option ' + (index + 1); select.appendChild(opt); });
      select.value = item && Number.isInteger(item.correctIndex) ? item.correctIndex : 0;
      answer.append(label, select);
    } else if (type === 'true_false') {
      answer.innerHTML = '<label>Correct answer</label><select name="answer[' + index + ']" required><option value="true">True</option><option value="false">False</option></select>';
      answer.querySelector('select').value = item && item.answer === false ? 'false' : 'true';
    } else {
      answer.innerHTML = '<label>Answer</label><input name="answer[' + index + ']" required>';
      answer.querySelector('input').value = item && item.answer || '';
    }
  }
  function addQuestion(item) {
    const card = template.content.firstElementChild.cloneNode(true);
    card.querySelector('.type-select').value = item && item.type || 'multiple_choice';
    card.querySelector('textarea[name="question[]"]').value = item && item.question || '';
    card.querySelector('textarea[name="explanation[]"]').value = item && item.explanation || '';
    card.querySelector('.remove-question').addEventListener('click', () => { card.remove(); refreshNumbers(); });
    card.querySelector('.type-select').addEventListener('change', () => updateCard(card));
    card.querySelector('.add-option').addEventListener('click', () => optionRow(card, ''));
    questions.appendChild(card);
    (item && item.options || ['', '']).forEach(value => optionRow(card, value));
    updateCard(card, item);
    refreshNumbers();
  }
  document.getElementById('addQuestion').addEventListener('click', () => addQuestion());
  document.getElementById('quizForm').addEventListener('submit', () => {
    questions.querySelectorAll('.question').forEach((card, index) => {
      const correctIndex = card.querySelector('select[name^="correctIndex"]');
      if (correctIndex) correctIndex.name = 'correctIndex[' + index + ']';
      card.querySelectorAll('.option-row input').forEach(input => input.name = 'options[' + index + '][]');
      const answer = card.querySelector('[name^="answer["]');
      if (answer) answer.name = 'answer[' + index + ']';
    });
  });
  if (existing.length) existing.forEach(addQuestion); else addQuestion();
})();
</script>
</body></html>
