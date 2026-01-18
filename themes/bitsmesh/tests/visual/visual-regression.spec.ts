import { test, expect } from '@playwright/test';

test.describe('Visual Regression', () => {
  test.beforeEach(async ({ page }) => {
    // Disable animations for visual stability
    await page.addInitScript(() => {
      const style = document.createElement('style');
      style.textContent = `
        *, *::before, *::after {
          animation-duration: 0s !important;
          transition-duration: 0s !important;
        }
      `;
      document.head.appendChild(style);
    });
  });

  test('homepage - light mode', async ({ page }) => {
    await page.goto('/', { waitUntil: 'domcontentloaded' });
    await page.evaluate(() => localStorage.setItem('bits-theme-mode', 'light'));
    await page.reload({ waitUntil: 'domcontentloaded' });
    await page.waitForLoadState('networkidle');

    await expect(page).toHaveScreenshot('homepage-light.png', {
      fullPage: true,
      maxDiffPixels: 100,
    });
  });

  test('homepage - dark mode', async ({ page }) => {
    await page.goto('/', { waitUntil: 'domcontentloaded' });
    await page.evaluate(() => localStorage.setItem('bits-theme-mode', 'dark'));
    await page.reload({ waitUntil: 'domcontentloaded' });
    await page.waitForLoadState('networkidle');

    await expect(page).toHaveScreenshot('homepage-dark.png', {
      fullPage: true,
      maxDiffPixels: 100,
    });
  });

  test('header component', async ({ page }) => {
    await page.goto('/', { waitUntil: 'domcontentloaded' });
    await page.waitForLoadState('networkidle');

    const header = page.locator('header.bits-header');
    await header.waitFor({ state: 'visible' });
    await expect(header).toHaveScreenshot('header.png', { maxDiffPixels: 50 });
  });

  test('right panel component', async ({ page }) => {
    await page.goto('/', { waitUntil: 'domcontentloaded' });
    await page.waitForLoadState('networkidle');

    const rightPanel = page.locator('#bits-right-panel');
    await rightPanel.waitFor({ state: 'visible' });
    await expect(rightPanel).toHaveScreenshot('right-panel.png', { maxDiffPixels: 50 });
  });
});

test.describe('Visual Regression - Mobile', () => {
  test.use({ viewport: { width: 375, height: 667 } });

  test.beforeEach(async ({ page }) => {
    // Disable animations for visual stability
    await page.addInitScript(() => {
      const style = document.createElement('style');
      style.textContent = `
        *, *::before, *::after {
          animation-duration: 0s !important;
          transition-duration: 0s !important;
        }
      `;
      document.head.appendChild(style);
    });
  });

  test('homepage mobile - light mode', async ({ page }) => {
    await page.goto('/', { waitUntil: 'domcontentloaded' });
    await page.evaluate(() => localStorage.setItem('bits-theme-mode', 'light'));
    await page.reload({ waitUntil: 'domcontentloaded' });
    await page.waitForLoadState('networkidle');

    await expect(page).toHaveScreenshot('homepage-mobile-light.png', {
      fullPage: true,
      maxDiffPixels: 100,
    });
  });

  test('homepage mobile - dark mode', async ({ page }) => {
    await page.goto('/', { waitUntil: 'domcontentloaded' });
    await page.evaluate(() => localStorage.setItem('bits-theme-mode', 'dark'));
    await page.reload({ waitUntil: 'domcontentloaded' });
    await page.waitForLoadState('networkidle');

    await expect(page).toHaveScreenshot('homepage-mobile-dark.png', {
      fullPage: true,
      maxDiffPixels: 100,
    });
  });
});
