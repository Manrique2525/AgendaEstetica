# SPEC-006 - Public Booking

## SPEC ID

`SPEC-006`

## Title

Public Booking

## Status

`DEFINITION COMPLETED / AWAITING HUMAN APPROVAL`

This Definition describes the public booking consumer identified as roadmap item `06`. It does not authorize Technical Discovery, Development, Checkpoint A, migrations, routes, dependencies or implementation code.

## Roadmap Source

The provisional roadmap places `06. Public booking` immediately after `05. Admin agenda` and before the Notification Engine. SPEC-003, SPEC-004 and SPEC-005 are closed and remain authoritative for their respective domains and consumers.

## Business Problem

A customer should be able to request or book an appointment through a public experience without authenticating as an internal administrator. The public consumer must make a safe appointment request understandable on a phone while preserving the Appointment Engine as the only authority for availability, duration, status, capacity, history and concurrency.

## Purpose

Define the business capability and boundaries for a focused Public Booking consumer. The eventual consumer may let a visitor select an eligible service and booking interval, provide the minimum customer contact information, submit the request and receive a clear result. It must consume SPEC-003 and SPEC-004 rather than create a second booking or availability engine.

## Actors

- Public visitor or customer without an application account in V1.
- Customer domain record, representing the operational identity used by an appointment.
- Professional, an operational bookable resource and not an authenticated User.
- Yaris administrator, the downstream operational user who sees resulting appointments in Admin Agenda.

`Professional != User` and `Customer != internal User`. This Definition does not introduce customer or professional login.

## Goals

- Let a public visitor understand available Services without exposing internal administration data.
- Let the visitor select a valid booking option through a future approved public flow.
- Collect only the minimum Customer identity/contact data required for an appointment.
- Submit a booking request safely and provide clear success, validation and conflict outcomes.
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
- Automatic Professional assignment unless separately approved.
- Public customer cancellation, rescheduling or appointment lookup by default.
- New Appointment states, public IDs, booking holds or persistence changes without cross-SPEC approval.
- SPEC-007 or any later SPEC.

## In-Scope Business Workflows

- Public booking entry from the public product surface.
- Active Service/category discovery and selection.
- Professional choice when the approved business flow requires a specific Professional.
- Date/time or bookable-option selection using future public availability behavior.
- Minimum Customer identity/contact capture.
- Review and explicit booking submission.
- On-screen success confirmation with the resulting appointment facts that are safe to disclose.
- Safe validation, unavailable, stale-state, abuse and unexpected-failure presentation.
- Downstream visibility of the created appointment in Admin Agenda.

These are business workflows, not authorization for routes, controllers, Vue components or persistence design.

## Out-of-Scope Workflows

- Administrative Appointment operations already owned by SPEC-005.
- Public cancellation, public rescheduling and public appointment lookup unless a later decision adds them.
- Customer profile management or account lifecycle.
- Professional management or automatic assignment.
- Schedule, TimeOff and BusinessHours administration.
- Notification delivery, payments, deposits and ecommerce.

## Domain Ownership and Dependencies

### SPEC-003 dependencies

SPEC-006 may consume active ServiceCategory, Service, Professional, ProfessionalService, BusinessProfile and business configuration, plus Customer identity. SPEC-003 remains authoritative for those concepts, validation, lifecycle and privacy boundaries. Customer data is not a public directory and Core fields are not automatically public-readable or publicly writable.

Candidate public-readable data is limited to active Service/category presentation, approved duration and pricing presentation, eligible Professional display data, and the business-local date/time context needed for booking. Customer records, normalized phones and internal configuration remain non-public. Public Customer creation/matching is an open Definition decision, not an implementation decision.

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

The likely V1 direction is guest booking without Customer authentication. The visitor must provide the minimum operational identity/contact data, currently expected to be `name` and `phone`; no address, birth date, gender, email, notes or marketing profile is invented here.

The following remain decisions for Technical Discovery and human approval:

- Whether the public flow always creates a Customer or can recognize an existing one.
- How matching and duplicate Customers work while `phone_normalized` remains indexed but non-unique.
- Whether a public visitor may create a Customer record directly through the booking boundary.
- What consent/contact metadata, if any, is needed for future notifications.

Customer authentication remains out of V1 unless explicitly approved.

## Availability and Professional Selection

The public user needs a clear way to select a valid bookable time before submission; whether this is a slot list or another representation is a business requirement to confirm and a Technical Discovery topic. No slot algorithm is defined here.

The current Appointment Engine evaluates a specific Professional. V1 should prefer a specific Professional selection unless the business explicitly chooses `cualquiera disponible`. Any-compatible selection, ranking or automatic assignment is a cross-SPEC decision because it may require new orchestration beyond the current Action contract.

Service duration is displayed as information only. The visitor cannot provide or override `duration_minutes`.

## Appointment Status and Lifecycle

No new status is proposed by this Definition. The current CreateAppointment behavior produces `confirmed`; whether public submission should immediately create a confirmed Appointment or first represent a request requiring administrative approval is an explicit Business open question.

If approval is required, introducing `pending`, `requested`, `approved` or `rejected` would affect the closed SPEC-004 lifecycle and is a `CROSS-SPEC DECISION REQUIRING HUMAN APPROVAL`. SPEC-006 must not invent or persist such a status.

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

Public cancellation, rescheduling and appointment lookup are not included in V1 by default. Exposing those capabilities would require a separate public authorization/identifier decision and must not reuse authenticated Admin Agenda mutation endpoints. No public ID or token strategy is chosen here.

## Pricing, Payments and Notifications

Public Booking may present approved Service pricing semantics if the public product requires it, without inventing prices or turning booking into checkout. Payments and deposits remain deferred.

The minimum success experience is on-screen. WhatsApp, email, SMS, reminders and notification jobs remain outside SPEC-006 unless the roadmap or a later approved scope explicitly assigns them.

## Security and Abuse Goals

- Public routes must not expose the administrative Sanctum boundary or admin data.
- Customer and Appointment enumeration must be prevented or bounded.
- Server-side validation and SPEC-004 authority must govern every booking submission.
- Conflict messages must be safe and must not disclose SQL, locks, internal classes or persistence details.
- The public flow must address duplicate submits, appointment spam, availability scraping and high-rate availability requests.
- Rate-limit values, abuse controls, CSRF/session behavior and idempotency strategy require Technical Discovery; no vendor or mechanism is selected here.
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

The flow supports the approved Professional-selection behavior, without implying automatic assignment.

### FR-04 Bookable time selection

The visitor can select a time that the approved public booking flow presents as bookable.

### FR-05 Customer contact

The flow collects the minimum approved Customer identity/contact fields and does not require an account by default.

### FR-06 Explicit submission

The visitor explicitly reviews and submits the booking request.

### FR-07 Authoritative appointment creation

A valid submission is processed through the authoritative SPEC-004 operation and never through direct persistence.

### FR-08 Duration integrity

The visitor cannot control authoritative appointment duration; Service duration remains the domain authority.

### FR-09 Safe outcomes

The flow presents success, validation, unavailability, stale-state, abuse and unexpected-failure outcomes without internal details.

### FR-10 Admin integration

A successfully created Appointment is available to the existing Admin Agenda read consumer.

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

## Non-Functional Requirements

- Backend authority: all eligibility, availability, duration, lifecycle and persistence decisions are server/domain-owned.
- Privacy: public Customer and Appointment data is minimized and non-enumerable.
- Security: public authentication, CSRF/session, rate limits, abuse controls and duplicate-submission strategy are explicitly discovered before development.
- Temporal correctness: BusinessProfile timezone, UTC instants and inherited DST behavior are preserved.
- Concurrency safety: booking races inherit SPEC-004 transaction and locking behavior; SPEC-006 adds no competing concurrency model.
- Performance: public reads and availability interactions must be bounded and assessed with representative volume during Discovery.
- Accessibility and responsive design are acceptance concerns, not implementation-library decisions.

## Acceptance Criteria

1. The Definition identifies SPEC-006 as roadmap item 06 Public Booking and records its business purpose.
2. The Definition identifies public visitors, Customers, Professionals and Yaris administration as distinct actors/boundaries.
3. The Definition preserves SPEC-003 authority for Core entities and does not introduce Customer or Professional authentication.
4. The Definition preserves SPEC-004 authority for availability, duration, status, history, capacity, timezone/DST, locking and concurrency.
5. The Definition identifies the Admin Agenda as the downstream consumer without expanding its scope.
6. The Definition states the likely guest-booking direction and leaves Customer matching/creation decisions explicit.
7. The Definition identifies public time selection as a business requirement without specifying a slot algorithm.
8. The Definition does not invent a new Appointment status or silently alter the closed lifecycle.
9. The Definition defines public data minimization and prevents Customer/Appointment enumeration as a requirement.
10. The Definition keeps public cancellation, rescheduling and lookup out of V1 unless separately approved.
11. The Definition keeps payments, deposits, notifications, CMS, ecommerce and later modules deferred.
12. The Definition identifies security, abuse, rate-limit and duplicate-submission requirements without selecting implementation mechanisms.
13. The Definition includes mobile and accessibility goals.
14. Functional and non-functional requirements are business-observable, testable and implementation-independent.
15. Technical Discovery topics needed to resolve public API, availability, identity, security, UX, privacy, performance and testing are listed.
16. No routes, controllers, Actions, models, migrations, Vue implementation, tests or dependencies are introduced by this Definition.
17. No production data, prices, people, photos, testimonials or policies are invented.
18. SPEC-006 remains `DEFINITION COMPLETED / AWAITING HUMAN APPROVAL`; Technical Discovery and Development are not started.

## Open Questions

### Business

- Is V1 strictly guest booking without Customer login?
- Does public submission create `confirmed` immediately or require administrative approval?
- Can a visitor select a specific Professional only, or is `cualquiera disponible` required?
- Are public cancellation, rescheduling or appointment lookup needed in V1?
- Which Customer fields are mandatory, and is phone the primary operational contact?
- Should public booking create a Customer, match an existing Customer, or use another identity boundary?
- Is public Service pricing presentation required, and are deposits explicitly deferred?
- Is on-screen confirmation sufficient without notifications?

### Architecture

- What public API/read boundary is needed for Services, Professionals and bookable times?
- Does public availability require a new consumer query over SPEC-004 or an approved Action extension?
- What public identifier or capability token is needed, if any, without exposing internal Appointment IDs?
- What Customer matching/creation strategy preserves the non-unique normalized phone contract?
- What duplicate-submit/idempotency strategy is appropriate without prematurely modifying SPEC-004 persistence?
- What public session/CSRF boundary applies to anonymous same-origin booking?

### UX

- What are the exact booking steps and review contents?
- How should Professional selection and “any available” be presented if approved?
- How should business-local date/time and unavailable outcomes be communicated?
- What safe appointment facts belong on success?
- Is the booking entry part of the future public site or a focused route within it?

### Security

- Which controls prevent Customer and Appointment enumeration?
- What rate-limit and abuse policy is needed for availability and submit requests?
- What PII may appear in success and error responses?
- Is any public access token required for future self-service?
- What operational monitoring is needed without introducing external providers?

## Cross-SPEC Decisions Requiring Human Approval

The following must not be resolved silently in SPEC-006:

- Any new `pending`, `requested`, `approved` or `rejected` Appointment status.
- Any change to SPEC-004 lifecycle, history, duration or concurrency contracts.
- Any public Appointment ID, lookup token or self-service capability token.
- Any automatic Professional assignment or “any available” orchestration.
- Any booking hold/reservation model.
- Any idempotency persistence or source field added to the Appointment domain.
- Any Customer uniqueness, deduplication or authentication change.

## Risks

- A displayed public option may become unavailable before submission; the flow must rely on authoritative revalidation.
- Anonymous submissions may create duplicate Customers if matching is not carefully defined.
- Availability reads may be scraped or abused.
- Public responses may leak Customer or Appointment information if projections are not minimized.
- Timezone/DST behavior may confuse visitors if business-local presentation is inconsistent.
- Approval requirements could conflict with SPEC-004's current immediate `confirmed` creation behavior.
- “Any Professional” could require new domain orchestration and alter the current specific-Professional contract.

## Technical Discovery Requirements

Future Discovery must investigate, without assuming a solution:

- Existing public SPA/API conventions and the smallest `/api/v1/public/*` boundary.
- Public Service/Professional projections and active-state semantics.
- Availability/time-selection architecture using SPEC-004 authority.
- Customer matching, creation, privacy and duplicate behavior.
- CreateAppointment integration and public transport validation.
- Confirmed-versus-approval business decision and cross-SPEC impact.
- Public identifiers and any future self-service authorization.
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

## Definition State

```text
SPEC-006: DEFINITION COMPLETED / AWAITING HUMAN APPROVAL
Canonical name: Public Booking
Roadmap item: 06
Definition: COMPLETED
Technical Discovery: NOT STARTED
Development: NOT STARTED
Checkpoint A: NOT STARTED
SPEC-007+: NOT AUTHORIZED
```
