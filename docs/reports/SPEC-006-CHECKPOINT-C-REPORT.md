# SPEC-006 CHECKPOINT C REPORT

## Scope

Checkpoint C implements only server-side Customer resolution, the public booking POST, immediate confirmed Appointment creation through `CreateAppointment`, atomic Customer/Appointment behavior, Idempotency-Key validation/locking/replay/conflict handling, same-origin public POST integration and safe public mutation errors. It does not implement Checkpoint D `/reservar` or Vue, Checkpoint E full abuse/phone-HMAC throttling, Checkpoint F, public self-service, payments, notifications, new schema, dependencies or SPEC-007.

## Route Surface

The final public SPEC-006 surface is exactly four GET routes and one POST route:

```text
GET  /api/v1/public/booking/context
GET  /api/v1/public/booking/services
GET  /api/v1/public/booking/professionals?service_id={id}
GET  /api/v1/public/booking/availability?service_id={id}&professional_id={id}&date=YYYY-MM-DD
POST /api/v1/public/booking/appointments
```

No public Customer, Appointment detail/lookup, cancellation, reschedule, payment or notification route was added. The POST is public and does not use `auth:sanctum`; Admin Agenda authentication remains unchanged.

## Request Contract

`PublicBookingAppointmentRequest` requires:

```text
service_id
professional_id
starts_at
name
phone
```

It also requires the `Idempotency-Key` header as a UUID. `starts_at` requires ISO-8601 with an explicit offset or UTC `Z`. The request does not accept authority for `customer_id`, `ends_at`, `duration_minutes`, `status`, `price`, `pricing_type`, capacity, timezone, source, notes or history. Malformed input and missing/invalid key return JSON `422`.

## Customer Resolution

The existing `CustomerPhoneNormalizer` is the only phone normalization authority. The existing `phone_normalized` index remains non-unique and is treated only as a lookup signal.

The finalized resolution matrix is:

```text
0 matches
  -> create minimal Customer

1 match + equivalent canonical name
  -> reuse existing Customer

1 match + different canonical name
  -> create minimal Customer

2+ matches
  -> create minimal Customer
```

Name equivalence trims outer whitespace, collapses repeated internal whitespace and compares case-insensitively with Unicode-safe lowercasing. No fuzzy, nickname, phonetic, Levenshtein or meaningful-component removal is used. Reused Customers are never renamed, updated or merged. Public responses never reveal existence, match count, branch, Customer ID or `phone_normalized`.

New Customer creation uses only `name`, submitted `phone` and normalized phone. It occurs only inside booking; no Customer search/list/detail/management endpoint exists.

## Transaction and Authority

The orchestration flow is:

```text
validate transport
-> normalize canonical input
-> validate Idempotency-Key
-> compute fingerprint
-> acquire idempotency lock
-> inspect replay/conflict state
-> outer same-connection DB transaction
-> qualify current Service/Category/Professional
-> resolve/create Customer
-> derive ends_at from current Service.duration_minutes
-> CreateAppointment
-> commit
-> cache safe success snapshot
-> release lock
```

`CreateAppointment` owns Appointment persistence, final availability validation, capacity, duration snapshot, lifecycle and AppointmentHistory. SPEC-006 performs no direct Appointment or History write. Customer resolution does not pre-lock BusinessProfile, Professional or Appointment and does not alter ADR-003 lock order.

The outer transaction composes with the existing same-connection nested `DB::transaction()` used by `CreateAppointment` through Laravel savepoints. A newly created Customer rolls back when Appointment creation fails; a reused Customer remains unchanged. The test suite proves both outcomes against MySQL.

## Fresh State and Errors

The orchestration re-qualifies active Service/Category/Professional compatibility and derives the end from the current Service. The client cannot shorten/extend duration or submit an alternate end. Stale deactivation, incompatibility, past time or availability conflicts return safe HTTP `409` with `appointment_unavailable`; no Customer or Appointment remains.

Successful creation delegates to `CreateAppointment`, produces status `confirmed` and retains SPEC-004 creation history. The public response is HTTP `201` with a safe message, confirmed status, Service name, Professional name and canonical UTC start/end. It excludes Customer IDs/phone, normalized phone, Appointment ID, History, capacity and internal details.

## Idempotency

`Idempotency-Key` is mandatory on `POST /api/v1/public/booking/appointments` and must be a UUID. The server computes a SHA-256 fingerprint from deterministic sorted serialization of:

```text
service_id
professional_id
starts_at canonical instant
normalized phone
canonical name
```

Internal cache and lock names use HMAC-SHA256 with `config('app.key')` and the raw key is not logged. The existing database-backed Laravel cache and `cache_locks` infrastructure is used with a 15-minute result TTL and a maximum 2-second lock wait.

```text
same key + same fingerprint
  -> replay exact safe successful response

same key + different fingerprint
  -> 409 idempotency_key_conflict

same key concurrently, no result after bounded wait
  -> 409 idempotency_request_in_progress
```

Only successful responses after database commit are cached. Validation, `appointment_unavailable`, `429`, `500` and uncommitted failures are not cached as successful results. The narrow process-crash window between database commit and cache write remains an accepted V1 limitation; SPEC-004 conflict authority prevents a second blocking Appointment. Different Idempotency-Keys remain distinct requests and are governed by SPEC-004 availability/concurrency.

## Session and Security Boundary

The public POST is anonymous and same-origin. It does not use `auth:sanctum`, bearer tokens or localStorage authentication. Existing `statefulApi()` and XSRF cookie/header behavior remain the CSRF integration boundary; global CSRF is not disabled and CORS is unchanged. Full submission rate limiting, phone-HMAC throttling and broader abuse hardening remain deferred to Checkpoint E. C uses no new phone limiter.

Errors do not disclose SQLSTATE, QueryException, table names, lock internals, exception classes, Customer matching or stack traces. Malicious fields are ignored by the focused whitelist and cannot mutate unrelated state.

## Tests

Added `tests/Feature/Api/PublicBookingAppointmentTest.php` covering:

- missing/invalid Idempotency-Key and offset-less timestamp;
- new Customer creation and confirmed `CreateAppointment` booking;
- exactly-one equivalent-name Customer reuse;
- different-name and multiple-match Customer creation;
- no automatic existing Customer mutation;
- new Customer rollback on unavailable Appointment;
- reused Customer preservation on failure;
- fresh Service duration and malicious-field exclusion;
- stale inactive/incompatible resource rejection;
- exact successful replay without second Appointment/Customer/History;
- same-key different-fingerprint conflict;
- different-key SPEC-004 slot conflict;
- failed request not cached as success;
- 15-minute cache expiration;
- configured cache lock support and bounded in-progress conflict.

Focused C tests: 16 tests, 80 assertions, PASS.

## Regression and Quality Evidence

- Full backend: 197 tests, 1151 assertions, PASS.
- SPEC-004 concurrency: 17 tests, 212 assertions, PASS, not skipped.
- Checkpoint-A/B regressions: PASS.
- Frontend regression: 13 files, 42 tests, PASS.
- Pint: PASS.
- PHPStan: PASS.
- Composer validate/audit: PASS.
- ESLint, TypeScript, build and npm audit: PASS.
- Public route audit: exactly 4 GET and 1 POST route.
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

Not implemented: Checkpoint D `/reservar`/Vue/frontend Idempotency-Key lifecycle, Checkpoint E full abuse and phone-HMAC submission throttling, Checkpoint F, public cancellation/reschedule/lookup, new statuses, holds, payments, notifications, availability optimization/caching/indexes and SPEC-007+.

SPEC-003, SPEC-004 and SPEC-005 documents, closure reports and ADR-003 remain unchanged. No availability implementation was modified in C.

## Status

```text
SPEC-006: APPROVED FOR DEVELOPMENT / IN PROGRESS
Definition: COMPLETED / APPROVED
Technical Discovery: COMPLETED / APPROVED
Checkpoint A: COMPLETED / APPROVED
Checkpoint B: COMPLETED / APPROVED
Checkpoint C: COMPLETED / READY FOR HUMAN APPROVAL
Checkpoint D: NOT AUTHORIZED
Checkpoint E: NOT AUTHORIZED
Checkpoint F: NOT AUTHORIZED
SPEC-007+: NOT AUTHORIZED
```

## Recommended Next Action

Submit Checkpoint C for human review. Do not start Checkpoint D, `/reservar` or the public frontend.
