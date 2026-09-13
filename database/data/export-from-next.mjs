/**
 * Exports Next.js lib datasets to JSON for Laravel seeders.
 * Source of truth: ExamCenter src/lib/*.ts (data hardcoded from those files).
 */
import { readFileSync, writeFileSync, mkdirSync } from "node:fs";
import { dirname, join } from "node:path";
import { fileURLToPath } from "node:url";

const __dirname = dirname(fileURLToPath(import.meta.url));
const OUT_DIR = __dirname;
const NEXT_ROOT = join(__dirname, "../../../ExamCenter");

const SOURCE_FILES = [
  "src/lib/data.ts",
  "src/lib/mocks.ts",
  "src/lib/certificates.ts",
  "src/lib/plans.ts",
  "src/lib/pte/questions.ts",
  "src/lib/mock-results.ts",
];

for (const rel of SOURCE_FILES) {
  readFileSync(join(NEXT_ROOT, rel), "utf8");
}

function writeJson(name, data) {
  const path = join(OUT_DIR, name);
  writeFileSync(path, `${JSON.stringify(data, null, 2)}\n`, "utf8");
  return data.length;
}

function mockRunner(examId, section) {
  if (examId === "pte") return "pte";
  if (section === "reading" && examId === "ielts") return "ielts-reading";
  return "generic";
}

const exams = [
  {
    slug: "pte",
    name: "PTE Academic",
    short_name: "PTE",
    tagline: "AI-scored speaking & writing drills",
    accent: "#0d9488",
    accent_soft: "#ccfbf1",
    description:
      "Pearson Test of English with prediction banks and mock suites.",
    question_count: 21400,
    free_practice: true,
  },
  {
    slug: "ielts",
    name: "IELTS",
    short_name: "IELTS",
    tagline: "Band-focused practice for Academic & General",
    accent: "#2563eb",
    accent_soft: "#dbeafe",
    description:
      "Listening, Reading, Writing, Speaking with examiner-style feedback.",
    question_count: 8600,
    free_practice: true,
  },
  {
    slug: "bba",
    name: "BBA Entrance",
    short_name: "BBA",
    tagline: "Quant, verbal & logical readiness",
    accent: "#ea580c",
    accent_soft: "#ffedd5",
    description:
      "Business school entrance modules with timed sectional mocks.",
    question_count: 4200,
    free_practice: true,
  },
  {
    slug: "mba",
    name: "MBA / GMAT Track",
    short_name: "MBA",
    tagline: "Case, quant & critical reasoning",
    accent: "#7c3aed",
    accent_soft: "#ede9fe",
    description:
      "MBA prep pathways with adaptive difficulty and score targets.",
    question_count: 5100,
    free_practice: true,
  },
  {
    slug: "english-basic",
    name: "English Basics",
    short_name: "Basics",
    tagline: "Grammar, vocab & fluency foundation",
    accent: "#059669",
    accent_soft: "#d1fae5",
    description:
      "Everyday English drills for beginners stepping into exam prep.",
    question_count: 2800,
    free_practice: true,
  },
];

const taskTypes = [
  { slug: "read-aloud", skill: "speaking", name: "Read Aloud", done: 12, total: 1552, exam_slugs: ["pte"] },
  { slug: "repeat-sentence", skill: "speaking", name: "Repeat Sentence", done: 8, total: 2104, exam_slugs: ["pte"] },
  { slug: "describe-image", skill: "speaking", name: "Describe Image", done: 3, total: 890, exam_slugs: ["pte"] },
  { slug: "retell-lecture", skill: "speaking", name: "Re-tell Lecture", done: 1, total: 640, exam_slugs: ["pte"] },
  { slug: "answer-short", skill: "speaking", name: "Answer Short Question", done: 5, total: 1200, exam_slugs: ["pte"] },
  { slug: "cue-card", skill: "speaking", name: "Cue Card / Part 2", done: 4, total: 420, exam_slugs: ["ielts"] },
  { slug: "interview", skill: "speaking", name: "Interview Practice", done: 2, total: 180, exam_slugs: ["ielts", "english-basic"] },
  { slug: "swt", skill: "writing", name: "Summarize Written Text", done: 6, total: 520, exam_slugs: ["pte"] },
  { slug: "essay", skill: "writing", name: "Write Essay", done: 2, total: 380, exam_slugs: ["pte", "ielts"] },
  { slug: "task1", skill: "writing", name: "Task 1 Report", done: 1, total: 260, exam_slugs: ["ielts"] },
  { slug: "case-write", skill: "writing", name: "Case Analysis", done: 0, total: 140, exam_slugs: ["bba", "mba"] },
  { slug: "rw-fib", skill: "reading", name: "R&W Fill in the Blanks", done: 18, total: 980, exam_slugs: ["pte"] },
  { slug: "r-fib", skill: "reading", name: "R Fill in the Blanks", done: 9, total: 760, exam_slugs: ["pte"] },
  { slug: "reorder", skill: "reading", name: "Re-order Paragraphs", done: 4, total: 540, exam_slugs: ["pte"] },
  { slug: "rc-mcq", skill: "reading", name: "Reading Comprehension", done: 11, total: 890, exam_slugs: ["ielts", "bba", "mba", "english-basic"] },
  { slug: "sst", skill: "listening", name: "Summarize Spoken Text", done: 3, total: 410, exam_slugs: ["pte"] },
  { slug: "wfd", skill: "listening", name: "Write From Dictation", done: 15, total: 1200, exam_slugs: ["pte"] },
  { slug: "l-mcq", skill: "listening", name: "Listening MCQ", done: 7, total: 680, exam_slugs: ["pte", "ielts"] },
  { slug: "hiw", skill: "listening", name: "Highlight Incorrect Words", done: 2, total: 390, exam_slugs: ["pte"] },
  { slug: "full-mock", skill: "mock", name: "Full Mock Test", done: 0, total: 24, exam_slugs: ["pte", "ielts", "bba", "mba"] },
  { slug: "sectional-mock", skill: "mock", name: "Sectional Mock", done: 1, total: 48, exam_slugs: ["pte", "ielts", "bba", "mba"] },
];

const mockTestsRaw = [
  { id: "ielts-r-cam08-t4", examId: "ielts", section: "reading", title: "Cambridge 08 — Test 4", subtitle: "Academic Reading · 3 passages", durationMin: 60, questions: 40, attempts: 2, difficulty: "Standard", featured: true },
  { id: "ielts-r-cam12-t1", examId: "ielts", section: "reading", title: "Cambridge 12 — Test 1", subtitle: "Academic Reading", durationMin: 60, questions: 40, attempts: 0, difficulty: "Standard" },
  { id: "ielts-r-rising-sum", examId: "ielts", section: "reading", title: "Land of the Rising Sum", subtitle: "Sectional · Passage matching", durationMin: 20, questions: 13, attempts: 1, difficulty: "Advanced", featured: true },
  { id: "ielts-l-cam10-t2", examId: "ielts", section: "listening", title: "Cambridge 10 — Test 2", subtitle: "4 sections · audio included", durationMin: 30, questions: 40, attempts: 3, difficulty: "Standard" },
  { id: "ielts-l-cam15-t3", examId: "ielts", section: "listening", title: "Cambridge 15 — Test 3", subtitle: "Map + MCQ mix", durationMin: 30, questions: 40, attempts: 0, difficulty: "Advanced" },
  { id: "ielts-w-task12-a", examId: "ielts", section: "writing", title: "Academic Writing Set A", subtitle: "Task 1 chart + Task 2 essay", durationMin: 60, questions: 2, attempts: 1, difficulty: "Standard" },
  { id: "ielts-w-task12-b", examId: "ielts", section: "writing", title: "Academic Writing Set B", subtitle: "Process diagram + opinion essay", durationMin: 60, questions: 2, attempts: 0, difficulty: "Advanced" },
  { id: "ielts-s-cue-pack1", examId: "ielts", section: "speaking", title: "Speaking Pack — Cue Cards 01", subtitle: "Part 1–3 · examiner prompts", durationMin: 15, questions: 12, attempts: 2, difficulty: "Foundation" },
  { id: "ielts-s-cue-pack2", examId: "ielts", section: "speaking", title: "Speaking Pack — Cue Cards 02", subtitle: "Travel & cities theme", durationMin: 15, questions: 12, attempts: 0, difficulty: "Standard" },
  { id: "ielts-full-01", examId: "ielts", section: "full", title: "IELTS Full Mock — Session 01", subtitle: "L · R · W · S timed suite", durationMin: 165, questions: 94, attempts: 0, difficulty: "Advanced", featured: true },
  { id: "pte-full-a", examId: "pte", section: "full", title: "PTE Full Mock A", subtitle: "All 20 task types · timed exam flow", durationMin: 120, questions: 53, attempts: 1, difficulty: "Standard", featured: true },
  { id: "pte-full-b", examId: "pte", section: "full", title: "PTE Full Mock B", subtitle: "Full Academic sequence · AI-ready", durationMin: 120, questions: 53, attempts: 0, difficulty: "Advanced" },
  { id: "pte-s-ra-rs", examId: "pte", section: "speaking", title: "Speaking Sectional — Full Module", subtitle: "RA · RS · DI · RL · ASQ", durationMin: 25, questions: 23, attempts: 4, difficulty: "Standard" },
  { id: "pte-w-swt-we", examId: "pte", section: "writing", title: "Writing Sectional — SWT & Essay", subtitle: "Summarize + Write Essay", durationMin: 40, questions: 2, attempts: 1, difficulty: "Standard" },
  { id: "pte-r-rwfib", examId: "pte", section: "reading", title: "Reading Sectional — Full Module", subtitle: "RWFIB · MCQ · Reorder · FIB", durationMin: 30, questions: 13, attempts: 2, difficulty: "Standard" },
  { id: "pte-l-wfd", examId: "pte", section: "listening", title: "Listening Sectional — Full Module", subtitle: "SST · FIB · HIW · WFD & more", durationMin: 25, questions: 15, attempts: 3, difficulty: "Advanced" },
  { id: "bba-full-01", examId: "bba", section: "full", title: "BBA Entrance Full Mock 01", subtitle: "Quant · Verbal · Logic", durationMin: 90, questions: 100, attempts: 1, difficulty: "Standard", featured: true },
  { id: "bba-full-02", examId: "bba", section: "full", title: "BBA Entrance Full Mock 02", subtitle: "University pattern set", durationMin: 90, questions: 100, attempts: 0, difficulty: "Advanced" },
  { id: "bba-r-verbal", examId: "bba", section: "reading", title: "Verbal Ability Drill", subtitle: "RC + grammar + vocab", durationMin: 35, questions: 40, attempts: 2, difficulty: "Foundation" },
  { id: "bba-w-essay", examId: "bba", section: "writing", title: "Business Essay Sprint", subtitle: "Timed opinion & case write-up", durationMin: 45, questions: 2, attempts: 0, difficulty: "Standard" },
  { id: "bba-l-comp", examId: "bba", section: "listening", title: "Comprehension Audio Set", subtitle: "Short talks + MCQs", durationMin: 25, questions: 20, attempts: 1, difficulty: "Foundation" },
  { id: "bba-s-interview", examId: "bba", section: "speaking", title: "Admission Interview Mock", subtitle: "Personal + motivation prompts", durationMin: 20, questions: 10, attempts: 0, difficulty: "Standard" },
  { id: "mba-full-01", examId: "mba", section: "full", title: "MBA Aptitude Full Mock 01", subtitle: "Quant · CR · RC", durationMin: 120, questions: 80, attempts: 0, difficulty: "Advanced", featured: true },
  { id: "mba-r-cr", examId: "mba", section: "reading", title: "Critical Reasoning Pack", subtitle: "Assumptions & weaken/strengthen", durationMin: 40, questions: 30, attempts: 1, difficulty: "Advanced" },
  { id: "mba-w-case", examId: "mba", section: "writing", title: "Case Analysis Writing", subtitle: "Structured recommendation memo", durationMin: 50, questions: 1, attempts: 0, difficulty: "Advanced" },
  { id: "mba-s-gdpi", examId: "mba", section: "speaking", title: "GD / PI Simulation", subtitle: "Group discussion + interview", durationMin: 30, questions: 8, attempts: 2, difficulty: "Standard" },
  { id: "mba-l-brief", examId: "mba", section: "listening", title: "Case Brief Listening", subtitle: "Audio case + note questions", durationMin: 25, questions: 15, attempts: 0, difficulty: "Standard" },
  { id: "eng-r-a1", examId: "english-basic", section: "reading", title: "Basics Reading A1–A2", subtitle: "Short passages + MCQ", durationMin: 25, questions: 20, attempts: 3, difficulty: "Foundation", featured: true },
  { id: "eng-l-daily", examId: "english-basic", section: "listening", title: "Daily English Listening", subtitle: "Conversations & vocab", durationMin: 20, questions: 15, attempts: 1, difficulty: "Foundation" },
  { id: "eng-w-para", examId: "english-basic", section: "writing", title: "Paragraph Builder", subtitle: "Guided sentence → paragraph", durationMin: 30, questions: 5, attempts: 0, difficulty: "Foundation" },
  { id: "eng-s-intro", examId: "english-basic", section: "speaking", title: "Self-Intro Fluency", subtitle: "Warm-up speaking prompts", durationMin: 12, questions: 8, attempts: 2, difficulty: "Foundation" },
  { id: "eng-full-placement", examId: "english-basic", section: "full", title: "Placement Full Check", subtitle: "Mixed skills placement mock", durationMin: 45, questions: 40, attempts: 0, difficulty: "Foundation" },
];

const mockTests = mockTestsRaw.map((m) => ({
  slug: m.id,
  exam_slug: m.examId,
  section: m.section,
  title: m.title,
  subtitle: m.subtitle,
  duration_min: m.durationMin,
  questions: m.questions,
  attempts: m.attempts,
  difficulty: m.difficulty,
  featured: m.featured ?? false,
  runner: mockRunner(m.examId, m.section),
}));

const IELTS_READING_MOCK = "ielts-r-rising-sum";

const readingPassages = [
  { mock_slug: IELTS_READING_MOCK, label: "A", sort_order: 0, text: "Japan has long been associated with disciplined saving, meticulous craft, and a quiet confidence in long-term planning. Economists who study household behavior often point to cultural habits formed across generations: families treat surplus income as a buffer against uncertainty, not as fuel for impulsive consumption. That instinct shaped everything from postal savings accounts to the popularity of low-risk government bonds." },
  { mock_slug: IELTS_READING_MOCK, label: "B", sort_order: 1, text: "In the decades after the Second World War, this thrift became an engine of national recovery. Capital that might have leaked into luxury markets instead financed factories, rail networks, and export industries. The phrase “land of the rising sum” was coined by a foreign correspondent who noticed how ordinary deposits, multiplied by millions of households, quietly rebuilt a modern economy." },
  { mock_slug: IELTS_READING_MOCK, label: "C", sort_order: 2, text: "Yet the same habits later created new tensions. When interest rates fell and asset prices stalled, households kept saving while firms hesitated to invest. Policymakers struggled to convert private caution into public growth. Critics argued that a culture celebrated for prudence had become an obstacle to demand, innovation, and risk-taking among younger workers." },
  { mock_slug: IELTS_READING_MOCK, label: "D", sort_order: 3, text: "Today, fintech apps, side hustles, and global investment products are rewriting the story again. Young adults still value security, but they also chase yield through index funds and overseas markets. The old moral of patient accumulation has not disappeared—it has simply learned to travel digitally, seeking opportunity beyond domestic borders." },
];

const readingQuestions = [
  { mock_slug: IELTS_READING_MOCK, number: 1, part: 1, prompt: "Which statement best summarizes paragraph A?", options: ["Japanese households historically preferred speculative investments.", "Saving habits were shaped by a preference for long-term security.", "Government bonds were unpopular among ordinary families.", "Luxury spending drove Japan’s early industrial growth."], correct_index: 1, skill_tag: "Main idea", tip: "Summaries must match the paragraph’s central idea — not a side detail." },
  { mock_slug: IELTS_READING_MOCK, number: 2, part: 1, prompt: "The phrase “land of the rising sum” mainly refers to:", options: ["A tourism campaign for sunrise destinations.", "Military spending after the war.", "Collective household deposits powering recovery.", "A decline in postal savings accounts."], correct_index: 2, skill_tag: "Vocabulary in context", tip: "Go back to the sentence that introduces the phrase and read one line before/after." },
  { mock_slug: IELTS_READING_MOCK, number: 3, part: 1, prompt: "According to paragraph B, thrift contributed to recovery by:", options: ["Redirecting capital into productive national infrastructure.", "Encouraging imports of luxury goods.", "Reducing employment in factories.", "Eliminating the need for export industries."], correct_index: 0, skill_tag: "Detail / evidence", tip: "Look for cause → effect language (instead, financed, rebuilt)." },
  { mock_slug: IELTS_READING_MOCK, number: 4, part: 1, prompt: "What tension is described in paragraph C?", options: ["High interest rates discouraged all forms of saving.", "Continued saving collided with weak investment demand.", "Young workers rejected any form of financial caution.", "Firms invested too aggressively in risky ventures."], correct_index: 1, skill_tag: "Inference", tip: "Tension = two forces pulling opposite ways. Find both sides in the paragraph." },
  { mock_slug: IELTS_READING_MOCK, number: 5, part: 2, prompt: "Critics in paragraph C suggest that prudence:", options: ["Always guarantees rapid innovation.", "May limit demand and risk-taking.", "Was invented by foreign journalists.", "Only affects government policy, not households."], correct_index: 1, skill_tag: "Writer’s view / critics", tip: "Separate the writer’s voice from critics’ claims." },
  { mock_slug: IELTS_READING_MOCK, number: 6, part: 2, prompt: "Paragraph D implies that young adults today:", options: ["Have abandoned security entirely.", "Only invest in domestic postal accounts.", "Blend security goals with global digital investing.", "Reject index funds as too modern."], correct_index: 2, skill_tag: "Implication", tip: "‘Implies’ means the answer is strongly suggested, not always quoted word-for-word." },
  { mock_slug: IELTS_READING_MOCK, number: 7, part: 2, prompt: "Which idea links paragraphs A and D?", options: ["Security remains valued, but methods of pursuing it evolve.", "Saving culture has been completely replaced by luxury spending.", "Foreign correspondents still define economic policy.", "Interest rates are no longer relevant to households."], correct_index: 0, skill_tag: "Paragraph linking", tip: "Find the shared theme across distant paragraphs." },
  { mock_slug: IELTS_READING_MOCK, number: 8, part: 2, prompt: "A suitable title for the whole passage would be:", options: ["Why Japan Banned Overseas Funds", "From Household Thrift to Digital Yield", "The End of Manufacturing in Asia", "Postal Services and Tourism Marketing"], correct_index: 1, skill_tag: "Global understanding", tip: "A good title covers the full arc of the passage, not one paragraph only." },
  { mock_slug: IELTS_READING_MOCK, number: 9, part: 3, prompt: "The writer’s overall tone is best described as:", options: ["Hostile and sarcastic", "Neutral and analytical", "Celebratory without critique", "Confused and contradictory"], correct_index: 1, skill_tag: "Tone", tip: "Tone comes from word choice across the whole text — avoid extremes unless clearly shown." },
  { mock_slug: IELTS_READING_MOCK, number: 10, part: 3, prompt: "Which claim is supported by the passage?", options: ["Household saving never influenced national recovery.", "Low rates removed every reason to save.", "Cultural thrift interacted with changing economic conditions over time.", "Fintech erased all preference for security."], correct_index: 2, skill_tag: "Supported claims", tip: "Eliminate options that use absolute words (never / every / all) unless the text does." },
  { mock_slug: IELTS_READING_MOCK, number: 11, part: 3, prompt: "“Quietly rebuilt a modern economy” most nearly means:", options: ["Recovery happened without dramatic public spectacle.", "Factories were constructed overnight by the state alone.", "Households refused to participate in growth.", "Foreign aid replaced domestic capital entirely."], correct_index: 0, skill_tag: "Paraphrase", tip: "Match meaning, not just individual words." },
  { mock_slug: IELTS_READING_MOCK, number: 12, part: 3, prompt: "What is one change mentioned for younger savers?", options: ["They avoid all digital tools.", "They explore overseas markets through apps and funds.", "They only buy government bonds.", "They no longer value buffers against uncertainty."], correct_index: 1, skill_tag: "Specific detail", tip: "Scan for ‘young’ / ‘today’ / ‘apps’ keywords, then verify the full sentence." },
  { mock_slug: IELTS_READING_MOCK, number: 13, part: 3, prompt: "The passage suggests policymakers found it difficult to:", options: ["Print more luxury advertisements.", "Turn private caution into broader economic growth.", "Close postal savings accounts permanently.", "Stop young adults from using fintech."], correct_index: 1, skill_tag: "Inference", tip: "‘Suggests’ = conclusion drawn from stated facts about policy tension." },
];

const certificates = [
  { slug: "pte-pathway-proficient", exam_slug: "pte", category: "pathway", title: "PTE Academic Pathway Certificate", subtitle: "Awarded for completing the full PTE Academic practice pathway with consistent mock performance", status: "earned", score_label: "Predicted Score", score_value: "72", issued_on: "12 Sep 2026", meta: { recipient: "NextVerseSkill", credential_id: "NV-PTE-PATH-2026-0912", hours_completed: 48, modules: ["Speaking", "Writing", "Reading", "Listening", "Full Mock A"], level: "Proficient" } },
  { slug: "pte-module-speaking", exam_slug: "pte", category: "module", title: "PTE Speaking Mastery", subtitle: "Recognizes fluency-focused completion of Read Aloud, Repeat Sentence, Describe Image, Re-tell Lecture & ASQ", status: "earned", score_label: "Speaking Avg", score_value: "78", issued_on: "28 Aug 2026", meta: { recipient: "NextVerseSkill", credential_id: "NV-PTE-SPK-2026-0828", hours_completed: 16, modules: ["Read Aloud", "Repeat Sentence", "Describe Image", "Re-tell Lecture", "ASQ"], level: "Distinction" } },
  { slug: "pte-module-writing", exam_slug: "pte", category: "module", title: "PTE Writing Excellence", subtitle: "Awarded for Summarize Written Text and Write Essay practice with target word-count discipline", status: "earned", score_label: "Writing Avg", score_value: "71", issued_on: "02 Sep 2026", meta: { recipient: "NextVerseSkill", credential_id: "NV-PTE-WRT-2026-0902", hours_completed: 12, modules: ["Summarize Written Text", "Write Essay"], level: "Proficient" } },
  { slug: "pte-module-reading", exam_slug: "pte", category: "module", title: "PTE Reading Specialist", subtitle: "Confirms mastery drills across RWFIB, MCQ, Re-order Paragraphs and Reading FIB", status: "earned", score_label: "Reading Avg", score_value: "74", issued_on: "08 Sep 2026", meta: { recipient: "NextVerseSkill", credential_id: "NV-PTE-RD-2026-0908", hours_completed: 14, modules: ["RWFIB", "MCQ", "Re-order", "RFIB"], level: "Proficient" } },
  { slug: "pte-module-listening", exam_slug: "pte", category: "module", title: "PTE Listening Specialist", subtitle: "Unlock by completing SST, WFD, Highlight Incorrect Words and Listening FIB sectionals", status: "locked", score_label: "Target", score_value: "75+", issued_on: null, meta: { recipient: "NextVerseSkill", credential_id: "NV-PTE-LST-LOCKED", hours_completed: 9, modules: ["SST", "WFD", "HIW", "LFIB"], level: "Distinction" } },
  { slug: "pte-mock-full-a", exam_slug: "pte", category: "mock", title: "PTE Full Mock A Achievement", subtitle: "Issued after completing Full Mock A under timed exam conditions", status: "earned", score_label: "Mock Score", score_value: "69", issued_on: "10 Sep 2026", meta: { recipient: "NextVerseSkill", credential_id: "NV-PTE-MOCKA-2026-0910", hours_completed: 2, modules: ["Full Mock A", "53 Questions"], level: "Proficient" } },
  { slug: "pte-mock-full-b", exam_slug: "pte", category: "mock", title: "PTE Full Mock B Achievement", subtitle: "Finish PTE Full Mock B with AI scoring enabled to unlock", status: "pending", score_label: "Target", score_value: "75+", issued_on: null, meta: { recipient: "NextVerseSkill", credential_id: "NV-PTE-MOCKB-PENDING", hours_completed: 0, modules: ["Full Mock B"], level: "Distinction" } },
  { slug: "ielts-pathway-band", exam_slug: "ielts", category: "pathway", title: "IELTS Academic Readiness Certificate", subtitle: "Issued for balanced practice across Listening, Reading, Writing and Speaking", status: "earned", score_label: "Predicted Band", score_value: "7.0", issued_on: "05 Sep 2026", meta: { recipient: "NextVerseSkill", credential_id: "NV-IELTS-PATH-2026-0905", hours_completed: 52, modules: ["Listening", "Reading", "Writing", "Speaking"], level: "Proficient" } },
  { slug: "ielts-module-listening", exam_slug: "ielts", category: "module", title: "IELTS Listening Certificate", subtitle: "Recognizes completion of Cambridge-style listening sectionals", status: "earned", score_label: "Listening Band", score_value: "7.5", issued_on: "22 Aug 2026", meta: { recipient: "NextVerseSkill", credential_id: "NV-IELTS-LST-2026-0822", hours_completed: 14, modules: ["Section 1–4", "Map Labeling", "MCQ"], level: "Distinction" } },
  { slug: "ielts-module-reading", exam_slug: "ielts", category: "module", title: "IELTS Reading Specialist", subtitle: "Complete 3 Cambridge reading mocks to unlock this credential", status: "locked", score_label: "Target Band", score_value: "7.5", issued_on: null, meta: { recipient: "NextVerseSkill", credential_id: "NV-IELTS-RD-LOCKED", hours_completed: 8, modules: ["Cambridge Reading", "TFNG", "Matching"], level: "Distinction" } },
  { slug: "ielts-module-writing", exam_slug: "ielts", category: "module", title: "IELTS Writing Certificate", subtitle: "Awarded for Task 1 and Task 2 essay practice with structure feedback", status: "earned", score_label: "Writing Band", score_value: "6.5", issued_on: "30 Aug 2026", meta: { recipient: "NextVerseSkill", credential_id: "NV-IELTS-WRT-2026-0830", hours_completed: 18, modules: ["Task 1", "Task 2", "Essay Sets"], level: "Proficient" } },
  { slug: "ielts-module-speaking", exam_slug: "ielts", category: "module", title: "IELTS Speaking Certificate", subtitle: "Cue card and interview pack completion across Parts 1–3", status: "earned", score_label: "Speaking Band", score_value: "7.0", issued_on: "01 Sep 2026", meta: { recipient: "NextVerseSkill", credential_id: "NV-IELTS-SPK-2026-0901", hours_completed: 11, modules: ["Part 1", "Cue Cards", "Part 3"], level: "Proficient" } },
  { slug: "ielts-mock-full", exam_slug: "ielts", category: "mock", title: "IELTS Full Mock Certificate", subtitle: "Pending submission of IELTS Full Mock Session 01", status: "pending", score_label: "Target Band", score_value: "7.0+", issued_on: null, meta: { recipient: "NextVerseSkill", credential_id: "NV-IELTS-MOCK-PENDING", hours_completed: 0, modules: ["Full Mock Session 01"], level: "Proficient" } },
  { slug: "bba-pathway-entrance", exam_slug: "bba", category: "pathway", title: "BBA Entrance Prep Certificate", subtitle: "Confirms completion of quant, verbal and logic sectional preparation", status: "earned", score_label: "Aptitude Score", score_value: "82%", issued_on: "01 Sep 2026", meta: { recipient: "NextVerseSkill", credential_id: "NV-BBA-PATH-2026-0901", hours_completed: 30, modules: ["Quant", "Verbal", "Logic"], level: "Proficient" } },
  { slug: "bba-module-quant", exam_slug: "bba", category: "module", title: "BBA Quantitative Aptitude", subtitle: "Awarded for timed quantitative drills and accuracy milestones", status: "earned", score_label: "Quant Score", score_value: "85%", issued_on: "18 Aug 2026", meta: { recipient: "NextVerseSkill", credential_id: "NV-BBA-QNT-2026-0818", hours_completed: 12, modules: ["Arithmetic", "Algebra", "Data"], level: "Distinction" } },
  { slug: "bba-module-verbal", exam_slug: "bba", category: "module", title: "BBA Verbal Ability", subtitle: "Reading comprehension, grammar and vocabulary pathway certificate", status: "earned", score_label: "Verbal Score", score_value: "79%", issued_on: "24 Aug 2026", meta: { recipient: "NextVerseSkill", credential_id: "NV-BBA-VRB-2026-0824", hours_completed: 10, modules: ["RC", "Grammar", "Vocab"], level: "Proficient" } },
  { slug: "bba-module-logic", exam_slug: "bba", category: "module", title: "BBA Logical Reasoning", subtitle: "Complete advanced logic sectional set to unlock", status: "locked", score_label: "Target", score_value: "80%+", issued_on: null, meta: { recipient: "NextVerseSkill", credential_id: "NV-BBA-LOG-LOCKED", hours_completed: 6, modules: ["Puzzles", "Syllogism", "Series"], level: "Distinction" } },
  { slug: "bba-mock-full", exam_slug: "bba", category: "mock", title: "BBA Full Mock Certificate", subtitle: "Issued after Full Mock 01 under exam timing", status: "earned", score_label: "Mock Score", score_value: "78%", issued_on: "29 Aug 2026", meta: { recipient: "NextVerseSkill", credential_id: "NV-BBA-MOCK-2026-0829", hours_completed: 1.5, modules: ["Full Mock 01", "100 Questions"], level: "Proficient" } },
  { slug: "bba-mock-full-02", exam_slug: "bba", category: "mock", title: "BBA Full Mock 02 Achievement", subtitle: "Pending completion of University pattern Full Mock 02", status: "pending", score_label: "Target", score_value: "85%+", issued_on: null, meta: { recipient: "NextVerseSkill", credential_id: "NV-BBA-MOCK2-PENDING", hours_completed: 0, modules: ["Full Mock 02"], level: "Distinction" } },
  { slug: "mba-pathway-aptitude", exam_slug: "mba", category: "pathway", title: "MBA Aptitude Pathway Certificate", subtitle: "Pending final full mock review across Quant, CR and RC", status: "pending", score_label: "Percentile", score_value: "—", issued_on: null, meta: { recipient: "NextVerseSkill", credential_id: "NV-MBA-PATH-PENDING", hours_completed: 18, modules: ["Quant", "CR", "RC"], level: "Proficient" } },
  { slug: "mba-module-cr", exam_slug: "mba", category: "module", title: "Critical Reasoning Certificate", subtitle: "Awarded for assumptions, weaken/strengthen and inference drills", status: "earned", score_label: "CR Accuracy", score_value: "76%", issued_on: "15 Aug 2026", meta: { recipient: "NextVerseSkill", credential_id: "NV-MBA-CR-2026-0815", hours_completed: 9, modules: ["Assumptions", "Weaken/Strengthen", "Inference"], level: "Proficient" } },
  { slug: "mba-module-quant", exam_slug: "mba", category: "module", title: "MBA Quantitative Certificate", subtitle: "Complete advanced quant pack to unlock this credential", status: "locked", score_label: "Target", score_value: "80%+", issued_on: null, meta: { recipient: "NextVerseSkill", credential_id: "NV-MBA-QNT-LOCKED", hours_completed: 7, modules: ["Problem Solving", "Data Sufficiency"], level: "Distinction" } },
  { slug: "mba-module-case", exam_slug: "mba", category: "module", title: "MBA Case Analysis Certificate", subtitle: "Finish Case Analysis Writing + GD/PI simulation to unlock", status: "locked", score_label: "Target", score_value: "Pass", issued_on: null, meta: { recipient: "NextVerseSkill", credential_id: "NV-MBA-CASE-LOCKED", hours_completed: 4, modules: ["Case Writing", "GD/PI"], level: "Foundation" } },
  { slug: "mba-module-gdpi", exam_slug: "mba", category: "module", title: "GD / PI Simulation Certificate", subtitle: "Issued for completing group discussion and interview simulations", status: "earned", score_label: "Interview Score", score_value: "B+", issued_on: "25 Aug 2026", meta: { recipient: "NextVerseSkill", credential_id: "NV-MBA-GDPI-2026-0825", hours_completed: 6, modules: ["Group Discussion", "Personal Interview"], level: "Proficient" } },
  { slug: "mba-mock-full", exam_slug: "mba", category: "mock", title: "MBA Full Mock Certificate", subtitle: "Complete MBA Aptitude Full Mock 01 to earn this credential", status: "pending", score_label: "Target Percentile", score_value: "90+", issued_on: null, meta: { recipient: "NextVerseSkill", credential_id: "NV-MBA-MOCK-PENDING", hours_completed: 0, modules: ["Full Mock 01"], level: "Distinction" } },
  { slug: "eng-pathway-basics", exam_slug: "english-basic", category: "pathway", title: "English Basics Completion", subtitle: "Awarded for finishing foundation reading, listening, writing and speaking", status: "earned", score_label: "Level", score_value: "A2+", issued_on: "20 Aug 2026", meta: { recipient: "NextVerseSkill", credential_id: "NV-BASIC-PATH-2026-0820", hours_completed: 22, modules: ["Reading", "Listening", "Writing", "Speaking"], level: "Foundation" } },
  { slug: "eng-module-grammar", exam_slug: "english-basic", category: "module", title: "Grammar Foundations Certificate", subtitle: "Recognizes core grammar pathway completion for beginners", status: "earned", score_label: "Accuracy", score_value: "88%", issued_on: "10 Aug 2026", meta: { recipient: "NextVerseSkill", credential_id: "NV-BASIC-GRM-2026-0810", hours_completed: 8, modules: ["Tenses", "Articles", "Prepositions"], level: "Foundation" } },
  { slug: "eng-module-vocab", exam_slug: "english-basic", category: "module", title: "Vocabulary Builder Certificate", subtitle: "Awarded for daily vocab and usage drills", status: "earned", score_label: "Words Mastered", score_value: "420", issued_on: "14 Aug 2026", meta: { recipient: "NextVerseSkill", credential_id: "NV-BASIC-VOC-2026-0814", hours_completed: 7, modules: ["Word Lists", "Usage", "Synonyms"], level: "Foundation" } },
  { slug: "eng-module-speaking", exam_slug: "english-basic", category: "module", title: "Spoken Fluency Starter", subtitle: "Complete Self-Intro Fluency pack to unlock", status: "locked", score_label: "Target", score_value: "Pass", issued_on: null, meta: { recipient: "NextVerseSkill", credential_id: "NV-BASIC-SPK-LOCKED", hours_completed: 3, modules: ["Self Intro", "Daily Prompts"], level: "Foundation" } },
  { slug: "eng-module-writing", exam_slug: "english-basic", category: "module", title: "Paragraph Builder Certificate", subtitle: "Pending Paragraph Builder course completion", status: "pending", score_label: "Target", score_value: "Pass", issued_on: null, meta: { recipient: "NextVerseSkill", credential_id: "NV-BASIC-WRT-PENDING", hours_completed: 2, modules: ["Sentence → Paragraph"], level: "Foundation" } },
  { slug: "eng-mock-placement", exam_slug: "english-basic", category: "mock", title: "Placement Full Check Certificate", subtitle: "Issued after mixed-skills placement mock", status: "earned", score_label: "Placement", score_value: "A2", issued_on: "19 Aug 2026", meta: { recipient: "NextVerseSkill", credential_id: "NV-BASIC-PLACE-2026-0819", hours_completed: 1, modules: ["Placement Full Check"], level: "Foundation" } },
];

const diagnosticBank = {
  pte: [
    { id: "pte-q1", prompt: "In Read Aloud, what matters most for a strong score?", options: [{ label: "Speaking very fast to finish early", score: 0 }, { label: "Clear pronunciation with natural pacing", score: 2 }, { label: "Changing words to sound smarter", score: 0 }] },
    { id: "pte-q2", prompt: "Write From Dictation mainly checks:", options: [{ label: "Essay structure", score: 0 }, { label: "Listening accuracy + spelling", score: 2 }, { label: "Speaking fluency only", score: 1 }] },
  ],
  ielts: [
    { id: "ielts-q1", prompt: "In IELTS Reading, True/False/Not Given answers should be based on:", options: [{ label: "Your general knowledge", score: 0 }, { label: "Exact evidence in the passage", score: 2 }, { label: "Guessing from the title only", score: 0 }] },
    { id: "ielts-q2", prompt: "For Writing Task 2, a solid approach is:", options: [{ label: "Write as many words as possible with no plan", score: 0 }, { label: "Clear position + supporting ideas + conclusion", score: 2 }, { label: "Copy phrases from the question only", score: 0 }] },
  ],
  bba: [
    { id: "bba-q1", prompt: "If 20% of 250 is:", options: [{ label: "40", score: 0 }, { label: "50", score: 2 }, { label: "60", score: 0 }] },
    { id: "bba-q2", prompt: "Verbal RC questions are best solved by:", options: [{ label: "Reading every option first blindly", score: 1 }, { label: "Understanding the passage, then matching evidence", score: 2 }, { label: "Skipping the passage entirely", score: 0 }] },
  ],
  mba: [
    { id: "mba-q1", prompt: "Critical Reasoning usually asks you to:", options: [{ label: "Memorize facts from a textbook", score: 0 }, { label: "Evaluate assumptions and argument strength", score: 2 }, { label: "Translate the passage into Bangla", score: 0 }] },
    { id: "mba-q2", prompt: "A good case-analysis memo should include:", options: [{ label: "Only emotions and opinions", score: 0 }, { label: "Problem, options, recommendation, risks", score: 2 }, { label: "A long story with no decision", score: 0 }] },
  ],
  "english-basic": [
    { id: "eng-q1", prompt: "Choose the correct sentence:", options: [{ label: "She go to school every day.", score: 0 }, { label: "She goes to school every day.", score: 2 }, { label: "She going to school every day.", score: 0 }] },
    { id: "eng-q2", prompt: "A paragraph usually needs:", options: [{ label: "One clear idea with supporting sentences", score: 2 }, { label: "Many random topics in one block", score: 0 }, { label: "Only questions, no answers", score: 0 }] },
  ],
};

const diagnostics = [];
for (const [exam_slug, questions] of Object.entries(diagnosticBank)) {
  questions.forEach((q, sort_order) => {
    diagnostics.push({
      exam_slug,
      external_id: q.id,
      prompt: q.prompt,
      options: q.options,
      sort_order,
    });
  });
}

const pteAnswerKeys = {
  "mcsa-1": { kind: "single", index: 1 },
  "mcsa-2": { kind: "single", index: 2 },
  "mcma-1": { kind: "multi", indexes: [0, 2, 3] },
  "mcma-2": { kind: "multi", indexes: [0, 2, 4] },
  "lmcma-1": { kind: "multi", indexes: [0, 1, 3] },
  "lmcma-2": { kind: "multi", indexes: [0, 1, 3] },
  "hcs-1": { kind: "single", index: 1 },
  "lmcsa-1": { kind: "single", index: 1 },
  "lmcsa-2": { kind: "single", index: 1 },
  "smw-1": { kind: "single", index: 0 },
  "ro-1": { kind: "order", ids: ["a", "b", "d", "c"] },
  "ro-2": { kind: "order", ids: ["a", "d", "b", "c"] },
  "rwfib-1": { kind: "blanks", values: ["livable", "integrated"] },
  "rwfib-2": { kind: "blanks", values: ["spaced", "cramming"] },
  "rwfib-3": { kind: "blanks", values: ["bleaching", "ocean"] },
  "rwfib-4": { kind: "blanks", values: ["anticipate", "sequence"] },
  "rfib-1": { kind: "blanks", values: ["carefully", "when"] },
  "rfib-2": { kind: "blanks", values: ["moved", "trade"] },
  "rfib-3": { kind: "blanks", values: ["highlight", "recall"] },
  "lfib-1": { kind: "text", value: "sculpture online" },
  "lfib-2": { kind: "text", value: "exercise stress" },
  "asq-1": { kind: "text", value: "a dentist" },
  "asq-2": { kind: "text", value: "three hundred sixty-six" },
  "asq-3": { kind: "text", value: "cheap" },
  "asq-4": { kind: "text", value: "mars" },
  "asq-5": { kind: "text", value: "honey" },
  "hiw-1": { kind: "hiw", words: ["library", "railway"] },
  "hiw-2": { kind: "hiw", words: ["valuable", "ticket"] },
  "wfd-1": { kind: "text", value: "the workshop on academic writing begins after the lunch break" },
  "wfd-2": { kind: "text", value: "please keep your belongings with you at all times" },
  "wfd-3": { kind: "text", value: "climate policy requires cooperation between local and national governments" },
  "wfd-4": { kind: "text", value: "students may access the online portal using their university credentials" },
};

function buildPteQuestions() {
  const raTexts = [
    "Urban planners argue that compact cities can reduce commuting times, lower emissions, and create more vibrant public spaces when housing, work, and leisure are carefully mixed.",
    "Scientific collaboration across borders has accelerated vaccine research, allowing laboratories to share data quickly while maintaining rigorous standards of peer review.",
    "Libraries remain essential community hubs, offering free access to knowledge, digital skills workshops, and quiet places for students who lack study space at home.",
    "Climate models suggest that coastal cities must invest in resilient infrastructure, including elevated roads, restored wetlands, and early-warning systems for extreme weather.",
    "Entrepreneurs often underestimate the time required to validate a product idea, yet disciplined customer interviews can prevent costly mistakes later in development.",
    "Museum curators carefully balance preservation with public engagement, designing exhibitions that protect fragile artifacts while inviting visitors to explore history.",
  ];
  const rsAudio = [
    "The seminar on renewable energy starts at nine tomorrow morning.",
    "Please submit your assignment before the library closes tonight.",
    "Students should revise the chapter on photosynthesis carefully.",
    "The conference will be held in the main auditorium next Friday.",
    "Water scarcity is becoming a serious challenge in many regions.",
    "You can borrow up to four books with a standard membership card.",
    "The researcher published her findings in an international journal.",
    "Traffic congestion usually peaks during the early evening hours.",
  ];
  const raInst =
    "Look at the text below. In 35 seconds, you must read this text aloud as naturally and clearly as possible. You have 35 seconds to read aloud.";
  const rsInst =
    "You will hear a sentence. Please repeat the sentence exactly as you hear it. You will hear the sentence only once.";

  const out = [];
  let sort = 0;

  raTexts.forEach((text, i) => {
    const n = i + 1;
    out.push({
      external_id: `ra-${n}`,
      type: "read-aloud",
      section: "speaking-writing",
      title: `Read Aloud ${n}`,
      instructions: raInst,
      correct_text: null,
      answer_key: null,
      sort_order: sort++,
      payload: { prep_seconds: 35, record_seconds: 35, text },
    });
  });

  rsAudio.forEach((audioText, i) => {
    const n = i + 1;
    out.push({
      external_id: `rs-${n}`,
      type: "repeat-sentence",
      section: "speaking-writing",
      title: `Repeat Sentence ${n}`,
      instructions: rsInst,
      correct_text: null,
      answer_key: null,
      sort_order: sort++,
      payload: { prep_seconds: 3, record_seconds: 15, audio_text: audioText },
    });
  });

  const diItems = [
    { title: "Average daily screen time by age group (hours)", hint: "Bar chart comparing ages 10–17, 18–29, 30–44, and 45+" },
    { title: "How recycled paper is produced", hint: "Collection → Sorting → Pulping → Cleaning → Rolling → Packaging" },
    { title: "Campus map changes 2000–2020", hint: "New library wing, relocated sports field, added tram stop" },
  ];
  diItems.forEach((item, i) => {
    const n = i + 1;
    out.push({
      external_id: `di-${n}`,
      type: "describe-image",
      section: "speaking-writing",
      title: `Describe Image ${n}`,
      instructions:
        n === 3
          ? "Describe the map showing changes to a university campus between 2000 and 2020."
          : n === 2
            ? "Look at the process diagram. In 25 seconds, prepare. Then describe the process clearly in 40 seconds."
            : "Look at the graph below. In 25 seconds, please speak into the microphone and describe in detail what the graph is showing. You will have 40 seconds to give your response.",
      correct_text: null,
      answer_key: null,
      sort_order: sort++,
      payload: { prep_seconds: 25, record_seconds: 40, image_title: item.title, image_hint: item.hint },
    });
  });

  out.push({
    external_id: "rl-1",
    type: "retell-lecture",
    section: "speaking-writing",
    title: "Re-tell Lecture 1",
    instructions:
      "You will hear a lecture. After listening, in 10 seconds, please speak into the microphone and retell what you have just heard from the lecture in your own words. You will have 40 seconds to give your response.",
    correct_text: null,
    answer_key: null,
    sort_order: sort++,
    payload: {
      prep_seconds: 10,
      record_seconds: 40,
      audio_text:
        "Today I want to talk about urban heat islands. Cities trap more heat than surrounding rural areas because concrete and asphalt absorb sunlight, while limited trees reduce cooling through shade and evaporation. Researchers have found that nighttime temperatures in dense districts can stay several degrees higher, which increases energy demand for air conditioning and raises health risks for elderly residents. Simple interventions such as reflective roofs, street trees, and permeable pavements can lower surface temperatures and improve comfort without major redesign of the city.",
    },
  });

  const asqItems = [
    ["What do we call a doctor who specializes in treating teeth?", "A dentist"],
    ["How many days are there in a leap year?", "Three hundred sixty-six"],
    ["What is the opposite of expensive?", "Cheap or inexpensive"],
    ["Which planet is known as the Red Planet?", "Mars"],
    ["What do bees produce?", "Honey"],
  ];
  asqItems.forEach(([prompt, correctText], i) => {
    const n = i + 1;
    const id = `asq-${n}`;
    out.push({
      external_id: id,
      type: "answer-short",
      section: "speaking-writing",
      title: `Answer Short Question ${n}`,
      instructions:
        "You will hear a question. Please give a simple and short answer. Often just one or a few words is enough.",
      correct_text: correctText,
      answer_key: pteAnswerKeys[id] ?? null,
      sort_order: sort++,
      payload: { prep_seconds: 2, record_seconds: 10, audio_text: prompt, prompt },
    });
  });

  out.push({
    external_id: "swt-1",
    type: "swt",
    section: "speaking-writing",
    title: "Summarize Written Text 1",
    instructions:
      "Read the passage below and summarize it using one sentence. Type your response in the box at the bottom of the screen. You have 10 minutes to finish this task. Your response will be judged on the quality of your writing and on how well your response presents the key points in the passage.",
    correct_text: null,
    answer_key: null,
    sort_order: sort++,
    payload: {
      time_limit_seconds: 600,
      word_limit: { min: 5, max: 75 },
      text: "Remote work has shifted from a temporary emergency measure to a lasting feature of many industries. Companies report lower office costs and access to a wider talent pool, while employees value flexibility and reduced commuting. However, managers worry about weaker collaboration, uneven workloads, and the difficulty of mentoring junior staff online. Successful organizations are experimenting with hybrid schedules, clearer documentation habits, and intentional in-person days that protect focus time without abandoning teamwork.",
    },
  });

  out.push({
    external_id: "essay-1",
    type: "essay",
    section: "speaking-writing",
    title: "Write Essay 1",
    instructions:
      "You will have 20 minutes to plan, write and revise an essay about the topic below. Your response will be judged on how well you develop a position, organize your ideas, present supporting details, and control the elements of standard written English. You should write 200-300 words.",
    correct_text: null,
    answer_key: null,
    sort_order: sort++,
    payload: {
      time_limit_seconds: 1200,
      word_limit: { min: 200, max: 300 },
      prompt:
        "Some people believe universities should focus mainly on practical job skills, while others argue that a broad liberal education is more valuable. Discuss both views and give your own opinion.",
    },
  });

  const rwfib = [
    { tokens: ["Cities", "can", "become", "more", "___1___", "when", "public", "transport", "is", "reliable,", "affordable,", "and", "___2___", "with", "cycling", "routes."], blanks: [{ id: "1", choices: ["livable", "literal", "limited", "private"] }, { id: "2", choices: ["integrated", "ignored", "invented", "isolated"] }] },
    { tokens: ["Effective", "revision", "depends", "on", "___1___", "practice", "rather", "than", "last-minute", "___2___", "before", "an", "exam."], blanks: [{ id: "1", choices: ["spaced", "rushed", "random", "silent"] }, { id: "2", choices: ["cramming", "camping", "crafting", "claiming"] }] },
    { tokens: ["Marine", "biologists", "monitor", "coral", "reefs", "to", "detect", "early", "signs", "of", "___1___", "caused", "by", "rising", "___2___", "temperatures."], blanks: [{ id: "1", choices: ["bleaching", "blending", "breathing", "borrowing"] }, { id: "2", choices: ["ocean", "office", "outer", "ancient"] }] },
    { tokens: ["A", "clear", "thesis", "statement", "helps", "readers", "___1___", "the", "writer's", "main", "argument", "and", "follow", "the", "___2___", "of", "supporting", "points."], blanks: [{ id: "1", choices: ["anticipate", "abandon", "accuse", "avoid"] }, { id: "2", choices: ["sequence", "silence", "shortage", "shadow"] }] },
  ];
  rwfib.forEach((item, i) => {
    const id = `rwfib-${i + 1}`;
    out.push({
      external_id: id,
      type: "rw-fib",
      section: "reading",
      title: `R&W Fill in the Blanks ${i + 1}`,
      instructions:
        "Below is a text with blanks. Click on each blank, a list of choices will appear. Select the appropriate answer choice for each blank.",
      correct_text: null,
      answer_key: pteAnswerKeys[id],
      sort_order: sort++,
      payload: { passage_tokens: item.tokens, blanks: item.blanks },
    });
  });

  const mcma = [
    {
      text: "Community gardens improve neighborhood cohesion, provide fresh produce, and create outdoor classrooms for children. They can also reduce food miles and offer mental-health benefits through regular contact with nature. Critics note that gardens require volunteer labor and secure land tenure to remain sustainable.",
      prompt: "Which of the following are mentioned as benefits of community gardens?",
      options: [
        "Stronger social connections among neighbors",
        "Immediate replacement of all supermarkets",
        "Access to fresh food grown locally",
        "Educational opportunities for children",
        "Guaranteed government funding forever",
      ],
    },
    {
      text: "Open-source software thrives because developers worldwide can inspect code, fix bugs quickly, and propose new features. Companies adopt open-source tools to avoid vendor lock-in and to customize systems. Challenges include fragmented documentation and the need for skilled maintainers.",
      prompt: "According to the passage, open-source software can:",
      options: [
        "Allow global collaboration on code improvements",
        "Eliminate the need for any skilled maintainers",
        "Help organizations avoid vendor lock-in",
        "Always provide perfect documentation",
        "Support customization of systems",
      ],
    },
  ];
  mcma.forEach((item, i) => {
    const id = `mcma-${i + 1}`;
    out.push({
      external_id: id,
      type: "mc-multi",
      section: "reading",
      title: `Reading MCQ Multi ${i + 1}`,
      instructions: i === 0 ? "Read the text and answer the question by selecting all the correct responses. More than one response is correct." : "Select all the correct responses. More than one response is correct.",
      correct_text: null,
      answer_key: pteAnswerKeys[id],
      sort_order: sort++,
      payload: { multi: true, text: item.text, prompt: item.prompt, options: item.options },
    });
  });

  const reorder = [
    {
      paragraphs: [
        { id: "a", text: "First, researchers collect baseline measurements of air quality across several neighborhoods." },
        { id: "b", text: "They then install sensors on streetlights to track pollution throughout the day." },
        { id: "c", text: "Finally, the data informs city policies on traffic restrictions and green corridors." },
        { id: "d", text: "After several months, patterns emerge showing peaks near major intersections." },
      ],
    },
    {
      paragraphs: [
        { id: "a", text: "Sleep pressure builds the longer a person stays awake." },
        { id: "b", text: "During deep sleep, the brain clears metabolic waste more efficiently." },
        { id: "c", text: "As a result, consistent sleep schedules support memory and mood." },
        { id: "d", text: "Circadian rhythms also influence when the body expects rest." },
      ],
    },
  ];
  reorder.forEach((item, i) => {
    const id = `ro-${i + 1}`;
    out.push({
      external_id: id,
      type: "reorder",
      section: "reading",
      title: `Re-order Paragraphs ${i + 1}`,
      instructions:
        i === 0
          ? "The text boxes in the left panel have been placed in a random order. Restore the original order by dragging the text boxes from the left panel to the right panel."
          : "Restore the original order of the paragraphs.",
      correct_text: null,
      answer_key: pteAnswerKeys[id],
      sort_order: sort++,
      payload: { paragraphs: item.paragraphs },
    });
  });

  const rfib = [
    { tokens: ["Good", "leaders", "listen", "___", "feedback", "and", "adjust", "plans", "___", "conditions", "change."], wordBank: ["before", "when", "carefully", "never", "loudly"] },
    { tokens: ["The", "museum", "exhibit", "explains", "how", "ancient", "traders", "___", "goods", "across", "long", "___", "routes."], wordBank: ["desert", "moved", "ignored", "trade", "quietly"] },
    { tokens: ["Students", "who", "annotate", "texts", "tend", "to", "___", "key", "ideas", "and", "___", "them", "later."], wordBank: ["recall", "highlight", "delete", "forget", "borrow"] },
  ];
  rfib.forEach((item, i) => {
    const id = `rfib-${i + 1}`;
    out.push({
      external_id: id,
      type: "r-fib",
      section: "reading",
      title: `Reading Fill in the Blanks ${i + 1}`,
      instructions:
        "In the text below some words are missing. Drag words from the box below to the appropriate place in the text. To undo an answer choice, drag the word back to the box below the text.",
      correct_text: null,
      answer_key: pteAnswerKeys[id],
      sort_order: sort++,
      payload: { passage_tokens: item.tokens, word_bank: item.wordBank },
    });
  });

  const mcsa = [
    {
      text: "Although electric buses reduce local emissions, their environmental benefit depends on how the electricity is generated. If the grid relies heavily on coal, gains are smaller than when renewable sources dominate.",
      prompt: "What does the author emphasize?",
      options: [
        "Electric buses always eliminate pollution completely",
        "Grid energy sources affect the impact of electric buses",
        "Coal plants are unrelated to transport policy",
        "Renewable energy cannot power public transit",
      ],
    },
    {
      text: "Peer tutoring programs often help both partners: tutors reinforce their own understanding by explaining concepts, while tutees receive personalized support in a less formal setting than a lecture hall.",
      prompt: "According to the text, peer tutoring is beneficial because:",
      options: [
        "Only tutors improve their grades",
        "Lectures become unnecessary",
        "Both tutors and tutees can gain from the exchange",
        "It replaces all professional teachers",
      ],
    },
  ];
  mcsa.forEach((item, i) => {
    const id = `mcsa-${i + 1}`;
    out.push({
      external_id: id,
      type: "mc-single",
      section: "reading",
      title: `Reading MCQ Single ${i + 1}`,
      instructions:
        i === 0
          ? "Read the text and answer the multiple-choice question by selecting the correct response. Only one response is correct."
          : "Select the correct response. Only one response is correct.",
      correct_text: null,
      answer_key: pteAnswerKeys[id],
      sort_order: sort++,
      payload: { text: item.text, prompt: item.prompt, options: item.options },
    });
  });

  out.push({
    external_id: "sst-1",
    type: "sst",
    section: "listening",
    title: "Summarize Spoken Text 1",
    instructions:
      "You will hear a short lecture. Write a summary for a fellow student who was not present at the lecture. You should write 50-70 words. You have 10 minutes to finish this task. Your response will be judged on the quality of your writing and on how well your response presents the key points presented in the lecture.",
    correct_text: null,
    answer_key: null,
    sort_order: sort++,
    payload: {
      time_limit_seconds: 600,
      word_limit: { min: 50, max: 70 },
      audio_text:
        "In this talk I discuss microplastics in freshwater systems. Tiny plastic fragments enter rivers from clothing fibers, packaging waste, and tire wear. Once in the water, they absorb chemical pollutants and may be eaten by fish and insects, moving up the food chain. Scientists are developing better filters at wastewater plants and urging designers to reduce shedding from synthetic textiles. Public awareness campaigns also encourage people to wash clothes less frequently at lower temperatures.",
    },
  });

  const lmcma = [
    {
      audioText:
        "Our campus wellness center now offers free counseling appointments, a sleep workshop every Thursday, and subsidized gym memberships for first-year students. Parking discounts are not included in this program.",
      prompt: "Which services are offered by the wellness center?",
      options: [
        "Free counseling appointments",
        "Thursday sleep workshops",
        "Free campus parking for everyone",
        "Subsidized gym memberships for first-years",
      ],
    },
    {
      audioText:
        "The field trip will include a guided tour of the wetlands, a water-sampling activity, and a short quiz at the end. Lunch is not provided, so please bring your own food.",
      prompt: "What will participants do on the field trip?",
      options: [
        "Take a guided wetlands tour",
        "Collect water samples",
        "Receive a free catered lunch",
        "Complete a short quiz",
      ],
    },
  ];
  lmcma.forEach((item, i) => {
    const id = `lmcma-${i + 1}`;
    out.push({
      external_id: id,
      type: "l-mc-multi",
      section: "listening",
      title: `Listening MCQ Multi ${i + 1}`,
      instructions:
        i === 0
          ? "Listen to the recording and answer the question by selecting all the correct responses. You will need to select more than one response."
          : "Select all the correct responses.",
      correct_text: null,
      answer_key: pteAnswerKeys[id],
      sort_order: sort++,
      payload: { multi: true, audio_text: item.audioText, prompt: item.prompt, options: item.options },
    });
  });

  const lfib = [
    {
      audioText:
        "The museum will open a new gallery dedicated to modern sculpture next month, and tickets can be booked online from Monday.",
      passageTokens: ["The", "museum", "will", "open", "a", "new", "gallery", "dedicated", "to", "modern", "___", "next", "month,", "and", "tickets", "can", "be", "booked", "___", "from", "Monday."],
      correctText: "sculpture online",
    },
    {
      audioText:
        "Researchers found that brief daily exercise can improve concentration and reduce workplace stress among office employees.",
      passageTokens: ["Researchers", "found", "that", "brief", "daily", "___", "can", "improve", "concentration", "and", "reduce", "workplace", "___", "among", "office", "employees."],
      correctText: "exercise stress",
    },
  ];
  lfib.forEach((item, i) => {
    const id = `lfib-${i + 1}`;
    out.push({
      external_id: id,
      type: "l-fib",
      section: "listening",
      title: `Listening Fill in the Blanks ${i + 1}`,
      instructions: i === 0 ? "You will hear a recording. Type the missing words in each blank." : "Type the missing words in each blank.",
      correct_text: item.correctText,
      answer_key: pteAnswerKeys[id],
      sort_order: sort++,
      payload: { audio_text: item.audioText, passage_tokens: item.passageTokens },
    });
  });

  out.push({
    external_id: "hcs-1",
    type: "highlight-summary",
    section: "listening",
    title: "Highlight Correct Summary 1",
    instructions: "You will hear a recording. Click on the paragraph that best relates to the recording.",
    correct_text: null,
    answer_key: pteAnswerKeys["hcs-1"],
    sort_order: sort++,
    payload: {
      audio_text:
        "Many small businesses struggle not because of bad products, but because of weak cash-flow planning. Owners often focus on sales while forgetting delayed payments from customers. Simple forecasting tools and clearer invoice terms can prevent sudden shortfalls.",
      options: [
        "Small businesses fail mainly due to unpopular products and weak branding campaigns.",
        "Cash-flow planning and invoice discipline matter as much as generating sales.",
        "Forecasting tools are unnecessary if a company has strong customer demand.",
        "Delayed payments never affect companies with growing revenue.",
      ],
    },
  });

  const lmcsa = [
    {
      audioText: "The library will extend weekend hours during exam week, staying open until midnight on Saturdays and Sundays.",
      prompt: "What change is announced?",
      options: [
        "The library will close on weekends",
        "Weekend hours will be extended during exam week",
        "Only Sundays will have longer hours",
        "Midnight openings apply all year",
      ],
    },
    {
      audioText: "Passengers on flight 218 should proceed to gate 14 for boarding, which begins in twenty minutes.",
      prompt: "Where should passengers go?",
      options: ["Gate 12", "Gate 14", "Baggage claim", "The ticket counter"],
    },
  ];
  lmcsa.forEach((item, i) => {
    const id = `lmcsa-${i + 1}`;
    out.push({
      external_id: id,
      type: "l-mc-single",
      section: "listening",
      title: `Listening MCQ Single ${i + 1}`,
      instructions:
        i === 0
          ? "Listen to the recording and answer the multiple-choice question by selecting the correct response. Only one response is correct."
          : "Select the correct response. Only one response is correct.",
      correct_text: null,
      answer_key: pteAnswerKeys[id],
      sort_order: sort++,
      payload: { audio_text: item.audioText, prompt: item.prompt, options: item.options },
    });
  });

  out.push({
    external_id: "smw-1",
    type: "missing-word",
    section: "listening",
    title: "Select Missing Word 1",
    instructions:
      "You will hear a recording about biodiversity. At the end of the recording the last word or group of words has been replaced by a beep. Select the correct option to complete the recording.",
    correct_text: null,
    answer_key: pteAnswerKeys["smw-1"],
    sort_order: sort++,
    payload: {
      audio_text:
        "Protecting wetlands is essential because these ecosystems filter water, store carbon, and provide habitats for countless",
      options: ["species", "skyscrapers", "schedules", "softwares"],
    },
  });

  const hiw = [
    {
      audioText: "The laboratory will analyze soil samples collected near the river this afternoon.",
      transcriptWithErrors: [
        { word: "The" },
        { word: "library", incorrect: true },
        { word: "will" },
        { word: "analyze" },
        { word: "soil" },
        { word: "samples" },
        { word: "collected" },
        { word: "near" },
        { word: "the" },
        { word: "railway", incorrect: true },
        { word: "this" },
        { word: "afternoon." },
      ],
    },
    {
      audioText: "Candidates must bring a valid passport and the confirmation email to the test center.",
      transcriptWithErrors: [
        { word: "Candidates" },
        { word: "must" },
        { word: "bring" },
        { word: "a" },
        { word: "valuable", incorrect: true },
        { word: "passport" },
        { word: "and" },
        { word: "the" },
        { word: "confirmation" },
        { word: "ticket", incorrect: true },
        { word: "to" },
        { word: "the" },
        { word: "test" },
        { word: "center." },
      ],
    },
  ];
  hiw.forEach((item, i) => {
    const id = `hiw-${i + 1}`;
    out.push({
      external_id: id,
      type: "highlight-incorrect",
      section: "listening",
      title: `Highlight Incorrect Words ${i + 1}`,
      instructions:
        i === 0
          ? "You will hear a recording. Below is a transcript of the recording. Some words in the transcription differ from what the speaker said. Please click on the words that are different."
          : "Click on the words that are different from what the speaker said.",
      correct_text: null,
      answer_key: pteAnswerKeys[id],
      sort_order: sort++,
      payload: { audio_text: item.audioText, transcript_with_errors: item.transcriptWithErrors },
    });
  });

  const wfdTexts = [
    "The workshop on academic writing begins after the lunch break.",
    "Please keep your belongings with you at all times.",
    "Climate policy requires cooperation between local and national governments.",
    "Students may access the online portal using their university credentials.",
  ];
  wfdTexts.forEach((audioText, i) => {
    const id = `wfd-${i + 1}`;
    out.push({
      external_id: id,
      type: "wfd",
      section: "listening",
      title: `Write From Dictation ${i + 1}`,
      instructions:
        "You will hear a sentence. Type the sentence in the box below exactly as you hear it. Write as much of the sentence as you can. You will hear the sentence only once.",
      correct_text: audioText,
      answer_key: pteAnswerKeys[id],
      sort_order: sort++,
      payload: { audio_text: audioText },
    });
  });

  return out;
}

const pteQuestions = buildPteQuestions();

mkdirSync(OUT_DIR, { recursive: true });

const counts = {
  "exams.json": writeJson("exams.json", exams),
  "task_types.json": writeJson("task_types.json", taskTypes),
  "mock_tests.json": writeJson("mock_tests.json", mockTests),
  "reading_questions.json": writeJson("reading_questions.json", readingQuestions),
  "reading_passages.json": writeJson("reading_passages.json", readingPassages),
  "certificates.json": writeJson("certificates.json", certificates),
  "diagnostics.json": writeJson("diagnostics.json", diagnostics),
  "pte_questions.json": writeJson("pte_questions.json", pteQuestions),
};

console.log("Export complete:");
for (const [file, count] of Object.entries(counts)) {
  console.log(`  ${file}: ${count} records`);
}
