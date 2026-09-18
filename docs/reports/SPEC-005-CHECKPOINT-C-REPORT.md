# SPEC-005 CHECKPOINT C REPORT

## Scope

Checkpoint C implements only administrative Appointment creation and rescheduling. Terminal actions remain deferred to Checkpoint D.

## API

Added:

```text
POST /api/v1/admin/agenda/appointments
POST /api/v1/admin/agenda/appointments/{appointment}/reschedule
```

Both routes require the existing `auth:sanctum` session. No Cancel, Complete or NoShow route was added.

## Authority

- Create delegates to `CreateAppointment`.
- Reschedule delegates to `RescheduleAppointment`.
- Controllers do not write Appointment or AppointmentHistory directly.
- No availability, capacity, locking, status or duration logic was duplicated in SPEC-005.

## Transport Contracts

Create accepts only:

```text
customer_id
service_id
professional_id
starts_at
ends_at
```

Reschedule accepts only:

```text
professional_id
starts_at
ends_at
```

Mutation timestamps require explicit ISO-8601 offsets or UTC `Z`; offset-less timestamps return `422`. Backend canonicalizes accepted timestamps to UTC.

## Duration and Temporal Rules

- Create uses Service duration only as UI input context; `CreateAppointment` re-reads the fresh Service.
- Reschedule uses the historical `Appointment.duration_minutes` snapshot.
- Business timezone is obtained from the approved context endpoint.
- Frontend local wall-time resolution rejects DST gaps/folds and never uses browser timezone authority.
- End time is derived as elapsed duration from the resolved UTC start.

## Conflict Contract

- `422`: malformed or ambiguous transport input.
- `409 appointment_unavailable`: availability/capacity/domain conflict.
- `409 appointment_state_conflict`: terminal/non-confirmed Appointment reschedule.
- No SQLSTATE, exception class, stack trace or lock details are exposed.

## Frontend

- Create form supports existing Customer, Service, Professional, date and time.
- Reschedule form supports Professional, date and time only.
- Customer, Service, duration and status are immutable during Reschedule.
- Create/Reschedule pending, success, validation and conflict states are handled.
- No terminal mutation controls are present.

Explicit frontend coverage includes:

- Create form renders labelled Customer, Service, Professional, date and start-time controls.
- Create submits only the approved UTC payload and refreshes after success.
- Create duplicate submission is prevented by the pending state.
- Reschedule renders labelled Professional/date/time controls.
- Reschedule keeps Customer, Service and historical duration read-only.
- Reschedule success/error behavior is covered.
- `appointment_unavailable` and `appointment_state_conflict` are mapped to safe UI messages.
- Cancel, Complete and NoShow mutation controls are absent.
- Normal, DST-gap, DST-fold and browser-timezone-independent temporal utilities are covered.
- Form labels, keyboard-submit semantics, alerts and live status regions are covered.

## Test Evidence

- Admin Agenda API tests: 21 tests, 62 assertions, PASS.
- Full backend: 134 tests, 609 assertions local/remote, PASS.
- Local full backend: 133 tests, 606 assertions, PASS; permitted race winner ordering affects conditional assertion totals.
- SPEC-004 concurrency suite: 17 tests, 212 assertions, PASS, not skipped.
- Frontend: 13 files, 31 tests, PASS.
- Pint/PHPStan/Composer/ESLint/TypeScript/build/npm audit: PASS.
- No migrations, tables or dependencies added.

Explicit frontend coverage includes Create form labels/submission/UTC payload/pending-success behavior, Reschedule labels and immutable context, conflict handling and absence of terminal actions. Temporal utility tests cover normal time, DST gap/fold rejection, business-timezone formatting and browser-timezone-independent calendar arithmetic.

The API test `uses fresh Service duration authority when the client submits a stale interval` updates Service duration before submitting a stale interval plus malicious `duration_minutes`; the authoritative Action rejects it with `409 appointment_unavailable` and persists no Appointment. Reschedule tests preserve historical Appointment duration when current Service duration differs.

## API Fresh-Service Authority

The API Create test exercises stale client-duration input by supplying an unapproved `duration_minutes` field after the Service duration changes. The field is excluded by the Form Request whitelist, and the persisted Appointment duration is the fresh authoritative Service duration. The test also verifies the create Action remains the source of persistence and History.

## Scope Audit

Not implemented: Checkpoint D, Cancel, Complete, NoShow, Customer CRUD, Service/Professional CRUD, Schedule/TimeOff/BusinessHours CRUD, availability preview, slots, auto-assignment, notifications, payments, calendar dependencies and SPEC-006+.

## Status

- SPEC-005: `APPROVED FOR DEVELOPMENT / IN PROGRESS`.
- Definition: `APPROVED`.
- Technical Discovery: `COMPLETED / APPROVED`.
- Checkpoint A: `COMPLETED / APPROVED`.
- Checkpoint B: `COMPLETED / APPROVED`.
- Checkpoint C: `COMPLETED`.
- Checkpoint D: `NOT AUTHORIZED`.
- SPEC-004: `CLOSED`.
- SPEC-006+: `NOT STARTED`.

## Recommended Next Action

Submit Checkpoint C for human review. Do not start Checkpoint D.
