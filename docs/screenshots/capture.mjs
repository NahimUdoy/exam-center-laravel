import { chromium } from 'playwright';
import { mkdirSync } from 'fs';
import { dirname, resolve } from 'path';
import { fileURLToPath } from 'url';

const __dirname = dirname(fileURLToPath(import.meta.url));
const OUT = resolve(__dirname);
const BASE = process.env.NVS_URL || 'http://127.0.0.1:8010';

mkdirSync(OUT, { recursive: true });

async function shot(page, name, { fullPage = false, maxHeight = 1800 } = {}) {
    await page.waitForTimeout(500);
    const opts = {
        path: `${OUT}/${name}.jpg`,
        type: 'jpeg',
        quality: 76,
        animations: 'disabled',
    };

    if (fullPage) {
        const height = await page.evaluate((cap) => Math.min(document.documentElement.scrollHeight, cap), maxHeight);
        const width = await page.evaluate(() => Math.max(document.documentElement.clientWidth, 1280));
        opts.clip = { x: 0, y: 0, width, height };
    }

    await page.screenshot(opts);
    console.log(`saved ${name}.jpg`);
}

async function go(page, path) {
    await page.goto(`${BASE}${path}`, { waitUntil: 'domcontentloaded', timeout: 45000 });
    await page.waitForTimeout(800);
}

async function main() {
    const browser = await chromium.launch({ channel: 'chrome', headless: true });
    const context = await browser.newContext({
        viewport: { width: 1440, height: 900 },
        deviceScaleFactor: 1,
    });
    const page = await context.newPage();

    await go(page, '/?theme=studio');
    await shot(page, '01-home', { fullPage: true, maxHeight: 2200 });

    await page.locator('#plan').last().scrollIntoViewIfNeeded();
    await page.waitForTimeout(400);
    await shot(page, '02-level-check');

    await go(page, '/academy?theme=studio');
    await shot(page, '03-academy', { fullPage: true });

    await go(page, '/dashboard?exam=pte&theme=studio');
    await shot(page, '04-dashboard-studio', { fullPage: true });

    await go(page, '/dashboard?exam=pte&theme=classic');
    await shot(page, '05-dashboard-classic', { fullPage: true });

    await go(page, '/practice/speaking/read-aloud?exam=pte&theme=studio');
    await shot(page, '06-practice-task-types');

    await page.getByRole('button', { name: 'Log in' }).click();
    await page.waitForTimeout(400);
    await shot(page, '07-login-modal');
    await page.keyboard.press('Escape');

    await go(page, '/mock-tests?exam=pte&theme=studio');
    await shot(page, '08-mock-tests', { fullPage: true });

    await go(page, '/mock-tests/ielts-r-cam08-t4?exam=ielts&theme=studio');
    await shot(page, '09-mock-ielts');

    await go(page, '/mock-tests/pte-full-a?exam=pte&theme=studio');
    await shot(page, '10-mock-pte');

    await go(page, '/english-basics?exam=english-basic&theme=studio');
    await shot(page, '11-english-basics', { fullPage: true });

    await go(page, '/english-basics/parts-of-speech?exam=english-basic&theme=studio');
    await shot(page, '12-english-topic', { fullPage: true });

    await go(page, '/certificates?exam=pte&theme=studio');
    await shot(page, '13-certificates', { fullPage: true });

    await go(page, '/certificates/pte-pathway-proficient?exam=pte&theme=studio');
    await shot(page, '14-certificate', { fullPage: true });

    await go(page, '/login');
    await page.fill('input[name=email]', 'student@nextverseskill.test');
    await page.fill('input[name=password]', 'password');
    await page.click('button[type=submit]');
    await page.waitForTimeout(1200);

    await go(page, '/practice/speaking/read-aloud/1001648?exam=pte&theme=studio');
    await shot(page, '15-practice-question', { fullPage: true });

    await go(page, '/profile');
    await shot(page, '16-profile', { fullPage: true });

    await go(page, '/#plan');
    const start = page.getByRole('button', { name: /Click Here/i });
    if (await start.count()) {
        await start.first().click();
        await page.waitForTimeout(400);
        const cont = page.getByRole('button', { name: /Continue/i });
        if (await cont.count()) {
            await cont.first().click();
            await page.waitForTimeout(400);
        }
        const check = page.getByRole('button', { name: /Quick check/i });
        if (await check.count()) {
            await check.first().click();
            await page.waitForTimeout(400);
        }
        for (let i = 0; i < 8; i++) {
            const option = page.locator('#plan button.w-full').first();
            if (!(await option.count())) {
                break;
            }
            await option.click();
            await page.waitForTimeout(350);
        }
        const open = page.getByRole('button', { name: /Open Academy plan/i });
        if (await open.count()) {
            await open.first().click();
            await page.waitForTimeout(1200);
        }
    }
    await go(page, '/academy/plan?exam=pte&theme=studio');
    await shot(page, '17-academy-plan', { fullPage: true, maxHeight: 2000 });

    const admin = await browser.newContext({ viewport: { width: 1440, height: 900 } });
    const adminPage = await admin.newPage();
    await go(adminPage, '/admin/login');
    await shot(adminPage, '18-admin-login');
    await adminPage.fill('input[name=password]', 'nextverse');
    await adminPage.click('button[type=submit]');
    await adminPage.waitForTimeout(1000);

    await go(adminPage, '/admin');
    await shot(adminPage, '19-admin-overview', { fullPage: true });

    await go(adminPage, '/admin/site');
    await shot(adminPage, '20-admin-site', { fullPage: true });

    await go(adminPage, '/admin/media');
    await shot(adminPage, '21-admin-media', { fullPage: true });

    await go(adminPage, '/admin/exams');
    await shot(adminPage, '22-admin-exams', { fullPage: true });

    await go(adminPage, '/admin/questions');
    await shot(adminPage, '23-admin-questions');

    await go(adminPage, '/admin/mocks');
    await shot(adminPage, '24-admin-mocks');

    await browser.close();
}

main().catch((error) => {
    console.error(error);
    process.exit(1);
});
