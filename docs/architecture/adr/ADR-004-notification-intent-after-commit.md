# ADR-004 - Notification Intent and Post-Commit Delivery Direction

## Status

`DRAFT / REQUIRES HUMAN APPROVAL`

This ADR records the Technical Discovery recommendation for SPEC-007. It does not authorize implementation, schema changes, queue jobs, providers or changes to SPEC-003 through SPEC-006.

## Context

The Notification Engine must process committed appointment events, suppress stale work, prevent duplicate delivery and survive queue retries or worker restarts. The current application has a database queue with `after_commit=true`, but no notification jobs, events, listeners, providers or notification persistence. Appointment lifecycle remains authoritative in SPEC-004.

## Options Considered

1. Queue-only fire-and-forget dispatch after an appointment action.
2. Poll AppointmentHistory without a durable notification intent.
3. A durable Notification Intent record created transactionally with the originating appointment mutation, then processed through the database queue after commit.
4. A generic transactional outbox subsystem for all future domains.

## Provisional Recommendation

Use a focused Notification Intent as the bounded notification outbox for SPEC-007. The intent should be created in the same transaction as the authoritative appointment mutation, carry a provider-neutral event identity and protected minimum data, and become executable only after commit through the existing database queue. A due-intent scheduler should enqueue stable intent IDs, and the worker should re-read current authoritative state before delivery.

Do not introduce a generic cross-domain outbox or Redis. Do not let a provider decide appointment eligibility, consent, retry policy or Appointment state.

## Rationale

- Queue-only dispatch can lose work in the process window after commit and before dispatch, and has no durable stale-work or deduplication record.
- History polling is delayed, couples notification timing to operational history and still requires a durable deduplication cursor.
- A focused intent supports idempotency, stale suppression, retry state, safe operational visibility and provider replacement at the scale of one business.
- A generic outbox is broader than the current need and would create an architecture not justified by the single notification consumer.

## Required Constraints

- The integration point must preserve SPEC-004 lock order and Appointment transaction authority.
- Notification dispatch must never occur before commit.
- Intent uniqueness must distinguish appointment event/history identity, notification type, channel and scheduled occurrence/version.
- Worker payloads should carry stable identifiers rather than raw Customer phone or rendered message bodies.
- Consent/channel rules, event coverage, recipient snapshot semantics, retention and schema require human/business approval before Development.

## Consequences

- A later approved Development checkpoint may require a durable notification intent migration and a narrowly scoped integration with appointment mutations.
- The worker and scheduler must revalidate current Appointment state and intent identity immediately before provider dispatch.
- Provider-specific code remains behind a separate abstraction; Fake WhatsApp stays in roadmap item 08.
- This ADR must be accepted or replaced before any implementation relying on the recommendation.

## Open Approval Questions

- Is the transactional Notification Intent integration with SPEC-004 acceptable as the V1 event source?
- Is the proposed focused outbox sufficient, or is a broader outbox justified by evidence?
- Which event, consent, channel, recipient, retention and retry decisions are approved for Development?
