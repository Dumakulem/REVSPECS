JSON STRUCTURE FORMAT FOR GENERATIVE AI FOR QUIZ: 
You are building a 50-question active-recall review quiz from the attached PDF, for a computer science student preparing for an exam. Base every question strictly on the content of the attached PDF — do not introduce outside facts, and do not ask about anything the PDF doesn't cover.

Mix these four question types across the 50 questions, in roughly this distribution:

14 "multiple_choice" — one correct answer, three plausible distractors
12 "fill_blank" — a statement with one key term replaced by _____
12 "true_false" — a claim that is either accurate or a plausible-sounding inaccuracy
12 "identification" — a short factual question with a single short answer (a term, name, or number)

Style guidance (this is an active-recall review tool, like a Gizmo-style quiz — not a trivia quiz):

Favor core definitions, key terms, processes, and relationships between concepts over obscure or trivial details.
Keep each question's wording unambiguous — a student who understood the material should get exactly one clearly correct answer.
For "multiple_choice", the three wrong options should be genuinely plausible (common misconceptions or adjacent terms), not obviously wrong.
For "fill_blank" and "identification", keep the expected answer short (a word, short phrase, or number) — not a sentence.
Vary question difficulty: roughly a third easy recall, a third moderate, a third that requires connecting two ideas from the PDF.
Write a one- or two-sentence "explanation" for every question — shown to the student after they answer, reinforcing why the answer is correct.

Respond with ONLY raw JSON — no markdown code fences, no preamble, no commentary before or after — matching this exact shape:

{
  "subject": "<subject code exactly as given>",
  "items": [
    {
      "id": 1,
      "type": "multiple_choice",
      "question": "string",
      "options": ["string", "string", "string", "string"],
      "correctIndex": 0,
      "explanation": "string"
    },
    {
      "id": 2,
      "type": "fill_blank",
      "question": "string containing exactly one _____ placeholder",
      "answer": "string",
      "acceptableAnswers": ["string", "string"],
      "explanation": "string"
    },
    {
      "id": 3,
      "type": "true_false",
      "question": "string",
      "answer": true,
      "explanation": "string"
    },
    {
      "id": 4,
      "type": "identification",
      "question": "string",
      "answer": "string",
      "acceptableAnswers": ["string", "string"],
      "explanation": "string"
    }
  ]
}

Field notes:

id — sequential integer, 1 through 50, no gaps or repeats.
type — must be exactly one of: multiple_choice, fill_blank, true_false, identification.
options — required only for multiple_choice; always exactly 4 items.
correctIndex — required only for multiple_choice; 0-based index into options.
answer — required for fill_blank, true_false (boolean true/false, not a string), and identification.
acceptableAnswers — optional, for fill_blank/identification only; include common synonyms, abbreviations, or alternate phrasings so a student isn't marked wrong for a correct-but-differently-worded answer.
Every item needs explanation.
