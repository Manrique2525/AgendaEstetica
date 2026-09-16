# SPEC-009 - CMS / Landing

## Status

`DEVELOPMENT IN PROGRESS - CHECKPOINT C PARTIAL / BROWSER QA PENDING`

Definition is approved. Development is authorized for Checkpoints A, B and the technical portion of C only. Real browser QA remains pending human verification. This
Definition covers only the public landing/storefront foundation. It does not
implement a generic CMS editor, ecommerce, booking changes or academic
presentation features.

## Roadmap and Epic

- Roadmap item: `09. CMS / landing`.
- Epic: `EPIC-STOREFRONT-ACADEMIC-PHASE-1`.
- Epic role: coordination only; it does not authorize combined development.
- SPEC-010 owns Ecommerce catalog.
- SPEC-011 remains Inventory.
- SPEC-012 owns Cart / checkout / orders.
- SPEC-013 remains Reviews / favorites.
- Recommended future SPEC-021 owns Appointment Request.
- Recommended future SPEC-022 owns Academic Phase 1 Presentation.

SPEC-006 Public Booking remains closed and unchanged: `/reservar` submits a
guest booking that immediately creates a confirmed Appointment through the
existing authority. AppointmentRequest is not part of SPEC-009.

## Objective

Replace the provisional technical homepage with a real business-facing public
foundation for Salón y Barbería Yaris while preserving the approved design
system and leaving catalog/order/booking behavior unchanged.

## Approved Business Data

- Name: `Salón y Barbería Yaris`
- Tagline: `Belleza y elegancia`
- Hours: `Todos los días, 10:00 a. m. a 8:00 p. m.`
- WhatsApp: `+52 993 229 4158`
- Display number: `993 229 4158`
- General location: `Fraccionamiento Ciudad Bicentenario, C.P. 86290`

No street, number, email, social network, employee, testimonial, promotion or
additional policy is invented.

## Actors and Dependencies

- Visitor: reads the public business presentation and follows approved links.
- Yaris administrator: existing internal admin authority, not changed here.
- Public storefront foundation: presents static approved business information.
- SPEC-006 Public Booking: existing `/reservar` consumer, unchanged.
- SPEC-010 Ecommerce catalog: future owner of actual products and details.
- SPEC-013 and recommended SPEC-022: future owners of later presentation
  surfaces.

Reuse `PublicLayout`, `UiContainer`, `UiCard`, `UiButton`, existing router and
the approved CSS tokens/font system. The existing service projection may be
shown only if approved current Service data is available; no service names or
prices are invented and no Service CRUD is added.

## CMS Scope Review

The current repository has no CMS model, editor, content API or authoring
surface. Roadmap item 09 is titled `CMS / landing`, but the current requirement
is a business landing page and the audit found no evidence requiring a generic
editor. Therefore SPEC-009 recommends a minimal static content architecture
with one centralized public business-data source. CMS editing is a future
checkpoint or later scope only if a concrete authoring requirement is
approved. No database persistence is justified solely for the phone, tagline
or location in this Definition.

## Functional Requirements

### FR-01 Public identity

The public homepage presents the approved Yaris name and tagline and removes
technical Foundation copy such as `Base visual del sistema`.

### FR-02 Hero

The hero presents the business name, tagline, `Ver productos` CTA and
`Solicitar cita` CTA.

### FR-03 Navigation

The public navigation presents `Inicio`, `Tienda`, `Servicios` and `Contacto`
without creating broken routes.

### FR-04 Appointment CTA

`Solicitar cita` targets the existing `/reservar` route and does not alter its
immediate-confirmed booking behavior.

### FR-05 Store CTA

`Ver productos` is structurally prepared for SPEC-010. Until SPEC-010 exists,
it must target a clearly labelled store-coming-soon anchor or remain inactive
by explicit product decision; it must not pretend to be a functioning catalog.

### FR-06 Business information

The homepage presents only the approved hours, WhatsApp contact and general
location.

### FR-07 Contact behavior

The WhatsApp action may open an external conversation. It must not authenticate
or verify a Customer, create an order, create an appointment or register a
Customer.

### FR-08 Footer

The footer presents Yaris identity, navigation and contact integration points.
Future Privacy/Security links may be reserved, but no invented legal text is
published.

### FR-09 Services boundary

If a services preview is included, it consumes approved existing Service data;
otherwise `Servicios` targets a homepage section. No service catalog or CRUD is
introduced.

### FR-10 Metadata

Homepage title, description, one meaningful H1 and semantic structure use only
approved business data. No legal entity, coordinates or social profile is
invented.

## Non-Functional Requirements

### NFR-01 Architecture

Use the existing Vue SPA, PublicLayout, router, UI primitives, tokens and font
system. Do not create a second design system or duplicate layout/component
contracts.

### NFR-02 Responsive behavior

The public shell must work at representative widths 375, 390, 768, 1024,
1440 and 1920 without horizontal overflow.

### NFR-03 Accessibility

Use semantic header/nav/footer, correct link/button semantics, keyboard-
operable mobile navigation, visible focus, Escape behavior consistent with
existing patterns, meaningful heading hierarchy, accessible WhatsApp link and
sensible mobile touch targets.

### NFR-04 Reduced motion

If motion is later introduced, it must respect reduced-motion preferences. No
motion dependency is authorized here.

### NFR-05 Privacy

Do not collect or expose customer/order data in the landing foundation.
Provisional legal content remains `PENDIENTE DE REVISIÓN`.

### NFR-06 Scope safety

No product, inventory, cart, order, payment, AppointmentRequest, customer-auth,
signature, invoice, provider or Fake WhatsApp behavior is included.

## Acceptance Criteria

- AC-01: Technical homepage presentation is replaced by approved Yaris
  business-facing content.
- AC-02: Name is exactly `Salón y Barbería Yaris`.
- AC-03: Tagline is exactly `Belleza y elegancia`.
- AC-04: Both `Ver productos` and `Solicitar cita` CTAs are present.
- AC-05: Navigation contains Inicio, Tienda, Servicios and Contacto.
- AC-06: `Solicitar cita` points to `/reservar`.
- AC-07: Existing Public Booking behavior is unchanged.
- AC-08: Hours are shown as todos los días, 10:00 a. m. a 8:00 p. m.
- AC-09: WhatsApp displays `993 229 4158` and has accessible contact semantics.
- AC-10: Location is limited to Fraccionamiento Ciudad Bicentenario, C.P. 86290.
- AC-11: Approved colors, fonts, PublicLayout and existing primitives are reused.
- AC-12: No invented business data appears.
- AC-13: Store CTA does not present unfinished ecommerce as functional.
- AC-14: No ecommerce, inventory, order, payment or invoice implementation leaks in.
- AC-15: No AppointmentRequest, pending status or admin Solicitudes leaks in.
- AC-16: No customer login, WhatsApp verification or digital-signature feature leaks in.
- AC-17: Footer reserves future legal integration without invented legal copy.
- AC-18: Navigation contains no broken destination in the approved foundation.
- AC-19: Keyboard, focus, semantic structure and touch-target requirements are met.
- AC-20: Representative responsive widths pass visual QA without overflow.
- AC-21: Business-safe title, description and H1 are present.
- AC-22: Implementation is separately authorized and reviewed before work begins.

## Proposed Checkpoints

Checkpoint A and B are implemented under their respective authorizations.
Checkpoint C remains unauthorized:

- Checkpoint A: public shell, centralized business data, header, navigation and
  footer.
- Checkpoint B: hero, business/contact content and preparation-only store
  categories.
- Checkpoint C: responsive, accessibility, SEO and visual QA.

Generic CMS editing is deferred unless a later approved requirement establishes
content authorship, permissions, persistence, preview and publication needs.

## Out of Scope

Product models/data/detail, Mary Kay or hair-care catalog, cart, orders,
checkout, inventory, payments, invoice/demo invoice, reviews, favorites,
customer accounts, WhatsApp verification, AppointmentRequest, admin
Solicitudes, booking status changes, privacy legal copy, security legal copy,
digital signature, academic screens, providers and Fake WhatsApp.

## State

```text
SPEC-009 - CMS / landing: DEVELOPMENT IN PROGRESS
Definition: APPROVED
Technical Discovery: APPROVED
Development: IN PROGRESS - CHECKPOINT C TECHNICAL PORTION ONLY
Checkpoint A: COMPLETED / APPROVED
Checkpoint B: COMPLETED / APPROVED
Checkpoint C: PARTIALLY IMPLEMENTED / BROWSER QA PENDING
SPEC-007: PAUSED / UNCHANGED
SPEC-008: FAKE WHATSAPP / UNCHANGED / NOT AUTHORIZED
```

STOP. Complete the human browser QA checklist before requesting Checkpoint C closure. Do not start SPEC-010.
