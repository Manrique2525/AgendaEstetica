# SPEC-004 CHECKPOINT E REPORT

## Scope

Checkpoint E validates real MySQL concurrency for the already-approved D transaction architecture. Checkpoint F final audit is documented separately.

## Harness

`tests/Support/Concurrency/ConcurrentProcessRunner.php` launches independent OS PHP processes with `proc_open`, ready/start barrier files, bounded readiness/wait timeouts and cleanup. `tests/Support/Concurrency/worker.php` boots Laravel independently, receives testing-only configuration, invokes real production Actions and writes structured success/exception/SQLSTATE results.

The suite runs under `tests/Unit/Concurrency/AppointmentConcurrencyTest.php` so committed fixtures are visible to independent workers. It verifies `mysql`, `agenda_estetica_test`, MySQL `8.4.11` and InnoDB tables. Workers overlap in wall-clock execution after a shared start barrier and report structured domain/SQL outcomes.

## Isolation and Locking

The runtime isolation level is `REPEATABLE-READ`. D was corrected after reproducing a stale snapshot: `RescheduleAppointment` could establish a consistent read before waiting on a Professional lock. D now passes `useCurrentReads=true` to `CheckAppointmentAvailability`, which uses current/locking reads after canonical locks. The lock order remains BusinessProfile, affected Professionals ascending by ID, Appointment when applicable, re-read/revalidate, write/history and commit.

## Lock Blocking Evidence

- BusinessProfile lock: child Create waits until parent singleton lock commits, then succeeds.
- Professional lock: child Create acquires BusinessProfile and waits on the target Professional, then succeeds after release.
- Appointment lock: child Cancel waits on the locked Appointment, then succeeds after release.

## Race Results

| Scenario | Iterations | Result | Deadlocks | Timeouts |
| --- | ---: | --- | ---: | ---: |
| Same-Professional Create | 10 | one winner/one domain loser; no double booking | 0 | 0 |
| Cross-Professional capacity 1 Create | 10 | one winner/one domain loser; no oversubscription | 0 | 0 |
| Capacity 2, two creators | 1 | both succeed | 0 | 0 |
| Capacity 2, three creators | 1 | exactly two succeed | 0 | 0 |
| Same-target Reschedule | 1 | one winner/one domain loser; loser unchanged | 0 | 0 |
| Opposite-direction Reschedule | 10 | both succeed with sorted locks | 0 | 0 |
| Cancel vs Complete | 1 | one terminal winner and one domain loser | 0 | 0 |
| Cancel vs NoShow | 1 | one terminal winner and one domain loser | 0 | 0 |
| Reschedule vs Cancel | 1 | valid serializable outcome | 0 | 0 |
| Cancel + competing Create, released capacity | 1 | cancel-first allows Create; create-first rejects; no oversubscription | 0 | 0 |
| Complete + competing Create, released capacity | 1 | complete-first allows Create; create-first rejects; no oversubscription | 0 | 0 |

## Required Scenario Matrix

1. BusinessProfile lock blocking.
2. Professional lock blocking.
3. Appointment lock blocking.
4. Same-Professional/same-interval concurrent Create.
5. Cross-Professional Create at capacity 1.
6. Capacity 2 with two creators.
7. Capacity 2 with three creators.
8. Same-target Professional/interval concurrent Reschedule.
9. Opposite-direction Reschedules with ascending Professional locks.
10. Cancel versus Complete.
11. Cancel versus NoShow.
12. Reschedule versus Cancel.
13. Cancel plus competing Create after released capacity.
14. Complete plus competing Create after released capacity.
15. BusinessHours replacement versus Create.
16. ProfessionalSchedule replacement versus Create.
17. ProfessionalSchedule replacement versus Reschedule with post-lock validation.

## Coordination Results

- BusinessHours replacement versus Create: child waits and rejects against committed new hours.
- ProfessionalSchedule replacement versus Create: child waits and rejects against committed new schedule.
- ProfessionalSchedule replacement versus Reschedule: child waits, uses current reads after lock and rejects stale target schedule.

The two release races permit only these serial outcomes: the release mutation commits first and Create succeeds, or Create evaluates first and rejects while the release mutation succeeds. Neither outcome produces capacity oversubscription or partial history.

## Stale Snapshot Defect

The schedule/re-schedule test initially allowed an invalid reschedule after the parent committed a new schedule. Root cause: a prior consistent read under `REPEATABLE-READ` remained visible after the Professional lock wait. The minimal fix adds a D-only current-read mode to availability and locks fresh Service/related rows where required. ADR-003 lock order and C default read-only behavior remain unchanged.

## History and Invariants

- Every successful mutation has exactly one corresponding history event.
- Losing workers produce no history.
- Same-Professional confirmed double booking is prevented.
- Global confirmed capacity is never exceeded.
- Terminal status races produce exactly one terminal transition.
- Reschedule loser state and history remain unchanged.
- No worker timeouts, lock wait timeouts or unexpected SQL exceptions occurred.

The dedicated suite was also rerun in isolation after one intentionally parallelized local invocation exceeded the harness timeout while sharing the test database with other commands. The isolated result was 17 tests and 212 assertions, PASS; the timeout was not reproduced and did not produce a worker result, SQL exception or application deadlock. Assertion totals vary by serial winner because losing-worker detail assertions are conditional; the remote Quality run recorded the same 17 passing tests.

## Retry and Architecture

- Custom deadlock retry: none.
- Distributed/Redis lock: none.
- New tables/migrations: none.
- ADR-003 updated narrowly to require current/locking reads after locks.

## Production Defect Audit

- Production code changed: yes.
- Defect: under MySQL `REPEATABLE-READ`, Reschedule could retain a pre-lock consistent-read snapshot while waiting on a Professional lock.
- Reproduction: concurrent ProfessionalSchedule replacement versus Reschedule could incorrectly allow a target interval that the committed replacement removed.
- Root cause: stale snapshot after the lock wait.
- Fix: D mutation paths request current/locking reads after canonical locks; Checkpoint C's default read-only behavior is unchanged.
- ADR impact: ADR-003 was updated narrowly to record the post-lock current-read requirement; lock order remains valid.
- Regression: the stale-snapshot race passes repeatedly in the dedicated suite.

## Regression Evidence

- Concurrency suite: 17 tests, 212 assertions locally, PASS; remote Quality: 17 passed.
- Full Pest: 113 tests, 547 assertions locally, PASS; remote Quality: 113 passed (547 assertions in run 34427983370).
- Frontend: 10 files, 24 tests, PASS.
- Composer, Pint, PHPStan, ESLint, TypeScript, build and audits: PASS.
- Migration lifecycle on `agenda_estetica_test`: PASS.
- Foundation/C.1 API regressions: PASS.
- No API or frontend changes.

## Route and Security Audit

- `/api/v1/health`: existing regression returns 200 JSON.
- Unauthenticated `/api/v1/admin/auth/me` and `/api/v1/admin/auth/logout` return JSON 401 regressions.
- Unknown `/api/v1/*` returns JSON 404 regression.
- `php artisan route:list` contains no Appointment API, booking API or concurrency endpoint.
- No credentials, development data, temporary barrier files or Checkpoint-F changes are included.

## Scope Audit

Not implemented: Checkpoint F, stress beyond the bounded E suite, generic retry framework, API, frontend, notifications, payments, slots, auto-assignment, special hours and SPEC-005.

## Status

- SPEC-004: `READY FOR HUMAN ACCEPTANCE`.
- Checkpoints A-F: `COMPLETED / APPROVED`.
- Checkpoint E: `COMPLETED`.
- Checkpoint F: `COMPLETED`.
- SPEC-005: `NOT STARTED`.

## Recommended Next Action

Checkpoint E evidence is included in the Checkpoint F final audit. Do not close SPEC-004 without explicit human approval.
