import { test, expect } from '@playwright/test';

test.describe('Dark Mode', () => {
  test.beforeEach(async ({ page, context }) => {
    // Clear storage before navigation
    await context.clearCookies();
    await page.goto('/', { waitUntil: 'domcontentloaded' });
    await page.evaluate(() => localStorage.clear());
    await page.reload({ waitUntil: 'domcontentloaded' });
  });

  test('should toggle dark mode on click', async ({ page }) => {
    const toggle = page.locator('.bits-dark-toggle');

    // Initially should be light mode
    await expect(page.locator('body')).not.toHaveClass(/dark-layout/);

    // Click to enable dark mode
    await toggle.click();
    await expect(page.locator('body')).toHaveClass(/dark-layout/);

    // Click to disable dark mode
    await toggle.click();
    await expect(page.locator('body')).not.toHaveClass(/dark-layout/);
  });

  test('should persist dark mode preference', async ({ page }) => {
    // Enable dark mode
    await page.locator('.bits-dark-toggle').click();
    await expect(page.locator('body')).toHaveClass(/dark-layout/);

    // Reload page
    await page.reload({ waitUntil: 'domcontentloaded' });

    // Should still be dark mode
    await expect(page.locator('body')).toHaveClass(/dark-layout/);
  });

  test('should show correct icon based on mode', async ({ page }) => {
    const toggle = page.locator('.bits-dark-toggle');
    const moonIcon = toggle.locator('.icon-moon');
    const sunIcon = toggle.locator('.icon-sun');

    // Light mode should show moon icon (visible) and hide sun icon
    await expect(moonIcon).toBeVisible();
    await expect(sunIcon).toBeHidden();

    // Dark mode should show sun icon (visible) and hide moon icon
    await toggle.click();
    await expect(page.locator('body')).toHaveClass(/dark-layout/);
    await expect(sunIcon).toBeVisible();
    await expect(moonIcon).toBeHidden();
  });
});
