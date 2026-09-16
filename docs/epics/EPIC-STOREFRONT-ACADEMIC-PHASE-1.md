# EPIC-STOREFRONT-ACADEMIC-PHASE-1

## Status

`AUDITED / DEFINITION READY FOR HUMAN REVIEW`

Development and every implementation checkpoint are **NOT AUTHORIZED**.
This is a documentation boundary for one coherent public site combining the
Yaris salon experience with an academic storefront. It does not replace or
renumber roadmap SPECs.

## Identifier Decision

The Epic does not claim a new numeric identifier. It consumes the established
roadmap items without renaming them:

- `SPEC-009 CMS / landing`: public storefront foundation.
- `SPEC-010 Ecommerce catalog`: Mary Kay/Cuidado capilar product presentation.
- `SPEC-011 Inventory`: future ecommerce inventory authority, unchanged.
- `SPEC-012 Cart / checkout / orders`: direct order, review, folio and payment
  boundary.
- `SPEC-013 Reviews / favorites`: future reviews/favorites authority,
  unchanged.

The established roadmap continues with `SPEC-014 Meta WhatsApp`, `SPEC-015
Integración pública completa`, `SPEC-016 Security hardening`, `SPEC-017
Performance, accessibility y SEO`, `SPEC-018 E2E / QA`, `SPEC-019 Staging` and
`SPEC-020 Production`. No existing item legitimately owns Appointment Request
or Academic Phase 1 Presentation. The next unused numeric identifiers after
the roadmap are recommended as `SPEC-021 Appointment Request` and `SPEC-022
Academic Phase 1 Presentation`, pending separate roadmap and human approval.

The original proposed mapping is therefore rejected and reconciled to the
existing roadmap. No roadmap title or history is overwritten.

## Authoritative Business Identity

- Salón y Barbería Yaris
- Belleza y elegancia
- Todos los días, 10:00 a. m. – 8:00 p. m.
- WhatsApp: +52 993 229 4158
- Fraccionamiento Ciudad Bicentenario, C.P. 86290

No street, house number, municipality, state, email, social account,
employees, prices, products, inventory, testimonials or policies are invented.

## Existing Authorities

- `PublicLayout`, current UI primitives, semantic tokens and approved fonts.
- SPEC-003 for Customer identity and phone handling.
- SPEC-004 for Appointment status, availability, capacity, concurrency,
  persistence and lifecycle.
- SPEC-005 for the existing Admin Agenda.
- SPEC-006 for guest booking and immediate confirmed Appointment creation.
- SPEC-007 Notification Engine: paused and untouched.
- SPEC-008: Fake WhatsApp, not ecommerce.
- Existing Sanctum admin authentication.

## Proposed Boundaries

### Public site

One mobile-first public surface with Inicio, Tienda, Servicios, Contacto and
Solicitar cita. Reuse `PublicLayout`, tokens, typography and UI primitives;
do not create a second design system.

### Direct order request

This Epic hands catalog presentation to SPEC-010, preserves SPEC-011 as the
inventory owner, and hands cart/checkout/orders to SPEC-012. It does not approve
their schema or implementation.

```text
catalog -> product -> client-only cart -> minimum customer data
  -> review/correction -> order request -> folio and summary
```

Yaris charges and delivers personally. Submission is not automatically `PAID`.
No Stripe, Mercado Pago, card data, payment webhook or payment gateway is
authorized.

Conceptually evaluate only `Product`, `ProductCategory`, `Order` and
`OrderItem`; keep the cart client-only unless recovery/multi-device needs are
approved. Snapshot product name and displayed price in an order item. Payment,
fulfillment and inventory remain separate boundaries.

Mary Kay is an explicit external flow to `https://www.marykay.com.mx/yaris`,
with a notice that purchase occurs outside this site. No local paid order or
fulfillment synchronization is claimed. Cuidado capilar is salon-delivered
and must not be labelled Mary Kay or assigned invented brands.

### Appointment approval

Prefer a separate `AppointmentRequest`:

```text
guest -> pending AppointmentRequest -> Yaris review
  -> existing CreateAppointment -> confirmed Appointment
```

Pending requests do not block availability by default. Approval revalidates
availability through `CreateAppointment` and can fail safely or request an
alternative. This preserves SPEC-004 and is preferable to reopening
`AppointmentStatus`; never change `confirmed` to `pending` silently.

### Academic features

- Privacy/Security: one or two linked information surfaces; provisional copy
  must say `PENDIENTE DE REVISIÓN`.
- Admin login: reuse `/admin/login`; no duplicate admin authentication.
- Customer login: demo-only screen unless a real identity/verification scope
  is approved. Opening WhatsApp is not authentication or verification.
- Order review, correction, submission, folio and summary may be real later.
- A checkbox is not a digital signature; use `DEMOSTRACIÓN` or
  `FUNCIÓN PENDIENTE DE INTEGRACIÓN` until coordinated with the team.
- Any sample invoice must say `DOCUMENTO DE PRUEBA — SIN VALIDEZ FISCAL` and
  must not be called a CFDI, SAT invoice or stamped invoice.

## Data and Security Policy

Real product names, photographs, prices and inventory are unavailable. Any
future fixture must visibly say `EJEMPLO`, `DEMO` or `DATOS DE PRUEBA` and be
development/test-only; no invented production seeder is authorized.

Collect only the minimum name, phone and delivery/invoice data actually
approved. Address is not collected by default. Future design must address
guest abuse, PII, order/request enumeration, admin authorization, external
redirect disclosure, fake payment states, customer verification and invoice
sensitivity. Public resources must not expose sequential sensitive IDs.

## Roadmap Handoff

- SPEC-009 owns only the public CMS/landing foundation and is defined
  separately in `docs/specs/SPEC-009-cms-landing.md`.
- SPEC-010 owns product listing/detail and demo-data policy.
- SPEC-011 remains Inventory and is not redefined as direct orders.
- SPEC-012 owns cart, checkout/orders, review/correction, folio and payment
  boundary; `OrderFolio` is not assumed to be a separate entity.
- SPEC-013 remains Reviews/Favorites and is not redefined as academic work.
- SPEC-021 is the recommended future owner for pending AppointmentRequest and
  admin approval, preserving SPEC-004 and SPEC-006 authority.
- SPEC-022 is the recommended future owner for academic presentation and
  walkthrough surfaces.

Every handoff requires its own Definition, Discovery where needed,
Development authorization, checkpoints and human acceptance.

## Proposed Checkpoints

1. SPEC-009 public CMS/landing foundation.
2. SPEC-010 product catalog.
3. SPEC-012 cart/checkout/orders, consuming SPEC-011 where authorized.
4. SPEC-021 AppointmentRequest and Yaris approval.
5. SPEC-022 academic presentation.
6. Integrated walkthrough and QA after the relevant SPECs are accepted.

These are planning boundaries only. Each requires separate authorization,
acceptance criteria, tests and a report.

## Acceptance Boundary

- Current SPEC-006 immediate-confirmed booking is not silently changed.
- Approval uses a separate request unless a future decision explicitly reopens
  AppointmentStatus.
- Direct orders are requests, not automatically paid.
- Mary Kay purchases remain external.
- No real catalog, payment, invoice, signature or verification claims are
  invented.
- No production code, schema, routes, tests or dependencies are added here.
- Development remains unauthorized.

```text
Epic: AUDITED / DEFINITION READY FOR HUMAN REVIEW
Development: NOT AUTHORIZED
SPEC-007 Notification Engine: PAUSED / UNCHANGED
SPEC-008 Fake WhatsApp: RESERVED / NOT AUTHORIZED
SPEC-009 CMS / landing: DEVELOPMENT IN PROGRESS - CHECKPOINT B ONLY
SPEC-010 Ecommerce catalog: PRESERVED / NOT AUTHORIZED
SPEC-011 Inventory: PRESERVED / NOT AUTHORIZED
SPEC-012 Cart / checkout / orders: PRESERVED / NOT AUTHORIZED
SPEC-013 Reviews / favorites: PRESERVED / NOT AUTHORIZED
SPEC-021 Appointment Request: RECOMMENDED / NOT AUTHORIZED
SPEC-022 Academic Phase 1 Presentation: RECOMMENDED / NOT AUTHORIZED
```

STOP. Submit Audit + Definition for human review.
