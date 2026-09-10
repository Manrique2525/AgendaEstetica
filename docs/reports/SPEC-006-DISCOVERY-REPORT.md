# SPEC-006 TECHNICAL DISCOVERY REPORT

## Discovery Status

- SPEC-006: `DEFINITION APPROVED / TECHNICAL DISCOVERY COMPLETED / AWAITING HUMAN APPROVAL`.
- Canonical name: `Public Booking`.
- Roadmap item: `06. Public booking`, after Admin Agenda and before Notification Engine.
- Discovery branch: `docs/spec-006-discovery`.
- Starting Definition HEAD: `50c5fc0de752e663eed20ee765fcb7fa1ee31d78`.
- No application code, routes, schema, dependencies or implementation tests were changed.

## Executive Summary

Public Booking V1 is a same-origin, guest booking consumer. A visitor selects an active Service, a specific compatible Professional and a valid bookable time, supplies required name and phone, reviews the booking and submits it. The public flow resolves or creates a minimal Customer and immediately delegates appointment creation to `CreateAppointment`, producing a confirmed Appointment visible to Admin Agenda.

The recommended design uses a small `/api/v1/public/booking/*` boundary, existing Laravel database cache/session/rate-limiter facilities, server-side Customer resolution and bounded one-business-day bookable-time queries. No new domain status, appointment source, public lookup token, booking hold, migration, dependency or SPEC-003/004/005 change is required.

## 1. Architecture and Ownership

1. Proposed architecture: `Public Vue SPA -> public API -> public orchestration/query layer -> SPEC-003 entities + SPEC-004 authority -> MySQL`.
2. Modular monolith is preserved; no microservice, Kafka, RabbitMQ, Redis requirement, Kubernetes or second backend is introduced.
3. SPEC-003 remains authoritative for BusinessProfile, BusinessHours, ServiceCategory, Service, Professional, ProfessionalService and Customer.
4. SPEC-004 remains authoritative for Appointment, AppointmentHistory, ProfessionalSchedule, ProfessionalTimeOff, availability, capacity, duration, timezone/DST domain correctness, lifecycle, locking, concurrency and `CreateAppointment`.
5. SPEC-005 remains the downstream Admin Agenda consumer; public-created Appointments appear through its existing reads with no source-specific branch.
6. SPEC-006 owns only public orchestration, public-safe projections, bounded bookable-time presentation, minimum Customer resolution, public abuse/duplicate protections and public UX.
7. The Discovery explicitly rejects duplicating BusinessHours, schedule coverage, TimeOff overlap, Appointment overlap, capacity, duration, DST validation, lock order or lifecycle rules.

## 2. Proposed Public API

The smallest recommended V1 surface is:

```text
GET  /api/v1/public/booking/context
GET  /api/v1/public/booking/services
GET  /api/v1/public/booking/professionals?service_id={id}
GET  /api/v1/public/booking/availability?service_id={id}&professional_id={id}&date=YYYY-MM-DD
POST /api/v1/public/booking/appointments
```

8. Context is optional as a separate implementation endpoint but recommended for the authoritative timezone and future public display context.
9. No V1 endpoint is proposed for Customer search/detail, Appointment detail/lookup/history, cancellation, reschedule, payments, notifications, raw schedules, raw TimeOff or capacity internals.
10. All success responses use the existing `data` envelope; exact controller/request/resource names remain Development decisions.
11. The public group must not use `auth:sanctum`; it is anonymous public access with the separate CSRF/session boundary described below.

### Public context projection

Recommended safe response:

```json
{
  "data": {
    "timezone": "<BusinessProfile.timezone>"
  }
}
```

12. No complete BusinessProfile, internal capacity, Customer or schedule data is returned.

### Service projection

Recommended public-safe fields:

```text
id
name

13. Only active Services under an active ServiceCategory are projected.
14. Pricing preserves authoritative values: `fixed` exact configured amount, `starting_from` configured amount rendered as `Desde $X`, and `variable` rendered as `Precio variable` with no amount.
15. A stable presentation value may be produced by the public serializer from the raw enum/amount, but no payment, quote, discount or checkout field is added.

### Professional projection

Recommended fields are `id` and display `name`, limited to active Professionals compatible with the selected active Service. No user relationship, schedule, TimeOff or administrative metadata is exposed.

## 3. Bookable-Time Design

16. Availability request parameters are `service_id`, `professional_id` and one business-local `date=YYYY-MM-DD`.
17. One local business day per request is the recommended bound; no arbitrary month/year availability query is allowed.
18. Recommended booking horizon is from the current business-local date through 90 calendar days ahead. This is a technical abuse/performance bound, not a new Appointment domain rule; it requires confirmation during Development planning.
19. Dates before the current business-local date are not bookable. For today, candidate starts whose UTC instant is not in the future at evaluation time are excluded.
20. No minimum lead time is invented because no current domain/product rule defines one.
21. No slot granularity exists in the current Appointment Engine. A 15-minute candidate increment is recommended as a public presentation default, not as a new domain invariant; any different product requirement must be approved before Development.
22. Candidate generation is distinct from authority: the public consumer generates bounded local candidates, resolves each candidate through the BusinessProfile timezone and fresh Service duration, and relies on SPEC-004 availability authority for final eligibility.
23. Existing `CheckAppointmentAvailability` validates an exact interval and already evaluates Service/Category active state, Professional active state and compatibility, BusinessHours, ProfessionalSchedule, TimeOff, Professional overlap and global capacity.
24. The first implementation should reuse that authority rather than copy its private rules. A maximum 15-minute grid across one day bounds candidates to at most 96 before business/schedule filtering.
25. Development must benchmark query count and latency. If candidate-by-candidate validation is excessive, stop and request an approved SPEC-004 read/bulk capability; do not duplicate domain algorithms in SPEC-006.

### Slot response

Recommended safe slot projection:

```text
starts_at: UTC ISO-8601 instant
ends_at: UTC ISO-8601 instant derived from fresh Service duration
local_date: business-local YYYY-MM-DD
local_time: business-local HH:mm
```

26. The server generates both instants and local display values, so the browser does not reconstruct DST gaps or folds.
27. A slot is availability information, not a reservation or hold.
28. No booking hold, temporary reservation, expiration job, Redis lock or slot persistence is required by this Discovery.

## 4. Timezone and Race Contract

29. `BusinessProfile.timezone` is the sole timezone authority.
30. Public date inputs use business-local `YYYY-MM-DD`; final mutation timestamps use explicit-offset ISO-8601 or UTC `Z`.
31. Slot instants are serialized by the server as UTC plus business-local display context.
32. DST gaps and ambiguous folds must never be offered as slots; inherited SPEC-004 temporal validation remains authoritative.
33. A displayed slot does not guarantee reservation. At final submit, `CreateAppointment` must re-read fresh Service and current domain state.
34. If a slot becomes unavailable, the public API returns HTTP `409` with `appointment_unavailable`; the frontend refreshes availability and asks the visitor to choose again.
35. Service deactivation, Professional deactivation, schedule changes, TimeOff changes and capacity changes between display and submit must fail safely through fresh authority, never produce stale success.
36. Existing ADR-003 lock order and transaction behavior are reused; SPEC-006 adds no lock or retry architecture.

## 5. Customer Resolution

37. Normalization authority is the existing `CustomerPhoneNormalizer`; no second phone algorithm is allowed.
38. `customers.phone_normalized` is indexed but not unique. Discovery makes no one-phone-one-Customer assumption and proposes no uniqueness constraint.
39. Recommended server-side strategy: normalize submitted phone, lock/read matching normalized rows within the booking transaction, reuse exactly one match, create a minimal Customer when there are zero matches, and create a new minimal Customer when multiple matches exist rather than guessing an identity.
40. Reuse never silently changes an existing Customer name when submitted name differs; the submitted name is retained only for a newly created Customer. This avoids overwriting shared-family phones, typos or changed names.
41. The API never reveals whether a Customer exists, how many matches were found, which Customer was selected or any Customer ID.
42. Customer create/reuse is allowed only as part of the booking operation; there is no public Customer search, list, detail or management surface.
43. Recommended transaction boundary: public booking orchestration owns one same-connection database transaction around Customer resolution and the nested `CreateAppointment` call, relying on Laravel savepoint semantics. Development must verify nested rollback behavior against the target MySQL setup.
44. If a new Customer is created and `CreateAppointment` fails, the outer transaction rolls back both records. A reused Customer is not modified.
45. The orchestration must not change SPEC-004 canonical lock ordering; Customer resolution occurs before the Action's existing BusinessProfile/Professional/Service locking path and adds no domain lock protocol.

## 6. Public Booking Mutation

Recommended logical request fields:

```text
service_id
professional_id
starts_at
name
phone
```

46. `ends_at` is not client-controlled. The public orchestration loads fresh Service, derives the end from authoritative duration and delegates to `CreateAppointment`.
47. The client cannot submit `duration_minutes`, status, Customer ID, normalized phone, price or an alternate end time as authority.
48. The final submission revalidates Service active state, active category, Professional active state, Service/Professional compatibility, exact interval availability, capacity and current concurrency through existing authority.
49. Successful submission creates a confirmed Appointment immediately; no approval queue or new status is introduced.
50. AppointmentHistory is written by SPEC-004 and is not separately created or exposed by SPEC-006.

### Success projection

Recommended success data contains only a safe confirmation message, Service name, Professional name, business-local date/time, canonical start/end instants if needed for display, and `confirmed` status. It omits Customer ID/phone, AppointmentHistory, capacity, lock data and internal Appointment ID. No public lookup identifier is required by V1.

## 7. Duplicate Submission and Idempotency

51. Frontend must disable submit while pending, but this is not sufficient on its own.
52. Recommended server strategy: require an opaque `Idempotency-Key` per booking intent and bind it to a normalized request fingerprint excluding raw phone from logs/keys.
53. Use the existing database cache store and cache table for a short 15-minute idempotency record plus a database-backed cache lock; add no package, table or column.
54. A repeated key with the same fingerprint replays the safe stored response. A repeated key with a different fingerprint returns safe HTTP `422`.
55. If the first request committed but its response was lost before cache storage, the authoritative Appointment conflict remains the safety fallback; the API must not create a second booking.
56. Idempotency persistence required: no new schema; it uses existing Laravel database cache infrastructure. Development must verify cache atomicity and failure handling.
57. Two legitimate requests with the same Customer/Service/Professional/time are still governed by SPEC-004 overlap/capacity authority and are not silently merged.

## 8. Anonymous Session, CSRF and Auth Separation

58. Recommended model is same-origin anonymous public API with the existing Laravel stateful API/session foundation, not bearer tokens.
59. Public POST should use the existing XSRF cookie/header behavior where the request is stateful; it must not require `auth:sanctum`.
60. Existing `statefulApi()` and `SESSION_DRIVER=database` are available; public and admin route groups must remain distinct.
61. No cross-origin wildcard CORS is recommended. If a future deployment separates origins, stop for a security/architecture decision before implementation.
62. No Customer or Professional authentication is introduced.

## 9. Rate Limits and Abuse

Use existing Laravel `RateLimiter` and database cache facilities; values below are recommended Development defaults to validate, not new business rules:

63. Catalog/context reads: 120 requests per minute per IP.
64. Professional lookup: 60 requests per minute per IP.
65. Availability: 30 requests per minute per IP, additionally bounded to one date and one Service/Professional pair per request.
66. Booking submission: 5 requests per minute per IP and 3 per hour by a non-reversible hash of normalized phone; never use raw phone as a key or log field.
67. Rate-limit key policy combines IP and, where available, an anonymous session; phone-derived keys are hashed and access-controlled.
68. Controls address availability scraping, booking spam, high-frequency requests, identity probing, many phones from one IP and the same phone across many IPs without revealing identity matches.
69. CAPTCHA is not required for V1. Start with rate limiting, server validation and operational monitoring; add a provider only after abuse evidence and separate approval.
70. Monitor conceptually 429 volume, booking conflicts, validation failures and unexpected 5xx responses using existing Laravel logging/CI facilities.

## 10. Error Contract

71. `422`: malformed request, missing name/phone, invalid phone format, invalid timestamp or idempotency-key fingerprint mismatch.
72. `409 appointment_unavailable`: stale/unavailable slot, inactive or incompatible booking resources, schedule/TimeOff/capacity conflict, or an unknown resource handled generically to avoid enumeration.
73. `429`: rate limit exceeded with retry guidance that discloses no identity information.
74. `500`: generic safe public message only; no SQLSTATE, QueryException, table, lock, stack, class or filesystem path.
75. Public booking does not normally return `401`; anonymous access is intended. Authentication errors apply only if a future session boundary explicitly requires it.
76. Unknown/inactive Service or Professional should use the same safe unavailable policy rather than distinguish resource existence publicly.
77. Customer name/phone validation may identify errors in the visitor's own submitted fields but must never reveal database matching.
78. No public error returns Customer ID, Appointment ID, History, capacity, schedule rows or internal conflict details.

## 11. Frontend Discovery

79. Recommended public route is `/reservar` within the existing public SPA and PublicLayout/design system; no second SPA is needed.
80. Recommended mobile-first sequence: Service -> specific Professional -> date/time -> name/phone -> review -> submit -> on-screen success.
81. Changing Service resets Professional, selected slot and dependent pricing/duration state.
82. Changing Professional resets selected slot and availability state.
83. Changing date refetches availability for the selected Service/Professional/date.
84. Availability is fetched on Service, Professional and date changes and manually retried after `409`; no polling is required.
85. Stale availability responses are protected with request versioning or AbortController; the older response cannot overwrite newer selections.
86. A `409` preserves safe Service/Professional/contact context, refreshes availability and requires a new explicit slot choice; it never fakes success.
87. A `429` shows a safe retry-later message without exposing rate-limit keys.
88. Pricing UX is text-based: exact formatted amount for `fixed`, `Desde $X` for `starting_from`, `Precio variable` for `variable`; no payment CTA.
89. Time UX uses 24-hour business-local display, with server-provided UTC instants and local display values.
90. Native Vue state/composables are recommended; Pinia/Vuex are unnecessary for the bounded flow.
91. The existing native fetch wrapper is recommended; Axios is not needed.
92. Forms, loading, validation, conflict, unavailable, success and abuse states must be keyboard and screen-reader accessible.
93. Mobile behavior must support small/large phones, tablet and desktop without horizontal overflow or a scheduler grid.

## 12. Projection and Authority Matrices

### Data projection

| Projection | Proposed fields | Exclusions |
| --- | --- | --- |
| Context | timezone | full BusinessProfile, capacity internals |
| Service | id, name, duration, pricing_type, price, minimal category | admin metadata, payment fields |
| Professional | id, name | user, schedule, TimeOff, admin metadata |
| Slot | UTC start/end, local date/time | holds, locks, capacity details |
| Success | safe message, Service/Professional names, business-local time, confirmed status | Customer data, History, internal ID |
| Error | safe message, stable code where needed | SQL, locks, exception details |

### Authority matrix

| Concern | Authority |
| --- | --- |
| Business timezone | SPEC-003 BusinessProfile |
| Service pricing/duration | SPEC-003 Service; duration enforced by SPEC-004 |
| Professional compatibility | SPEC-003 relationship and SPEC-004 validation |
| Customer normalization | SPEC-003 `CustomerPhoneNormalizer` |
| Customer resolution orchestration | SPEC-006 public consumer |
| Availability/capacity | SPEC-004 |
| Appointment creation | SPEC-004 `CreateAppointment` |
| Concurrency/locking | SPEC-004 / ADR-003 |
| Lifecycle | SPEC-004 |
| History | SPEC-004 AppointmentHistory |
| Public presentation | SPEC-006 consumer |

### Transaction matrix

| Operation | Recommended owner | Notes |
| --- | --- | --- |
| Customer reuse | public orchestration transaction | no Customer mutation |
| Customer creation | same outer transaction | rollback if appointment fails |
| Appointment creation | existing `CreateAppointment` nested transaction | same connection/savepoint verification required |
| Idempotency | existing database cache lock/record | no domain schema |

## 13. Performance and Schema Analysis

94. Availability requests are bounded to one local day, one Service, one specific Professional and a 90-day horizon.
95. Candidate generation is capped at 96 15-minute starts before schedule filtering; actual business/schedule intervals reduce this set.
96. Existing Appointment indexes are `professional_id/status/starts_at/ends_at` and `status/starts_at/ends_at`; no new index is justified at Discovery.
97. Customer resolution uses existing indexed `phone_normalized`; no uniqueness constraint is proposed.
98. Active Service/Professional lookups use existing active/category/compatibility relationships; Development should verify query plans against realistic data.
99. History is not loaded for catalog, Professional or availability responses.
100. N+1 risk must be avoided in Service/Professional projections and slot validation; benchmark candidate validation before selecting a bulk strategy.
101. New tables: none required.
102. New columns: none required.
103. New indexes: none required.
104. New constraints: none required.
105. New migrations: none expected.
106. Idempotency uses the existing database cache table; it does not require Appointment `source`, `public_id`, `booking_token`, `schedule_version` or price snapshot.

## 14. ADR Assessment

107. No new ADR is required at Discovery: the recommendation stays within the modular monolith, existing cache/session facilities and SPEC-004 authority.
108. A future ADR is required before any durable cross-cutting change to public idempotency persistence, Customer identity semantics, public identifiers or availability architecture.

## 15. Development Checkpoint Plan

### Checkpoint A - Public Read API, Context and Catalog

- Scope: public context, active Service/category projection, pricing presentation data and active Service-compatible Professional projection.
- Evidence: API projection/auth/PII tests, no Customer/Appointment enumeration, safe errors and query review.
- Exclusions: availability, Customer resolution, booking mutation, payment, notifications and self-service.
- Stop boundary: stop if public read requires exposing raw schedules, TimeOff, capacity or Customer data.

### Checkpoint B - Bookable-Time Availability

- Scope: one-date bounded availability request, candidate generation, slot projection, timezone/DST handling, past-time/horizon bounds and SPEC-004 authority reuse.
- Evidence: bookable-time tests, DST tests, past/horizon tests, stale availability handling, performance/query evidence and no duplicated rules.
- Exclusions: Customer resolution, final booking mutation, holds, payments and notifications.
- Stop boundary: stop and request cross-SPEC review if existing exact-interval authority cannot meet performance without duplicate algorithms.

### Checkpoint C - Customer Resolution and Booking Mutation

- Scope: required name/phone, normalization, unambiguous reuse, safe multi-match behavior, minimal create/reuse transaction, idempotency and `CreateAppointment` delegation.
- Evidence: create/reuse/failure rollback tests, name mismatch, non-enumeration, malicious payload, duplicate submit, 409 race, fresh Service duration and confirmed response.
- Exclusions: Customer management, new Customer uniqueness, new Appointment status, public self-service and payments.
- Stop boundary: stop if nested transaction, idempotency or Customer identity needs schema/domain changes.

### Checkpoint D - Public Vue Booking Flow

- Scope: `/reservar`, mobile flow, state resets, pricing labels, slot selection, name/phone, review, submit, success and safe errors.
- Evidence: frontend flow, stale request, 409/429 UX, accessibility and responsive tests.
- Exclusions: Admin Agenda redesign, accounts, self-service mutations, notification/payment UI.
- Stop boundary: stop if flow requires a second SPA, state library or calendar dependency without approval.

### Checkpoint E - Security, Abuse and Conflict Hardening

- Scope: CSRF/session separation, rate limits, idempotency failure behavior, enumeration protection, safe logs/errors and race-focused consumer tests.
- Evidence: security/error/rate-limit tests, review of PII/logs, duplicate/race behavior and no raw internals.
- Exclusions: CAPTCHA provider, monitoring vendor, new auth/RBAC and cross-SPEC schema changes.
- Stop boundary: stop for any required external security dependency or closed-SPEC modification.

### Checkpoint F - Final Tests, Documentation and Acceptance Audit

- Scope: complete regression, performance/security/accessibility/scope audit, documentation and remote CI.
- Evidence: all gates, dedicated SPEC-004 concurrency, final report and clean tree.
- Exclusions: formal closure, merge and SPEC-007.
- Stop boundary: submit for human acceptance; do not auto-advance.

Development stop policy: no checkpoint starts automatically; each requires separate human authorization and its own acceptance evidence.

## 16. Test Strategy for Future Development

108. Backend tests: public context/catalog, active filtering, pricing projection, Professional compatibility, bounded availability, past/horizon behavior, DST, capacity, overlap, TimeOff, BusinessHours/Schedule effects, Customer create/reuse, non-enumeration, duplicate submit, successful CreateAppointment, stale-slot 409, deactivation races, rate limits and safe errors.
109. Focused concurrency tests: only public orchestration risks such as duplicate idempotency keys and Customer create/reuse races; retain and run the complete SPEC-004 concurrency suite without duplicating it.
110. Frontend tests: complete step flow, Service/Professional/slot reset, pricing labels, name/phone validation, stale requests, review, pending submit, success, 409/429 UX, accessible errors and mobile structure.
111. Security tests: no Customer/Appointment enumeration, malicious field rejection/ignoring, CSRF/session separation, rate-limit keys, idempotency mismatch and error disclosure.
112. DST/timezone tests: business-local date conversion, server-provided slot instants, no gap/fold slots, past-time exclusion and BusinessProfile timezone authority.

## 17. Cross-SPEC and Scope Audit

113. Current SPEC-003 changes required: none.
114. Current SPEC-004 changes required: none.
115. Current SPEC-005 changes required: none.
116. Future cross-SPEC review is required only for new statuses, holds, public lookup identifiers, Customer uniqueness/authentication, Appointment source/idempotency persistence or a new availability architecture.
117. Deferred: Customer accounts/CRUD, public cancel/reschedule/lookup, payments/deposits/checkout, notifications, WhatsApp/email/SMS, auto-assignment, rich calendars, RBAC, actor attribution, retention automation and SPEC-007+.

## Final State

```text
SPEC-006: DEFINITION COMPLETED / APPROVED
Technical Discovery: COMPLETED / AWAITING HUMAN APPROVAL
Development: NOT STARTED
Checkpoint A: NOT AUTHORIZED
SPEC-007+: NOT AUTHORIZED
Application changes: NONE
Schema/dependency changes: NONE
```

## Recommended Next Action

STOP. Submit Technical Discovery for human review. Do not create `feat/spec-006-public-booking`, start Checkpoint A, implement routes, write code/tests/migrations, install dependencies or start SPEC-007.
