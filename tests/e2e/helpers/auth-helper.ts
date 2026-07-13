import { expect, type Page } from '@playwright/test';

export const TEST_USER_EMAIL = process.env.PLAYWRIGHT_USER_EMAIL ?? 'admin@gmail.com';
export const TEST_USER_PASSWORD = process.env.PLAYWRIGHT_USER_PASSWORD ?? '123456Aa@';

const waitForUiReady = async (page: Page) => {
  await page.waitForLoadState('domcontentloaded');
  await page.waitForLoadState('networkidle').catch(() => undefined);
};

const isLoginPage = async (page: Page) => {
  if (/\/login(?:\?|$)/.test(page.url())) {
    return true;
  }

  return page.locator('form[action$="/login"], input[name="email"], input[name="password"]').first().isVisible().catch(() => false);
};

export const loginIfNeeded = async (page: Page) => {
  await waitForUiReady(page);

  if (!(await isLoginPage(page))) {
    return;
  }

  await page.locator('input[name="email"]').fill(TEST_USER_EMAIL);
  await page.locator('input[name="password"]').fill(TEST_USER_PASSWORD);
  await page.getByRole('button', { name: /sign in|login|đăng nhập/i }).click();

  await waitForUiReady(page);
  await expect(page).not.toHaveURL(/\/login(?:\?|$)/);
};

export const gotoAuthenticated = async (page: Page, path: string) => {
  await page.goto(path);
  await loginIfNeeded(page);

  const currentPath = new URL(page.url()).pathname;
  if (currentPath !== path.split('?')[0]) {
    await page.goto(path);
    await loginIfNeeded(page);
  }

  await waitForUiReady(page);
};
