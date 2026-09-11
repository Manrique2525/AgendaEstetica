# SPEC-007 TECHNICAL DISCOVERY REPORT

## Discovery Status

- SPEC-007: `TECHNICAL DISCOVERY COMPLETED / READY FOR HUMAN REVIEW`.
- Definition: `COMPLETED / APPROVED`.
- Branch: `docs/spec-007-discovery`.
- Development: `NOT AUTHORIZED`.
- Checkpoint A: `NOT AUTHORIZED`.
- SPEC-008: `NOT AUTHORIZED`.
- ADR-004: `DRAFT / REQUIRES HUMAN APPROVAL`.

## Documents and Code Reviewed

- `AGENTS.md`, context, Master Technical Specification, Architecture, Domain Rules, Test Plan and Roadmap.
- SPEC-003, SPEC-004, SPEC-005, SPEC-006 and SPEC-007.
- SPEC-004 Discovery/Closure, SPEC-005 Closure and SPEC-006 Closure reports.
- ADR-001, ADR-002, ADR-003 and ADR-004.
- `CreateAppointment`, `RescheduleAppointment`, `CancelAppointment`, `CompleteAppointment`, `MarkAppointmentNoShow`.
- `Appointment`, `AppointmentHistory`, `Customer`, `BusinessProfile`, `CustomerPhoneNormalizer`.
- Queue/cache/app configuration, `.env.example`, `routes/console.php`, API routes, logging search and transaction patterns.

No application code, migration, table, job, event, listener, provider, route, Vue change, dependency, production configuration or test was added. No temporary experiment was performed.

## Current Framework Capabilities

- Queue default: database, queue `default`, table `jobs`.
- Queue `retry_after`: 90 seconds.
- `database.after_commit`: configurable and defaults to `true`; `.env.example` sets `DB_QUEUE_AFTER_COMMIT=true`.
- Existing queue tables: `jobs`, `job_batches`, `failed_jobs`.
- Worker foundation exists through `php artisan queue:work --once`; no business jobs exist.
- `routes/console.php` has no business scheduler. `schedule:list` has no business tasks.
- Database cache and `cache_locks` exist and are already used by SPEC-006 idempotency.
- Appointment mutations use Laravel `DB::transaction()`.
- SPEC-004 writes AppointmentHistory in the same transaction as Appointment mutations.
- No notification interfaces, jobs, events, listeners, provider implementations or notification persistence exist.

## Primary Architecture Contradiction

### Would the original transactional-intent proposal modify SPEC-004?

**YES.** The original ADR-004 recommendation would require an integration change to the closed Appointment mutation transactions. At minimum, the following existing classes would need to create NotificationIntent records or emit an approved transactional integration event:

- `app/Actions/CreateAppointment.php` - Appointment and created History are written inside its transaction.
- `app/Actions/RescheduleAppointment.php` - Appointment update and rescheduled History are written inside its transaction.
- `app/Actions/CancelAppointment.php` - status update and History are written inside its transaction.
- `app/Actions/CompleteAppointment.php` - status update and History are written inside its transaction.
- `app/Actions/MarkAppointmentNoShow.php` - status update and History are written inside its transaction.

`AppointmentHistory` creation itself would not necessarily need a schema change, but the producer transaction or a shared transactional integration boundary would. Hiding this behind an Event/Listener would not remove the cross-SPEC coupling if the event must be guaranteed atomically with the closed transaction.

### Strategy A - Transactional NotificationIntent

```text
Appointment mutation transaction
  -> Appointment / History / NotificationIntent
  -> COMMIT
  -> queue intent ID
```

Advantages: strongest atomicity, smallest commit-to-intent loss window and immediate intent creation. Costs: requires modifying the five relevant Appointment Actions or an approved shared transactional boundary, couples closed SPEC-004 transaction ownership to SPEC-007 persistence, and requires cross-SPEC approval, schema and extensive transaction tests. It is not authorized by this Discovery.

### Strategy B - AppointmentHistory-driven ingestion

```text
SPEC-004 writes Appointment / History
  -> COMMIT
SPEC-007 ingestor reads committed History
  -> idempotently creates NotificationIntent
  -> queues intent ID
```

The current History schema has a durable auto-increment ID, `appointment_id`, `event_type`, nullable old/new status, nullable old/new UTC intervals, nullable old/new Professional IDs and `created_at`. It is sufficient to identify created/confirmed, rescheduled and cancellation/status-change events without modifying SPEC-004.

Recommended reliability model: process History IDs in ascending order using a durable ingestion high-water mark, create intents and advance the mark in one SPEC-007 transaction, and advance only after intent creation commits. A crash before mark advancement safely reprocesses the source row; a crash after intent commit is safe through the unique dedupe identity. A bounded replay/reconciliation scan is required for recovery.

Costs: bounded scheduler/ingestor latency and a commit-to-ingestion window. These are preferable to changing closed SPEC-004 because the source is durable and replayable.

### Reconciled recommendation

Recommend **Strategy B** for immediate lifecycle notifications. Use a separate current-Appointment query for scheduled reminders. This preserves SPEC-004 exactly.

Closed-SPEC modification required: **NO** for the recommended event-source architecture.

## Event Sources and Identity

| Notification class | Source | Identity |
| --- | --- | --- |
| Immediate confirmed/created | committed `AppointmentHistory` created row | `appointment_id + history_id + type + channel` |
| Immediate rescheduled | committed `AppointmentHistory` rescheduled row | `appointment_id + history_id + type + channel` |
| Immediate cancelled | committed status-change History row, if approved | `appointment_id + history_id + type + channel` |
| Reminder | current eligible Appointment scan | `appointment_id + reminder occurrence/version + type + channel` |

`appointment_id + type` alone is rejected because multiple reschedules and reminder occurrences must remain distinct. History IDs are stable source-event identities without changing History semantics.

Ingestion idempotency requires a unique deterministic NotificationIntent identity. Repeated History scans must be safe and must not create duplicate intent or delivery work.

## Recommended Event Allowlist

| Event | Recommendation | Approval state |
| --- | --- | --- |
| Appointment confirmed/created | IN | Human approval required |
| Appointment rescheduled | IN | Human approval required |
| Appointment cancelled | IN | Human approval required |
| Scheduled reminder | IN | Human approval required |
| Appointment completed | OUT by default | Explicit use case required |
| Appointment no-show | OUT by default | Explicit policy required |
| Marketing/promotional | OUT | Separate scope required |

Not every History row automatically generates a notification.

## NotificationIntent Role

NotificationIntent is a focused durable notification work record, idempotency boundary, provider-independent delivery request and operational status record. It is not a generic system-wide outbox. It may act as a bounded notification outbox for this consumer only.

Recommended conceptual fields:

| Field/concept | Classification |
| --- | --- |
| intent ID | Required |
| Appointment ID | Required |
| source History ID or reminder occurrence | Required according to source |
| notification type | Required |
| logical channel | Required |
| scheduled occurrence/version | Required for reminders |
| deterministic dedupe key | Required and database-unique |
| Customer reference | Required/pending privacy decision |
| protected destination snapshot | Pending business/privacy decision |
| raw phone in logs/keys | Rejected |
| normalized phone persistence | Pending; never exposed/logged as identity |
| template identifier/version | Required/pending |
| rendered message body | Rejected by default |
| minimal immutable event facts | Optional/pending |
| status | Required |
| attempt count | Required |
| provider-neutral error classification | Required |
| provider reference | Optional/pending |
| timestamps | Required |
| retention metadata | Pending |

## Immediate Events and Scheduled Reminders

Both classes share intent persistence, deduplication, provider abstraction, queue processing, statuses, retries, stale checks and privacy rules.

Immediate events are ingested from committed History and have bounded ingestion latency. Reminders are derived from current Appointments and must use current `status`, current `starts_at` and `BusinessProfile.timezone` immediately before intent creation and delivery.

Reminder timing options for human decision:

- same-day reminder;
- 24 hours before;
- 2 hours before;
- multiple reminders;
- custom business policy.

No timing is selected here. Exact cadence is a later technical decision unless an approved business SLA makes it a prerequisite.

## Channel and Consent Decision Gate

`channel != provider`.

Recommended logical channel model:

```text
channel: whatsapp
provider: FakeWhatsAppProvider (SPEC-008, later)
```

SPEC-007 may define the logical channel contract without implementing WhatsApp. SPEC-008 owns Fake WhatsApp behavior, simulation and provider-specific tests. No Meta, Twilio, WABA, SMS, email or real provider is selected.

SPEC-003 is not consent authority. It defines Customer identity/contact data and phone normalization, while explicitly deferring marketing consent, notification preferences and opt-in/opt-out workflows.

Business decision options:

| Decision | Option A | Option B | Recommended default | Architecture impact | Blocks Checkpoint A |
| --- | --- | --- | --- | --- | --- |
| BD-01 Event allowlist | confirmations/reschedules/cancellations/reminders | narrower allowlist | approve baseline IN/OUT table | Defines History ingestion policy | YES |
| BD-02 Delivery channel | generic logical channel only | approve logical WhatsApp channel for SPEC-008 handoff | generic channel plus logical WhatsApp name, no provider | Defines intent/channel uniqueness | YES |
| BD-03 Transactional consent | appointment phone may receive transactional messages generated from that appointment | explicit notification opt-in required | BUSINESS/LEGAL DECISION REQUIRED; safe absent-state suppression | Affects eligibility and data fields | YES |
| BD-04 Reminder timing | same-day / 24h / 2h / multiple / custom | no reminders in V1 | BUSINESS DECISION REQUIRED | Affects reminder occurrence and scheduler | YES if reminders are IN |
| BD-05 Quiet hours | explicit quiet-hours policy | no independent quiet-hours engine; timing avoids undesirable hours | no values selected; prefer no separate engine initially | Affects scheduler/late events | NO for immediate-only A |
| BD-06 Phone changes | current Customer phone at delivery | protected destination snapshot | choose before intent schema; snapshot is safer historical semantics but increases PII | Affects recipient fields/privacy | NO for event allowlist; YES for intent schema checkpoint |
| BD-07 Retention | business/legal duration | minimal duration after operational need | BUSINESS/LEGAL DECISION REQUIRED | Affects cleanup and PII retention | NO if schema omits duration field; YES before production |

Transactional appointment communication and promotional communication must remain separate. Marketing/promotional communication is OUT. No legal conclusion is made by this Discovery.

## Recipient and Template Strategy

Recommended V1 default is to keep stable Customer/Appointment references in queue payloads and re-read authoritative data at execution. Whether the notification intent stores a protected destination snapshot or resolves the current Customer phone remains a business/privacy decision.

Current-phone-at-delivery honors corrections and stores less duplicate PII, but can redirect a historical event. A protected snapshot fixes the intended destination, but can become stale and increases retention obligations. No raw phone may be used in dedupe keys, log correlation or exception context.

The engine should own provider-neutral template identifiers and versions. Providers do not own business wording. Store minimal immutable event facts; reject rendered message-body persistence by default. Copy, language, sender identity and template versions remain pending business data.

## Commit, Queue and Outbox Strategy

Strategy B avoids changing the closed Appointment transactions. Appointment/History commit first; the ingestor then creates NotificationIntent in its own transaction. Only after that transaction commits is a stable intent ID queued. Worker dispatch re-reads intent and current Appointment.

A separate generic transactional outbox is **not required**. The focused NotificationIntent is a bounded notification outbox for this module. This recommendation is captured in draft ADR-004 and requires human approval because the durable intent/high-water-mark design is cross-cutting.

## Stale Work, Cancellation and Rescheduling

Before delivery, re-read NotificationIntent and current Appointment. Suppress when:

- Appointment is cancelled;
- Appointment is no longer confirmed for a reminder;
- current `starts_at` differs from the reminder occurrence;
- the intent is already delivered, suppressed or failed terminally;
- the source event/intent has been superseded;
- channel, consent or recipient eligibility is no longer valid.

Do not delete database queue rows as the primary cancellation mechanism. Leave work addressable and suppress at execution. A reschedule creates a new reminder occurrence only if the approved reminder policy includes it.

## Status and Retry Model

Recommended minimal statuses:

```text
pending     intent awaits due/dispatch work
processing  one worker owns the current attempt
delivered   provider-neutral delivery success
suppressed  policy, eligibility or staleness prevents delivery
failed      terminal failure after approved retry policy
```

`failed` is terminal. Transient failures remain retryable while work is pending/claimed; no additional public status is needed by default. Retry count, backoff, lease and operator recovery are technical Development decisions, not business blockers unless an approved SLA depends on them.

Failure recommendations:

- timeout/network/provider rate limit: bounded retry;
- invalid destination: suppress or terminal-fail, no infinite retry;
- permanent provider rejection: terminal `failed`;
- stale cancellation/reschedule: `suppressed`;
- worker restart/lease expiry: safe reclaim using intent timestamps and atomic claim.

## Scheduler Recommendation

Use a periodic scheduler to find due durable reminder intents or eligible current Appointments, then enqueue stable intent IDs. This is preferred over one delayed job per reminder for restart, reschedule and stale-work behavior. Scheduler cadence is a **NON-BLOCKING TECHNICAL DECISION** unless a human-approved reminder SLA requires precision. Existing `retry_after=90` is infrastructure baseline, not notification retry policy.

## Provider Boundary

Provider responsibilities are accepting an approved provider-neutral message, returning a provider-neutral result/reference and classifying transient/permanent provider errors. Providers must not decide eligibility, consent or retry policy, mutate Appointment/Customer state, create records or own business wording. Fake WhatsApp remains SPEC-008.

## Operational Visibility and Public Boundary

Recommended V1 minimum is durable NotificationIntent status plus safe structured/redacted logs. No new Admin Agenda UI is required for delivery correctness; operational UI is a later checkpoint decision. No public notification status, unsubscribe, preference, lookup or webhook endpoint is recommended. Real provider webhooks and receipts are deferred.

## Security and Privacy Analysis

- PII leakage: minimize persisted fields, queue payloads, logs and errors.
- Provider secrets: server-side only, never in queue payloads or public responses.
- Duplicate delivery: unique dedupe identity and atomic intent claiming.
- Amplification: explicit event allowlist, bounded retries and no recursive Appointment mutation.
- Queue poisoning: stable opaque intent IDs and current-state revalidation.
- Unauthorized triggering: no public endpoint; future admin surfaces use existing auth.
- Template injection: constrained variables and provider-neutral rendering boundary.
- Stale messages: current Appointment/intent checks immediately before send.
- Replay: unique identity and terminal state transitions.

## Performance, Concurrency and Database Assessment

No production traffic is invented. Use synthetic 10, 100 and 1000 appointment scenarios for later query/queue benchmarking. A due-intent query will likely need a status/scheduled-time index if schema is approved; no index is authorized now.

Required later MySQL scenarios include two workers claiming one intent, retry overlap, reschedule/cancel versus reminder delivery, duplicate History ingestion and worker restart. Notification processing must not acquire or reorder SPEC-004 BusinessProfile/Professional/Appointment locks.

If persistence is approved, use Appointment FKs and a database-unique dedupe key. Preserve existing restrictive Appointment/History deletion behavior. Customer FK and destination-snapshot deletion semantics remain privacy/business decisions.

## Testing Strategy

Later Development must cover event eligibility, History ingestion, post-commit intent execution, rollback behavior, deduplication, concurrent workers, stale reschedule/cancel suppression, transient/terminal failures, provider isolation, privacy/log safety, timezone/DST reminders, queue restart/delay, approved phone-change semantics, no Appointment mutation and no duplicate messages. Use MySQL integration tests for transactions, uniqueness, queue persistence and concurrency. No new tests were added in Discovery.

## ADR Assessment

ADR-004 is required because the durable NotificationIntent/high-water-mark and SPEC-004 ingestion boundary are durable cross-cutting architecture decisions.

```text
Path: docs/architecture/adr/ADR-004-notification-intent-after-commit.md
Status: DRAFT / REQUIRES HUMAN APPROVAL
```

It must be accepted or replaced before Development. No SPEC-004 modification is recommended under Strategy B.

## Final Decision Classification

### BLOCKS DEVELOPMENT

- BD-01 event allowlist.
- BD-02 logical channel and SPEC-008 handoff.
- BD-03 transactional consent policy and absent-state behavior.
- BD-04 reminder inclusion and timing if reminders are V1.
- Acceptance of Strategy B and draft ADR-004 integration boundary.
- NotificationIntent schema/dedupe identity and recipient strategy before the schema checkpoint.

### BLOCKS ONLY LATER CHECKPOINT

- Exact recipient snapshot/current-phone implementation.
- Template version/rendering snapshot details.
- Durable status reason categories.
- Admin operational visibility.
- Detailed retry/lease/worker recovery behavior.

### MUST RESOLVE BEFORE PRODUCTION/CLOSURE

- Retention duration and cleanup policy.
- Provider credentials/sender identity.
- Final message copy/language.
- Consent record origin, revocation and privacy/legal policy.

### NON-BLOCKING TECHNICAL DECISION

- Scheduler cadence without an approved SLA.
- Exact retry seconds/counts while finite/transient-only/terminal semantics are preserved.
- Queue worker process sizing after synthetic benchmark.
- Optional provider correlation reference format.

### DEFERRED

- Fake WhatsApp implementation and simulation in SPEC-008.
- Real WhatsApp/Meta/SMS/email providers and webhooks.
- Consent-management UI, notification history UI, marketing and bulk messaging.
- Generic outbox beyond focused NotificationIntent.
- SPEC-008+.

## Checkpoint A Minimum Prerequisites

Before Checkpoint A can begin, human approval is required for:

- immediate event allowlist;
- logical channel semantics and SPEC-008 boundary;
- transactional consent policy or explicit approved absence behavior;
- whether reminders are in A/V1 and their policy category;
- Strategy B/ADR-004 as the event-source boundary.

Exact retry backoff, scheduler cadence and Admin UI are not prerequisites for an event/eligibility checkpoint unless an approved SLA makes them relevant.

## Development Readiness

```text
Technical Discovery: COMPLETED / READY FOR HUMAN REVIEW
Development: NOT AUTHORIZED
Checkpoint A: NOT AUTHORIZED
```

The architecture recommendation is ready for review but Development is blocked until the listed business and ADR decisions are resolved.

## Scope and Implementation Audit

```text
new application code: NONE
new migrations: NONE
new tables: NONE
new Jobs: NONE
new Events/Listeners: NONE
new providers: NONE
new routes: NONE
new Vue: NONE
new dependencies: NONE
new production configuration: NONE
closed SPEC modifications: NONE
```

## Regression Evidence

Discovery changed documentation only. Existing application regression remains:

- Backend: `208 tests / 1202 assertions`, PASS.
- SPEC-004 concurrency: `17 tests / 212 assertions`, PASS, `0 skipped`.
- Frontend: `15 files / 55 tests`, PASS.
- Pint, PHPStan, Composer validate/audit, ESLint, TypeScript, build and npm audit: PASS.
- No new functionality tests were added.

## Final Discovery State

```text
SPEC-007: TECHNICAL DISCOVERY COMPLETED / READY FOR HUMAN REVIEW
Definition: COMPLETED / APPROVED
Technical Discovery: COMPLETED / READY FOR HUMAN REVIEW
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

STOP. Submit this Discovery and draft ADR-004 for human review. Resolve Development-blocking decisions before authorizing Checkpoint A. Do not implement Notification Engine or start SPEC-008.
