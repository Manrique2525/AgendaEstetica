# SPEC-006 CHECKPOINT D REPORT

## Status

- Checkpoint D: `COMPLETED / READY FOR HUMAN APPROVAL`.
- Scope: public Vue booking flow at `/reservar`.
- No backend, schema, dependency, authentication or API route changes were introduced.

## Delivered

- Added the public booking API client using the existing native fetch wrapper.
- Added the unauthenticated `/reservar` route and mobile-first booking page.
- Implemented Service, compatible Professional, business-local date and server-provided slot selection.
- Implemented dependent state resets and stale availability request protection.
- Implemented pricing labels from the authoritative public Service projection.
- Implemented accessible name and phone fields, review, pending submit and confirmed success state.
- Implemented safe handling for validation errors, stale `409`, rate-limit `429` and unexpected failures.
- Generated and retained the frontend `Idempotency-Key` for unchanged submission retries.
- Invalidated the key after an `appointment_unavailable` conflict so a newly selected slot submits with a new key.

## Evidence

| Check | Result |
| --- | --- |
| `npm run typecheck` | PASS |
| `npm run lint` | PASS |
| `npm run test` | PASS, 55 tests |
| `npm run build` | PASS |
| `php artisan test` | PASS, 198 tests / 1156 assertions |
| `vendor/bin/pint --test` | PASS, 132 files |

Focused frontend coverage includes the complete guest flow, approved POST payload, idempotency key presence and stale-slot conflict recovery.

## Final Human-Review Evidence

| Critical behavior | Covered | Test file | Exact test name |
| --- | --- | --- | --- |
| `/reservar` public route, no admin auth | YES | `resources/js/router/index.test.ts` | `resolves the public booking route without authentication` |
| PublicLayout used | YES | `resources/js/pages/public/PublicBookingPage.test.ts` | `renders publicly with PublicLayout and completes the guest flow with the exact payload` |
| Canonical server `starts_at` | YES | `resources/js/pages/public/PublicBookingPage.test.ts` | `renders publicly with PublicLayout and completes the guest flow with the exact payload` |
| Exact POST payload and Idempotency-Key | YES | `resources/js/services/api/publicBooking.test.ts` | `sends only the approved mutation payload and Idempotency-Key header` |
| Same-payload uncertain retry | YES | `resources/js/pages/public/PublicBookingPage.test.ts` | `uses the same key and payload for an uncertain unchanged retry` |
| Changed payload creates new key | YES | `resources/js/pages/public/PublicBookingPage.test.ts` | `creates a new key when a fingerprint field changes` |
| Pending double-submit | YES | `resources/js/pages/public/PublicBookingPage.test.ts` | `allows only one POST when submit is triggered twice while pending` |
| `appointment_unavailable` preservation/refetch/new key | YES | `resources/js/pages/public/PublicBookingPage.test.ts` | `preserves contact context and refreshes availability after appointment_unavailable` |
| Stale availability race | YES | `resources/js/pages/public/PublicBookingPage.test.ts` | `ignores a late availability response from an older selection` |
| `idempotency_request_in_progress` | YES | `resources/js/pages/public/PublicBookingPage.test.ts` | `handles %s without success or internal details` (`idempotency_request_in_progress`) |
| `idempotency_key_conflict` | YES | `resources/js/pages/public/PublicBookingPage.test.ts` | `handles %s without success or internal details` (`idempotency_key_conflict`) |
| `429` safe retry-later UX | YES | `resources/js/pages/public/PublicBookingPage.test.ts` | `shows safe retry-later UX for 429 without an automatic retry loop` |
| `201` confirmed success | YES | `resources/js/pages/public/PublicBookingPage.test.ts` | `renders publicly with PublicLayout and completes the guest flow with the exact payload` |
| Service dependent reset | YES | `resources/js/pages/public/PublicBookingPage.test.ts` | `resets dependent state when Service or Professional changes` |
| Professional dependent reset | YES | `resources/js/pages/public/PublicBookingPage.test.ts` | `resets dependent state when Service or Professional changes` |
| Customer fields limited to name/phone | YES | `resources/js/pages/public/PublicBookingPage.test.ts` | `renders publicly with PublicLayout and completes the guest flow with the exact payload` |
| fixed/starting_from/variable pricing | YES | `resources/js/pages/public/PublicBookingPage.test.ts` | `renders every authoritative pricing label without adding a payment action` |

## Scope Audit

- No Pinia/Vuex, Axios, calendar dependency, second SPA, localStorage or sessionStorage.
- No Admin Agenda redesign, accounts, self-service, payments or notifications.
- Backend remains `4 GET / 1 POST` for the public booking boundary.

## Stop Boundary

Checkpoint E and F remain unauthorized. Submit this checkpoint for human acceptance; do not start another SPEC automatically.
