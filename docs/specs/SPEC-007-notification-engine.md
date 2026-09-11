# SPEC-007 - Notification Engine

## SPEC ID

`SPEC-007`

## Title

Notification Engine

## Status

`DEFINITION COMPLETED / READY FOR HUMAN REVIEW`

This Definition covers roadmap item `07. Notification engine`, immediately after the merged Public Booking consumer and before Fake WhatsApp. Technical Discovery, Development and all implementation checkpoints are not authorized.

## Roadmap Reference

The approved roadmap orders the next module as:

```text
06. Public booking
07. Notification engine
08. Fake WhatsApp
```

SPEC-006 Public Booking is closed and merged. SPEC-007 is the next unfinished roadmap item; it must not be skipped or reordered.

## Business Objective

Provide a reliable, privacy-aware notification capability for eligible salon events without coupling appointment business rules to a particular delivery provider. The engine should turn approved operational events into controlled notification work, avoid duplicate sends, respect customer communication boundaries and remain observable through safe application outcomes.

## Problem Statement

The current system can create, change and complete appointment records, but it does not yet coordinate customer-facing reminders or other approved operational messages. Adding notification calls directly to appointment Actions would couple domain transactions to external delivery, risk sending before commit, duplicate messages during retries and make future providers difficult to replace.

The system needs one notification boundary that consumes authoritative appointment outcomes, schedules work after successful commits and isolates delivery concerns from SPEC-003 Core, SPEC-004 Appointment Engine, SPEC-005 Admin Agenda and SPEC-006 Public Booking.

## Actors and Boundaries

- Yaris administrator, who operates appointments and may need operational notification outcomes.
- Customer, who may receive an approved notification without requiring an application account.
- Professional, an appointment resource and not an authenticated notification user by default.
- Notification Engine, the internal coordinator of eligibility, intent, deduplication and dispatch work.
- Delivery provider, an infrastructure boundary that receives an approved message request; the concrete Fake WhatsApp provider belongs to roadmap item 08 unless explicitly included by a later approved scope.
- Queue worker and scheduler, system actors responsible for processing committed notification work.

`Customer != internal User` and `Professional != User`. SPEC-007 does not introduce customer or professional authentication.

## Dependencies and Authority

### SPEC-003 Business Core

SPEC-007 may consume Customer identity and approved business configuration. Customer identity/contact data and phone normalization remain governed by SPEC-003; contact-channel and consent semantics require SPEC-007 Technical Discovery or a separately approved decision. Notification Engine must not become Customer CRUD or identity management.

### SPEC-004 Appointment Engine

SPEC-007 consumes authoritative Appointment status, time, Service, Professional, duration, lifecycle and history outcomes. It must react only to committed domain results and must not implement availability, capacity, appointment status transitions or appointment persistence rules.

### SPEC-005 Admin Agenda

Admin Agenda remains the authenticated operational consumer. SPEC-007 must not create a second administrative workflow or bypass existing Appointment Actions to send messages.

### SPEC-006 Public Booking

Public Booking remains the guest booking consumer. SPEC-007 may consume successful confirmed appointment outcomes, but must not change the public booking route, Customer resolution, idempotency contract or public API surface.

### Architecture

The approved architecture remains a Laravel/Vue modular monolith with a database queue and `after_commit=true`. Provider calls must be isolated behind an approved abstraction and must not occur inside Controllers or uncommitted domain transactions.

## In Scope for V1 Definition

- A Notification Engine boundary for approved appointment-related messages.
- Eligibility evaluation based on authoritative committed appointment state and approved communication rules.
- Notification intent/work-item lifecycle sufficient to support queued processing and safe failure handling.
- Idempotent suppression of duplicate notification work for the same approved business event.
- Dispatch through a provider abstraction rather than provider-specific business logic.
- Database queue integration after transaction commit, consistent with the approved architecture.
- Safe handling of unavailable providers, invalid recipient data, retries and terminal delivery outcomes, subject to Discovery decisions.
- Minimal operational visibility of notification outcome without exposing message content or unnecessary personal data.
- Tests for eligibility, deduplication, commit ordering, failure handling, retry behavior and provider isolation after the contracts are discovered.

These are capability boundaries, not authorization to create tables, jobs, providers, routes or UI.

## Explicitly Out of Scope

- Fake WhatsApp provider implementation from roadmap item 08.
- Meta WhatsApp, WhatsApp Cloud API, SMS, email or any external delivery vendor implementation.
- Customer accounts, authentication, consent-management UI or Customer CRUD.
- Professional portal or Professional authentication.
- Appointment creation, rescheduling, cancellation, completion, no-show or new Appointment statuses.
- Public booking changes, public lookup, public cancellation or public rescheduling.
- Payments, deposits, checkout, ecommerce, CMS, reviews and favorites.
- Marketing campaigns, newsletters, segmentation, bulk messaging or analytics product work.
- Replacing SPEC-004 lifecycle/history authority or SPEC-006 public-booking behavior.
- New infrastructure such as Redis, Kafka, RabbitMQ, external WAF or a hosted notification platform.
- SPEC-008 or any later roadmap item.

## Business Rules to Preserve

- Notifications are sent only for eligible future appointments with valid current scheduling context, approved consent and an available communication channel, subject to Discovery definition of each condition.
- Notification work must not be sent before the originating appointment transaction commits.
- Duplicate notification delivery must be prevented using an idempotency identity based on the approved appointment event, version and type contract.
- Pending notification work must be invalidated or safely suppressed when an appointment is cancelled or rescheduled, according to the discovered event model.
- Appointment status, time, availability, capacity, duration and history remain authoritative in SPEC-004.
- Customer identity, phone normalization and privacy boundaries remain authoritative in SPEC-003 and existing approved consumers.
- Provider failure must not mutate Appointment state or create a competing appointment lifecycle.
- Unknown business data, channel policies, consent and provider credentials remain pending until explicitly confirmed.

## Functional Requirements

### FR-01 Notification eligibility

The engine evaluates whether an approved appointment event is eligible for notification using authoritative appointment state and approved communication rules.

### FR-02 Committed-event processing

Notification work is created or dispatched only after the originating business transaction has successfully committed.

### FR-03 Appointment-event coverage

The engine supports the approved set of appointment-related notification events discovered for V1 without changing Appointment lifecycle semantics.

### FR-04 Idempotent delivery intent

Repeated processing of the same appointment event does not create duplicate notification work or duplicate delivery attempts beyond the approved retry policy.

### FR-05 State-change suppression

Cancellation, rescheduling and other authoritative appointment changes prevent obsolete pending notification work from being delivered.

### FR-06 Provider isolation

The engine dispatches through a provider boundary so notification orchestration is not coupled to a concrete external vendor.

### FR-07 Failure handling

Provider failure, invalid recipient data and transient processing failure produce safe, recoverable outcomes without changing appointment state.

### FR-08 Retry control

Retries are bounded and distinguish transient failures from terminal failures according to the discovered delivery policy.

### FR-09 Privacy-minimized payloads

The engine sends only the minimum approved recipient and message data required by the selected notification type and provider.

### FR-10 Operational outcome

Authorized operational consumers can determine notification outcome without exposing message secrets or unnecessary customer data.

### FR-11 Customer boundary

A Customer does not need an application account to receive an approved notification, and the engine does not create a Customer management surface.

### FR-12 Provider availability

The engine handles unavailable or unconfigured delivery channels safely without falsely reporting successful delivery.

### FR-13 Appointment authority

The engine never creates, modifies or transitions an Appointment as a side effect of notification processing.

### FR-14 Safe completion

An eligible notification can reach a clear terminal outcome such as delivered, suppressed or failed, using the final discovered vocabulary.

## Non-Functional Requirements

- **NFR-01 Authority:** Appointment and Customer domain decisions remain owned by SPEC-003/SPEC-004; Notification Engine coordinates only notification policy and delivery work.
- **NFR-02 Reliability:** Processing is safe under queue retries, worker restarts and repeated event publication.
- **NFR-03 Transaction safety:** No notification is dispatched from an uncommitted transaction; the approved database queue and `after_commit=true` boundary are preserved.
- **NFR-04 Privacy:** Logs, payloads, operational views and failures minimize phone numbers, names, message bodies, provider credentials and delivery metadata.
- **NFR-05 Security:** Provider credentials remain server-side; unauthenticated public consumers cannot inspect notification work or delivery outcomes.
- **NFR-06 Observability:** Delivery outcomes and failure categories are diagnosable through existing application mechanisms without introducing a monitoring vendor.
- **NFR-07 Performance:** Queue processing does not block appointment creation or administrative/public request latency.
- **NFR-08 Testability:** Eligibility, idempotency, commit ordering, retry and provider isolation rules are independently testable against MySQL and the approved queue setup.

## Security and Privacy Boundary

- No public notification endpoint, notification lookup token or notification management route is authorized by this Definition.
- No customer or professional authentication is introduced.
- Provider secrets, raw credentials, raw message payloads and internal delivery identifiers are never public data.
- Raw phone numbers and sensitive customer data must not be used as log keys or exposed in operational errors.
- Consent and communication-channel eligibility require explicit Discovery confirmation before implementation.
- Queue jobs must not bypass authorization or call Admin mutation routes.
- External provider calls require a separate approved provider boundary and must not be performed by Controllers.

## Authorization Boundary

SPEC-007 is an internal application capability. Administrators may eventually inspect approved operational outcomes through a separately defined surface; Customers and Professionals have no authenticated management surface in this Definition. No public or admin route is authorized yet.

## Data and Schema Considerations

The Definition does not authorize migrations or persistence design. Discovery must determine whether existing queue/job infrastructure is sufficient or whether a notification intent/outcome model is required. Any new table, column, index, durable idempotency key, retention rule or consent field requires explicit design and approval before Development.

Expected Definition state:

```text
new tables: NONE
new migrations: NONE
new columns: NONE
new indexes: NONE
new constraints: NONE
```

## API Considerations

No API route is authorized by this Definition. Discovery must determine whether notification outcomes are consumed only by internal jobs/actions or whether a future authenticated Admin Agenda read is required. Public Booking and existing Admin Agenda API contracts must remain unchanged unless a later approved checkpoint authorizes a compatible consumer change.

## Frontend Considerations

No Vue page, component, store or public notification UI is authorized by this Definition. Discovery may evaluate a minimal authenticated operational status surface only if the business requires it; customer-facing notification content is delivered through provider boundaries, not a new public SPA.

## Integration Expectations

- SPEC-003 supplies Customer identity/contact data and phone normalization; communication-channel and consent semantics remain Discovery-required.
- SPEC-004 supplies authoritative Appointment lifecycle and committed event facts.
- SPEC-005 remains the operational appointment consumer and is not redesigned.
- SPEC-006 remains the public booking consumer and is not changed.
- Roadmap item 08 Fake WhatsApp is a later provider consumer and must not be silently pulled into SPEC-007.

## Technical Discovery Required Topics

- Exact V1 notification event types and whether creation, confirmation, rescheduling, cancellation, completion or reminders are included.
- Timing model, lead times, business timezone behavior and scheduler ownership.
- Consent, opt-in, channel eligibility and treatment of missing or invalid contact data.
- Notification intent/outcome schema, uniqueness identity, event/version semantics and retention.
- Commit ordering, database queue configuration, `after_commit=true` verification and worker retry boundaries.
- Provider interface contract, fake-provider handoff to roadmap item 08 and external-provider exclusion.
- Message template ownership, localization, variable substitution and privacy minimization.
- Status vocabulary for queued, suppressed, delivered, failed, retryable and terminal outcomes.
- Cancellation/reschedule invalidation and stale pending work behavior.
- Failure classification, retry/backoff limits, dead-letter handling and manual recovery policy.
- Authorization for any future Admin operational view and absence of public lookup/enumeration.
- Logging, metrics, correlation identifiers and PII redaction using existing facilities.
- Performance under queue retries and appointment write load.
- MySQL transaction, locking, concurrency and idempotency tests.
- Dependency and infrastructure impact; no new provider is approved by this Definition.

## Business Questions and Blockers

- Which appointment events must generate notifications in V1?
- Are notifications strictly transactional/operational, or are any marketing messages intended? Marketing is out of scope unless separately approved.
- What communication channels are actually available and approved for the business?
- What consent must exist before sending, and where is that consent captured and audited?
- What exact lead times, quiet hours, timezone and holiday policies apply?
- What should the customer experience when a provider is unavailable or delivery fails?
- Is a notification status visible to Yaris in V1, and if so through which authenticated surface?
- What retention period applies to notification payloads, outcomes and redacted logs?

No implementation should begin while these questions affect the event, consent, channel, persistence or provider contract.

## Business Data Pending

- Approved communication channels and provider availability.
- Consent wording, capture source and retention policy.
- Official notification timing, quiet hours and timezone policy.
- Message templates, language and approved business copy.
- Provider credentials, sender identity and delivery policy.
- Customer contact data completeness and operational fallback policy.

No real people, phone numbers, credentials, provider keys, message copy or production policies are invented by this Definition.

## Deferred Decisions

- Concrete notification tables, columns, indexes and retention jobs.
- Concrete queue job names, event classes, listeners and scheduler commands.
- Concrete provider interface method signatures and Fake WhatsApp implementation.
- Concrete delivery status enum and retry/backoff values.
- Admin notification dashboard or customer notification history.
- External provider integration, webhooks, delivery receipts and opt-out automation.
- Consent-management UI and marketing preference model.

## Proposed Implementation Checkpoints

These are planning boundaries only and do not authorize implementation:

### Checkpoint A - Event and Eligibility Contract

Freeze approved event types, eligibility, consent/channel rules and authority boundaries.

### Checkpoint B - Durable Intent and Idempotency

Design and implement the smallest approved notification intent/outcome persistence and duplicate-suppression contract, if Discovery authorizes schema.

### Checkpoint C - Queue and Failure Processing

Implement post-commit queue processing, retry/terminal failure handling and stale-work suppression using the approved database queue.

### Checkpoint D - Provider Boundary

Implement the provider abstraction and integration boundary without pulling Fake WhatsApp or an external vendor ahead of roadmap authorization.

### Checkpoint E - Operational Read and Security Hardening

Only if approved, add authenticated operational outcome visibility, redaction, authorization and abuse/failure hardening.

### Checkpoint F - Final Tests, Documentation and Acceptance Audit

Run complete regression, reliability, privacy, scope and acceptance audit before closure. This does not authorize closure or merge automatically.

## Acceptance Criteria

1. The Definition identifies SPEC-007 as roadmap item 07 Notification Engine immediately after merged Public Booking and before Fake WhatsApp.
2. The Definition states the business problem and objective without inventing provider, consent, timing or production data.
3. The Definition distinguishes administrators, Customers, Professionals, the engine, queue workers and delivery providers.
4. The Definition preserves the one-business modular-monolith architecture and approved database queue boundary.
5. The Definition preserves SPEC-003 authority for Customer identity, contact data and phone normalization without claiming notification-consent authority.
6. The Definition preserves SPEC-004 authority for Appointment lifecycle, status, time, history, duration, availability, capacity and concurrency.
7. The Definition preserves SPEC-005 Admin Agenda and SPEC-006 Public Booking as downstream/upstream consumers without redesigning them.
8. The Definition includes eligibility, committed-event processing, idempotency, stale-work suppression, provider isolation and safe failure handling as V1 capabilities.
9. The Definition explicitly excludes accounts, public self-service, payments, notifications outside this engine, external providers, Fake WhatsApp implementation and SPEC-008+.
10. The Definition identifies security, privacy, consent, credential, logging and non-enumeration boundaries.
11. The Definition does not authorize API routes, frontend surfaces, schema, migrations, dependencies or infrastructure.
12. The Definition identifies the required Discovery questions for events, timing, consent, channels, persistence, queue behavior, providers, retries, privacy and testing.
13. The Definition records business-data requirements as pending and does not invent real contacts, credentials, templates or policies.
14. The Definition proposes bounded implementation checkpoints without authorizing Development or Checkpoint A.
15. The Definition records an explicit human approval gate and remains `DEFINITION COMPLETED / READY FOR HUMAN REVIEW`.

## Definition of Done

- SPEC-007 Definition is complete, bounded and internally consistent with roadmap item 07.
- Business objective, actors, dependencies, rules, V1 boundaries and acceptance criteria are explicit.
- Existing SPEC-003 through SPEC-006 authorities are preserved.
- Technical Discovery questions and business blockers are listed without premature implementation decisions.
- No application code, routes, models, migrations, tests, dependencies, infrastructure or production data are introduced.
- Documentation diff contains only the SPEC-007 Definition and the required roadmap status reference.
- Human review is required before Technical Discovery or Development.

## Definition State

```text
SPEC-007: DEFINITION COMPLETED / READY FOR HUMAN REVIEW
Canonical name: Notification Engine
Roadmap item: 07
Definition: COMPLETED / READY FOR HUMAN REVIEW
Technical Discovery: NOT AUTHORIZED
Development: NOT AUTHORIZED
Checkpoint A: NOT AUTHORIZED
Checkpoint B: NOT AUTHORIZED
Checkpoint C: NOT AUTHORIZED
Checkpoint D: NOT AUTHORIZED
Checkpoint E: NOT AUTHORIZED
Checkpoint F: NOT AUTHORIZED
Closure: NOT AUTHORIZED
Merge: NOT AUTHORIZED
SPEC-008+: NOT AUTHORIZED
```

## Final Definition Decision

```text
SPEC-007 - Notification Engine: DEFINITION COMPLETED
Definition: READY FOR HUMAN REVIEW
Technical Discovery: NOT AUTHORIZED
Development: NOT AUTHORIZED
Application changes: NONE
Schema/dependency changes: NONE
SPEC-008+: NOT AUTHORIZED
```

STOP. Submit the SPEC-007 Definition for human review. Do not start Technical Discovery, Development, Checkpoint A or SPEC-008+.
