# YARIS STOREFRONT + ECOMMERCE INTEGRATION AUDIT REPORT

## Repository

1. `main` HEAD: `e87d5fcca22d7fb070ec295c9647a9a69306a52a`
2. `origin/main` HEAD: `e87d5fcca22d7fb070ec295c9647a9a69306a52a`
3. Working tree at audit start: clean
4. Audit branch: `docs/epic-storefront-academic-phase-1`
5. Selected identifier: `EPIC-STOREFRONT-ACADEMIC-PHASE-1`
6. Reason: this scope crosses roadmap items 09-13 and conflicts with the
   closed booking confirmation contract; SPEC-007 and SPEC-008 are occupied.
7. SPEC-008 preserved: YES

The published `feat/spec-007-notification-engine` branch at
`81f20c7ca606b97969ee6b266308ef82d36e0a92` was not touched.

## Roadmap Reconciliation

The original SPEC-009–013 proposal was rejected because it overwrote
established roadmap intent. The Epic now consumes the existing items:

| SPEC | Established title | Epic role |
| --- | --- | --- |
| SPEC-009 | CMS / landing | Public storefront foundation |
| SPEC-010 | Ecommerce catalog | Mary Kay/Cuidado capilar presentation |
| SPEC-011 | Inventory | Future inventory authority, unchanged |
| SPEC-012 | Cart / checkout / orders | Direct order, review, folio and payment boundary |
| SPEC-013 | Reviews / favorites | Future reviews/favorites authority, unchanged |

The roadmap continues with SPEC-014 Meta WhatsApp, SPEC-015 Integración pública
completa, SPEC-016 Security hardening, SPEC-017 Performance, accessibility y
SEO, SPEC-018 E2E / QA, SPEC-019 Staging and SPEC-020 Production. No existing
item legitimately owns Appointment Request or Academic Phase 1 Presentation;
SPEC-021 and SPEC-022 are recommended future identifiers, not authorized work.

## Current Screen Inventory

### Public

| Route | Component | Current purpose | State | Result |
| --- | --- | --- | --- | --- |
| `/` | `FoundationPage` | Technical foundation page | Real technical | Reuse layout/tokens, replace content later |
| `/reservar` | `PublicBookingPage` | Guest service booking | Real working | Reuse existing API/domain; approval is new scope |
| `/:pathMatch(.*)*` | `NotFoundPage` | SPA 404 | Real | Reuse |
| `/tienda`, product detail, cart, checkout | None | Store | Missing | New later |
| `/contacto`, `/privacidad`, `/seguridad` | None | Public information | Missing | New later |
| Customer login/register | None | Customer identity | Missing | Demo/future scope only |

`routes/web.php:5-8` serves one Vue SPA shell. `routes/api.php:13-31` exposes
health and only Public Booking APIs: context, services, professionals,
availability and appointment POST. There are no product, order, invoice,
payment or appointment-request APIs.

### Admin

| Route | Component | Current purpose | State | Result |
| --- | --- | --- | --- | --- |
| `/admin/login` | `AdminLoginPage` | Admin login | Real working | Reuse; no duplicate login |
| `/admin` | `AdminPage` | Technical admin home | Real | Reuse `AdminLayout` |
| `/admin/agenda` | `AdminAgendaPage` | Agenda and appointment creation | Real working | Keep separate from requests |
| `/admin/agenda/:id` | `AdminAgendaDetailPage` | Detail/history/lifecycle | Real working | Reuse |
| `/admin/solicitudes`, `/admin/pedidos`, `/admin/productos` | None | Future modules | Missing | Same AdminLayout later |

## Home

8. Reusable: Yaris name, phrase, `PublicLayout`, `UiCard`, `UiContainer`,
   semantic tokens and typography.
9. Missing: business hero, `Ver productos`, `Solicitar cita`, Inicio/Tienda/
   Servicios/Contacto navigation, hours, WhatsApp, location and footer.
10. Current exact content (`FoundationPage.vue:6-15`): “Base visual del
    sistema”, “Salón y Barbería Yaris”, “Belleza y elegancia” and “Vue SPA
    shell técnico. El contenido de negocio se incorporará en SPECs posteriores.”
11. `PublicLayout.vue:5-15` has only dark main, name, phrase and slot; no nav.
12. Booking exists at `/reservar` as a five-step flow with confirmed result.
13. Admin Agenda exists and is functional.
14. Privacy/security: none.
15. Customer auth: none; admin auth exists.
16. Ecommerce/cart/order: none.
17. Proposed change: replace only technical home content after approval;
    preserve the existing public foundation.

## Store

18. Catalog capability: appointment Service catalog only.
19. Product model: none.
20. Cart: none.
21. Order model: none.
22. Missing: product/category/detail, cart, order request, review, folio,
    fulfillment boundary, invoice request and admin order visibility.
23. Direct-order recommendation: client-only cart; future `Product`,
    `ProductCategory`, `Order`, `OrderItem`; snapshot item name/price; status
    such as submitted/accepted/rejected/fulfilled/cancelled; never infer paid.
24. Mary Kay: external link plus explicit outside-site warning; no sync claim.
25. Hair care: local salon delivery; no invented brands or Mary Kay labels.

## Booking

26. Current behavior: Public Booking delegates to `CreatePublicBooking` and
    `CreateAppointment`, which creates a confirmed Appointment immediately.
27. Current load error: `Promise.all` catch at `PublicBookingPage.vue:88` sets
    `No pudimos cargar las opciones de reserva.`
28. Empty/failure distinction present: NO.
29. Cause: `!loading && !services.length` at lines 101-109 renders the empty
    message after both a successful `[]` and a failed request.
30. Future distinction: successful empty -> “No hay servicios disponibles”;
    failed request -> “No se pudieron cargar las opciones”. Not fixed here.
31. Conflict: requested pending approval differs from closed SPEC-006 immediate
    confirmed booking and SPEC-004's four statuses.
32. Recommendation: separate pending `AppointmentRequest`, approved through
    existing `CreateAppointment`.
33. Pending request blocks availability: NO by default; no implicit hold.
34. Approval UI: dedicated Solicitudes area in existing `AdminLayout`.
35. SPEC-004 changes required: NO for preferred design; reopening statuses is
    a separate cross-SPEC decision.

## Academic Phase 1

36. Privacy/security current state: no pages.
37. Add later: footer/form links and `PENDIENTE DE REVISIÓN` provisional copy.
38. Admin auth: `/admin/login`, Sanctum and protected admin APIs; reuse.
39. Customer auth: absent.
40. WhatsApp verification: absent and unapproved; chat opening is not auth.
41. Boundary: demo UX only until real verification Discovery is approved.
42. Order review/integrity: absent; suitable for real future review/correction.
43. Digital signature: absent; checkbox cannot be called a signature.
44. Invoice request: absent; decide future flag/state versus demo-only UI.
45. Demo invoice: downloadable sample labelled no fiscal validity.

## Real Versus Demo Matrix

| Feature | Classification |
| --- | --- |
| Home | REAL technical shell; business landing missing |
| Catalog | REAL services; product catalog missing |
| Product detail, cart, order review/submission/folio | NOT IMPLEMENTED |
| Customer data | REAL minimal guest booking data |
| Mary Kay external link | NOT IMPLEMENTED |
| Booking | REAL / WORKING, immediate confirmed |
| Appointment approval | NOT IMPLEMENTED / future request scope |
| Privacy, Security | NOT IMPLEMENTED |
| Admin login | REAL / WORKING |
| Customer login | NOT IMPLEMENTED |
| WhatsApp verification | NOT IMPLEMENTED / PENDING DISCOVERY |
| Digital signature | NOT IMPLEMENTED / PENDING TEAM INTEGRATION |
| Invoice request/demo download | NOT IMPLEMENTED |

## Data and Architecture

46. Missing real data: official timezone/profile, services, durations, prices,
    professionals, assignments, schedules, policies, product names/photos/
    prices/inventory, delivery rules, invoice data and final legal copy.
47. Demo strategy: dev/test fixtures only, visibly `EJEMPLO`/`DEMO`/`DATOS DE
    PRUEBA`; no invented production products.
48. Production-data invention detected: NO.
49. Proposed modules: Storefront, Product Catalog, Direct Order Request,
    Appointment Request and Academic Presentation; keep payments/invoices/
    notifications separate.
50. Reuse: layouts, tokens, primitives, Public Booking APIs, CreateAppointment,
    availability, phone normalization and Sanctum.
51. Conflicts: pending approval versus immediate confirmation; customer account
    versus guest booking; roadmap 09-13 overlap; SPEC-007/008 reservations.
52. Concepts: Product, ProductCategory, Order, OrderItem, OrderFolio,
    AppointmentRequest, conceptual only.
53. Likely schema: products/categories, order/items/snapshots, request state,
    optional invoice request; no schema decision.
54. Likely API: catalog, order submission/review, protected admin order/request
    reads and request approval; no route authorized.
55. Likely frontend: home/nav, catalog, product, client cart, review/folio,
    request booking, admin requests and academic screens.
56. Security: abuse, PII, enumeration, admin authorization, redirects, auth,
    payment/signature/invoice false-state prevention.
57. Privacy: minimum customer data, no default address, redacted data and safe
    public identifiers.

## Plan

58. Checkpoints: A home/contact; B catalog; C cart/order/review/folio; D
    AppointmentRequest/approval; E academic boundaries; F integrated QA.
59. Reuse: public layout/tokens in A-B; HTTP conventions in C; CreateAppointment
    and AdminLayout in D; auth/primitives in E; existing quality gates in F.
60. New work: missing public pages, product/order/request contracts, future
    persistence, request review and labelled demonstrations.
61. Blocking decisions: SPEC mapping, real data, order/inventory lifecycle,
    delivery, customer verification, approval policy, legal copy, signature,
    invoice and admin permissions.
62. Non-blocking: component names, aliases and one versus two legal pages.
63. Deferred: payments, external sync, inventory automation, customer accounts,
    verification, signature, CFDI, providers, reviews/favorites and CMS authoring.

## Governance

64. Application code changes: NONE
65. Schema changes: NONE
66. Routes changed: NONE
67. Tests changed: NONE
68. Dependencies changed: NONE
69. SPEC-007 branch touched: NO
70. SPEC-008 touched: NO
71. `git diff --check`: PASS

## Documentation / Git

72. Created: `docs/epics/EPIC-STOREFRONT-ACADEMIC-PHASE-1.md` and this report.
73. Modified: `docs/roadmap/ROADMAP.md` only to register this review scope.
74. Commit: NONE
75. Commit hash: NONE
76. Push: NONE
77. Working tree: documentation changes intentionally uncommitted for review.
78. Local/remote synchronization: audit branch local only.

## Status

79. Epic status: DEFINED / COORDINATION ONLY
80. SPEC-009 Definition: DEFINITION COMPLETED / READY FOR HUMAN REVIEW
81. SPEC-009 Development: NOT AUTHORIZED
82. SPEC-009 Checkpoint A: NOT AUTHORIZED
83. SPEC-010–013: PRESERVED / NOT AUTHORIZED
84. SPEC-007: PAUSED / UNCHANGED
85. SPEC-008: FAKE WHATSAPP / UNCHANGED / NOT AUTHORIZED
86. Blockers: human approval and future SPEC-021/SPEC-022 mapping approval.
87. Recommended next action: STOP and submit reconciled SPEC-009 Definition.

```text
SPEC-007: PAUSED / UNCHANGED
SPEC-008: FAKE WHATSAPP / UNCHANGED / NOT AUTHORIZED
STOREFRONT + ECOMMERCE: AUDITED / DEFINITION READY FOR HUMAN REVIEW
SPEC-009: CMS / LANDING / DEFINITION COMPLETED / READY FOR HUMAN REVIEW
SPEC-010–013: PRESERVED / NOT AUTHORIZED
SPEC-021: APPOINTMENT REQUEST / RECOMMENDED / NOT AUTHORIZED
SPEC-022: ACADEMIC PHASE 1 PRESENTATION / RECOMMENDED / NOT AUTHORIZED
Development: NOT AUTHORIZED
Application code: NONE
Recommended next action: STOP.
```
