# SPEC-002 - UX and Design System Foundation

## SPEC ID

`SPEC-002`

## Title

UX and Design System Foundation

## Status

`APPROVED FOR DEVELOPMENT / IN PROGRESS`

Esta SPEC fue aprobada explícitamente para desarrollo y se encuentra en implementación por checkpoints. Solo el checkpoint autorizado actualmente puede ejecutarse; no autoriza el avance automático a Checkpoint B ni a checkpoints posteriores.

## Owner / Approval Model

- Owner: pendiente de asignación formal; la definición queda bajo revisión del proyecto.
- Approval model: aceptación explícita del responsable del proyecto después de revisar alcance, decisiones abiertas, riesgos y Acceptance Criteria.
- Checkpoint A: `COMPLETED`.
- Checkpoint B: `COMPLETED`.
- Checkpoint C y posteriores: `NOT AUTHORIZED`.

## Discovery Assessment

- SPEC-002 Technical Discovery: `COMPLETED`.
- Technical assessment: `PASS`.
- Implementation readiness: `CHECKPOINT B COMPLETED`.
- Checkpoint C and later implementation: `NOT AUTHORIZED`.

## Checkpoint B Evidence

- Delivered: `UiButton`, `UiInput`, `UiFormField`, `UiContainer` and `UiCard` with focused Vitest coverage.
- Deferred: `UiSection` and all speculative components.
- Evidence: frontend and backend quality gates pass; no pages, layouts, routes, auth logic or business functionality changed.
- Report: `docs/reports/SPEC-002-CHECKPOINT-B-REPORT.md`.

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

- No new runtime or UI package is required by the Discovery result.
- Existing browser and CSS capabilities are preferred.
- Font binaries are not yet in the repository; implementation must obtain only approved files from the official upstream project or authoritative Google Fonts distribution and preserve license notices.
- No remote font provider, image provider, CDN, font provider or design asset vendor is required or approved as a runtime dependency.

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
- Use the existing technical pages as consumers; no dedicated showcase route is required by default.
- Define testing, review, documentation and CI expectations for a future implementation.

### Required component candidates

The following components are required only when their variants are proven useful by the existing technical pages:

- `Button` with default, secondary, quiet, disabled and loading states.
- `FormField` and `Input` for `/admin/login`.
- `Container` or an equivalent shared layout convention.
- One surface primitive, `Section` or `Card`, selected from actual duplication evidence rather than implementing both speculatively.
- Loading, empty and error states where the existing technical flows can demonstrate them.

### Deferred component candidates

The following remain deferred until a real flow requires them:

- `Select`.
- `Textarea`.
- `Checkbox`.
- `Radio`.
- `Badge`.
- `Modal`/`Dialog`.
- `Spinner` as a standalone component if an inline loading treatment is sufficient.
- Navigation menus, tables, date pickers, calendars, tabs, drawers and data visualization.

No deferred component is to be created speculatively. The governing principle is `abstract after evidence, not before evidence`.

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

The approved token model has two conceptual layers:

1. Brand primitives: only the confirmed values above.
2. Semantic UI tokens: roles equivalent to `surface`, `surface-elevated`, `text-primary`, `text-secondary`, `border`, `action-primary`, `action-secondary`, `focus`, `error` and `success`.

Final token names may be adjusted during the first implementation checkpoint, but components must consume semantic roles rather than repeated brand hex values.

## Typography

Confirmed type direction:

- `Cormorant Garamond Bold` for titles.
- `Montserrat SemiBold` for navigation and subtitles.
- `Poppins Regular` for body text.
- `Montserrat Bold` for buttons, prices and promotions.

Discovery resolved the font distribution strategy as follows:

- Hosting: `SELF-HOSTED`.
- Runtime provider: Google Fonts CDN is rejected for the current architecture.
- Source policy: obtain binaries only from the official upstream project or authoritative Google Fonts distribution.
- Format preference: WOFF2 when the authoritative distribution provides it.
- Do not silently convert TTF/OTF to WOFF2; stop and document the decision if conversion becomes necessary.
- Preserve the corresponding SIL Open Font License 1.1 copyright/license notices and reserved font names.
- No font npm package or loader package is required.

Initial implementation weights are limited to:

- Cormorant Garamond: `700` Bold.
- Montserrat: `600` SemiBold and `700` Bold.
- Poppins: `400` Regular.

Do not load all weights, all italics or complete families unnecessarily. Use a non-blocking `font-display` strategy after implementation testing and retain documented fallback stacks.

Fallback stacks must preserve readable contrast, wrapping and hierarchy. Font binaries are not added during definition finalization.

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

The approved token strategy is CSS-first and compatible with Tailwind CSS 4:

- Use `@theme` for brand primitives and other tokens that should participate directly in Tailwind utilities.
- Use CSS custom properties and `:root` for semantic/runtime variables that do not need to generate utilities directly.
- Use an explicit `@theme inline` mapping only when a semantic role must be exposed as a utility.
- Expose color roles such as background, surface, foreground, muted, primary, accent, success, warning, danger and focus.
- Map confirmed brand values to roles without making every brand color a component-specific utility.
- Define only spacing, radii and shadows that demonstrate repeated use; do not build a speculative scale.
- Keep component variants expressed through semantic roles rather than raw hex values.
- Prefer default Tailwind breakpoints unless a concrete layout requirement proves a custom breakpoint necessary.
- Document any token that is technical-neutral rather than confirmed brand identity.

The implementation must not create `tailwind.config.js`, revert to a Tailwind CSS 3 configuration model or create a second token source unless extraordinary future evidence is documented and reviewed.

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

No dedicated showcase route is required by default. The existing `/` route remains a technical consumer and must not become a commercial landing page. Any later showcase exception must remain technical, removable and separately justified.

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

PHP gates must remain green even if the implementation changes only frontend presentation. Discovery found the current `quality.yml` sufficient; no CI workflow changes are required by SPEC-002 unless implementation evidence proves otherwise.

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

The official logo is not available in the repository and approved photography remains absent. Mitigation: keep logo-dependent acceptance asset-specific and use only text or neutral technical placeholders. Font licensing and hosting strategy were resolved by Discovery.

### R-202: Font loading choice affects privacy and performance

Self-hosting still requires the approved files, limited weights and preserved OFL notices. Mitigation: use the resolved self-hosted policy and stop before adding binaries if authoritative assets or licensing evidence are unavailable.

### R-203: Design-system scope grows into business UI

Component libraries can easily become speculative dashboards or booking flows. Mitigation: require every component to have a technical use case or approved reuse rationale and enforce the Out of Scope list.

### R-204: Public/admin divergence creates duplicate tokens

Different surface needs may cause duplicated colors and spacing. Mitigation: share semantic tokens and separate only layout/density rules that have a concrete reason.

### R-205: Accessibility is treated as visual polish only

Aesthetic changes can regress labels, focus, status semantics or reduced motion. Mitigation: include accessibility review and targeted tests in Acceptance Criteria and Definition of Done.

## Resolved Decisions

### Tailwind and token architecture

- `Tailwind CSS 4` with a CSS-first architecture is approved.
- Use `@theme` for brand primitives and tokens that must generate Tailwind utilities.
- Use CSS custom properties and `:root` for semantic/runtime values that do not need direct utility generation.
- Use `@theme inline` only for explicit semantic-to-utility mappings.
- Do not create `tailwind.config.js` unless extraordinary future evidence is documented and reviewed.

### Font hosting and loading

- Status: `RESOLVED`.
- Strategy: `SELF-HOSTED`.
- Google Fonts CDN is rejected as a runtime provider for this architecture.
- Approved source: official upstream project or authoritative Google Fonts distribution.
- Preferred format: WOFF2 when officially available.
- Initial weights: Cormorant Garamond 700; Montserrat 600 and 700; Poppins 400.
- Do not silently convert TTF/OTF to WOFF2; stop and document any required conversion.
- Preserve SIL Open Font License 1.1 notices and reserved names.
- No font npm package or loader package is approved by default.

### Logo availability and fallback

- Official logo in repository: `NOT AVAILABLE`.
- Status: `NON-BLOCKING FOR CORE IMPLEMENTATION`.
- Until an approved asset is supplied, use only `Salón y Barbería Yaris` as a text wordmark or a neutral technical placeholder when strictly necessary.
- Do not generate, draw, trace, recolor or use an AI replacement.
- Logo-specific criteria are `ASSET-DEPENDENT` and may remain pending without blocking the core Design System.

### Dependency and framework policy

- New npm runtime dependencies: `NONE`.
- New Composer dependencies: `NONE`.
- External UI framework: `NONE`.
- Animation library: `NONE`.
- Icon library: `NONE` unless a later real consumer justifies one.
- Axe-based accessibility checks: `RECOMMENDED` for later implementation review, `DEFERRED`, not required as a new dependency now.
- Modal/Dialog, standalone spinner, toast system and other speculative components: `DEFERRED`.

### Showcase and surface policy

- No dedicated showcase route is required by default.
- Existing `/` remains a technical surface and may be restyled without becoming a commercial landing page.
- Public and admin share one design system; they may differ in layout, density, navigation and content hierarchy.

## Pending Asset-Specific Items

- Official logo asset and any logo-dependent acceptance evidence remain pending.
- Exact font binaries and deployment asset path remain implementation inputs; the hosting, source, weight and licensing policy is resolved.
- Exact token names, component prop names, file organization, neutral technical copy and any custom breakpoint remain non-blocking implementation details.

## Implementation Readiness

- Discovery: `COMPLETED`.
- Architecture decisions: `SUFFICIENT`.
- Entire-SPEC implementation blockers: `NONE`.
- Official logo: pending, non-blocking for core implementation and asset-specific acceptance only.
- Font strategy: `RESOLVED`.
- New runtime dependencies: `NONE`.
- Backend/API/database changes: `NONE`.
- Readiness: `READY FOR HUMAN DEVELOPMENT APPROVAL`.

## External Dependencies

- Existing browser CSS support for custom properties, `:focus-visible`, media queries and reduced-motion preferences.
- Approved font assets and licenses under the resolved self-hosting policy are implementation inputs.
- No external font provider, UI framework, animation package, icon package, font package or image provider is required.

## ADR Assessment

No ADR is created by this definition or Discovery finalization. The approved CSS-first token strategy and self-hosted font policy do not change application architecture.

Potential ADR candidates only if a later approved implementation makes a durable architectural choice:

- A materially different font hosting policy that introduces a mandatory external provider.
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
12. Missing fonts and images degrade to documented fallbacks without breaking hierarchy or layout; unavailable official logos use only the approved neutral placeholder or plain-text rule, and any official-logo fidelity criterion is explicitly `ASSET-DEPENDENT` rather than blocking the core system.
13. Component and layout tests cover useful variants and accessibility-related markup without a snapshot-heavy or pixel-diff strategy.
14. Existing Foundation backend and frontend quality gates pass unchanged.
15. Security review confirms no secrets, tracking code, unapproved remote assets or auth-storage changes were introduced.
16. Documentation and implementation report are complete, and remote GitHub Actions CI is green.
17. Human acceptance is recorded before changing this SPEC from an implementation status to an accepted/closed status.

## Definition of Done

- Scope implemented only after SPEC-002 approval.
- Every Acceptance Criterion is marked PASS or has an explicitly approved exception.
- Tokens, typography, component variants and layout boundaries are documented.
- Existing auth/API behavior remains unchanged.
- Backend tests, frontend tests, lint, typecheck, build, static analysis and audits pass.
- Accessibility review covers semantics, labels, keyboard focus, contrast, reduced motion and touch targets.
- Responsive review covers the four conceptual viewport ranges.
- Security review covers remote assets, secrets, tracking and auth storage.
- Font assets, limited weights and OFL notices follow the approved self-hosting policy.
- Implementation report is created and documentation is updated.
- GitHub Actions backend and frontend jobs are green.
- No business data, business module, SPEC-003 or unrelated dependency was introduced.
- Working tree is clean after the approved implementation workflow.
- Human acceptance is recorded; the official logo may remain pending as an asset-specific acceptance item without blocking the core Design System.

## Proposed Implementation Checkpoints

### Checkpoint A - Token and typography foundation

- Objective: establish the CSS-first token layers and approved typography/fallback strategy.
- Scope: brand primitives, semantic roles, type roles, focus/motion baseline and minimal global rules.
- Dependencies: authoritative font assets under the resolved self-hosting policy, approved logo boundary and contrast review.
- Acceptance evidence: compiled token utilities exist, semantic roles are used by a technical surface, required contrast pairs pass and fallback behavior is documented.
- Stop condition: stop before adding components if font assets/license evidence or token-role contrast remains unresolved.

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
- Dependencies: Checkpoint D and explicit human approval for development.
- Acceptance evidence: all SPEC criteria and DoD items audited, GitHub Actions green and human acceptance recorded.
- Stop condition: do not mark SPEC-002 complete or start another SPEC without explicit acceptance.

## Implementation Boundaries

If this SPEC is approved for implementation later:

- Work must occur on a dedicated implementation branch created from updated `main`, not on this documentation branch and not directly on `main`.
- Allowed application changes are limited to the frontend styling, components, layouts and technical pages explicitly listed here.
- Do not change `app/`, `bootstrap/`, `config/`, `database/`, API routes, authentication logic, package manifests, lockfiles or CI unless a separate approved decision proves it necessary and the work is stopped for review.
- Add only the approved font weights from the official source policy, preserve OFL notices and stop if the authoritative files or licensing evidence are unavailable.
- Do not use a remote font provider, install font packages, UI frameworks, animation libraries or icon packages by default.
- Do not create future business routes, data or modules as showcase content.

## Discovery Requirements

Discovery was completed as an analysis-only activity and produced `docs/reports/SPEC-002-DISCOVERY-REPORT.md`. Its requirements are retained as the audit record:

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

No Discovery implementation, dependency installation or business content creation was authorized.

## Definition State

This file incorporates the completed Discovery decisions and is ready for human development approval. Implementation remains unauthorized until that approval is explicit.
