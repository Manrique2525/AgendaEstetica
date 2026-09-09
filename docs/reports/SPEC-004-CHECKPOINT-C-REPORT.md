# SPEC-004 CHECKPOINT C REPORT

## Scope

Checkpoint C implements only the read-only `CheckAppointmentAvailability` engine. It does not create or mutate Appointments, histories, schedules or time-off.

## Availability Contract

Inputs are a specific Service, a specific Professional and UTC start/end `CarbonImmutable` values. The result is a boolean. No API, DTO framework or consumer workflow was added.

## Enforced Rules

- Positive requested interval.
- Elapsed duration exactly matches `Service.duration_minutes`.
- Category, Service and Professional are active.
- ProfessionalService compatibility exists.
- BusinessHours fully cover the interval.
- ProfessionalSchedule fully covers the interval.
- ProfessionalTimeOff does not overlap.
- Confirmed Appointment overlap does not exist for the Professional.
- Global confirmed-capacity peak remains within BusinessProfile configuration.

## Temporal and DST Behavior

- Concrete inputs are UTC instants.
- Recurring schedules resolve local `TIME` values using BusinessProfile timezone.
- Adjacent recurring intervals are merged for coverage.
- DST gaps are rejected.
- Ambiguous DST folds are rejected.
- No timezone dependency or conversion service was added.

## Overlap and Capacity

Half-open `[start, end)` semantics are used. Existing confirmed Appointment overlap uses `existing_start < candidate_end AND existing_end > candidate_start`. Terminal appointments do not block. Capacity uses a half-open event sweep with end events before start events at equal timestamps. No lock or reservation is performed in C.

## Tests

`tests/Feature/Appointment/AvailabilityTest.php` covers valid/invalid intervals, duration, active states, compatibility, BusinessHours/ProfessionalSchedule coverage and gaps, TimeOff, confirmed overlap, terminal states, non-naive capacity, DST gap/fold rejection and no side effects.

## Scope Audit

Not implemented: Appointment mutations, history writes, availability locking, capacity reservations, schedule version, slot generation, APIs, frontend, payments, notifications and Checkpoint D.

## Evidence

- Backend: 82 tests, 286 assertions, PASS.
- Frontend: 10 files, 24 tests, PASS.
- MySQL: 8.4.11.
- Test DB: `agenda_estetica_test`.
- Migration lifecycle: PASS.
- Composer/PHP/frontend gates: PASS.
- Route audit: no C routes.

## Status

- SPEC-004: `APPROVED FOR DEVELOPMENT / IN PROGRESS`.
- Checkpoint A: `COMPLETED / APPROVED`.
- Checkpoint B: `COMPLETED / APPROVED`.
- Checkpoint C: `COMPLETED`.
- Checkpoint D: `NOT AUTHORIZED`.
- SPEC-005: `NOT STARTED`.

## Commits and CI

- `0cce8e0 feat: add SPEC-004 availability and capacity engine`.
- `01ccbb7 docs: report SPEC-004 checkpoint C`.
- Push: PASS.
- Workflow: `Quality`.
- Run: `34374012470`.
- Backend: PASS.
- Frontend: PASS.

## Synchronization

The implementation branch is synchronized at `01ccbb7` and the working tree is clean.

## Recommended Next Action

Submit Checkpoint C for human review. Do not start Checkpoint D.
