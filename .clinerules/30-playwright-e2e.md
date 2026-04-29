---
paths:
  - "tests/e2e/**"
  - "playwright.config.ts"
  - "scripts/run-playwright-report.mjs"
  - "tests/docs/**"
---

# Playwright and test rules

- Prefer stable locators: accessible roles first, then explicit `data-*` attributes when needed.
- If a test depends on seeded or created data, use API setup/cleanup when that is already the project pattern.
- After creating or deleting data outside the browser flow, reload or wait for the UI to reflect the new state before asserting.
- Keep screenshots, traces, and videos enabled for failing tests unless there is a strong reason to change the debugging setup.
- If changing routes, labels, or data attributes used by tests, update the affected specs in the same task.
- Keep `playwright.config.ts` and helper scripts aligned on host, port, and `PLAYWRIGHT_BASE_URL` behavior.
- Test reports in `tests/docs` should start with a quick conclusion, then summary, then grouped failures, then suggested next checks.
