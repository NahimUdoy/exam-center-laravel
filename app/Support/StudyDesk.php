<?php

namespace App\Support;

use App\Models\Exam;

class StudyDesk
{
    /**
     * @return array{
     *     headline: string,
     *     promise: string,
     *     today: array{title: string, minutes: int, steps: list<string>},
     *     score: list<string>,
     *     plan: list<string>,
     *     templates: list<array{title: string, body: string}>,
     *     phrases: list<string>,
     *     traps: list<string>,
     *     vocab: list<array{word: string, use: string}>,
     *     formulas: list<array{name: string, rule: string}>,
     *     drills: list<array{label: string, href: string, hint: string}>
     * }
     */
    public static function forExam(Exam $exam): array
    {
        $slug = $exam->slug;

        return [
            'headline' => match ($slug) {
                'pte' => 'PTE study desk — score the high-weight tasks first',
                'ielts' => 'IELTS study desk — band language you can actually use',
                'bba' => 'BBA study desk — speed, accuracy, and calm timing',
                'mba' => 'MBA study desk — reason cleanly, then compute',
                default => 'Study desk — a daily path for this exam',
            },
            'promise' => match ($slug) {
                'pte' => 'Repeat Sentence, Write From Dictation, and Read Aloud move Speaking and Writing together. Drill those daily.',
                'ielts' => 'Examiners reward a clear position, a reason, and one example. Fluency beats rare words you cannot control.',
                'bba' => 'Most entrance papers are speed tests. Learn 8 formulas, skip a sinkhole question, come back later.',
                'mba' => 'Critical reasoning is a pattern: conclusion, evidence, gap. Quant is the same 12 ideas in new clothes.',
                default => 'Short daily drills beat a 4-hour panic session the night before.',
            },
            'today' => self::today($exam),
            'score' => self::score($slug),
            'plan' => self::plan($slug),
            'templates' => self::templates($slug),
            'phrases' => self::phrases($slug),
            'traps' => self::traps($slug),
            'vocab' => self::vocab($slug),
            'formulas' => self::formulas($slug),
            'drills' => self::drills($exam),
        ];
    }

    /**
     * @return list<string>
     */
    public static function phrases(?string $examSlug = 'ielts'): array
    {
        return match ($examSlug) {
            'pte' => [
                'The chart compares… and the highest figure is…',
                'Overall, there is an upward / downward trend in…',
                'The lecture is mainly about… The speaker then explains…',
                'In conclusion, the data suggests that…',
                'One clear example is…, which shows that…',
            ],
            'bba', 'mba' => [
                'The argument concludes that… because…',
                'This option is too extreme — the passage only says…',
                'If the percentage is of the original, then…',
                'Eliminate two options first, then compare the remaining pair.',
                'I am not solving this now — mark, move, return.',
            ],
            default => [
                'I would say… because… For example…',
                'It depends. In my hometown… whereas in a big city…',
                'The main reason is… Another point is…',
                'To be honest, I have mixed feelings. On the one hand…',
                'That reminds me of a time when…',
                'If I had to choose, I would prefer… since…',
            ],
        };
    }

    /**
     * @return array{title: string, minutes: int, steps: list<string>}
     */
    private static function today(Exam $exam): array
    {
        $day = now()->dayOfYear;
        $sets = match ($exam->slug) {
            'pte' => [
                ['Repeat Sentence × 15, then Write From Dictation × 10', 25, ['Use a headset.', 'Shadow the audio once.', 'Type WFD without pausing the second time.']],
                ['Read Aloud × 8 with a timer', 20, ['Mark stress on long words.', 'Finish the last sentence — do not speed-run.']],
                ['One Describe Image + one SWT', 25, ['Speak the overview first.', 'SWT: one sentence, subject + what happened + result.']],
            ],
            'ielts' => [
                ['Speaking: 5 Part 1 answers + 1 cue card', 20, ['Record yourself.', 'Restart any answer under 20 seconds.']],
                ['Reading: one True / False / Not Given block', 20, ['Underline the qualifier: all, only, always.', 'NG means the passage is silent.']],
                ['Writing Task 2 outline only, then 15 minutes of body 1', 25, ['Thesis in 1 sentence.', 'Each body: idea → reason → example.']],
            ],
            'bba' => [
                ['Quant: 15 mixed arithmetic questions, 20 minutes', 20, ['No calculator if your paper bans it.', 'Skip any item after 90 seconds.']],
                ['Verbal: 1 short RC + 8 grammar items', 20, ['Read the question before the passage.', 'Watch subject–verb and prepositions.']],
                ['Logic: series + syllogism drill', 20, ['Write the pattern in 4 characters.', 'Never assume extra facts.']],
            ],
            'mba' => [
                ['CR: 8 assumption / weaken items', 25, ['Circle the conclusion.', 'The assumption is the missing bridge.']],
                ['Quant: 10 problem-solving, 20 minutes', 20, ['Estimate before you compute.', 'Plug in numbers if algebra stalls.']],
                ['One case-write: issue, option, risk', 25, ['One recommendation only.', 'Name the implementation risk.']],
            ],
            default => [
                ['8 grammar drills + 5 minutes of speaking', 20, ['Say the example sentence aloud.', 'Then do the questions without notes.']],
            ],
        };

        $pick = $sets[$day % count($sets)];

        return [
            'title' => $pick[0],
            'minutes' => $pick[1],
            'steps' => $pick[2],
        ];
    }

    /**
     * @return list<string>
     */
    private static function score(string $slug): array
    {
        return match ($slug) {
            'pte' => [
                'Speaking and Writing share a large combined score — RA, RS, and WFD feed both.',
                'Reading still needs RW Fill in the Blanks and Re-order Paragraphs; do not ignore them.',
                'Partial credit exists. A slightly imperfect RA is better than silence.',
                'WFD spelling and word order both count. Capital letters do not.',
            ],
            'ielts' => [
                'Speaking: Fluency, Vocabulary, Grammar, Pronunciation — 25% each.',
                'Writing Task 2 is worth more than Task 1. Do not spend 40 minutes on the chart.',
                'Band 7 needs a clear position and paragraphs that stay on that position.',
                'Reading and Listening are raw marks converted to a band. Every question is 1 mark.',
            ],
            'bba' => [
                'Most papers are +1 / 0 or mild negative marking. Read the instruction page first.',
                'Quant and Logical Reasoning are usually the rank-makers.',
                'GK is a bonus, not a rescue. Do not dump 2 weeks only on current affairs.',
                'A blank is better than a wild guess if negative marking is on.',
            ],
            'mba' => [
                'GMAT-style: Verbal + Quant scaled scores. One silly arithmetic error is expensive.',
                'Critical Reasoning and RC reward precision, not extra knowledge.',
                'IR / Data Insights (if your paper has it): read the graph title before the numbers.',
                'Case interviews and written analysis: one clear recommendation beats three vague ones.',
            ],
            default => [
                'Accuracy first, then speed. A clean easy item is worth more than a guessed hard one.',
            ],
        };
    }

    /**
     * @return list<string>
     */
    private static function plan(string $slug): array
    {
        return match ($slug) {
            'pte' => [
                'Days 1–3: RS + WFD every day. Build the ear.',
                'Days 4–6: RA + DI templates. Record and listen back once.',
                'Days 7–9: Reading FIB and Re-order. 20 minutes, timed.',
                'Days 10–11: SST and Listening Fill in the Blanks.',
                'Days 12–13: One full mock. Note the 3 task types you missed.',
                'Day 14: Only those 3 types. Sleep 8 hours.',
            ],
            'ielts' => [
                'Days 1–3: Part 1 speaking daily + 20 new collocations.',
                'Days 4–6: One cue card a day, 2 minutes, no notes after prep.',
                'Days 7–8: Reading TFNG and matching headings.',
                'Days 9–10: Task 2 — two essays, 40 minutes each.',
                'Days 11–12: Listening sections 1–2 for detail, 3–4 for lecture.',
                'Days 13–14: Full mock + speaking interview on this site.',
            ],
            'bba' => [
                'Days 1–4: Percentages, ratio, average, profit–loss. 25 timed items/day.',
                'Days 5–7: Time–work, speed, simple interest.',
                'Days 8–10: Series, coding–decoding, syllogism.',
                'Days 11–12: One RC + error-spotting set daily.',
                'Days 13: Full mock. Mark every item over 90 seconds.',
                'Day 14: Redo only the marked items.',
            ],
            'mba' => [
                'Days 1–3: CR assumption and weaken. 10/day, review why wrong.',
                'Days 4–6: Quant — rates, mixtures, overlapping sets.',
                'Days 7–9: RC — main idea, inference, tone. No outside knowledge.',
                'Days 10–11: One case-write and one data chart.',
                'Days 12–13: Mixed timed set like the real paper.',
                'Day 14: Light review of error log only.',
            ],
            default => [
                'Pick one grammar topic a day from English Basics, then speak 5 answers.',
            ],
        };
    }

    /**
     * @return list<array{title: string, body: string}>
     */
    private static function templates(string $slug): array
    {
        return match ($slug) {
            'pte' => [
                ['title' => 'Describe Image', 'body' => 'The image is a [bar/line/pie/map] about [topic]. The highest is [X] and the lowest is [Y]. Overall, [trend]. One extra detail is [Z].'],
                ['title' => 'Summarize Written Text', 'body' => '[Topic] [verb] [main result], which [effect], according to the passage.'],
                ['title' => 'Essay body', 'body' => 'First, [reason]. For instance, [example]. This means [result]. Second, [reason].'],
            ],
            'ielts' => [
                ['title' => 'Speaking Part 1', 'body' => 'Direct answer + because + one example from your life. Stop after 20–30 seconds.'],
                ['title' => 'Cue card clock', 'body' => '0:00 what it is · 0:30 when/where · 1:00 details · 1:30 why it matters · last 10s wrap.'],
                ['title' => 'Task 2', 'body' => 'Intro (paraphrase + thesis) · Body 1 (idea, reason, example) · Body 2 · Conclusion that repeats the thesis in new words.'],
            ],
            'bba' => [
                ['title' => 'Percentage change', 'body' => 'Change / original × 100. If a price rises 20% then falls 20%, it does not return to the start.'],
                ['title' => 'RC first 20 seconds', 'body' => 'Read the question stem, then scan. Do not read the full passage twice.'],
                ['title' => 'Guess policy', 'body' => 'If two options remain and there is no negative mark, guess. If −0.25, guess only with a reason.'],
            ],
            'mba' => [
                ['title' => 'CR assumption', 'body' => 'Conclusion = C. Evidence = E. The assumption is what must be true for E to support C.'],
                ['title' => 'Weaken', 'body' => 'Find the choice that breaks the bridge between evidence and conclusion — not a sad extra fact.'],
                ['title' => 'Case write', 'body' => 'Issue in one line. Recommendation. Why (2 points). One risk and how to watch it.'],
            ],
            default => [
                ['title' => 'Answer shape', 'body' => 'Say the idea, give a sentence, then try the drill.'],
            ],
        };
    }

    /**
     * @return list<string>
     */
    private static function traps(string $slug): array
    {
        return match ($slug) {
            'pte' => [
                'Speaking too fast on RA so the last clause is missing.',
                'WFD: adding a word that “sounds like” it belongs.',
                'Essay: memorized off-topic paragraphs.',
                'Re-order: putting two related sentences together without the time order.',
            ],
            'ielts' => [
                'True/False/NG: treating “not mentioned” as False.',
                'Writing: no clear opinion on an opinion question.',
                'Speaking: 8-second answers in Part 1, then panic in Part 3.',
                'Listening: writing the first number you hear when the question asks for the final price.',
            ],
            'bba' => [
                'Solving until the end when the question only needs an estimate.',
                'Ratio questions: mixing part:part with part:whole.',
                'Reading “all” when the stem said “some”.',
                'Spending 4 minutes on Q3 and rushing the last 10 easy items.',
            ],
            'mba' => [
                'Using real-world knowledge on RC when the passage disagrees.',
                'Strengthen/weaken: picking an option that is true but off-scope.',
                'Quant: answering for x when they asked for x + y.',
                'Case: listing problems with no decision.',
            ],
            default => [
                'Translating every word instead of using the pattern in the example sentence.',
            ],
        };
    }

    /**
     * @return list<array{word: string, use: string}>
     */
    private static function vocab(string $slug): array
    {
        return match ($slug) {
            'pte', 'ielts' => [
                ['word' => 'on the whole', 'use' => 'On the whole, students prefer a quiet library to a café.'],
                ['word' => 'a case in point', 'use' => 'Night buses are a case in point — they help shift workers.'],
                ['word' => 'to some extent', 'use' => 'Phones help study to some extent, but they also scatter attention.'],
                ['word' => 'in the long run', 'use' => 'In the long run, daily 20-minute speaking beats a weekend binge.'],
                ['word' => 'tend to', 'use' => 'Cities tend to grow around transport lines, not the other way round.'],
                ['word' => 'whereas', 'use' => 'I review notes in the morning, whereas my sister prefers late nights.'],
                ['word' => 'account for', 'use' => 'Young adults account for the highest screen-time group in the chart.'],
                ['word' => 'rather than', 'use' => 'Give a reason rather than a list of rare idioms.'],
            ],
            'bba', 'mba' => [
                ['word' => 'assumption', 'use' => 'An assumption is unstated, but the argument needs it.'],
                ['word' => 'inference', 'use' => 'An inference must follow from the lines given, not from the news.'],
                ['word' => 'net', 'use' => 'Net profit is after costs — do not treat it as revenue.'],
                ['word' => 'compounded', 'use' => 'Interest compounded annually is not the same as simple interest.'],
                ['word' => 'scope', 'use' => 'If an option goes outside the argument’s scope, drop it.'],
                ['word' => 'trade-off', 'use' => 'Every case recommendation has a trade-off; name it.'],
            ],
            default => [
                ['word' => 'for example', 'use' => 'I walk to class. For example, I leave home at seven.'],
            ],
        };
    }

    /**
     * @return list<array{name: string, rule: string}>
     */
    private static function formulas(string $slug): array
    {
        if (! in_array($slug, ['bba', 'mba'], true)) {
            return [];
        }

        return [
            ['name' => 'Percentage', 'rule' => 'Part / whole × 100. Increase: new = original × (1 + r/100).'],
            ['name' => 'Profit / loss', 'rule' => 'P = SP − CP. Profit % = P / CP × 100.'],
            ['name' => 'Average', 'rule' => 'Sum / n. Combined average: total sum / total people.'],
            ['name' => 'Ratio mix', 'rule' => 'If A:B = 2:3 and B:C = 3:4, then A:B:C = 2:3:4.'],
            ['name' => 'Speed', 'rule' => 'Distance = speed × time. Average speed ≠ average of two speeds if times differ.'],
            ['name' => 'Work', 'rule' => 'If A does a job in a days, rate = 1/a per day.'],
            ['name' => 'Simple interest', 'rule' => 'SI = P r t / 100. Amount = P + SI.'],
            ['name' => 'Permutation (order matters)', 'rule' => 'nPr = n! / (n−r)!'],
        ];
    }

    /**
     * @return list<array{label: string, href: string, hint: string}>
     */
    private static function drills(Exam $exam): array
    {
        $e = ['exam' => $exam->slug];

        return match ($exam->slug) {
            'pte' => [
                ['label' => 'Read Aloud', 'href' => route('practice.task', ['skill' => 'speaking', 'task' => 'read-aloud'] + $e), 'hint' => 'Stress and finish the last clause'],
                ['label' => 'Repeat Sentence', 'href' => route('practice.task', ['skill' => 'speaking', 'task' => 'repeat-sentence'] + $e), 'hint' => 'Highest daily ROI'],
                ['label' => 'Write From Dictation', 'href' => route('practice.task', ['skill' => 'listening', 'task' => 'wfd'] + $e), 'hint' => 'Spelling + word order'],
                ['label' => 'Full PTE mock', 'href' => route('mock-tests.index', $e), 'hint' => 'Once you have 3 quiet days'],
            ],
            'ielts' => [
                ['label' => 'One-to-one speaking', 'href' => route('speaking.show', $e), 'hint' => 'Parts 1–3 with a follow-up'],
                ['label' => 'Cue cards', 'href' => route('practice.task', ['skill' => 'speaking', 'task' => 'cue-card'] + $e), 'hint' => '2 minutes, 1 minute prep'],
                ['label' => 'Task 2 essay', 'href' => route('practice.task', ['skill' => 'writing', 'task' => 'essay'] + $e), 'hint' => 'Opinion + two bodies'],
                ['label' => 'Reading mock', 'href' => route('mock-tests.index', $e), 'hint' => 'TFNG and matching'],
            ],
            'bba' => [
                ['label' => 'Quant drill mock', 'href' => route('mock-tests.index', $e), 'hint' => 'Timed arithmetic'],
                ['label' => 'Case write', 'href' => route('practice.task', ['skill' => 'writing', 'task' => 'case-write'] + $e), 'hint' => 'One recommendation + risk'],
                ['label' => 'Interview answers', 'href' => route('speaking.show', $e), 'hint' => 'Reason + example'],
                ['label' => 'English Basics', 'href' => route('english-basics.index', ['exam' => 'english-basic']), 'hint' => 'If grammar is the leak'],
            ],
            'mba' => [
                ['label' => 'CR / RC mocks', 'href' => route('mock-tests.index', $e), 'hint' => 'Assumption and inference'],
                ['label' => 'Case write', 'href' => route('practice.task', ['skill' => 'writing', 'task' => 'case-write'] + $e), 'hint' => 'Issue → decision → risk'],
                ['label' => 'Speaking interview', 'href' => route('speaking.show', $e), 'hint' => 'Explain a number calmly'],
                ['label' => 'Study formulas', 'href' => route('study.show', $e).'#formulas', 'hint' => 'Eight rules on this page'],
            ],
            default => [
                ['label' => 'Grammar path', 'href' => route('english-basics.index', ['exam' => 'english-basic']), 'hint' => 'One topic, then 8 questions'],
                ['label' => 'Speak', 'href' => route('speaking.show', $e), 'hint' => 'Short answers with a reason'],
            ],
        };
    }
}
