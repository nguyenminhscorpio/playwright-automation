import fs from 'node:fs';
import { defineConfig, devices } from '@playwright/test';

const localWindowsPhp = 'C:/laragon/bin/php/php-8.3.30-Win32-vs16-x64/php.exe';
const phpExecutable = process.env.PHP_EXECUTABLE || (fs.existsSync(localWindowsPhp) ? localWindowsPhp : 'php');
const artisanAppProvider = 'app/Providers/AppServiceProvider.php';
const canBootLaravelCli = fs.existsSync(artisanAppProvider);
const baseURL = process.env.PLAYWRIGHT_BASE_URL || 'http://127.0.0.1:8000';

export default defineConfig({
  testDir: './tests/e2e',
  globalSetup: './tests/e2e/global-setup.ts',
  timeout: 60 * 1000,
  fullyParallel: true,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 2 : 0,
  workers: 1,
  reporter: [['html', { open: 'never' }]],
  use: {
    baseURL,
    headless: !!process.env.CI,
    screenshot: 'only-on-failure',
    trace: 'retain-on-failure',
    video: 'retain-on-failure',
  },
  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
  ],
  webServer: canBootLaravelCli
    ? {
        command: `"${phpExecutable}" artisan serve --host=127.0.0.1 --port=8000`,
        port: 8000,
        reuseExistingServer: !process.env.CI,
        timeout: 120 * 1000,
      }
    : undefined,
});
