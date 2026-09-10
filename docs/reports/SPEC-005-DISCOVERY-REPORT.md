# SPEC-005 TECHNICAL DISCOVERY REPORT

## 1. Discovery Status

- SPEC-005: `TECHNICAL DISCOVERY COMPLETED / AWAITING HUMAN APPROVAL`.
- Definition: `APPROVED`.
- Development: `NOT AUTHORIZED`.
- Checkpoint A: `NOT AUTHORIZED`.
- Starting Definition HEAD: `f6af68f`.
- Discovery branch: `docs/spec-005-discovery`.
- Application code changed during Discovery: `NONE`.

## 2. Executive Summary

SPEC-005 is the Admin Agenda consumer identified as roadmap item 05. It should provide an authenticated administrative view and operational workflow over the closed SPEC-003 and SPEC-004 domain contracts.

The recommended V1 consumer is a mobile-first day view with a bounded range/list view. It uses a focused administrative API and the existing Vue/Sanctum foundation. Appointment reads use a focused query layer; all Appointment mutations delegate to SPEC-004 Actions. No new tables, calendar library, state library, API implementation or frontend implementation is authorized by this Discovery.

## 3. Inherited Authority

### SPEC-003

SPEC-005 consumes:

- `BusinessProfile` and authoritative `BusinessProfile.timezone`.
- `BusinessHours`.
- `ServiceCategory` and `Service`.
- `Professional` and `ProfessionalService`.
- `Customer` identity.

SPEC-005 does not own or duplicate these domain models.

### SPEC-004

SPEC-005 consumes:

- `Appointment` and `AppointmentHistory`.
- `ProfessionalSchedule` and `ProfessionalTimeOff` as read context only.
- `CheckAppointmentAvailability`.
- `CreateAppointment`.
- `RescheduleAppointment`.
- `CancelAppointment`.
- `CompleteAppointment`.
- `MarkAppointmentNoShow`.

Availability, capacity, duration, status, DST, locking, concurrency and history remain SPEC-004 authority.

## 4. Architecture

The recommended flow is:

```text
Vue Admin Agenda
  -> existing fetch/Sanctum client
  -> /api/v1/admin/agenda/*
  -> focused Controller/Form Request/Resource boundary
  -> focused read query or Action
  -> SPEC-003 models / SPEC-004 Actions
  -> MySQL
```

The read side may use focused query classes or query services. A generic Repository Pattern, CQRS framework or reporting subsystem is not justified.

The write side must invoke SPEC-004 Actions. Controllers and Vue components must not write `Appointment` or `AppointmentHistory` directly.

## 5. Agenda View Recommendation

### Recommended V1

`Day + bounded list/range`.

- Day view is the primary operational view for salon work.
- A bounded list/range view supports navigation across several days without requiring a dense calendar grid.
- Week navigation may be represented as date navigation/filtering, not as a full grid in V1.
- Month view is deferred; no operational requirement currently justifies its complexity.

This recommendation requires human/product confirmation before Development.

### Mobile strategy

- Use stacked appointment cards or a timeline/list on small screens.
- Use a day navigator and compact filters rather than a dense desktop calendar grid.
- Use a wider list/timeline composition on tablet and desktop.
- Keep touch targets, focus and status semantics accessible.
- Use the existing `AdminLayout` and SPEC-002 primitives.

## 6. Date and Time Contract

`BusinessProfile.timezone` is the inherited and authoritative business timezone. SPEC-005 does not ask which timezone the business uses and does not introduce another timezone authority.

Recommended request contract:

- `from`: required business-local calendar date, inclusive, `YYYY-MM-DD`.
- `to`: required business-local calendar date, exclusive, `YYYY-MM-DD`.
- The backend resolves the date range using `BusinessProfile.timezone`.
- The database query uses UTC `starts_at`/`ends_at` instants.
- The API returns canonical UTC instants plus enough business-local display context for the consumer.
- A bounded maximum range is required; Discovery recommends 31 calendar days for list/range reads, subject to implementation benchmarking.

Appointments remain concrete UTC instants. Recurring BusinessHours and ProfessionalSchedule remain business-local `TIME` rules. No implicit browser timezone conversion is authoritative.

DST errors and ambiguous local input must be rejected or explicitly represented by the inherited SPEC-004 temporal contract. Admin Agenda must present those errors; it must not choose a fold or normalize a gap.

## 7. Read API Proposal

The proposed namespace is `/api/v1/admin/agenda`.

### Appointment collection

```text
GET /api/v1/admin/agenda/appointments
```

Proposed query parameters:

- `from`, required, inclusive business-local date.
- `to`, required, exclusive business-local date.
- `professional_id`, optional.
- `status`, optional approved status value.
- `service_id`, optional.
- `customer_id`, optional exact operational filter if needed.

Customer free-text search is deferred until a concrete need and bounded query contract are approved. Unbounded Customer loading is not allowed.

### Appointment detail

```text
GET /api/v1/admin/agenda/appointments/{appointment}
```

The detail response may include focused AppointmentHistory ordered chronologically. History is read-only; no history mutation endpoint exists.

### Response shape

Responses should preserve the Foundation `data` envelope and existing JSON error behavior. The agenda list should return a bounded collection with deterministic ordering:

```text
starts_at ASC, id ASC
```

Appointments intersecting the requested range are included using:

```text
starts_at < range_end AND ends_at > range_start
```

An appointment crossing a date boundary must not disappear because its start is outside the displayed date.

## 8. Appointment Detail Projection

The minimum operational projection is:

- Appointment internal reference required by the authenticated admin contract.
- `status`.
- `starts_at`, `ends_at` as UTC instants and business-local display values where justified.
- `duration_minutes` historical snapshot.
- Service identity/name/category.
- Professional identity/name.
- Minimal Customer name.
- Customer phone only if daily operation demonstrates a concrete need.
- Focused AppointmentHistory fields.

No price, notes, source, public identifier or copied Service/Professional name snapshot is added to persistence.

## 9. Appointment Creation Decision

Administrative creation is proposed in SPEC-005 V1.

The authenticated admin selects existing Customer, Service, Professional and an interval. The request delegates to `CreateAppointment`. SPEC-005 does not create Customer records, introduce Customer CRUD or bypass compatibility, availability, capacity, duration or transaction validation.

Customer creation is deferred to a future Customer-management decision unless a later human-approved scope adds it. The operational consequence is explicit: V1 creation requires an existing Customer.

## 10. Mutation API Proposal

Explicit intent endpoints are preferred over a generic status mutation:

```text
POST /api/v1/admin/agenda/appointments
POST /api/v1/admin/agenda/appointments/{appointment}/reschedule
POST /api/v1/admin/agenda/appointments/{appointment}/cancel
POST /api/v1/admin/agenda/appointments/{appointment}/complete
POST /api/v1/admin/agenda/appointments/{appointment}/no-show
```

These are Discovery proposals only. Exact routes and request contracts require human approval before Development.

### Create

Transport fields are limited to:

```text
customer_id
service_id
professional_id
starts_at
ends_at
```

The backend resolves transport time semantics and calls `CreateAppointment`.

### Reschedule

Transport fields are limited to:

```text
professional_id
starts_at
ends_at
```

Customer, Service, duration, status and history cannot be changed by this operation.

### Terminal actions

Cancel, Complete and NoShow accept intent only. They do not accept an arbitrary target status. Frontend visibility is UX; the backend Action remains authoritative.

## 11. Error and Conflict Contract

The existing API conventions should be preserved:

- `401`: missing/invalid Sanctum session.
- `403`: future authorization boundary rejects the authenticated admin.
- `404`: Appointment or referenced resource not found.
- `422`: transport validation failure.
- `409`: valid request rejected by domain state/availability/concurrency conflict, including an interval becoming unavailable or a terminal transition losing a race.
- `500`: unexpected failure without SQL, stack trace or lock internals.

Conflict responses should identify the operation and safe user-facing reason, then allow the UI to refresh authoritative detail/list data. No raw SQLSTATE is exposed.

## 12. Authorization and Session

V1 uses the existing `auth:sanctum` same-origin administrative session. No new RBAC tables, roles, Professional login or Customer login are proposed.

A focused Policy/Gate may be introduced during Development only if multiple administrator behavior or an actual authorization distinction requires it. Until then, authenticated internal admin access is the boundary.

Mutations use the existing CSRF cookie/XSRF header behavior in the fetch wrapper. Browser token storage is not introduced.

## 13. Schedule, TimeOff and BusinessHours Context

SPEC-005 may consume read-only schedule/TimeOff/BusinessHours context to explain an unavailable interval or orient an agenda view.

It does not create, edit, delete or approve ProfessionalSchedule, ProfessionalTimeOff or BusinessHours. Administrative management of those records is deferred unless a later approved scope assigns it explicitly.

No availability preview endpoint is required for V1. The UI may submit an operation and receive authoritative SPEC-004 validation/conflict results. A preview endpoint remains optional future work and cannot reserve capacity.

## 14. Read Query and Performance Design

The list query should be a focused query class/service using:

- UTC range intersection.
- Optional Professional, status and Service filters.
- Deterministic `starts_at ASC, id ASC` order.
- Eager-loaded Customer, Service/category and Professional.
- History loaded only for detail, not every agenda card.

The existing SPEC-004 indexes are sufficient as the starting point: Professional/status/time and status/time indexes support the primary filters. Any additional index must be justified with actual EXPLAIN evidence during Development. No new table or read model is required by Discovery.

## 15. Frontend Architecture

- Add a future `/admin/agenda` route within the existing AdminLayout.
- Prefer a route-addressable appointment detail view or drawer with a stable deep-link strategy; exact choice remains UX approval.
- Reuse the existing `http` fetch wrapper and `useAuth` session.
- Use focused agenda API modules and composables; do not scatter raw fetch calls through pages.
- Do not add Pinia/Vuex by default; composable/local state is sufficient for the first consumer.
- Reuse `UiButton`, `UiInput`, `UiFormField`, `UiContainer` and `UiCard` where appropriate.
- Do not create a parallel admin shell or design system.

Required UI states are loading, empty, filtered, request failure, unauthorized, domain conflict, mutation pending, mutation success and stale-data refresh.

## 16. Accessibility and Presentation

- Semantic landmarks, headings, controls and status regions.
- Keyboard navigation and visible focus.
- Accessible dialogs/drawers if selected.
- Status must not rely on color alone.
- Touch targets and readable text at mobile zoom.
- Reduced-motion behavior inherited from SPEC-002.
- Spanish presentation labels for the four canonical statuses; persistence/API values remain canonical English enum strings.
- Day/time display should use a consistent 12-hour or 24-hour project convention, to be confirmed in UX review, while persistence remains UTC.

## 17. Calendar Dependency Decision

Recommendation: `DEFER LIBRARY / START WITH FOCUSED NATIVE VUE LIST-DAY PRESENTATION`.

Rationale:

- V1 recommendation is day plus bounded list/range, not a full month/week grid.
- Native Vue composition avoids bundle, licensing and maintenance cost.
- Existing UI primitives and Tailwind foundation are sufficient for the first focused surface.
- A calendar library can be evaluated later if a human-approved week/month grid requirement emerges.

No dependency was installed or selected during Discovery.

## 18. Testing Strategy

### Backend/API

- Authenticated/unauthenticated agenda reads.
- Authorization boundary if a Policy/Gate is approved.
- Date/range inclusion and UTC/business-local conversion.
- Professional/status/Service filters and deterministic ordering.
- Cross-boundary Appointment inclusion.
- Minimal detail/history projection.
- Create, Reschedule, Cancel, Complete and NoShow delegation.
- Validation, `404`, `409`, terminal-state and stale-data conflicts.
- No direct Appointment/History writes from controllers.

### Frontend

- `/admin/agenda` route protection.
- Loading, empty, error and conflict states.
- Filters/date navigation.
- Detail/history presentation.
- Mutation pending/success/error behavior.
- Mobile/responsive semantics and accessibility-related markup.

### Regression boundary

SPEC-005 tests should verify delegation and consumer contracts. They must not duplicate SPEC-004 low-level MySQL concurrency tests; the existing 17-test concurrency suite remains the domain authority.

## 19. Checkpoint Proposal

The following is a proposal for later Development approval:

### Checkpoint A - Admin read API and authorization

- Scope: bounded agenda query, detail/history projection, auth boundary, filters and UTC/date semantics.
- Evidence: Feature/API tests, query tests, error contract, no code outside approved boundary.
- Exclusions: Vue, mutations, Customer CRUD, Schedule/TimeOff CRUD.

### Checkpoint B - Admin read experience

- Scope: `/admin/agenda`, day/list views, filters, detail, loading/empty/error states and mobile behavior.
- Evidence: Vue tests, route tests, accessibility and responsive review.
- Exclusions: new domain logic, calendar dependency by default.

### Checkpoint C - Appointment creation and reschedule consumer

- Scope: existing Customer selection, CreateAppointment and RescheduleAppointment intent flows.
- Evidence: API/delegation tests, conflict handling, mutation UX and regression coverage.
- Exclusions: Customer CRUD, arbitrary Service/Customer reassignment, direct writes.

### Checkpoint D - Terminal actions and final hardening

- Scope: Cancel, Complete, NoShow, history refresh, stale-data conflicts, security/privacy and performance review.
- Evidence: complete consumer tests, route audit, query plans and CI.
- Exclusions: notifications, payments, public booking and SPEC-006.

### Final audit checkpoint

- Scope: final tests, documentation, security, accessibility, scope and CI audit.
- Status: not authorized.

## 20. Definition AC Mapping

| Definition AC | Discovery interpretation | Planned evidence | Planned checkpoint |
| --- | --- | --- | --- |
| AC-01 | Canonical Admin Agenda roadmap scope | SPEC and roadmap | A-D |
| AC-02 | Separate Core/Engine/consumer ownership | API/query/action design | A |
| AC-03 | No implementation in Definition/Discovery | Git diff audit | Discovery |
| AC-04 | All appointment mutations delegate to SPEC-004 | delegation tests | C-D |
| AC-05 | No duplicated domain algorithm | architecture/code review | A-D |
| AC-06 | Sanctum administrative boundary | auth tests | A |
| AC-07 | Read/detail/mutation workflow definition | UX/API tests | A-D |
| AC-08 | Customer data minimization | Resource/privacy review | A |
| AC-09 | Deferred workflow boundaries | scope audit | Discovery |
| AC-10 | Security/API/data/concurrency boundaries | audit matrix | A-D |
| AC-11 | Separate open-question categories | this report | Discovery |
| AC-12 | HOW topics identified | Discovery requirements | Discovery |
| AC-13 | No invented business data | scope/security audit | all |
| AC-14 | Observable eventual implementation tests | checkpoint test plans | A-D |
| AC-15 | Await human approval; no Development | Git/status audit | Discovery |

## 21. ADR Assessment

No new ADR is required by this Discovery. Existing ADR-003 fully covers temporal/concurrency authority. A future calendar dependency or materially new read/API architecture may require an ADR only if implementation evidence demonstrates a durable cross-cutting decision.

## 22. Open Decisions Requiring Human/Product Review

### Business

- Confirm day + list/range as the V1 agenda view rather than a full week/month grid.
- Confirm which approved lifecycle Actions are exposed in V1.
- Confirm whether Customer phone is operationally necessary in detail.
- Confirm retention/anonymization policy remains deferred.

### Architecture

- Approve the proposed `/api/v1/admin/agenda/*` boundary and exact endpoint contracts.
- Approve the 31-calendar-day range guard or select a benchmark-based limit.
- Confirm whether authenticated-only access is sufficient for V1.

### UX

- Confirm mobile day/list interaction and detail drawer/page choice.
- Confirm 12-hour versus 24-hour display convention.
- Confirm conflict/terminal-action confirmation language and behavior.

### Security

- Confirm minimum Customer projection.
- Confirm any focused Policy/Gate requirement for future administrators.
- Confirm whether additional audit accountability beyond AppointmentHistory is needed.

## 23. Explicit Deferred Scope

No Customer CRUD, Schedule CRUD, TimeOff CRUD, BusinessHours CRUD, public booking, notifications, payments, slots, automatic assignment, calendar package, state library, migrations, routes, controllers, Resources, Vue components or production code is authorized by this Discovery.

## 24. Final Discovery State

```text
SPEC-005: TECHNICAL DISCOVERY COMPLETED / AWAITING HUMAN APPROVAL
Definition: APPROVED
Technical Discovery: COMPLETED
Development: NOT AUTHORIZED
Checkpoint A: NOT AUTHORIZED
SPEC-004: CLOSED
SPEC-006+: NOT STARTED
```
