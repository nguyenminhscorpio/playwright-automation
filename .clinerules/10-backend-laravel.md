---
paths:
  - "app/**"
  - "routes/**"
  - "database/**"
  - "config/**"
  - "tests/Feature/**"
  - "tests/Unit/**"
---

# Laravel backend rules

- Keep controllers thin. Put business logic in services or repositories when it is more than simple orchestration.
- Prefer explicit Eloquent queries scoped to the current user when working with user-owned data such as decks, cards, notes, imports, or reviews.
- Follow existing naming and folder conventions in `app/Services`, `app/Repositories`, and `app/Http/Controllers`.
- Do not move server-rendered screen logic away from `ScreenController` unless the change clearly requires a different controller.
- Preserve return types and existing response styles used by nearby code.
- When adding new behavior, prefer extending the current service or repository that already owns that concern.
- Avoid hidden magic. Favor readable query building, clear variable names, and straightforward data shaping for Blade views and APIs.
