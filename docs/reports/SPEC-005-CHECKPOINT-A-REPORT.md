# SPEC-005 CHECKPOINT A REPORT

## Scope

Checkpoint A implements only the authenticated Admin Agenda read foundation and lookup endpoints. It does not implement Vue agenda pages, appointment mutations, Customer CRUD, Schedule CRUD, TimeOff CRUD, BusinessHours CRUD, slot generation or calendar dependencies.

## Routes

All routes use the existing `auth:sanctum` administrative session:

```text
GET /api/v1/admin/agenda/appointments
GET /api/v1/admin/agenda/appointments/{appointment}
GET /api/v1/admin/agenda/customers
GET /api/v1/admin/agenda/services
GET /api/v1/admin/agenda/professionals
```

No POST/PATCH/PUT/DELETE Admin Agenda mutation route exists. A POST to the appointment collection is rejected with `405`.

## Read Architecture

- Controllers coordinate only request/query/resource flow.
- `ListAdminAgendaAppointments` is the focused read Action.
- `AdminAgendaRange` resolves business-local dates using authoritative `BusinessProfile.timezone` and converts boundaries to UTC.
- Appointment queries use half-open overlap semantics: `starts_at < range_end` and `ends_at > range_start`.
- Ordering is deterministic: `starts_at ASC`, then `id ASC`.
- No pagination is used because V1 enforces a maximum 31-calendar-day range.
- Appointment list cards eager-load only Customer, Service/category and Professional.
- History loads only on Appointment detail.

## Range and Filters

Required query parameters:

```text
from=YYYY-MM-DD
to=YYYY-MM-DD
```

`from` is inclusive and `to` is exclusive in the business timezone. Invalid dates, inverted/equal ranges and ranges longer than 31 calendar days return JSON `422` validation errors.

Optional filters are `professional_id`, canonical `status`, `service_id` and `customer_id`. Free-text agenda filtering is not included.

## Projections

Collection projection includes only Appointment id/status/times/duration and Customer name, Service name and Professional name. Customer phone, normalized phone and history are excluded from cards.

Detail adds Customer phone, minimal Service category and ordered read-only AppointmentHistory. Concrete timestamps are serialized as UTC ISO-8601 values.

Lookup endpoints are bounded and minimal:

- Customer: `q`, server-side name/phone search, maximum 20 results, `id/name/phone` only.
- Service: active Service in active category, minimal service/pricing/category data.
- Professional: active Professionals, optionally filtered by Service compatibility, `id/name` only.

No lookup creates or mutates domain data.

## Security and Authority

- Existing Sanctum authentication is reused.
- No new roles, permissions, guards, RBAC tables or token storage were added.
- No direct Appointment or AppointmentHistory writes exist.
- SPEC-004 Actions remain the only future mutation authority.
- No SQL, stack traces, normalized phone, internal lock details or unrelated model fields are exposed.

## Tests

Added `tests/Feature/Api/AdminAgendaTest.php` covering:

- unauthenticated access to all read endpoints;
- bounded range validation;
- overlap inclusion and filters;
- business-local timezone to UTC range conversion;
- minimal collection projection;
- detail phone/history projection;
- bounded Customer lookup;
- active/compatible Service and Professional lookups;
- absence of mutation routes.

## Quality Evidence

- Checkpoint A API/foundation tests: 20 tests, 79 assertions, PASS.
- Full backend: 124 tests, 577 assertions, PASS.
- Existing SPEC-004 concurrency suite: 17 tests, 212 assertions, PASS, not skipped.
- Pint: PASS.
- PHPStan: PASS.
- Composer validate/audit: PASS.
- Frontend: 10 files, 24 tests, PASS.
- ESLint, TypeScript, build and npm audit: PASS.
- Route audit: only the five read routes were added; no mutation routes.
- `git diff --check`: PASS.

## Scope Audit

Not implemented: Checkpoint B, Vue Admin Agenda, Create/Reschedule/Cancel/Complete/NoShow endpoints, Customer CRUD, Schedule CRUD, TimeOff CRUD, BusinessHours CRUD, availability preview, slots, calendar dependency, notifications, payments, Public Booking and SPEC-006+.

Application implementation changes are limited to the approved Checkpoint A read API and its tests. No migrations or dependencies were added.

## Status

- SPEC-005: `APPROVED FOR DEVELOPMENT / IN PROGRESS`.
- Definition: `APPROVED`.
- Technical Discovery: `COMPLETED / APPROVED`.
- Checkpoint A: `COMPLETED`.
- Checkpoint B: `NOT AUTHORIZED`.
- Development beyond A: `NOT AUTHORIZED`.
- SPEC-004: `CLOSED`.
- SPEC-006+: `NOT STARTED`.

## Recommended Next Action

Submit Checkpoint A for human review. Do not start Checkpoint B.
