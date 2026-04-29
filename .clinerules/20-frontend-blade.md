---
paths:
  - "resources/views/**"
  - "resources/js/**"
  - "resources/css/**"
  - "app/HTML/**"
---

# Frontend and Blade rules

- This project is not a React SPA. Prefer Blade, semantic HTML, CSS, and small JavaScript enhancements.
- Preserve existing class naming patterns such as `hero__title`, `deck-card__title`, and similar BEM-like structures.
- Keep markup accessible: use semantic headings, buttons, links, labels, and readable text.
- Treat `data-*` attributes as part of the testing contract. Do not remove or rename them casually.
- If UI changes affect navigation or screen meaning, keep route targets and button/link labels consistent unless the task requires a deliberate rename.
- When updating styles, preserve the current visual direction unless the task asks for a redesign.
- Prefer simple DOM structure over clever abstractions so Blade templates stay easy to read and maintain.
