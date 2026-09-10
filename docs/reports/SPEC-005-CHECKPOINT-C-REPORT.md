# SPEC-005 CHECKPOINT C REPORT

## Scope

Checkpoint C implements only authenticated administrative Appointment creation and rescheduling. It does not implement terminal actions, Customer CRUD, Service/Professional CRUD, Schedule/TimeOff CRUD, availability preview, slots, auto-assignment, notifications, payments or Checkpoint D.

## API Surface

Added exactly:

```text
POST /api/v1/admin/agenda/appointments
POST /api/v1/admin/agenda/appointments/{appointment}/reschedule
```

Both use existing `auth:sanctum`. Existing read routes remain unchanged. No Cancel, Complete or NoShow endpoint was added.

## Authority and Transport

- Create delegates directly to `CreateAppointment`.
- Reschedule delegates directly to `RescheduleAppointment`.
- Controllers do not write Appointment or AppointmentHistory directly.
- Create whitelist: Customer, Service, Professional, `starts_at`, `ends_at`.
- Reschedule whitelist: Professional, `starts_at`, `ends_at`.
- Caller cannot provide status, duration, price, source, notes, capacity or history.
- Timestamps require ISO-8601 with explicit offset or UTC `Z`.
- Backend canonicalizes accepted timestamps to UTC.
- Domain validation remains SPEC-004 authority.

## Conflict Contract

- `422`: malformed/ambiguous transport input or validation failure.
- `409 appointment_unavailable`: authoritative availability/capacity/domain conflict.
- `409 appointment_state_conflict`: reschedule of a non-confirmed Appointment.
- No SQLSTATE, exception class, table name, lock detail or stack trace is exposed.

## Create Behavior

- Authenticated admin selects an existing Customer, Service, Professional and local date/time.
- Existing Service duration is used only for UX end-time derivation.
- `CreateAppointment` re-reads current Service duration and remains authoritative.
- Successful response is `201` with the approved detail projection.
- Domain conflict leaves no Appointment or History partial write.
- Customer creation remains out of scope.

## Reschedule Behavior

- Only `professional_id`, `starts_at` and `ends_at` are accepted.
- Customer, Service, duration and status remain immutable.
- UI derives end time from the historical `Appointment.duration_minutes` snapshot.
- Successful response is `200` with refreshed detail/history.
- Terminal Appointment reschedule returns `409 appointment_state_conflict`.

## Temporal UX

- The frontend consumes `GET /api/v1/admin/agenda/context` for `BusinessProfile.timezone`.
- Local date/time selection resolves through an explicit native `Intl`-based utility.
- Normal local times resolve to UTC.
- DST gaps and folds reject locally without submission.
- Browser/device timezone is never authoritative.
- Create/Reschedule end time is calculated as elapsed duration from resolved UTC start.

## Frontend Scope

- Create form is read-only-domain-authority UI with existing Customer/Service/Professional selectors.
- Reschedule form appears only for confirmed Appointments.
- Customer, Service, duration and status are not editable during Reschedule.
- Only Create and Reschedule controls are present.
- Cancel, Complete and NoShow controls/methods remain absent for Checkpoint D.
- No mutation API method is added for terminal actions.

## Test Evidence

- Admin Agenda API suite: 20 tests, 59 assertions, PASS.
- Full backend: 133 tests, 606 assertions, PASS.
- SPEC-004 concurrency: 17 tests, 212 assertions, PASS, not skipped.
- Frontend: 12 files, 29 tests, PASS.
- Pint: PASS.
- PHPStan: PASS.
- Composer validate/audit: PASS.
- ESLint, TypeScript, build and npm audit: PASS.
- Route audit: eight Admin Agenda routes total, six GET and two POST; no terminal mutation routes.
- No migrations, tables or dependencies added.

## Scope Audit

Not implemented: Checkpoint D, Cancel, Complete, NoShow, Customer CRUD, Service CRUD, Professional CRUD, Schedule CRUD, TimeOff CRUD, BusinessHours CRUD, availability preview, slot generation, auto-assignment, notifications, payments, calendar dependency and SPEC-006+.

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
