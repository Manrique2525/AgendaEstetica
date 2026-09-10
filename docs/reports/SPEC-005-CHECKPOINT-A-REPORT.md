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

- Checkpoint A API tests: 14 tests, 42 assertions, PASS.
- Foundation/C.1 API regressions: 9 tests, 49 assertions, PASS.
- Full backend: 127 tests, 589 assertions, PASS.
- Existing SPEC-004 concurrency suite: 17 tests, 212 assertions, PASS, not skipped.
- Pint: PASS.
- PHPStan: PASS.
- Composer validate/audit: PASS.
- Frontend: 10 files, 24 tests, PASS.
- ESLint, TypeScript, build and npm audit: PASS.
- Route audit: only the five read routes were added; no mutation routes.
- `git diff --check`: PASS.

## EXPLAIN Evidence

Representative MySQL 8.4 plans against the testing schema:

| Query | Type | Chosen key | Key length | Rows | Extra |
| --- | --- | --- | ---: | ---: | --- |
| Unfiltered UTC range overlap | `index` | `appointments_status_time_index` | 140 | 10 | `Using where; Using index; Using filesort` |
| Professional + range overlap | `ref` | `appointments_professional_status_time_index` | 8 | 1 | `Using where; Using index; Using filesort` |
| Status + range overlap | `range` | `appointments_status_time_index` | 135 | 1 | `Using where; Using index; Using filesort` |
| Service + range overlap | `ref` | `appointments_service_id_foreign` | 8 | 1 | `Using where; Using filesort` |

The unfiltered query has `appointments_status_time_index` in `possible_keys`, but because its leading `status` column is unconstrained, MySQL uses an index scan rather than a selective range access. This is accepted for the single-business V1 and hard maximum of 31 calendar days; no speculative migration/index was added. Query plans must be rechecked during a future volume/performance review.

## DST and Ordering Evidence

- Spring-forward `America/New_York`, `2026-03-08` to `2026-03-09`: independently resolved UTC range is 23 hours; endpoint includes a boundary-crossing Appointment and excludes the next-day Appointment.
- Fall-back `America/New_York`, `2026-11-01` to `2026-11-02`: independently resolved UTC range is 25 hours; endpoint includes/excludes the corresponding boundary cases.
- Same-start ordering test: two Appointments are returned by `starts_at ASC`, then ascending `id`.

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
