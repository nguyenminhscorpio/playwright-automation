import { expect, test } from '@playwright/test';

test.describe('Dashboard smoke', () => {
  test('loads dashboard successfully', async ({ page }) => {
    await page.goto('/dashboard');
    await expect(page).toHaveURL(/dashboard/);
  });
});
