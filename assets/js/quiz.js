(function () {
  const TYPE_LABELS = {
    multiple_choice: 'Multiple choice',
    fill_blank: 'Fill in the blank',
    true_false: 'True or false',
    identification: 'Identification'
  };

  let items = [];
  let currentIndex = 0;
  let score = 0;
  let missed = [];
  let subjectLabel = '';

  function normalize(s) {
    return (s || '').toString().trim().toLowerCase().replace(/\s+/g, ' ');
  }

  function isTextCorrect(item, userText) {
    const candidates = [item.answer, ...(item.acceptableAnswers || [])].map(normalize);
    return candidates.includes(normalize(userText));
  }

  function getCorrectDisplay(item) {
    if (item.type === 'true_false') return item.answer ? 'True' : 'False';
    if (item.type === 'multiple_choice') return item.options[item.correctIndex];
    return item.answer;
  }

  function formatUserAnswer(item, userValue) {
    if (item.type === 'true_false') return userValue ? 'True' : 'False';
    if (item.type === 'multiple_choice') return item.options[userValue];
    return userValue;
  }

  function updateProgress() {
    document.getElementById('progressText').textContent = `Question ${currentIndex + 1} of ${items.length}`;
    document.getElementById('progressFill').style.width = `${(currentIndex / items.length) * 100}%`;
  }

  function makeCheckButton(onClick) {
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'btn-next check-btn';
    btn.textContent = 'Check answer';
    btn.addEventListener('click', onClick);
    return btn;
  }

  function recordAnswer(item, userAnswerDisplay, correct) {
    if (!correct) {
      missed.push({
        question: item.question,
        userAnswerDisplay: userAnswerDisplay || '(blank)',
        correctAnswerDisplay: getCorrectDisplay(item),
        explanation: item.explanation
      });
    }
  }

  function showFeedback(item, correct, revealAnswer) {
    if (correct) score++;
    const card = document.querySelector('.question-card');
    const box = document.createElement('div');
    box.className = 'feedback-box ' + (correct ? 'feedback-correct' : 'feedback-incorrect');

    const verdict = document.createElement('p');
    verdict.className = 'feedback-verdict';
    verdict.textContent = correct
      ? '✓ Correct!'
      : (revealAnswer ? `✗ Not quite — correct answer: ${revealAnswer}` : '✗ Not quite.');
    box.appendChild(verdict);

    const expl = document.createElement('p');
    expl.className = 'feedback-explanation';
    expl.textContent = item.explanation;
    box.appendChild(expl);

    const nextBtn = document.createElement('button');
    nextBtn.type = 'button';
    nextBtn.className = 'btn-next';
    nextBtn.textContent = (currentIndex === items.length - 1) ? 'See results' : 'Next question →';
    nextBtn.addEventListener('click', goNext);
    box.appendChild(nextBtn);

    card.appendChild(box);
    nextBtn.focus();
  }

  function submitChoice(item, userValue, clickedBtn, answerArea) {
    const buttons = Array.from(answerArea.querySelectorAll('.option-btn'));
    buttons.forEach(b => b.disabled = true);

    let correct;
    if (item.type === 'true_false') {
      correct = (userValue === item.answer);
      buttons.forEach(b => {
        if ((b.textContent === 'True') === item.answer) b.classList.add('correct');
      });
    } else {
      correct = (userValue === item.correctIndex);
      buttons.forEach((b, idx) => {
        if (idx === item.correctIndex) b.classList.add('correct');
      });
    }
    if (!correct) clickedBtn.classList.add('incorrect');

    recordAnswer(item, formatUserAnswer(item, userValue), correct);
    showFeedback(item, correct);
  }

  function submitText(item, rawValue, card) {
    const input = card.querySelector('.blank-input');
    const checkBtn = card.querySelector('.check-btn');
    if (input) input.disabled = true;
    if (checkBtn) checkBtn.disabled = true;

    const correct = isTextCorrect(item, rawValue);
    if (input) input.classList.add(correct ? 'input-correct' : 'input-incorrect');

    recordAnswer(item, (rawValue || '').trim(), correct);
    showFeedback(item, correct, correct ? null : item.answer);
  }

  function renderQuestion() {
    const item = items[currentIndex];
    updateProgress();
    const body = document.getElementById('quizBody');
    body.innerHTML = '';

    const card = document.createElement('div');
    card.className = 'question-card';

    const badge = document.createElement('span');
    badge.className = 'type-badge';
    badge.textContent = TYPE_LABELS[item.type] || item.type;
    card.appendChild(badge);

    const qEl = document.createElement('p');
    qEl.className = 'question-text';

    const answerArea = document.createElement('div');
    answerArea.className = 'answer-area';

    if (item.type === 'fill_blank' && item.question.includes('_____')) {
      const parts = item.question.split('_____');
      qEl.appendChild(document.createTextNode(parts[0]));
      const input = document.createElement('input');
      input.type = 'text';
      input.className = 'blank-input';
      input.autocomplete = 'off';
      input.spellcheck = false;
      qEl.appendChild(input);
      qEl.appendChild(document.createTextNode(parts.slice(1).join('_____')));
      card.appendChild(qEl);

      const checkBtn = makeCheckButton(() => submitText(item, input.value, card));
      input.addEventListener('keydown', e => {
        if (e.key === 'Enter') { e.preventDefault(); checkBtn.click(); }
      });
      answerArea.appendChild(checkBtn);
      card.appendChild(answerArea);
      setTimeout(() => input.focus(), 0);

    } else if (item.type === 'identification' || item.type === 'fill_blank') {
      qEl.textContent = item.question;
      card.appendChild(qEl);
      const input = document.createElement('input');
      input.type = 'text';
      input.className = 'blank-input';
      input.placeholder = 'Type your answer';
      input.autocomplete = 'off';
      input.spellcheck = false;
      answerArea.appendChild(input);
      const checkBtn = makeCheckButton(() => submitText(item, input.value, card));
      input.addEventListener('keydown', e => {
        if (e.key === 'Enter') { e.preventDefault(); checkBtn.click(); }
      });
      answerArea.appendChild(checkBtn);
      card.appendChild(answerArea);
      setTimeout(() => input.focus(), 0);

    } else if (item.type === 'true_false') {
      qEl.textContent = item.question;
      card.appendChild(qEl);
      ['True', 'False'].forEach(label => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'option-btn';
        btn.textContent = label;
        btn.addEventListener('click', () => submitChoice(item, label === 'True', btn, answerArea));
        answerArea.appendChild(btn);
      });
      card.appendChild(answerArea);

    } else {
      qEl.textContent = item.question;
      card.appendChild(qEl);
      (item.options || []).forEach((opt, idx) => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'option-btn';
        btn.textContent = opt;
        btn.addEventListener('click', () => submitChoice(item, idx, btn, answerArea));
        answerArea.appendChild(btn);
      });
      card.appendChild(answerArea);
    }

    body.appendChild(card);
  }

  function goNext() {
    currentIndex++;
    if (currentIndex >= items.length) showResults();
    else renderQuestion();
  }

  function resultsRemark(pct) {
    if (pct === 100) return 'Perfect score!';
    if (pct >= 80) return 'Great work — almost there.';
    if (pct >= 50) return 'Decent start — review the missed ones below.';
    return 'Worth another pass through the reviewer first.';
  }

  function showResults() {
    document.getElementById('progressFill').style.width = '100%';
    document.getElementById('progressText').textContent = 'Complete';

    const body = document.getElementById('quizBody');
    body.innerHTML = '';

    const pct = Math.round((score / items.length) * 100);
    const card = document.createElement('div');
    card.className = 'question-card';

    const scoreEl = document.createElement('p');
    scoreEl.className = 'results-score';
    scoreEl.innerHTML = `${score} / ${items.length} <span class="results-pct">(${pct}%)</span>`;
    card.appendChild(scoreEl);

    const labelEl = document.createElement('p');
    labelEl.className = 'results-label';
    labelEl.textContent = resultsRemark(pct);
    card.appendChild(labelEl);

    const details = document.createElement('details');
    details.className = 'review-details';

    const summary = document.createElement('summary');
    summary.className = 'review-summary';

    const summaryText = document.createElement('span');
    summaryText.className = 'review-summary-text';
    summary.appendChild(summaryText);

    const chevron = document.createElement('span');
    chevron.className = 'review-chevron';
    chevron.setAttribute('aria-hidden', 'true');
    chevron.textContent = '▼';
    summary.appendChild(chevron);

    details.appendChild(summary);

    const content = document.createElement('div');
    content.className = 'review-content';

    if (missed.length) {
      summaryText.textContent = `Show missed answers (${missed.length})`;
      details.addEventListener('toggle', () => {
        summaryText.textContent = details.open
          ? `Hide missed answers (${missed.length})`
          : `Show missed answers (${missed.length})`;
      });

      missed.forEach(m => {
        const row = document.createElement('div');
        row.className = 'review-row';

        const q = document.createElement('p');
        q.className = 'review-q';
        q.textContent = m.question;
        row.appendChild(q);

        const yourLine = document.createElement('p');
        yourLine.className = 'review-line';
        yourLine.appendChild(document.createTextNode('Your answer: '));
        const yourSpan = document.createElement('span');
        yourSpan.className = 'review-wrong';
        yourSpan.textContent = m.userAnswerDisplay;
        yourLine.appendChild(yourSpan);
        row.appendChild(yourLine);

        const correctLine = document.createElement('p');
        correctLine.className = 'review-line';
        correctLine.appendChild(document.createTextNode('Correct answer: '));
        const correctSpan = document.createElement('span');
        correctSpan.className = 'review-right';
        correctSpan.textContent = m.correctAnswerDisplay;
        correctLine.appendChild(correctSpan);
        row.appendChild(correctLine);

        const explEl = document.createElement('p');
        explEl.className = 'review-explanation';
        explEl.textContent = m.explanation;
        row.appendChild(explEl);

        content.appendChild(row);
      });

    } else {
      summaryText.textContent = 'Review answers';
      const perfect = document.createElement('p');
      perfect.className = 'review-perfect';
      perfect.textContent = '✓ Perfect score — nothing to review.';
      content.appendChild(perfect);
    }

    details.appendChild(content);
    card.appendChild(details);

    const retakeBtn = document.createElement('button');
    retakeBtn.type = 'button';
    retakeBtn.className = 'btn-next';
    retakeBtn.textContent = 'Retake quiz';
    retakeBtn.addEventListener('click', () => {
      currentIndex = 0; score = 0; missed = [];
      renderQuestion();
    });
    card.appendChild(retakeBtn);

    body.appendChild(card);
  }

  function showError(msg) {
    document.getElementById('quizTitle').textContent = subjectLabel || 'Quiz unavailable';
    document.getElementById('progressText').textContent = '';
    document.getElementById('progressFill').style.width = '0%';
    const body = document.getElementById('quizBody');
    body.innerHTML = '';
    const card = document.createElement('div');
    card.className = 'question-card';
    const p = document.createElement('p');
    p.className = 'question-text';
    p.style.marginBottom = '0';
    p.textContent = msg;
    card.appendChild(p);
    body.appendChild(card);
  }

  async function init() {
    const params = new URLSearchParams(window.location.search);
    const subjectCode = params.get('subject');
    subjectLabel = params.get('name') || subjectCode || 'Quiz';
    document.title = `RevSpecs — ${subjectLabel} Quiz`;

    if (!subjectCode) {
      showError('No subject was specified — go back and pick one from RevSpecs.');
      return;
    }

    try {
      const res = await fetch('generate_quiz.php' + window.location.search);
      const data = await res.json().catch(() => null);

      if (!res.ok || !data) {
        showError((data && data.error) || 'Could not load this quiz.');
        return;
      }
      if (!Array.isArray(data.items) || !data.items.length) {
        showError('This quiz has no questions yet.');
        return;
      }

      items = data.items;
      document.getElementById('quizTitle').textContent = `${subjectLabel} — AI Quiz`;
      renderQuestion();
    } catch (err) {
      showError('Could not reach the server. Please try again.');
    }
  }

  init();
})();