import { mkdirSync, readFileSync, writeFileSync } from 'node:fs';
import { dirname, join, relative, resolve } from 'node:path';
import { spawnSync } from 'node:child_process';

const cwd = process.cwd();
const defaultSpec = 'tests/e2e/dashboard.spec.ts';
const specPath = process.argv[2] || defaultSpec;
const resolvedSpecPath = resolve(cwd, specPath);
const outputDir = resolve(cwd, 'tests/docs');
const outputFile = resolve(outputDir, 'dashboard-e2e-latest-report.md');
const rawJsonFile = resolve(cwd, 'test-results/dashboard-e2e-latest-report.json');
const baseURL = process.env.PLAYWRIGHT_BASE_URL || 'http://127.0.0.1:8000';
const playwrightCli = resolve(cwd, 'node_modules/playwright/cli.js');

mkdirSync(dirname(rawJsonFile), { recursive: true });
mkdirSync(outputDir, { recursive: true });

const run = spawnSync(
  process.execPath,
  [
    playwrightCli,
    'test',
    specPath,
    '--project=chromium',
    `--reporter=json`,
  ],
  {
    cwd,
    encoding: 'utf8',
    env: {
      ...process.env,
      PLAYWRIGHT_BASE_URL: baseURL,
    },
    maxBuffer: 20 * 1024 * 1024,
  }
);

writeFileSync(rawJsonFile, run.stdout || '', 'utf8');

const safeReadJson = () => {
  try {
    return JSON.parse(readFileSync(rawJsonFile, 'utf8'));
  } catch {
    return null;
  }
};

const report = safeReadJson();

const collectSpecs = (suite, filePath = '', items = []) => {
  const nextFilePath = suite.file || filePath;

  for (const child of suite.suites || []) {
    collectSpecs(child, nextFilePath, items);
  }

  for (const spec of suite.specs || []) {
    items.push({
      file: spec.file || nextFilePath,
      title: spec.title,
      ok: spec.ok,
      tests: spec.tests || [],
    });
  }

  return items;
};

const summarizeResult = (result) => {
  const attachments = (result.attachments || [])
    .filter((attachment) => attachment.path)
    .map((attachment) => `- ${attachment.name || 'attachment'}: \`${relative(cwd, attachment.path)}\``);

  const errors = [];

  if (result.error?.message) {
    errors.push(result.error.message.trim());
  }

  for (const item of result.errors || []) {
    if (item?.message) {
      errors.push(item.message.trim());
    }
  }

  return {
    status: result.status || 'unknown',
    durationMs: result.duration || 0,
    errors,
    attachments,
  };
};

const normalizeStatus = (test, resultStatus) => {
  if (test.status === 'skipped') {
    return 'skipped';
  }

  if (test.ok === true || resultStatus === 'passed' || test.status === 'expected') {
    return 'passed';
  }

  if (test.expectedStatus && test.status === test.expectedStatus) {
    return test.expectedStatus;
  }

  return resultStatus || test.status || 'unknown';
};

const specs = report ? collectSpecs(report) : [];
const failedCases = [];
const passedCases = [];
const skippedCases = [];

for (const spec of specs) {
  const titlePath = [spec.file, spec.title].filter(Boolean).join(' :: ');

  for (const test of spec.tests) {
    const testTitle = [...(test.titlePath || []), test.title].filter(Boolean).join(' > ') || titlePath;
    const results = (test.results || []).map(summarizeResult);
    const finalResult = results[results.length - 1] || { status: 'unknown', durationMs: 0, errors: [], attachments: [] };
    const item = {
      file: spec.file || specPath,
      title: testTitle,
      status: normalizeStatus(test, finalResult.status),
      durationMs: finalResult.durationMs,
      errors: finalResult.errors,
      attachments: finalResult.attachments,
    };

    if (item.status === 'passed') {
      passedCases.push(item);
    } else if (item.status === 'skipped') {
      skippedCases.push(item);
    } else {
      failedCases.push(item);
    }
  }
}

const exitCode = typeof run.status === 'number' ? run.status : 1;
const total = passedCases.length + failedCases.length + skippedCases.length;
const now = new Date().toISOString().slice(0, 10);

const markdown = [
  '# Dashboard E2E Auto Report',
  '',
  `- Date: \`${now}\``,
  `- Spec: \`${relative(cwd, resolvedSpecPath)}\``,
  `- Base URL: \`${baseURL}\``,
  `- Command: \`node node_modules/playwright/cli.js test ${specPath} --project=chromium --reporter=json\``,
  `- Exit code: \`${exitCode}\``,
  '',
  '## Summary',
  '',
  `- Total: \`${total}\``,
  `- Passed: \`${passedCases.length}\``,
  `- Failed: \`${failedCases.length}\``,
  `- Skipped: \`${skippedCases.length}\``,
  '',
];

if (failedCases.length > 0) {
  markdown.push('## Failed Cases', '');

  failedCases.forEach((item, index) => {
    markdown.push(`${index + 1}. \`${item.title}\``);
    markdown.push(`   File: \`${item.file}\``);
    markdown.push(`   Status: \`${item.status}\``);

    if (item.errors.length > 0) {
      markdown.push('   Error:');
      markdown.push(`   ${item.errors[0].split('\n').join('\n   ')}`);
    }

    if (item.attachments.length > 0) {
      markdown.push('   Attachments:');
      item.attachments.forEach((line) => markdown.push(`   ${line}`));
    }

    markdown.push('');
  });
} else {
  markdown.push('## Failed Cases', '', 'No failed cases.', '');
}

markdown.push('## Passed Cases', '');

if (passedCases.length > 0) {
  passedCases.forEach((item, index) => {
    markdown.push(`${index + 1}. \`${item.title}\``);
  });
  markdown.push('');
} else {
  markdown.push('No passed cases.', '');
}

if (run.stderr?.trim()) {
  markdown.push('## STDERR', '', '```text', run.stderr.trim(), '```', '');
}

writeFileSync(outputFile, markdown.join('\n'), 'utf8');

console.log(`Auto report generated: ${relative(cwd, outputFile)}`);
console.log(`Passed: ${passedCases.length}, Failed: ${failedCases.length}, Skipped: ${skippedCases.length}`);

if (failedCases.length > 0 || exitCode !== 0) {
  process.exit(exitCode || 1);
}
