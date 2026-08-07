const puppeteer = require('puppeteer');
const outDir = 'C:\\Users\\IRWINS~1\\AppData\\Local\\Temp\\claude\\c--laragon-www-Valuasi-Ekonomi\\511b73c8-0aa1-4d2b-ad94-2fc917d2d48a\\scratchpad\\';

(async () => {
    const browser = await puppeteer.launch({ headless: 'new' });
    const page = await browser.newPage();
    await page.setViewport({ width: 1400, height: 1200 });
    await page.goto('http://localhost:8000/', { waitUntil: 'networkidle0', timeout: 30000 });

    await page.evaluate(() => {
        const el = document.querySelector('.province-map-select-box') || document.querySelector('svg');
        if (el) el.scrollIntoView({ block: 'center' });
    });
    await new Promise(r => setTimeout(r, 500));
    await page.screenshot({ path: outDir + '1-before-select.png' });

    const selectHandle = await page.$('.province-map-select');
    if (selectHandle) {
        const val = await page.evaluate(() => {
            const sel = document.querySelector('.province-map-select');
            const opt = sel.options[1];
            return opt ? opt.value : '';
        });
        await selectHandle.select(val);
        await new Promise(r => setTimeout(r, 500));
        await page.screenshot({ path: outDir + '2-after-select.png' });
    } else {
        console.log('select not found');
    }

    await page.setViewport({ width: 400, height: 900 });
    await new Promise(r => setTimeout(r, 500));
    await page.evaluate(() => {
        const el = document.querySelector('.province-map-select-box') || document.querySelector('svg');
        if (el) el.scrollIntoView({ block: 'center' });
    });
    await page.screenshot({ path: outDir + '3-mobile-after-select.png' });

    console.log('done');
    await browser.close();
})().catch(e => { console.error(e); process.exit(1); });
