---
name: Cognitive Clarity
colors:
  surface: '#f8f9fb'
  surface-dim: '#d9dadc'
  surface-bright: '#f8f9fb'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f3f4f6'
  surface-container: '#edeef0'
  surface-container-high: '#e7e8ea'
  surface-container-highest: '#e1e2e4'
  on-surface: '#191c1e'
  on-surface-variant: '#434655'
  inverse-surface: '#2e3132'
  inverse-on-surface: '#f0f1f3'
  outline: '#737686'
  outline-variant: '#c3c6d7'
  surface-tint: '#0053db'
  primary: '#004ac6'
  on-primary: '#ffffff'
  primary-container: '#2563eb'
  on-primary-container: '#eeefff'
  inverse-primary: '#b4c5ff'
  secondary: '#006c49'
  on-secondary: '#ffffff'
  secondary-container: '#6cf8bb'
  on-secondary-container: '#00714d'
  tertiary: '#784b00'
  on-tertiary: '#ffffff'
  tertiary-container: '#996100'
  on-tertiary-container: '#ffeedd'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#dbe1ff'
  primary-fixed-dim: '#b4c5ff'
  on-primary-fixed: '#00174b'
  on-primary-fixed-variant: '#003ea8'
  secondary-fixed: '#6ffbbe'
  secondary-fixed-dim: '#4edea3'
  on-secondary-fixed: '#002113'
  on-secondary-fixed-variant: '#005236'
  tertiary-fixed: '#ffddb8'
  tertiary-fixed-dim: '#ffb95f'
  on-tertiary-fixed: '#2a1700'
  on-tertiary-fixed-variant: '#653e00'
  background: '#f8f9fb'
  on-background: '#191c1e'
  surface-variant: '#e1e2e4'
typography:
  headline-xl:
    fontFamily: Lexend
    fontSize: 40px
    fontWeight: '700'
    lineHeight: '1.2'
  headline-lg:
    fontFamily: Lexend
    fontSize: 32px
    fontWeight: '600'
    lineHeight: '1.25'
  headline-md:
    fontFamily: Lexend
    fontSize: 24px
    fontWeight: '600'
    lineHeight: '1.3'
  body-lg:
    fontFamily: Inter
    fontSize: 18px
    fontWeight: '400'
    lineHeight: '1.6'
  body-md:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '400'
    lineHeight: '1.5'
  label-md:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '500'
    lineHeight: '1.4'
    letterSpacing: 0.02em
  label-sm:
    fontFamily: Inter
    fontSize: 12px
    fontWeight: '600'
    lineHeight: '1.4'
    letterSpacing: 0.05em
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  base: 8px
  xs: 4px
  sm: 12px
  md: 16px
  lg: 24px
  xl: 40px
  gutter: 16px
  margin: 24px
---

## Brand & Style
The design system is anchored in the "Corporate / Modern" aesthetic, distilled to support high-performance learning. The brand personality is **helpful, focused, and motivating**, designed to reduce cognitive load while providing positive reinforcement through visual feedback. 

The interface prioritizes functional minimalism to ensure users remain in a "flow state" during study sessions. It balances professional reliability with a friendly accessibility, using generous whitespace to prevent information density from becoming overwhelming. The overall emotional response should be one of quiet confidence and steady progress.

## Colors
The palette utilizes a high-contrast primary blue to drive action and focus. Success green and warning yellow are reserved for functional feedback—specifically for "Correct" and "Review Later" states in the learning loop. 

The background grey (#F3F4F6) provides a soft, non-reflective canvas that reduces eye strain during long study periods. Neutral tones follow a strict hierarchy: deep charcoal for primary legibility and mid-tone greys for secondary instructional text. Surface colors should remain white (#FFFFFF) to pop against the neutral background.

## Typography
The design system employs **Lexend** for headings to leverage its specific design for readability and its motivating, athletic character. **Inter** is used for all functional body text and UI labels to maintain a clean, systematic feel.

Typography should be used to create a clear information hierarchy: 
- Use Headline-XL for achievement milestones.
- Use Body-LG for the content on flashcards to ensure maximum legibility.
- Use Label-SM in all-caps for category tags or metadata.

## Layout & Spacing
The design system relies on a **12-column fluid grid** for dashboard views and a centered **fixed-width container (max 640px)** for the core flashcard study interface. This narrowing of the layout during study sessions minimizes peripheral distractions.

An 8px linear scale governs all padding and margins. Larger increments (24px, 40px) are used to separate distinct content blocks, while smaller increments (8px, 12px) group related elements like a question and its associated hint.

## Elevation & Depth
Depth is communicated through **ambient shadows** and a three-tier tonal layer system. 

1. **Level 0 (Background):** The neutral grey (#F3F4F6) acts as the base.
2. **Level 1 (Cards/Containers):** Pure white surfaces with a subtle, diffused shadow (Blur: 10px, Y: 4px, Opacity: 5% Black).
3. **Level 2 (Active/Hover States):** Enhanced shadows with a slight primary blue tint to indicate interactivity (Blur: 20px, Y: 8px, Opacity: 10% Primary Blue).

This creates a "tactile paper" feel, where the flashcards appear to sit just above the workspace.

## Shapes
The shape language uses a **Rounded** (Level 2) approach to maintain the "friendly" brand promise. Standard components like input fields and buttons use a 0.5rem (8px) radius. 

Flashcards and major containers use a `rounded-xl` (1.5rem / 24px) corner radius to differentiate them from smaller UI controls and emphasize them as the primary objects of interaction. Circular shapes are reserved strictly for progress indicators and user avatars.

## Components
- **Flashcards:** The centerpiece. Use white backgrounds, 24px rounded corners, and a Level 1 shadow. Content should be centered with Headline-MD or Body-LG typography.
- **Buttons:** Primary buttons use the Primary Blue with white text. They should have a subtle 2px bottom border in a darker shade of blue to provide a tactile, pressable feel.
- **Progress Bars:** Use a thick 8px track. The unfilled portion should be a light tint of the primary color, while the filled portion uses a gradient from Primary Blue to Success Green to visualize "mastery."
- **Chips/Tags:** Used for "Difficulty" or "Subject." These should have 0.25rem rounded corners and use low-saturation background tints of the primary/secondary colors to remain unobtrusive.
- **Input Fields:** Clean white boxes with a 1px border (#D1D5DB). On focus, the border transitions to Primary Blue with a soft 3px outer glow.
- **Lists:** Study decks should be presented in vertical lists with 12px spacing between items, each encased in a Level 1 elevated surface.