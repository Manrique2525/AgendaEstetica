# SPEC-006 - Public Booking

## SPEC ID

`SPEC-006`

## Title

Public Booking

## Status

`APPROVED FOR DEVELOPMENT / IN PROGRESS`

This Definition and its Technical Discovery describe the public booking consumer identified as roadmap item `06`. Checkpoints A-E are implemented; Checkpoint F and further development require separate approval.

## Roadmap Source

The provisional roadmap places `06. Public booking` immediately after `05. Admin agenda` and before the Notification Engine. SPEC-003, SPEC-004 and SPEC-005 are closed and remain authoritative for their respective domains and consumers.

## Business Problem

A customer should be able to book an appointment through a public experience without authenticating as an internal administrator. The public consumer must make immediate confirmed booking understandable on a phone while preserving the Appointment Engine as the only authority for availability, duration, status, capacity, history and concurrency.

## Purpose

Define the business capability and boundaries for a focused Public Booking consumer. The consumer lets a guest select an eligible Service, a specific Professional and a valid bookable time, provide required name and phone, submit the booking and receive an on-screen confirmation. It must consume SPEC-003 and SPEC-004 rather than create a second booking or availability engine.

## Actors

- Public visitor or customer without an application account in V1.
- Customer domain record, representing the operational identity used by an appointment.
- Professional, an operational bookable resource and not an authenticated User.
- Yaris administrator, the downstream operational user who sees resulting appointments in Admin Agenda.

`Professional != User` and `Customer != internal User`. This Definition does not introduce customer or professional login.

## Goals

- Let a public visitor understand available Services without exposing internal administration data.
- Let a guest select a specific Professional and valid bookable time through the approved public flow.
- Collect required `name` and `phone` without requiring an account.
- Create or reuse the minimum Customer record and submit an immediate confirmed booking safely.
- Provide clear on-screen success, validation and conflict outcomes.
- Use the authoritative SPEC-004 Appointment Engine for every appointment decision.
- Make a successfully created appointment visible to the existing Admin Agenda consumer.
- Preserve BusinessProfile timezone and business-local presentation at the public boundary.
- Make the experience mobile-first, accessible and resistant to common duplicate/abuse scenarios.

## Non-Goals

- Admin Agenda changes or a second administrative workflow.
- Customer accounts, passwords, OAuth or email verification by default.
- Professional accounts, login or a Professional portal.
- Customer CRUD administration.
- ProfessionalSchedule, ProfessionalTimeOff or BusinessHours CRUD.
- Notifications, reminders, WhatsApp, email or SMS delivery.
- Payments, deposits, refunds or financial confirmation.
- CMS, ecommerce, products, inventory, cart or orders.
- Rich week/month calendar grids, drag/drop or a calendar library.
- Automatic Professional assignment or any-Professional selection.
- Public customer cancellation, rescheduling or appointment lookup.
- New Appointment states, required public IDs, booking holds or persistence changes.
- SPEC-007 or any later SPEC.

## In-Scope Business Workflows

- Public booking entry from the public product surface.
- Active Service/category discovery and selection.
- Specific Professional selection.
- Valid bookable date/time selection using future public availability behavior.
- Required `name` and `phone` capture without Customer authentication.
- Minimal Customer create or reuse as part of booking.
- Review and explicit booking submission.
- Immediate confirmed Appointment creation through `CreateAppointment`.
- On-screen success confirmation with safe Service, Professional and date/time facts.
- Safe validation, unavailable, stale-state, abuse and unexpected-failure presentation.
- Downstream visibility of the created appointment in Admin Agenda.

These are business workflows, not authorization for routes, controllers, Vue components or persistence design.

## Out-of-Scope Workflows

- Administrative Appointment operations already owned by SPEC-005.
- Public cancellation, public rescheduling and public appointment lookup.
- Customer profile management or account lifecycle.
- Professional management or automatic assignment.
- Schedule, TimeOff and BusinessHours administration.
- Notification delivery, payments, deposits and ecommerce.

## Domain Ownership and Dependencies

### SPEC-003 dependencies

SPEC-006 may consume active ServiceCategory, Service, Professional, ProfessionalService, BusinessProfile and business configuration, plus Customer identity. SPEC-003 remains authoritative for those concepts, validation, lifecycle and privacy boundaries. Customer data is not a public directory; SPEC-006 may create or reuse only the minimum Customer identity required by a booking and does not own general Customer CRUD.

Candidate public-readable data is limited to active Service/category presentation, approved duration and pricing presentation, eligible Professional display data, and the business-local date/time context needed for booking. Customer records, normalized phones and internal configuration remain non-public. Public Customer create/reuse is limited to the booking capability and does not authorize public search or enumeration.

### SPEC-004 dependencies

SPEC-006 consumes Appointment, CheckAppointmentAvailability, CreateAppointment, Service duration authority, BusinessHours and ProfessionalSchedule effects, ProfessionalTimeOff effects, timezone/DST rules, capacity rules and transactional concurrency behavior. SPEC-004 remains authoritative for Appointment persistence, status, duration, availability, capacity, locks, history and all domain validation.

### SPEC-005 integration dependency

Admin Agenda is the downstream operational consumer. A successfully created public Appointment should be observable there through its existing authoritative read surface. SPEC-006 must not call or expose Admin Agenda mutation routes as a public authorization shortcut.

## Authority Boundaries

SPEC-006 does not own:

- Appointment availability, slots or capacity calculation.
- Professional overlap, BusinessHours, ProfessionalSchedule or ProfessionalTimeOff rules.
- Appointment duration authority or historical duration integrity.
- Appointment status transitions, AppointmentHistory or concurrency/locking.
- Customer canonical identity rules or a new uniqueness model.

Conceptually:

```text
Public Booking
  -> future public boundary
  -> SPEC-003 Core concepts / SPEC-004 authoritative operations
  -> MySQL
```

The frontend must never become availability authority or decide whether an appointment exists.

## Customer Identity Boundary

V1 is guest booking without Customer authentication. The visitor must provide both required fields `name` and `phone`; address, birth date, gender, email, notes and marketing profile are not part of V1.

The booking capability may reuse an existing Customer or create a minimal Customer when no existing record is available. Technical Discovery finalizes server-side matching, normalization flow, duplicate handling and transaction behavior while preserving the existing `phone_normalized` indexed-but-not-unique contract. Public Customer search, enumeration and general Customer CRUD remain out of scope.

Customer authentication is out of V1; any future account flow requires a separate approved scope.

## Availability and Professional Selection

The public user must select a valid bookable time before submission. Bookable-time/slot selection is in scope as a business capability; the finalized Discovery contract uses 15-minute candidate starts, one business-local date per request and a 90-calendar-day inclusive horizon. A displayed available time is not a reservation guarantee and may become unavailable before final submit.

The visitor must select a specific Professional. The flow does not offer `cualquiera disponible`, server-selected Professionals, ranking, round robin or automatic assignment. Final Service/Professional compatibility remains backend/domain-authoritative.

Service duration is displayed as information only. The visitor cannot provide or override `duration_minutes`.

## Appointment Status and Lifecycle

New Appointment status required: `NO`. Successful Public Booking immediately invokes `CreateAppointment` and creates a `confirmed` Appointment.

V1 has no approval queue or intermediate booking-request state. Introducing `pending`, `requested`, `approved`, `rejected` or equivalent later would affect the closed SPEC-004 lifecycle and require separate human authorization and cross-SPEC design.

The inherited lifecycle and history authority remains SPEC-004. Public Booking must not expose AppointmentHistory or allow public status mutation.

## Public Data and Privacy

Candidate public data is limited to:

- Active Service categories and Services.
- Service duration and an approved pricing presentation without invented values.
- Active Professional display data if the visitor chooses a Professional.
- Business timezone/hours effects needed to understand booking times.
- A safe success projection of the visitor's own newly created appointment.

The public boundary must not expose Customer lists, Customer phones, `phone_normalized`, AppointmentHistory, internal Appointment IDs unnecessarily, capacity internals, raw schedules, raw TimeOff records, lock details or administrative data. The exact success identifier and lookup strategy require Discovery.

## Public Self-Service Boundary

Public cancellation, rescheduling and appointment lookup are out of V1. The public boundary must not reuse authenticated Admin Agenda mutation endpoints, and no public booking-management identifier or token is required by this Definition.

## Pricing, Payments and Notifications

Public Booking displays Service pricing information according to the authoritative `pricing_type` already defined by SPEC-003:

- `fixed`: exact configured Service price.
- `starting_from`: `Desde $X`, using the configured Service amount and not implying a guaranteed final price.
- `variable`: `Precio variable`, without inventing an amount, showing `$0` or presenting an arbitrary minimum.

Pricing is informational only. It creates no payment obligation and does not turn booking into checkout. Payments, deposits and checkout are out of scope.

The required success experience is on-screen only. WhatsApp, email, SMS, reminders and notification jobs are out of scope.

Pricing text must be understandable without relying only on color or styling to distinguish the three pricing types.

## Security and Abuse Goals

- Public routes must not expose the administrative Sanctum boundary or admin data.
- Customer and Appointment enumeration must be prevented or bounded.
- Server-side validation and SPEC-004 authority must govern every booking submission.
- Conflict messages must be safe and must not disclose SQL, locks, internal classes or persistence details.
- The public flow must address duplicate submits, appointment spam, availability scraping and high-rate availability requests.
- Rate limits, abuse controls, CSRF/session behavior and idempotency follow the finalized Technical Discovery contract and use no new vendor or mechanism.
- No bearer-token or browser-storage authentication is introduced by this Definition.

## Mobile and Accessibility Goals

- Mobile-first booking from a phone-sized viewport.
- Keyboard-accessible controls and semantic forms.
- Clear field errors and non-color-only status communication.
- Touch-friendly Service, Professional and date/time selection.
- Accessible loading, conflict, success and unavailable states.
- Public time presentation must use the inherited BusinessProfile timezone.

## Functional Requirements

### FR-01 Public access

An unauthenticated visitor can reach the public booking entry without an administrative login.

### FR-02 Service selection

The visitor can inspect and select an active Service through a bounded public presentation.

### FR-03 Professional selection

The visitor selects one specific Professional; the flow does not offer any-Professional or server-selected assignment.

### FR-04 Bookable time selection

The visitor can select a time that the approved public booking flow presents as bookable.

### FR-05 Customer contact

The flow requires `name` and `phone` and does not require a Customer account.

### FR-06 Explicit submission

The visitor explicitly reviews and submits the booking.

### FR-07 Authoritative appointment creation

A valid submission resolves or creates the minimal Customer and immediately invokes `CreateAppointment`, producing a confirmed Appointment without direct persistence.

### FR-08 Duration integrity

The visitor cannot control authoritative appointment duration; Service duration remains the domain authority.

### FR-09 Safe outcomes

The flow presents success, validation, unavailability, stale-state, abuse and unexpected-failure outcomes without internal details.

### FR-10 Admin integration

A successfully created confirmed Appointment is available to the existing Admin Agenda read consumer.

### FR-11 Timezone correctness

Public date/time presentation uses the authoritative BusinessProfile timezone and does not rely on browser timezone authority.

### FR-12 Data minimization

Public responses expose only approved Service, Professional, business-time and safe booking-result data.

### FR-13 Duplicate safety

The public flow prevents or safely handles accidental repeated submission without creating duplicate appointments.

### FR-14 Abuse resistance

Public availability and submission behavior is bounded against enumeration, scraping and high-rate abuse.

### FR-15 Accessible operation

The public booking flow is operable with keyboard and assistive technology, including validation and status feedback.

### FR-16 Responsive operation

The flow remains usable on mobile, tablet and desktop without requiring a separate product.

### FR-17 Pricing presentation

The public flow presents existing Service pricing information as exact price for `fixed`, `Desde $X` for `starting_from`, and `Precio variable` for `variable`, without inventing amounts. Pricing is informational only and does not add payment or checkout behavior.

## Non-Functional Requirements

- Backend authority: all eligibility, availability, duration, lifecycle and persistence decisions are server/domain-owned.
- Privacy: public Customer and Appointment data is minimized and non-enumerable.
- Security: public authentication, CSRF/session, rate limits, abuse controls and duplicate-submission strategy are explicitly discovered before development.
- Temporal correctness: BusinessProfile timezone, UTC instants and inherited DST behavior are preserved.
- Concurrency safety: booking races inherit SPEC-004 transaction and locking behavior; SPEC-006 adds no competing concurrency model.
- Performance: public reads and availability interactions must be bounded and assessed with representative volume during Discovery.
- Accessibility and responsive design are acceptance concerns, not implementation-library decisions.

Functional requirement count: `17`.

## Acceptance Criteria

1. The Definition identifies SPEC-006 as roadmap item 06 Public Booking and records its business purpose.
2. The Definition identifies public visitors, Customers, Professionals and Yaris administration as distinct actors/boundaries.
3. The Definition preserves SPEC-003 authority for Core entities and does not introduce Customer or Professional authentication.
4. The Definition preserves SPEC-004 authority for availability, duration, status, history, capacity, timezone/DST, locking and concurrency.
5. The Definition identifies the Admin Agenda as the downstream consumer without expanding its scope.
6. The Definition freezes guest booking, required name/phone and Customer create/reuse without Customer authentication.
7. The Definition requires a specific Professional and excludes any-Professional selection and auto-assignment.
8. The Definition requires bookable-time selection without specifying the slot algorithm.
9. The Definition freezes immediate `confirmed` creation through `CreateAppointment` and introduces no new Appointment status.
10. The Definition preserves SPEC-004 lifecycle authority and requires no lifecycle change to SPEC-004.
11. The Definition defines public data minimization and prevents Customer/Appointment enumeration.
12. The Definition excludes public cancellation, rescheduling and appointment lookup from V1.
13. The Definition keeps payments, deposits and outbound notifications out of V1 and requires on-screen confirmation.
14. The Definition identifies security, abuse, rate-limit and duplicate-submission requirements without selecting mechanisms.
15. The Definition freezes public Service pricing presentation: exact configured price for `fixed`, `Desde $X` for `starting_from`, and `Precio variable` without an invented amount for `variable`.
16. The Definition keeps pricing informational and excludes payment, deposits and checkout.
17. Functional and non-functional requirements are business-observable, testable and implementation-independent, including mobile/accessibility goals.
18. Technical Discovery topics needed to resolve public API, availability, Customer resolution, security, UX, privacy, performance and testing are listed.
19. No routes, controllers, Actions, models, migrations, Vue implementation, tests or dependencies are introduced by this Definition.
20. SPEC-006 remains `DEFINITION COMPLETED / AWAITING HUMAN APPROVAL`; Technical Discovery and Development are not started.

## Open Questions

### Business

NONE. Public Service pricing visibility and presentation are finalized for V1.

### Architecture

See `docs/reports/SPEC-006-DISCOVERY-REPORT.md` for the finalized public API, availability, Customer, idempotency and session/CSRF contracts. No unresolved Architecture question remains for the current V1 boundary; checkpoint implementation must verify the documented transaction and performance evidence.

### UX

- What are the exact booking steps and review contents?
- How should specific Professional selection be presented?
- How should business-local date/time and unavailable outcomes be communicated?
- What safe appointment facts belong on success?
- Is the booking entry part of the future public site or a focused route within it?

### Security

The finalized security boundary requires non-enumerating responses, HMAC-protected phone limiter keys, bounded rate limits, safe PII projections and existing Laravel monitoring facilities. Implementation evidence is deferred to the approved Development checkpoints.

## Future Cross-SPEC Changes Requiring Human Approval

Current SPEC-006 V1 cross-SPEC changes required: `NONE`.

The following must not be resolved silently in SPEC-006:

- Any new `pending`, `requested`, `approved` or `rejected` Appointment status.
- Any change to SPEC-004 lifecycle, history, duration or concurrency contracts.
- Any future public Appointment ID, lookup token or self-service capability token.
- Any future automatic Professional assignment or “any available” orchestration.
- Any booking hold/reservation model.
- Any idempotency persistence or source field added to the Appointment domain.
- Any Customer uniqueness, deduplication or authentication change beyond the approved minimal create/reuse capability.

## Risks

- A displayed public option may become unavailable before submission; the flow must rely on authoritative revalidation.
- Anonymous submissions may create duplicate Customers if matching is not carefully defined.
- Availability reads may be scraped or abused.
- Public responses may leak Customer or Appointment information if projections are not minimized.
- Timezone/DST behavior may confuse visitors if business-local presentation is inconsistent.

## Technical Discovery Topics

The completed Discovery investigated the following topics without authorizing implementation:

- Existing public SPA/API conventions and the smallest `/api/v1/public/*` boundary.
- Public Service/Professional projections and active-state semantics.
- Availability/time-selection architecture using SPEC-004 authority.
- Customer matching, creation, privacy and duplicate behavior.
- CreateAppointment integration and public transport validation.
- Safe success response without requiring a public lookup identifier.
- Anonymous session, CSRF, rate limits, abuse controls and enumeration protection.
- Duplicate-submission/idempotency behavior and persistence impact.
- Business timezone, UTC transport and DST presentation.
- Success, conflict, unavailable and error semantics.
- Mobile flow, accessibility, keyboard behavior and responsive presentation.
- Query shape, bounded reads, N+1 risk, performance and representative EXPLAIN evidence.
- Backend, frontend, security, privacy, concurrency and integration test strategy.
- Dependency impact; no dependency is approved by this Definition.

## Explicit Scope Audit

This Definition introduces no application implementation, routes, controllers, Actions, Vue components, migrations, tests, dependencies, production data, Customer CRUD, schedule administration, notification delivery, payment behavior or SPEC-007+ work.

## Technical Discovery Reference

Technical Discovery is documented in `docs/reports/SPEC-006-DISCOVERY-REPORT.md`. The report defines the final public contracts, authority boundaries, Customer resolution, availability strategy, abuse controls and Development checkpoint plan. It does not implement or authorize code.

## Definition State

```text
SPEC-006: APPROVED FOR DEVELOPMENT / IN PROGRESS
Canonical name: Public Booking
Roadmap item: 06
Definition: COMPLETED / APPROVED
Technical Discovery: COMPLETED / APPROVED
Development: CHECKPOINTS A-D
Checkpoint A: COMPLETED / APPROVED
Checkpoint B: COMPLETED / APPROVED
Checkpoint C: COMPLETED / APPROVED
Checkpoint D: COMPLETED / APPROVED
Checkpoint E: COMPLETED / READY FOR HUMAN APPROVAL
Checkpoint F: NOT AUTHORIZED
SPEC-007+: NOT AUTHORIZED
```
