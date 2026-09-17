# SPEC-006 CHECKPOINT E REPORT

## Status

- Checkpoint E: `COMPLETED / READY FOR HUMAN APPROVAL`.
- Scope: booking abuse limits, safe throttling, enumeration/error hardening and security-focused regression tests.
- Checkpoint F, closure, merge and SPEC-007 remain unauthorized.

## Delivered

- Added a Laravel route limiter of `5 requests/minute/IP` to the existing public booking POST.
- Added a `3 requests/hour` limiter keyed by HMAC-SHA256 of the normalized phone using `config('app.key')`.
- Applied phone quota only after idempotency replay/conflict inspection and before Customer/Appointment execution.
- Preserved the existing C idempotency lock, replay, conflict and bounded in-progress behavior.
- Standardized both public throttle paths on safe JSON `429` with code `too_many_requests`; IP throttles retain Laravel `Retry-After`.
- Preserved generic resource conflicts and safe generic API `500` responses.
- Added focused security tests for IP isolation, phone normalization/HMAC isolation, quota accounting, replay/conflict behavior, safe `429` and safe `500`.

## Quota Policy

| Request outcome | IP quota | Phone execution quota |
| --- | --- | --- |
| Any booking HTTP request | Counts | Not applicable |
| `422` transport validation | Counts | Does not count |
| Successful new execution | Counts | Counts |
| `409 appointment_unavailable` after valid execution | Counts | Counts |
| Successful same-key replay | Counts | Does not count |
| Same-key fingerprint conflict | Counts | Does not count |
| Same-key in-progress conflict | Counts | Does not count; bounded behavior remains covered by C |

## Security Audit

- Customer matching branch, Customer ID, normalized phone and Appointment ID remain private.
- No raw phone, normalized phone, Idempotency-Key or application key is logged by SPEC-006 code.
- No `auth:sanctum`, bearer token, CORS or CSRF exclusion was added to public booking.
- No CAPTCHA, external anti-bot provider, Redis or monitoring infrastructure was added.
- Existing BusinessProfile, Professional and Appointment lock ordering remains unchanged.
- Availability implementation and its accepted 295-query evidence were not modified.

## Evidence

| Check | Result |
| --- | --- |
| Focused E security tests | PASS, 9 tests / 41 assertions |
| Full backend | PASS, 207 tests / 1197 assertions |
| SPEC-004 concurrency | PASS, 17 tests / 212 assertions / 0 skipped |

The focused E suite uses the existing database cache/rate-limiter infrastructure and does not add schema or dependencies.

## Schema and Dependency Audit

```text
new tables: NONE
new migrations: NONE
new columns: NONE
new indexes: NONE
new constraints: NONE
Composer dependencies: NONE
npm dependencies: NONE
infrastructure: NONE
```

## Stop Boundary

Checkpoint F, final acceptance audit, closure, merge and SPEC-007 remain unauthorized. Submit Checkpoint E for human review and stop.
