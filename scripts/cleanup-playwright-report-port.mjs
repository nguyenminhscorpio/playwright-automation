import { execSync } from 'node:child_process';

const port = process.env.PLAYWRIGHT_REPORT_PORT || '9323';

const getPidsOnWindows = (targetPort) => {
  const output = execSync(`netstat -ano | findstr :${targetPort}`, {
    encoding: 'utf8',
    stdio: ['ignore', 'pipe', 'ignore'],
    shell: true,
  }).trim();

  if (!output) {
    return [];
  }

  return [...new Set(
    output
      .split(/\r?\n/)
      .map((line) => line.trim().split(/\s+/).pop())
      .filter(Boolean)
  )];
};

const getPidsOnUnix = (targetPort) => {
  const output = execSync(`lsof -ti tcp:${targetPort}`, {
    encoding: 'utf8',
    stdio: ['ignore', 'pipe', 'ignore'],
    shell: true,
  }).trim();

  if (!output) {
    return [];
  }

  return [...new Set(output.split(/\r?\n/).filter(Boolean))];
};

const killPids = (pids) => {
  if (pids.length === 0) {
    console.log(`No process is using port ${port}.`);
    return;
  }

  for (const pid of pids) {
    if (process.platform === 'win32') {
      execSync(`taskkill /PID ${pid} /F`, { stdio: 'ignore', shell: true });
    } else {
      execSync(`kill -9 ${pid}`, { stdio: 'ignore', shell: true });
    }
  }

  console.log(`Cleared port ${port} from PID(s): ${pids.join(', ')}`);
};

try {
  const pids = process.platform === 'win32' ? getPidsOnWindows(port) : getPidsOnUnix(port);
  killPids(pids);
} catch {
  console.log(`No process is using port ${port}.`);
}
