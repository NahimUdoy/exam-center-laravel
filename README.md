# NextVerseSkill Exam Center

A free practice academy for **IELTS, PTE, MBA, BBA, and English Basics**. Students can browse every exam and task type without an account. Login is required to open questions, submit answers, save scores, and run a speaking interview.

**Live site:** [http://exam-center-w40t.onrender.com/](http://exam-center-w40t.onrender.com/)

This README is a visual map of the product. Skim the screenshots to see every major screen.

## Showcase film

A ~100 second cinematic tour: Ken Burns motion across real product screens, chapter cards, and an original score (no licensed music).

[![NextVerseSkill showcase](docs/showcase/stills/nvs-open-travel.jpg)](docs/showcase/nextverseskill-showcase.mp4)

**Play:** [docs/showcase/nextverseskill-showcase.mp4](docs/showcase/nextverseskill-showcase.mp4) (1920×1080, ~40 MB)

Rebuild from stills and screenshots:

```bash
python3 docs/showcase/compose_score.py
python3 docs/showcase/build_film.py
```

## Visual tour

| Area | What it does |
| --- | --- |
| [Home](#1-home--marketing) | Landing page, course cards, level-check start |
| [Academy](#2-academy--personal-plan) | Level check and personal study plan |
| [Practice](#3-practice-dashboard--two-themes) | Studio / Classic themes, skill decks, locked questions |
| [Study desk](#4-study-desk) | Scoring, 14-day plan, templates, phrases, traps |
| [Speaking](#5-one-to-one-speaking) | Free in-browser examiner interview (Parts 1–3) |
| [Mocks](#6-mock-tests) | IELTS reading paper, PTE full mock, BBA/MBA aptitude |
| [English Basics](#7-english-basics) | Grammar path and topic drills |
| [Certificates](#8-certificates) | Pathway, module, and mock credentials |
| [Account](#9-login--profile) | Login modal and student profile |
| [Admin CMS](#10-admin-cms) | Add exams, question types, questions, mocks, and media |

---

## 1. Home & marketing

The homepage is the public front door: exam tracks, free practice promise, and the 1-minute Academy level check.

![Home](docs/screenshots/01-home.jpg)

The level-check wizard lives on the home page. Guests can walk through the questions; finishing the plan needs a login so the plan can be saved.

![Level check](docs/screenshots/02-level-check.jpg)

---

## 2. Academy & personal plan

Academy explains the study path. After the level check, a logged-in student gets a week-by-week plan with daily minutes and total study time.

![Academy](docs/screenshots/03-academy.jpg)

![Personal Academy plan](docs/screenshots/17-academy-plan.jpg)

---

## 3. Practice dashboard — two themes

Use the **Classic / Studio** switch in the header. The exam bar (PTE, IELTS, BBA, MBA, Basics) stays on every app page.

**Studio** is the warm card layout.

![Studio dashboard](docs/screenshots/04-dashboard-studio.jpg)

**Classic** is the navy academy layout, with stronger section cards.

![Classic dashboard](docs/screenshots/05-dashboard-classic.jpg)

Opening a task type (for example Read Aloud) is public. Guests see the deck and filters, but question titles stay locked until login.

![Practice task types](docs/screenshots/06-practice-task-types.jpg)

After login, the actual question opens: prompt, timer or mic, submit, and score tabs.

![Practice question](docs/screenshots/15-practice-question.jpg)

---

## 4. Study desk

Each exam has a **Study** page (no login). Switch IELTS, PTE, BBA, or MBA in the header.

Students get today’s timed drill, how scoring works, a 14-day plan, stealable templates and phrases, common traps, collocations, and (for BBA/MBA) a one-page formula sheet.

Open `/study?exam=ielts` (or `pte`, `bba`, `mba`).

![Study desk](docs/screenshots/25-study-desk.jpg)

---

## 5. One-to-one speaking

A free in-browser examiner: warm-up questions, one cue card, then discussion. No paid API. Chrome or Edge with a headset works best; typing is the fallback.

Login is required. Each **Start interview** shuffles a new set from 250+ Part 1 questions and 80+ cue cards. **Skip** jumps one question; **Restart** (or **New session** after the recap) starts over.

![One-to-one speaking](docs/screenshots/26-speaking.jpg)

---

## 6. Mock tests

The mock library lists full and section mocks per exam. BBA and MBA papers use real quant, verbal, logic, and critical-reasoning items.

![Mock test list](docs/screenshots/08-mock-tests.jpg)

IELTS mocks start as a paper-style reading test.

![IELTS mock start](docs/screenshots/09-mock-ielts.jpg)

PTE mocks start with a device check, then a timed speaking / writing / reading / listening flow.

![PTE mock start](docs/screenshots/10-mock-pte.jpg)

---

## 7. English Basics

A beginner path: short explanation, example sentences, then eight questions per topic.

![English Basics index](docs/screenshots/11-english-basics.jpg)

![English Basics topic](docs/screenshots/12-english-topic.jpg)

---

## 8. Certificates

Pathway, module, and mock credentials for each exam. Earned, pending, and locked states are all visible.

![Certificate gallery](docs/screenshots/13-certificates.jpg)

![Certificate detail](docs/screenshots/14-certificate.jpg)

---

## 9. Login & profile

Guests can browse types. Opening a question, starting a speaking interview, or submitting an answer opens this modal.

![Login modal](docs/screenshots/07-login-modal.jpg)

Signed-in students manage name, email, and password on the profile page.

![Profile](docs/screenshots/16-profile.jpg)

Demo student (local / seed): `student@nextverseskill.test` / `password`

---

## 10. Admin CMS

Add new exams (TOEFL, GRE, or your own), homepage course cards, question types, practice questions, and mocks from here. Do it in order: **exam → task type → questions**.

The overview screen has an **Add prep content** strip with those steps. Pick a skill on a new task (speaking, writing, reading, listening) so the player knows how students should answer.

![Admin login](docs/screenshots/18-admin-login.jpg)

![Admin overview](docs/screenshots/19-admin-overview.jpg)

![Site & Academy editor](docs/screenshots/20-admin-site.jpg)

![Media library](docs/screenshots/21-admin-media.jpg)

![Exams collection](docs/screenshots/22-admin-exams.jpg)

![Practice questions](docs/screenshots/23-admin-questions.jpg)

![Mock tests](docs/screenshots/24-admin-mocks.jpg)

Admin URL: `/admin/login`. Password comes from `ADMIN_PASSWORD` in `.env` (see `.env.example`). Default in seed/docs: `nextverse`.

---

## How access works

| Action | Guest | Logged in |
| --- | --- | --- |
| Home, Academy, dashboard, Study desk, mock list, certificates | Yes | Yes |
| Browse question **types** (Read Aloud, Essay, …) | Yes | Yes |
| See question titles and open a question | No — login modal | Yes |
| One-to-one speaking interview | No — login | Yes |
| Submit an answer / save a score | No | Yes |
| Finish Academy plan | No | Yes |
| Add exams, tasks, questions in Admin | No | Admin password |

---

## Local setup

```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate --seed
npm install && npm run build
php artisan serve
```

Then open `http://127.0.0.1:8000`.

Switch themes with the header control, or add `?theme=studio` / `?theme=classic` to any URL.

Useful paths:

| Path | What |
| --- | --- |
| `/study?exam=ielts` | Study desk |
| `/speaking/one-to-one?exam=ielts` | One-to-one speaking |
| `/dashboard?exam=pte` | Practice hub |
| `/admin/login` | CMS |

## Refresh these screenshots

From a running app (default `http://127.0.0.1:8010`):

```bash
npm install playwright
NVS_URL=http://127.0.0.1:8000 node docs/screenshots/capture.mjs
```

The script writes JPEGs into `docs/screenshots/`.
