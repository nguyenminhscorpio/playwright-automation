import { spawnSync } from 'node:child_process';
import { resolve } from 'node:path';

const cwd = process.cwd();
const cleanupScript = resolve(cwd, 'scripts/cleanup-playwright-report-port.mjs');
const playwrightCli = resolve(cwd, 'node_modules/playwright/cli.js');
const port = process.env.PLAYWRIGHT_REPORT_PORT || '9323';
const host = process.env.PLAYWRIGHT_REPORT_HOST || '127.0.0.1';

spawnSync(process.execPath, [cleanupScript], {
  cwd,
  stdio: 'inherit',
  env: process.env,
});

const result = spawnSync(
  process.execPath,
  [playwrightCli, 'show-report', '--host', host, '--port', port],
  {
    cwd,
    stdio: 'inherit',
    env: process.env,
  }
);

process.exit(result.status ?? 0);
