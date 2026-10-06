# SPEC-005 CHECKPOINT B REPORT

## Scope

Checkpoint B implements the read-only Native Vue Admin Agenda experience and the minimal authenticated `BusinessProfile.timezone` context correction required by the approved temporal contract. It does not implement Appointment mutations, Customer CRUD, Schedule/TimeOff CRUD, calendar dependencies or Checkpoint C.

## Timezone Integration Correction

Checkpoint A exposed UTC Appointment instants and business-local range handling in backend, but the frontend had no approved source for the business timezone. B adds:

```text
GET /api/v1/admin/agenda/context
```

The endpoint requires `auth:sanctum` and returns only:

```json
{
  "data": {
    "timezone": "<BusinessProfile.timezone>"
  }
}
```

The value is read from the singleton `BusinessProfile` and is not hard-coded. Frontend rendering never falls back to browser/device timezone.

## Frontend Read Experience

- `/admin/agenda` uses the existing `AdminLayout`.
- `/admin/agenda/:id` provides route-addressable detail.
- Day view is the default.
- List/range view uses the bounded backend `from`/`to` contract.
- Previous, Today and Next use calendar-date arithmetic rather than elapsed milliseconds.
- Filters use existing lookup endpoints for Professional, Service and Customer.
- Status labels are presented in Spanish while canonical API values remain unchanged.
- Appointment cards omit Customer phone and history.
- Detail shows operational Customer phone, Service/Professional context and focused History.
- No mutation controls or mutation API methods were added.

## Temporal Behavior

- Business today uses `BusinessProfile.timezone`.
- UTC Appointment instants are formatted with explicit `Intl.DateTimeFormat` timezone.
- Date grouping uses business-local dates, not UTC dates.
- Spring-forward and fall-back utility coverage is present.
- No calendar dependency, date library, Pinia or Vuex was added.

## UX and Accessibility

- Loading, empty, error, unauthorized and detail-not-found states are represented.
- Request-version protection prevents stale agenda responses from replacing newer results.
- Customer lookup is bounded and server-side.
- Existing `AdminLayout`, `UiButton`, `UiInput`, `UiFormField` and `UiCard` are reused.
- Semantic headings, links, labels, status text, focus styles and responsive stacked cards are used.
- No business data or fake appointments were added.

## Route Surface

Checkpoint B route surface is now exactly six read-only GET endpoints:

```text
GET /api/v1/admin/agenda/context
GET /api/v1/admin/agenda/appointments
GET /api/v1/admin/agenda/appointments/{appointment}
GET /api/v1/admin/agenda/customers
GET /api/v1/admin/agenda/services
GET /api/v1/admin/agenda/professionals
```

No POST/PATCH/PUT/DELETE Appointment route exists. Checkpoint C mutation work remains unauthorized.

## Tests and Quality

- Admin Agenda API tests: PASS.
- Frontend tests: 12 files, 29 tests, PASS.
- Full backend: 129 tests, 592 assertions, PASS.
- Dedicated SPEC-004 concurrency suite: 17 tests, 212 assertions, PASS, not skipped.
- Pint: PASS.
- PHPStan: PASS.
- Composer validate/audit: PASS.
- ESLint, TypeScript, build and npm audit: PASS.
- Route audit: PASS.
- `git diff --check`: PASS.

## Scope Audit

Not implemented: Checkpoint C, Create/Reschedule/Cancel/Complete/NoShow endpoints, Customer CRUD, Service CRUD, Professional CRUD, Schedule CRUD, TimeOff CRUD, BusinessHours CRUD, Public Booking, slots, auto-assignment, notifications, payments, calendar dependency and SPEC-006+.

## Status

- SPEC-005: `APPROVED FOR DEVELOPMENT / IN PROGRESS`.
- Definition: `APPROVED`.
- Technical Discovery: `COMPLETED / APPROVED`.
- Checkpoint A: `COMPLETED / APPROVED`.
- Checkpoint B: `COMPLETED`.
- Checkpoint C: `NOT AUTHORIZED`.
- SPEC-004: `CLOSED`.
- SPEC-006+: `NOT STARTED`.

## Recommended Next Action

Submit Checkpoint B for human review. Do not start Checkpoint C.
