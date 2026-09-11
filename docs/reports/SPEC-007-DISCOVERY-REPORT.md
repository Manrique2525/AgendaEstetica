# SPEC-007 TECHNICAL DISCOVERY REPORT

## Discovery Status

- SPEC-007: `TECHNICAL DISCOVERY COMPLETED / BLOCKED FOR DEVELOPMENT`.
- Definition: `COMPLETED / APPROVED`.
- Discovery branch: `docs/spec-007-discovery`.
- Development, Checkpoint A and SPEC-008 remain unauthorized.
- Draft ADR: `docs/architecture/adr/ADR-004-notification-intent-after-commit.md`.

## Documents and Application Areas Reviewed

- Governance, context, Master Technical Specification, Architecture, Domain Rules, Test Plan and Roadmap.
- SPEC-003 through SPEC-007 and SPEC-004/SPEC-005/SPEC-006 closure reports.
- ADR-001, ADR-002, ADR-003 and draft ADR-004.
- Appointment Actions: `CreateAppointment`, `RescheduleAppointment`, `CancelAppointment`, `CompleteAppointment`, `MarkAppointmentNoShow`.
- `Appointment`, `AppointmentHistory`, `Customer`, `BusinessProfile` and `CustomerPhoneNormalizer`.
- `routes/api.php`, `routes/console.php`, `bootstrap/app.php`, `config/queue.php`, `config/cache.php`, `config/app.php`, `.env.example`, Composer and npm manifests.
- Existing application logging/search results, queue/scheduler surfaces, public/admin boundaries and transaction patterns.

No application implementation, temporary experiment, schema change, dependency change, test or production configuration change was made during Discovery.

## Current Infrastructure Findings

- Default queue connection: `database`.
- Queue table: `jobs`; default queue: `default`.
- Queue retry baseline: 90 seconds.
- `database.after_commit`: configurable through `DB_QUEUE_AFTER_COMMIT`, default `true` and `.env.example` sets `DB_QUEUE_AFTER_COMMIT=true`.
- Existing queue tables: `jobs`, `job_batches`, `failed_jobs`.
- Worker foundation: `php artisan queue:work --once` and documented persistent worker strategy; no business jobs exist.
- Scheduler: `routes/console.php` contains only the framework `inspire` command; `php artisan schedule:list` has no business tasks.
- Scheduler foundation: `schedule:run`/`schedule:work` are available; no reminder schedule currently exists.
- Cache: database-backed cache is the default and existing `cache_locks` infrastructure is used by SPEC-006 idempotency.
- Transactions: appointment Actions use Laravel `DB::transaction`; SPEC-004 acquires BusinessProfile, Professional and Appointment locks in ADR-003 order and writes AppointmentHistory in the same transaction.
- Logging: no SPEC-007 notification logging, provider interface or notification-specific exception mapping exists.
- Current public/admin API has no notification route and must remain unchanged by Discovery.

## Notification-Event Analysis

| Event | Initial classification | Discovery recommendation | Development status |
| --- | --- | --- | --- |
| Appointment created/confirmed | V1 candidate | Immediate transactional confirmation if channel, consent and copy are approved. Use the created AppointmentHistory identity. | Blocked by event/channel/consent approval |
| Appointment rescheduled | V1 candidate | Immediate transactional update and invalidate obsolete reminders. Each reschedule must be a distinct event occurrence. | Blocked by event/channel/consent approval |
| Appointment cancelled | V1 candidate | Immediate transactional cancellation message may be useful, but must be explicitly approved; suppress pending reminders. | Business approval required |
| Appointment completed | OUT by default | No customer notification is recommended without a concrete business use case. | Deferred/out |
| Appointment no-show | OUT by default | No customer notification is recommended without an explicit policy and approved copy. | Deferred/out |
| Scheduled reminder | V1 candidate | Use the same intent/delivery pipeline with a scheduled occurrence and current-state stale check. | Blocked by timing/consent/channel approval |

Not every AppointmentHistory row should automatically become a notification. Eligibility must be an explicit policy, not an event-type catch-all.

## Immediate and Scheduled Models

Immediate event-driven notifications and temporal reminders should share:

- provider-neutral Notification Intent;
- deduplication identity;
- queue processing and provider abstraction;
- safe status, retry and failure semantics;
- privacy and operational redaction rules.

They should differ in trigger and eligibility:

- Immediate events are created from a committed approved mutation and are eligible for post-commit dispatch.
- Reminders have a `scheduled_for` occurrence, are discovered by a due-intent scheduler and must re-read current Appointment state before delivery.

## Channel and SPEC-008 Boundary

SPEC-007 should define a generic channel concept only. It should not select or ship WhatsApp, SMS, email, Meta, Twilio, WABA or another provider. Roadmap item 08 owns Fake WhatsApp implementation, delivery simulation and provider-specific tests/demo behavior. SPEC-007 owns intent, eligibility, timing, deduplication, queue orchestration, stale suppression, provider-neutral outcomes and privacy/security rules.

## Consent Assessment

SPEC-003 explicitly defines Customer identity, display phone and normalized phone, and explicitly defers marketing consent, WhatsApp promotional opt-in, notification preferences, campaign consent and opt-out workflows to Notification/Meta WhatsApp scopes. SPEC-003 is not notification-consent authority.

Recommended distinction:

- Transactional appointment messages and marketing/promotional messages must be separate policy categories.
- Marketing/promotional messaging is OUT of this V1 Definition unless separately approved.
- Whether transactional appointment notifications require explicit consent is a BUSINESS DECISION REQUIRED before Development.
- The absence of consent should not be treated as permission until the business/legal policy is approved; the safe implementation default is suppression when the required state is absent.
- Channel-specific consent, origin, persistence, revocation and opt-out semantics require Discovery/business approval.

Consent decisions are Development blockers because they affect eligibility, data shape, privacy and delivery behavior.

## Reminder Timing, Quiet Hours and Timezone

The repository defines `BusinessProfile.timezone` as the business temporal authority and stores concrete Appointment instants in UTC. No notification timing or quiet-hour value is defined.

Recommendation:

- Store Appointment instants in UTC and derive due reminder occurrences using `BusinessProfile.timezone` at the temporal boundary.
- Persist the resolved occurrence identity/scheduled time so a reschedule creates a distinct reminder occurrence.
- Use business-local calendar semantics for reminder policy and UTC instants for queue execution.
- Revalidate DST transitions using the existing temporal authority; do not use the server timezone as business authority.
- Do not invent same-day, 24-hour, multi-reminder or quiet-hour values.

Required decisions: reminder lead times, number of reminders, quiet hours, holidays/closures, late reschedule/cancellation behavior and provider delay tolerance.

## Notification Persistence Decision

### Alternatives

| Option | Assessment |
| --- | --- |
| Queue-only fire-and-forget | Rejected for the recommended V1 because work can be lost between commit and dispatch and there is no durable deduplication/stale-work state. |
| AppointmentHistory polling | Rejected as the primary source because it is delayed, couples notification timing to operational history and still needs a durable cursor/intent. |
| Durable Notification Intent | Recommended, pending approval; supports deduplication, retries, stale suppression, crash recovery and safe operational status. |
| Generic transactional outbox | Not recommended for V1; broader than the single notification consumer. A focused Notification Intent can serve as the bounded notification outbox. |

### Recommendation

Use a durable, provider-neutral Notification Intent created transactionally with the originating approved appointment event, then processed after commit. This is a cross-cutting integration decision and is captured in draft ADR-004. It requires human approval before Development.

### Conceptual Fields

| Concept | Classification | Reason |
| --- | --- | --- |
| intent ID | Required | Stable worker and operational reference. |
| appointment ID | Required | Re-read authoritative current state. |
| event/history ID | Required | Distinguishes creation and every reschedule/status event. |
| notification type | Required | Confirmation, reminder or approved event category. |
| channel | Required | Provider-neutral channel identity. |
| scheduled occurrence/version | Required for reminders | Distinguishes original and rescheduled reminder occurrences. |
| deduplication key | Required | Database-enforced duplicate suppression. |
| recipient reference | Required/pending | Customer reference or protected destination strategy requires decision. |
| recipient raw phone | Rejected by default | Avoid durable raw PII unless a protected snapshot is approved. |
| normalized phone | Rejected in logs/keys; persistence pending | Existing normalization authority must not become an exposed identifier. |
| template identifier/version | Required/pending | Prevents later template edits from changing an existing intent unexpectedly. |
| rendered message body | Rejected by default | Avoids unnecessary durable PII; retain only if a business/legal requirement exists. |
| minimal immutable event facts | Optional/pending | Useful for historical rendering, but must be privacy-minimized. |
| status | Required | Minimal lifecycle and stale/failure handling. |
| attempt count | Required | Bounded retry control. |
| last error classification | Required | Provider-neutral diagnosis without raw provider response. |
| provider correlation reference | Optional/pending | Useful operationally, but must contain no secret or raw provider payload. |
| timestamps | Required | Created, scheduled, processing, completed and updated times as approved. |
| retention metadata | Pending | Requires business/legal retention decision. |

## Recipient Strategy

The engine should carry stable Customer/Appointment references in queue payloads and re-read authoritative data at execution. A destination snapshot may be required for historical correctness if a Customer phone changes between booking and delivery, but this is a business/privacy decision.

Recommendation for approval:

- Snapshot the approved destination at intent creation only if the business chooses historical recipient semantics.
- Protect any persisted destination and never place raw phone in dedupe keys, log correlation keys or exception context.
- If the destination is absent or invalid, suppress safely rather than falsely report delivery.
- Define whether a phone update before a reminder uses the original approved destination or current Customer contact.

## Message and Template Strategy

Notification Engine should own provider-neutral template identifiers and a rendering contract, not provider-specific wording. A Notification Intent should reference a template/version and minimal immutable event facts. Rendered message storage is rejected by default because it increases PII retention; if later required, it needs explicit privacy and retention approval. Real message copy, language, sender identity and templates are business data pending.

## Event Source and Commit Ordering

### Alternatives

- Provider calls from Appointment Actions: rejected because it couples domain mutations to delivery and can send before commit.
- Notification orchestration directly in Controllers: rejected by architecture and bypasses domain authority.
- Polling AppointmentHistory: rejected as primary source; can remain a reconciliation fallback only if later justified.
- Provider-neutral domain event after commit: useful for decoupling, but has a crash window before durable intent creation.
- Transactional Notification Intent: recommended for durable correctness, with a narrow approved integration point around appointment mutations.

### Recommended Boundary

The originating Appointment transaction creates the provider-neutral Notification Intent for approved events. Commit succeeds first. Only then does an after-commit queue path enqueue stable intent IDs. The worker re-reads the intent and current Appointment before provider dispatch. Provider success/failure updates notification state only; it never mutates Appointment state.

This requires a cross-SPEC integration decision because SPEC-004 Actions currently own the appointment transaction. No SPEC-004 file is modified by this Discovery.

## Outbox Assessment

A separate generic outbox is not justified for one Notification Engine consumer at current single-business scale. A durable Notification Intent can provide the focused outbox properties needed here. The recommendation is not accepted architecture until ADR-004 and the event integration point receive human approval.

## Idempotency and Event Identity

The deduplication unit should include:

```text
appointment_id
event/history_id
notification_type
channel
scheduled_occurrence/version
```

`appointment_id + type` alone is insufficient because multiple reschedules and reminder occurrences must remain distinct. Existing `AppointmentHistory` IDs provide a stable event identity for created, rescheduled and status-change events without changing SPEC-004 semantics. The specific event-type policy remains pending.

## Stale Work and Cancellation/Rescheduling

Do not delete queued database jobs directly as the primary strategy. Keep the intent/job addressable and suppress at execution:

- Re-read current Appointment status, `starts_at`, Professional and relevant Service/Customer state.
- Re-read the intent status and event/history identity.
- A cancelled Appointment suppresses pending reminders.
- A rescheduled Appointment suppresses the old reminder occurrence and creates a distinct new occurrence only if the approved policy requires it.
- Rapid reschedules are handled by event identity and current-state checks; an obsolete queued intent cannot send solely because it was once valid.
- Provider delivery must occur only after all current checks pass.

Recommended status is `suppressed` with a safe reason category such as obsolete, cancelled, ineligible or missing destination. Avoid an oversized separate `obsolete` lifecycle unless Discovery evidence requires it.

## Notification Status Model

Recommended minimal states:

```text
pending       intent exists and is awaiting due/dispatch work
processing    one worker owns the current attempt
delivered     provider-neutral delivery success recorded
suppressed    eligibility/staleness/channel policy prevents delivery
failed        terminal failure after the approved retry policy
```

Transient failures remain retryable while the intent is pending/queued; they do not need a second status unless implementation evidence requires it. `failed` must mean terminal or operator-review state, not an ambiguous temporary error.

## Retry and Failure Semantics

| Failure | Recommendation |
| --- | --- |
| Provider timeout/network failure | Retry with bounded approved backoff. |
| Provider rate limit | Retry only with provider-neutral bounded delay and no amplification. |
| Invalid destination | Suppress or terminal-fail; do not retry unchanged data indefinitely. |
| Permanent provider rejection | Terminal `failed`; preserve safe classification only. |
| Appointment cancelled/rescheduled stale intent | Suppress immediately before delivery. |
| Application bug/serialization failure | Terminal failure plus safe operational signal; no raw payload logging. |
| Worker restart/lease expiry | Reclaim safely using attempt/processing timestamps and idempotent state transition. |

Retry counts, backoff, lease duration and operator recovery are Development-blocking decisions unless approved by business/architecture evidence.

## Queue and Scheduler Architecture

- Keep the existing `database` queue, `default` queue and `after_commit=true` configuration.
- Queue jobs should carry `notification_intent_id`, not Customer phone, full Appointment payload or rendered message body.
- Immediate intents can be enqueued after commit.
- Scheduled reminders should use a periodic scheduler that selects due durable intents and dispatches stable IDs. This is more restart-resilient than one delayed job per reminder and makes rescheduling/stale suppression explicit.
- A delayed job may be reconsidered only if due-query cost or operational evidence justifies it.
- Existing `retry_after=90` is infrastructure baseline, not an approved notification retry policy.
- Worker and scheduler process requirements remain operational documentation, not new infrastructure.

## Provider Abstraction

Conceptual provider responsibilities:

- Accept an already-approved provider-neutral message request.
- Return a provider-neutral success/failure result.
- Return an optional provider correlation reference.
- Classify provider errors as transient or permanent without deciding business eligibility.

Provider must not:

- Load or mutate Appointment domain state.
- Decide eligibility, consent or retry policy.
- Create Customer or Appointment records.
- Own business message wording or public API behavior.

Fake WhatsApp implementation, simulation and provider-specific tests belong to SPEC-008.

## Operational Visibility and Boundaries

Recommended minimum V1 observability is durable safe status plus redacted application-level outcome signals. A dedicated Admin Agenda indicator/dashboard is not required for delivery correctness and should remain deferred unless Yaris identifies a support need. No public notification status, unsubscribe, preference, webhook or lookup endpoint is recommended for SPEC-007 V1.

Real provider webhooks and delivery receipts are deferred. Fake provider behavior belongs to SPEC-008.

## Security and Privacy Threat Model

- PII leakage: minimize intent, queue, logs and errors; never log raw phone, provider credentials or full message bodies by default.
- Provider-secret leakage: keep credentials server-side and outside queue payloads/public responses.
- Duplicate messages: database dedupe identity plus worker state transition.
- Notification amplification: explicit event allowlist, bounded retries and no recursive Appointment mutation.
- Queue poisoning: stable opaque intent IDs, authorization at creation and current-state revalidation.
- Unauthorized triggering: no public endpoint; future admin surface must use existing auth boundary.
- Template injection: constrain template variables and separate provider payload encoding from message content.
- Stale delivery: current Appointment/intent checks immediately before dispatch.
- Replay: dedupe key and terminal state transitions must be atomic.

## Retention and Logging

Retention period for intents, provider references, error classes, payload snapshots and redacted logs is a BUSINESS/LEGAL DECISION REQUIRED. Distinguish operational history from provider payload and PII. Store provider-neutral error classifications, not raw provider responses or request bodies. Use safe correlation identifiers; never use raw phone or secrets as identifiers.

## Performance and Concurrency

No production traffic number is assumed. Analyze synthetic technical scales of 10, 100 and 1000 appointments only for query/queue behavior. A due-intent query will likely need a composite status/scheduled-time index if schema is approved; this is not a migration decision yet.

Concurrency scenarios requiring MySQL integration tests:

- Two workers claim the same intent.
- A retry overlaps the original provider attempt.
- Reschedule races reminder delivery.
- Cancellation races reminder delivery.
- Duplicate publication of one appointment event.
- Worker restart during processing.

The notification path must not acquire or reorder SPEC-004 Appointment locks. Intent uniqueness/claim transitions should be local to notification persistence.

## Database and Deletion Strategy

If durable persistence is approved, use database FKs and uniqueness constraints for Appointment relation and deduplication. Appointment lifecycle/history currently restricts deletion; notification records must not weaken that behavior. Customer relation and destination snapshot deletion semantics require a privacy/business decision. Do not cascade in a way that removes required operational evidence without retention approval.

## Testing Strategy

Later approved Development should test:

- Eligibility for each approved event type.
- Post-commit dispatch.
- Rollback produces no executable notification intent.
- Deduplication and repeated event publication.
- Two workers claiming one intent.
- Reschedule stale suppression.
- Cancellation stale suppression.
- Transient retry and terminal failure classification.
- Provider isolation and provider-neutral result mapping.
- Privacy/log safety and secret redaction.
- Business timezone reminder calculation and DST cases where applicable.
- Queue restart/delay behavior.
- Customer phone-change semantics after business approval.
- No Appointment or AppointmentHistory mutation from delivery.
- No duplicate message delivery under the approved provider contract.

Use MySQL integration tests for transaction, uniqueness, queue persistence and concurrency behavior. Use the dedicated `agenda_estetica_test` database and existing test isolation conventions. Do not add tests or implementation during this Discovery branch.

## Safe Experiments

No temporary experiments were performed. Framework findings came from read-only inspection of installed configuration and source. Cleanup result: no temporary files, fixtures, jobs, cache entries or processes were created.

## ADR Assessment

ADR required: `YES` for the recommended durable Notification Intent/post-commit integration because it is a durable cross-cutting boundary involving SPEC-004 transaction integration and future persistence. Draft only:

```text
docs/architecture/adr/ADR-004-notification-intent-after-commit.md
Status: DRAFT / REQUIRES HUMAN APPROVAL
```

No ADR is accepted automatically and no implementation is authorized by the draft.

## Development Blockers

- Business approval of V1 notification event types and whether cancellation/reschedule messages are required.
- Business/legal decision on transactional consent, absent consent behavior, channel-specific consent, revocation and opt-out.
- Approved delivery channel concept and handoff boundary to SPEC-008 Fake WhatsApp.
- Reminder lead times, number of reminders, quiet hours, holiday behavior and timezone policy.
- Recipient current-versus-snapshot semantics when Customer contact data changes.
- Message/template ownership, language, variables and snapshot policy.
- Durable Notification Intent schema, dedupe identity, retention and FK/deletion behavior.
- Event-source integration point with SPEC-004 and acceptance of the transactional intent/ADR-004 recommendation.
- Retry/backoff, lease, terminal failure and operator recovery policy.
- Minimum operational visibility and authorization boundary.

## Non-Blocking Pending Decisions

- Exact provider-neutral error vocabulary after event/channel policy is approved.
- Whether a separate attempt-history record is justified beyond intent attempt counters.
- Due-query cadence and worker process sizing after a synthetic benchmark.
- Optional provider correlation reference format.

## Deferred Decisions

- Fake WhatsApp implementation and simulation in SPEC-008.
- Real WhatsApp, Meta, SMS, email providers and webhooks.
- Customer consent-management UI and notification history UI.
- Marketing campaigns, bulk messaging and analytics.
- Generic outbox beyond the focused Notification Intent.
- SPEC-008+ and all later roadmap items.

## Final Recommended Checkpoints

1. **Checkpoint A - Event and Eligibility Contract:** approve event allowlist, transactional/promotional boundary, consent, channel, timing and authority rules.
2. **Checkpoint B - Durable Intent and Idempotency:** approve ADR-004, schema, event identity, dedupe, recipient/template snapshots and retention.
3. **Checkpoint C - Queue and Failure Processing:** implement post-commit processing, due scheduling, retries, stale suppression and terminal outcomes.
4. **Checkpoint D - Provider Boundary:** implement provider-neutral abstraction only; keep Fake WhatsApp in SPEC-008.
5. **Checkpoint E - Operational Read and Security:** add only an approved authenticated operational outcome surface and redaction hardening.
6. **Checkpoint F - Final Tests and Audit:** complete reliability, privacy, scope and acceptance verification before closure.

No checkpoint is authorized by this Discovery.

## Development Readiness

```text
Technical Discovery: COMPLETED / BLOCKED FOR DEVELOPMENT
Development: NOT AUTHORIZED
Checkpoint A: NOT AUTHORIZED
```

The architecture recommendation is documented, but Development must not begin until the listed business and cross-SPEC blockers are resolved and human approval is granted.

## Scope and Implementation Audit

```text
new application code: NONE
new migrations: NONE
new tables: NONE
new routes: NONE
new Jobs: NONE
new Event/Listener classes: NONE
new provider implementation: NONE
new Vue: NONE
new dependencies: NONE
new production configuration: NONE
```

SPEC-003, SPEC-004, SPEC-005, SPEC-006 and existing accepted ADRs remain unchanged. SPEC-008 was not started.

## Regression Evidence

Discovery changed only documentation. Existing application regression remains the approved baseline:

- Backend: `208 tests / 1202 assertions`, PASS.
- SPEC-004 concurrency: `17 tests / 212 assertions`, PASS, `0 skipped`.
- Frontend: `15 files / 55 tests`, PASS.
- Pint, PHPStan, Composer validate/audit, ESLint, TypeScript, build and npm audit: PASS.
- No new functionality tests were added during Discovery.

## Final Discovery State

```text
SPEC-007: TECHNICAL DISCOVERY COMPLETED / BLOCKED FOR DEVELOPMENT
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

## Recommended Next Action

STOP. Submit this Technical Discovery and draft ADR-004 for human review. Resolve the Development-blocking business and cross-SPEC decisions before authorizing Checkpoint A. Do not implement Notification Engine or start SPEC-008.
