# Project context

- This repository is a Laravel 13 + PHP 8.3 application with Vite for assets.
- The UI is primarily server-rendered with Blade views under `resources/views/screens`.
- Frontend behavior is lightweight and should stay compatible with the existing Blade-first approach.
- E2E coverage uses Playwright with specs under `tests/e2e`.

# Working rules

- Prefer small, focused changes that fit the current architecture instead of introducing a new pattern.
- Read the existing controller, service, repository, Blade, and test files before changing behavior.
- Reuse existing services and repositories before adding new abstractions.
- Keep user-facing copy, route names, and data attributes stable unless the task explicitly requires a change.
- If changing behavior that affects tests or reports, update the related Playwright specs or docs in the same task when practical.
- When domain intent is unclear, inspect `app/Spec/*.md` before making larger product decisions.
