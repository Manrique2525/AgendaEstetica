# SPEC-002 - UX and Design System Foundation

## SPEC ID

`SPEC-002`

## Title

UX and Design System Foundation

## Status

`READY FOR DISCOVERY`

Esta SPEC tiene el alcance aprobado para Discovery. No autoriza implementación, instalación de dependencias ni cambios en la aplicación.

## Owner / Approval Model

- Owner: pendiente de asignación formal; la definición queda bajo revisión del proyecto.
- Approval model: aceptación explícita del responsable del proyecto después de revisar alcance, decisiones abiertas, riesgos y Acceptance Criteria.
- El estado `READY FOR DISCOVERY` permite únicamente Discovery autorizado explícitamente; no equivale a `APPROVED FOR DEVELOPMENT`.

## Objective

Definir una base visual y de experiencia reutilizable para la SPA Vue existente, alineada con la identidad confirmada de Salón y Barbería Yaris, sin anticipar módulos de negocio.

La SPEC debe establecer tokens, tipografía, reglas responsive, accesibilidad, estados y un conjunto pequeño de componentes base que permita construir posteriormente superficies públicas y administrativas sin duplicar estilos ni introducir una librería UI externa.

## Context

SPEC-001 está `CLOSED` y dejó una SPA Vue 3 con TypeScript, Vite 8, Tailwind CSS 4 y Vue Router. La aplicación contiene únicamente rutas técnicas y autenticación administrativa mínima. El frontend actual usa estilos neutros de demostración y `resources/css/app.css` solo define la importación de Tailwind y un tema sans inicial.

El roadmap histórico coloca `UX y design system` antes de `Foundation técnica`. Foundation se implementó y cerró como SPEC-001 por decisión del proyecto. No se reescribe esa historia; después del cierre de Foundation, UX y design system es el siguiente bloque lógico porque puede definir la capa visual reutilizable antes de implementar business core, agenda, booking, CMS o ecommerce.

## Problem Being Solved

- No existe una definición centralizada de colores, tipografías, estados de foco, espaciado visual y movimiento.
- Los layouts y páginas técnicas actuales usan clases neutras repetidas y no expresan la identidad Yaris.
- No hay una frontera documentada entre primitives visuales reutilizables y páginas funcionales de negocio.
- La futura implementación podría introducir valores hexadecimales, componentes o reglas responsive duplicadas.
- La experiencia pública y la administrativa necesitan compartir fundamentos, pero no necesariamente el mismo layout o densidad visual.

## Dependencies

### Normative dependencies

- `AGENTS.md`.
- `docs/context/PROJECT_CONTEXT.md`.
- `docs/context/BUSINESS_CONTEXT.md`.
- `docs/MASTER_TECHNICAL_SPEC.md`.
- `docs/architecture/ARCHITECTURE.md`.
- `docs/domain/DOMAIN_RULES.md`.
- `docs/testing/TEST_PLAN.md`.
- `docs/specs/SPEC-001-project-foundation.md`.
- `docs/reports/SPEC-001-IMPLEMENTATION-REPORT.md`.
- ADR-001 Laravel 13.
- ADR-002 MySQL 8.4 LTS.

### Technical baseline from SPEC-001

SPEC-002 consumes, but does not reimplement, the following baseline:

- Laravel 13.
- PHP 8.3+.
- MySQL 8.4 LTS.
- Vue 3.5.
- TypeScript 5.9.
- Vite 8.
- Tailwind CSS 4.
- Vue Router 4.
- Sanctum SPA authentication.
- API `/api/v1`.
- Database Queue and scheduler foundation.
- Private-default Storage.
- Pest, Larastan, Pint, Vitest, Vue Test Utils and ESLint.
- GitHub Actions quality CI.

### External dependencies

- No new runtime or UI package is approved by this draft.
- Existing browser and CSS capabilities are preferred.
- Brand font files, licenses and logo assets are not confirmed in the repository and must be investigated before implementation.
- No image provider, CDN, font provider or design asset vendor is required by this SPEC.

### Technical placeholder rule

Mientras el logo oficial no esté disponible, Discovery y cualquier trabajo técnico independiente del logo pueden utilizar únicamente un placeholder técnico neutral o el nombre de marca en texto plano cuando sea estrictamente necesario. No se permite crear, redibujar, trazar, modificar ni presentar como oficial un logo ficticio. Cualquier criterio de aceptación visual que dependa del logo oficial debe esperar el asset original aprobado.

## Inputs from SPEC-001

- Vue SPA entry point, layouts, router and technical pages already exist.
- `resources/css/app.css` is the Tailwind CSS 4 integration point.
- `PublicLayout` and `AdminLayout` are separate surfaces that currently share a minimal structural pattern.
- Existing technical routes are `/`, `/admin/login`, `/admin` and the not-found route.
- Sanctum and authentication behavior must remain unchanged; this SPEC only considers presentation of the login and authenticated technical shell.
- No customers, services, professionals, appointments, products, orders, CMS or WhatsApp domain has been implemented.

## Scope

### In scope

- Define the Yaris visual direction and a small CSS-first token vocabulary compatible with Tailwind CSS 4.
- Define semantic color roles, typography roles, spacing guidance, radii, shadows, focus states and motion rules.
- Define integration rules for the confirmed type families without installing font packages or inventing font assets.
- Define the minimum reusable component set and classify deferred components.
- Define shared public/admin foundations while allowing separate layouts and density.
- Define mobile-first behavior for small phone, large phone, tablet and desktop ranges without hardcoding commercial device names.
- Define semantic and keyboard-accessible interaction requirements.
- Define presentation-only treatment for the existing technical pages.
- Define a technical showcase or equivalent verification surface only if it is needed to prove variants; it must not become a business page.
- Define testing, review, documentation and CI expectations for a future implementation.

### Required component candidates

The following components are required for implementation only when their variants are proven useful by the technical pages or showcase:

- `Container`.
- `Section`.
- `Button` with default, secondary, quiet, disabled and loading states.
- `FormField`.
- `Input`.
- `Textarea` only if the showcase or existing technical flows need it.
- `Card`.
- `Badge` only if a meaningful technical state needs it.
- Loading, empty and error states where the existing technical flows can demonstrate them.

### Deferred component candidates

The following remain deferred until a real flow requires them:

- `Select`.
- `Checkbox`.
- `Radio`.
- `Modal`/`Dialog`.
- `Spinner` as a standalone component if an inline loading treatment is sufficient.
- Navigation menus, tables, date pickers, calendars, tabs, drawers and data visualization.

No deferred component is to be created speculatively.

## Out of Scope

- Functional Store, Services, Appointments, Checkout, CMS, Gallery, WhatsApp, Orders or business dashboards.
- Business models, database migrations, API endpoints, backend services or domain rules.
- Changes to Sanctum, login/logout behavior, authorization or API contracts.
- Customer, professional, service, product, price, promotion or testimonial data.
- Real commercial images, logo files or social/contact details that are not confirmed.
- A full landing page or navigation architecture for future business modules.
- A generic design system library intended for unrelated products.
- Vuetify, PrimeVue, Quasar, Bootstrap UI, Element Plus or another external UI framework.
- Pinia, Axios, animation libraries, icon libraries or font packages unless a future approved decision demonstrates a concrete need.
- Playwright, browser E2E, pixel-diff testing, snapshot-heavy testing, coverage enforcement or mutation testing.
- Production deployment, hosting, CDN, CMS, asset pipeline or external font provider setup.
- Reordering the historical roadmap or rewriting SPEC-001 history.
- SPEC-003 or any subsequent SPEC.

## Business Rules

This is a presentation and UX foundation SPEC, so no new business rule or domain state is introduced.

The following project rules still constrain the design:

- Do not present invented services, people, prices, products, promotions or testimonials as real content.
- Professionals remain resources, not administrative users; no visual design may imply a customer or professional account flow that is not approved.
- Backend remains authoritative for authentication, authorization, validation and business state.
- Technical placeholders must be clearly labeled as technical or pending and must not resemble production business content.

## Brand Foundation

Only confirmed brand information may be used:

- Brand: `Salón y Barbería Yaris`.
- Slogan: `Belleza y elegancia`.
- Black: `#050505`.
- Fuchsia: `#D01772`.
- Pink: `#CB6CA5`.
- Turquoise: `#3AC4D7`.
- Soft Gold: `#F5CC7A`.
- White.

The implementation must expose these through semantic tokens rather than scattering raw brand hex values across components. Additional neutral or state colors must be classified as technical tokens and must not be presented as confirmed brand colors.

## Typography

Confirmed type direction:

- `Cormorant Garamond Bold` for titles.
- `Montserrat SemiBold` for navigation and subtitles.
- `Poppins Regular` for body text.
- `Montserrat Bold` for buttons, prices and promotions.

The implementation must decide the loading strategy before coding:

- Prefer self-hosted, licensed font files if the project actually possesses the assets and licenses.
- Do not add remote font loading by assumption.
- If the assets or licensing are unavailable, use documented fallback stacks while the decision remains open.
- Use `font-display` appropriate to the selected strategy and avoid blocking first render.
- Keep privacy and network behavior explicit if a provider is ever proposed.

Fallback stacks must preserve readable contrast, wrapping and hierarchy. No font package is installed as part of SPEC definition.

## Visual Direction

The visual language should be:

- Elegant.
- Modern.
- Minimal.
- Beauty/feminine primary.
- Barbering secondary.

Guidance:

- Use predominantly black backgrounds where the surface benefits from them.
- Preserve high white contrast for readable content.
- Use fuchsia as the primary call-to-action emphasis.
- Use turquoise and soft gold sparingly as accents or supporting states.
- Use large imagery only when real approved assets exist.
- Avoid visual saturation, excessive gradients, excessive glow and excessive animation.
- Avoid generic dashboard styling for the public experience.
- Keep the admin surface more operational and restrained while sharing tokens with the public surface.

## Design Token Strategy

The proposed token strategy is CSS-first and compatible with Tailwind CSS 4:

- Define semantic custom properties in the CSS integration point.
- Expose color roles such as background, surface, foreground, muted, primary, accent, success, warning, danger and focus.
- Map confirmed brand values to roles without making every brand color a component-specific utility.
- Define only spacing, radii and shadows that demonstrate repeated use; do not build a speculative scale.
- Keep component variants expressed through semantic roles rather than raw hex values.
- Prefer default Tailwind breakpoints unless a concrete layout requirement proves a custom breakpoint necessary.
- Document any token that is technical-neutral rather than confirmed brand identity.

The implementation must not revert to a Tailwind CSS 3 configuration model or create a second token source.

## UX Requirements

- Public and admin layouts share foundational tokens but may use different information density and navigation patterns.
- Interactive states must be visually distinguishable for default, hover, focus-visible, active, disabled, loading, success, warning, error and empty conditions where applicable.
- A disabled control must not be confused with a loading control.
- Error messages must remain close to the affected control or region and use semantic markup.
- Content hierarchy must remain understandable without relying on color alone.
- Technical placeholder content must be concise and clearly non-business.
- The visual system must not imply unsupported flows such as customer registration, checkout or appointment confirmation.
- Copy introduced for a technical showcase must be in Spanish or explicitly labeled as technical content; it must not invent business claims.

## Frontend Requirements

- Use Vue 3 SFCs and TypeScript consistent with SPEC-001.
- Keep components presentational and reusable; do not move business rules into them.
- Keep auth composables, HTTP services and route guards unchanged unless a presentation-only integration requires a non-behavioral prop or slot.
- Centralize design tokens in the existing Tailwind CSS 4 entry point.
- Avoid raw brand values outside token definitions.
- Use semantic HTML and explicit props/events for component variants.
- Keep public and admin layouts separate where their structure differs.
- Do not introduce a global state library for visual state.

## Existing Technical Pages

| Surface | SPEC-002 treatment | Boundary |
| --- | --- | --- |
| `/` | Restyle the existing technical foundation page or replace it with a clearly technical showcase if needed | No landing page, business content or marketing claims |
| `/admin/login` | Restyle presentation and form states | Keep Sanctum/auth logic, validation and navigation behavior unchanged |
| `/admin` | Restyle the authenticated technical state | No dashboard modules, metrics or business actions |
| NotFound | Restyle the existing technical error surface | Preserve router behavior and accessible error communication |

The exact showcase composition is an implementation decision within this scope, not authorization to add business pages.

## Backend Requirements

No backend implementation is expected.

- No controllers, actions, requests, resources, middleware, auth logic or API contracts change.
- No business validation or rules are added.
- If a technical page needs static presentation data, it must remain local to the frontend and not create an API.

## Database Impact

Not applicable for this SPEC. No migrations, models, seeders, tables, indexes or database settings are permitted.

## API Impact

Not applicable for this SPEC. Existing `/api/v1`, Sanctum and authentication contracts remain unchanged. No endpoint is added, removed or versioned.

## Security Considerations

- Do not add secrets, external provider keys, remote asset credentials or tracking code.
- Do not change cookie, CSRF, Sanctum, CORS, session or authorization behavior.
- Do not load fonts, images or scripts from a third party without an approved privacy, performance and licensing decision.
- Preserve semantic labels and error handling without exposing authentication details.
- Do not place user or business data into static showcase markup.
- Preserve the existing frontend rule that authentication tokens are not stored in browser storage.

## Accessibility Requirements

The implementation must cover, at minimum:

- Semantic HTML elements for landmarks, headings, navigation, forms and status regions.
- Programmatic label associations for every form control.
- Keyboard access to every interactive control.
- Clearly visible `:focus-visible` styles that meet the project contrast target.
- Sufficient text and control contrast for the selected surfaces; no unverified WCAG certification claim.
- Color is not the sole carrier of state or meaning.
- Loading and error states are announced or exposed through appropriate status semantics where needed.
- Reduced-motion preference disables or minimizes non-essential transitions.
- Interactive targets have practical minimum size and spacing, especially on touch layouts.
- Text remains readable when zoomed and when it wraps.

## Responsive Requirements

The system is mobile-first and must be reviewed at these conceptual ranges:

- Small phone: single-column layout, no horizontal scrolling, touch-friendly controls.
- Large phone: preserve hierarchy while allowing modest two-column groupings only when content supports it.
- Tablet: use available width for composition without forcing desktop density.
- Desktop: support wider content containers, balanced whitespace and distinct public/admin density.

No commercial device names are required. Responsive behavior must be validated from layout rules and representative viewport checks during implementation.

## Motion

- Motion is subtle, purposeful and short-duration.
- Motion must communicate state or hierarchy, not decorate every interaction.
- `prefers-reduced-motion: reduce` must disable non-essential transitions and animations.
- No animation library is needed for the defined scope.

## Image Strategy

No real images are selected in this SPEC. The implementation may define:

- image aspect-ratio tokens or utilities only when repeated use is demonstrated;
- `object-fit` and object-position behavior;
- responsive image sizing and lazy-loading expectations;
- neutral technical placeholders clearly marked as placeholders.

It must not invent salon photographs, staff portraits, product images or testimonials.

## Testing Strategy

Future implementation tests must focus on useful behavior rather than visual snapshots:

- Component variants render their intended semantic elements and states.
- Buttons expose disabled and loading behavior without inventing business actions.
- Form controls have programmatic labels and accessible error associations where applicable.
- Layout and router integration preserve the existing technical routes.
- Public and admin layouts render their intended surface boundaries.
- Reduced-motion styles and focus-visible rules are present in the compiled CSS or are covered by targeted component/style checks where practical.
- Responsive CSS is validated through build/static checks and focused viewport review, not pixel-perfect snapshots.
- Existing Foundation frontend tests remain green.
- Playwright and browser E2E remain outside this SPEC.

No snapshot suite covering entire pages is required.

## Quality Gates

The implementation must inherit the Foundation quality gates:

```text
composer validate --strict
composer audit
vendor/bin/pint --test
vendor/bin/phpstan analyse
php artisan test
npm run lint
npm run typecheck
npm run test
npm run build
npm audit
GitHub Actions backend and frontend jobs
```

PHP gates must remain green even if the implementation changes only frontend presentation. No CI workflow changes are authorized by this draft.

## Edge Cases

- Long Spanish labels must wrap without breaking controls or causing horizontal scrolling.
- Empty, error and loading states must remain readable on dark and light surfaces.
- Focus indicators must remain visible over black, white, fuchsia, turquoise and gold surfaces.
- Disabled controls must retain sufficient contrast while communicating non-interactivity.
- The login error must not expose whether an email exists or reveal backend internals.
- A missing font asset must fall back without collapsing the visual hierarchy.
- A missing image must preserve the intended aspect ratio without showing invented content.
- `prefers-reduced-motion` must not leave content inaccessible or dependent on animation completion.
- Not-found and technical states must remain understandable without business navigation.

## Risks

### R-201: Brand assets are incomplete

The confirmed color and font direction exists, but logo files, font files, licenses and approved photography are not confirmed. Mitigation: keep assets as explicit discovery inputs and use technical placeholders only.

### R-202: Font loading choice affects privacy and performance

Self-hosting, provider loading and system fallback have different licensing, privacy and rendering consequences. Mitigation: Discovery must produce evidence and a recommendation; the decision blocks development approval, not Discovery.

### R-203: Design-system scope grows into business UI

Component libraries can easily become speculative dashboards or booking flows. Mitigation: require every component to have a technical use case or approved reuse rationale and enforce the Out of Scope list.

### R-204: Public/admin divergence creates duplicate tokens

Different surface needs may cause duplicated colors and spacing. Mitigation: share semantic tokens and separate only layout/density rules that have a concrete reason.

### R-205: Accessibility is treated as visual polish only

Aesthetic changes can regress labels, focus, status semantics or reduced motion. Mitigation: include accessibility review and targeted tests in Acceptance Criteria and Definition of Done.

## Open Decisions

### A. Discovery decisions

1. **Font availability and loading strategy**
   - Status: `OPEN — DISCOVERY REQUIRED`.
   - Blocking stage: `APPROVED FOR DEVELOPMENT`.
   - Does not block: `DISCOVERY`.
   - Discovery must determine confirmed license, approved distribution source, self-hosting feasibility, external-provider implications, privacy, performance, `font-display` behavior, fallback stack, required weights and bundle/network impact for Cormorant Garamond, Montserrat and Poppins.
   - Discovery must not choose Google Fonts CDN, Fontsource or manual font binaries without evidence and approval.

2. **Official logo availability**
   - Status: `OPEN — ASSET VERIFICATION REQUIRED`.
   - Does not block: `DISCOVERY`.
   - May block: implementation and acceptance criteria that specifically require the official logo.
   - Discovery must verify whether an approved original logo exists, available formats, SVG/vector availability, PNG/raster availability, supplied light/dark variants, minimum usable resolution and brand-color fidelity.
   - Discovery must not redraw, generate, trace or modify a logo asset without authorization.

3. Confirm whether the technical showcase should be the `/` page or a non-public development-only route, without exposing a business page.
4. Confirm whether the admin surface should use the same dark-first treatment as the public surface or a quieter light operational variant.
5. Confirm whether a custom breakpoint is actually needed after layout prototypes; default Tailwind breakpoints are preferred.
6. Confirm exact Spanish copy for technical empty/error/loading examples; this does not block Discovery if placeholders remain clearly technical.

### B. Development-approval blockers

1. Resolve the font strategy with Discovery evidence before changing this SPEC to `APPROVED FOR DEVELOPMENT` if typography implementation depends on supplied font assets or an external provider.
2. Resolve whether the official logo is available before approving logo-dependent implementation or acceptance criteria. If it is unavailable, approve the text-only or neutral-placeholder boundary explicitly.
3. Confirm that any durable provider, component-library or cross-application token decision is either rejected or documented through an approved ADR before implementation.

### C. Non-blocking implementation details

1. Exact token names, component prop names and file organization may be selected during approved implementation within this scope.
2. Exact neutral technical placeholder copy may be selected during implementation without inventing business claims.
3. Default Tailwind breakpoints remain preferred unless implementation evidence justifies a scoped custom breakpoint.

## External Dependencies

- Existing browser CSS support for custom properties, `:focus-visible`, media queries and reduced-motion preferences.
- Approved font assets and licenses, if self-hosting is selected.
- No external UI framework, animation package, icon package, font package or image provider is required by this draft.

## ADR Assessment

No ADR is created by this definition task.

Potential ADR candidates only if a later approved implementation makes a durable architectural choice:

- Mandatory external font provider or a project-wide self-hosting policy.
- Adoption of a component library or UI framework.
- A token architecture that must be shared across multiple applications or repositories.

CSS-first tokens within the existing Tailwind CSS 4 integration do not currently require an ADR because they refine the approved frontend foundation without changing the application architecture.

## Documentation Requirements

Future implementation must update:

- this SPEC with final decisions and Acceptance Criteria results;
- a SPEC-002 implementation report with files, commands, tests, accessibility review and responsive review;
- relevant frontend development documentation if a new token or component convention is introduced;
- architecture or ADR documentation only if a decision assessed as architectural is approved.

No documentation update should silently change the scope or mark this SPEC accepted without human approval.

## Acceptance Criteria

The following criteria are written for the future implementation and are not yet passed:

1. A CSS-first token set is centralized in the existing Tailwind CSS 4 integration point.
2. Confirmed brand colors are represented through semantic roles, and raw brand hex values are not duplicated across components.
3. Typography roles are represented with the approved families or an explicitly approved fallback strategy, with font loading behavior documented.
4. Public and admin layouts share the token foundation while preserving intentionally different layout/density rules.
5. The required component set has documented variants for default, focus, disabled and loading/error states where applicable.
6. Components use semantic HTML, explicit labels and keyboard-accessible interaction patterns.
7. Focus-visible styles are present and remain distinguishable across the defined surface colors.
8. Reduced-motion preferences suppress non-essential transitions and animations.
9. Existing technical routes `/`, `/admin/login`, `/admin` and NotFound receive the approved presentation treatment without changing auth, router or API behavior.
10. No business page, business endpoint, business model, migration or invented business data is introduced.
11. Small-phone, large-phone, tablet and desktop layouts do not require horizontal scrolling for the scoped technical surfaces.
12. Missing fonts and images degrade to documented fallbacks without breaking hierarchy or layout; unavailable official logos use only the approved neutral placeholder or plain-text rule, and logo-dependent acceptance waits for the original asset.
13. Component and layout tests cover useful variants and accessibility-related markup without a snapshot-heavy or pixel-diff strategy.
14. Existing Foundation backend and frontend quality gates pass unchanged.
15. Security review confirms no secrets, tracking code, unapproved remote assets or auth-storage changes were introduced.
16. Documentation and implementation report are complete, and remote GitHub Actions CI is green.
17. Human acceptance is recorded before changing this SPEC from `READY FOR DISCOVERY` to an implementation status.

## Definition of Done

- Scope implemented only after SPEC-002 approval.
- Every Acceptance Criterion is marked PASS or has an explicitly approved exception.
- Tokens, typography, component variants and layout boundaries are documented.
- Existing auth/API behavior remains unchanged.
- Backend tests, frontend tests, lint, typecheck, build, static analysis and audits pass.
- Accessibility review covers semantics, labels, keyboard focus, contrast, reduced motion and touch targets.
- Responsive review covers the four conceptual viewport ranges.
- Security review covers remote assets, secrets, tracking and auth storage.
- Implementation report is created and documentation is updated.
- GitHub Actions backend and frontend jobs are green.
- No business data, business module, SPEC-003 or unrelated dependency was introduced.
- Working tree is clean after the approved implementation workflow.
- Human acceptance is recorded, and all development-approval blockers are resolved or explicitly accepted.

## Implementation Boundaries

If this SPEC is approved for implementation later:

- Work must occur on a dedicated implementation branch created from updated `main`, not on this documentation branch and not directly on `main`.
- Allowed application changes are limited to the frontend styling, components, layouts and technical pages explicitly listed here.
- Do not change `app/`, `bootstrap/`, `config/`, `database/`, API routes, authentication logic, package manifests, lockfiles or CI unless a separate approved decision proves it necessary and the work is stopped for review.
- Do not install fonts, UI frameworks, animation libraries or icon packages without resolving the relevant open decision.
- Do not create future business routes, data or modules as showcase content.

## Discovery Requirements

Discovery must remain analysis-only and must review:

- Current Vue component tree.
- Existing Tailwind CSS 4 setup and `resources/css/app.css`.
- Existing technical layouts and pages.
- Current accessibility baseline, including labels, focus, status semantics and keyboard behavior.
- Current responsive behavior and representative viewport assumptions.
- Browser support assumptions for CSS custom properties, `:focus-visible` and reduced motion.
- Candidate components grounded in actual technical routes.
- Token architecture and semantic role naming.
- Availability, licensing and format of confirmed font assets.
- Logo availability and missing brand assets.
- Design assets actually available, without selecting commercial imagery.
- Any existing screenshots or references, if they are provided by the business.
- Whether technical pages should be restyled in place or use a bounded showcase.

Discovery must not implement code, install dependencies or create business content.

## Definition State

This file has scope approval for Discovery only. Discovery is not yet started or authorized by this document alone; implementation remains unauthorized.
