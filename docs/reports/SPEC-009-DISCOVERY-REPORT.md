# SPEC-009 TECHNICAL DISCOVERY REPORT

## Status

- SPEC-009: `TECHNICAL DISCOVERY COMPLETED / READY FOR HUMAN REVIEW`.
- Definition: `APPROVED`.
- Development: `NOT AUTHORIZED`.
- Checkpoints A-C: `NOT AUTHORIZED`.
- Branch: `docs/spec-009-discovery`.
- Definition base: `922717207ba278b27d8e61cd60dbd4e22e9243ce`.

## Discovery Scope

This Discovery covers only the public CMS/landing foundation. It does not
implement the homepage, modify `PublicLayout`, add routes, add tests, create
schema, or authorize SPEC-010+.

The approved temporary behavior is:

```text
Ver productos -> #tienda
#tienda -> Mary Kay / Cuidado capilar catalog-in-preparation categories only
```

No products, prices, stock, photographs, order state or external purchase
synchronization are presented as real.

## Current Implementation Evidence

### Homepage

`resources/js/pages/public/FoundationPage.vue:6-17` is the current homepage.
It imports `PublicLayout` and `UiCard`, and displays:

- `Base visual del sistema`.
- `Salón y Barbería Yaris` as the current H1.
- `Belleza y elegancia`.
- Technical copy stating that business content will arrive in later SPECs.

The existing card and page structure can be retained as a structural starting
point, but the technical copy and one-card presentation must be replaced by a
business-facing shell.

### Public shell

`resources/js/layouts/PublicLayout.vue:5-15` currently owns the dark full-page
surface, `UiContainer`, business name, tagline and page slot. It has:

- No public header navigation.
- No mobile menu.
- No skip link.
- No footer.
- No sticky/focus/menu state.

Header, navigation and footer should be added to this shared public shell in a
future implementation, not duplicated in FoundationPage or PublicBookingPage.

### Routing

`resources/js/router/index.ts:11-53` defines `/` for FoundationPage,
`/reservar` for PublicBookingPage, protected `/admin` routes, `/admin/login`
and a catch-all NotFoundPage. `routes/web.php:5-8` serves the SPA shell and
excludes API, Sanctum, build and storage paths from the fallback.

No new public route is required for SPEC-009. Use normal anchor links:

- Inicio -> `/`.
- Tienda -> `/#tienda` or `#tienda` from the homepage.
- Servicios -> `/#servicios` or `#servicios`.
- Contacto -> `/#contacto` or `#contacto`.
- Solicitar cita -> `/reservar`.

Vue Router uses `createWebHistory()` and has no `scrollBehavior`. Native
`<a href="#id">` navigation remains the simplest compatible choice and
supports direct `/#contacto` URLs when the target exists. Future CSS should
use `scroll-margin-top` only if a sticky header is introduced. No JS scrolling
library or new route is justified.

### Existing public navigation and accessibility

There is no public navigation pattern. `AdminLayout.vue:13-20` has ordinary
`RouterLink` navigation for Inicio and Agenda, but no mobile menu. Existing
focus support is visible in `UiButton`, `UiInput`, `UiFormField` and Admin
links; there is no skip link or reduced-motion implementation found.

`resources/js/pages/technicalPages.test.ts:15-22` currently asserts the
technical Foundation copy and will need a future implementation test update,
but no test change is authorized in Discovery.

### Metadata

`resources/views/app.blade.php:1-11` provides `lang="es"`, charset, viewport,
`<title>AgendaEstetica</title>` and Vite assets. There is no description meta,
router-aware title logic or SPA metadata service. SPEC-009 should minimally
replace the technical title and add an approved business-safe description;
SSR, router metadata dependencies and advanced SEO are unnecessary.

## Reusable Contracts

Available primitives:

- `UiButton.vue`: primary/secondary variants, disabled/loading and visible
  focus ring; suitable for hero CTAs when a button is actually an action.
- `UiContainer.vue`: centered `max-w-3xl` container with responsive padding.
- `UiCard.vue`: elevated bordered card; suitable for category/contact blocks.
- `UiInput.vue`, `UiFormField.vue`: form-only primitives, not needed for the
  static landing shell.
- `UiConfirmDialog.vue`: unrelated to the landing surface.

The existing Tailwind CSS-first system is sufficient:

- `resources/css/theme.css:1-62` defines #050505, #D01772, #CB6CA5,
  #3AC4D7, #F5CC7A and #FFFFFF plus semantic surface/action/focus tokens.
- `resources/css/fonts.css:1-26` self-hosts Cormorant Garamond 700, Montserrat
  600-700 and Poppins 400.
- `resources/css/app.css:1-13` imports Tailwind, fonts and theme.

No new semantic token, font, button system, container system or icon library is
needed for Discovery's target. The visual system supports the entire shell.

## Business Data Source

No `site.ts`, `business.ts`, static public-data module or equivalent frontend
source exists. `publicBooking.ts` is an API client for service booking, not a
general site-content source. `BusinessProfile` exists in the backend, but its
approved fields and current public endpoint do not constitute a CMS content
contract; using it would add a network dependency to static landing content.

### Recommendation

SPEC-009 V1 should use one small typed static public-site data module with a
shaped contract for the approved name, tagline, hours, display phone, contact
phone and location. Components should consume that contract rather than
scatter literals. This gives deterministic rendering and simple SEO, avoids a
database/CMS dependency, and leaves a clean adapter boundary for a future
focused CMS or BusinessProfile-backed source.

No database persistence is justified for these mostly static facts in SPEC-009.

### CMS classification

CMS capability is **PARTIALLY SATISFIED BY CENTRALIZED STATIC CONTENT**;
generic editing is **DEFERRED**. Evidence:

- Roadmap title is `CMS / landing`.
- No CMS model, editor, content API or authoring surface exists.
- Current requirements need a trustworthy landing, not authoring,
  permissions, drafts, preview, publication or revision history.
- The master specification excludes a free-form page builder.

If future authoring is approved, preserve the shaped public-site data contract
and replace its source behind the same interface. That future decision may
require an ADR; this ordinary static-source choice does not.

## Homepage Architecture

The hero should be semantic and text-first:

- One meaningful H1: `Salón y Barbería Yaris`.
- Tagline: `Belleza y elegancia`.
- `Ver productos` is a link to `#tienda`.
- `Solicitar cita` is a link to `/reservar`.
- No stock photo, invented image, testimonial, promotion or decorative media
  requirement is introduced.

The `#tienda` section should contain two informational, non-clickable category
cards:

- `Mary Kay` — `Catálogo en preparación.`
- `Cuidado capilar` — `Catálogo en preparación.`

The cards must not look like product cards, show prices/stock/photos or imply
that products are available. SPEC-010 can later make the categories links or
replace the section with a canonical `/tienda` catalog.

The `#servicios` section should be a lightweight anchor and optional preview.
The preferred initial behavior is a short approved business-facing pointer to
`/reservar`, not a homepage API request. If approved Service data is later
shown, it must use the existing public API with independent loading, empty and
error states and a fallback that leaves the core homepage usable.

The `#contacto` section should show only approved hours, WhatsApp and general
location. Contact is informational/outbound; it does not authenticate, verify,
create an order, create an appointment or register a Customer.

The footer should show identity, navigation and contact. Do not publish
invented legal text or link absent Privacy/Security routes; reserve those
integration positions for the later academic scope and mark future content
`PENDIENTE DE REVISIÓN`.

## Link and Navigation Decisions

### WhatsApp

Use the digits-only canonical destination:

```text
https://wa.me/529932294158
```

Do not add a prefilled message without approved business copy. A new tab is
acceptable for the external destination with `target="_blank"` and
`rel="noopener noreferrer"`, plus an accessible label such as “Abrir
WhatsApp de Salón y Barbería Yaris”. No SPEC-008 integration is involved.

### Location

Use a plain text contact card containing only:

```text
Fraccionamiento Ciudad Bicentenario
C.P. 86290
```

No coordinates, map link, Google Maps, Leaflet or external map dependency is
justified.

### Header behavior

Recommend a static header for V1. There is no existing sticky-header contract,
and static navigation avoids anchor offsets, backdrop/viewport and focus
complexity. If later changed to sticky, require `scroll-margin-top`, clear
focus visibility and mobile viewport review. No scrollspy or IntersectionObserver
is needed; active scroll state is not required for this small page.

## Accessibility and Responsive Requirements

Future implementation must provide semantic header/nav/main/section/footer,
one H1, logical heading hierarchy, keyboard-operable mobile menu, Escape close,
visible focus, correct link semantics, accessible external link labeling,
sensible touch targets and no horizontal overflow. The mobile menu must close
after anchor navigation and must not leave body scroll locked.

The page is text/design-led and should remain mobile-first at 375/390, adapt to
tablet at 768/1024 and use restrained multi-column layout at 1440/1920. No
screenshot-specific hardcoding is recommended.

## SEO and Performance

Minimum SEO is document title, meta description, one H1 and semantic sections,
using only approved business data. `/reservar` may retain a route-specific
title later, but no metadata library is needed for this checkpoint.

The current frontend uses Vite/Tailwind/Vue with no carousel, animation,
icon or CMS SDK dependency. Static shell additions should have minimal bundle
impact. No new dependency is justified. No analytics, tracking, cookies or
consent banner is introduced.

## Failure and Security Boundaries

The landing must not depend on Service API success for core identity/contact
content. If a service preview is approved later, separate:

- Loading: neutral loading state.
- Empty success: “No hay servicios disponibles.”
- Request failure: “No se pudieron cargar las opciones.”

The current booking page conflates these states because its empty branch checks
only `!services.length`; SPEC-009 must not repeat that pattern.

Trusted static strings have a reduced XSS surface. Future CMS-authored HTML
would require sanitization, authorization, preview/publication controls and an
ADR if architecture changes. SPEC-009 collects no new PII and has no public
order/request identifiers.

## Test and Visual QA Strategy

Future Development tests should cover homepage identity/tagline, both CTA
destinations, the three anchors, approved hours/phone/location, only the two
catalog-in-preparation categories, absence of product/pricing/stock claims,
keyboard mobile-menu behavior, Escape, focus and `/reservar` regression.

The current repository has Vitest component tests but no Playwright/browser
E2E or visual screenshot tooling in `package.json`. Do not add browser tooling
in Discovery. Use manual browser QA for the six representative widths unless a
future approved QA scope adds tooling.

## Checkpoint Proposal

- **Checkpoint A — Public Shell:** centralized public-site data, PublicLayout
  header/nav/footer, static responsive mobile menu and anchor integration.
- **Checkpoint B — Homepage Content:** hero, approved CTA behavior, #tienda
  category placeholders, #servicios anchor and #contacto section.
- **Checkpoint C — Polish/QA:** metadata, responsive widths, accessibility,
  browser verification, copy and scope audit.

No unresolved business decision blocks Checkpoint A because the approved
identity/contact facts are sufficient. The temporary `Ver productos -> #tienda`
behavior is already approved. Development and Checkpoint A remain unauthorized.

## ADR Assessment

No ADR is required for the recommended static typed data module, native hash
links, static header or ordinary component composition. A future generic CMS,
BusinessProfile content API or authored HTML security model would be a separate
durable architectural decision and should be an ADR draft before development.

## Handoff Boundaries

- SPEC-010: Ecommerce catalog, Mary Kay/Cuidado capilar product listing/detail
  and demo-data policy.
- SPEC-011: Inventory, unchanged.
- SPEC-012: Cart/checkout/orders, customer order data, review, direct order,
  folio and unpaid/paid boundary.
- SPEC-013: Reviews/favorites, unchanged.
- Recommended SPEC-021: pending AppointmentRequest and admin approval; pending
  requests do not block availability and approval hands off to CreateAppointment.
- Recommended SPEC-022: academic privacy/security, customer-auth boundary,
  signature/invoice demonstrations and walkthrough evidence.

All are planned or recommended only, not authorized.

## Scope Audit

```text
Application code: NONE
Vue changes: NONE
Routes: NONE
Tests: NONE
Schema: NONE
Dependencies: NONE
Assets: NONE
SPEC-007: PAUSED / UNCHANGED
SPEC-008: FAKE WHATSAPP / UNCHANGED
```

## Verification Evidence

- Backend regression: `208 tests / 1202 assertions PASS`.
- Frontend regression: `15 files / 55 tests PASS`.
- `git diff --check`: PASS.
- No test, production, schema, route, dependency or asset changes were made
  during Discovery.

## Final Discovery Decision

```text
SPEC-009 - CMS / Landing
Definition: APPROVED
Technical Discovery: COMPLETED / READY FOR HUMAN REVIEW
Development: NOT AUTHORIZED
Checkpoint A: NOT AUTHORIZED
Generic CMS: DEFERRED unless evidence proves necessary
Application code: NONE
SPEC-010: NOT AUTHORIZED
SPEC-007: PAUSED / UNCHANGED
SPEC-008: FAKE WHATSAPP / UNCHANGED
```

STOP. Submit Technical Discovery for human review.
