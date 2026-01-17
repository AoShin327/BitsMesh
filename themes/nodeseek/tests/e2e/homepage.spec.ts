import { test, expect } from '@playwright/test';

test.describe('Homepage', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/', { waitUntil: 'domcontentloaded' });
  });

  test('should display header with site title', async ({ page }) => {
    await expect(page.locator('.bits-site-title')).toBeVisible();
  });

  test('should display navigation menu', async ({ page }) => {
    await expect(page.locator('.bits-nav-menu')).toBeVisible();
  });

  test('should display main content area', async ({ page }) => {
    await expect(page.locator('#bits-body-left')).toBeVisible();
  });

  test('should display right panel on desktop', async ({ page }) => {
    await expect(page.locator('#bits-right-panel')).toBeVisible();
  });

  test('should have dark mode toggle', async ({ page }) => {
    await expect(page.locator('.bits-dark-toggle')).toBeVisible();
  });

  test('should have search box', async ({ page }) => {
    await expect(page.locator('.bits-search-box')).toBeVisible();
  });
});

test.describe('Homepage - Mobile', () => {
  test.use({ viewport: { width: 375, height: 667 } });

  test.beforeEach(async ({ page }) => {
    await page.goto('/', { waitUntil: 'domcontentloaded' });
  });

  test('should hide right panel on mobile', async ({ page }) => {
    await expect(page.locator('#bits-right-panel')).not.toBeVisible();
  });

  test('should show hamburger menu on mobile', async ({ page }) => {
    await expect(page.locator('.bits-hamburger')).toBeVisible();
  });
});
