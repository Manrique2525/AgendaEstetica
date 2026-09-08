# SPEC-004 - Appointment Engine

## SPEC ID

`SPEC-004`

## Title

Appointment Engine

## Status

`READY FOR DEVELOPMENT APPROVAL`

Technical Discovery is complete. This document defines the recommended development scope; implementation, migrations, models, enums, Actions, APIs, frontend work and SPEC-005 remain unauthorized until separate human approval.

## Objective

Define the backend/domain engine that can determine whether appointments may exist safely, using the Core contracts delivered by SPEC-003. The engine must establish the domain model, availability rules, resource constraints, capacity semantics, state transitions, history and concurrency strategy required by later Admin Agenda and Public Booking consumers.

The objective is not to build a calendar UI, public booking website, API consumer, notification integration or payment system.

## Context

SPEC-001 and SPEC-002 are closed. SPEC-003 is closed and provides the business profile, weekly business hours, services, professionals, compatibility relationship, customers, pricing semantics and IANA timezone validation. The roadmap places Appointment Engine immediately after Business Core, followed by Admin Agenda, Public Booking, notifications, CMS and ecommerce.

Business Rules already confirm that appointments require approval, professional overlaps are forbidden, availability must respect business hours, professional constraints, duration, blocks and global capacity, and the backend recalculates availability before accepting an operation. The exact appointment persistence, temporal, state, snapshot and concurrency decisions remain to be discovered.

## Problem

Later consumers need one authoritative engine instead of duplicating rules for:

- Appointment existence and historical integrity.
- Service duration and resource compatibility.
- Business and professional schedule constraints.
- Professional overlap prevention.
- Global simultaneous-client capacity.
- Approval, confirmation, completion, cancellation and rescheduling transitions.
- Concurrent create/reschedule operations.

Without an explicit engine boundary, Admin Agenda and Public Booking could implement conflicting availability and state behavior.

## Dependencies

### Normative dependencies

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
- `docs/specs/SPEC-003-business-core.md`.
- `docs/reports/SPEC-003-DISCOVERY-REPORT.md`.
- `docs/reports/SPEC-003-IMPLEMENTATION-REPORT.md`.
- `docs/reports/SPEC-003-CLOSURE-REPORT.md`.

### Technical baseline

- Laravel 13 and PHP 8.3+.
- MySQL 8.4 LTS.
- Existing Laravel modular monolith and API conventions.
- Existing Sanctum administration boundary.
- Pest, Pint, Larastan, Vitest, ESLint, TypeScript and GitHub Actions quality CI.

## Inputs from SPEC-003

Appointment Engine consumes and does not duplicate:

- `BusinessProfile.timezone`.
- `BusinessProfile.max_simultaneous_clients`.
- `BusinessHour` weekly intervals.
- `Service.duration_minutes`, `pricing_type`, `price` and `active`.
- `Professional.active`.
- `ProfessionalService` compatibility.
- `Customer` identity and contact fields.

The exact business timezone, production profile, real services, professionals, prices and schedules remain business-data pending.

## In Scope

### Appointment domain

- Appointment record and references to Customer, Service and Professional.
- Appointment temporal boundaries and duration usage.
- Appointment status model using PHP backed string enum semantics.
- Controlled state transitions and appointment history.
- Approval, confirmation, completion, cancellation, no-show and rescheduling operations.
- Historical rescheduling semantics without approving a `schedule_version` field by default.

### Availability and resources

- Availability calculation/query behavior as backend/domain behavior.
- BusinessHours interaction.
- Professional recurring schedule.
- Professional time-off/unavailable intervals using the smallest adequate model.
- Service duration and ProfessionalService compatibility.
- Professional overlap prevention.
- Global simultaneous-client capacity enforcement.

### Persistence and consistency

- Appointment persistence model and historical snapshots where justified.
- Appointment history/auditability needed for state and schedule changes.
- Transaction boundaries for create, reschedule, cancellation and state changes where availability is affected.
- MySQL 8.4 integrity constraints, indexes and concurrency protection.
- Tests for valid, invalid, boundary, historical and concurrent operations.

## Explicitly Out of Scope

- Admin Agenda UI or CRUD screens.
- Public Booking UI or public API.
- Controllers, Form Requests, API Resources or HTTP routes unless a separate consumer decision explicitly moves a minimal contract into scope.
- Notifications, reminders, WhatsApp, email or webhooks.
- Deposit policy, payment gateways, payment webhooks and refunds.
- CMS, ecommerce, inventory, cart, checkout and orders.
- Customer accounts, passwords, login or deduplication.
- Production data and seeders.
- Generic repositories, CQRS, event bus, distributed locks or new dependencies.
- SPEC-005 or any later SPEC.

## Deferred Modules

- Admin Agenda: future administrative consumer.
- Public Booking: future public consumer and abuse/rate-limit boundary.
- Notification Engine: reminder eligibility, queueing and delivery.
- Payments: deposit collection and payment state integration.
- CMS and ecommerce: unrelated consumers.

## Appointment Entity Assessment

Appointment is proposed as the central stateful record. Candidate fields require Discovery confirmation:

- Customer reference.
- Service reference.
- Professional reference for the specific Professional evaluated by the initial Engine scope.
- UTC persisted start/end instants or an approved equivalent temporal representation.
- Status.
- Source, notes and snapshots only when a concrete consumer and privacy decision justify them.
- Standard timestamps.

An appointment should remain a historical record rather than a routinely deleted row. Cancellation and no-show should preserve history.

## Time Persistence Assessment

Technical application timezone remains UTC. Business wall-clock interpretation uses `BusinessProfile.timezone`. Discovery must decide whether the persisted representation is:

- UTC instants with business-local conversion at boundaries.
- A combination of UTC instants plus a business-local date/time snapshot.
- Another representation justified by DST and historical requirements.

The chosen model must handle DST gaps and ambiguous local times without silently accepting an impossible or duplicated instant.

## Duration Snapshot Assessment

Existing Service duration is authoritative for current availability. Historical duration representation is a `DEVELOPMENT-APPROVAL BLOCKER`: Discovery must decide whether an appointment stores effective duration/end data so later service edits cannot rewrite historical timing.

An appointment must not silently change duration when `Service.duration_minutes` changes.

## Pricing Snapshot Assessment

Appointment price is `DISCOVERY REQUIRED` but not automatically required for the Engine. Discovery must first show an Engine invariant that consumes an agreed/quoted amount, especially for `starting_from` and `variable` services. Appointment Engine must not become a sales/order model or integrate payments.

## Service and Professional Name Snapshot Assessment

Service-name and Professional-name snapshots are `DISCOVERY REQUIRED / LOWER PRIORITY`. Prefer Core references and current catalog/resource data unless historical or legal correctness proves a snapshot necessary. No duplicate display fields are approved by this draft.

## Appointment Status Assessment

DOMAIN_RULES currently lists a broader candidate vocabulary, but SPEC-004 does not approve every item as a persistent status:

```text
pending
confirmed
cancelled
completed
no_show
```

Discovery must confirm the final set, which statuses block capacity and professional availability, and the precise transition graph. Approval/request states such as `REQUEST_RECEIVED`, `PENDING_APPROVAL`, `APPROVED` and `REJECTED` remain workflow-dependent. `DEPOSIT_PENDING` is excluded; `RESCHEDULED` is treated as an operation/history event rather than an approved persistent status. Unknown strings must not be accepted.

## State Transition Assessment

State changes must be controlled by domain behavior and must preserve history. A cancelled appointment must not silently return to confirmed. Approval is required by current business context, but the exact approval workflow remains Discovery/business-rule pending. Cancellation windows and no-show rules remain business-rule pending.

## Appointment History Assessment

Discovery must choose between a focused AppointmentHistory model and another explicit history representation for:

- Creation.
- Status changes.
- Rescheduling.
- Professional/service changes.
- Cancellation and completion decisions.

This is not permission to create a generic audit-log subsystem.

## `schedule_version` Assessment

The master architecture references `schedule_version` for rescheduling/history, but this field is not approved by default. Discovery must first demonstrate:

- Owning record.
- Initial value.
- Increment events.
- Stale-read/concurrency problem solved.
- Relationship to history and availability checks.

If transactions and locking solve the stale-write problem without a version counter, Discovery should reject `schedule_version`. It is not an Acceptance Criterion in this draft.

## Professional Schedule Assessment

BusinessHours are global recurring business intervals and are not automatically professional availability. Professional recurring schedule is `IN SPEC-004`; Discovery must determine the smallest adequate model for:

- recurring weekly intervals;
- professional time-off/unavailable intervals;
- temporary blocks or date-specific exceptions only if the same minimal model can represent them.

Generic calendar recurrence engines and generic blocks tables are not approved by default.

## Professional Exception Assessment

Date-specific professional exceptions may be required to produce reliable availability. Discovery must define ownership, temporal representation, overlap rules, deletion/history behavior and whether exceptions belong in Appointment Engine or a later Admin Agenda scope.

## Business Special Hours Assessment

SPEC-003 deferred holidays and temporary business closures. Business special hours are `DEFERRED` from SPEC-004 V1; no `BusinessSpecialHours` table is approved by this draft. A future scope may extend availability explicitly.

## Availability Definition

The candidate definition of an available interval requires all approved conditions to hold:

- Within BusinessHours.
- Within the professional's approved schedule.
- Service and Professional are compatible.
- Service and Professional are active for new appointments.
- Duration fits the available interval.
- No professional overlap.
- No blocking time-off or schedule block.
- Global capacity is not exceeded.

The final condition set and statuses that block availability require Discovery confirmation.

## ServiceCategory Active Assessment

Whether an inactive ServiceCategory blocks new appointments while its Service remains active is `DISCOVERY REQUIRED`. No automatic service deactivation or compatibility mutation is approved.

## Professional Overlap Assessment

The candidate temporal rule is:

```text
startA < endB AND startB < endA
```

Adjacent intervals are allowed. The final persistence/concurrency strategy must prevent two concurrent writes from creating a professional conflict.

## Global Capacity Assessment

SPEC-003 owns `max_simultaneous_clients` configuration. SPEC-004 proposes enforcement when creating or changing appointments. Discovery must determine which statuses and temporal boundaries count as active clients, including pending, approved, confirmed, cancelled, completed and no-show states.

## Capacity Status Semantics

Capacity semantics are `DISCOVERY REQUIRED` and `BUSINESS-RULE PENDING`. No assumption that every stored appointment counts is authorized.

## Create Appointment Assessment

Create must validate Customer, Service, Professional compatibility, active state, duration, schedules, overlap, capacity, status and concurrency in the backend. It must not be implemented during Definition.

## Reschedule Assessment

Rescheduling belongs to the Engine only if it reuses the complete availability and capacity rules and preserves history. It must not assume a `schedule_version` field; exact stale-write handling, actors and policy are Discovery/business-rule decisions.

## Cancellation Assessment

Cancellation is a candidate controlled transition. No cancellation window or fee is invented. Existing cancellation policy is business-rule pending.

## Completion/No-show Assessment

Completion and no-show are candidate domain outcomes. Discovery must determine whether they are Engine transitions or later administrative workflows. They do not imply payment/order behavior.

## Deposit Policy Assessment

Deposit policy is `OUT OF SPEC-004`. The existing `DEPOSIT_PENDING` vocabulary is not part of the draft status baseline. Payments and deposit collection belong to a later approved scope.

## Payment Boundary

No payment gateway, payment webhook, refund or card/transfer verification is in scope.

## Slot Granularity Assessment

Slot granularity is `BUSINESS-RULE / DISCOVERY REQUIRED`. The draft does not assume 5, 10, 15 or 30 minute increments. Discovery must distinguish validating a requested start time from generating candidate slots and decide whether both belong to the Engine.

## Buffer Time Assessment

Buffer time is `OUT` because no approved requirement exists for cleanup or before/after-service buffers.

## Customer Relationship

Appointments reference Core Customer identity. No customer login, automatic merge, deduplication or duplicated name/phone fields are approved without a historical snapshot decision.

## Service Relationship

Appointments reference Service and consume current duration, pricing semantics and active state. Duration and price snapshot behavior is Discovery Required.

## Professional Relationship

Appointments reference Professional and rely on ProfessionalService compatibility. No copied compatibility list or professional authentication is approved.

## Any-Compatible Professional Assessment

Any-compatible-professional selection is `DEFERRED TO CONSUMER / FUTURE SCOPE`. SPEC-004 initially evaluates availability for a specific Professional. No automatic assignment, ranking or load balancing is approved.

## Appointment Source Assessment

Appointment source is `DEFERRED TO CONSUMER SPEC`. Admin, Public Booking and WhatsApp consumers may introduce source semantics later when a concrete need exists.

## Admin Agenda Boundary

Admin Agenda is a later consumer of Engine operations and availability. No admin calendar, CRUD UI or admin API is in scope.

## Public Booking Boundary

Public Booking is a later consumer. No public appointment/availability endpoint, account, rate limit or enumeration protection implementation is in scope for this Definition.

## Notifications/WhatsApp Boundary

Notifications may consume eligible appointment state later. No reminders, messages, templates, WhatsApp integration or webhooks are in scope.

## Ecommerce/CMS Boundary

No ecommerce, product, order, payment, CMS or marketing behavior is in scope.

## Database Impact

Potential new entities require Discovery:

- appointments.
- appointment history.
- professional recurring schedule/exception data if justified.
- minimal appointment constraints and indexes.

No migration, model or schema is authorized by this DRAFT.

## API Impact

No API is authorized. Engine should be domain-first; later Admin Agenda/Public Booking consumers must justify HTTP contracts independently.

## Frontend Impact

`NONE` for this Definition. Admin Agenda and Public Booking remain future consumers.

## Concurrency Considerations

Discovery must evaluate MySQL 8.4 transaction/locking strategies that prevent:

- Professional overlap races.
- Capacity overbooking races.
- Stale reschedule writes.
- Concurrent state transitions.

An availability check followed by an unprotected insert is insufficient.

## Transaction Considerations

Create, reschedule and availability-affecting state changes likely require transactions. Exact boundaries, lock order and deadlock handling are Discovery Required.

## Idempotency Assessment

Consumer/API idempotency is `DEFERRED`. Engine concurrency integrity remains mandatory, but no request-token infrastructure is designed without an HTTP consumer.

## Public Identifier Assessment

Public identifiers are `DEFERRED`. Internal BIGINT IDs remain the baseline; a future public consumer may introduce an opaque reference through its own approved scope.

## Timezone/DST Considerations

Business-local recurring rules use the pending `BusinessProfile.timezone`; technical instants remain UTC. Discovery must test DST gaps/folds using technical fixtures without selecting Yaris's production timezone.

## Security Considerations

- Backend remains the authority.
- Future administrative consumers must preserve Sanctum/admin authorization.
- Future public consumers must address abuse and enumeration separately.
- No API security implementation is authorized by this draft.

## Privacy Considerations

Appointment data may connect customer identity, service, professional and time. Discovery must minimize fields, decide notes/privacy, avoid public exposure of internal fields and preserve historical references without inventing retention policy.

## Authorization Model

No new roles or permissions are proposed. Existing Yaris admin authentication remains the future administrative boundary; public/customer actions remain future consumer decisions.

## Validation Model

Form Requests validate future HTTP boundaries. Actions/domain services enforce create, reschedule, cancellation, state, overlap and capacity invariants. Availability must be recalculated by the backend at persistence time.

## Auditability

Focused AppointmentHistory is a candidate. Generic audit logging is out of scope. Discovery must distinguish snapshots, state history and cross-cutting audit events.

## Testing Strategy

Future in-scope tests should cover:

- Model/FK/UNIQUE/CHECK integrity.
- Availability boundary cases.
- Business/professional schedules and exceptions if approved.
- Service duration and active-state behavior.
- Professional compatibility and overlap.
- Capacity counting semantics.
- State transitions and history.
- Reschedule/cancellation behavior.
- Timezone/DST cases.
- Transaction rollback and concurrency races.

## Concurrency-Test Strategy

Discovery must define realistic MySQL integration tests with separate transactions/connections or another credible race harness. Unit tests alone cannot prove capacity and overlap safety.

## Quality Gates

Inherit existing gates:

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

## Acceptance Criteria

The following are draft implementation criteria and require Discovery decisions before development:

1. Appointment Engine scope is implemented without Admin Agenda, Public Booking, Notifications, Payments, CMS or ecommerce.
2. Appointments reference Core Customer, Service and Professional concepts without unauthorized duplication.
3. Appointment persistence preserves approved historical identity and temporal semantics.
4. Approved status values are PHP backed string enums and unknown values are rejected.
5. State transitions are controlled, explicit and historical.
6. Availability respects approved business/professional schedules, duration, compatibility and active states.
7. Professional overlap is rejected, including concurrent write scenarios.
8. Approved global capacity semantics are enforced transactionally.
9. Create/reschedule/cancellation operations revalidate backend availability before persistence.
10. Rescheduling preserves history and the approved stale-write strategy without assuming `schedule_version`.
11. Time persistence and business timezone behavior handle approved DST cases.
12. Customer, Service and Professional deletion/lifecycle behavior preserves historical integrity.
13. Approved duration historical-integrity decisions are implemented; appointment price remains only if Discovery proves it is an Engine invariant.
14. No customer accounts, payment integration, notification delivery or public API is introduced.
15. Security, privacy, authorization and safe errors are tested at approved consumer boundaries.
16. MySQL 8.4 constraints, indexes, transaction boundaries and concurrency behavior are tested.
17. Dedicated test DB, all quality gates, documentation and remote CI are green.
18. Human acceptance is recorded before SPEC-004 closure.

## Definition of Done

- Scope approved and limited to Appointment Engine domain behavior.
- Every AC passes or has an explicitly approved exception.
- Time, state, schedule, capacity and concurrency decisions are documented.
- MySQL integrity and race-sensitive behavior are tested.
- No API/UI/deferred module leakage exists.
- Backend and frontend regression gates pass.
- Security, privacy and auditability reviews are complete.
- Implementation and audit reports exist.
- Remote CI is green.
- Working tree is clean.
- Human acceptance is recorded before closure.

## Discovery-Required Decisions

- Exact appointment schema.
- UTC/local persistence and DST strategy.
- Duration and price snapshots.
- Final status set and transition graph.
- Blocking statuses for availability/capacity.
- Professional recurring schedule and time-off/exception model.
- ServiceCategory.active interpretation for new appointments.
- Capacity counting semantics.
- Whether `schedule_version` is needed or should be rejected.
- Appointment history representation.
- Reschedule/cancellation/no-show semantics.
- Slot granularity and whether slot generation belongs to the Engine.
- Buffer-time decision if a future business rule appears.
- API necessity.
- Soft-delete/deletion and historical FK behavior.
- Required indexes, FKs and CHECK constraints.
- Concurrency/locking/deadlock strategy.
- DST gaps/folds.

## Business-Rule Pending Items

- Exact cancellation window and policy.
- No-show operational policy.
- Which statuses count toward capacity.
- Any-compatible-professional request policy is deferred to a consumer scope.
- Automatic professional assignment is out of scope.
- Final time-off rules.

## Development-Approval Blockers

- Technical Discovery is required before implementation.
- Appointment temporal persistence and concurrency strategy must be approved before migrations.
- Final state transition graph and capacity semantics must be approved before domain actions.

## Deferred Decisions

- Public/admin HTTP contracts.
- UI/calendar presentation.
- Notification scheduling and delivery.
- Payments/deposits integration.
- Business special hours/holiday model.
- Appointment source and consumer/API idempotency.
- Customer authentication and deduplication.
- Generic audit/retention subsystem.

## Non-Blocking Decisions

- Exact class namespaces.
- Exact Action/Service names.
- Whether small result structures are arrays or focused DTOs after Discovery.
- Technical fixture timezones.

## ADR Assessment

ADR-003 is created as a `DRAFT` because temporal persistence and lock ordering are durable cross-cutting decisions. It requires human approval before implementation; it does not authorize code or migrations.

## Discovery Requirements

Technical Discovery must inspect current Laravel conventions and produce evidence for every Discovery-Required decision. It must include schema alternatives, time/DST behavior, concurrency strategy, relationship lifecycle, privacy, future consumers and test isolation. Discovery is not authorized by this document.

## Proposed Implementation Checkpoints

These are proposals, not authorization:

- A: Appointment persistence, statuses and history foundation.
- B: Professional schedule/exception foundation if approved.
- C: Availability, overlap and capacity engine.
- D: Transactional create/reschedule/cancellation operations.
- E: Concurrency and final integrity hardening.
- F: Final tests, documentation and audit.

The final checkpoint count and boundaries require Discovery and human approval.

## Scope-Size Assessment

Appointment Engine is appropriately sized only if limited to backend/domain appointment, schedule, availability, capacity, state and concurrency behavior. It becomes too large if it includes Admin Agenda, Public Booking, Notifications, Payments, CMS, ecommerce or broad UI. Scope reduction is required if Discovery confirms too many independent policy domains.

## Technical Discovery Resolved Decisions

### Final persistent concept set

The recommended Appointment Engine concept/table set is:

```text
appointments
appointment_histories
professional_schedules
professional_time_off
```

No business special-hours table is recommended for SPEC-004 V1.

### Temporal persistence

Concrete dated events are recommended as UTC `DATETIME` values for `starts_at` and `ends_at`. Recurring `BusinessHours` and `ProfessionalSchedule` values remain business-local `TIME` values interpreted with `BusinessProfile.timezone`. The UTC choice avoids MySQL `TIMESTAMP` session-timezone conversion and makes overlap/capacity queries predictable. Local input conversion must reject nonexistent DST times and require explicit disambiguation/offset for ambiguous folds.

### Appointment fields

The recommended minimum `appointments` shape is:

```text
id
customer_id
service_id
professional_id
starts_at              DATETIME UTC
ends_at                DATETIME UTC
duration_minutes       positive historical snapshot
status                 string backed PHP enum value
created_at
updated_at
```

No `schedule_version`, source, notes, service-name snapshot, professional-name snapshot or appointment price is approved by default. Price remains a Discovery/business consumer question and does not turn Appointment into a sales record.

### Appointment status recommendation

The recommended operational baseline is:

```text
pending
confirmed
cancelled
completed
no_show
```

`REQUEST_RECEIVED`, `PENDING_APPROVAL`, `APPROVED` and `REJECTED` remain workflow-dependent. `DEPOSIT_PENDING` is out. `RESCHEDULED` is an operation/history event, not a persistent status.

The recommended transition graph is:

```text
pending   -> confirmed, cancelled
confirmed -> cancelled, completed, no_show
cancelled -> terminal
completed -> terminal
no_show   -> terminal
```

Rescheduling preserves the current operational status and creates history. Whether `pending` blocks resources is a business decision; the conservative recommendation is that it blocks while awaiting approval, with an explicit future resolution/expiry policy.

### Appointment history recommendation

Use a focused `appointment_histories` record for creation/status/reschedule/cancellation/completion/no-show facts. The recommended fields are an appointment FK, event type, nullable from/to status, nullable previous/new UTC interval, nullable previous/new professional references and timestamp. This is not a generic audit log.

### Professional schedule and time-off

`professional_schedules` is in scope for recurring ISO weekday `TIME` intervals. `professional_time_off` is in scope for UTC dated intervals. Both require positive interval checks, exact uniqueness where applicable, overlap validation and replacement/transaction decisions. No recurrence engine, RRULE, HR leave model or generic calendar framework is recommended.

### Availability and capacity

SPEC-004 validates a requested interval for a specific Professional. Candidate-slot generation is deferred to a future consumer. Availability intersects BusinessHours, ProfessionalSchedule and ProfessionalTimeOff, then checks active states, compatibility, duration, professional overlap and global capacity. `ServiceCategory.active` should block new appointments when false, without mutating Service records; this remains a final development-rule confirmation.

Global capacity uses an interval/event-sweep calculation over blocking appointments, not same-start counting. The recommended conservative default is that `pending` and `confirmed` block resources/capacity; this requires explicit business approval before development.

### Concurrency recommendation

For capacity-affecting operations, the recommended lock order is:

```text
1. BusinessProfile singleton row
2. affected Professional rows in ascending ID order
3. affected Appointment row, if existing
4. re-read and validate
5. write and history in one transaction
```

The singleton lock serializes global capacity writes for this single-business, small-volume system. Professional locks protect overlap. Rescheduling between professionals locks both in sorted order. `schedule_version` is rejected by default because the lock/revalidation protocol addresses the identified stale-write problem.

### Idempotency/source/public identity

HTTP request-token idempotency, appointment source and public identifiers are deferred to consumer specifications. This does not defer database race protection.

### Discovery blockers before development

The following require human/business approval before implementation:

- final status/blocking semantics, especially whether `pending` consumes resources;
- exact cancellation/no-show operational policy;
- temporal/DST boundary contract for local input;
- final appointment/history schema and FK deletion policy;
- professional schedule/time-off schema and overlap rules;
- concurrency lock order and retry policy;
- whether appointment price is needed by an Engine consumer.

## Definition State

```text
SPEC-004: READY FOR DEVELOPMENT APPROVAL
Technical Discovery: COMPLETED
Implementation: NOT AUTHORIZED
SPEC-005: NOT STARTED
```
