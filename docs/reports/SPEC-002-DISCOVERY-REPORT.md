# SPEC-002 Technical Discovery Report

## 1. Repository state

- Repository: `AgendaEstetica`.
- Discovery base: `22ec5a2` (`docs: define SPEC-002 UX design system`).
- `main`: `3f558ae`.
- SPEC-002 definition branch: `origin/docs/spec-002-definition` at `22ec5a2`.
- Working tree at Discovery start: clean.
- SPEC-001: `CLOSED`.
- SPEC-002: `READY FOR DISCOVERY`.
- Implementation: not authorized.

No application code, dependencies or generated assets were changed during Discovery.

## 2. Discovery branch

Discovery was performed on:

```text
docs/spec-002-discovery
```

The branch was created exactly from `22ec5a2`.

## 3. Documentation reviewed

Read completely:

- `AGENTS.md`.
- `docs/context/CONTEXT_INDEX.md`.
- `docs/context/PROJECT_CONTEXT.md`.
- `docs/context/BUSINESS_CONTEXT.md`.
- `docs/MASTER_TECHNICAL_SPEC.md`.
- `docs/architecture/ARCHITECTURE.md`.
- `docs/architecture/adr/ADR-001-adopt-laravel-13.md`.
- `docs/architecture/adr/ADR-002-adopt-mysql-8-4-lts.md`.
- `docs/domain/DOMAIN_RULES.md`.
- `docs/roadmap/ROADMAP.md`.
- `docs/testing/TEST_PLAN.md`.
- `docs/specs/SPEC-001-project-foundation.md`.
- `docs/specs/SPEC-002-ux-design-system.md`.
- `docs/reports/SPEC-001-IMPLEMENTATION-REPORT.md`.

Supporting implementation files were inspected read-only to establish the actual frontend baseline.

## 4. Current frontend tree

Relevant tracked source tree:

```text
resources/
├── css/
│   └── app.css
├── js/
│   ├── App.test.ts
│   ├── App.vue
│   ├── app.ts
│   ├── env.d.ts
│   ├── composables/
│   │   ├── useAuth.test.ts
│   │   └── useAuth.ts
│   ├── layouts/
│   │   ├── AdminLayout.vue
│   │   └── PublicLayout.vue
│   ├── pages/
│   │   ├── NotFoundPage.vue
│   │   ├── admin/
│   │   │   ├── AdminLoginPage.vue
│   │   │   └── AdminPage.vue
│   │   └── public/
│   │       └── FoundationPage.vue
│   ├── router/
│   │   ├── index.test.ts
│   │   └── index.ts
│   └── services/
│       ├── http.test.ts
│       ├── http.ts
│       └── api/
│           └── auth.ts
└── views/
    └── app.blade.php
```

There is no `components/` directory, no `stores/` directory and no existing UI component layer.

## 5. Existing Vue component inventory

| File | Classification | Evidence and recommendation |
| --- | --- | --- |
| `resources/js/App.vue` | `KEEP`, `TECHNICAL ONLY` | Root `RouterView` only. No visual responsibility should be added beyond application composition. |
| `resources/js/layouts/PublicLayout.vue` | `RESTYLE IN SPEC-002` | Real public surface shell with page background, padding and max-width. Keep slot contract. |
| `resources/js/layouts/AdminLayout.vue` | `RESTYLE IN SPEC-002` | Real admin shell with a separate background. Keep slot contract and avoid dashboard navigation. |
| `resources/js/pages/public/FoundationPage.vue` | `RESTYLE IN SPEC-002`, `TECHNICAL ONLY` | Existing technical route at `/`; may serve as a bounded technical showcase without business content. |
| `resources/js/pages/admin/AdminLoginPage.vue` | `RESTYLE IN SPEC-002` | First real form consumer. Preserve auth calls, route navigation and error behavior. |
| `resources/js/pages/admin/AdminPage.vue` | `RESTYLE IN SPEC-002`, `TECHNICAL ONLY` | Authenticated technical state only; no dashboard modules. |
| `resources/js/pages/NotFoundPage.vue` | `RESTYLE IN SPEC-002`, `TECHNICAL ONLY` | Error surface using the public shell. Preserve router behavior and accessible messaging. |

There are no existing reusable Vue components to classify as `REFACTOR IN SPEC-002`.

## 6. Existing layouts

### `PublicLayout.vue`

- Renders a `main` landmark.
- Uses `min-h-screen`, `bg-slate-50`, `px-6`, `py-12`, `text-slate-900`.
- Uses a centered `max-w-3xl` wrapper.
- Provides only a default slot.
- Has no header, footer, navigation, logo or business content.

### `AdminLayout.vue`

- Renders a `main` landmark.
- Uses `min-h-screen`, `bg-slate-100`, `px-6`, `py-12`, `text-slate-900`.
- Uses the same centered `max-w-3xl` wrapper as the public layout.
- Provides only a default slot.
- Has no dashboard navigation or module structure.

Both layouts should share semantic tokens, container conventions and focus rules while retaining separate public/admin layout responsibilities.

## 7. Existing technical pages/routes

| Route | Current behavior | Discovery classification |
| --- | --- | --- |
| `/` | `FoundationPage`, public meta surface, technical Vue SPA shell message | Restyle in place as the bounded technical public surface; do not turn it into a landing page. |
| `/admin/login` | Sanctum login form with email/password, API error message and submitting disabled state | Restyle in place; first form-system consumer. Auth logic remains untouched. |
| `/admin` | Authenticated user name/email and logout button | Restyle in place as a technical authenticated state; no dashboard. |
| Unknown frontend path | `NotFoundPage` rendered through `PublicLayout` | Restyle technical error surface; preserve fallback route. |

`app.blade.php` already declares `lang="es"`, a viewport and the Vite CSS/JS entries. The document title remains the generic `AgendaEstetica`; changing metadata is not required for this Discovery and is not an SEO implementation task.

## 8. Tailwind CSS 4 current setup

- Installed `tailwindcss`: `4.3.3`.
- Installed `@tailwindcss/vite`: `4.3.3`.
- `vite.config.ts` registers `laravel-vite-plugin`, Vue and `@tailwindcss/vite`.
- `resources/css/app.css` begins with `@import 'tailwindcss';`.
- `app.css` explicitly registers vendor pagination views, storage views, `resources/js` and `resources/views` through `@source`.
- There is no `tailwind.config.js`, `tailwind.config.ts` or Tailwind 3 configuration.
- The current `@theme` block defines only `--font-sans` as Instrument Sans plus system/emoji fallbacks.
- There are no custom CSS layers, semantic tokens, `:root` variables, `@font-face`, component classes, breakpoints or motion rules.
- Existing source classes are statically written in Vue templates, which is compatible with Tailwind source detection.

Official Tailwind CSS 4 documentation confirms that `@theme` variables define token namespaces and generate corresponding utilities, while regular `:root` variables are appropriate for values that should not create utility classes. The documentation also warns against dynamically constructing class names because source detection operates on complete text tokens.

Sources:

- <https://tailwindcss.com/docs/theme>
- <https://tailwindcss.com/docs/detecting-classes-in-source-files>

## 9. Current CSS architecture

Current architecture is a single CSS entry point:

```text
resources/css/app.css
  @import tailwindcss
  @source declarations
  @theme font-sans override
```

There is no separate token file, component stylesheet, global base layer, scoped component CSS or custom utility layer. This is a good starting point for a small CSS-first system and does not justify introducing a larger CSS directory hierarchy during SPEC-002.

## 10. Hardcoded visual-value audit

### Colors

- No custom hex, RGB, HSL or CSS color literals exist in `resources/js`, `resources/css` or `resources/views`.
- Existing colors are Tailwind default slate utilities: `slate-50`, `slate-100`, `slate-200`, `slate-300`, `slate-500`, `slate-600`, `slate-900`, `red-700` and `white`.
- The current implementation does not use any confirmed Yaris brand color.

### Typography

- `app.css` hardcodes the current `Instrument Sans` sans stack.
- Templates use utility sizes `text-sm`, `text-3xl`, weights `font-medium` and `font-semibold`, tracking utilities and default inherited font behavior.
- No requested Yaris font family is loaded or declared.

### Spacing, radius and shadow

- Layouts repeat `px-6`, `py-12` and `max-w-3xl`.
- Technical cards repeat `rounded-xl`, `border`, `p-8` and `shadow-sm`.
- The login form repeats `mt-6`, `space-y-4`, `mt-1`, `px-3`, `py-2`.
- Buttons repeat `rounded-md`, `px-4`, `py-2` and `text-sm`.
- There are no custom radius, shadow or spacing values outside Tailwind utilities.

### Motion and responsive utilities

- No `transition`, `animation`, `transform`, `duration-*`, `animate-*` or `prefers-reduced-motion` rule exists.
- No responsive variants such as `sm:`, `md:`, `lg:`, `xl:` or `2xl:` are used in the technical pages.
- The current system is therefore not evidence of responsive behavior beyond fluid width and max-width constraints.

## 11. Recommended token architecture

Keep one CSS-first source in `resources/css/app.css`, using Tailwind 4 primitives rather than a Tailwind 3 config file.

Recommended layers:

1. `@import` and existing `@source` declarations remain at the top.
2. Brand primitive tokens use `@theme` namespaces that should generate utilities only where direct utility use is valuable.
3. Semantic roles are defined separately from raw brand primitives. Roles should describe intent, not hue, for example surface, text, action, border, focus and feedback.
4. If a semantic role must become a utility, expose it through an explicit Tailwind `@theme inline` mapping to a semantic CSS variable rather than duplicating raw values.
5. Use normal `:root` custom properties for semantic values that should not create a broad utility API.
6. Keep public/admin differences in role values or layout context, not in two independent token systems.
7. Add only repeated typography, spacing, radius, shadow and motion values; retain Tailwind defaults when no project-specific evidence exists.

This preserves the current build and source detection model, keeps tokens inspectable in CSS, and avoids speculative utilities. Any dynamic component variant must map props to complete static class strings, not construct class names from arbitrary values.

## 12. Brand primitive strategy

Use only the confirmed primitives:

| Primitive | Value | Discovery use |
| --- | --- | --- |
| Brand black | `#050505` | Primary dark surface and dark text contexts after contrast review. |
| Brand fuchsia | `#D01772` | Primary action/accent candidate; white text passes the normal-text calculation in the tested pair. |
| Brand pink | `#CB6CA5` | Accent/decorative or dark-text context; white text is not safe for normal text in the tested pair. |
| Brand turquoise | `#3AC4D7` | Accent/decorative or dark-text context; white text is not safe in the tested pair. |
| Soft gold | `#F5CC7A` | Accent/decorative or dark-text context; white text is not safe in the tested pair. |
| White | `#FFFFFF` | Light surface and text on sufficiently dark backgrounds. |

Neutral grays may remain technical tokens for borders, muted text and surfaces. They must not be presented as additional confirmed brand colors.

## 13. Semantic token strategy

The implementation should define a deliberately small semantic vocabulary, subject to final naming during approved implementation:

- `surface-page`.
- `surface-elevated`.
- `surface-inverse`.
- `text-primary`.
- `text-secondary`.
- `text-inverse`.
- `border-default`.
- `border-strong`.
- `action-primary` and its foreground/focus roles.
- `action-secondary` and its foreground/focus roles.
- `focus-ring`.
- `feedback-success`, `feedback-warning`, `feedback-danger` and `feedback-info`.

Brand primitives should feed these roles only after contrast and usage context are verified. A single semantic role should not silently change meaning between public and admin surfaces.

## 14. Current typography baseline

- The only declared family is Instrument Sans in `--font-sans`.
- No `@font-face` rule exists.
- No font files are present in source or public asset locations.
- No `font-display`, preload or unicode-range configuration exists.
- Current templates rely on Tailwind default font sizes and weights.
- There is no typographic role separation between display, navigation, body or action text.

## 15. Font availability/licensing research

Repository evidence:

- No Cormorant Garamond, Montserrat or Poppins binaries exist.
- No font license files exist in the repository.
- No external font stylesheet is referenced in `app.blade.php` or `app.css`.

Official-source evidence reviewed:

- Google Fonts metadata identifies Cormorant Garamond as OFL, with a variable `wght` axis from 300 to 700. Source: <https://raw.githubusercontent.com/google/fonts/main/ofl/cormorantgaramond/METADATA.pb>.
- Google Fonts metadata identifies Montserrat as OFL, with a variable `wght` axis from 100 to 900. Source: <https://raw.githubusercontent.com/google/fonts/main/ofl/montserrat/METADATA.pb>.
- Google Fonts metadata identifies Poppins as OFL and lists normal static weights from 100 through 900. Source: <https://raw.githubusercontent.com/google/fonts/main/ofl/poppins/METADATA.pb>.
- The corresponding OFL license texts were checked for all three families in the Google Fonts repository. Sources: the `OFL.txt` files in the same three directories.
- Google Fonts CSS API documentation confirms external CSS delivery, precise style/weight selection, variable-font axis ranges and a `display` parameter, but this does not resolve the project's privacy or network policy. Source: <https://developers.google.com/fonts/docs/css2>.

The official metadata is evidence of upstream availability and licensing model, not evidence that the project is authorized to redistribute a particular binary or has selected a provider.

## 16. Font loading options

### A. Self-hosted fonts

Pros:

- No runtime third-party font request.
- Stable rendering independent of provider availability.
- Easier CSP and privacy posture once assets and licenses are approved.
- Local caching and deployment behavior are under project control.

Cons:

- Requires approved files, license notices and an asset ownership decision.
- Adds font bytes and potentially multiple subsets/weights to the repository or build artifacts.
- Requires careful `font-display`, preload and layout-shift decisions.

### B. External font provider

Pros:

- No font binaries in the repository.
- Provider can optimize formats, subsets and variable axes.
- Easy initial setup.

Cons:

- Runtime network dependency and privacy implications.
- CSP, consent and availability considerations.
- Additional DNS/request latency and potential layout shift.
- Provider choice would be a durable external dependency and may require an ADR.

### C. npm/package-based font assets

Pros:

- Versioned dependency and reproducible package installation if a legitimate package is selected.
- Potentially easy bundler integration.

Cons:

- Package provenance and license must be verified separately from the upstream font.
- Adds dependency and lockfile churn for an asset that can be served directly.
- Does not inherently solve subset, weight, privacy or payload decisions.

No option was installed or downloaded during Discovery.

## 17. Recommended font strategy

`RECOMMENDED`: self-host only after Discovery evidence confirms the exact assets, licenses, weights and distribution path. Prefer the smallest Latin/Latin-ext payload that covers confirmed Spanish UI copy, use only required weights, preserve the license notices, and use a non-blocking `font-display` strategy after implementation testing.

`ALTERNATIVE`: use documented system fallbacks for the first implementation if approved font assets are unavailable. This permits token and component work without pretending that brand typography is complete.

`REJECTED FOR NOW`: unconditional Google Fonts CDN, Fontsource or manual binary addition without a project-specific evidence and approval step. Google Fonts is a legitimate source, but external delivery is not automatically acceptable for privacy, CSP and availability reasons.

The recommendation is consistent with the project's no-unapproved-external-provider rule. It does not authorize acquiring or adding font files.

## 18. Font implementation blockers

- No Discovery blocker: token naming, fallback stacks, component architecture and non-font layout work can be planned independently.
- Checkpoint blocker: final typography implementation requires an approved distribution strategy and exact weights.
- Final-acceptance blocker: brand-typography acceptance cannot be marked complete while the font strategy or license/assets remain unresolved.
- Full development approval: recommended only after the font decision is recorded, or after an explicit fallback-only exception is approved.

## 19. Logo/brand asset inventory

Inspected source and legitimate asset locations:

- `public/`: only framework/public technical files and `favicon.ico`; no Yaris logo.
- `resources/`: no SVG, PNG, JPG, WebP, logo or image asset.
- `storage/app/public/`: only `.gitignore`; no uploaded asset.
- `resources/views/app.blade.php`: no logo markup.
- Vue pages/layouts: no image, SVG or logo markup.
- `public/build/`: generated build output was not treated as a source asset; it contains no tracked brand asset.

## 20. Logo availability result

```text
APPROVED LOGO ASSET: NOT AVAILABLE
```

No original logo, vector, raster, light variant, dark variant or source file is available in the repository locations inspected.

## 21. Logo-dependent scope

Logo-independent work:

- Token architecture.
- Typography role definitions and fallbacks.
- Button, form, container, section and card primitives.
- Public/admin layout structure.
- Technical route restyling without logo dependency.
- Accessibility and responsive behavior.

Logo-dependent work:

- Final official logo placement.
- Logo-specific sizing, clear space, contrast variants and asset acceptance.
- Any criterion that claims official brand-mark fidelity.

The absence of the logo does not block Discovery or the entire implementation. It blocks only logo-dependent acceptance unless a text-only or neutral-placeholder exception is approved.

## 22. Logo implementation blockers

- No blocker for Discovery.
- No blocker for token/component/layout implementation if a neutral technical placeholder or plain text is permitted.
- Blocker for logo-specific acceptance and final brand-mark fidelity.
- Prohibited actions: redraw, generate, trace, recolor or invent a replacement logo.

## 23. Existing responsive baseline

The source currently provides only limited fluid behavior:

- `w-full` inputs can shrink to the parent width.
- `max-w-3xl` centers the main content.
- `min-h-screen` fills the viewport height.
- Fixed horizontal padding `px-6` and vertical padding `py-12` apply at every width.
- No responsive utility variants are present.
- No grid, flex, breakpoint, overflow, image or navigation behavior exists in the technical pages.

No browser automation or viewport rendering was run. This is a code/build baseline, not a visual certification.

## 24. Responsive gaps

- The same large vertical padding is used at small and large widths without evidence that it is appropriate.
- The `max-w-3xl` wrapper is generic and has no surface-specific container strategy.
- Form controls have no explicit minimum touch sizing or focus geometry.
- There is no mobile navigation because there is no navigation yet; no navigation should be invented in this SPEC.
- No responsive two-column or content-density behavior exists to evaluate.
- Long translated labels and error messages have not been tested at representative widths.
- No explicit `overflow-x` policy or image behavior exists.

## 25. Supported viewport strategy

Recommended implementation review matrix:

- Small phone: narrow single column, wrapped labels, no horizontal scrolling.
- Large phone: same hierarchy with modest spacing adjustments only where evidence supports them.
- Tablet: wider container and optional grouped fields only when a real technical flow benefits.
- Desktop: restrained max-width, balanced whitespace and distinct public/admin density.
- Wide desktop: verify content does not become excessively narrow or stretched.

Use representative viewport widths rather than commercial device names. The browser baseline should target current evergreen Chrome, Edge, Firefox and Safari, plus current iOS/Android browser families. No legacy Internet Explorer or obsolete browser support is required by the project.

## 26. Existing accessibility baseline

| Area | Status | Evidence |
| --- | --- | --- |
| Document language | `PASS` | `app.blade.php` declares `lang="es"`. |
| Main landmark | `PASS` | Both layouts render `main`. |
| Heading hierarchy | `PASS` | Technical pages provide a single visible `h1` each. |
| Form labels | `PASS` | Login labels use `for` with matching `id`. |
| Button semantics | `PASS` | Actions use native `button` elements with types. |
| Error announcement | `PASS` | Login error uses `role="alert"`. |
| Auth autocomplete | `PASS` | Email and password fields use appropriate `autocomplete` values. |
| Keyboard focus styling | `GAP` | No project `:focus-visible` style exists; browser defaults are the only evidence. |
| Error association | `GAP` | Error is global `role="alert"`; fields have no `aria-invalid` or `aria-describedby` association. |
| Loading semantics | `GAP` | Submit text changes and button disables, but no explicit status semantics exist. |
| Disabled visual treatment | `GAP` | Only `disabled:opacity-50` is present on the login button; contrast and distinction are unverified. |
| Navigation landmark/skip link | `NOT PRESENT` | No navigation or skip link exists, and no navigation is needed for current technical routes. |
| Reduced motion | `NOT PRESENT` | No motion exists and no explicit reduced-motion rule exists. |
| Images/alt text | `NOT APPLICABLE` | No images exist in the current technical UI. |
| Modal keyboard handling | `NOT APPLICABLE` | No modal exists. |

This is a baseline audit, not a WCAG conformance claim.

## 27. Accessibility gaps

Implementation should address:

- Project-level `:focus-visible` treatment across dark, light and accent surfaces.
- Form-level and field-level error semantics without changing auth behavior.
- Explicit loading/status semantics for async login and future reusable actions.
- Disabled versus loading visual distinction.
- Contrast validation for brand-derived action and focus roles.
- Practical touch target sizing for buttons and inputs.
- Reduced-motion CSS baseline even if initial motion is minimal.

Do not add an ARIA attribute solely to hide an underlying semantic problem. Prefer native elements and labels first.

## 28. Contrast analysis

Contrast was calculated from the confirmed sRGB hex values using the WCAG relative-luminance formula. These are static pair calculations, not a complete component audit.

| Foreground on background | Ratio | Discovery result |
| --- | ---: | --- |
| White on black | `20.38:1` | Safe for normal text by the WCAG AA minimum-text threshold. |
| White on fuchsia | `5.19:1` | Safe for normal text by the WCAG AA threshold. |
| Black on fuchsia | `3.93:1` | Not safe for normal text; suitable only for large-text contexts if other component requirements pass. |
| White on pink | `3.37:1` | Not safe for normal text; large-text-only candidate. |
| Black on pink | `6.04:1` | Safe for normal text. |
| White on turquoise | `2.09:1` | Not safe for normal or large text; use as accent/decorative background with dark text instead. |
| Black on turquoise | `9.76:1` | Safe for normal text. |
| White on soft gold | `1.52:1` | Not safe for text; do not use white text on gold. |
| Black on soft gold | `13.39:1` | Safe for normal text. |

The W3C WCAG 2.2 explanation uses `4.5:1` for normal text and `3:1` for large text under SC 1.4.3. Brand colors must therefore be assigned to semantic roles with foregrounds chosen by context, not used as arbitrary text/background pairs.

Source: <https://www.w3.org/WAI/WCAG22/Understanding/contrast-minimum.html>.

## 29. Reduced-motion baseline

- Current source has no transition, animation or transform rules.
- Current absence of motion avoids an existing motion regression but is not a reusable policy.
- Future non-essential motion should be short and purposeful.
- Future CSS must use `prefers-reduced-motion: reduce` to suppress non-essential transitions/animations.
- The W3C reference identifies the reduced-motion media query as a sufficient technique for disabling interaction animation; this is a baseline requirement, not a mandate for an animation library.

Source: <https://www.w3.org/WAI/WCAG22/Understanding/animation-from-interactions.html>.

## 30. Component candidates

| Candidate | Current evidence | Discovery result |
| --- | --- | --- |
| Button | Login submit and admin logout already exist | Real reusable primitive. |
| FormField | Login labels, inputs and alert form a repeated field pattern | Real pattern, likely required with Input. |
| Input | Login has email and password controls | Required first consumer. |
| Textarea | No current consumer | Defer. |
| Select | No current consumer | Defer until business form exists. |
| Checkbox | No current consumer | Defer. |
| Radio | No current consumer | Defer. |
| Card | Four technical pages use bordered padded sections | Required only as a modest surface primitive, not a broad content system. |
| Badge | No meaningful current state needs it | Defer. |
| Modal/Dialog | No current consumer | Defer. |
| Loading indicator | Login has text/disabled loading behavior | Start as a Button state; standalone spinner not required. |
| Error state | Login alert and NotFound page exist | Required as a semantic pattern; standalone component only if reuse proves it. |
| Empty state | No current empty data surface | Defer until a real flow exists. |
| Container | Both layouts duplicate `max-w-3xl` wrapper | Required structural primitive or shared layout convention. |
| Section | Technical pages use a single section/card surface | Useful if it reduces layout duplication; keep API small. |

## 31. Required components

Recommended minimum for SPEC-002 implementation:

- `Button` with primary, secondary/outline and quiet/text variants only where consumers exist.
- `FormField`/field pattern with label, control, help/error association and required state.
- `Input` for the existing email/password form.
- `Container` or an equivalent shared container convention.
- `Section` or `Card` for the existing technical surfaces, but not both if they become redundant.
- Semantic loading, error and disabled states as component behavior rather than speculative standalone systems.

Each required component needs a real technical consumer and focused tests.

## 32. Deferred components

Defer until first real use:

- `Textarea`.
- `Select`.
- `Checkbox`.
- `Radio`.
- `Badge`.
- `Modal/Dialog`.
- Standalone spinner.
- Toast/notification system.
- Tables, date pickers, calendars, tabs, drawers, navigation systems and data visualizations.

This keeps SPEC-002 appropriately sized and avoids anticipating business core or appointments.

## 33. Component naming/structure recommendation

Recommended location if implementation proves the components necessary:

```text
resources/js/components/ui/
```

Use project-specific semantic names such as `UiButton.vue`, `UiInput.vue`, `UiFormField.vue`, `UiCard.vue` and `UiContainer.vue` only if the repository adopts a consistent prefix. `Base*` and `App*` are alternatives, but mixing naming families should be avoided.

Recommendation:

- Keep reusable components presentational and prop-driven.
- Keep business/API/auth logic in existing services/composables.
- Use static class maps for variants so Tailwind source detection sees complete classes.
- Do not create a component wrapper for every single Tailwind utility.
- Prefer one component with a small explicit variant API over parallel near-duplicates.

## 34. Public layout recommendation

Keep `PublicLayout` as a public technical shell with:

- `main` landmark.
- Shared container width and responsive padding.
- Page background and semantic text roles.
- Optional technical header/footer slots only if a real technical use case requires them.
- No invented business navigation, social links, address, services or promotional copy.

The current `/` page should remain technical until a later SPEC authorizes public business content.

## 35. Admin layout recommendation

Keep `AdminLayout` as a restrained authenticated technical shell with:

- Shared container, typography and controls.
- Clear distinction from the public surface through density and surface roles, not a second token system.
- No final dashboard navigation, metrics, module menu or business action.
- Presentation-only treatment of the current authenticated state.

The layout must not change route guards, session handling or API behavior.

## 36. Public/admin shared-system strategy

Share:

- Brand primitives and semantic color roles.
- Typography roles and fallback policy.
- Spacing, radius, border, focus and motion rules.
- Button, input, field, error and loading behavior.
- Accessibility conventions.

Diverge only where evidence supports it:

- Page layout and content density.
- Navigation structure when future modules exist.
- Surface/background emphasis.
- Information presentation and operational density.

Do not maintain two independent design systems.

## 37. Form-system recommendation

Use `/admin/login` as the first real consumer.

Recommended contract:

- Native `label` and control association remains mandatory.
- `FormField` owns label/help/error relationships, not authentication logic.
- Input supports default, focus-visible, invalid, disabled and read-only states only if a real consumer exists.
- Error text receives a stable id and is associated with the control when field-specific errors are available.
- Global auth errors remain a form-level alert and must not reveal account existence.
- Submit loading is represented by the button state and an appropriate status message without introducing a toast system.
- `autocomplete="username"` and `autocomplete="current-password"` are preserved.

## 38. Button variants/states recommendation

Required states:

- Default.
- Hover where the pointer supports it.
- Focus-visible.
- Active/pressed where meaningful.
- Disabled.
- Loading.

Recommended variants:

- Primary action.
- Secondary/outline.
- Quiet/text.

Destructive is deferred because no destructive action is currently in scope. Avoid a large variant matrix until a real consumer requires it.

## 39. Feedback-state recommendation

- Loading: begin with button loading and route/form pending patterns; no standalone spinner requirement.
- Error: field-associated error plus form-level alert pattern.
- Empty: defer until a real data surface exists.
- Success: defer a generic toast system; use local confirmation semantics when a real action requires it.

The design should define semantic roles and announcements before adding visual decoration.

## 40. Modal/dialog decision

`DEFER`.

There is no current modal consumer, no dialog route and no business flow authorized for one. Implementing a dialog now would create focus-trap, escape, scroll-lock and portal decisions without evidence.

## 41. Icon strategy

No icon dependency exists and no icon markup is currently present.

`RECOMMENDED`: defer icon package adoption. Use native text and semantic controls for the current technical pages. If a later scoped need appears, evaluate inline SVG or a package separately with accessibility and bundle evidence. Do not create an icon abstraction in SPEC-002 without a consumer.

## 42. Media/image primitive decision

No current media consumer exists and no image assets are available.

`RECOMMENDED`: document aspect ratio, `object-fit`, responsive sizing and lazy-loading rules only. Defer a media component until an approved real asset and real content consumer exist. Do not add placeholders that resemble commercial salon imagery.

## 43. CSS/SFC styling strategy

Recommended balance:

- Keep Tailwind utilities for local layout and simple state composition.
- Put repeated visual decisions in semantic tokens and reusable components.
- Use scoped CSS only for component-specific behavior that cannot be expressed clearly through existing utilities/tokens.
- Keep custom global rules in `app.css` small and layered: theme/tokens, minimal base/accessibility rules, then narrowly justified component utilities.
- Do not create a large CSS framework inside the application.
- Do not generate dynamic Tailwind class names from component props; use static variant maps.

The current one-file CSS entry point is sufficient. No CSS file split is justified by present scope.

## 44. Dependency audit

Direct frontend dependencies observed:

- Vue `3.5.42`.
- Vue Router `4.6.4`.
- Tailwind CSS `4.3.3`.
- `@tailwindcss/vite` `4.3.3`.
- Vite `8.2.2`.
- Vue plugin `6.0.8`.
- Vitest `4.1.11`.
- Vue Test Utils `2.5.0`.
- Vue TSC `3.3.11`.
- ESLint `10.10.0` and Vue/TypeScript ESLint tooling.
- `jsdom` `26.1.0`.

Direct Composer dependencies remain the SPEC-001 Laravel/Sanctum/Foundation baseline. No UI or font package is present.

`npm ls --depth=0` reports `@laravel/multiplex` as an unmet optional dependency. It is not used by the current scripts or frontend source and is not a SPEC-002 requirement.

## 45. New dependency recommendation

Runtime dependency additions: `NONE`.

Development dependency additions: `NONE REQUIRED` based on current consumers and test strategy.

An axe-based testing package is `RECOMMENDED` only if the team wants automated rule checks for the component surface after implementation; it is not required to complete Discovery and must not be installed without a separate approval. Existing jsdom tests plus manual accessibility review are sufficient for the initial scope, with browser tooling deferred.

No Composer dependency, font package, icon package, animation package or UI framework is justified.

## 46. UI framework assessment

`No external UI framework required`.

Evidence:

- The current UI has four technical pages and no component library needs.
- Tailwind CSS 4 already provides the approved styling foundation.
- A small local component layer is enough for Button, FormField/Input, Container and Card/Section.
- Adding Vuetify, PrimeVue, Quasar, Bootstrap UI or Element Plus would introduce visual and dependency decisions outside the approved scope.

Adopting a UI framework would be an architectural decision requiring an ADR and human review. It is rejected for this Discovery.

## 47. Frontend testing baseline

- Vitest runs in `jsdom` and includes `resources/js/**/*.test.ts`.
- Vue Test Utils is installed and used for the root app test.
- Existing tests cover root mounting, router metadata/resolution, HTTP response/error normalization and auth state transitions.
- No page or layout component test currently exists.
- `tsconfig.json` includes all Vue and TypeScript source under `resources/js` with strict mode.
- ESLint flat configuration includes Vue and TypeScript recommended rules.
- No accessibility-specific test utility is installed.
- No browser layout or visual regression test exists.

## 48. Component testing strategy

For future implementation:

- Test component props/variants through rendered classes or semantic attributes, not implementation internals.
- Assert user-visible text, disabled/loading state and native element semantics.
- Test `label`/control association, error id/described-by behavior and alert/status roles.
- Exercise keyboard-relevant events in jsdom where behavior is represented by the component.
- Test public/admin layout slot rendering and route integration.
- Keep snapshots small and local if used; do not snapshot full pages or compiled CSS.
- Retain existing Foundation tests unchanged and add focused tests only for newly created components.

## 49. Accessibility testing tooling assessment

- Required: targeted Vue Test Utils/jsdom assertions for semantic markup, labels, roles and state attributes.
- Recommended: manual keyboard/focus review and contrast calculation for final tokens.
- Recommended but not required now: axe-based automated checks once the component set exists.
- Deferred: browser accessibility automation and Playwright, consistent with SPEC-002 scope and the existing testing plan.

No accessibility dependency was installed.

## 50. Responsive testing strategy

Use a review matrix for small phone, large phone, tablet, desktop and wide desktop:

- Inspect layout at representative widths in browser developer tools during implementation.
- Run static audits for hardcoded widths, overflow risks, responsive utility usage and long-label behavior.
- Use focused component tests for structural class/variant behavior, not layout measurements.
- Run the production build and inspect generated CSS for successful token/class generation.
- Document that jsdom does not validate actual CSS layout, viewport overflow or font rendering.

No Playwright or other browser automation is required for this SPEC.

## 51. Existing CI impact

`.github/workflows/quality.yml` already runs:

- Composer validation and audit.
- Pint and Larastan.
- Pest backend tests.
- npm install/audit.
- ESLint.
- TypeScript typecheck.
- Vitest.
- Vite production build.

The workflow triggers on `main`, `feat/**` and pull requests, but not `docs/**` branches. SPEC-002 implementation should not require CI changes; the existing commands cover the frontend quality gates. A future implementation branch or PR may still run checks according to the repository's configured event policy.

## 52. Security implications

- External font providers would introduce third-party requests, CSP and privacy implications; no provider is approved.
- No tracker, analytics script, third-party JavaScript or remote image source is present.
- Auth cookies, CSRF, Sanctum and browser storage behavior must remain untouched.
- Technical showcase data must remain static and non-sensitive.
- Font licenses and license notices must be preserved if assets are later self-hosted.
- No secrets, credentials or provider keys are needed.
- Native form semantics and error text must not expose authentication details.

## 53. Performance considerations

- Keep font families and weights minimal; do not load every available weight.
- Prefer WOFF2 or an approved optimized web format if the eventual distribution path provides it; no binary was added or verified locally.
- Use `font-display` deliberately to avoid invisible text and excessive layout shift. MDN documents `swap`, `fallback` and `optional` as available strategies.
- Avoid preload until actual font URLs, usage and critical rendering impact are known.
- Keep token CSS small and avoid generating broad speculative utility namespaces.
- Reuse components without creating wrapper layers for every utility.
- No real images exist; defer image payload and lazy-loading choices until assets are approved.
- Do not benchmark artificially during Discovery.

## 54. Technical showcase assessment

Options:

| Option | Value | Risk |
| --- | --- | --- |
| A. Temporary dedicated route | Isolates component evidence | Adds a route and removal/visibility decision without a current consumer. |
| B. Existing `/` route | Uses an existing technical surface and avoids route proliferation | Technical showcase may be visible publicly and must remain clearly non-business. |
| C. No dedicated showcase | Keeps scope smallest; tests and current routes provide evidence | Less convenient for manual component review. |

## 55. Recommended showcase strategy

`RECOMMENDED: C, no dedicated showcase route.`

Use focused component tests and the existing technical pages as consumers. Restyle `/` only as the technical public shell defined by SPEC-002. If manual review later proves a showcase necessary, prefer a clearly technical treatment of the existing `/` route over adding a new public route, and keep all copy neutral and removable.

This minimizes public technical leakage and avoids a temporary navigation surface. It does not authorize any route or page change now.

## 56. Acceptance Criteria impact

The 17 SPEC-002 criteria remain valid and are not modified by Discovery. Readiness impact:

- Criteria 1-2: technically feasible with the existing Tailwind 4 setup; implementation pending.
- Criterion 3: pending the font loading/distribution decision and fallback acceptance.
- Criteria 4-8: feasible with a local component/token layer; implementation pending.
- Criterion 9: applies to the four technical routes only; no business page is required.
- Criterion 10: confirmed as a hard scope boundary.
- Criteria 11-12: require responsive testing and font/logo fallback evidence; implementation pending.
- Criterion 13: supported by Vitest/Vue Test Utils; no snapshot-heavy strategy.
- Criterion 14: existing Foundation gates can run unchanged.
- Criterion 15: no external provider or auth change is required.
- Criterion 16: requires a future implementation report and green remote CI.
- Criterion 17: human acceptance remains required before development status.

No criterion requires an invented logo, unverified provider, invented imagery or business data.

## 57. Recommended SPEC changes

`RECOMMENDED SPEC CHANGE` for a later review, not applied during Discovery:

- Preserve `READY FOR DISCOVERY`; do not transition the SPEC automatically.
- Add the Discovery recommendation that no dedicated showcase route is required unless later evidence changes that conclusion.
- Add the exact contrast findings or a link to this report if the project wants brand-role usage to be traceable from the SPEC.
- Clarify that an axe-based test is recommended/deferred, not a mandatory new dependency.
- Keep font and logo items as development-approval blockers with precise scope impact, not Discovery blockers.

No SPEC change was made during this task.

## 58. ADR assessment

No ADR was created.

No current finding changes the modular monolith, Laravel/Vue boundary, API, authentication, persistence or queue architecture. CSS-first tokens in the existing `app.css` are a reversible refinement of the approved frontend foundation.

Potential future ADR only if evidence leads to a durable choice:

- Mandatory external font provider or organization-wide self-hosting policy.
- Third-party UI/component system adoption.
- Shared token package across multiple applications/repositories.

Font distribution can remain a documented implementation decision if self-hosting is selected without creating a cross-application architecture.

## 59. Proposed implementation checkpoints

### Checkpoint A - Token and typography foundation

- Objective: establish the CSS-first token layers and approved typography/fallback strategy.
- Scope: primitives, semantic roles, type roles, focus/motion baseline and minimal global rules.
- Dependencies: approved font distribution decision or explicit fallback exception; approved logo boundary; contrast review.
- Acceptance evidence: compiled token utilities exist, semantic roles are used by at least one technical surface, required contrast pairs pass, fallback behavior is documented.
- Stop condition: stop before adding components if font licensing/distribution or token role contrast remains unresolved.

### Checkpoint B - Core UI primitives

- Objective: create only components with current consumers.
- Scope: Button, FormField/Input, Container and one surface primitive (`Card` or `Section`) with tested states.
- Dependencies: Checkpoint A and existing Vue/Test Utils conventions.
- Acceptance evidence: focused tests cover variants, labels, states and semantic output; no speculative components are added.
- Stop condition: stop if a component has no actual technical consumer or requires business semantics.

### Checkpoint C - Technical layouts and pages

- Objective: apply the approved system to existing technical surfaces.
- Scope: public/admin layouts, `/`, `/admin/login`, `/admin` and NotFound presentation only.
- Dependencies: Checkpoints A-B; existing auth/router behavior remains unchanged.
- Acceptance evidence: routes still resolve and auth flows remain behaviorally equivalent; no business content appears.
- Stop condition: stop before adding navigation, dashboard modules or business placeholders.

### Checkpoint D - Accessibility and responsive hardening

- Objective: verify keyboard, focus, semantics, contrast, reduced motion and responsive behavior.
- Scope: targeted markup corrections, focus/error/loading states and manual viewport matrix.
- Dependencies: Checkpoint C and approved semantic roles.
- Acceptance evidence: accessibility review, contrast table, reduced-motion review and viewport matrix are documented.
- Stop condition: stop if a required fix would alter auth/API behavior or introduce an unapproved dependency.

### Checkpoint E - Tests, documentation and final audit

- Objective: close the implementation with evidence and regression safety.
- Scope: component tests, existing quality gates, security/performance review, report and CI verification.
- Dependencies: Checkpoint D and all development-approval blockers resolved or explicitly accepted.
- Acceptance evidence: all SPEC criteria and DoD items audited, GitHub Actions green, human acceptance recorded.
- Stop condition: do not mark SPEC-002 complete or start another SPEC without explicit acceptance.

## 60. Scope-size assessment

`APPROPRIATELY SIZED WITH CONTROLLED REDUCTION`.

The current scope is appropriate if limited to tokens, typography strategy, a handful of primitives, four technical surfaces and their accessibility/responsive baseline. It becomes too large if it adds a full public landing page, dashboard navigation, modal system, data components, image library, icon package or business content.

No SPEC-003 is needed or proposed.

## 61. Development-approval blockers

### Blocker 1: font distribution strategy

- Classification: `BLOCKER` for full development approval of typography-complete SPEC-002.
- Scope: blocks the typography portion of Checkpoint A and final typography acceptance; it does not block Discovery or token/component planning with documented fallbacks.
- Required resolution: confirm license/assets and choose approved self-hosting, approved external provider or explicit fallback-only implementation.

### Blocker 2: official logo asset

- Classification: `NON-BLOCKER` for the overall implementation; `BLOCKER` only for logo-dependent criteria.
- Scope: does not block Discovery, token work, components or technical layouts using text/neutral placeholder; blocks official-logo fidelity acceptance.
- Required resolution: verify original asset or explicitly accept that logo-dependent criteria are deferred.

### No other development blocker found

- No backend/API/database work is required.
- No new package is required.
- No UI framework decision is pending.
- No CI change is required.

## 62. Non-blocking decisions

- Exact token names and component prop names.
- Whether a `Card` or `Section` primitive absorbs the current surface pattern.
- Exact neutral technical copy.
- Admin dark-first versus restrained light surface treatment.
- Custom breakpoint need; default Tailwind breakpoints remain preferred.
- Whether manual review later benefits from using `/` as a technical showcase.
- Whether axe-based checks are added after the component set exists.
- Icon strategy, modal/dialog and media primitive remain deferred until a real consumer exists.

## 63. Implementation readiness assessment

```text
Discovery: COMPLETED
SPEC-002: READY FOR DISCOVERY (awaiting Discovery review)
Implementation: NOT AUTHORIZED
Full implementation readiness: NOT READY until font strategy is resolved or explicitly accepted as fallback-only and logo-dependent criteria are bounded
Runtime dependency additions: NONE RECOMMENDED
Backend/API/database changes: NONE
```

Discovery produced evidence and recommendations but did not approve development or change the SPEC status.

## 64. Files created

- `docs/reports/SPEC-002-DISCOVERY-REPORT.md`.

## 65. Files modified

None.

## 66. Application-code changes

None.

No Vue, CSS, Blade, Laravel, route, test, package, lockfile, workflow or asset file was modified.

## 67. Git status

At report creation, the only intended change is the new Discovery report on `docs/spec-002-discovery`. The branch remains separate from `main` and the definition branch.

## 68. Commit status

No Discovery commit has been created yet at report creation time. The required next repository operation is one reviewed commit with message:

```text
docs: complete SPEC-002 technical discovery
```

## 69. Push status

No Discovery push has been performed yet at report creation time. The intended destination after review is:

```text
origin/docs/spec-002-discovery
```

No merge, PR, branch deletion or push to `main` is authorized by this task.

## 70. Recommended next action

Review this Discovery report, resolve the development-approval decisions, and then determine whether SPEC-002 may transition to `READY` / `APPROVED FOR DEVELOPMENT`.

Do not modify application code, install dependencies, add fonts, create components, change CSS, modify SPEC-002, create an ADR or start implementation until separately authorized.
