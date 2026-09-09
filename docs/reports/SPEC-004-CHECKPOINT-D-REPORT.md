# SPEC-004 CHECKPOINT D REPORT

## Scope

Checkpoint D implements only transactional Appointment operations: create, reschedule, cancel, complete and no-show. Checkpoint E concurrency stress/hardening remains unauthorized.

## Transaction Architecture

All mutation Actions use transactions, lock BusinessProfile first, lock affected Professional IDs in ascending order, lock Appointment when applicable, re-read authoritative state, revalidate availability, write Appointment/History and commit atomically.

## Actions

- `CreateAppointment` creates `confirmed` appointments with duration snapshots and `created` history.
- `RescheduleAppointment` supports confirmed appointments, preserves duration/status, excludes the current Appointment from C checks and writes `rescheduled` history.
- `CancelAppointment`, `CompleteAppointment` and `MarkAppointmentNoShow` implement only approved confirmed transitions and write `status_changed` history.

No generic transition Action, timing policy, payment behavior or notification behavior was added.

## Checkpoint C Reuse

`CheckAppointmentAvailability` now supports optional `excludeAppointmentId` and `expectedDurationMinutes` for rescheduling. Defaults preserve Checkpoint-C behavior.

## Test Evidence

- Backend: 96 tests, 335 assertions, PASS.
- Frontend: 10 files, 24 tests, PASS.
- MySQL: 8.4.11.
- Test DB: `agenda_estetica_test`.
- Fresh migration, rollback and re-migration: PASS.
- Composer/PHP/frontend gates: PASS.
- Quality CI: PASS.

Covered operations include successful/failed create, stale Service reread, self-exclusion, same/different Professional reschedule, duration preservation, no-op reschedule, terminal-state rejection, status transitions, capacity release and history ordering.

## Scope Audit

Not implemented: Checkpoint E race stress, custom retry abstraction, new APIs, frontend, notifications, payments, slots, auto-assignment, special hours or SPEC-005.

## Status

- SPEC-004: `APPROVED FOR DEVELOPMENT / IN PROGRESS`.
- Checkpoint A: `COMPLETED / APPROVED`.
- Checkpoint B: `COMPLETED / APPROVED`.
- Checkpoint C: `COMPLETED / APPROVED`.
- Checkpoint D: `COMPLETED`.
- Checkpoint E: `NOT AUTHORIZED`.
- SPEC-005: `NOT STARTED`.

## Recommended Next Action

Submit Checkpoint D for human review. Do not start Checkpoint E.
