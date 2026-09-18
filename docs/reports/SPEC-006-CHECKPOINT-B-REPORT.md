# SPEC-006 CHECKPOINT B REPORT

## Scope

Checkpoint B implements only the public bookable-time availability read endpoint. It adds bounded 15-minute candidate generation, one business-local date validation, the inclusive 90-calendar-day horizon, past-time exclusion, DST-safe local resolution, fresh Service duration use, SPEC-004 availability delegation, safe empty/unavailable responses, the availability rate limit and backend evidence. It does not implement Checkpoint C Customer resolution or booking mutation, Idempotency-Key, CreateAppointment public orchestration, phone rate limiting, `/reservar`, frontend booking, Checkpoint E/F, schema changes or dependencies.

## Route Surface

The final public SPEC-006 surface is exactly four GET routes and zero POST routes:

```text
GET /api/v1/public/booking/context
GET /api/v1/public/booking/services
GET /api/v1/public/booking/professionals?service_id={id}
GET /api/v1/public/booking/availability?service_id={id}&professional_id={id}&date=YYYY-MM-DD
```

No public booking POST, Customer route, availability preview variant, cancellation or reschedule route was added. Public routes remain outside `auth:sanctum`; Admin Agenda routes remain protected and unchanged.

## Request and Date Contract

`PublicBookingAvailabilityRequest` accepts only required positive integer `service_id`, required positive integer `professional_id` and required `date` in `YYYY-MM-DD` format. Extra query fields do not gain authority. `from`, `to`, `duration`, `ends_at`, `timezone`, `capacity` and `slot_increment` are not accepted inputs.

Dates are evaluated against the server's current instant in `BusinessProfile.timezone`. The allowed inclusive interval is:

```text
business-local today <= date <= business-local today + 90 calendar days
```

Past dates and dates beyond the horizon return JSON `422`. For today, candidate starts at or before business-local current time are omitted. No additional minimum lead time is applied.

## Candidate and Temporal Behavior

- Candidate starts are generated every 15 minutes from `00:00` through `23:45`, at most 96 before filtering.
- Candidate starts are resolved from business-local wall time using timezone transition offsets and round-trip matching.
- Nonexistent spring-forward starts and ambiguous fall-back starts are omitted.
- Candidate ends are derived from the current Service `duration_minutes` as elapsed UTC minutes; duration is never rounded.
- Browser/device timezone, request timezone and browser clock are not authoritative.
- Slots return canonical UTC `starts_at`/`ends_at` plus business-local `local_start`/`local_end`.
- Slots are unique and ordered by ascending generated start time.
- A returned slot is read-time availability only, not a reservation or hold.

## Resource and Domain Authority

Before candidate generation, the focused Action qualifies an active Service under an active ServiceCategory and an active Professional compatible with that Service. Invalid/inactive/incompatible resources return generic JSON `409 appointment_unavailable` without identifying the reason or exposing internal IDs.

Each candidate interval is delegated to the existing `CheckAppointmentAvailability::execute()` contract. That SPEC-004 authority evaluates Service/category and Professional active state, compatibility, BusinessHours, ProfessionalSchedule, ProfessionalTimeOff, confirmed Appointment overlap and global capacity. SPEC-006 does not copy any of those rules and does not call `CreateAppointment`.

Individual unavailable candidates are omitted. A valid request with no eligible candidates returns HTTP 200 with `slots: []`. No Appointment, AppointmentHistory or Customer query/write is performed by the availability operation.

## Response Contract

Successful responses use the existing `data` envelope:

```json
{
  "data": {
    "date": "2026-01-05",
    "timezone": "UTC",
    "slots": [
      {
        "starts_at": "2026-01-05T09:00:00+00:00",
        "ends_at": "2026-01-05T09:45:00+00:00",
        "local_start": "09:00",
        "local_end": "09:45"
      }
    ]
  }
}
```

The response exposes no Customer, Appointment, History, capacity, schedule, TimeOff, lock, conflict-reason or internal resource data.

## Conflict and Rate-Limit Contract

- Malformed/missing IDs or date format: JSON `422`.
- Past or out-of-horizon date: JSON `422` with safe date validation.
- Unusable resource selection: JSON `409`, code `appointment_unavailable`, generic message.
- Valid active request with no slots: JSON `200`, empty `slots`.
- Availability rate limit: 30 requests/minute/IP via existing Laravel RateLimiter.
- Rate-limit overflow: safe JSON `429` without limiter keys or HTML.
- Unexpected failure: existing safe API JSON handling; no SQL, locks, stack or exception details.

## Performance Evidence

Representative local MySQL test request:

```text
timezone: UTC
Service duration: 45 minutes
BusinessHours: 09:00-12:00
ProfessionalSchedule: 09:00-12:00
candidate starts generated: 96
slots returned: 10
database queries observed: 295
local elapsed test time: 1.36 seconds
```

The 295 queries are bounded repeated calls to existing `CheckAppointmentAvailability`, not lazy-loaded Customer/History/Schedule relations. The one-date/96-candidate cap is retained for V1. Development must revisit this evidence if realistic production volume makes the repeated authority checks excessive; it must not copy SPEC-004 rules or add a speculative index.

Existing indexes remain sufficient for the current query architecture. New index: `NONE`.

## Tests

Added `tests/Feature/Api/PublicBookingAvailabilityTest.php` covering:

- unauthenticated valid availability;
- required query parameters and malformed dates;
- inclusive today-plus-90 boundary and rejection beyond it;
- past date and elapsed time exclusion with frozen server clock;
- 15-minute starts, unique ordering and exact Service duration;
- spring-forward gap omission and fall-back fold omission;
- valid empty availability;
- generic inactive/incompatible resource conflict;
- confirmed Appointment blocking and terminal Appointment nonblocking;
- global capacity blocking despite a free selected Professional;
- current Service duration when deriving candidate ends;
- inactive and incompatible Professional qualification;
- ProfessionalTimeOff overlap exclusion;
- no Customer queries during availability;
- availability 30/min/IP JSON 429.

Focused B tests: 16 tests, 88 assertions, PASS.

## Regression and Quality Evidence

- Full backend: 182 tests, 1076 assertions, PASS.
- SPEC-004 concurrency: 17 tests, 212 assertions, PASS, not skipped.
- Checkpoint-A public read tests: PASS.
- Frontend regression: 13 files, 42 tests, PASS.
- Pint: PASS.
- PHPStan: PASS.
- Composer validate/audit: PASS.
- ESLint, TypeScript, build and npm audit: PASS.
- Public route audit: exactly 4 GET and 0 POST routes.
- `git diff --check`: PASS.

## Schema and Scope Audit

```text
new tables: NONE
new columns: NONE
new indexes: NONE
new constraints: NONE
new migrations: NONE
Composer dependencies: NONE
npm dependencies: NONE
infrastructure: NONE
```

Not implemented: Checkpoint C Customer resolution/create/reuse, booking POST, CreateAppointment public orchestration, Idempotency-Key, anonymous booking transaction, phone HMAC limiter, Checkpoint D `/reservar`/Vue, Checkpoint E/F, payment, notification, self-service, auto-assignment and SPEC-007+.

SPEC-003, SPEC-004 and SPEC-005 documents and closure reports remain unchanged. No direct Appointment, AppointmentHistory or Customer write exists in Checkpoint B.

## Status

```text
SPEC-006: APPROVED FOR DEVELOPMENT / IN PROGRESS
Definition: COMPLETED / APPROVED
Technical Discovery: COMPLETED / APPROVED
Checkpoint A: COMPLETED / APPROVED
Checkpoint B: COMPLETED / READY FOR HUMAN APPROVAL
Checkpoint C: NOT AUTHORIZED
Checkpoint D: NOT AUTHORIZED
Checkpoint E: NOT AUTHORIZED
Checkpoint F: NOT AUTHORIZED
SPEC-007+: NOT AUTHORIZED
```

## Recommended Next Action

Submit Checkpoint B for human review. Do not start Checkpoint C, Customer resolution or booking mutation.
