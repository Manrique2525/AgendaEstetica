# SPEC-006 CHECKPOINT A REPORT

## Scope

Checkpoint A implements only the public read foundation: booking context, active Service catalog, active Service-compatible Professional projection, safe public Resources, public route separation and read rate limits. It does not implement availability/slots, booking mutation, Customer resolution, Idempotency-Key, public Vue, migrations or dependencies.

## Routes

Exactly three public GET routes were added:

```text
GET /api/v1/public/booking/context
GET /api/v1/public/booking/services
GET /api/v1/public/booking/professionals?service_id={id}
```

The routes are outside the Admin Agenda `auth:sanctum` group. They use the public `/api/v1/public/booking/*` boundary and remain same-origin. No public POST, availability, Customer, Appointment, history, cancellation, reschedule, payment or notification route was added.

## Context

`GET /api/v1/public/booking/context` returns exactly:

```json
{
  "data": {
    "timezone": "<BusinessProfile.timezone>"
  }
}
```

The value comes from the singleton `BusinessProfile` using the established `singleton_key` query. No fallback, hard-coded timezone, capacity, phone, internal ID or other BusinessProfile field is exposed.

## Service Catalog

The Services endpoint returns only active Services belonging to an active ServiceCategory. Its focused projection is:

```text
id
name

Ordering is deterministic by `service_category_id`, `name`, then `id`. The catalog is bounded by the active single-business catalog and does not add generic pagination.

Pricing uses the authoritative `ServicePricingType` and amount:

- `fixed`: exact configured amount and `$X.XX` presentation.
- `starting_from`: configured amount and `Desde $X.XX` presentation.
- `variable`: `price=null` and `Precio variable`; no invented amount, zero or minimum.

No payment, deposit, checkout, quote or discount field is exposed.

## Professionals

`service_id` is required and validated as a positive integer. The endpoint returns only active Professionals linked to the selected active Service under an active category. Unknown or inactive Services safely return an empty collection; malformed `service_id` returns JSON `422`.

The focused projection is exactly `id` and `name`, ordered by `name`, then `id`. Schedules, TimeOff, user relationships, timestamps and administrative metadata are not loaded or exposed.

## Authentication, Rate Limits and Errors

- Public routes do not use `auth:sanctum`; public access is unauthenticated.
- Admin routes remain in their existing authenticated group.
- Existing same-origin/session/CORS configuration was not changed.
- No CSRF or session architecture was added in A because A has GET routes only.
- Catalog/context reads use existing Laravel rate-limiter infrastructure at 120 requests/minute/IP.
- Professional reads use existing Laravel rate-limiter infrastructure at 60 requests/minute/IP.
- Rate-limit overflow returns safe JSON `429` without internal keys or HTML.
- Unknown `/api/v1/*` routes remain JSON `404`.

## Query and Security Audit

- Services eager-load only the minimal category projection and query active/category state.
- Professionals use the existing `professional_service` relationship and active/category filters.
- Customer data: none.
- Appointment data: none.
- AppointmentHistory: none.
- ProfessionalSchedule: none.
- ProfessionalTimeOff: none.
- Capacity internals: none.
- User/admin metadata: none.
- No raw SQL, dynamic sort columns or arbitrary filters were introduced.
- Existing indexes are used; no speculative index was added.

## Tests

Added `tests/Feature/Api/PublicBookingTest.php` covering:

- unauthenticated context access and exact timezone-only projection;
- active Service/category filtering;
- fixed, starting-from and variable pricing semantics;
- minimal Service/category projection and deterministic ordering;
- active compatible Professional filtering;
- inactive/unknown Service behavior;
- required positive `service_id` validation;
- unknown public route JSON `404`;
- catalog `120/min/IP` JSON `429`;
- Professional `60/min/IP` JSON `429`.

## Quality Evidence

- Focused Checkpoint-A tests: 12 tests, 244 assertions, PASS.
- Full backend: 166 tests, 988 assertions, PASS.
- SPEC-004 concurrency: 17 tests, 212 assertions, PASS, not skipped.
- Frontend regression: 13 files, 42 tests, PASS.
- Pint: PASS.
- PHPStan: PASS.
- Composer validate/audit: PASS.
- ESLint, TypeScript, build and npm audit: PASS.
- Route audit: exactly 3 public GET routes and 0 public POST routes.
- `git diff --check`: PASS.

## Schema and Dependency Audit

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

## Scope Audit

Not implemented: Checkpoint B availability/slots, 15-minute generation, 90-day horizon behavior, past-time filtering, Checkpoint C Customer resolution/booking mutation/Idempotency-Key/CreateAppointment public orchestration, Checkpoint D `/reservar` Vue, Checkpoint E abuse hardening, Checkpoint F final audit, Customer CRUD, schedule administration, payments, deposits, checkout, notifications, auto-assignment and SPEC-007+.

SPEC-003, SPEC-004 and SPEC-005 files and closure reports remain unchanged. No application behavior outside the approved public read boundary was added.

## Status

```text
SPEC-006: APPROVED FOR DEVELOPMENT / IN PROGRESS
Definition: COMPLETED / APPROVED
Technical Discovery: COMPLETED / APPROVED
Checkpoint A: COMPLETED / READY FOR HUMAN APPROVAL
Checkpoint B: NOT AUTHORIZED
Checkpoint C: NOT AUTHORIZED
Checkpoint D: NOT AUTHORIZED
Checkpoint E: NOT AUTHORIZED
Checkpoint F: NOT AUTHORIZED
SPEC-007+: NOT AUTHORIZED
```

## Recommended Next Action

Submit Checkpoint A for human review. Do not start Checkpoint B, availability, slots or any later checkpoint.
