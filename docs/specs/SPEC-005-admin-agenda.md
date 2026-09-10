# SPEC-005 - Admin Agenda

## SPEC ID

`SPEC-005`

## Title

Admin Agenda

## Status

`IMPLEMENTATION COMPLETED / AWAITING FINAL HUMAN ACCEPTANCE`

This document defines the approved Admin Agenda consumer from roadmap item 05. Technical Discovery is complete and Checkpoints A-E are implemented; formal Closure and merge require separate approval.

## Roadmap Source

The approved provisional roadmap identifies item `05. Admin agenda` immediately after `04. Appointment engine` and before Public Booking, Notification Engine, CMS and ecommerce. SPEC-004 is closed and provides the authoritative Appointment Engine consumed by this SPEC.

## Purpose

Provide an internal administrative surface for Yaris to understand and operate the salon agenda using the already-approved Core and Appointment Engine contracts.

The Admin Agenda is a consumer of backend/domain capabilities. It must not become a second availability engine, a second appointment state machine or a replacement for SPEC-004 Actions.

## Business Problem

After the Appointment Engine is available, the administrator needs a controlled operational view of appointments and resource availability. Without an Admin Agenda consumer, appointment records and lifecycle operations remain technically available but are not organized into an internal workflow for daily salon operation.

## Actors

### Primary administrator

Yaris, authenticated through the existing administrative Sanctum session, can view agenda information and perform explicitly approved administrative operations.

### Future administrators

Future additional administrator accounts remain subject to a separate authorization/permission decision. This Definition does not create roles or permissions.

### Professionals

Professionals remain operational resources, not authenticated users. This SPEC does not create Professional login or a Professional portal.

### Customers

Customers remain business identities without application accounts. Customer information is displayed only where required for an authenticated administrative operation and must follow data minimization.

## Goals

- Give the administrator a reliable agenda view backed by persisted Appointments.
- Make appointment status, time, Service, Customer and Professional context understandable.
- Expose only availability and lifecycle decisions authorized by SPEC-004.
- Preserve AppointmentHistory for every mutation.
- Keep backend validation and concurrency authoritative.
- Make schedule and TimeOff context understandable without duplicating their rules.
- Provide safe loading, empty, validation, conflict and authorization states.

## Non-Goals

- Public Booking.
- Customer self-service or customer accounts.
- Professional accounts or a Professional portal.
- A second availability, capacity or status engine.
- Candidate-slot generation as a new domain concept.
- Automatic Professional selection or ranking.
- Notifications, WhatsApp, email or reminders.
- Deposits, payments, refunds or financial workflows.
- BusinessSpecialHours, holiday calendars or buffers.
- ProfessionalSchedule CRUD or ProfessionalTimeOff CRUD/approval workflows.
- CMS, ecommerce, inventory, checkout or orders.
- Production business data or invented catalog data.
- SPEC-006 or any later SPEC.

## Proposed Scope

This scope is `PROPOSED / REQUIRES HUMAN APPROVAL`. It is based only on the roadmap and the closed SPEC-003/SPEC-004 contracts.

### Agenda view

- Show persisted appointments in an administrative day/week-oriented view.
- Represent the selected date or date range using the business timezone at the consumer boundary.
- Distinguish `confirmed`, `cancelled`, `completed` and `no_show` appointments.
- Make Professional assignment and temporal boundaries visible.
- Show empty and unavailable states without inventing appointments or availability.

### Agenda filtering and navigation

- Navigate approved date ranges.
- Filter by Professional.
- Filter by approved Appointment status.
- Filter by Service or ServiceCategory when useful for the approved consumer workflow.
- Search or identify a Customer only within the authenticated administrative boundary and only when a concrete UX decision justifies it.
- Preserve server authority for all filter semantics and resource visibility.

### Appointment detail

- Show Customer, Service, Professional, UTC-backed temporal values rendered in the business timezone and duration snapshot.
- Show focused AppointmentHistory events in chronological order.
- Show the distinction between current Service data and the historical Appointment duration.
- Do not present deferred price, notes, source, public ID or unsupported status data.

### Appointment operations

The proposed administrative workflow includes initiating an appointment creation request. The authenticated administrator must invoke the authoritative Action; no direct Appointment write is allowed.

The consumer may expose only approved operations whose UX and authorization are confirmed during Discovery:

- Create a confirmed Appointment through `CreateAppointment`.
- Reschedule through `RescheduleAppointment`.
- Cancel through `CancelAppointment`.
- Complete through `CompleteAppointment`.
- Mark no-show through `MarkAppointmentNoShow`.

The consumer must never write Appointment or AppointmentHistory directly. Availability, status, duration, capacity, locking and history remain delegated to SPEC-004.

### Resource context

- Present existing ProfessionalSchedule, ProfessionalTimeOff and BusinessHours context where it helps explain an unavailable interval.
- Schedule and TimeOff administration remain deferred; this consumer does not create, edit, delete or approve those records.
- Treat the Appointment Engine as authoritative for availability outcomes.
- Do not expose raw database locking, internal exception details or implementation-specific query state.

## Domain Boundaries

### Owned by SPEC-003

- BusinessProfile and business configuration.
- BusinessHours.
- ServiceCategory and Service.
- Professional and ProfessionalService.
- Customer identity.

### Owned by SPEC-004

- Appointment and AppointmentHistory persistence.
- Appointment statuses and transitions.
- ProfessionalSchedule and ProfessionalTimeOff domain data.
- Requested-interval availability.
- Professional overlap and global capacity.
- Duration snapshot and historical integrity.
- Transaction boundaries, lock order and concurrency behavior.

### Owned by SPEC-005

- Authenticated administrative agenda presentation.
- Administrative navigation/filtering/detail workflows.
- Consumer-level loading, empty, validation, conflict and authorization presentation.
- Consumer authorization decisions only after an approved permission boundary exists.

### Deferred to later scopes

- Public Booking.
- Notification Engine.
- Payments/deposits.
- CMS, ecommerce and reporting.
- Professional self-service.
- ProfessionalSchedule and ProfessionalTimeOff administrative management.

## Dependencies

- SPEC-003 Business Core is closed and authoritative for Core concepts.
- SPEC-004 Appointment Engine is closed and authoritative for appointment behavior.
- Existing Foundation admin Sanctum session and same-origin SPA boundary.
- Existing Vue, TypeScript, Tailwind and Vite frontend foundation.
- Existing Laravel modular monolith and `/api/v1` conventions.
- MySQL 8.4 persistence and dedicated testing database.

## Functional Requirements

### FR-01 Authenticated access

The Admin Agenda is available only inside the existing administrative authentication boundary. Unauthenticated access must remain a safe JSON `401` at any future API boundary and must not expose agenda data.

### FR-02 Authoritative appointment reads

Agenda reads must use backend-owned queries/Actions and must not infer appointment state from frontend-only state.

### FR-03 Date and timezone handling

`BusinessProfile.timezone` is the inherited business-timezone authority. The consumer must define how a business-local date/range is converted to the UTC query boundary; concrete appointments remain UTC instants.

### FR-04 Appointment presentation

The user must be able to identify the selected date/range, Professional, Service, Customer context, duration snapshot and status for each displayed appointment.

### FR-05 History visibility

Appointment details must expose the focused history needed to understand creation, rescheduling and terminal transitions without creating a generic audit log.

### FR-06 Approved mutations

Any enabled mutation must invoke the corresponding SPEC-004 Action and must preserve its transaction, validation, capacity, locking and history behavior.

### FR-07 Conflict handling

The consumer must present domain unavailability, stale state, validation, authorization and unexpected failure states distinctly enough for an administrator to act safely without receiving internal implementation details.

### FR-08 No direct persistence

Controllers, API Resources and Vue components must not contain appointment business rules or direct Appointment/AppointmentHistory writes.

### FR-09 Resource context

Where existing ProfessionalSchedule or ProfessionalTimeOff context is presented, it must be read from authoritative backend models and must not become an alternate availability calculation or administration workflow.

### FR-10 Data minimization

Only Customer and operational fields required by an authenticated agenda workflow may be exposed. No public representation is implied.

### FR-11 Responsive operation

The approved consumer must remain usable on mobile, tablet and desktop ranges without requiring a separate mobile product or new UI framework.

### FR-12 Accessibility

The consumer must provide semantic navigation, keyboard operation, visible focus, status announcements where needed, usable error association and reduced-motion behavior consistent with the closed design system.

## Security Boundary

- Existing Sanctum same-origin authentication remains the entry boundary.
- Authorization must be backend-owned.
- Any permission/role expansion requires a separate approved decision; this Definition does not add roles.
- Agenda data must not be exposed through public routes.
- Errors must not disclose SQL, stack traces, credentials, internal IDs beyond the approved administrative contract or unrelated Customer data.
- No browser storage token authentication is introduced.

## API/UI Boundary

Technical Discovery defines the `/api/v1/admin/agenda/*` consumer boundary, explicit intent endpoints, lookup endpoints and `/admin/agenda` plus `/admin/agenda/:id` routes. This Definition does not authorize implementation, migrations, controllers, Resources or Vue components.

If a later Discovery confirms an API is needed, it must use `/api/v1/admin/*`, preserve the existing error contract and delegate business behavior to Actions/domain services.

## Data Ownership

- Appointment facts: SPEC-004.
- Core identity/catalog/resource facts: SPEC-003.
- Authentication/session: Foundation.
- Agenda presentation/filter state: SPEC-005 consumer.
- No duplicated authoritative appointment, capacity, duration or status data is owned by SPEC-005.

## Concurrency and Transactions

Read views may become stale and must communicate that limitation where relevant. Mutations must reuse SPEC-004 Actions and therefore inherit:

- BusinessProfile-first locking.
- Professional IDs ascending.
- Appointment locking when applicable.
- Post-lock current reads and authoritative revalidation.
- Atomic Appointment/History writes.

SPEC-005 must not introduce a second retry, locking, reservation or capacity abstraction.

## Discovery Decisions

- Implementation-level endpoint signatures and request/Resource contracts.
- Read query implementation and pagination if future volume requires it.
- Authorization changes if multiple administrators are introduced; V1 uses the authenticated internal User boundary.
- Loading/cache strategy and invalidation details.
- Query/index confirmation through Development EXPLAIN evidence.
- Exact copy and component details for conflicts and confirmations.

## Human Approval Points

- Approve day plus bounded list/range as V1 views.
- Approve existing-Customer-only creation and minimal Customer projections.
- Approve exposure of the five existing SPEC-004 Actions.
- Approve 24-hour display and route-addressable detail.
- Record retention/anonymization as deferred.

## Architecture Approval Points

- Approve the `/api/v1/admin/agenda/*` boundary and exact endpoint set.
- Approve the 31-calendar-day range guard.
- Approve authenticated-only access with no new RBAC in V1.

## UX Approval Points

- Approve mobile day/list interaction and `/admin/agenda/:id` detail route.
- Approve 24-hour display convention.
- Approve conflict and terminal-action confirmation behavior.

## Security Approval Points

- Approve the minimum Customer projection and phone placement.
- Approve no additional Policy/Gate or actor audit in V1.

## Acceptance Criteria

1. The Admin Agenda Definition records the canonical roadmap name and position for human approval.
2. The Definition clearly distinguishes SPEC-003 ownership, SPEC-004 ownership and SPEC-005 consumer ownership.
3. The Definition does not introduce API, UI, migration, dependency or production-code implementation.
4. Appointment reads and mutations are explicitly delegated to the authoritative SPEC-004 boundary.
5. The Definition does not duplicate availability, capacity, status, duration, locking or history rules.
6. Actors and authenticated administrative boundaries are documented.
7. Primary agenda, filtering, detail and mutation workflows are stated as proposed functional scope.
8. Customer and operational data minimization boundaries are explicit.
9. Deferred workflows and later modules are explicitly listed.
10. Security, API/UI, data ownership and concurrency boundaries are documented.
11. Business, architecture, UX and security open questions are separated.
12. Technical Discovery topics required to resolve HOW are listed.
13. No real business data, prices, people, images, policies or testimonials are invented.
14. The Definition has observable, testable Acceptance Criteria for its eventual implementation.
15. SPEC-005 remains `TECHNICAL DISCOVERY COMPLETED / AWAITING HUMAN APPROVAL` and development is not authorized.

## Technical Discovery Requirements

If human approval is granted, a separate Technical Discovery must investigate:

- Current API/SPA consumer conventions and the smallest Admin Agenda boundary.
- Authenticated authorization requirements.
- Read query shape, date/timezone boundaries, filters and pagination.
- Appointment detail/history representation.
- Mutation form contracts and Action integration.
- Stale-read/conflict UX and response mapping.
- Schedule/TimeOff presentation ownership.
- MySQL indexes and representative EXPLAIN plans.
- Responsive/accessibility interaction design.
- Security/privacy review for administrative Customer data.
- Test strategy for reads, mutations, authorization, conflicts and responsive frontend behavior.
- Dependency impact if a calendar visualization is proposed.

## Definition State

```text
SPEC-005: APPROVED FOR DEVELOPMENT / IN PROGRESS
Definition: APPROVED
Technical Discovery: COMPLETED
Development: CHECKPOINTS A-E
SPEC-004: CLOSED
Checkpoint A: COMPLETED / APPROVED
Checkpoint B: COMPLETED
Checkpoint C: COMPLETED
Checkpoint D: COMPLETED / APPROVED
Checkpoint E: COMPLETED / READY FOR HUMAN APPROVAL
Closure: NOT AUTHORIZED
Merge: NOT AUTHORIZED
SPEC-006+: NOT STARTED
```
