import fs from 'node:fs';
import { spawnSync } from 'node:child_process';
import type { FullConfig } from '@playwright/test';
import { TEST_USER_EMAIL, TEST_USER_PASSWORD } from './helpers/auth-helper';

const localWindowsPhp = 'C:/laragon/bin/php/php-8.3.30-Win32-vs16-x64/php.exe';

export default async function globalSetup(_config: FullConfig) {
  const phpExecutable = process.env.PHP_EXECUTABLE || (fs.existsSync(localWindowsPhp) ? localWindowsPhp : 'php');
  const code = [
    "$user = \\App\\Models\\User::query()->updateOrCreate(",
    `['email' => '${TEST_USER_EMAIL}'],`,
    `['name' => 'Admin', 'password' => \\Illuminate\\Support\\Facades\\Hash::make('${TEST_USER_PASSWORD}')]`,
    ');',
    "echo 'Playwright user ready: '.$user->email.PHP_EOL;",
  ].join('');

  const result = spawnSync(phpExecutable, ['artisan', 'tinker', '--execute', code], {
    cwd: process.cwd(),
    encoding: 'utf8',
  });

  if (result.status !== 0) {
    throw new Error(
      [
        'Unable to prepare Playwright login user.',
        result.stdout,
        result.stderr,
      ].filter(Boolean).join('\n'),
    );
  }
}
