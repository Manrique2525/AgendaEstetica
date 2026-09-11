# ADR-004 - Notification Intent and Durable Event Ingestion

## Status

`DRAFT / REQUIRES HUMAN APPROVAL`

This ADR records the reconciled Technical Discovery recommendation for SPEC-007. It does not authorize implementation, schema changes, queue jobs, providers or changes to SPEC-003 through SPEC-006.

## Context

SPEC-004 is closed and owns the Appointment transaction, lifecycle and AppointmentHistory writes. Its Actions currently create or mutate Appointment and History records inside `DB::transaction()` and do not create notification work. SPEC-007 needs reliable event ingestion, duplicate suppression and stale-work handling without hiding a reverse dependency inside a listener or provider.

The application already has a database queue with `after_commit=true`, but no notification jobs, events, listeners, providers or notification persistence. AppointmentHistory is durable, append-only and committed with the originating mutation.

## Decision Under Review

Use two provider-neutral ingestion paths:

1. **Immediate lifecycle events:** a SPEC-007 ingestor reads committed eligible `AppointmentHistory` rows, records a durable high-water mark only after idempotent intent creation, and queues stable Notification Intent IDs. It does not modify SPEC-004 Actions or History.
2. **Scheduled reminders:** a scheduler queries current eligible Appointments, derives a business-local reminder occurrence using `BusinessProfile.timezone`, creates an idempotent Notification Intent and queues its stable ID.

Both paths share Notification Intent persistence, deduplication, queue processing, stale checks, provider abstraction and status/retry semantics.

## Options Considered

### Strategy A - Transactional Notification Intent

```text
Appointment mutation transaction
  -> Appointment / History
  -> NotificationIntent
  -> commit
  -> queue intent ID
```

This provides strong atomicity and minimizes event-loss windows, but requires modifying `CreateAppointment`, `RescheduleAppointment`, `CancelAppointment` and any other Appointment mutation that emits notifications. It creates a reverse integration from closed SPEC-004 into SPEC-007 persistence or an approved shared event boundary.

### Strategy B - AppointmentHistory-Driven Ingestion

```text
SPEC-004 writes Appointment / History
  -> commit
SPEC-007 ingestor reads committed History
  -> idempotently creates NotificationIntent
  -> queues intent ID
```

The current AppointmentHistory schema contains an auto-incrementing row ID, Appointment ID, event type, status changes, old/new UTC interval values, old/new Professional IDs and `created_at`. It provides stable source identity for created, rescheduled and status-change events without modifying SPEC-004.

Strategy B introduces bounded delivery latency and a small commit-to-ingestion window, but supports crash recovery through replay from a durable high-water mark and a unique Notification Intent deduplication identity. Re-reading an already ingested history row is safe because intent creation is idempotent.

## Reconciled Recommendation

Select **Strategy B for immediate lifecycle notifications** and current-Appointment scanning for scheduled reminders. Preserve SPEC-004 exactly as the Appointment/History authority.

The ingestor must:

- read only committed AppointmentHistory rows;
- process history IDs in deterministic ascending order;
- create intents and advance its high-water mark in one Notification Engine persistence transaction;
- advance the mark only after the corresponding intents are committed;
- safely reprocess the same history row after a crash using the unique dedupe key;
- never call a provider or mutate Appointment state;
- use a bounded overlap/replay strategy if a high-water mark is unavailable or recovery is required.

For reminders, the scheduler must query current Appointment status and `starts_at`, derive occurrences through `BusinessProfile.timezone`, and create an intent keyed by the Appointment plus approved reminder occurrence/version. A reschedule creates a new occurrence; cancelled or no-longer-confirmed appointments suppress old work.

## Consequences

- No changes to `CreateAppointment`, `RescheduleAppointment`, `CancelAppointment`, `CompleteAppointment`, `MarkAppointmentNoShow` or AppointmentHistory are required for the recommended initial event source.
- Immediate notifications have scheduler/ingestor latency and need a documented processing cadence.
- A durable NotificationIntent model remains recommended for dedupe, retries, stale suppression and safe status.
- The ingestion high-water mark and intent unique identity must be durable and transactionally safe within SPEC-007.
- A separate generic outbox is not introduced.
- Provider-specific code remains behind a separate abstraction; Fake WhatsApp remains SPEC-008.

## Required Constraints

- SPEC-004 lock order and transaction boundaries remain unchanged.
- AppointmentHistory is read-only to SPEC-007.
- Notification dispatch occurs only after the Notification Intent transaction commits.
- Worker payloads carry stable intent IDs, not raw Customer phone or rendered message bodies.
- The worker re-reads the intent and current Appointment before delivery.
- Consent, event allowlist, channels, recipient semantics, retention and retry policy require human/business approval before Development.

## Rejected Alternatives

- Provider calls from Appointment Actions: couples domain mutation to delivery and risks pre-commit sends.
- Notification orchestration in Controllers: violates the architecture and bypasses domain authority.
- Queue-only fire-and-forget: lacks durable dedupe/stale state and can lose work after commit.
- AppointmentHistory polling without a durable high-water mark and unique intent: can skip or duplicate events.
- Generic cross-domain outbox: broader than the single notification consumer.
- Redis/distributed locks: outside the approved architecture and unnecessary for this bounded design.

## Open Approval Questions

- Is AppointmentHistory-driven ingestion acceptable for immediate lifecycle notifications despite bounded scheduler latency?
- Is the focused NotificationIntent plus durable ingestion mark sufficient, without a generic outbox?
- Which event, consent, channel, recipient, retention and retry decisions are approved for Development?
