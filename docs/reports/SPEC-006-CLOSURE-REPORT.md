# SPEC-006 CLOSURE REPORT

## Closure Status

- SPEC-006: `CLOSED / READY FOR MERGE`.
- Human acceptance: `APPROVED` for Definition, Checkpoints A-F and formal closure.
- Merge to `main`: `NOT AUTHORIZED`.
- Branch deletion: `NOT AUTHORIZED / NOT PERFORMED`.
- SPEC-007: `NOT AUTHORIZED`.

## Executive Summary

SPEC-006 delivered the Public Booking V1 consumer for AgendaEstetica. Guests can select an active Service, a specific compatible Professional and a server-provided business-local time at `/reservar`, provide name and phone, submit without an account and receive an on-screen confirmed booking. The resulting normal Appointment is visible through the existing Admin Agenda read API.

The final public API remains exactly four GET routes and one POST route. Availability, appointment creation, Customer identity, timezone and concurrency remain delegated to their approved authorities. Security hardening adds the approved booking IP and normalized-phone limits using existing Laravel cache/rate-limiter infrastructure.

## Scope Delivered

- Public guest booking at `/reservar` using the existing Vue SPA and `PublicLayout`.
- Public API surface: `4 GET / 1 POST`.
- Active Service selection with authoritative pricing labels.
- Specific compatible Professional selection.
- Business-local date and server-generated bookable slot selection.
- Backend-authoritative availability with timezone/DST handling.
- Required Customer `name` and `phone` without Customer authentication.
- Approved Customer create/reuse resolution and transactional rollback behavior.
- Immediate confirmed Appointment creation through `CreateAppointment`.
- Normal visibility through the existing Admin Agenda read API.
- Idempotency-Key duplicate safety, replay, conflict and bounded in-progress handling.
- Booking `5/min/IP` and new-execution `3/hour/HMAC(phone_normalized)` abuse limits.
- Safe errors, privacy minimization, non-enumeration, accessible responsive UX and on-screen confirmation.

## Explicitly Excluded

- Customer accounts, Customer login and Professional accounts or portal.
- Any Professional selection and automatic assignment.
- Public cancellation, rescheduling and appointment lookup.
- Payments, deposits and checkout.
- Outbound WhatsApp, email and SMS notifications.
- New Appointment statuses such as pending or requested.
- Public week/month scheduler, drag/drop and calendar libraries.
- New API endpoints, schema, dependencies, infrastructure, CAPTCHA and external bot services.
- Availability redesign, Customer identity redesign, idempotency schema redesign and SPEC-007+.

These are approved V1 boundaries, not defects.

## Architecture and Authority

- SPEC-003 Business Core remains authoritative for BusinessProfile, Service, Professional, compatibility, Customer and `CustomerPhoneNormalizer`.
- SPEC-004 Appointment Engine remains authoritative for availability, duration, Appointment persistence, status, history, capacity, locks and concurrency.
- SPEC-005 Admin Agenda remains the downstream operational read consumer.
- `CheckAppointmentAvailability` remains availability authority.
- `CreateAppointment` remains Appointment creation authority.
- `BusinessProfile.timezone` remains temporal authority.
- ADR-003 lock ordering remains unchanged.
- SPEC-006 adds no direct Appointment or AppointmentHistory writes.

## Final Public Surface

```text
GET  /api/v1/public/booking/context
GET  /api/v1/public/booking/services
GET  /api/v1/public/booking/professionals
GET  /api/v1/public/booking/availability
POST /api/v1/public/booking/appointments
```

Frontend public booking route: `/reservar`.

No public Customer CRUD, Appointment GET, History, cancellation, reschedule, lookup, payment or notification route exists.

## Checkpoint History

| Checkpoint | Scope | Result | Evidence |
| --- | --- | --- | --- |
| A | Public context, catalog and compatible Professionals | COMPLETED / APPROVED | `SPEC-006-CHECKPOINT-A-REPORT.md` |
| B | Bounded bookable-time availability | COMPLETED / APPROVED | `SPEC-006-CHECKPOINT-B-REPORT.md` |
| C | Customer resolution and booking mutation | COMPLETED / APPROVED | `SPEC-006-CHECKPOINT-C-REPORT.md` |
| D | Public Vue booking flow | COMPLETED / APPROVED | `SPEC-006-CHECKPOINT-D-REPORT.md` |
| E | Security, abuse and conflict hardening | COMPLETED / APPROVED | `SPEC-006-CHECKPOINT-E-REPORT.md` |
| F | Final tests, documentation and acceptance audit | COMPLETED / APPROVED | `SPEC-006-CHECKPOINT-F-REPORT.md` |

## Acceptance Evidence

- Definition historical acceptance: `20/20 PASS`.
- Final implementation acceptance: `34/34 PASS`.
- No blocking defect remains.
- Admin integration test: `tests/Feature/Api/PublicBookingAppointmentTest.php` - `makes a public confirmed booking visible through the existing Admin Agenda read`.

The historical Definition matrix and final implementation matrix remain separate in the Checkpoint-F report.

## Security and Privacy

- Catalog/context: `120/min/IP`.
- Professionals: `60/min/IP`.
- Availability: `30/min/IP`.
- Booking HTTP requests: `5/min/IP`.
- New valid booking executions: `3/hour/HMAC-SHA256(normalized phone)` using `config('app.key')`.
- Successful same-key replay, idempotency conflict, in-progress conflict and transport `422` do not consume new phone execution quota.
- Valid unavailable execution consumes phone quota.
- Public throttles use safe JSON `429` with `too_many_requests`; Laravel `Retry-After` is preserved for route throttling.
- No Customer ID, Appointment ID, normalized phone, matching branch, raw phone, raw Idempotency-Key, HMAC or `app.key` is exposed or explicitly logged by SPEC-006.
- Same-origin stateful XSRF remains enabled; public booking does not use `auth:sanctum`; CORS is unchanged.
- CAPTCHA and external anti-bot services were not added.

## Final Quality Evidence

- Backend: `208 tests / 1202 assertions`, PASS.
- Focused A: `12 tests / 244 assertions`, PASS.
- Focused B: `16 tests / 88 assertions`, PASS.
- Focused C: `17 tests / 85 assertions`, PASS.
- Focused E: `9 tests / 41 assertions`, PASS.
- SPEC-004 concurrency: `17 tests / 212 assertions`, PASS, `0 skipped`.
- Frontend: `15 files / 55 tests`, PASS.
- ESLint, TypeScript, build and npm audit: PASS.
- Pint, PHPStan, Composer validate and Composer audit: PASS.
- Foundation health/auth/unknown API regressions: PASS.
- Representative availability limitation unchanged: `96 candidates / 295 DB queries / 1.36 s local`.

## Accepted V1 Limitations

- Availability retains the approved one-day, 96-candidate implementation and benchmark.
- No Playwright/browser E2E suite is included.
- Idempotency retains the narrow DB-commit to success-cache-write crash window.
- No mathematical exactly-once guarantee is claimed.

These are accepted V1 limitations, not closure defects.

## Persistence and Dependencies

```text
new tables: NONE
new migrations: NONE
new columns: NONE
new indexes: NONE
new constraints: NONE
new Composer dependencies: NONE
new npm dependencies: NONE
new infrastructure: NONE
```

SPEC-003, SPEC-004, SPEC-005 and ADR-003 remain unchanged.

## Git and Branch State

- Closure commit is documentation-only.
- Feature branch: `feat/spec-006-public-booking`.
- `main`: unchanged.
- `origin/main`: unchanged.
- Branch deletion: not performed.
- Merge: not performed.
- Feature branch remains ready for separately authorized merge.

## Final State

```text
SPEC-006: CLOSED / READY FOR MERGE
Definition: COMPLETED / APPROVED
Technical Discovery: COMPLETED / APPROVED
Checkpoint A: COMPLETED / APPROVED
Checkpoint B: COMPLETED / APPROVED
Checkpoint C: COMPLETED / APPROVED
Checkpoint D: COMPLETED / APPROVED
Checkpoint E: COMPLETED / APPROVED
Checkpoint F: COMPLETED / APPROVED
Closure: COMPLETED / APPROVED
Merge: NOT AUTHORIZED
SPEC-007: NOT AUTHORIZED
```

## Final Closure Decision

```text
SPEC-006 - Public Booking: FORMALLY CLOSED
Implementation: COMPLETE
Definition historical acceptance: 20 / 20 PASS
Final implementation acceptance: 34 / 34 PASS
Blocking defects: NONE
Scope leakage: NONE
Closed-SPEC changes: NONE
Merge readiness: READY FOR MERGE
Merge authorization: NOT AUTHORIZED
```

STOP. Submit closure evidence for review and wait for explicit merge authorization.
