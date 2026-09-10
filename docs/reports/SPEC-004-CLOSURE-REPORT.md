# SPEC-004 CLOSURE REPORT

## Closure Status

- SPEC-004: `CLOSED`.
- Human acceptance: `APPROVED`.
- Closure authorization: `GRANTED`.
- Merge to `main`: `NOT AUTHORIZED`.
- Production release: `NOT AUTHORIZED`.
- SPEC-005: `DEFINITION COMPLETED / AWAITING HUMAN APPROVAL`.

## Executive Summary

SPEC-004 delivered the backend/domain Appointment Engine for AgendaEstetica. The delivered scope includes appointment persistence and history, professional schedules, professional TimeOff, requested-interval availability, global capacity, transactional mutations, deterministic locking, real MySQL concurrency hardening and temporal/DST validation.

No API, frontend consumer, Admin Agenda, Public Booking, notifications, payments or SPEC-005 functionality was introduced.

## Approved Checkpoints

| Checkpoint | Scope | Result |
| --- | --- | --- |
| A | Appointment persistence, statuses and history | COMPLETED / APPROVED |
| B | Professional schedule and TimeOff foundation | COMPLETED / APPROVED |
| C | Availability, overlap and capacity engine | COMPLETED / APPROVED |
| D | Transactional Appointment operations | COMPLETED / APPROVED |
| E | Real MySQL concurrency hardening | COMPLETED / APPROVED |
| F | Final tests, documentation and audit | COMPLETED / APPROVED |

## Final Acceptance

- Acceptance criteria: `18/18 PASS`.
- Technical blockers: `NONE`.
- Acceptance blockers: `NONE`.
- Human acceptance: explicitly granted by the user after Checkpoint F.

## Final Domain Model

The final SPEC-004 table set is exactly:

```text
appointments
appointment_histories
professional_schedules
professional_time_off
```

Final persistent statuses are exactly `confirmed`, `cancelled`, `completed` and `no_show`. Only `confirmed` blocks the selected Professional and counts toward global capacity. Terminal states cannot reopen.

Concrete appointment and TimeOff instants use UTC-contract `DATETIME`; recurring BusinessHours and ProfessionalSchedule use business-local `TIME` interpreted through `BusinessProfile.timezone`. DST gaps and ambiguous folds are rejected unless a future consumer supplies explicit disambiguation.

Appointment duration is a historical snapshot. Appointment price, names, notes, source, public IDs, idempotency and `schedule_version` remain outside SPEC-004 V1.

## Availability and Capacity

Availability validates interval order, duration, active states, compatibility, BusinessHours, ProfessionalSchedule, TimeOff, selected-Professional overlap and global capacity.

Capacity uses confirmed appointments, half-open intervals and an event sweep with end events before start events at equal timestamps. Naive overlapping-row counts are not used.

## Transactions and Concurrency

The canonical lock order is:

```text
BusinessProfile
affected Professionals ascending by ID
Appointment when applicable
post-lock current/locking reads
revalidation
Appointment/History writes
commit
```

Under MySQL `REPEATABLE-READ`, Checkpoint E reproduced a stale snapshot after a lock wait. The implementation now uses current/locking reads after canonical locks. Lock order remained unchanged and no custom retry abstraction was added.

Final concurrency evidence:

- 17 dedicated tests, PASS, not skipped.
- Same-Professional Create: 10 iterations.
- Cross-Professional capacity=1 Create: 10 iterations.
- Opposite-direction Reschedule: 10 iterations.
- Capacity, lifecycle, release and schedule replacement races: PASS.
- Double booking: prevented.
- Capacity oversubscription: prevented.
- Deadlocks: 0.
- Lock timeouts: 0.
- Worker timeouts: 0 in final isolated run.
- Unexpected SQL errors: 0.

## Final Quality Evidence

Local final evidence:

- Backend: 113 tests, 547 assertions, PASS.
- Dedicated concurrency: 17 tests, 212 assertions, PASS.
- Frontend: 10 files, 24 tests, PASS.
- Composer, Pint, PHPStan, ESLint, TypeScript, build, npm audit and `git diff --check`: PASS.
- Testing database lifecycle fresh/rollback/re-migration: PASS.
- MySQL: `8.4.11`, InnoDB, `REPEATABLE-READ`, `agenda_estetica_test`.

Closure-head Quality evidence:

- Workflow: `Quality`.
- Run: `34430086512`.
- Commit: `3e51637` (`3e51637a17a679c59f7517aebbc9bffb240556bd`).
- Branch: `feat/spec-004-appointment-engine`.
- Backend: PASS, 113 tests, 547 assertions.
- Concurrency: PASS, 17 tests, not skipped.
- Frontend: PASS, 10 files, 24 tests.

This closure commit is documentation-only; the final evidence synchronization commit below is also documentation-only and receives its own final-head Quality run.

## Deferred Boundaries

The following remain intentionally deferred and are not defects in SPEC-004:

- Admin Agenda and Public Booking API/UI.
- Slot generation and automatic Professional assignment.
- Notifications, WhatsApp, email, payments and deposits.
- BusinessSpecialHours and buffers.
- Public IDs, source and consumer idempotency.
- TimeOff administrative CRUD workflow.
- Production business data and official timezone selection.

## Git and Branch State

- Closure documentation commits: `b596ef0`, `3e51637`.
- Feature branch: `feat/spec-004-appointment-engine`.
- `main`: unchanged.
- `origin/main`: unchanged.
- Feature branch deletion: not performed.
- Working tree after final commit: clean.
- Local and remote feature branch: synchronized.

## Final State

SPEC-004 is formally closed with human acceptance. Merge to `main` requires separate explicit authorization. SPEC-005 has only a Definition and awaits human approval; Technical Discovery and Development have not started.
